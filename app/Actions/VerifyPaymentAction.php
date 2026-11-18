<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VerifyPaymentAction
{
    /**
     * Atomically verify or reject the active pending payment attempt for an order.
     *
     * @return array{order_number?:string,error?:string}
     */
    public function execute(int|string $paymentId, string $newVerificationStatus, ?string $adminNotes, ?int $actorId): array
    {
        return DB::transaction(function () use ($paymentId, $newVerificationStatus, $adminNotes, $actorId): array {
            $paymentReference = Payment::query()->select(['id', 'order_id'])->findOrFail($paymentId);
            $order = Order::query()->whereKey($paymentReference->order_id)->lockForUpdate()->firstOrFail();
            $payment = Payment::query()->whereKey($paymentId)->lockForUpdate()->firstOrFail();

            if ($payment->verification_status !== Payment::STATUS_PENDING) {
                return ['error' => 'Pembayaran ini sudah diproses. Minta pelanggan mengunggah bukti baru sebelum keputusan berikutnya.'];
            }

            if ($order->status !== Order::STATUS_WAITING_PAYMENT) {
                return ['error' => 'Pembayaran hanya dapat diverifikasi saat pesanan menunggu pembayaran.'];
            }

            if (!$this->proofExists($payment->proof_file_path)) {
                return ['error' => 'Berkas bukti transfer tidak ditemukan. Minta pelanggan mengunggah ulang.'];
            }

            if (!Money::matches($payment->amount, $order->total_amount)) {
                return ['error' => 'Nominal yang dilaporkan berbeda dari total tagihan. Jangan setujui pembayaran sampai selisih diklarifikasi.'];
            }

            $attempt = PaymentAttempt::query()
                ->where('payment_id', $payment->id)
                ->where('proof_file_path', $payment->proof_file_path)
                ->where('verification_status', PaymentAttempt::STATUS_PENDING)
                ->lockForUpdate()
                ->first();

            if (!$attempt) {
                return ['error' => 'Riwayat unggahan aktif tidak ditemukan. Jangan lanjutkan putusan; minta pelanggan mengunggah ulang bukti.'];
            }

            $verifiedAt = now();
            $before = [
                'payment_status' => $payment->verification_status,
                'order_status' => $order->status,
                'amount' => $payment->amount,
            ];

            $payment->update([
                'verification_status' => $newVerificationStatus,
                'admin_notes' => $adminNotes,
                'verified_by' => $actorId,
                'verified_at' => $verifiedAt,
            ]);

            $attempt->update([
                'verification_status' => $newVerificationStatus,
                'admin_notes' => $adminNotes,
                'verified_by' => $actorId,
                'verified_at' => $verifiedAt,
            ]);

            if ($newVerificationStatus === Payment::STATUS_VALID) {
                $order->update(['status' => Order::STATUS_PAYMENT_VERIFIED]);

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'previous_status' => Order::STATUS_WAITING_PAYMENT,
                    'new_status' => Order::STATUS_PAYMENT_VERIFIED,
                    'remarks' => 'Pembayaran BCA Merchant diverifikasi VALID oleh admin: ' . ($adminNotes ?: 'Lunas'),
                    'changed_by' => $actorId,
                ]);
            }

            AuditLog::record('payment.verified', $payment, $before, [
                'payment_status' => $payment->verification_status,
                'order_status' => $order->status,
                'amount' => $payment->amount,
                'admin_notes' => $adminNotes,
            ]);

            return ['order_number' => $order->order_number];
        });
    }

    private function proofExists(?string $path): bool
    {
        if (!$path || str_starts_with($path, 'http')) {
            return false;
        }

        return Storage::disk('local')->exists($path) || Storage::disk('public')->exists($path);
    }
}

