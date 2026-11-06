@extends('layouts.app')

@section('title', 'Pengajuan Pembatalan Pesanan #' . $order->order_number)

@section('content')
<!-- Header Banner -->
<div class="py-4 border-bottom" style="background: radial-gradient(circle at 10% 20%, #FDFBF7 0%, #F4ECE3 90%); border-color: rgba(216, 197, 174, 0.4) !important;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none" style="color: var(--color-mocha);">Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}" class="text-decoration-none" style="color: var(--color-mocha);">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customer.orders.index') }}" class="text-decoration-none" style="color: var(--color-mocha);">Pesanan Saya</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customer.orders.show', $order->id) }}" class="text-decoration-none" style="color: var(--color-mocha);">{{ $order->order_number }}</a></li>
                <li class="breadcrumb-item active text-secondary" aria-current="page">Pengajuan Pembatalan</li>
            </ol>
        </nav>
        <h1 class="h2 fw-bold font-heading mb-0 text-danger" style="letter-spacing: -0.02em;">
            <i class="bi bi-x-octagon me-2"></i> Pengajuan Pembatalan Pesanan
        </h1>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <x-card class="p-4 p-md-5 border-0 shadow">
                <!-- Warning Notice -->
                <div class="alert p-4 mb-4 rounded-4 small border-0 shadow-sm" style="background: rgba(245, 158, 11, 0.12); border-left: 4px solid #D97706 !important; line-height: 1.6;">
                    <strong class="text-dark d-block mb-1"><i class="bi bi-shield-exclamation me-1" style="color: #D97706;"></i> Ketentuan Pembatalan Pesanan Uyuy Jastip:</strong>
                    <ul class="mb-0 ps-3 text-secondary">
                        <li>Pengajuan ini tidak membatalkan pesanan secara otomatis seketika.</li>
                        <li>Permohonan Anda akan ditinjau dan diputuskan secara manual oleh staf <strong>Admin</strong>.</li>
                        <li>Status pesanan tetap aktif hingga putusan resmi disetujui oleh Admin di sistem.</li>
                    </ul>
                </div>

                <!-- Order Brief Bento -->
                <div class="p-3 rounded-4 mb-4 small" style="background: #FAF8F5; border: 1.5px solid rgba(216, 197, 174, 0.45);">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Nomor Faktur:</span>
                        <strong class="text-dark font-heading">{{ $order->order_number }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Total Tagihan:</span>
                        <strong class="text-dark font-heading">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-secondary">Status Saat Ini:</span>
                        <x-status-badge :status="$order->status" />
                    </div>
                </div>

                <form action="{{ route('customer.orders.cancellation.post', $order->id) }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="reason" class="form-label small fw-semibold text-secondary mb-1">
                            Alasan Mengajukan Pembatalan <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-uyuy p-1 @error('reason') border-danger @enderror">
                            <textarea class="form-control border-0 bg-transparent shadow-none" id="reason" name="reason" rows="4" required placeholder="Tuliskan alasan jelas mengapa Anda ingin membatalkan pesanan ini (contoh: salah memilih ukuran ml, perubahan aroma, perubahan alamat pengiriman)...">{{ old('reason') }}</textarea>
                        </div>
                        @error('reason')
                            <div class="text-danger small mt-1 ps-1" style="font-size: 0.82rem;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <a href="{{ route('customer.orders.show', $order->id) }}" class="btn btn-outline-secondary py-2 px-3">
                            <i class="bi bi-arrow-left me-1"></i> Batal & Kembali
                        </a>
                        <button type="submit" class="btn btn-danger py-2 px-4 shadow-sm rounded-3">
                            <i class="bi bi-send me-1"></i> Kirim Permohonan ke Admin
                        </button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</div>
@endsection
