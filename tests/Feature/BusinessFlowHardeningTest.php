<?php

namespace Tests\Feature;

use App\Models\CancellationRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Models\PoPeriod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash as HashFacade;
use Tests\TestCase;

class BusinessFlowHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_retry_with_the_same_token_returns_one_order(): void
    {
        [$customerUser, $customer] = $this->createCustomer();
        $owner = $this->createUser(User::ROLE_OWNER);
        $this->createPo($owner);
        $product = $this->createProduct();
        $token = (string) Str::uuid();
        $cart = [
            $product->id . '_60' => [
                'product_id' => $product->id,
                'size_ml' => 60,
                'quantity' => 1,
            ],
        ];
        $payload = [
            'checkout_token' => $token,
            'recipient_name' => 'Pelanggan Uji',
            'recipient_phone' => '081234567890',
            'shipping_address' => 'Alamat uji lengkap',
        ];

        $this->actingAs($customerUser)
            ->withSession(['cart' => $cart, 'checkout_token' => $token])
            ->post(route('customer.checkout.post'), $payload)
            ->assertRedirect();

        $order = Order::query()->where('checkout_token', $token)->firstOrFail();

        $this->actingAs($customerUser)
            ->withSession(['cart' => $cart, 'checkout_token' => $token])
            ->post(route('customer.checkout.post'), $payload)
            ->assertRedirect(route('customer.orders.show', $order->id))
            ->assertSessionHas('info');

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'customer_id' => $customer->id,
            'checkout_token' => $token,
        ]);
        $this->assertNotNull($order->fresh()->payment_deadline_at);
    }

    public function test_checkout_uses_the_explicit_selected_variant_price_and_cost_snapshot(): void
    {
        [$customerUser] = $this->createCustomer();
        $owner = $this->createUser(User::ROLE_OWNER);
        $this->createPo($owner);
        $product = $this->createProduct();
        $product->variants()->create(['size_ml' => 35, 'price' => 41000, 'cost_price' => 22000]);
        $token = (string) Str::uuid();

        $this->actingAs($customerUser)
            ->withSession(['cart' => [$product->id . '_35' => ['product_id' => $product->id, 'size_ml' => 35, 'quantity' => 2]], 'checkout_token' => $token])
            ->post(route('customer.checkout.post'), [
                'checkout_token' => $token,
                'recipient_name' => 'Pelanggan Uji',
                'recipient_phone' => '+62 812-3456-7890',
                'shipping_address' => 'Alamat uji lengkap',
                'shipping_postal_code' => '40123',
            ])
            ->assertRedirect();

        $detail = \App\Models\OrderDetail::query()->firstOrFail();
        $this->assertSame('41000.00', $detail->unit_price);
        $this->assertSame('22000.00', $detail->cost_price);
        $this->assertDatabaseHas('orders', ['shipping_postal_code' => '40123', 'total_amount' => 82000]);
    }

    public function test_checkout_is_rejected_outside_the_po_date_window(): void
    {
        [$customerUser] = $this->createCustomer();
        $owner = $this->createUser(User::ROLE_OWNER);
        $this->createPo($owner, startDate: now('Asia/Jakarta')->addDay()->toDateString());
        $product = $this->createProduct();
        $token = (string) Str::uuid();

        $this->actingAs($customerUser)
            ->withSession([
                'cart' => [$product->id . '_60' => ['product_id' => $product->id, 'size_ml' => 60, 'quantity' => 1]],
                'checkout_token' => $token,
            ])
            ->post(route('customer.checkout.post'), [
                'checkout_token' => $token,
                'recipient_name' => 'Pelanggan Uji',
                'recipient_phone' => '081234567890',
                'shipping_address' => 'Alamat uji lengkap',
            ])
            ->assertRedirect(route('customer.cart'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_admin_cannot_approve_cancellation_while_payment_proof_is_pending(): void
    {
        [, $customer] = $this->createCustomer();
        $admin = $this->createUser(User::ROLE_ADMIN);
        $owner = $this->createUser(User::ROLE_OWNER);
        $period = $this->createPo($owner);
        $order = $this->createOrder($customer, $period);
        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'BCA Merchant',
            'amount' => $order->total_amount,
            'proof_file_path' => 'payments/pending-proof.jpg',
            'uploaded_at' => now(),
            'verification_status' => Payment::STATUS_PENDING,
        ]);
        $cancellation = CancellationRequest::create([
            'order_id' => $order->id,
            'reason' => 'Pelanggan meminta pembatalan pesanan.',
            'status' => CancellationRequest::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.cancellations.decide.post', $cancellation->id), [
                'status' => CancellationRequest::STATUS_APPROVED,
            ])
            ->assertRedirect(route('admin.cancellations.show', $cancellation->id))
            ->assertSessionHas('warning');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_WAITING_PAYMENT,
        ]);
        $this->assertDatabaseHas('cancellation_requests', [
            'id' => $cancellation->id,
            'status' => CancellationRequest::STATUS_PENDING,
        ]);
        $this->assertDatabaseCount('payment_attempts', 0);
    }

    public function test_payment_reuploads_preserve_previous_proof_attempts(): void
    {
        Storage::fake('local');
        [$customerUser, $customer] = $this->createCustomer();
        $owner = $this->createUser(User::ROLE_OWNER);
        $period = $this->createPo($owner);
        $order = $this->createOrder($customer, $period);
        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'BCA Merchant',
            'amount' => $order->total_amount,
            'verification_status' => Payment::STATUS_PENDING,
        ]);

        foreach ([1, 2] as $attemptNumber) {
            $this->actingAs($customerUser)
                ->post(route('customer.orders.payment.post', $order->id), [
                    'amount' => $order->total_amount,
                    'proof_file' => UploadedFile::fake()->create("proof-{$attemptNumber}.jpg", 100, 'image/jpeg'),
                ])
                ->assertRedirect(route('customer.orders.show', $order->id));
        }

        $attempts = PaymentAttempt::query()->orderBy('attempt_number')->get();
        $this->assertCount(2, $attempts);
        $this->assertSame(PaymentAttempt::STATUS_SUPERSEDED, $attempts[0]->verification_status);
        $this->assertSame(PaymentAttempt::STATUS_PENDING, $attempts[1]->verification_status);
        Storage::disk('local')->assertExists($attempts[0]->proof_file_path);
        Storage::disk('local')->assertExists($attempts[1]->proof_file_path);

        $admin = $this->createUser(User::ROLE_ADMIN);
        $this->actingAs($admin)
            ->post(route('admin.payments.verify.post', $order->payment->id), [
                'verification_status' => Payment::STATUS_REJECTED,
                'admin_notes' => 'Nama pengirim tidak cocok dengan mutasi bank.',
            ])
            ->assertRedirect(route('admin.payments.index'));

        $this->assertDatabaseHas('payment_attempts', [
            'id' => $attempts[1]->id,
            'verification_status' => PaymentAttempt::STATUS_REJECTED,
            'verified_by' => $admin->id,
            'admin_notes' => 'Nama pengirim tidak cocok dengan mutasi bank.',
        ]);
    }

    public function test_expired_order_cannot_receive_a_new_proof_even_if_an_older_proof_is_pending(): void
    {
        [$customerUser, $customer] = $this->createCustomer();
        $owner = $this->createUser(User::ROLE_OWNER);
        $period = $this->createPo($owner);
        $order = $this->createOrder($customer, $period);
        $order->update(['payment_deadline_at' => now()->subMinute()]);

        Storage::fake('local');
        Storage::disk('local')->put('payments/existing-proof.jpg', 'previous proof');
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'BCA Merchant',
            'amount' => $order->total_amount,
            'proof_file_path' => 'payments/existing-proof.jpg',
            'uploaded_at' => now()->subHour(),
            'verification_status' => Payment::STATUS_PENDING,
        ]);
        PaymentAttempt::create([
            'payment_id' => $payment->id,
            'attempt_number' => 1,
            'amount' => $order->total_amount,
            'proof_file_path' => 'payments/existing-proof.jpg',
            'uploaded_at' => now()->subHour(),
            'verification_status' => PaymentAttempt::STATUS_PENDING,
        ]);

        $this->actingAs($customerUser)
            ->post(route('customer.orders.payment.post', $order->id), [
                'amount' => $order->total_amount,
                'proof_file' => UploadedFile::fake()->create('replacement.jpg', 100, 'image/jpeg'),
            ])
            ->assertRedirect(route('customer.orders.show', $order->id))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('payment_attempts', 1);
        Storage::disk('local')->assertExists('payments/existing-proof.jpg');
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'proof_file_path' => 'payments/existing-proof.jpg',
        ]);
    }

    public function test_owner_revenue_and_procurement_recap_only_include_verified_payments(): void
    {
        $owner = $this->createUser(User::ROLE_OWNER);
        [, $customer] = $this->createCustomer();
        $period = $this->createPo($owner);

        $verifiedOrder = $this->createOrder($customer, $period);
        $verifiedOrder->update(['status' => Order::STATUS_PAYMENT_VERIFIED]);
        Payment::create([
            'order_id' => $verifiedOrder->id,
            'payment_method' => 'BCA Merchant',
            'amount' => $verifiedOrder->total_amount,
            'verification_status' => Payment::STATUS_VALID,
        ]);

        $unpaidOrder = $this->createOrder($customer, $period);
        $unpaidOrder->update(['total_amount' => 250000]);
        Payment::create([
            'order_id' => $unpaidOrder->id,
            'payment_method' => 'BCA Merchant',
            'amount' => $unpaidOrder->total_amount,
            'verification_status' => Payment::STATUS_PENDING,
        ]);

        $this->actingAs($owner)
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertViewHas('totalRevenue', fn ($amount) => (float) $amount === 100000.0);

        $this->actingAs($owner)
            ->get(route('owner.recap.index', ['po_period_id' => $period->id]))
            ->assertOk()
            ->assertViewHas('summary', fn (array $summary) => $summary['total_revenue'] == 100000
                && $summary['verified_orders'] === 1
                && $summary['unpaid_orders'] === 1);
    }

    public function test_expired_unpaid_orders_are_cancelled_but_pending_proof_is_preserved(): void
    {
        $owner = $this->createUser(User::ROLE_OWNER);
        [, $customer] = $this->createCustomer();
        $period = $this->createPo($owner);
        $expired = $this->createOrder($customer, $period);
        $expired->update(['payment_deadline_at' => now()->subMinute()]);
        Payment::create(['order_id' => $expired->id, 'payment_method' => 'BCA Merchant', 'amount' => $expired->total_amount, 'verification_status' => Payment::STATUS_PENDING]);

        $pendingProof = $this->createOrder($customer, $period);
        $pendingProof->update(['payment_deadline_at' => now()->subMinute()]);
        Payment::create(['order_id' => $pendingProof->id, 'payment_method' => 'BCA Merchant', 'amount' => $pendingProof->total_amount, 'proof_file_path' => 'payments/waiting.jpg', 'verification_status' => Payment::STATUS_PENDING]);

        $this->artisan('orders:cancel-expired')->assertExitCode(0);
        $this->assertDatabaseHas('orders', ['id' => $expired->id, 'status' => Order::STATUS_CANCELLED]);
        $this->assertDatabaseHas('orders', ['id' => $pendingProof->id, 'status' => Order::STATUS_WAITING_PAYMENT]);
        $this->assertDatabaseHas('order_status_histories', ['order_id' => $expired->id, 'new_status' => Order::STATUS_CANCELLED]);
    }

    public function test_admin_must_record_and_upload_a_full_refund_before_approving_paid_cancellation(): void
    {
        Storage::fake('local');
        [, $customer] = $this->createCustomer();
        $admin = $this->createUser(User::ROLE_ADMIN);
        $owner = $this->createUser(User::ROLE_OWNER);
        $period = $this->createPo($owner);
        $order = $this->createOrder($customer, $period);
        $order->update(['status' => Order::STATUS_PAYMENT_VERIFIED]);
        $payment = Payment::create(['order_id' => $order->id, 'payment_method' => 'BCA Merchant', 'amount' => $order->total_amount, 'verification_status' => Payment::STATUS_VALID]);
        $cancellation = CancellationRequest::create(['order_id' => $order->id, 'reason' => 'Pembeli meminta pengembalian dana.', 'status' => CancellationRequest::STATUS_PENDING]);

        $this->actingAs($admin)->post(route('admin.cancellations.decide.post', $cancellation->id), [
            'status' => CancellationRequest::STATUS_APPROVED,
        ])->assertSessionHasErrors(['refund_amount', 'refund_reference', 'refund_proof']);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => Order::STATUS_PAYMENT_VERIFIED]);

        $this->actingAs($admin)->post(route('admin.cancellations.decide.post', $cancellation->id), [
            'status' => CancellationRequest::STATUS_APPROVED,
            'refund_amount' => $payment->amount,
            'refund_reference' => 'BCA-REF-2026-001',
            'refund_proof' => UploadedFile::fake()->create('refund.jpg', 100, 'image/jpeg'),
            'admin_notes' => 'Refund manual sesuai mutasi bank.',
        ])->assertRedirect(route('admin.cancellations.index'));

        $cancellation->refresh();
        $this->assertSame(CancellationRequest::STATUS_APPROVED, $cancellation->status);
        $this->assertSame('BCA-REF-2026-001', $cancellation->refund_reference);
        $this->assertNotNull($cancellation->refunded_at);
        Storage::disk('local')->assertExists($cancellation->refund_proof_path);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => Order::STATUS_CANCELLED]);
    }

    public function test_forgot_password_flow_sends_a_reset_notification_and_updates_the_hash(): void
    {
        Notification::fake();
        $user = $this->createUser(User::ROLE_CUSTOMER);
        $this->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);

        $token = \Illuminate\Support\Facades\Password::broker()->createToken($user);
        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'A-strong-new-password-123',
            'password_confirmation' => 'A-strong-new-password-123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(HashFacade::check('A-strong-new-password-123', $user->fresh()->password));
    }

    public function test_register_is_throttled_and_security_headers_are_added(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('register.post'), [])->assertRedirect();
        }
        $this->post(route('register.post'), [])->assertStatus(429);

        auth()->logout();
        $response = $this->get('/');
        $response->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff')->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Content-Security-Policy');
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        [, $ownerCustomer] = $this->createCustomer();
        [$otherCustomerUser] = $this->createCustomer();
        $owner = $this->createUser(User::ROLE_OWNER);
        $order = $this->createOrder($ownerCustomer, $this->createPo($owner));

        $this->actingAs($otherCustomerUser)
            ->get(route('customer.orders.show', $order->id))
            ->assertForbidden();
    }

    public function test_admin_must_record_shipping_data_when_marking_order_shipped(): void
    {
        [, $customer] = $this->createCustomer();
        $admin = $this->createUser(User::ROLE_ADMIN);
        $owner = $this->createUser(User::ROLE_OWNER);
        $order = $this->createOrder($customer, $this->createPo($owner));
        $order->update(['status' => Order::STATUS_PROCESSING]);

        $this->actingAs($admin)->post(route('admin.orders.status.update', $order->id), [
            'new_status' => Order::STATUS_SHIPPED,
        ])->assertSessionHasErrors(['courier', 'tracking_number']);

        $this->actingAs($admin)->post(route('admin.orders.status.update', $order->id), [
            'new_status' => Order::STATUS_SHIPPED,
            'courier' => 'JNE',
            'tracking_number' => 'JNE-TEST-001',
            'shipping_cost' => 18000,
        ])->assertRedirect(route('admin.orders.show', $order->id));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_SHIPPED,
            'courier' => 'JNE',
            'tracking_number' => 'JNE-TEST-001',
            'shipping_cost' => 18000,
        ]);
    }

    public function test_catalog_filters_on_available_variants_and_cart_quantity_can_be_updated(): void
    {
        [$customerUser] = $this->createCustomer();
        $owner = $this->createUser(User::ROLE_OWNER);
        $this->createPo($owner);
        $product = $this->createProduct();
        $product->variants()->create(['size_ml' => 35, 'price' => 41000, 'cost_price' => null]);

        $this->get(route('catalog', ['size_ml' => 35]))->assertOk()->assertSee($product->name);
        $key = $product->id . '_60';
        $this->actingAs($customerUser)
            ->withSession(['cart' => [$key => ['product_id' => $product->id, 'size_ml' => 60, 'quantity' => 1]]])
            ->put(route('customer.cart.update', $key), ['quantity' => 4])
            ->assertRedirect(route('customer.cart'))
            ->assertSessionHas('cart.' . $key . '.quantity', 4);
    }

    public function test_customer_can_add_an_available_product_variant_to_the_cart(): void
    {
        [$customerUser] = $this->createCustomer();
        $owner = $this->createUser(User::ROLE_OWNER);
        $this->createPo($owner);
        $product = $this->createProduct();

        $this->actingAs($customerUser)
            ->post(route('customer.cart.add'), [
                'product_id' => $product->id,
                'size_ml' => 60,
                'quantity' => 1,
            ])
            ->assertRedirect(route('customer.cart'))
            ->assertSessionHas('cart.' . $product->id . '_60.quantity', 1);
    }

    public function test_owner_can_correct_a_po_period_without_ordered_date_changes(): void
    {
        $owner = $this->createUser(User::ROLE_OWNER);
        $period = $this->createPo($owner);

        $this->actingAs($owner)->put(route('owner.po-periods.update', $period->id), [
            'name' => 'PO Uji Diperbarui',
            'start_date' => now('Asia/Jakarta')->toDateString(),
            'end_date' => now('Asia/Jakarta')->addDays(10)->toDateString(),
            'notes' => 'Catatan yang diperbaiki.',
        ])->assertRedirect(route('owner.po-periods.index'));

        $this->assertDatabaseHas('po_periods', ['id' => $period->id, 'name' => 'PO Uji Diperbarui', 'notes' => 'Catatan yang diperbaiki.']);
    }

    private function createCustomer(): array
    {
        $user = $this->createUser(User::ROLE_CUSTOMER);
        $customer = Customer::create([
            'user_id' => $user->id,
            'full_name' => 'Pelanggan Uji',
            'phone_number' => '081234567890',
            'default_address' => 'Alamat uji',
            'city' => 'Bandung',
        ]);

        return [$user, $customer];
    }

    private function createUser(string $role): User
    {
        return User::create([
            'name' => 'Pengguna Uji ' . $role,
            'email' => $role . '-' . Str::uuid() . '@example.test',
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }

    private function createPo(User $owner, ?string $startDate = null): PoPeriod
    {
        $today = now('Asia/Jakarta')->toDateString();
        $period = PoPeriod::create([
            'name' => 'PO Uji',
            'start_date' => $startDate ?? $today,
            'end_date' => now('Asia/Jakarta')->addDays(7)->toDateString(),
            'status' => PoPeriod::STATUS_OPEN,
            'created_by' => $owner->id,
        ]);
        PoPeriod::clearActiveCache();
        return $period;
    }

    private function createProduct(): Product
    {
        $product = Product::create([
            'name' => 'Parfum Uji',
            'brand' => 'In Parfume',
            'category' => 'Floral',
            'size_ml' => 60,
            'price' => 95000,
            'is_active' => true,
        ]);
        $product->variants()->create(['size_ml' => 60, 'price' => 95000, 'cost_price' => null]);
        return $product;
    }

    private function createOrder(Customer $customer, PoPeriod $period): Order
    {
        return Order::create([
            'order_number' => 'UJ-PO' . $period->id . '-' . now()->format('YmdHis') . '-' . Str::random(5),
            'customer_id' => $customer->id,
            'po_period_id' => $period->id,
            'total_amount' => 100000,
            'status' => Order::STATUS_WAITING_PAYMENT,
            'recipient_name' => 'Pelanggan Uji',
            'recipient_phone' => '081234567890',
            'shipping_address' => 'Alamat uji lengkap',
        ]);
    }
}
