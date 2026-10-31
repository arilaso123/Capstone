@extends('layouts.owner')

@section('title', 'Dashboard Eksekutif Owner')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 fw-bold mb-1 font-heading">Dashboard Eksekutif Pemilik Usaha</h2>
        <span class="text-muted small">Ringkasan bisnis pre-order parfum, kontrol periode PO, dan pemantauan menyeluruh.</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('owner.po-periods.create') }}" class="btn btn-mocha btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Buat Periode PO Baru
        </a>
        <a href="{{ route('owner.products.create') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-box-seam me-1"></i> Tambah Produk
        </a>
    </div>
</div>

<!-- Active PO Control Card -->
@if($activePo)
    <div class="card card-uyuy p-4 mb-4 border-champagne" style="background: linear-gradient(135deg, #FFFFFF 0%, #FAF5EE 100%);">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background-color: var(--color-champagne-light); color: var(--color-mocha); width: 56px; height: 56px; font-size: 1.6rem;">
                    <i class="bi bi-calendar2-week-fill"></i>
                </div>
                <div>
                    <div class="small text-muted fw-bold text-uppercase">Kontrol Periode Pre-Order Utama:</div>
                    <h4 class="fw-bold mb-0">{{ $activePo->name }}</h4>
                    <span class="small text-secondary">
                        Periode: <strong>{{ $activePo->start_date->format('d M Y') }}</strong> s.d. <strong>{{ $activePo->end_date->format('d M Y') }}</strong> &bull; Total Pesanan Batch: <strong>{{ $activePo->orders()->count() }} Pesanan</strong>
                    </span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <x-status-badge status="Dibuka" />
                <form action="{{ route('owner.po-periods.status.toggle', $activePo->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tutup periode PO ini? Pelanggan tidak akan dapat membuat pesanan baru setelah PO ditutup.')">
                        <i class="bi bi-lock-fill me-1"></i> Tutup Periode PO Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>
@else
    <div class="card card-uyuy p-4 mb-4 border-secondary" style="background: #F9FAFB;">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-secondary text-white" style="width: 56px; height: 56px; font-size: 1.6rem;">
                    <i class="bi bi-lock-fill"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0">Tidak Ada Periode PO yang Sedang Dibuka</h5>
                    <span class="small text-muted">Pelanggan tidak dapat melakukan checkout sampai periode PO baru dibuka.</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('owner.po-periods.index') }}" class="btn btn-outline-secondary btn-sm">Lihat Semua PO</a>
                <a href="{{ route('owner.po-periods.create') }}" class="btn btn-mocha btn-sm">Buka PO Baru</a>
            </div>
        </div>
    </div>
@endif

<!-- High Level Business Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-receipt-cutoff"></i></div>
            <div>
                <div class="stat-value">{{ $totalOrdersCount }}</div>
                <div class="stat-label">Total Pesanan Kumulatif</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('owner.customers.index') }}" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon" style="background: #E0F2FE; color: #0369A1;"><i class="bi bi-people"></i></div>
                <div>
                    <div class="stat-value">{{ $totalCustomersCount }}</div>
                    <div class="stat-label">Total Pelanggan Terdaftar</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: #DCFCE7; color: #166534;"><i class="bi bi-cash-stack"></i></div>
            <div>
                <div class="stat-value fs-5">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                <div class="stat-label">Total Nilai Transaksi Selesai/Aktif</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('owner.products.index') }}" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--color-champagne-light); color: var(--color-mocha);"><i class="bi bi-boxes"></i></div>
                <div>
                    <div class="stat-value">{{ $activeProductsCount }}</div>
                    <div class="stat-label">Katalog Produk Aktif</div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Orders Breakdown By Status -->
    <div class="col-lg-6">
        <div class="card card-uyuy p-4 h-100">
            <h5 class="fw-bold font-heading mb-3">Distribusi Status Pesanan ({{ $activePo ? $activePo->name : 'Semua PO' }})</h5>
            <div class="list-group list-group-flush small">
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-clock text-warning"></i> Menunggu Pembayaran
                    </span>
                    <span class="badge bg-warning text-dark rounded-pill">{{ $statusBreakdown['waiting'] }} pesanan</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-check2-circle text-primary"></i> Pembayaran Diverifikasi
                    </span>
                    <span class="badge bg-primary rounded-pill">{{ $statusBreakdown['verified'] }} pesanan</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-box-seam" style="color: #6B21A8;"></i> Diproses (Belanja ke In Parfume)
                    </span>
                    <span class="badge rounded-pill text-white" style="background: #6B21A8;">{{ $statusBreakdown['processing'] }} pesanan</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-truck text-info"></i> Barang Dikirim
                    </span>
                    <span class="badge bg-info text-dark rounded-pill">{{ $statusBreakdown['shipped'] }} pesanan</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-patch-check-fill text-success"></i> Selesai
                    </span>
                    <span class="badge bg-success rounded-pill">{{ $statusBreakdown['completed'] }} pesanan</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-x-circle text-danger"></i> Dibatalkan
                    </span>
                    <span class="badge bg-danger rounded-pill">{{ $statusBreakdown['cancelled'] }} pesanan</span>
                </div>
            </div>
            <div class="mt-4 pt-2 border-top">
                <a href="{{ route('owner.recap.index') }}" class="btn btn-mocha btn-sm w-100">
                    <i class="bi bi-file-earmark-bar-graph me-1"></i> Buka Rekapitulasi Lengkap Batch Ini
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Management Shortcuts -->
    <div class="col-lg-6">
        <div class="card card-uyuy p-4 h-100">
            <h5 class="fw-bold font-heading mb-3">Tindakan Cepat Pemilik Usaha</h5>
            <div class="d-grid gap-3">
                <a href="{{ route('owner.po-periods.index') }}" class="p-3 rounded-3 bg-light border text-decoration-none text-dark d-flex align-items-center justify-content-between hover-shadow">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-calendar-plus fs-4 text-mocha"></i>
                        <div>
                            <strong class="d-block">Kelola Periode PO</strong>
                            <span class="small text-muted">Buka periode baru atau atur jadwal siklus 1 minggu.</span>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>

                <a href="{{ route('owner.products.index') }}" class="p-3 rounded-3 bg-light border text-decoration-none text-dark d-flex align-items-center justify-content-between hover-shadow">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-boxes fs-4 text-mocha"></i>
                        <div>
                            <strong class="d-block">Katalog Produk In Parfume</strong>
                            <span class="small text-muted">Tambah varian parfum baru, ubah harga, atau nonaktifkan.</span>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>

                <a href="{{ route('owner.customers.index') }}" class="p-3 rounded-3 bg-light border text-decoration-none text-dark d-flex align-items-center justify-content-between hover-shadow">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-person-lines-fill fs-4 text-mocha"></i>
                        <div>
                            <strong class="d-block">Database Pelanggan Jastip</strong>
                            <span class="small text-muted">Lihat data nomor WhatsApp dan riwayat belanja pelanggan.</span>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
