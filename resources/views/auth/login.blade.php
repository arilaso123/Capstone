@extends('layouts.app')

@section('title', 'Masuk Akun')

@section('content')
<div class="container py-5 my-md-4">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5 col-xl-5">
            
            <!-- Floating Luxury Card -->
            <x-card class="p-4 p-md-5 border-0 shadow">
                
                <!-- Brand Header -->
                <div class="text-center mb-4 pb-2">
                    <div class="d-inline-flex p-3 rounded-circle mb-3" style="background-color: var(--color-champagne-light); box-shadow: 0 4px 14px rgba(216, 197, 174, 0.4);">
                        <img src="{{ asset('images/logo-emblem.png') }}" alt="Uyuy Jastip" style="height: 52px; width: auto; object-fit: contain;">
                    </div>
                    <h3 class="font-heading mb-2" style="font-size: 1.75rem;">Selamat Datang Kembali</h3>
                    <p class="text-secondary small mb-0">Masuk untuk mengelola pesanan parfum pre-order Anda</p>
                </div>

                <!-- Form Login -->
                <form action="{{ route('login.post') }}" method="POST">
                    @csrf

                    <!-- Email Input -->
                    <x-input 
                        name="email" 
                        label="Alamat Email" 
                        type="email" 
                        icon="bi bi-envelope" 
                        placeholder="nama@email.com" 
                        required 
                        autofocus 
                    />

                    <!-- Password Input -->
                    <x-input 
                        name="password" 
                        label="Kata Sandi" 
                        type="password" 
                        icon="bi bi-shield-lock" 
                        placeholder="Masukkan kata sandi akun" 
                        required 
                    />

                    <!-- Remember Me Checkbox -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember" style="border-color: #D8C5AE; cursor: pointer;">
                            <label class="form-check-label small text-secondary" for="remember" style="cursor: pointer; user-select: none;">
                                Ingat sesi saya
                            </label>
                        </div>
                    </div>

                    <!-- Submit CTA Button -->
                    <div class="d-grid mb-4">
                        <x-button type="submit" variant="mocha" class="py-3 shadow-sm" icon="bi bi-box-arrow-in-right">
                            Masuk ke Akun
                        </x-button>
                    </div>
                </form>

                <!-- Divider Minimalis -->
                <div class="position-relative text-center my-4">
                    <hr style="border-color: rgba(216, 197, 174, 0.4); margin: 0;">
                    <span class="position-absolute top-50 start-50 translate-middle px-3 bg-white text-muted small" style="font-size: 0.78rem;">
                        Demo Pengujian
                    </span>
                </div>

                <!-- Demo Account Selector (Pill Style) -->
                <div class="p-3 rounded-4" style="background-color: var(--color-champagne-light); border: 1px dashed var(--color-champagne);">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-semibold" style="font-size: 0.8rem; color: var(--color-charcoal);">
                            <i class="bi bi-lightning-charge-fill me-1" style="color: var(--color-mocha);"></i> Isi Otomatis Akun Demo
                        </span>
                        <x-demo-badge label="Prototipe" />
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm demo-pill-btn flex-fill text-center" data-email="customer@uyuy.test" data-role="Customer">
                            <i class="bi bi-person me-1"></i> Customer
                        </button>
                        <button type="button" class="btn btn-sm demo-pill-btn flex-fill text-center" data-email="admin@uyuy.test" data-role="Admin">
                            <i class="bi bi-shield-shaded me-1"></i> Admin
                        </button>
                        <button type="button" class="btn btn-sm demo-pill-btn flex-fill text-center" data-email="owner@uyuy.test" data-role="Owner">
                            <i class="bi bi-star-fill me-1" style="color: #D97706;"></i> Owner
                        </button>
                    </div>
                </div>

                <!-- Footer Link -->
                <div class="text-center mt-4 pt-2 small text-secondary">
                    Belum memiliki akun pelanggan? 
                    <a href="{{ route('register') }}" class="text-decoration-none fw-semibold" style="color: var(--color-mocha); transition: color 0.2s;">
                        Daftar sekarang
                    </a>
                </div>

            </x-card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const emailInput = document.getElementById('email');
        const passInput = document.getElementById('password');
        const demoBtns = document.querySelectorAll('.demo-pill-btn');

        demoBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                emailInput.value = this.getAttribute('data-email');
                passInput.value = 'password';
                
                // Micro-interaction feedback saat diklik
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });
    });
</script>
@endpush
