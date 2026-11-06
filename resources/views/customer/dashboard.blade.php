@extends('layouts.app')

@section('title', 'Dashboard Pelanggan')

@section('content')
<!-- Header Banner -->
<div class="py-4 border-bottom" style="background: radial-gradient(circle at 10% 20%, #FDFBF7 0%, #F4ECE3 90%); border-color: rgba(216, 197, 174, 0.4) !important;">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none" style="color: var(--color-mocha);">Beranda</a></li>
                        <li class="breadcrumb-item active text-secondary" aria-current="page">Portal Pelanggan</li>
                    </ol>
                </nav>
                <h1 class="h2 fw-bold font-heading mb-1" style="color: var(--color-charcoal); letter-spacing: -0.02em;">
                    Halo, {{ auth()->user()->name }}!
                </h1>
                <p class="text-secondary small mb-0">Kelola pemesanan parfum pre-order dan pantau status pengiriman Anda.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('catalog') }}" class="btn btn-mocha py-2 px-3 shadow-sm d-flex align-items-center gap-2">
                    <i class="bi bi-bag-plus"></i> Pesan Parfum
                </a>
                <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-mocha py-2 px-3">
                    <i class="bi bi-receipt"></i> Semua Pesanan
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <!-- Active PO Status Bento Banner -->
    @if($activePo)
        <x-card class="p-4 mb-4 border-0 shadow">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex p-3 rounded-circle" style="background-color: var(--color-champagne-light); color: var(--color-mocha); box-shadow: 0 4px 12px rgba(216, 197, 174, 0.4);">
                        <i class="bi bi-calendar2-week fs-4"></i>
                    </div>
                    <div>
                        <span class="small fw-bold text-uppercase" style="color: var(--color-mocha); letter-spacing: 0.8px; font-size: 0.72rem;">
                            Batch Pre-Order Berjalan
                        </span>
                        <h4 class="fw-bold font-heading mb-1 text-dark">{{ $activePo->name }}</h4>
                        <span class="small text-secondary">
                            Batas waktu pemesanan s.d. <strong>{{ $activePo->end_date->format('d M Y') }}</strong> (Pukul 23.59 WIB)
                        </span>
                    </div>
                </div>
                <div>
                    <x-status-badge :status="$activePo->status" />
                </div>
            </div>
        </x-card>
    @else
        <div class="alert p-4 mb-4 rounded-4 d-flex align-items-center justify-content-between shadow-sm border-0" style="background: rgba(244, 236, 227, 0.7); border-left: 4px solid var(--color-mocha) !important;">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-info-circle fs-4" style="color: var(--color-mocha);"></i>
                <div>
                    <strong class="text-dark">Saat ini belum ada periode Pre-Order yang dibuka.</strong>
                    <div class="small text-secondary">Owner akan segera membuka siklus batch baru. Pantau jadwal atau jelajahi katalog parfum kami.</div>
                </div>
            </div>
            <a href="{{ route('po.info') }}" class="btn btn-sm btn-outline-mocha px-3 py-1">Lihat Jadwal</a>
        </div>
    @endif

    <!-- Statistics Bento Grid -->
    <div class="row g-4 mb-5">
        <div class="col-sm-6 col-lg-3">
            <x-card class="p-4 border-0 shadow h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold text-secondary" style="font-size: 0.8rem;">Total Pesanan</span>
                    <div class="p-2 rounded-3" style="background: #FAF8F5; color: var(--color-mocha);"><i class="bi bi-receipt fs-5"></i></div>
                </div>
                <h3 class="display-6 fw-bold font-heading text-dark mb-0">{{ $totalOrders }}</h3>
                <span class="small text-muted" style="font-size: 0.75rem;">Seluruh transaksi</span>
            </x-card>
        </div>

        <div class="col-sm-6 col-lg-3">
            <x-card class="p-4 border-0 shadow h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold text-secondary" style="font-size: 0.8rem;">Menunggu Bayar</span>
                    <div class="p-2 rounded-3" style="background: rgba(245, 158, 11, 0.12); color: #D97706;"><i class="bi bi-clock fs-5"></i></div>
                </div>
                <h3 class="display-6 fw-bold font-heading text-dark mb-0">{{ $waitingPayment }}</h3>
                <span class="small text-muted" style="font-size: 0.75rem;">Perlu unggah bukti</span>
            </x-card>
        </div>

        <div class="col-sm-6 col-lg-3">
            <x-card class="p-4 border-0 shadow h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold text-secondary" style="font-size: 0.8rem;">Sedang Diproses</span>
                    <div class="p-2 rounded-3" style="background: rgba(147, 51, 234, 0.12); color: #7C3AED;"><i class="bi bi-box-seam fs-5"></i></div>
                </div>
                <h3 class="display-6 fw-bold font-heading text-dark mb-0">{{ $processing }}</h3>
                <span class="small text-muted" style="font-size: 0.75rem;">Belanja / Dikemas</span>
            </x-card>
        </div>

        <div class="col-sm-6 col-lg-3">
            <x-card class="p-4 border-0 shadow h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold text-secondary" style="font-size: 0.8rem;">Pesanan Selesai</span>
                    <div class="p-2 rounded-3" style="background: rgba(22, 163, 74, 0.12); color: #16A34A;"><i class="bi bi-check2-circle fs-5"></i></div>
                </div>
                <h3 class="display-6 fw-bold font-heading text-dark mb-0">{{ $completed }}</h3>
                <span class="small text-muted" style="font-size: 0.75rem;">Tiba di tujuan</span>
            </x-card>
        </div>
    </div>

    <!-- Recent Orders Table Card -->
    <x-card class="p-4 p-md-5 border-0 shadow">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom" style="border-color: rgba(216, 197, 174, 0.3) !important;">
            <div>
                <h5 class="fw-bold font-heading mb-1 text-dark">Pesanan Terbaru Anda</h5>
                <p class="small text-secondary mb-0">Daftar transaksi pesanan parfum yang terdaftar pada akun Anda.</p>
            </div>
            <a href="{{ route('customer.orders.index') }}" class="btn btn-sm btn-outline-mocha px-3 py-2 mt-2 mt-sm-0">
                Lihat Semua Pesanan <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="small" style="background: #FAF8F5; color: var(--color-charcoal);">
                    <tr>
                        <th class="border-0 rounded-start-3 py-3">No. Faktur</th>
                        <th class="border-0 py-3">Periode PO</th>
                        <th class="border-0 py-3">Total Belanja</th>
                        <th class="border-0 py-3">Status Pesanan</th>
                        <th class="border-0 py-3">Bukti Transfer</th>
                        <th class="border-0 rounded-end-3 py-3 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                        <tr style="border-bottom: 1px solid rgba(216, 197, 174, 0.25);">
                            <td class="py-3">
                                <strong class="text-dark font-heading">{{ $order->order_number }}</strong>
                                <div class="text-muted small" style="font-size: 0.75rem;">{{ $order->created_at->format('d M Y, H:i') }} WIB</div>
                            </td>
                            <td class="py-3 small text-secondary">{{ $order->poPeriod?->name ?? '-' }}</td>
                            <td class="py-3 fw-bold font-heading text-dark">
                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3">
                                <x-status-badge :status="$order->status" />
                            </td>
                            <td class="py-3">
                                @if($order->payment && $order->payment->proof_file_path)
                                    <span class="badge rounded-pill px-3 py-1 {{ $order->payment->isValid() ? 'bg-success' : ($order->payment->isRejected() ? 'bg-danger' : 'bg-info') }}">
                                        {{ $order->payment->verification_status }}
                                    </span>
                                @else
                                    <span class="badge rounded-pill px-3 py-1" style="background: rgba(245, 158, 11, 0.15); color: #B45309;">
                                        <i class="bi bi-exclamation-circle me-1"></i> Belum Diunggah
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('customer.orders.show', $order->id) }}" class="btn btn-sm btn-outline-mocha px-3 py-1">
                                        Detail
                                    </a>
                                    @if($order->status === \App\Models\Order::STATUS_WAITING_PAYMENT)
                                        <a href="{{ route('customer.orders.payment', $order->id) }}" class="btn btn-sm btn-mocha px-3 py-1">
                                            Upload Bukti
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2" style="color: var(--color-mocha);"></i>
                                    <h6 class="fw-bold text-dark">Belum Ada Pesanan yang Tercatat</h6>
                                    <p class="small text-secondary mb-3">Mulai buat pesanan titip parfum racikan In Parfume di katalog kami.</p>
                                    <a href="{{ route('catalog') }}" class="btn btn-sm btn-mocha px-3 py-2">
                                        Jelajahi Katalog Parfum &rarr;
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
@endsection
