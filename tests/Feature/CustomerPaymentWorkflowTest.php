<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Models\PoPeriod;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerPaymentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function createCustomerUser(string $name = 'Pelanggan Uji'): array
    {
        $user = User::create([
            'name' => $name,
            'email' => 'customer-' . Str::uuid() . '@example.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $customer = Customer::create([
            'user_id' => $user->id,
            'full_name' => $name,
            'phone_number' => '081234567890',
            'default_address' => 'Jl. Pengujian No. 10',
            'city' => 'Bandung',
            'postal_code' => '40115',
        ]);

        return [$user, $customer];
    }

    private function createAdminUser(): User
    {
        return User::create([
            'name' => 'Admin Operasional',
            'email' => 'admin-' . Str::uuid() . '@example.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
        ]);
    }

    private function createOwnerUser(): User
    {
        return User::create([
            'name' => 'Pemilik Jastip',
            'email' => 'owner-' . Str::uuid() . '@example.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_OWNER,
        ]);
    }

    private function createOpenPoPeriod(User $owner): PoPeriod
    {
        $period = PoPeriod::create([
            'name' => 'PO Batch Testing Aktif',
            'start_date' => now('Asia/Jakarta')->subDays(2)->toDateString(),
            'end_date' => now('Asia/Jakarta')->addDays(5)->toDateString(),
            'status' => PoPeriod::STATUS_OPEN,
            'notes' => 'Periode pengujian aktif.',
            'created_by' => $owner->id,
        ]);
        PoPeriod::clearActiveCache();

        return $period;
    }

    private function createOrderWithPayment(Customer $customer, PoPeriod $period, float $totalAmount = 150000, ?Carbon $deadline = null): array
    {
        $order = Order::create([
            'order_number' => 'UJ-PO' . $period->id . '-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
            'customer_id' => $customer->id,
            'po_period_id' => $period->id,
            'total_amount' => $totalAmount,
            'payment_deadline_at' => $deadline ?? now()->addDay(),
            'status' => Order::STATUS_WAITING_PAYMENT,
            'recipient_name' => $customer->full_name,
            'recipient_phone' => $customer->phone_number,
            'shipping_address' => $customer->default_address,
            'shipping_postal_code' => $customer->postal_code,
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'BCA Merchant',
            'amount' => $totalAmount,
            'verification_status' => Payment::STATUS_PENDING,
        ]);

        return [$order, $payment];
    }

    /**
     * PAY-01: Customer opens valid payment page.
     * Expected: HTTP 200, shows bank account info, total amount, deadline, and upload form.
     */
    public function test_pay_01_customer_opens_valid_payment_page(): void
    {
        [$user, $customer] = $this->createCustomerUser();
        $owner = $this->createOwnerUser();
        $period = $this->createOpenPoPeriod($owner);
        [$order, $payment] = $this->createOrderWithPayment($customer, $period, 175000);

        $response = $this->actingAs($user)->get(route('customer.orders.payment', $order->id));

        $response->assertOk();
        $response->assertSee('Instruksi transfer');
        $response->assertSee(config('jastip.bca_account'));
        $response->assertSee(config('jastip.bca_name'));
        $response->assertSee(number_format(175000, 0, ',', '.'));
        $response->assertSee('Batas pembayaran');
        $response->assertSee('Kirim bukti pembayaran');
        $response->assertSee('name="proof_file"', false);
    }

    /**
     * PAY-02: Customer opens expired payment page.
     * Expected: Redirected with clear expired error message explaining why upload is blocked.
     */
    public function test_pay_02_customer_opens_expired_payment(): void
    {
        [$user, $customer] = $this->createCustomerUser();
        $owner = $this->createOwnerUser();
        $period = $this->createOpenPoPeriod($owner);
        // Set deadline in the past
        [$order, $payment] = $this->createOrderWithPayment($customer, $period, 120000, now()->subHours(2));

        $response = $this->actingAs($user)->get(route('customer.orders.payment', $order->id));

        $response->assertRedirect(route('customer.orders.show', $order->id));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Batas pembayaran telah lewat', session('error'));
    }

    /**
     * PAY-03: Customer uploads valid image proof (JPEG/PNG).
     * Expected: File accepted, stored in private disk, payment attempt recorded with attempt_number 1.
     */
    public function test_pay_03_customer_uploads_valid_image_proof(): void
    {
        Storage::fake('local');
        [$user, $customer] = $this->createCustomerUser();
        $owner = $this->createOwnerUser();
        $period = $this->createOpenPoPeriod($owner);
        [$order, $payment] = $this->createOrderWithPayment($customer, $period, 120000);

        $file = UploadedFile::fake()->create('bukti_transfer.jpg', 300, 'image/jpeg');

        $response = $this->actingAs($user)->post(route('customer.orders.payment.post', $order->id), [
            'amount' => 120000,
            'proof_file' => $file,
        ]);

        $response->assertRedirect(route('customer.orders.show', $order->id));
        $response->assertSessionHas('success');

        $payment->refresh();
        $this->assertNotNull($payment->proof_file_path);
        Storage::disk('local')->assertExists($payment->proof_file_path);

        $this->assertDatabaseHas('payment_attempts', [
            'payment_id' => $payment->id,
            'attempt_number' => 1,
            'verification_status' => PaymentAttempt::STATUS_PENDING,
            'proof_file_path' => $payment->proof_file_path,
        ]);
    }

    /**
     * PAY-04: Customer uploads valid PDF proof.
     * Expected: Accepted and stored successfully in private disk.
     */
    public function test_pay_04_customer_uploads_valid_pdf_proof(): void
    {
        Storage::fake('local');
        [$user, $customer] = $this->createCustomerUser();
        $owner = $this->createOwnerUser();
        $period = $this->createOpenPoPeriod($owner);
        [$order, $payment] = $this->createOrderWithPayment($customer, $period, 240000);

        $file = UploadedFile::fake()->create('rekening_koran.pdf', 500, 'application/pdf');

        $response = $this->actingAs($user)->post(route('customer.orders.payment.post', $order->id), [
            'amount' => 240000,
            'proof_file' => $file,
        ]);

        $response->assertRedirect(route('customer.orders.show', $order->id));
        $response->assertSessionHas('success');

        $payment->refresh();
        $this->assertNotNull($payment->proof_file_path);
        Storage::disk('local')->assertExists($payment->proof_file_path);
        $this->assertStringEndsWith('.pdf', $payment->proof_file_path);
    }

    /**
     * PAY-05: Customer uploads invalid MIME type.
     * Expected: Validation fails, redirected back with format error.
     */
    public function test_pay_05_customer_uploads_invalid_mime(): void
    {
        Storage::fake('local');
        [$user, $customer] = $this->createCustomerUser();
        $owner = $this->createOwnerUser();
        $period = $this->createOpenPoPeriod($owner);
        [$order, $payment] = $this->createOrderWithPayment($customer, $period, 95000);

        $file = UploadedFile::fake()->create('payload.exe', 100, 'application/x-msdownload');

        $response = $this->actingAs($user)->post(route('customer.orders.payment.post', $order->id), [
            'amount' => 95000,
            'proof_file' => $file,
        ]);

        $response->assertSessionHasErrors(['proof_file']);
        $payment->refresh();
        $this->assertNull($payment->proof_file_path);
    }

    /**
     * PAY-06: Customer uploads oversized file (> 2MB).
     * Expected: Validation fails, rejected with max size error.
     */
    public function test_pay_06_customer_uploads_oversized_file(): void
    {
        Storage::fake('local');
        [$user, $customer] = $this->createCustomerUser();
        $owner = $this->createOwnerUser();
        $period = $this->createOpenPoPeriod($owner);
        [$order, $payment] = $this->createOrderWithPayment($customer, $period, 95000);

        // 3000 KB exceeds 2048 KB limit
        $file = UploadedFile::fake()->create('huge_receipt.jpg', 3000, 'image/jpeg');

        $response = $this->actingAs($user)->post(route('customer.orders.payment.post', $order->id), [
            'amount' => 95000,
            'proof_file' => $file,
        ]);

        $response->assertSessionHasErrors(['proof_file']);
        $payment->refresh();
        $this->assertNull($payment->proof_file_path);
    }

    /**
     * PAY-07: Customer submits incorrect amount.
     * Expected: Rejected with amount mismatch error.
     */
    public function test_pay_07_customer_submits_incorrect_amount(): void
    {
        Storage::fake('local');
        [$user, $customer] = $this->createCustomerUser();
        $owner = $this->createOwnerUser();
        $period = $this->createOpenPoPeriod($owner);
        [$order, $payment] = $this->createOrderWithPayment($customer, $period, 120000);

        $file = UploadedFile::fake()->create('bukti.jpg', 200, 'image/jpeg');

        $response = $this->actingAs($user)->post(route('customer.orders.payment.post', $order->id), [
            'amount' => 100000, // Does not match 120000
            'proof_file' => $file,
        ]);

        $response->assertSessionHasErrors(['amount']);
        $payment->refresh();
        $this->assertNull($payment->proof_file_path);
    }

    /**
     * PAY-08: Customer tampers with amount payload.
     * Expected: Server-side validation rejects manipulation, total remains authoritative from order.
     */
    public function test_pay_08_customer_tampers_with_amount_server_side_authoritative(): void
    {
        Storage::fake('local');
        [$user, $customer] = $this->createCustomerUser();
        $owner = $this->createOwnerUser();
        $period = $this->createOpenPoPeriod($owner);
        [$order, $payment] = $this->createOrderWithPayment($customer, $period, 150000);

        $file = UploadedFile::fake()->create('bukti.png', 200, 'image/png');

        // Attacker modifies POST body to pay Rp 1.000 instead of Rp 150.000
        $response = $this->actingAs($user)->post(route('customer.orders.payment.post', $order->id), [
            'amount' => 1000.00,
            'proof_file' => $file,
        ]);

        $response->assertSessionHasErrors(['amount']);

        // Order total unchanged
        $order->refresh();
        $this->assertEquals(150000, (float) $order->total_amount);

        // Payment amount unchanged
        $payment->refresh();
        $this->assertEquals(150000, (float) $payment->amount);
        $this->assertNull($payment->proof_file_path);
    }

    /**
     * PAY-09: Customer reuploads rejected proof.
     * Expected: Attempt #1 retained as rejected, Attempt #2 created as pending, audit history preserved.
     */
    public function test_pay_09_customer_reuploads_rejected_proof(): void
    {
        Storage::fake('local');
        [$user, $customer] = $this->createCustomerUser();
        $owner = $this->createOwnerUser();
        $period = $this->createOpenPoPeriod($owner);
        [$order, $payment] = $this->createOrderWithPayment($customer, $period, 180000);

        // Upload #1
        $file1 = UploadedFile::fake()->create('proof1.jpg', 200, 'image/jpeg');
        $this->actingAs($user)->post(route('customer.orders.payment.post', $order->id), [
            'amount' => 180000,
            'proof_file' => $file1,
        ]);

        // Admin rejects attempt #1
        $payment->refresh();
        $attempt1 = PaymentAttempt::where('payment_id', $payment->id)->first();
        $attempt1->update([
            'verification_status' => PaymentAttempt::STATUS_REJECTED,
            'admin_notes' => 'Foto bukti buram dan tidak terbaca nomor referensinya.',
        ]);
        $payment->update([
            'verification_status' => Payment::STATUS_REJECTED,
            'admin_notes' => 'Foto bukti buram dan tidak terbaca nomor referensinya.',
        ]);

        // Customer uploads reupload #2
        $file2 = UploadedFile::fake()->create('proof2.jpg', 250, 'image/jpeg');
        $response = $this->actingAs($user)->post(route('customer.orders.payment.post', $order->id), [
            'amount' => 180000,
            'proof_file' => $file2,
        ]);

        $response->assertRedirect(route('customer.orders.show', $order->id));
        $response->assertSessionHas('success');

        // Check attempt history
        $this->assertDatabaseCount('payment_attempts', 2);

        $this->assertDatabaseHas('payment_attempts', [
            'payment_id' => $payment->id,
            'attempt_number' => 1,
            'verification_status' => PaymentAttempt::STATUS_REJECTED,
            'admin_notes' => 'Foto bukti buram dan tidak terbaca nomor referensinya.',
        ]);

        $this->assertDatabaseHas('payment_attempts', [
            'payment_id' => $payment->id,
            'attempt_number' => 2,
            'verification_status' => PaymentAttempt::STATUS_PENDING,
        ]);

        $payment->refresh();
        $this->assertEquals(Payment::STATUS_PENDING, $payment->verification_status);
        $this->assertNull($payment->admin_notes); // Notes cleared for new pending attempt
    }

    /**
     * PAY-10: Customer accesses another customer's payment page.
     * Expected: HTTP 403 Forbidden.
     */
    public function test_pay_10_customer_accesses_another_customers_payment(): void
    {
        [$userA, $customerA] = $this->createCustomerUser('Customer A');
        [$userB, $customerB] = $this->createCustomerUser('Customer B');
        $owner = $this->createOwnerUser();
        $period = $this->createOpenPoPeriod($owner);
        [$orderA, $paymentA] = $this->createOrderWithPayment($customerA, $period, 120000);

        // User B attempts to access User A's payment page
        $response = $this->actingAs($userB)->get(route('customer.orders.payment', $orderA->id));
        $response->assertForbidden();

        // User B attempts to upload payment for User A's order
        Storage::fake('local');
        $file = UploadedFile::fake()->create('proof.jpg', 200, 'image/jpeg');
        $postResponse = $this->actingAs($userB)->post(route('customer.orders.payment.post', $orderA->id), [
            'amount' => 120000,
            'proof_file' => $file,
        ]);
        $postResponse->assertForbidden();
    }

    /**
     * PAY-11: Customer accesses another customer's payment proof.
     * Expected: HTTP 403 Forbidden.
     */
    public function test_pay_11_customer_accesses_another_customers_proof(): void
    {
        Storage::fake('local');
        [$userA, $customerA] = $this->createCustomerUser('Customer A');
        [$userB, $customerB] = $this->createCustomerUser('Customer B');
        $owner = $this->createOwnerUser();
        $period = $this->createOpenPoPeriod($owner);
        [$orderA, $paymentA] = $this->createOrderWithPayment($customerA, $period, 120000);

        $path = UploadedFile::fake()->create('proof.jpg', 200, 'image/jpeg')->store('payments', 'local');
        $paymentA->update(['proof_file_path' => $path]);
        $attempt = PaymentAttempt::create([
            'payment_id' => $paymentA->id,
            'attempt_number' => 1,
            'amount' => 120000,
            'proof_file_path' => $path,
            'verification_status' => PaymentAttempt::STATUS_PENDING,
        ]);

        // User B attempts to view payment proof of User A
        $this->actingAs($userB)->get(route('payments.proof', $paymentA->id))->assertForbidden();
        $this->actingAs($userB)->get(route('payments.attempt-proof', $attempt->id))->assertForbidden();

        // User A (owner) can view their own proof
        $this->actingAs($userA)->get(route('payments.proof', $paymentA->id))->assertOk();
        $this->actingAs($userA)->get(route('payments.attempt-proof', $attempt->id))->assertOk();
    }

    /**
     * PAY-12: Admin opens payment verification queue.
     * Expected: Pending item is visible in Admin queue and verify page opens with HTTP 200.
     */
    public function test_pay_12_admin_opens_payment_verification_queue(): void
    {
        Storage::fake('local');
        [$user, $customer] = $this->createCustomerUser();
        $admin = $this->createAdminUser();
        $owner = $this->createOwnerUser();
        $period = $this->createOpenPoPeriod($owner);
        [$order, $payment] = $this->createOrderWithPayment($customer, $period, 135000);

        $path = UploadedFile::fake()->create('receipt.jpg', 200, 'image/jpeg')->store('payments', 'local');
        $payment->update([
            'proof_file_path' => $path,
            'verification_status' => Payment::STATUS_PENDING,
            'uploaded_at' => now(),
        ]);
        PaymentAttempt::create([
            'payment_id' => $payment->id,
            'attempt_number' => 1,
            'amount' => 135000,
            'proof_file_path' => $path,
            'verification_status' => PaymentAttempt::STATUS_PENDING,
        ]);

        // Admin accesses verification index
        $indexResponse = $this->actingAs($admin)->get(route('admin.payments.index', ['status' => Payment::STATUS_PENDING]));
        $indexResponse->assertOk();
        $indexResponse->assertSee($order->order_number);

        // Admin accesses single verification page
        $verifyResponse = $this->actingAs($admin)->get(route('admin.payments.verify', $payment->id));
        $verifyResponse->assertOk();
        $verifyResponse->assertSee($order->order_number);
        $verifyResponse->assertSee(number_format(135000, 0, ',', '.'));
    }

    /**
     * PAY-13: Uploaded proof remains private (direct public URL unavailable).
     * Expected: File is strictly on private local disk, not public, and private response has security headers.
     */
    public function test_pay_13_uploaded_proof_remains_private(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        [$user, $customer] = $this->createCustomerUser();
        $owner = $this->createOwnerUser();
        $period = $this->createOpenPoPeriod($owner);
        [$order, $payment] = $this->createOrderWithPayment($customer, $period, 120000);

        $file = UploadedFile::fake()->create('private_receipt.jpg', 200, 'image/jpeg');
        $this->actingAs($user)->post(route('customer.orders.payment.post', $order->id), [
            'amount' => 120000,
            'proof_file' => $file,
        ]);

        $payment->refresh();
        $path = $payment->proof_file_path;

        // 1. File exists on local (private) disk
        Storage::disk('local')->assertExists($path);

        // 2. File does NOT exist on public disk
        Storage::disk('public')->assertMissing($path);

        // 3. Authorized access via dedicated route has strong security headers
        $response = $this->actingAs($user)->get(route('payments.proof', $payment->id));
        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    /**
     * PAY-14: Upload does not automatically verify payment.
     * Expected: Verification status remains 'Menunggu Verifikasi', order remains 'Menunggu Pembayaran'.
     */
    public function test_pay_14_upload_does_not_automatically_verify_payment(): void
    {
        Storage::fake('local');
        [$user, $customer] = $this->createCustomerUser();
        $owner = $this->createOwnerUser();
        $period = $this->createOpenPoPeriod($owner);
        [$order, $payment] = $this->createOrderWithPayment($customer, $period, 120000);

        $file = UploadedFile::fake()->create('manual_proof.png', 150, 'image/png');
        $this->actingAs($user)->post(route('customer.orders.payment.post', $order->id), [
            'amount' => 120000,
            'proof_file' => $file,
        ]);

        $order->refresh();
        $payment->refresh();

        // Must remain WAITING_PAYMENT, NOT automatically PAYMENT_VERIFIED
        $this->assertSame(Order::STATUS_WAITING_PAYMENT, $order->status);

        // Must remain PENDING, NOT automatically VALID
        $this->assertSame(Payment::STATUS_PENDING, $payment->verification_status);
        $this->assertNull($payment->verified_by);
        $this->assertNull($payment->verified_at);
    }
}
