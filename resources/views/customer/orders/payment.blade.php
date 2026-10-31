@extends('layouts.app')

@section('title', 'Unggah Bukti Pembayaran #' . $order->order_number)

@section('content')
<div class="py-4 border-bottom" style="background-color: var(--color-warm-ivory);">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-secondary">Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}" class="text-secondary">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customer.orders.index') }}" class="text-secondary">Pesanan Saya</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customer.orders.show', $order->id) }}" class="text-secondary">{{ $order->order_number }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Unggah Bukti Transfer</li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0">Informasi & Unggah Bukti Pembayaran</h1>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Payment Instructions Card -->
            <div class="card card-uyuy p-4 mb-4">
                <h5 class="fw-bold font-heading mb-3">Petunjuk Transfer BCA Merchant</h5>
                <p class="small text-secondary mb-3">
                    Silakan selesaikan pembayaran pesanan Anda sejumlah tagihan yang tertera di bawah ini melalui kanal transfer Bank Central Asia (BCA) yang digunakan Uyuy Jastip:
                </p>

                <div class="p-3 rounded-3 bg-light border mb-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-sm-6">
                            <span class="small text-muted d-block">Nomor Rekening / Merchant BCA:</span>
                            <span class="fs-5 fw-bold text-dark font-monospace">8720-1928-33</span>
                            <span class="small text-muted d-block">a.n. <strong>UYUY JASTIP PARFUM</strong></span>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <span class="small text-muted d-block">Total yang Harus Dibayar:</span>
                            <span class="fs-4 fw-bold text-dark">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                            <span class="badge bg-secondary small">Pesanan #{{ $order->order_number }}</span>
                        </div>
                    </div>
                </div>

                <div class="alert alert-light border small text-muted mb-0">
                    <i class="bi bi-info-circle-fill text-primary me-1"></i> Mohon cantumkan nomor pesanan <code>{{ $order->order_number }}</code> pada kolom berita transfer untuk mempermudah pengecekan staf admin.
                </div>
            </div>

            <!-- Upload Form -->
            <div class="card card-uyuy p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold font-heading mb-0">Formulir Pengunggahan Bukti</h5>
                    @if($order->payment?->proof_file_path)
                        <span class="badge bg-info">Bukti Sebelumnya Telah Diunggah</span>
                    @endif
                </div>

                <form action="{{ route('customer.orders.payment.post', $order->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="payment_method" class="form-label small fw-semibold">Metode Pembayaran</label>
                        <input type="text" class="form-control bg-light" id="payment_method" name="payment_method" value="BCA Merchant (Transfer Non-Tunai)" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label small fw-semibold">Jumlah yang Ditransfer (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount', (int)$order->total_amount) }}" required>
                        @error('amount')
                            <div class="invalid-feedback small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="proof_file" class="form-label small fw-semibold">Berkas Foto / Tangkapan Layar Bukti Transfer <span class="text-danger">*</span></label>
                        <input type="file" class="form-control @error('proof_file') is-invalid @enderror" id="proof_file" name="proof_file" accept="image/jpeg,image/png,image/jpg,application/pdf" required>
                        <div class="form-text small">Format yang didukung: JPG, PNG, atau PDF. Ukuran berkas maksimal 2 MB. Pastikan struk atau bukti mutasi terlihat jelas.</div>
                        @error('proof_file')
                            <div class="invalid-feedback small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Retention & Operational Notice -->
                    <div class="p-3 rounded-2 bg-light border small text-muted mb-4">
                        <div class="fw-semibold text-dark mb-1"><i class="bi bi-shield-lock me-1"></i> Kebijakan Retensi Berkas:</div>
                        Sesuai standar operasional dan privasi mitra, berkas bukti transfer Anda disimpan dengan masa retensi maksimal <strong>1 (satu) bulan</strong> setelah proses verifikasi selesai.
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('customer.orders.show', $order->id) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Pesanan
                        </a>
                        <button type="submit" class="btn btn-mocha px-4">
                            <i class="bi bi-cloud-arrow-up me-1"></i> Kirim Bukti Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
