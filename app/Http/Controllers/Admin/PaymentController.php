<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['order.customer', 'verifier']);

        if ($request->filled('status')) {
            $query->where('verification_status', $request->input('status'));
        }

        $payments = $query->latest()->paginate(10)->withQueryString();

        return view('admin.payments.index', compact('payments'));
    }

    public function verify($id)
    {
        $payment = Payment::with(['order.customer.user', 'order.orderDetails'])->findOrFail($id);

        return view('admin.payments.verify', compact('payment'));
    }

    public function processVerification(Request $request, $id)
    {
        $payment = Payment::with('order')->findOrFail($id);

        if ($payment->order->status === Order::STATUS_CANCELLED) {
            return redirect()->route('admin.payments.index')
                ->with('error', 'Pembayaran tidak dapat diproses karena pesanan #' . $payment->order->order_number . ' telah dibatalkan.');
        }

        if (!$payment->proof_file_path) {
            return redirect()->back()
                ->with('error', 'Pelanggan belum mengunggah berkas bukti transfer untuk pesanan ini.');
        }

        $request->validate([
            'verification_status' => ['required', 'in:Valid,Ditolak'],
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $newVerificationStatus = $request->input('verification_status');
        $adminNotes = $request->input('admin_notes');

        DB::transaction(function () use ($payment, $newVerificationStatus, $adminNotes) {
            $payment->update([
                'verification_status' => $newVerificationStatus,
                'admin_notes' => $adminNotes,
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);

            $order = $payment->order;
            if ($newVerificationStatus === 'Valid' && $order->status === Order::STATUS_WAITING_PAYMENT) {
                $order->update(['status' => Order::STATUS_PAYMENT_VERIFIED]);

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'previous_status' => Order::STATUS_WAITING_PAYMENT,
                    'new_status' => Order::STATUS_PAYMENT_VERIFIED,
                    'remarks' => 'Pembayaran BCA Merchant diverifikasi VALID oleh admin: ' . ($adminNotes ?: 'Lunas'),
                    'changed_by' => Auth::id(),
                ]);
            }
        });

        return redirect()->route('admin.payments.index')
            ->with('success', "Hasil verifikasi pembayaran untuk pesanan #{$payment->order->order_number} berhasil dicatat ({$newVerificationStatus}).");
    }
}

