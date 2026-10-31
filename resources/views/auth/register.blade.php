@extends('layouts.app')

@section('title', 'Daftar Akun Pelanggan')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-7">
            <div class="card card-uyuy p-4 p-md-5 shadow-sm">
                <div class="text-center mb-4">
                    <div class="stat-icon mx-auto mb-2" style="background: var(--color-champagne-light); color: var(--color-mocha);">
                        <i class="bi bi-person-plus-fill fs-4"></i>
                    </div>
                    <h3 class="fw-bold font-heading mb-1">Pendaftaran Akun Pelanggan</h3>
                    <p class="text-secondary small">Buat akun untuk melakukan pre-order parfum dan memantau pesanan Anda.</p>
                </div>

                <form action="{{ route('register.post') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="Nama lengkap Anda">
                            @error('name')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label small fw-semibold">Alamat Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required placeholder="contoh@gmail.com">
                            @error('email')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="phone_number" class="form-label small fw-semibold">Nomor WhatsApp / Telepon <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('phone_number') is-invalid @enderror" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" required placeholder="Contoh: 08123456789">
                            <span class="text-muted" style="font-size: 0.72rem;">Digunakan admin untuk konfirmasi pesanan dan pengiriman.</span>
                            @error('phone_number')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="city" class="form-label small fw-semibold">Kota / Kabupaten</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city') }}" placeholder="Contoh: Jakarta Selatan">
                            @error('city')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="address" class="form-label small fw-semibold">Alamat Pengiriman Lengkap</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2" placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan...">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label small fw-semibold">Kata Sandi <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required placeholder="Minimal 8 karakter">
                            @error('password')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label small fw-semibold">Konfirmasi Kata Sandi <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required placeholder="Ulangi kata sandi">
                        </div>
                    </div>

                    <div class="form-check mt-4 mb-4">
                        <input class="form-check-input" type="checkbox" id="terms" required>
                        <label class="form-check-label small text-muted" for="terms">
                            Saya menyetujui ketentuan pre-order Uyuy Jastip dan memahami bahwa verifikasi pembayaran dilakukan manual oleh admin.
                        </label>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-mocha py-2">
                            <i class="bi bi-person-check me-1"></i> Buat Akun Pelanggan
                        </button>
                    </div>
                </form>

                <div class="text-center mt-3 pt-3 border-top small text-muted">
                    Sudah memiliki akun? <a href="{{ route('login') }}" class="text-decoration-none fw-semibold" style="color: var(--color-mocha);">Masuk di sini</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

