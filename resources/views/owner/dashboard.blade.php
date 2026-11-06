@extends('layouts.owner')

@section('title', 'Dashboard Eksekutif Owner')

@section('content')
<!-- Header Banner -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom" style="border-color: rgba(216, 197, 174, 0.3) !important;">
    <div>
        <h2 class="h3 fw-bold font-heading mb-1 text-dark" style="letter-spacing: -0.02em;">Dashboard Eksekutif Pemilik Usaha</h2>
        <span class="text-secondary small">Ringkasan bisnis pre-order parfum, kontrol siklus PO, dan pemantauan menyeluruh.</span>
    </div>
    <div class="d-flex gap-2 mt-2 mt-sm-0">
        <a href="{{ route('owner.po-periods.create') }}" class="btn btn-mocha btn-sm py-2 px-3 shadow-sm d-flex align-items-center gap-1">
            <i class="bi bi-plus-circle"></i> Buka Periode PO Baru
        </a>
        <a href="{{ route('owner.products.create') }}" class="btn btn-outline-mocha btn-sm py-2 px-3 d-flex align-items-center gap-1">
            <i class="bi bi-box-seam"></i> Tambah Produk
        </a>
    </div>
</div>

<!-- Active PO Control Banner -->
@if($activePo)
    <x-card class="p-4 mb-4 border-0 shadow">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="d-inline-flex p-3 rounded-circle" style="background-color: var(--color-champagne-light); color: var(--color-mocha); box-shadow: 0 4px 12px rgba(216, 197, 174, 0.4);">
                    <i class="bi bi-calendar2-week-fill fs-3"></i>
                </div>
                <div>
                    <span class="small fw-bold text-uppercase" style="color: var(--color-mocha); letter-spacing: 0.8px; font-size: 0.72rem;">
                        Kontrol Periode Pre-Order Aktif
                    </span>
                    <h4 class="fw-bold font-heading mb-1 text-dark">{{ $activePo->name }}</h4>
                    <span class="small text-secondary">
                        Periode: <strong>{{ $activePo->start_date->format('d M Y') }}</strong> s.d. <strong>{{ $activePo->end_date->format('d M Y') }}</strong> &bull; Terdaftar: <strong>{{ $activePo->orders()->count() }} Pesanan</strong>
                    </span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <x-status-badge status="Dibuka" />
                <form action="{{ route('owner.po-periods.status.toggle', $activePo->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger px-3 py-2 rounded-3" onclick="return confirm('Tutup periode PO ini? Pelanggan tidak akan dapat membuat pesanan baru setelah PO ditutup.')">
                        <i class="bi bi-lock-fill me-1"></i> Tutup Periode PO Sekarang
                    </button>
                </form>
            </div>
        </div>
    </x-card>
@else
    <x-card class="p-4 mb-4 border-0 shadow" style="background: #FAF8F5;">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 rounded-circle bg-secondary text-white" style="width: 52px; height: 52px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-lock-fill fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold font-heading mb-1 text-dark">Tidak Ada Periode PO yang Sedang Dibuka</h5>
                    <span class="small text-secondary">Pelanggan tidak dapat melakukan checkout sampai periode PO baru dibuka.</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('owner.po-periods.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">Lihat Semua PO</a>
                <a href="{{ route('owner.po-periods.create') }}" class="btn btn-mocha btn-sm px-3 py-2 shadow-sm">Buka PO Baru</a>
            </div>
        </div>
    </x-card>
@endif

<!-- High-Level Business Metric Bento Grid -->
<div class="row g-4 mb-4">
    <!-- Total Orders -->
    <div class="col-sm-6 col-xl-3">
        <x-card class="p-4 border-0 shadow h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small fw-semibold text-secondary" style="font-size: 0.8rem;">Total Pesanan</span>
                <div class="p-2 rounded-3" style="background: #FAF8F5; color: var(--color-mocha);">
                    <i class="bi bi-receipt-cutoff fs-5"></i>
                </div>
            </div>
            <h3 class="display-6 fw-bold font-heading text-dark mb-0">{{ $totalOrdersCount }}</h3>
            <span class="small text-muted" style="font-size: 0.75rem;">Kumulatif seluruh batch</span>
        </x-card>
    </div>

    <!-- Registered Customers -->
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('owner.customers.index') }}" class="text-decoration-none">
            <x-card interactive class="p-4 border-0 shadow h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold text-secondary" style="font-size: 0.8rem;">Pelanggan Terdaftar</span>
                    <div class="p-2 rounded-3" style="background: rgba(3, 105, 161, 0.12); color: #0369A1;">
                        <i class="bi bi-people fs-5"></i>
                    </div>
                </div>
                <h3 class="display-6 fw-bold font-heading text-dark mb-0">{{ $totalCustomersCount }}</h3>
                <span class="small text-muted" style="font-size: 0.75rem;">Database pelanggan</span>
            </x-card>
        </a>
    </div>

    <!-- Revenue -->
    <div class="col-sm-6 col-xl-3">
        <x-card class="p-4 border-0 shadow h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small fw-semibold text-secondary" style="font-size: 0.8rem;">Total Nilai Transaksi</span>
                <div class="p-2 rounded-3" style="background: rgba(22, 163, 74, 0.12); color: #16A34A;">
                    <i class="bi bi-cash-stack fs-5"></i>
                </div>
            </div>
            <h4 class="fw-bold font-heading text-dark mb-0 fs-3">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h4>
            <span class="small text-muted" style="font-size: 0.75rem;">Omzet pesanan valid</span>
        </x-card>
    </div>

    <!-- Active Products -->
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('owner.products.index') }}" class="text-decoration-none">
            <x-card interactive class="p-4 border-0 shadow h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold text-secondary" style="font-size: 0.8rem;">Katalog Parfum</span>
                    <div class="p-2 rounded-3" style="background: var(--color-champagne-light); color: var(--color-mocha);">
                        <i class="bi bi-boxes fs-5"></i>
                    </div>
                </div>
                <h3 class="display-6 fw-bold font-heading text-dark mb-0">{{ $activeProductsCount }}</h3>
                <span class="small text-muted" style="font-size: 0.75rem;">Varian aktif In Parfume</span>
            </x-card>
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Orders Breakdown By Status -->
    <div class="col-lg-6">
        <x-card class="p-4 p-md-5 border-0 shadow h-100">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom" style="border-color: rgba(216, 197, 174, 0.3) !important;">
                <h5 class="fw-bold font-heading mb-0 text-dark">Distribusi Status Pesanan</h5>
                <span class="small text-muted" style="font-size: 0.75rem;">{{ $activePo ? $activePo->name : 'Semua PO' }}</span>
            </div>

            <div class="list-group list-group-flush small">
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 bg-transparent" style="border-color: rgba(216, 197, 174, 0.25);">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-clock fs-5 text-warning"></i> Menunggu Pembayaran
                    </span>
                    <span class="badge rounded-pill px-3 py-1" style="background: rgba(245, 158, 11, 0.15); color: #B45309;">
                        {{ $statusBreakdown['waiting'] }} pesanan
                    </span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 bg-transparent" style="border-color: rgba(216, 197, 174, 0.25);">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-check2-circle fs-5 text-primary"></i> Pembayaran Diverifikasi
                    </span>
                    <span class="badge rounded-pill px-3 py-1" style="background: rgba(30, 64, 175, 0.12); color: #1E40AF;">
                        {{ $statusBreakdown['verified'] }} pesanan
                    </span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 bg-transparent" style="border-color: rgba(216, 197, 174, 0.25);">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-box-seam fs-5" style="color: #6B21A8;"></i> Diproses (Belanja ke In Parfume)
                    </span>
                    <span class="badge rounded-pill px-3 py-1 text-white" style="background: #6B21A8;">
                        {{ $statusBreakdown['processing'] }} pesanan
                    </span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 bg-transparent" style="border-color: rgba(216, 197, 174, 0.25);">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-truck fs-5 text-info"></i> Barang Dikirim
                    </span>
                    <span class="badge rounded-pill px-3 py-1" style="background: rgba(13, 148, 136, 0.12); color: #0D9488;">
                        {{ $statusBreakdown['shipped'] }} pesanan
                    </span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 bg-transparent" style="border-color: rgba(216, 197, 174, 0.25);">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-patch-check-fill fs-5 text-success"></i> Selesai
                    </span>
                    <span class="badge rounded-pill px-3 py-1" style="background: rgba(22, 163, 74, 0.12); color: #16A34A;">
                        {{ $statusBreakdown['completed'] }} pesanan
                    </span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 bg-transparent" style="border-color: rgba(216, 197, 174, 0.25);">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-x-circle fs-5 text-danger"></i> Dibatalkan
                    </span>
                    <span class="badge rounded-pill px-3 py-1" style="background: rgba(239, 68, 68, 0.12); color: #DC2626;">
                        {{ $statusBreakdown['cancelled'] }} pesanan
                    </span>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top" style="border-color: rgba(216, 197, 174, 0.3) !important;">
                <a href="{{ route('owner.recap.index') }}" class="btn btn-mocha w-100 py-3 shadow-sm d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-file-earmark-bar-graph"></i> Buka Rekapitulasi Lengkap Batch Ini
                </a>
            </div>
        </x-card>
    </div>

    <!-- Quick Management Action Shortcuts -->
    <div class="col-lg-6">
        <x-card class="p-4 p-md-5 border-0 shadow h-100">
            <h5 class="fw-bold font-heading mb-4 pb-2 border-bottom text-dark" style="border-color: rgba(216, 197, 174, 0.3) !important;">
                Aksi Cepat Pemilik Usaha
            </h5>

            <div class="d-grid gap-3">
                <a href="{{ route('owner.po-periods.index') }}" class="p-3 rounded-4 text-decoration-none text-dark d-flex align-items-center justify-content-between" style="background: #FAF8F5; border: 1.5px solid rgba(216, 197, 174, 0.45); transition: all 0.25s ease;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 rounded-circle" style="background: rgba(139, 107, 80, 0.12); color: var(--color-mocha);">
                            <i class="bi bi-calendar-plus fs-4"></i>
                        </div>
                        <div>
                            <strong class="d-block font-heading text-dark fs-6">Kelola Periode Pre-Order</strong>
                            <span class="small text-secondary">Buka periode baru atau sesuaikan siklus PO 1 minggu.</span>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>

                <a href="{{ route('owner.products.index') }}" class="p-3 rounded-4 text-decoration-none text-dark d-flex align-items-center justify-content-between" style="background: #FAF8F5; border: 1.5px solid rgba(216, 197, 174, 0.45); transition: all 0.25s ease;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 rounded-circle" style="background: var(--color-champagne-light); color: var(--color-mocha);">
                            <i class="bi bi-boxes fs-4"></i>
                        </div>
                        <div>
                            <strong class="d-block font-heading text-dark fs-6">Katalog Produk In Parfume</strong>
                            <span class="small text-secondary">Tambah varian parfum baru, atur varian ukuran & harga titip.</span>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>

                <a href="{{ route('owner.customers.index') }}" class="p-3 rounded-4 text-decoration-none text-dark d-flex align-items-center justify-content-between" style="background: #FAF8F5; border: 1.5px solid rgba(216, 197, 174, 0.45); transition: all 0.25s ease;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 rounded-circle" style="background: rgba(3, 105, 161, 0.12); color: #0369A1;">
                            <i class="bi bi-person-lines-fill fs-4"></i>
                        </div>
                        <div>
                            <strong class="d-block font-heading text-dark fs-6">Database Pelanggan Jastip</strong>
                            <span class="small text-secondary">Akses data WhatsApp dan riwayat frekuensi belanja pelanggan.</span>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>
            </div>
        </x-card>
    </div>
</div>
@endsection
