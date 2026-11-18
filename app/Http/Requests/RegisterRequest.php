<?php

namespace App\Http\Requests;

use App\Support\IndonesianPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Registration routes use the guest middleware.
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['phone_number' => IndonesianPhone::normalize((string) $this->input('phone_number', ''))]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['required', 'string', 'max:16', 'regex:/^08[1-9][0-9]{7,10}$/'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'postal_code' => ['nullable', 'string', 'regex:/^[0-9]{5}$/'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Alamat email ini sudah terdaftar.',
            'phone_number.required' => 'Nomor WhatsApp / telepon wajib diisi.',
            'phone_number.regex' => 'Masukkan nomor WhatsApp Indonesia yang valid, misalnya 081234567890 atau +6281234567890.',
            'postal_code.regex' => 'Kode pos harus terdiri dari 5 angka.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ];
    }
}
