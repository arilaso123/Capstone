<?php

namespace App\Http\Controllers;

use App\Models\CancellationRequest;
use Illuminate\Support\Facades\Storage;

class RefundProofController extends Controller
{
    public function show(CancellationRequest $cancellation)
    {
        $cancellation->loadMissing('order');
        abort_unless($cancellation->order && $cancellation->refund_proof_path, 404);
        $this->authorize('view', $cancellation->order);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($cancellation->refund_proof_path), 404);

        return $disk->response($cancellation->refund_proof_path, basename($cancellation->refund_proof_path), [
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate, max-age=0',
            'Content-Security-Policy' => "default-src 'none'; img-src 'self' data:; style-src 'unsafe-inline'; sandbox",
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'no-referrer',
        ]);
    }
}
