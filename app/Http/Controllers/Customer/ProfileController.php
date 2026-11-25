<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Support\IndonesianPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $customer = $user->customer;

        return view('customer.profile', compact('user', 'customer'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone_number' => [
                'required',
                'string',
                'max:30',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (!is_string($value) || !IndonesianPhone::isValid($value)) {
                        $fail('Format nomor WhatsApp tidak valid. Gunakan nomor Indonesia (contoh: 081234567890).');
                    }
                },
            ],
            'default_address' => ['required', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'digits:5'],
        ], [
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'phone_number.required' => 'Nomor WhatsApp aktif wajib diisi.',
            'default_address.required' => 'Alamat pengiriman default wajib diisi.',
            'city.required' => 'Kota / Kabupaten wajib diisi.',
            'postal_code.digits' => 'Kode pos harus terdiri dari 5 digit angka.',
        ]);

        $user = Auth::user();
        $normalizedPhone = IndonesianPhone::normalize($validated['phone_number']);

        DB::transaction(function () use ($user, $validated, $normalizedPhone): void {
            $user->update([
                'name' => $validated['full_name'],
            ]);

            Customer::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'full_name' => $validated['full_name'],
                    'phone_number' => $normalizedPhone,
                    'default_address' => $validated['default_address'],
                    'city' => $validated['city'],
                    'postal_code' => $validated['postal_code'] ?: null,
                ]
            );
        });

        return redirect()->route('customer.profile.edit')
            ->with('success', 'Profil dan alamat pengiriman Anda berhasil diperbarui.');
    }
}
