@extends('layouts.app')

@section('title', 'Lupa Kata Sandi')

@section('content')
<div class="container py-5 auth-page auth-recovery-page">
    <div class="auth-card-column">
        <section class="auth-card" aria-labelledby="forgot-password-heading">
            <header class="auth-card-header">
                <img src="{{ asset('images/logo-emblem.png') }}" alt="Uyuy Jastip" class="auth-brand-mark">
                <p class="home-eyebrow">Pemulihan akun</p>
                <h1 id="forgot-password-heading">Atur ulang kata sandi</h1>
                <p>Masukkan alamat email akun. Jika email terdaftar, instruksi pemulihan akan dikirim.</p>
            </header>

            @if(session('status'))<p class="auth-feedback" role="status">{{ session('status') }}</p>@endif

            <form method="POST" action="{{ route('password.email') }}" class="auth-form">
                @csrf
                <div class="customer-checkout-field">
                    <label for="email">Alamat email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" @if($errors->has('email')) aria-describedby="forgot-email-error" @endif>
                    @error('email')<p class="customer-checkout-error" id="forgot-email-error">{{ $message }}</p>@enderror
                </div>
                <button class="auth-submit" type="submit">Kirim instruksi reset <span aria-hidden="true">→</span></button>
            </form>

            <footer class="auth-card-footer"><a href="{{ route('login') }}">Kembali ke halaman masuk</a></footer>
        </section>
    </div>
</div>
@endsection
