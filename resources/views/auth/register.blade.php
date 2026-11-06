@extends('layouts.app')

@section('title', 'Daftar Akun Pelanggan')

@section('content')
<div class="container py-5 my-md-3">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-7">
            <!-- Floating Luxury Card -->
            <x-card class="p-4 p-md-5 border-0 shadow">
                <!-- Header Emblem -->
                <div class="text-center mb-4 pb-2">
                    <div class="d-inline-flex p-3 rounded-circle mb-3" style="background-color: var(--color-champagne-light); box-shadow: 0 4px 14px rgba(216, 197, 174, 0.4);">
                        <img src="{{ asset('images/logo-emblem.png') }}" alt="Uyuy Jastip" style="height: 52px; width: auto; object-fit: contain;">
                    </div>
                    <h3 class="font-heading mb-2" style="font-size: 1.75rem;">Pendaftaran Akun Pelanggan</h3>
                    <p class="text-secondary small mb-0">Buat akun untuk melakukan pre-order parfum dan memantau pesanan Anda secara transparan.</p>
                </div>

                <form action="{{ route('register.post') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <!-- Nama Lengkap -->
                        <div class="col-md-6">
                            <label for="name" class="form-label small fw-semibold text-secondary mb-1">
                                Nama Lengkap <span class="text-danger">*</span>
                            </label>
                            <div class="input-group-uyuy d-flex align-items-center @error('name') border-danger @enderror">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control form-control-uyuy" id="name" name="name" value="{{ old('name') }}" required placeholder="Nama lengkap Anda">
                            </div>
                            @error('name')
                                <div class="text-danger small mt-1 ps-1" style="font-size: 0.82rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label for="email" class="form-label small fw-semibold text-secondary mb-1">
                                Alamat Email <span class="text-danger">*</span>
                            </label>
                            <div class="input-group-uyuy d-flex align-items-center @error('email') border-danger @enderror">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control form-control-uyuy" id="email" name="email" value="{{ old('email') }}" required placeholder="contoh@gmail.com">
                            </div>
                            @error('email')
                                <div class="text-danger small mt-1 ps-1" style="font-size: 0.82rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- No WhatsApp -->
                        <div class="col-md-6">
                            <label for="phone_number" class="form-label small fw-semibold text-secondary mb-1">
                                No. WhatsApp / HP <span class="text-danger">*</span>
                            </label>
                            <div class="input-group-uyuy d-flex align-items-center @error('phone_number') border-danger @enderror">
                                <span class="input-group-text"><i class="bi bi-whatsapp"></i></span>
                                <input type="tel" class="form-control form-control-uyuy" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" required placeholder="08123456789">
                            </div>
                            <span class="text-muted d-block ps-1 mt-1" style="font-size: 0.72rem;">Digunakan admin untuk konfirmasi pesanan dan resi pengiriman.</span>
                            @error('phone_number')
                                <div class="text-danger small mt-1 ps-1" style="font-size: 0.82rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Kota -->
                        <div class="col-md-6">
                            <label for="city" class="form-label small fw-semibold text-secondary mb-1">
                                Kota / Kabupaten
                            </label>
                            <div class="input-group-uyuy d-flex align-items-center @error('city') border-danger @enderror">
                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                <input type="text" class="form-control form-control-uyuy" id="city" name="city" value="{{ old('city') }}" placeholder="Contoh: Jakarta Selatan">
                            </div>
                            @error('city')
                                <div class="text-danger small mt-1 ps-1" style="font-size: 0.82rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Alamat Lengkap -->
                        <div class="col-12">
                            <label for="address" class="form-label small fw-semibold text-secondary mb-1">
                                Alamat Pengiriman Lengkap
                            </label>
                            <div class="input-group-uyuy p-1 @error('address') border-danger @enderror">
                                <textarea class="form-control border-0 bg-transparent shadow-none" id="address" name="address" rows="2" placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kode pos...">{{ old('address') }}</textarea>
                            </div>
                            @error('address')
                                <div class="text-danger small mt-1 ps-1" style="font-size: 0.82rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Kata Sandi -->
                        <div class="col-md-6">
                            <label for="password" class="form-label small fw-semibold text-secondary mb-1">
                                Kata Sandi <span class="text-danger">*</span>
                            </label>
                            <div class="input-group-uyuy d-flex align-items-center @error('password') border-danger @enderror">
                                <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                <input type="password" class="form-control form-control-uyuy" id="password" name="password" required placeholder="Minimal 8 karakter">
                            </div>
                            @error('password')
                                <div class="text-danger small mt-1 ps-1" style="font-size: 0.82rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Konfirmasi Sandi -->
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label small fw-semibold text-secondary mb-1">
                                Konfirmasi Kata Sandi <span class="text-danger">*</span>
                            </label>
                            <div class="input-group-uyuy d-flex align-items-center">
                                <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                                <input type="password" class="form-control form-control-uyuy" id="password_confirmation" name="password_confirmation" required placeholder="Ulangi kata sandi">
                            </div>
                        </div>
                    </div>

                    <!-- Terms Checkbox -->
                    <div class="form-check mt-4 mb-4">
                        <input class="form-check-input" type="checkbox" id="terms" required style="border-color: #D8C5AE; cursor: pointer;">
                        <label class="form-check-label small text-secondary" for="terms" style="cursor: pointer; line-height: 1.5;">
                            Saya menyetujui ketentuan pre-order Uyuy Jastip dan memahami bahwa verifikasi transfer pembayaran dilakukan secara manual oleh admin.
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid mb-3">
                        <x-button type="submit" variant="mocha" class="py-3 shadow-sm" icon="bi bi-person-check">
                            Buat Akun Pelanggan Sekarang
                        </x-button>
                    </div>
                </form>

                <!-- Footer Link -->
                <div class="text-center mt-3 pt-3 border-top small text-secondary">
                    Sudah memiliki akun? 
                    <a href="{{ route('login') }}" class="text-decoration-none fw-semibold" style="color: var(--color-mocha);">
                        Masuk ke akun
                    </a>
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection
