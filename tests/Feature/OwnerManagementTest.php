<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\PoPeriod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_po_period_store_validates_dates_closes_previous_open_po_and_toggles(): void
    {
        $owner = $this->createUser(User::ROLE_OWNER);
        $today = now('Asia/Jakarta')->toDateString();
        $nextWeek = now('Asia/Jakarta')->addDays(7)->toDateString();

        $this->actingAs($owner)->post(route('owner.po-periods.store'), [
            'name' => 'PO Invalid Date',
            'start_date' => $nextWeek,
            'end_date' => $today,
            'status' => PoPeriod::STATUS_OPEN,
        ])->assertSessionHasErrors(['end_date']);

        $this->actingAs($owner)->post(route('owner.po-periods.store'), [
            'name' => 'Batch Pertama',
            'start_date' => $today,
            'end_date' => $nextWeek,
            'status' => PoPeriod::STATUS_OPEN,
            'notes' => 'Batch awal',
        ])->assertRedirect(route('owner.po-periods.index'));

        $firstPo = PoPeriod::where('name', 'Batch Pertama')->firstOrFail();
        $this->assertSame($firstPo->id, PoPeriod::cachedActive()?->id);

        $this->actingAs($owner)->post(route('owner.po-periods.store'), [
            'name' => 'Batch Kedua',
            'start_date' => $today,
            'end_date' => $nextWeek,
            'status' => PoPeriod::STATUS_OPEN,
        ])->assertRedirect(route('owner.po-periods.index'));

        $secondPo = PoPeriod::where('name', 'Batch Kedua')->firstOrFail();
        $this->assertSame(PoPeriod::STATUS_CLOSED, $firstPo->fresh()->status);
        $this->assertSame(PoPeriod::STATUS_OPEN, $secondPo->status);
        $this->assertSame($secondPo->id, PoPeriod::cachedActive()?->id);

        $this->actingAs($owner)->post(route('owner.po-periods.status.toggle', $secondPo->id))
            ->assertRedirect(route('owner.po-periods.index'));

        $this->assertSame(PoPeriod::STATUS_CLOSED, $secondPo->fresh()->status);
        $this->assertNull(PoPeriod::cachedActive());
    }

    public function test_owner_can_create_update_and_toggle_products_with_variants_and_audit_logs(): void
    {
        $owner = $this->createUser(User::ROLE_OWNER);

        $this->actingAs($owner)->post(route('owner.products.store'), [
            'name' => 'Produk Tanpa Harga',
            'brand' => 'In Parfume',
            'variants' => [35 => [], 60 => [], 100 => []],
        ])->assertSessionHasErrors(['variants']);

        $this->actingAs($owner)->post(route('owner.products.store'), [
            'name' => 'Velvet Oud',
            'brand' => 'In Parfume',
            'category' => 'Woody Aromatic',
            'description' => 'Aroma hangat dengan oud lembut.',
            'variants' => [
                35 => ['price' => 42000, 'cost_price' => 21000],
                60 => ['price' => 95000, 'cost_price' => 48000],
                100 => ['price' => null, 'cost_price' => null],
            ],
        ])->assertRedirect(route('owner.products.index'));

        $product = Product::where('name', 'Velvet Oud')->with('variants')->firstOrFail();
        $this->assertCount(2, $product->variants);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'product.created',
            'subject_type' => Product::class,
            'subject_id' => $product->id,
        ]);

        $this->actingAs($owner)->put(route('owner.products.update', $product->id), [
            'name' => 'Velvet Oud Reserve',
            'brand' => 'In Parfume',
            'category' => 'Woody Aromatic',
            'is_active' => 1,
            'variants' => [
                35 => ['price' => 45000, 'cost_price' => 23000],
                60 => ['price' => null, 'cost_price' => null],
                100 => ['price' => 145000, 'cost_price' => 75000],
            ],
        ])->assertRedirect(route('owner.products.index'));

        $product->refresh();
        $this->assertSame('Velvet Oud Reserve', $product->name);
        $this->assertSame([35, 100], $product->variants()->orderBy('size_ml')->pluck('size_ml')->all());
        $this->assertSame(
            1,
            AuditLog::where('event', 'product.updated')
                ->where('subject_type', Product::class)
                ->where('subject_id', $product->id)
                ->count()
        );

        $this->actingAs($owner)->post(route('owner.products.toggle', $product->id))
            ->assertRedirect(route('owner.products.index'));
        $this->assertFalse($product->fresh()->is_active);
    }

    public function test_owner_recap_aggregates_verified_orders_only_and_customer_search_filters(): void
    {
        $owner = $this->createUser(User::ROLE_OWNER);
        $period = PoPeriod::create([
            'name' => 'PO Rekap Uji',
            'start_date' => now('Asia/Jakarta')->toDateString(),
            'end_date' => now('Asia/Jakarta')->addDays(5)->toDateString(),
            'status' => PoPeriod::STATUS_OPEN,
            'created_by' => $owner->id,
        ]);

        $product = Product::create([
            'name' => 'Amber Bloom',
            'brand' => 'In Parfume',
            'category' => 'Amber Floral',
            'size_ml' => 60,
            'price' => 95000,
            'is_active' => true,
        ]);

        $sitiUser = $this->createUser(User::ROLE_CUSTOMER, 'siti@example.test');
        $siti = Customer::create([
            'user_id' => $sitiUser->id,
            'full_name' => 'Siti Aminah',
            'phone_number' => '081234567890',
            'city' => 'Bandung',
        ]);
        $budiUser = $this->createUser(User::ROLE_CUSTOMER, 'budi@example.test');
        $budi = Customer::create([
            'user_id' => $budiUser->id,
            'full_name' => 'Budi Santoso',
            'phone_number' => '081987654321',
            'city' => 'Jakarta',
        ]);

        $verifiedOrder = Order::create([
            'order_number' => 'UJ-RECAP-1',
            'customer_id' => $siti->id,
            'po_period_id' => $period->id,
            'total_amount' => 190000,
            'status' => Order::STATUS_PAYMENT_VERIFIED,
            'recipient_name' => 'Siti Aminah',
            'recipient_phone' => '081234567890',
            'shipping_address' => 'Bandung',
        ]);
        OrderDetail::create([
            'order_id' => $verifiedOrder->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'size_ml' => 60,
            'quantity' => 2,
            'unit_price' => 95000,
            'cost_price' => 45000,
            'subtotal' => 190000,
        ]);
        Payment::create([
            'order_id' => $verifiedOrder->id,
            'payment_method' => 'BCA Merchant',
            'amount' => 190000,
            'verification_status' => Payment::STATUS_VALID,
            'verified_at' => now(),
        ]);

        $pendingOrder = Order::create([
            'order_number' => 'UJ-RECAP-2',
            'customer_id' => $budi->id,
            'po_period_id' => $period->id,
            'total_amount' => 285000,
            'status' => Order::STATUS_WAITING_PAYMENT,
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '081987654321',
            'shipping_address' => 'Jakarta',
        ]);
        OrderDetail::create([
            'order_id' => $pendingOrder->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'size_ml' => 60,
            'quantity' => 3,
            'unit_price' => 95000,
            'cost_price' => 45000,
            'subtotal' => 285000,
        ]);
        Payment::create([
            'order_id' => $pendingOrder->id,
            'payment_method' => 'BCA Merchant',
            'amount' => 285000,
            'verification_status' => Payment::STATUS_PENDING,
        ]);

        $recapResponse = $this->actingAs($owner)->get(route('owner.recap.index', ['po_period_id' => $period->id]));
        $recapResponse->assertOk();
        $recapResponse->assertViewHas('summary', function (array $summary): bool {
            return (int) $summary['total_items'] === 2
                && (float) $summary['total_revenue'] === 190000.0
                && (float) $summary['total_cost'] === 90000.0
                && (int) $summary['verified_orders'] === 1
                && (int) $summary['unpaid_orders'] === 1;
        });

        $this->actingAs($owner)
            ->get(route('owner.customers.index', ['search' => 'Siti']))
            ->assertOk()
            ->assertSee('Siti Aminah')
            ->assertDontSee('Budi Santoso');
    }

    public function test_owner_can_view_edit_product_page_and_unauthorized_users_are_denied(): void
    {
        $owner = $this->createUser(User::ROLE_OWNER);
        $admin = $this->createUser(User::ROLE_ADMIN);
        $customer = $this->createUser(User::ROLE_CUSTOMER);

        $product = Product::create([
            'name' => 'Rose Damascena',
            'brand' => 'In Parfume',
            'category' => 'Amber Floral',
            'description' => 'Mawar damaskus premium dengan aroma sensual.',
            'size_ml' => 35,
            'price' => 45000,
            'is_active' => true,
        ]);
        $product->variants()->createMany([
            ['size_ml' => 35, 'price' => 45000, 'cost_price' => 22000],
            ['size_ml' => 60, 'price' => 85000, 'cost_price' => 42000],
            ['size_ml' => 100, 'price' => 135000, 'cost_price' => 68000],
        ]);

        // 1. Guest redirected to login
        $this->get(route('owner.products.edit', $product->id))
            ->assertRedirect(route('login'));

        // 2. Customer denied (403)
        $this->actingAs($customer)
            ->get(route('owner.products.edit', $product->id))
            ->assertForbidden();

        // 3. Admin denied (403)
        $this->actingAs($admin)
            ->get(route('owner.products.edit', $product->id))
            ->assertForbidden();

        // 4. Owner receives 200 OK and view compiles cleanly
        $response = $this->actingAs($owner)
            ->get(route('owner.products.edit', $product->id));

        $response->assertOk();
        $response->assertSee('Ubah informasi produk parfum');
        $response->assertSee('Rose Damascena');
        $response->assertSee('value="45000', false);
        $response->assertSee('value="22000', false);
        $response->assertSee('value="85000', false);
        $response->assertSee('value="135000', false);
        $response->assertSee(route('owner.products.update', $product->id));
        $response->assertSee('name="_method" value="PUT"', false);
    }

    private function createUser(string $role, ?string $email = null): User
    {
        return User::create([
            'name' => 'Owner Test ' . $role,
            'email' => $email ?? ($role . '-' . Str::uuid() . '@example.test'),
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }
}

