<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentAttempt;
use Illuminate\Support\Facades\Storage;

class PaymentProofController extends Controller
{
    public function show(Payment $payment)
    {
        $order = $payment->order;
        abort_unless($order, 404);
        $this->authorize('view', $order);

        return $this->privateFileResponse($payment->proof_file_path);
    }

    public function showAttempt(PaymentAttempt $attempt)
    {
        $attempt->loadMissing('payment.order');
        $order = $attempt->payment?->order;
        abort_unless($order, 404);
        $this->authorize('view', $order);

        return $this->privateFileResponse($attempt->proof_file_path);
    }

    private function privateFileResponse(?string $proofPath)
    {
        abort_unless($proofPath, 404);
        abort_if(preg_match('/^https?:\/\//i', $proofPath) === 1, 404);

        $path = ltrim($proofPath, '/');
        $disk = Storage::disk('local');

        // Public disk fallback is limited to authorized users while legacy files are migrated.
        if (!$disk->exists($path)) {
            $disk = Storage::disk('public');
        }

        abort_unless($disk->exists($path), 404);

        return $disk->response($path, basename($path), [
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate, max-age=0',
            'Content-Security-Policy' => "default-src 'none'; img-src 'self' data:; style-src 'unsafe-inline'; sandbox",
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'no-referrer',
        ]);
    }
}
