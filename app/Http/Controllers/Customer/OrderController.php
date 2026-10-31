<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\CancellationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $customer = Auth::user()->customer;
        if (!$customer) {
            return redirect()->route('home')->with('error', 'Profil pelanggan tidak ditemukan.');
        }

        $query = $customer->orders()->with(['poPeriod', 'payment']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        return view('customer.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $customer = Auth::user()->customer;
        $order = Order::with(['poPeriod', 'orderDetails.product', 'payment', 'statusHistories.changer', 'cancellationRequest'])
            ->findOrFail($id);

        if ($order->customer_id !== $customer?->id && !Auth::user()->isAdmin() && !Auth::user()->isOwner()) {
            abort(403, 'Anda tidak memiliki hak untuk melihat pesanan ini.');
        }

        return view('customer.orders.show', compact('order'));
    }

    public function payment($id)
    {
        $customer = Auth::user()->customer;
        $order = Order::with('payment')->findOrFail($id);

        if ($order->customer_id !== $customer?->id) {
            abort(403, 'Akses ditolak.');
        }

        if ($order->status === Order::STATUS_CANCELLED) {
            return redirect()->route('customer.orders.show', $order->id)
                ->with('error', 'Pesanan ini telah dibatalkan sehingga pembayaran tidak dapat diproses.');
        }

        if ($order->payment?->isValid()) {
            return redirect()->route('customer.orders.show', $order->id)
                ->with('info', 'Pembayaran untuk pesanan ini telah diverifikasi VALID oleh admin.');
        }

        return view('customer.orders.payment', compact('order'));
    }

    public function uploadPayment(Request $request, $id)
    {
        $customer = Auth::user()->customer;
        $order = Order::with('payment')->findOrFail($id);

        if ($order->customer_id !== $customer?->id) {
            abort(403, 'Akses ditolak.');
        }

        if ($order->status === Order::STATUS_CANCELLED) {
            return redirect()->route('customer.orders.show', $order->id)
                ->with('error', 'Pesanan ini telah dibatalkan sehingga pembayaran tidak dapat diunggah.');
        }

        if ($order->payment?->isValid()) {
            return redirect()->route('customer.orders.show', $order->id)
                ->with('info', 'Pembayaran untuk pesanan ini telah diverifikasi VALID oleh admin.');
        }

        $request->validate([
            'amount' => ['required', 'numeric', 'min:1000'],
            'proof_file' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:2048'],
        ], [
            'amount.required' => 'Nominal transfer wajib diisi.',
            'proof_file.required' => 'Berkas bukti transfer wajib diunggah.',
            'proof_file.mimes' => 'Format berkas harus JPG, PNG, atau PDF.',
            'proof_file.max' => 'Ukuran berkas maksimal 2 MB.',
        ]);

        $path = $request->file('proof_file')->store('payments', 'public');

        $payment = $order->payment;
        if ($payment) {
            // Remove previous proof file if exists
            if ($payment->proof_file_path && !str_starts_with($payment->proof_file_path, 'http')) {
                Storage::disk('public')->delete($payment->proof_file_path);
            }

            $payment->update([
                'amount' => $request->amount,
                'proof_file_path' => $path,
                'uploaded_at' => now(),
                'verification_status' => 'Menunggu Verifikasi',
            ]);
        }

        return redirect()->route('customer.orders.show', $order->id)
            ->with('success', 'Bukti pembayaran berhasil diunggah! Staf admin akan segera memverifikasi pembayaran Anda.');
    }

    public function cancellation($id)
    {
        $customer = Auth::user()->customer;
        $order = Order::with('cancellationRequest')->findOrFail($id);

        if ($order->customer_id !== $customer?->id) {
            abort(403, 'Akses ditolak.');
        }

        if ($order->status === Order::STATUS_CANCELLED) {
            return redirect()->route('customer.orders.show', $order->id)
                ->with('info', 'Pesanan ini telah resmi dibatalkan.');
        }

        if ($order->cancellationRequest) {
            if ($order->cancellationRequest->isPending()) {
                return redirect()->route('customer.orders.show', $order->id)
                    ->with('warning', 'Permohonan pembatalan untuk pesanan ini telah diajukan dan sedang menunggu peninjauan admin.');
            }
            if ($order->cancellationRequest->isRejected()) {
                return redirect()->route('customer.orders.show', $order->id)
                    ->with('error', 'Permohonan pembatalan untuk pesanan ini telah ditolak oleh admin.');
            }
        }

        if (!$order->canBeCancelled()) {
            return redirect()->route('customer.orders.show', $order->id)
                ->with('error', 'Pesanan ini tidak dapat dibatalkan melalui sistem karena sudah masuk tahap proses pengadaan atau pengiriman.');
        }

        return view('customer.orders.cancellation', compact('order'));
    }

    public function submitCancellation(Request $request, $id)
    {
        $customer = Auth::user()->customer;
        $order = Order::with('cancellationRequest')->findOrFail($id);

        if ($order->customer_id !== $customer?->id) {
            abort(403, 'Akses ditolak.');
        }

        if ($order->status === Order::STATUS_CANCELLED) {
            return redirect()->route('customer.orders.show', $order->id)
                ->with('info', 'Pesanan ini telah resmi dibatalkan.');
        }

        if ($order->cancellationRequest) {
            if ($order->cancellationRequest->isPending()) {
                return redirect()->route('customer.orders.show', $order->id)
                    ->with('warning', 'Permohonan pembatalan untuk pesanan ini telah diajukan dan sedang menunggu peninjauan admin.');
            }
            if ($order->cancellationRequest->isRejected()) {
                return redirect()->route('customer.orders.show', $order->id)
                    ->with('error', 'Permohonan pembatalan untuk pesanan ini telah ditolak oleh admin.');
            }
        }

        if (!$order->canBeCancelled()) {
            return redirect()->route('customer.orders.show', $order->id)
                ->with('error', 'Pesanan ini tidak dapat dibatalkan.');
        }

        $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'reason.required' => 'Alasan pengajuan pembatalan wajib diisi.',
            'reason.min' => 'Alasan pembatalan minimal 5 karakter.',
        ]);

        CancellationRequest::updateOrCreate(
            ['order_id' => $order->id],
            [
                'reason' => $request->reason,
                'status' => CancellationRequest::STATUS_PENDING,
                'admin_notes' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]
        );

        return redirect()->route('customer.orders.show', $order->id)
            ->with('warning', 'Permohonan pembatalan telah dikirim dan menunggu persetujuan admin.');
    }
}

