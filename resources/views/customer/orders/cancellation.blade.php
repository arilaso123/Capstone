@extends('layouts.app')

@section('title', 'Pengajuan Pembatalan Pesanan #' . $order->order_number)

@section('content')
<div class="py-4 border-bottom" style="background-color: var(--color-warm-ivory);">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-secondary">Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}" class="text-secondary">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customer.orders.index') }}" class="text-secondary">Pesanan Saya</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customer.orders.show', $order->id) }}" class="text-secondary">{{ $order->order_number }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pengajuan Pembatalan</li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0 text-danger"><i class="bi bi-x-octagon me-2"></i> Pengajuan Pembatalan Pesanan</h1>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card card-uyuy p-4">
                <div class="alert alert-warning border-warning small mb-4">
                    <strong>Ketentuan Pembatalan Pesanan Uyuy Jastip:</strong>
                    <ul class="mb-0 mt-1 ps-3">
                        <li>Pengajuan pembatalan ini tidak serta-merta membatalkan pesanan secara otomatis.</li>
                        <li>Permohonan Anda akan diperiksa dan diputuskan oleh staf <strong>Admin</strong> terlebih dahulu.</li>
                        <li>Status pesanan akan tetap berjalan hingga keputusan resmi dicatat oleh Admin di sistem.</li>
                    </ul>
                </div>

                <!-- Order Brief -->
                <div class="p-3 rounded-3 bg-light border mb-4 small">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Nomor Pesanan:</span>
                        <strong>{{ $order->order_number }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Total Tagihan:</span>
                        <strong class="text-dark">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Status Saat Ini:</span>
                        <x-status-badge :status="$order->status" />
                    </div>
                </div>

                <form action="{{ route('customer.orders.cancellation.post', $order->id) }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="reason" class="form-label small fw-semibold">Alasan Mengajukan Pembatalan <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" rows="4" required placeholder="Tuliskan alasan jelas mengapa Anda ingin membatalkan pesanan ini (contoh: salah memilih varian aroma, perubahan alamat kirim, dsb)...">{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('customer.orders.show', $order->id) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Batal & Kembali
                        </a>
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="bi bi-send me-1"></i> Kirim Permohonan ke Admin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
