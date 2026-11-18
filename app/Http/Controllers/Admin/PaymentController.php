<?php

namespace App\Http\Controllers\Admin;

use App\Actions\VerifyPaymentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VerifyPaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:' . implode(',', [Payment::STATUS_PENDING, Payment::STATUS_VALID, Payment::STATUS_REJECTED])],
        ]);

        $query = Payment::with(['order.customer', 'verifier']);

        if (!empty($filters['status'])) {
            $query->where('verification_status', $filters['status']);
        }

        if (($filters['status'] ?? null) === Payment::STATUS_PENDING) {
            $query->whereNotNull('proof_file_path')
                ->whereHas('order', fn ($orders) => $orders->where('status', Order::STATUS_WAITING_PAYMENT));
        }

        $payments = $query->latest()->paginate(10)->withQueryString();

        return view('admin.payments.index', compact('payments'));
    }

    public function verify($id)
    {
        $payment = Payment::with(['order.customer.user', 'order.orderDetails', 'attempts.verifier'])->findOrFail($id);

        return view('admin.payments.verify', compact('payment'));
    }

    public function processVerification(VerifyPaymentRequest $request, $id, VerifyPaymentAction $verifyPayment)
    {
        $validated = $request->validated();
        $newVerificationStatus = $validated['verification_status'];
        $result = $verifyPayment->execute($id, $newVerificationStatus, $validated['admin_notes'] ?? null, Auth::id());

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        return redirect()->route('admin.payments.index')
            ->with('success', "Hasil verifikasi pembayaran untuk pesanan #{$result['order_number']} berhasil dicatat ({$newVerificationStatus}).");
    }
}

