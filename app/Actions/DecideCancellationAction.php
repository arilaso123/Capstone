<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\CancellationRequest;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Support\Money;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DecideCancellationAction
{
    /**
     * Atomically record Admin's decision on a cancellation request and clean up uploaded refund proofs on failure.
     *
     * @return array{order_number?:string,warning?:string,upload_error?:string}
     */
    public function execute(
        int|string $cancellationId,
        array $validated,
        ?UploadedFile $refundProof,
        ?int $actorId,
    ): array {
        $decision = $validated['status'];
        $notes = $validated['admin_notes'] ?? null;
        $refundPath = $refundProof?->store('refunds', 'local');

        if ($refundProof !== null && !$refundPath) {
            return ['upload_error' => 'Bukti refund gagal disimpan. Putusan belum diproses.'];
        }

        try {
            $result = DB::transaction(function () use ($cancellationId, $decision, $notes, $validated, $refundPath, $actorId): array {
                $reference = CancellationRequest::query()->select(['id', 'order_id'])->findOrFail($cancellationId);
                $order = Order::query()->whereKey($reference->order_id)->lockForUpdate()->firstOrFail();
                $payment = Payment::query()->where('order_id', $order->id)->lockForUpdate()->first();
                $cancellation = CancellationRequest::query()->whereKey($cancellationId)->lockForUpdate()->firstOrFail();

                if ($cancellation->status !== CancellationRequest::STATUS_PENDING) {
                    return ['warning' => 'Permohonan ini sudah diproses dengan keputusan: ' . $cancellation->status . '.'];
                }

                if (!$order->canBeCancelled()) {
                    return ['warning' => 'Status pesanan sudah berubah. Permohonan pembatalan ini tidak dapat diproses.'];
                }

                $paidOrder = $order->status === Order::STATUS_PAYMENT_VERIFIED || $payment?->isValid();
                if ($decision === CancellationRequest::STATUS_APPROVED && $paidOrder) {
                    if (!$payment?->isValid() || !$refundPath || empty($validated['refund_reference']) || empty($validated['refund_amount'])) {
                        return ['warning' => 'Pembatalan pesanan yang sudah dibayar hanya dapat disetujui setelah Admin mencatat refund lengkap.'];
                    }
                    if (!Money::matches($validated['refund_amount'], $payment->amount)
                        || !Money::matches($validated['refund_amount'], $order->total_amount)) {
                        return ['warning' => 'Nominal refund harus sama dengan pembayaran terverifikasi sebesar Rp ' . number_format((float) $payment->amount, 0, ',', '.') . '.'];
                    }
                }

                if ($decision === CancellationRequest::STATUS_APPROVED && $payment?->isPending() && $payment->proof_file_path) {
                    return ['warning' => 'Bukti transfer masih menunggu verifikasi. Selesaikan pemeriksaan pembayaran terlebih dahulu sebelum menyetujui pembatalan.'];
                }

                if ($decision === CancellationRequest::STATUS_APPROVED
                    && $payment?->isRejected()
                    && $payment->proof_file_path
                    && trim((string) $notes) === '') {
                    return ['warning' => 'Catatan rekonsiliasi wajib diisi karena pesanan memiliki bukti transfer yang telah ditolak. Pastikan mutasi bank sudah diperiksa.'];
                }

                $previousState = ['status' => $cancellation->status, 'order_status' => $order->status];
                $recordRefund = $decision === CancellationRequest::STATUS_APPROVED && $paidOrder;

                $cancellation->update([
                    'status' => $decision,
                    'admin_notes' => $notes,
                    'reviewed_by' => $actorId,
                    'reviewed_at' => now(),
                    'refund_amount' => $recordRefund ? $validated['refund_amount'] : null,
                    'refund_reference' => $recordRefund ? $validated['refund_reference'] : null,
                    'refund_proof_path' => $recordRefund ? $refundPath : null,
                    'refunded_at' => $recordRefund ? now() : null,
                ]);

                if ($decision === CancellationRequest::STATUS_APPROVED) {
                    $prevStatus = $order->status;
                    $order->update(['status' => Order::STATUS_CANCELLED]);

                    OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'previous_status' => $prevStatus,
                        'new_status' => Order::STATUS_CANCELLED,
                        'remarks' => 'Pesanan dibatalkan atas persetujuan permohonan pembatalan oleh Admin: ' . ($notes ?: 'Disetujui'),
                        'changed_by' => $actorId,
                    ]);
                }

                AuditLog::record('cancellation.decided', $cancellation, $previousState, [
                    'status' => $cancellation->status,
                    'order_status' => $order->status,
                    'refund_amount' => $cancellation->refund_amount,
                    'refund_reference' => $cancellation->refund_reference,
                    'refund_proof_path' => $cancellation->refund_proof_path,
                ]);

                return [
                    'order_number' => $order->order_number,
                    'refund_recorded' => $recordRefund,
                ];
            }, 3);
        } catch (Throwable $exception) {
            if ($refundPath) {
                Storage::disk('local')->delete($refundPath);
            }
            throw $exception;
        }

        if ($refundPath && !($result['refund_recorded'] ?? false)) {
            Storage::disk('local')->delete($refundPath);
        }

        return $result;
    }
}
