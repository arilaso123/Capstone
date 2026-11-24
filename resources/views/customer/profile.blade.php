@extends('layouts.app')

@section('title', 'Profil & Alamat Pengiriman — Uyuy Jastip')

@section('content')
<div class="container py-5 customer-checkout-page">
    <nav aria-label="Breadcrumb" class="customer-checkout-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Profil &amp; Alamat</li>
        </ol>
    </nav>

    <x-customer-portal-nav />

    <header class="customer-checkout-header">
        <div>
            <p class="home-eyebrow">AKUN PELANGGAN</p>
            <h1>Profil &amp; alamat pengiriman</h1>
            <p>Perbarui data diri dan alamat default Anda agar pengisian checkout pada batch pre-order berikutnya lebih cepat.</p>
        </div>
    </header>

    <section class="customer-profile-card" aria-labelledby="profileFormTitle">
        <h2 id="profileFormTitle" class="visually-hidden">Formulir Profil Pelanggan</h2>

        <form action="{{ route('customer.profile.update') }}" method="POST" class="customer-checkout-form">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="customer-checkout-field">
                        <label for="full_name">Nama lengkap <span aria-hidden="true">*</span></label>
                        <input id="full_name" type="text" name="full_name" value="{{ old('full_name', $customer->full_name ?? auth()->user()->name) }}" autocomplete="name" required>
                        @error('full_name')<p class="customer-checkout-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="customer-checkout-field">
                        <label for="email_readonly">Alamat email</label>
                        <input id="email_readonly" type="email" value="{{ auth()->user()->email }}" disabled>
                        <small>Email akun digunakan untuk login dan tidak dapat diubah di halaman ini.</small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="customer-checkout-field">
                        <label for="phone_number">Nomor WhatsApp aktif <span aria-hidden="true">*</span></label>
                        <input id="phone_number" type="tel" name="phone_number" value="{{ old('phone_number', $customer->phone_number ?? '') }}" placeholder="081234567890" autocomplete="tel" required>
                        <small>Digunakan admin untuk konfirmasi pembayaran dan pengiriman resi.</small>
                        @error('phone_number')<p class="customer-checkout-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="customer-checkout-field">
                        <label for="city">Kota / Kabupaten <span aria-hidden="true">*</span></label>
                        <input id="city" type="text" name="city" value="{{ old('city', $customer->city ?? '') }}" placeholder="Contoh: Jakarta Selatan" autocomplete="address-level2" required>
                        @error('city')<p class="customer-checkout-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="customer-checkout-field">
                        <label for="default_address">Alamat pengiriman default <span aria-hidden="true">*</span></label>
                        <textarea id="default_address" name="default_address" rows="3" placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan" autocomplete="street-address" required>{{ old('default_address', $customer->default_address ?? '') }}</textarea>
                        @error('default_address')<p class="customer-checkout-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="customer-checkout-field">
                        <label for="postal_code">Kode pos <span class="customer-checkout-optional">Opsional</span></label>
                        <input id="postal_code" type="text" name="postal_code" value="{{ old('postal_code', $customer->postal_code ?? '') }}" inputmode="numeric" maxlength="5" pattern="[0-9]{5}" placeholder="12430" autocomplete="postal-code">
                        <small>5 digit angka kode pos.</small>
                        @error('postal_code')<p class="customer-checkout-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-dark px-4">
                    <i class="bi bi-check2-circle me-1" aria-hidden="true"></i> Simpan perubahan
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
