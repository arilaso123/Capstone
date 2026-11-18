@extends('layouts.app')

@section('title', 'Masuk Akun')

@section('content')
<div class="container py-5 auth-page auth-login-page">
    <div class="auth-card-column">
        <section class="auth-card" aria-labelledby="login-heading">
            <header class="auth-card-header">
                <img src="{{ asset('images/logo-emblem.png') }}" alt="Uyuy Jastip" class="auth-brand-mark">
                <p class="home-eyebrow">Akun pelanggan</p>
                <h1 id="login-heading">Selamat datang kembali</h1>
                <p>Masuk untuk melihat pesanan dan memperbarui informasi akun Anda.</p>
            </header>

            <form action="{{ route('login.post') }}" method="POST" class="auth-form">
                @csrf
                <x-input name="email" label="Alamat email" type="email" icon="bi bi-envelope" placeholder="nama@email.com" required autofocus />
                <x-input name="password" label="Kata sandi" type="password" icon="bi bi-shield-lock" placeholder="Masukkan kata sandi" required />

                <div class="auth-form-options">
                    <label><input type="checkbox" id="remember" name="remember" value="1"> <span>Ingat sesi saya</span></label>
                    <a href="{{ route('password.request') }}">Lupa kata sandi?</a>
                </div>

                <button type="submit" class="auth-submit">Masuk ke akun <span aria-hidden="true">→</span></button>
            </form>

            @if (app()->environment(['local', 'testing']))
                <section class="auth-demo" aria-labelledby="auth-demo-heading">
                    <header><div><p class="home-eyebrow">Lingkungan pengujian</p><h2 id="auth-demo-heading">Isi akun demo</h2></div><x-demo-badge label="Prototipe" /></header>
                    <p>Akun ini membantu pengujian akses berdasarkan peran pada lingkungan demo.</p>
                    <div class="auth-demo-actions">
                        <button type="button" data-email="customer@uyuy.test">Customer</button>
                        <button type="button" data-email="admin1@uyuy.test">Admin</button>
                        <button type="button" data-email="owner@uyuy.test">Owner</button>
                    </div>
                </section>
            @endif

            <footer class="auth-card-footer">Belum memiliki akun pelanggan? <a href="{{ route('register') }}">Daftar sekarang</a></footer>
        </section>
    </div>
</div>
@endsection
