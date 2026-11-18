<?php

namespace Tests\Feature;

use App\Models\AuditLog;
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
use Tests\TestCase;

class CustomerAndAdminFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_supports_add_accumulate_remove_and_rejects_empty_or_stale_checkout(): void
    {
        [$customerUser] = $this->createCustomer();
        $owner = $this->createUser(User::ROLE_OWNER);
        $this->createOpenPo($owner);
        $product = $this->createProduct(95000);

        $this->actingAs($customerUser)->post(route('customer.cart.add'), [
            'product_id' => $product->id,
            'size_ml' => 60,
            'quantity' => 2,
        ])->assertRedirect(route('customer.cart'));

        $this->actingAs($customerUser)->post(route('customer.cart.add'), [
            'product_id' => $product->id,
            'size_ml' => 60,
            'quantity' => 3,
        ])->assertRedirect(route('customer.cart'));

        $cartKey = $product->id . '_60';
        $this->assertSame(5, session("cart.{$cartKey}.quantity"));

        $this->actingAs($customerUser)->put(route('customer.cart.update', $cartKey), [
            'quantity' => 51,
        ])->assertSessionHasErrors(['quantity']);

        $this->actingAs($customerUser)->post(route('customer.cart.remove', $cartKey))
            ->assertRedirect(route('customer.cart'));
        $this->assertEmpty(session('cart'));

        // Empty cart checkout is rejected cleanly even with a valid UUID checkout_token.
        $token = (string) Str::uuid();
        $this->actingAs($customerUser)
            ->withSession(['checkout_token' => $token, 'cart' => []])
            ->post(route('customer.checkout.post'), [
                'checkout_token' => $token,
                'recipient_name' => 'Pelanggan Uji',
                'recipient_phone' => '081234567890',
                'shipping_address' => 'Alamat lengkap pelanggan',
            ])
            ->assertRedirect(route('customer.cart'))
            ->assertSessionHas('error');
    }

    public function test_payment_proof_access_and_customer_endpoints_enforce_idor_protection(): void
    {
        Storage::fake('local');

        [$ownerCustomerUser, $ownerCustomer] = $this->createCustomer();
        [$otherCustomerUser] = $this->createCustomer();
        $admin = $this->createUser(User::ROLE_ADMIN);
        $owner = $this->createUser(User::ROLE_OWNER);
        $period = $this->createOpenPo($owner);
        $order = $this->createOrder($ownerCustomer, $period, 95000);

        $proofPath = UploadedFile::fake()->create('proof.jpg', 120, 'image/jpeg')->store('payments', 'local');
        $refundPath = UploadedFile::fake()->create('refund.jpg', 120, 'image/jpeg')->store('refunds', 'local');

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'BCA Merchant',
            'amount' => 95000,
            'proof_file_path' => $proofPath,
            'uploaded_at' => now(),
            'verification_status' => Payment::STATUS_PENDING,
        ]);
        $attempt = PaymentAttempt::create([
            'payment_id' => $payment->id,
            'attempt_number' => 1,
            'amount' => 95000,
            'proof_file_path' => $proofPath,
            'uploaded_by' => $ownerCustomerUser->id,
            'uploaded_at' => now(),
            'verification_status' => PaymentAttempt::STATUS_PENDING,
        ]);
        $cancellation = CancellationRequest::create([
            'order_id' => $order->id,
            'reason' => 'Ingin membatalkan pesanan.',
            'status' => CancellationRequest::STATUS_APPROVED,
            'refund_proof_path' => $refundPath,
        ]);

        // Unrelated customer cannot access upload, cancellation, or private proof files.
        $this->actingAs($otherCustomerUser)->get(route('customer.orders.payment', $order->id))->assertForbidden();
        $this->actingAs($otherCustomerUser)->get(route('customer.orders.cancellation', $order->id))->assertForbidden();
        $this->actingAs($otherCustomerUser)->get(route('payments.proof', $payment->id))->assertForbidden();
        $this->actingAs($otherCustomerUser)->get(route('payments.attempt-proof', $attempt->id))->assertForbidden();
        $this->actingAs($otherCustomerUser)->get(route('cancellations.refund-proof', $cancellation->id))->assertForbidden();

        // Owner of the order and admin staff are authorized to view private proofs.
        $this->actingAs($ownerCustomerUser)->get(route('payments.proof', $payment->id))->assertOk();
        $this->actingAs($ownerCustomerUser)->get(route('payments.attempt-proof', $attempt->id))->assertOk();
        $this->actingAs($admin)->get(route('cancellations.refund-proof', $cancellation->id))->assertOk();
    }

    public function test_admin_payment_verification_requires_notes_on_reject_approves_valid_and_prevents_reverify(): void
    {
        Storage::fake('local');

        [$customerUser, $customer] = $this->createCustomer();
        $admin = $this->createUser(User::ROLE_ADMIN);
        $owner = $this->createUser(User::ROLE_OWNER);
        $period = $this->createOpenPo($owner);
        $order = $this->createOrder($customer, $period, 120000);
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'BCA Merchant',
            'amount' => 120000,
            'verification_status' => Payment::STATUS_PENDING,
        ]);

        // Upload valid proof via HTTP endpoint.
        $this->actingAs($customerUser)->post(route('customer.orders.payment.post', $order->id), [
            'amount' => 120000,
            'proof_file' => UploadedFile::fake()->create('transfer.jpg', 150, 'image/jpeg'),
        ])->assertRedirect(route('customer.orders.show', $order->id));

        $payment->refresh();

        // Rejection without notes fails validation.
        $this->actingAs($admin)->post(route('admin.payments.verify.post', $payment->id), [
            'verification_status' => Payment::STATUS_REJECTED,
        ])->assertSessionHasErrors(['admin_notes']);

        // Valid verification moves order status, creates history, and writes AuditLog.
        $this->actingAs($admin)->post(route('admin.payments.verify.post', $payment->id), [
            'verification_status' => Payment::STATUS_VALID,
            'admin_notes' => 'Mutasi BCA cocok.',
        ])->assertRedirect(route('admin.payments.index'));

        $this->assertSame(Order::STATUS_PAYMENT_VERIFIED, $order->fresh()->status);
        $this->assertSame(Payment::STATUS_VALID, $payment->fresh()->verification_status);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'previous_status' => Order::STATUS_WAITING_PAYMENT,
            'new_status' => Order::STATUS_PAYMENT_VERIFIED,
        ]);
        $this->assertSame(
            1,
            AuditLog::where('event', 'payment.verified')
                ->where('subject_type', Payment::class)
                ->where('subject_id', $payment->id)
                ->count()
        );

        // Second verification decision on already-verified payment is rejected cleanly.
        $this->actingAs($admin)->post(route('admin.payments.verify.post', $payment->id), [
            'verification_status' => Payment::STATUS_REJECTED,
            'admin_notes' => 'Mencoba ubah keputusan.',
        ])->assertRedirect()->assertSessionHas('error');
    }

    public function test_admin_order_fulfillment_enforces_valid_transitions_and_completes(): void
    {
        [, $customer] = $this->createCustomer();
        $admin = $this->createUser(User::ROLE_ADMIN);
        $owner = $this->createUser(User::ROLE_OWNER);
        $order = $this->createOrder($customer, $this->createOpenPo($owner), 100000);
        $order->update(['status' => Order::STATUS_PAYMENT_VERIFIED]);

        // Jumping directly to Selesai is blocked.
        $this->actingAs($admin)->post(route('admin.orders.status.update', $order->id), [
            'new_status' => Order::STATUS_COMPLETED,
        ])->assertRedirect()->assertSessionHas('error');

        $this->actingAs($admin)->post(route('admin.orders.status.update', $order->id), [
            'new_status' => Order::STATUS_PROCESSING,
            'remarks' => 'Sedang dibeli dari gerai.',
        ])->assertRedirect(route('admin.orders.show', $order->id));

        $this->actingAs($admin)->post(route('admin.orders.status.update', $order->id), [
            'new_status' => Order::STATUS_SHIPPED,
            'courier' => 'SiCepat',
            'tracking_number' => 'SICEPAT-9988',
            'shipping_cost' => 15000,
        ])->assertRedirect(route('admin.orders.show', $order->id));

        $this->actingAs($admin)->post(route('admin.orders.status.update', $order->id), [
            'new_status' => Order::STATUS_COMPLETED,
        ])->assertRedirect(route('admin.orders.show', $order->id));

        $this->assertSame(Order::STATUS_COMPLETED, $order->fresh()->status);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'new_status' => Order::STATUS_COMPLETED,
        ]);
    }

    public function test_admin_can_reject_or_approve_unpaid_cancellation_with_audit_trail(): void
    {
        [$customerUser, $customer] = $this->createCustomer();
        $admin = $this->createUser(User::ROLE_ADMIN);
        $owner = $this->createUser(User::ROLE_OWNER);
        $period = $this->createOpenPo($owner);

        // 1. Customer submits cancellation and Admin rejects it.
        $orderA = $this->createOrder($customer, $period, 90000);
        $this->actingAs($customerUser)->post(route('customer.orders.cancellation.post', $orderA->id), [
            'reason' => 'Berubah pikiran mengenai varian.',
        ])->assertRedirect(route('customer.orders.show', $orderA->id));

        $cancelA = CancellationRequest::where('order_id', $orderA->id)->firstOrFail();

        $this->actingAs($admin)->post(route('admin.cancellations.decide.post', $cancelA->id), [
            'status' => CancellationRequest::STATUS_REJECTED,
        ])->assertSessionHasErrors(['admin_notes']);

        $this->actingAs($admin)->post(route('admin.cancellations.decide.post', $cancelA->id), [
            'status' => CancellationRequest::STATUS_REJECTED,
            'admin_notes' => 'Pesanan tetap diproses sesuai kesepakatan.',
        ])->assertRedirect(route('admin.cancellations.index'));

        $this->assertSame(Order::STATUS_WAITING_PAYMENT, $orderA->fresh()->status);
        $this->assertSame(CancellationRequest::STATUS_REJECTED, $cancelA->fresh()->status);

        // 2. Unpaid order cancellation approved directly without refund proof.
        $orderB = $this->createOrder($customer, $period, 90000);
        $cancelB = CancellationRequest::create([
            'order_id' => $orderB->id,
            'reason' => 'Salah pilih ukuran botol.',
            'status' => CancellationRequest::STATUS_PENDING,
        ]);

        $this->actingAs($admin)->post(route('admin.cancellations.decide.post', $cancelB->id), [
            'status' => CancellationRequest::STATUS_APPROVED,
            'admin_notes' => 'Disetujui sebelum pembayaran masuk.',
        ])->assertRedirect(route('admin.cancellations.index'));

        $this->assertSame(Order::STATUS_CANCELLED, $orderB->fresh()->status);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'cancellation.decided',
            'subject_type' => CancellationRequest::class,
            'subject_id' => $cancelB->id,
        ]);
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
            'name' => 'Pengguna ' . $role,
            'email' => $role . '-' . Str::uuid() . '@example.test',
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }

    private function createOpenPo(User $owner): PoPeriod
    {
        $period = PoPeriod::create([
            'name' => 'PO Uji',
            'start_date' => now('Asia/Jakarta')->toDateString(),
            'end_date' => now('Asia/Jakarta')->addDays(7)->toDateString(),
            'status' => PoPeriod::STATUS_OPEN,
            'created_by' => $owner->id,
        ]);
        PoPeriod::clearActiveCache();

        return $period;
    }

    private function createProduct(float $price): Product
    {
        $product = Product::create([
            'name' => 'Parfum Uji',
            'brand' => 'In Parfume',
            'category' => 'Floral',
            'size_ml' => 60,
            'price' => $price,
            'is_active' => true,
        ]);
        $product->variants()->create(['size_ml' => 60, 'price' => $price, 'cost_price' => null]);

        return $product;
    }

    private function createOrder(Customer $customer, PoPeriod $period, float $totalAmount): Order
    {
        return Order::create([
            'order_number' => 'UJ-TEST-' . Str::upper(Str::random(8)),
            'customer_id' => $customer->id,
            'po_period_id' => $period->id,
            'total_amount' => $totalAmount,
            'status' => Order::STATUS_WAITING_PAYMENT,
            'recipient_name' => 'Pelanggan Uji',
            'recipient_phone' => '081234567890',
            'shipping_address' => 'Alamat uji lengkap',
        ]);
    }
}

