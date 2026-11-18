<?php

namespace App\Http\Requests\Admin;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;

class VerifyPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Admin or owner role is enforced by route middleware.
    }

    public function rules(): array
    {
        return [
            'verification_status' => ['required', 'in:' . Payment::STATUS_VALID . ',' . Payment::STATUS_REJECTED],
            'admin_notes' => ['required_if:verification_status,' . Payment::STATUS_REJECTED, 'nullable', 'string', 'max:500'],
        ];
    }
}

