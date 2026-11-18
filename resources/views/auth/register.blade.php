@extends('layouts.app')

@section('title', 'Daftar Akun Pelanggan')

@section('content')
<div class="container py-5 auth-page auth-register-page">
    <div class="auth-card-column auth-card-column-wide">
        <section class="auth-card" aria-labelledby="register-heading">
            <header class="auth-card-header">
                <img src="{{ asset('images/logo-emblem.png') }}" alt="Uyuy Jastip" class="auth-brand-mark">
                <p class="home-eyebrow">Pendaftaran</p>
                <h1 id="register-heading">Buat akun pelanggan</h1>
                <p>Daftar untuk membuat pesanan pre-order dan memantau perkembangannya.</p>
            </header>

            <form action="{{ route('register.post') }}" method="POST" class="auth-form auth-register-form">
                @csrf
                <div class="auth-register-grid">
                    <div class="customer-checkout-field">
                        <label for="name">Nama lengkap <span aria-hidden="true">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" autocomplete="name" required placeholder="Nama lengkap Anda" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" @if($errors->has('name')) aria-describedby="name-error" @endif>
                        @error('name')<p class="customer-checkout-error" id="name-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="customer-checkout-field">
                        <label for="email">Alamat email <span aria-hidden="true">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" required placeholder="nama@email.com" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" @if($errors->has('email')) aria-describedby="email-error" @endif>
                        @error('email')<p class="customer-checkout-error" id="email-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="customer-checkout-field">
                        <label for="phone_number">Nomor WhatsApp <span aria-hidden="true">*</span></label>
                        <input type="tel" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" autocomplete="tel" required placeholder="08123456789" aria-invalid="{{ $errors->has('phone_number') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('phone_number') ? 'phone-help phone-error' : 'phone-help' }}">
                        <small id="phone-help">Gunakan nomor aktif untuk konfirmasi pesanan dan pengiriman.</small>
                        @error('phone_number')<p class="customer-checkout-error" id="phone-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="customer-checkout-field">
                        <label for="city">Kota atau kabupaten</label>
                        <input type="text" id="city" name="city" value="{{ old('city') }}" autocomplete="address-level2" placeholder="Contoh: Bandung" aria-invalid="{{ $errors->has('city') ? 'true' : 'false' }}" @if($errors->has('city')) aria-describedby="city-error" @endif>
                        @error('city')<p class="customer-checkout-error" id="city-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="customer-checkout-field">
                        <label for="postal_code">Kode pos <span class="customer-checkout-optional">Opsional</span></label>
                        <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code') }}" inputmode="numeric" maxlength="5" pattern="[0-9]{5}" autocomplete="postal-code" placeholder="40123" aria-invalid="{{ $errors->has('postal_code') ? 'true' : 'false' }}" @if($errors->has('postal_code')) aria-describedby="postal-code-error" @endif>
                        @error('postal_code')<p class="customer-checkout-error" id="postal-code-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="customer-checkout-field auth-register-address">
                        <label for="address">Alamat pengiriman <span class="customer-checkout-optional">Opsional</span></label>
                        <textarea id="address" name="address" rows="3" autocomplete="street-address" placeholder="Jalan, nomor rumah, RT/RW, kelurahan, dan kecamatan" aria-invalid="{{ $errors->has('address') ? 'true' : 'false' }}" @if($errors->has('address')) aria-describedby="address-error" @endif>{{ old('address') }}</textarea>
                        @error('address')<p class="customer-checkout-error" id="address-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="customer-checkout-field">
                        <label for="password">Kata sandi <span aria-hidden="true">*</span></label>
                        <input type="password" id="password" name="password" autocomplete="new-password" required placeholder="Minimal 8 karakter" aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" @if($errors->has('password')) aria-describedby="password-error" @endif>
                        @error('password')<p class="customer-checkout-error" id="password-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="customer-checkout-field">
                        <label for="password_confirmation">Konfirmasi kata sandi <span aria-hidden="true">*</span></label>
                        <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required placeholder="Ulangi kata sandi">
                    </div>
                </div>

                <label class="auth-terms"><input type="checkbox" id="terms" required><span>Saya memahami bahwa pembayaran diverifikasi secara manual oleh Admin dan persetujuan akun tunduk pada ketentuan layanan.</span></label>
                <button type="submit" class="auth-submit">Buat akun pelanggan <span aria-hidden="true">→</span></button>
            </form>

            <footer class="auth-card-footer">Sudah memiliki akun? <a href="{{ route('login') }}">Masuk ke akun</a></footer>
        </section>
    </div>
</div>
@endsection
