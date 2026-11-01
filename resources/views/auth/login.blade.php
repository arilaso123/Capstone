@extends('layouts.app')

@section('title', 'Masuk Akun')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-5">
            <div class="card card-uyuy p-4 p-md-5 shadow-sm">
                <div class="text-center mb-4">
                    <img src="{{ asset('images/logo-emblem.png') }}" alt="Uyuy Jastip" class="mb-2" style="height: 60px; width: auto; object-fit: contain;">
                    <h3 class="fw-bold font-heading mb-1">Masuk ke Akun Anda</h3>
                    <p class="text-secondary small">Sistem Informasi Pre-Order Parfum Uyuy Jastip</p>
                </div>

                <form action="{{ route('login.post') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label small fw-semibold">Alamat Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control border-start-0 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="nama@email.com">
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="form-label small fw-semibold mb-0">Kata Sandi</label>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-shield-lock"></i></span>
                            <input type="password" class="form-control border-start-0 @error('password') is-invalid @enderror" id="password" name="password" required placeholder="Masukkan kata sandi">
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label small text-muted" for="remember">Ingat Sesi Saya</label>
                    </div>

                    <div class="d-grid gap-2 mb-4">
                        <button type="submit" class="btn btn-mocha py-2">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
                        </button>
                    </div>
                </form>

                <!-- Demo Account Selector for Rapid Evaluation -->
                <div class="p-3 rounded-3 bg-light border text-center">
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                        <x-demo-badge label="Demo Evaluasi Prototipe" />
                    </div>
                    <p class="small text-muted mb-2" style="font-size: 0.78rem;">Klik salah satu peran untuk mengisi data login otomatis:</p>
                    <div class="d-flex flex-wrap justify-content-center gap-1">
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-demo-fill" data-email="customer@uyuy.test" data-role="Customer">
                            <i class="bi bi-person me-1"></i> Customer
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info text-dark btn-demo-fill" data-email="admin@uyuy.test" data-role="Admin">
                            <i class="bi bi-shield-shaded me-1"></i> Admin
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning text-dark btn-demo-fill" data-email="owner@uyuy.test" data-role="Owner">
                            <i class="bi bi-star-fill text-warning me-1"></i> Owner
                        </button>
                    </div>
                </div>

                <div class="text-center mt-4 pt-3 border-top small text-muted">
                    Belum memiliki akun pelanggan? <a href="{{ route('register') }}" class="text-decoration-none fw-semibold" style="color: var(--color-mocha);">Daftar di sini</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const emailInput = document.getElementById('email');
        const passInput = document.getElementById('password');
        const demoBtns = document.querySelectorAll('.btn-demo-fill');

        demoBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                emailInput.value = this.getAttribute('data-email');
                passInput.value = 'password';
            });
        });
    });
</script>
@endpush

