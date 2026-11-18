<?php

namespace App\Http\Requests;

use App\Support\IndonesianPhone;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // The customer role is enforced by the route middleware.
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'recipient_phone' => IndonesianPhone::normalize((string) $this->input('recipient_phone', '')),
        ]);
    }

    public function rules(): array
    {
        return [
            'checkout_token' => ['required', 'uuid'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_phone' => ['required', 'string', 'max:16', 'regex:/^08[1-9][0-9]{7,10}$/'],
            'shipping_address' => ['required', 'string', 'max:1000'],
            'shipping_postal_code' => ['nullable', 'string', 'regex:/^[0-9]{5}$/'],
            'customer_notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_name.required' => 'Nama penerima wajib diisi.',
            'recipient_phone.required' => 'Nomor WhatsApp penerima wajib diisi.',
            'recipient_phone.regex' => 'Masukkan nomor WhatsApp Indonesia yang valid, misalnya 081234567890 atau +6281234567890.',
            'shipping_address.required' => 'Alamat pengiriman lengkap wajib diisi.',
            'shipping_postal_code.regex' => 'Kode pos harus terdiri dari 5 angka.',
        ];
    }
}
