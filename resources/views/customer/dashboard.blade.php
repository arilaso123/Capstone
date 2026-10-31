@extends('layouts.app')

@section('title', 'Dashboard Pelanggan')

@section('content')
<div class="py-4 border-bottom" style="background-color: var(--color-warm-ivory);">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h1 class="h3 fw-bold mb-1">Halo, {{ auth()->user()->name }}!</h1>
                <p class="text-secondary small mb-0">Selamat datang di portal pre-order parfum Uyuy Jastip.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('catalog') }}" class="btn btn-mocha btn-sm d-flex align-items-center gap-1">
                    <i class="bi bi-bag-plus"></i> Pesan Parfum
                </a>
                <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-receipt"></i> Semua Pesanan
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <!-- Active PO Notification -->
    @if($activePo)
        <div class="card card-uyuy p-4 mb-4 border-champagne" style="background: linear-gradient(135deg, #FFFFFF 0%, #FAF6F0 100%);">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background-color: var(--color-champagne-light); color: var(--color-mocha);">
                        <i class="bi bi-calendar2-week fs-4"></i>
                    </div>
                    <div>
                        <div class="small text-muted fw-bold text-uppercase">Periode Pre-Order Berjalan:</div>
                        <h5 class="fw-bold mb-0">{{ $activePo->name }}</h5>
                        <span class="small text-secondary">Batas waktu pemesanan s.d. <strong>{{ $activePo->end_date->format('d M Y') }}</strong></span>
                    </div>
                </div>
                <div>
                    <x-status-badge :status="$activePo->status" />
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-secondary d-flex align-items-center justify-content-between card-uyuy mb-4">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-info-circle fs-5"></i>
                <span>Saat ini belum ada periode Pre-Order yang dibuka. Pantau terus jadwal pre-order berikutnya!</span>
            </div>
            <a href="{{ route('po.info') }}" class="btn btn-sm btn-outline-secondary">Lihat Jadwal</a>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-4 mb-5">
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-receipt"></i></div>
                <div>
                    <div class="stat-value">{{ $totalOrders }}</div>
                    <div class="stat-label">Total Pesanan Saya</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--status-waiting-bg); color: var(--status-waiting-color);"><i class="bi bi-clock"></i></div>
                <div>
                    <div class="stat-value">{{ $waitingPayment }}</div>
                    <div class="stat-label">Menunggu Bayar</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--status-processing-bg); color: var(--status-processing-color);"><i class="bi bi-box-seam"></i></div>
                <div>
                    <div class="stat-value">{{ $processing }}</div>
                    <div class="stat-label">Sedang Diproses</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--status-completed-bg); color: var(--status-completed-color);"><i class="bi bi-check2-circle"></i></div>
                <div>
                    <div class="stat-value">{{ $completed }}</div>
                    <div class="stat-label">Pesanan Selesai</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="card card-uyuy p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold font-heading mb-0">Pesanan Terbaru Anda</h5>
                <span class="small text-muted">Data riwayat transaksi pemesanan parfum milik akun Anda.</span>
            </div>
            <a href="{{ route('customer.orders.index') }}" class="btn btn-sm btn-outline-mocha">
                Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Periode PO</th>
                        <th>Total Nilai</th>
                        <th>Status Pesanan</th>
                        <th>Bukti Transfer</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                        <tr>
                            <td>
                                <strong class="text-dark">{{ $order->order_number }}</strong>
                                <div class="text-muted small">{{ $order->created_at->format('d M Y, H:i') }} WIB</div>
                            </td>
                            <td>{{ $order->poPeriod?->name ?? '-' }}</td>
                            <td class="fw-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            <td><x-status-badge :status="$order->status" /></td>
                            <td>
                                @if($order->payment && $order->payment->proof_file_path)
                                    <span class="badge {{ $order->payment->isValid() ? 'bg-success' : ($order->payment->isRejected() ? 'bg-danger' : 'bg-info') }}">
                                        {{ $order->payment->verification_status }}
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle me-1"></i> Belum Diunggah</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('customer.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                                    @if($order->status === \App\Models\Order::STATUS_WAITING_PAYMENT)
                                        <a href="{{ route('customer.orders.payment', $order->id) }}" class="btn btn-sm btn-mocha">Upload Bukti</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Belum ada pesanan yang tercatat. Silakan jelajahi <a href="{{ route('catalog') }}">katalog produk</a> kami.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
