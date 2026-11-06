@extends('layouts.app')

@section('title', 'Riwayat Pesanan Saya')

@section('content')
<!-- Header Banner -->
<div class="py-4 border-bottom" style="background: radial-gradient(circle at 10% 20%, #FDFBF7 0%, #F4ECE3 90%); border-color: rgba(216, 197, 174, 0.4) !important;">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none" style="color: var(--color-mocha);">Beranda</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}" class="text-decoration-none" style="color: var(--color-mocha);">Dashboard</a></li>
                        <li class="breadcrumb-item active text-secondary" aria-current="page">Pesanan Saya</li>
                    </ol>
                </nav>
                <h1 class="h2 fw-bold font-heading mb-0" style="color: var(--color-charcoal); letter-spacing: -0.02em;">Riwayat Pesanan Pre-Order</h1>
            </div>
            <a href="{{ route('catalog') }}" class="btn btn-mocha py-2 px-3 shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-plus-lg"></i> Pesan Parfum Baru
            </a>
        </div>
    </div>
</div>

<div class="container py-5">
    <x-card class="p-4 p-md-5 border-0 shadow">
        <!-- Filter Status Pills -->
        <div class="d-flex flex-wrap align-items-center gap-2 mb-4 pb-3 border-bottom" style="border-color: rgba(216, 197, 174, 0.3) !important;">
            <span class="small fw-semibold text-secondary me-2" style="font-size: 0.8rem;">Status:</span>
            <a href="{{ route('customer.orders.index') }}" class="btn btn-sm demo-pill-btn {{ !request('status') ? 'active' : '' }}" style="{{ !request('status') ? 'background: var(--color-mocha); color: #fff; border-color: var(--color-mocha);' : '' }}">
                Semua
            </a>
            <a href="{{ route('customer.orders.index', ['status' => \App\Models\Order::STATUS_WAITING_PAYMENT]) }}"
               class="btn btn-sm demo-pill-btn {{ request('status') === \App\Models\Order::STATUS_WAITING_PAYMENT ? 'active' : '' }}"
               style="{{ request('status') === \App\Models\Order::STATUS_WAITING_PAYMENT ? 'background: var(--color-mocha); color: #fff; border-color: var(--color-mocha);' : '' }}">
                Menunggu Bayar
            </a>
            <a href="{{ route('customer.orders.index', ['status' => \App\Models\Order::STATUS_PAYMENT_VERIFIED]) }}"
               class="btn btn-sm demo-pill-btn {{ request('status') === \App\Models\Order::STATUS_PAYMENT_VERIFIED ? 'active' : '' }}"
               style="{{ request('status') === \App\Models\Order::STATUS_PAYMENT_VERIFIED ? 'background: var(--color-mocha); color: #fff; border-color: var(--color-mocha);' : '' }}">
                Terverifikasi
            </a>
            <a href="{{ route('customer.orders.index', ['status' => \App\Models\Order::STATUS_PROCESSING]) }}"
               class="btn btn-sm demo-pill-btn {{ request('status') === \App\Models\Order::STATUS_PROCESSING ? 'active' : '' }}"
               style="{{ request('status') === \App\Models\Order::STATUS_PROCESSING ? 'background: var(--color-mocha); color: #fff; border-color: var(--color-mocha);' : '' }}">
                Diproses
            </a>
            <a href="{{ route('customer.orders.index', ['status' => \App\Models\Order::STATUS_SHIPPED]) }}"
               class="btn btn-sm demo-pill-btn {{ request('status') === \App\Models\Order::STATUS_SHIPPED ? 'active' : '' }}"
               style="{{ request('status') === \App\Models\Order::STATUS_SHIPPED ? 'background: var(--color-mocha); color: #fff; border-color: var(--color-mocha);' : '' }}">
                Dikirim
            </a>
            <a href="{{ route('customer.orders.index', ['status' => \App\Models\Order::STATUS_COMPLETED]) }}"
               class="btn btn-sm demo-pill-btn {{ request('status') === \App\Models\Order::STATUS_COMPLETED ? 'active' : '' }}"
               style="{{ request('status') === \App\Models\Order::STATUS_COMPLETED ? 'background: var(--color-mocha); color: #fff; border-color: var(--color-mocha);' : '' }}">
                Selesai
            </a>
            <a href="{{ route('customer.orders.index', ['status' => \App\Models\Order::STATUS_CANCELLED]) }}"
               class="btn btn-sm demo-pill-btn {{ request('status') === \App\Models\Order::STATUS_CANCELLED ? 'active' : '' }}"
               style="{{ request('status') === \App\Models\Order::STATUS_CANCELLED ? 'background: var(--color-mocha); color: #fff; border-color: var(--color-mocha);' : '' }}">
                Dibatalkan
            </a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="small" style="background: #FAF8F5; color: var(--color-charcoal);">
                    <tr>
                        <th class="border-0 rounded-start-3 py-3">Nomor Faktur</th>
                        <th class="border-0 py-3">Periode PO</th>
                        <th class="border-0 py-3">Tanggal Pesan</th>
                        <th class="border-0 py-3">Total Tagihan</th>
                        <th class="border-0 py-3">Status Pesanan</th>
                        <th class="border-0 py-3">Bukti Transfer</th>
                        <th class="border-0 rounded-end-3 py-3 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr style="border-bottom: 1px solid rgba(216, 197, 174, 0.25);">
                            <td class="py-3">
                                <strong class="text-dark font-heading">{{ $order->order_number }}</strong>
                            </td>
                            <td class="py-3 small text-secondary">{{ $order->poPeriod?->name ?? '-' }}</td>
                            <td class="py-3 small text-secondary">{{ $order->created_at->format('d M Y') }}</td>
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
                                        <i class="bi bi-clock me-1"></i> Belum Diunggah
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
                                            Bayar
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2" style="color: var(--color-mocha);"></i>
                                    <h6 class="fw-bold text-dark">Belum Ada Pesanan Ditemukan</h6>
                                    <p class="small text-secondary mb-0">Tidak ada data transaksi yang sesuai dengan filter yang dipilih.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="d-flex justify-content-center mt-4 pt-2">
                {{ $orders->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection
