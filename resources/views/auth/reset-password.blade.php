@extends('layouts.app')

@section('title', 'Atur Kata Sandi Baru')

@section('content')
<div class="container py-5 auth-page auth-recovery-page">
    <div class="auth-card-column">
        <section class="auth-card" aria-labelledby="reset-password-heading">
            <header class="auth-card-header">
                <img src="{{ asset('images/logo-emblem.png') }}" alt="Uyuy Jastip" class="auth-brand-mark">
                <p class="home-eyebrow">Pemulihan akun</p>
                <h1 id="reset-password-heading">Buat kata sandi baru</h1>
                <p>Gunakan kata sandi yang kuat dan konfirmasikan sebelum menyimpan.</p>
            </header>

            <form method="POST" action="{{ route('password.update') }}" class="auth-form">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="customer-checkout-field">
                    <label for="email">Alamat email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" required aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" @if($errors->has('email')) aria-describedby="reset-email-error" @endif>
                    @error('email')<p class="customer-checkout-error" id="reset-email-error">{{ $message }}</p>@enderror
                </div>
                <div class="customer-checkout-field">
                    <label for="password">Kata sandi baru</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" @if($errors->has('password')) aria-describedby="reset-password-error" @endif>
                    @error('password')<p class="customer-checkout-error" id="reset-password-error">{{ $message }}</p>@enderror
                </div>
                <div class="customer-checkout-field">
                    <label for="password_confirmation">Konfirmasi kata sandi</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                </div>
                <button class="auth-submit" type="submit">Simpan kata sandi <span aria-hidden="true">→</span></button>
            </form>
        </section>
    </div>
</div>
@endsection
