<?php

namespace App\Http\Requests\Admin;

use App\Models\CancellationRequest;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DecideCancellationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Admin or owner role is enforced by route middleware.
    }

    public function rules(): array
    {
        $cancellationId = $this->route('id');
        $preview = CancellationRequest::with('order.payment')->findOrFail($cancellationId);
        $paidOrder = $preview->order?->status === Order::STATUS_PAYMENT_VERIFIED
            || $preview->order?->payment?->isValid();
        $requiresRefund = $this->input('status') === CancellationRequest::STATUS_APPROVED && $paidOrder;

        return [
            'status' => ['required', 'in:' . CancellationRequest::STATUS_APPROVED . ',' . CancellationRequest::STATUS_REJECTED],
            'admin_notes' => ['required_if:status,' . CancellationRequest::STATUS_REJECTED, 'nullable', 'string', 'max:500'],
            'refund_amount' => [$requiresRefund ? 'required' : 'nullable', 'numeric', 'min:0.01'],
            'refund_reference' => $requiresRefund
                ? ['required', 'string', 'max:100', Rule::unique('cancellation_requests', 'refund_reference')->ignore($cancellationId)]
                : ['nullable', 'string', 'max:100'],
            'refund_proof' => [$requiresRefund ? 'required' : 'nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'refund_amount.required' => 'Nominal refund wajib dicatat sebelum menyetujui pembatalan pesanan yang sudah dibayar.',
            'refund_reference.required' => 'Nomor referensi transfer refund wajib dicatat.',
            'refund_proof.required' => 'Bukti transfer refund wajib diunggah sebelum persetujuan.',
            'refund_proof.image' => 'Bukti refund harus berupa berkas gambar.',
            'refund_proof.max' => 'Ukuran bukti refund maksimal 2 MB.',
        ];
    }
}

