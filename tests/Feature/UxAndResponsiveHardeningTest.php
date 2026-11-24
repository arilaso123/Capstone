<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\PoPeriod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class UxAndResponsiveHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_sorting_supports_price_asc_price_desc_name_asc_and_rejects_invalid_sort(): void
    {
        $mid = $this->createProductWithVariant('Beta Scent', 'Floral', 90000);
        $cheap = $this->createProductWithVariant('Zeta Scent', 'Fresh', 50000);
        $expensive = $this->createProductWithVariant('Alpha Scent', 'Woody', 140000);

        $ascResponse = $this->get(route('catalog', ['sort' => 'price_asc']));
        $ascResponse->assertOk();
        $ascIds = $ascResponse->viewData('products')->pluck('id')->all();
        $this->assertSame([$cheap->id, $mid->id, $expensive->id], $ascIds);

        $descResponse = $this->get(route('catalog', ['sort' => 'price_desc']));
        $descResponse->assertOk();
        $descIds = $descResponse->viewData('products')->pluck('id')->all();
        $this->assertSame([$expensive->id, $mid->id, $cheap->id], $descIds);

        $nameResponse = $this->get(route('catalog', ['sort' => 'name_asc']));
        $nameResponse->assertOk();
        $nameIds = $nameResponse->viewData('products')->pluck('id')->all();
        $this->assertSame([$expensive->id, $mid->id, $cheap->id], $nameIds);

        $this->get(route('catalog', ['sort' => 'malicious_sql']))
            ->assertSessionHasErrors(['sort']);
    }

    public function test_product_detail_shows_related_products_prioritizing_same_category(): void
    {
        $main = $this->createProductWithVariant('Delina Utama', 'Floral Rose', 100000);
        $sameCat = $this->createProductWithVariant('Rose Satin', 'Floral Rose', 85000);
        $otherCat = $this->createProductWithVariant('Cedar Wood', 'Woody', 95000);

        $response = $this->get(route('product.detail', $main->id));
        $response->assertOk();
        $related = $response->viewData('relatedProducts');

        $this->assertNotContains($main->id, $related->pluck('id')->all());
        $this->assertSame($sameCat->id, $related->first()->id);
        $this->assertContains($otherCat->id, $related->pluck('id')->all());
    }

    public function test_customer_can_view_and_update_profile_with_phone_normalization_and_rbac(): void
    {
        $customerUser = $this->createUser(User::ROLE_CUSTOMER, 'Budi Awal');
        $adminUser = $this->createUser(User::ROLE_ADMIN, 'Admin Uji');

        $this->actingAs($adminUser)
            ->get(route('customer.profile.edit'))
            ->assertForbidden();

        $this->actingAs($customerUser)
            ->get(route('customer.profile.edit'))
            ->assertOk()
            ->assertSee('Profil &amp; alamat pengiriman', false);

        $this->actingAs($customerUser)
            ->put(route('customer.profile.update'), [
                'full_name' => 'Budi Santoso',
                'phone_number' => '12345',
                'default_address' => 'Jl. Braga No. 10',
                'city' => 'Bandung',
                'postal_code' => '40111',
            ])
            ->assertSessionHasErrors(['phone_number']);

        $this->actingAs($customerUser)
            ->put(route('customer.profile.update'), [
                'full_name' => 'Budi Santoso',
                'phone_number' => '0812-3456-7890',
                'default_address' => 'Jl. Braga No. 10, Bandung',
                'city' => 'Bandung',
                'postal_code' => '40111',
            ])
            ->assertRedirect(route('customer.profile.edit'));

        $customerUser->refresh();
        $this->assertSame('Budi Santoso', $customerUser->name);
        $this->assertDatabaseHas('customers', [
            'user_id' => $customerUser->id,
            'full_name' => 'Budi Santoso',
            'phone_number' => '081234567890',
            'city' => 'Bandung',
            'postal_code' => '40111',
        ]);
    }

    public function test_cart_json_update_recalculates_totals_and_rotates_checkout_token(): void
    {
        $owner = $this->createUser(User::ROLE_OWNER, 'Owner Cart');
        PoPeriod::create([
            'name' => 'Batch Cart Test',
            'start_date' => now('Asia/Jakarta')->toDateString(),
            'end_date' => now('Asia/Jakarta')->addDays(5)->toDateString(),
            'status' => PoPeriod::STATUS_OPEN,
            'created_by' => $owner->id,
        ]);

        $customerUser = $this->createUser(User::ROLE_CUSTOMER, 'Pelanggan Cart');
        $product = $this->createProductWithVariant('Baccarat 540', 'Amber Floral', 75000);
        $variant = $product->variants()->firstOrFail();

        $this->actingAs($customerUser)
            ->post(route('customer.cart.add'), [
                'product_id' => $product->id,
                'size_ml' => $variant->size_ml,
                'quantity' => 1,
            ])
            ->assertRedirect();

        $initialToken = session('checkout_token');
        $cartKey = $product->id . '_' . $variant->size_ml;

        $jsonResponse = $this->actingAs($customerUser)
            ->putJson(route('customer.cart.update', $cartKey), [
                'quantity' => 3,
            ]);

        $jsonResponse->assertOk()
            ->assertJson([
                'success' => true,
                'quantity' => 3,
                'item_subtotal' => 225000,
                'total' => 225000,
                'total_bottles' => 3,
            ]);

        $rotatedToken = $jsonResponse->json('checkout_token');
        $this->assertNotEmpty($rotatedToken);
        $this->assertNotSame($initialToken, $rotatedToken);

        $this->actingAs($customerUser)
            ->putJson(route('customer.cart.update', '99999_35'), ['quantity' => 2])
            ->assertNotFound();
    }

    public function test_owner_recap_csv_export_includes_only_verified_payments_sanitizes_formulas_and_enforces_rbac(): void
    {
        $owner = $this->createUser(User::ROLE_OWNER, 'Owner Uji');
        $customerUser = $this->createUser(User::ROLE_CUSTOMER, 'Customer Uji');
        $customer = Customer::create([
            'user_id' => $customerUser->id,
            'full_name' => 'Customer Uji',
            'phone_number' => '6281234567800',
            'default_address' => 'Jl. Asia Afrika No. 1',
            'city' => 'Bandung',
        ]);

        $poPeriod = PoPeriod::create([
            'name' => 'Batch Ekspor CSV',
            'start_date' => now('Asia/Jakarta')->toDateString(),
            'end_date' => now('Asia/Jakarta')->addDays(5)->toDateString(),
            'status' => PoPeriod::STATUS_OPEN,
            'created_by' => $owner->id,
        ]);

        $verifiedProduct = $this->createProductWithVariant('=CMD() Verified Perfume', 'Woody', 100000);
        $unpaidProduct = $this->createProductWithVariant('Unpaid Perfume', 'Fresh', 75000);

        $verifiedOrder = Order::create([
            'order_number' => 'ORD-CSV-VALID-1',
            'customer_id' => $customer->id,
            'po_period_id' => $poPeriod->id,
            'status' => Order::STATUS_PAYMENT_VERIFIED,
            'total_amount' => 200000,
            'recipient_name' => 'Customer Uji',
            'recipient_phone' => '081234567800',
            'shipping_address' => 'Jl. Asia Afrika No. 1',
        ]);
        OrderDetail::create([
            'order_id' => $verifiedOrder->id,
            'product_id' => $verifiedProduct->id,
            'product_name' => $verifiedProduct->name,
            'size_ml' => 35,
            'quantity' => 2,
            'unit_price' => 100000,
            'cost_price' => 60000,
            'subtotal' => 200000,
        ]);
        Payment::create([
            'order_id' => $verifiedOrder->id,
            'amount' => 200000,
            'payment_method' => 'Transfer Bank',
            'proof_file_path' => 'payment_proofs/dummy.jpg',
            'verification_status' => Payment::STATUS_VALID,
            'verified_at' => now('Asia/Jakarta'),
        ]);

        $unpaidOrder = Order::create([
            'order_number' => 'ORD-CSV-UNPAID-1',
            'customer_id' => $customer->id,
            'po_period_id' => $poPeriod->id,
            'status' => Order::STATUS_WAITING_PAYMENT,
            'total_amount' => 75000,
            'recipient_name' => 'Customer Uji',
            'recipient_phone' => '081234567800',
            'shipping_address' => 'Jl. Asia Afrika No. 1',
        ]);
        OrderDetail::create([
            'order_id' => $unpaidOrder->id,
            'product_id' => $unpaidProduct->id,
            'product_name' => $unpaidProduct->name,
            'size_ml' => 35,
            'quantity' => 1,
            'unit_price' => 75000,
            'cost_price' => 45000,
            'subtotal' => 75000,
        ]);

        $this->actingAs($customerUser)
            ->get(route('owner.recap.export', ['po_period_id' => $poPeriod->id]))
            ->assertForbidden();

        $response = $this->actingAs($owner)
            ->get(route('owner.recap.export', ['po_period_id' => $poPeriod->id]));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));

        $csvContent = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csvContent);
        $this->assertStringContainsString("'=CMD() Verified Perfume", $csvContent);
        $this->assertStringNotContainsString('Unpaid Perfume', $csvContent);
    }

    public function test_layouts_render_cross_device_meta_tags_and_csp_safe_back_navigation(): void
    {
        $homeResponse = $this->get(route('home'));
        $homeResponse->assertOk();
        $homeResponse->assertSee('viewport-fit=cover', false);
        $homeResponse->assertSee('data-nav-root="true"', false);
        $homeResponse->assertDontSee('editorial-back-btn', false);
        $homeResponse->assertDontSee('onclick=', false);

        $catalogResponse = $this->get(route('catalog'));
        $catalogResponse->assertOk();
        $catalogResponse->assertSee('viewport-fit=cover', false);
        $catalogResponse->assertSee('data-nav-root="false"', false);
        $catalogResponse->assertSee('editorial-back-btn', false);
        $catalogResponse->assertDontSee('onclick=', false);
    }

    private function createUser(string $role, string $name): User
    {
        return User::create([
            'name' => $name,
            'email' => Str::lower(Str::random(10)) . '@example.com',
            'password' => Hash::make('password123'),
            'role' => $role,
        ]);
    }

    private function createProductWithVariant(string $name, string $category, float $price): Product
    {
        $product = Product::create([
            'name' => $name,
            'brand' => 'In Parfume Fragrance',
            'category' => $category,
            'description' => 'Parfum uji untuk verifikasi fitur.',
            'price' => $price,
            'size_ml' => 35,
            'is_active' => true,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'size_ml' => 35,
            'price' => $price,
            'cost_price' => $price * 0.6,
        ]);

        return $product;
    }
}
