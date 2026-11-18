<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\CancellationRequest;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CancelExpiredOrders extends Command
{
    protected $signature = 'orders:cancel-expired';

    protected $description = 'Cancel unpaid orders after their configured payment deadline';

    public function handle(): int
    {
        $now = now();
        $cancelled = 0;

        Order::query()
            ->where('status', Order::STATUS_WAITING_PAYMENT)
            ->whereNotNull('payment_deadline_at')
            ->where('payment_deadline_at', '<=', $now)
            ->orderBy('id')
            ->chunkById(100, function ($orders) use (&$cancelled): void {
                foreach ($orders as $candidate) {
                    $didCancel = DB::transaction(function () use ($candidate): bool {
                        $order = Order::query()->whereKey($candidate->id)->lockForUpdate()->first();
                        if (!$order || $order->status !== Order::STATUS_WAITING_PAYMENT || !$order->paymentDeadlinePassed()) {
                            return false;
                        }

                        $payment = Payment::query()->where('order_id', $order->id)->lockForUpdate()->first();
                        if ($payment?->isValid() || ($payment?->isPending() && $payment->proof_file_path)) {
                            return false;
                        }
                        if (CancellationRequest::query()->where('order_id', $order->id)
                            ->where('status', CancellationRequest::STATUS_PENDING)->exists()) {
                            return false;
                        }

                        $previous = $order->status;
                        $order->update(['status' => Order::STATUS_CANCELLED]);
                        OrderStatusHistory::create([
                            'order_id' => $order->id,
                            'previous_status' => $previous,
                            'new_status' => Order::STATUS_CANCELLED,
                            'remarks' => 'Pesanan dibatalkan otomatis karena melewati batas pembayaran tanpa bukti transfer yang menunggu verifikasi.',
                            'changed_by' => null,
                        ]);
                        AuditLog::record('order.expired', $order, ['status' => $previous], ['status' => $order->status]);
                        return true;
                    }, 3);

                    if ($didCancel) {
                        $cancelled++;
                    }
                }
            });

        $this->info("Pesanan kedaluwarsa dibatalkan: {$cancelled}.");
        return self::SUCCESS;
    }
}
