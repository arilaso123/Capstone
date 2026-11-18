<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\PoPeriod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class RoleAccessAndAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_storefront_pages_render_and_filter_catalog(): void
    {
        $owner = $this->createUser(User::ROLE_OWNER);
        $this->createOpenPo($owner);

        $matching = $this->createProduct('Santal Noir', 'Woody Aromatic', 60, 95000, true);
        $other = $this->createProduct('Citrus Breeze', 'Fresh Citrus', 60, 88000, true);
        $inactive = $this->createProduct('Hidden Oud', 'Woody Aromatic', 60, 120000, false);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Uyuy Jastip')
            ->assertSee('PO Uji Aktif');

        $this->get(route('catalog', ['search' => 'Santal']))
            ->assertOk()
            ->assertSee($matching->name)
            ->assertDontSee($other->name)
            ->assertDontSee($inactive->name);

        $this->get(route('product.detail', $matching->id))
            ->assertOk()
            ->assertSee($matching->name);

        $this->get(route('product.detail', $inactive->id))->assertNotFound();
        $this->get(route('po.info'))->assertOk();
    }

    public function test_customer_registration_normalizes_phone_saves_postal_code_and_validates_inputs(): void
    {
        $this->post(route('register.post'), [
            'name' => 'Pelanggan Baru',
            'email' => 'invalid-user@example.test',
            'phone_number' => '021123456',
            'postal_code' => '123',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ])->assertSessionHasErrors(['phone_number', 'postal_code', 'password']);

        $this->post(route('register.post'), [
            'name' => 'Rina Kartika',
            'email' => 'rina@example.test',
            'phone_number' => '+62 812-3456-7890',
            'city' => 'Bandung',
            'address' => 'Jl. Riau No. 12',
            'postal_code' => '40115',
            'password' => 'StrongPass123',
            'password_confirmation' => 'StrongPass123',
        ])->assertRedirect(route('customer.dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'rina@example.test',
            'role' => User::ROLE_CUSTOMER,
        ]);
        $this->assertDatabaseHas('customers', [
            'full_name' => 'Rina Kartika',
            'phone_number' => '081234567890',
            'postal_code' => '40115',
            'city' => 'Bandung',
        ]);

        auth()->logout();

        $this->post(route('register.post'), [
            'name' => 'Duplicate Email',
            'email' => 'rina@example.test',
            'phone_number' => '081234567891',
            'password' => 'StrongPass123',
            'password_confirmation' => 'StrongPass123',
        ])->assertSessionHasErrors(['email']);
    }

    public function test_login_redirects_by_role_and_logout_invalidates_session(): void
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER, 'cust@example.test');
        Customer::create([
            'user_id' => $customer->id,
            'full_name' => $customer->name,
            'phone_number' => '081234567890',
        ]);
        $admin = $this->createUser(User::ROLE_ADMIN, 'admin@example.test');
        $owner = $this->createUser(User::ROLE_OWNER, 'owner@example.test');

        $this->post(route('login.post'), [
            'email' => $customer->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors(['email']);
        $this->assertGuest();

        $this->post(route('login.post'), [
            'email' => $customer->email,
            'password' => 'password',
        ])->assertRedirect(route('customer.dashboard'));
        $this->assertAuthenticatedAs($customer);

        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();

        $this->post(route('login.post'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));
        $this->post(route('logout'))->assertRedirect(route('home'));

        $this->post(route('login.post'), [
            'email' => $owner->email,
            'password' => 'password',
        ])->assertRedirect(route('owner.dashboard'));
    }

    public function test_security_headers_disallow_unsafe_inline_scripts_and_render_external_module_only(): void
    {
        config(['app.debug' => false]);

        $response = $this->get(route('login'));
        $response->assertOk();

        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("script-src 'self' https://cdn.jsdelivr.net;", $csp);
        $this->assertStringNotContainsString("script-src 'self' 'unsafe-inline'", $csp);
    }

    public function test_role_based_access_control_enforces_route_boundaries(): void
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER);
        Customer::create([
            'user_id' => $customer->id,
            'full_name' => $customer->name,
            'phone_number' => '081234567890',
        ]);
        $admin = $this->createUser(User::ROLE_ADMIN);
        $owner = $this->createUser(User::ROLE_OWNER);

        // Guests must authenticate first.
        $this->get(route('customer.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('owner.dashboard'))->assertRedirect(route('login'));

        // Customer cannot reach admin or owner endpoints.
        $this->actingAs($customer)->get(route('customer.dashboard'))->assertOk();
        foreach ([
            route('admin.dashboard'),
            route('admin.orders.index'),
            route('admin.payments.index'),
            route('admin.cancellations.index'),
            route('owner.dashboard'),
            route('owner.po-periods.index'),
            route('owner.products.index'),
            route('owner.recap.index'),
            route('owner.customers.index'),
        ] as $forbiddenUrl) {
            $this->actingAs($customer)->get($forbiddenUrl)->assertForbidden();
        }

        // Admin can access admin panel, not customer or owner panels.
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        foreach ([
            route('customer.dashboard'),
            route('customer.cart'),
            route('owner.dashboard'),
            route('owner.po-periods.index'),
            route('owner.products.index'),
            route('owner.recap.index'),
            route('owner.customers.index'),
        ] as $forbiddenUrl) {
            $this->actingAs($admin)->get($forbiddenUrl)->assertForbidden();
        }

        // Owner can access owner and admin operational areas, not customer routes.
        $this->actingAs($owner)->get(route('owner.dashboard'))->assertOk();
        $this->actingAs($owner)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($owner)->get(route('customer.dashboard'))->assertForbidden();
        $this->actingAs($owner)->get(route('customer.cart'))->assertForbidden();
    }

    private function createUser(string $role, ?string $email = null): User
    {
        return User::create([
            'name' => 'Pengguna ' . $role,
            'email' => $email ?? ($role . '-' . Str::uuid() . '@example.test'),
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }

    private function createOpenPo(User $owner): PoPeriod
    {
        $period = PoPeriod::create([
            'name' => 'PO Uji Aktif',
            'start_date' => now('Asia/Jakarta')->toDateString(),
            'end_date' => now('Asia/Jakarta')->addDays(5)->toDateString(),
            'status' => PoPeriod::STATUS_OPEN,
            'created_by' => $owner->id,
        ]);
        PoPeriod::clearActiveCache();

        return $period;
    }

    private function createProduct(string $name, string $category, int $sizeMl, float $price, bool $active): Product
    {
        $product = Product::create([
            'name' => $name,
            'brand' => 'In Parfume',
            'category' => $category,
            'size_ml' => $sizeMl,
            'price' => $price,
            'is_active' => $active,
        ]);
        $product->variants()->create(['size_ml' => $sizeMl, 'price' => $price, 'cost_price' => null]);

        return $product;
    }
}
