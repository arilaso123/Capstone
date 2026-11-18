<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Models\CancellationRequest;
use App\Models\AuditLog;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:' . implode(',', Order::$statuses)],
        ]);

        $customer = Auth::user()->customer;
        if (!$customer) {
            return redirect()->route('home')->with('error', 'Profil pelanggan tidak ditemukan.');
        }

        $query = $customer->orders()->with(['poPeriod', 'payment']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        return view('customer.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with(['poPeriod', 'orderDetails.product', 'payment.attempts.verifier', 'statusHistories.changer', 'cancellationRequest'])
            ->findOrFail($id);
        $this->authorize('view', $order);

        return view('customer.orders.show', compact('order'));
    }

    public function payment($id)
    {
        $order = Order::with('payment.attempts')->findOrFail($id);
        $this->authorize('interact', $order);

        if ($order->status !== Order::STATUS_WAITING_PAYMENT) {
            return redirect()->route('customer.orders.show', $order->id)
                ->with('error', 'Pembayaran hanya dapat diunggah saat pesanan menunggu pembayaran.');
        }

        if ($order->payment?->isValid()) {
            return redirect()->route('customer.orders.show', $order->id)
                ->with('info', 'Pembayaran untuk pesanan ini telah diverifikasi VALID oleh admin.');
        }

        if ($order->paymentDeadlinePassed()) {
            return redirect()->route('customer.orders.show', $order->id)
                ->with('error', $this->paymentDeadlineMessage($order));
        }

        return view('customer.orders.payment', compact('order'));
    }

    public function uploadPayment(Request $request, $id)
    {
        $order = Order::with('payment')->findOrFail($id);
        $this->authorize('interact', $order);
        $customer = Auth::user()->customer;

        if ($order->status !== Order::STATUS_WAITING_PAYMENT) {
            return redirect()->route('customer.orders.show', $order->id)
                ->with('error', 'Pembayaran hanya dapat diunggah saat pesanan menunggu pembayaran.');
        }

        if ($order->payment?->isValid()) {
            return redirect()->route('customer.orders.show', $order->id)
                ->with('info', 'Pembayaran untuk pesanan ini telah diverifikasi VALID oleh admin.');
        }

        if ($order->paymentDeadlinePassed()) {
            return redirect()->route('customer.orders.show', $order->id)
                ->with('error', $this->paymentDeadlineMessage($order));
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'proof_file' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:2048'],
        ], [
            'amount.required' => 'Nominal transfer wajib diisi.',
            'proof_file.required' => 'Berkas bukti transfer wajib diunggah.',
            'proof_file.mimes' => 'Format berkas harus JPG, PNG, atau PDF.',
            'proof_file.max' => 'Ukuran berkas maksimal 2 MB.',
        ]);

        if (!Money::matches($validated['amount'], $order->total_amount)) {
            return back()->withErrors([
                'amount' => 'Nominal transfer harus sama dengan total tagihan Rp ' . number_format((float)$order->total_amount, 0, ',', '.') . '.',
            ])->withInput();
        }

        if (!$order->payment) {
            return redirect()->route('customer.orders.show', $order->id)
                ->with('error', 'Data pembayaran pesanan ini tidak ditemukan. Silakan hubungi Admin.');
        }

        $path = $request->file('proof_file')->store('payments', 'local');
        if (!$path) {
            return back()->with('error', 'Berkas bukti pembayaran gagal disimpan. Silakan coba kembali.');
        }

        try {
            $result = DB::transaction(function () use ($order, $customer, $path) {
                $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
                if ($lockedOrder->paymentDeadlinePassed()) {
                    return ['error' => 'Batas pembayaran telah lewat. Unggahan baru tidak dapat diterima.'];
                }
                if ($lockedOrder->customer_id !== $customer->id || $lockedOrder->status !== Order::STATUS_WAITING_PAYMENT) {
                    return ['error' => 'Status pesanan berubah. Bukti pembayaran tidak dapat diperbarui.'];
                }

                $payment = Payment::query()
                    ->where('order_id', $lockedOrder->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($payment->isValid()) {
                    return ['error' => 'Pembayaran pesanan ini sudah diverifikasi.'];
                }

                PaymentAttempt::query()
                    ->where('payment_id', $payment->id)
                    ->where('verification_status', PaymentAttempt::STATUS_PENDING)
                    ->lockForUpdate()
                    ->get()
                    ->each(fn (PaymentAttempt $attempt) => $attempt->update([
                        'verification_status' => PaymentAttempt::STATUS_SUPERSEDED,
                        'admin_notes' => 'Bukti digantikan oleh unggahan terbaru pelanggan sebelum verifikasi.',
                    ]));

                $attemptNumber = ((int) PaymentAttempt::where('payment_id', $payment->id)->max('attempt_number')) + 1;
                $uploadedAt = now();
                PaymentAttempt::create([
                    'payment_id' => $payment->id,
                    'attempt_number' => $attemptNumber,
                    'amount' => $lockedOrder->total_amount,
                    'proof_file_path' => $path,
                    'uploaded_at' => $uploadedAt,
                    'verification_status' => PaymentAttempt::STATUS_PENDING,
                ]);

                $payment->update([
                    'amount' => $lockedOrder->total_amount,
                    'proof_file_path' => $path,
                    'uploaded_at' => $uploadedAt,
                    'verification_status' => Payment::STATUS_PENDING,
                    'verified_by' => null,
                    'verified_at' => null,
                    'admin_notes' => null,
                ]);

                AuditLog::record('payment.proof_uploaded', $payment, null, [
                    'order_id' => $lockedOrder->id,
                    'attempt_number' => $attemptNumber,
                    'proof_file_path' => $path,
                    'amount' => $payment->amount,
                ]);

                return ['attempt_number' => $attemptNumber];
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        if (isset($result['error'])) {
            Storage::disk('local')->delete($path);
            return redirect()->route('customer.orders.show', $order->id)->with('error', $result['error']);
        }

        return redirect()->route('customer.orders.show', $order->id)
            ->with('success', 'Bukti pembayaran percobaan #' . $result['attempt_number'] . ' berhasil diunggah. Bukti dan keputusan sebelumnya disimpan untuk riwayat; staf Admin akan memverifikasi unggahan terbaru.');
    }

    public function cancellation($id)
    {
        $order = Order::with('cancellationRequest')->findOrFail($id);
        $this->authorize('interact', $order);

        if ($order->status === Order::STATUS_CANCELLED) {
            return redirect()->route('customer.orders.show', $order->id)
                ->with('info', 'Pesanan ini telah resmi dibatalkan.');
        }

        if ($order->cancellationRequest) {
            if ($order->cancellationRequest->isPending()) {
                return redirect()->route('customer.orders.show', $order->id)
                    ->with('warning', 'Permohonan pembatalan untuk pesanan ini telah diajukan dan sedang menunggu peninjauan admin.');
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
        if (!$customer) {
            return redirect()->route('home')->with('error', 'Profil pelanggan tidak ditemukan.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'reason.required' => 'Alasan pengajuan pembatalan wajib diisi.',
            'reason.min' => 'Alasan pembatalan minimal 5 karakter.',
        ]);

        $result = DB::transaction(function () use ($id, $customer, $validated) {
            $order = Order::query()->whereKey($id)->lockForUpdate()->firstOrFail();
            $this->authorize('interact', $order);

            if ($order->status === Order::STATUS_CANCELLED) {
                return 'cancelled';
            }

            $latestRequest = CancellationRequest::query()
                ->where('order_id', $order->id)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if ($latestRequest?->isPending()) {
                return 'pending';
            }

            if (!$order->canBeCancelled()) {
                return 'not_cancellable';
            }

            CancellationRequest::create([
                'order_id' => $order->id,
                'reason' => $validated['reason'],
                'status' => CancellationRequest::STATUS_PENDING,
            ]);

            return 'submitted';
        });

        if ($result !== 'submitted') {
            $messages = [
                'cancelled' => ['info', 'Pesanan ini telah resmi dibatalkan.'],
                'pending' => ['warning', 'Permohonan pembatalan sudah menunggu peninjauan Admin.'],
                'not_cancellable' => ['error', 'Pesanan ini tidak dapat dibatalkan pada tahap saat ini.'],
            ];
            [$level, $message] = $messages[$result];
            return redirect()->route('customer.orders.show', $id)->with($level, $message);
        }

        return redirect()->route('customer.orders.show', $id)
            ->with('warning', 'Permohonan pembatalan telah dikirim dan menunggu persetujuan admin.');
    }

    private function paymentDeadlineMessage(Order $order): string
    {
        if ($order->payment?->isPending() && $order->payment?->proof_file_path) {
            return 'Batas pembayaran telah lewat. Bukti yang sudah diterima tetap menunggu pemeriksaan Admin, tetapi unggahan baru tidak dapat dikirim.';
        }

        return 'Batas pembayaran telah lewat. Pesanan ini tidak dapat menerima unggahan baru dan akan dibatalkan otomatis.';
    }
}

