@extends('layouts.admin')

@section('title', 'Dashboard Operasional Admin')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom" style="border-color: rgba(216, 197, 174, 0.3) !important;">
    <div>
        <h2 class="h3 fw-bold font-heading mb-1 text-dark" style="letter-spacing: -0.02em;">Dashboard Operasional Admin</h2>
        <span class="text-secondary small">Kelola pemeriksaan bukti transfer BCA, pergerakan tahapan pesanan, dan peninjauan pembatalan pelanggan.</span>
    </div>
    <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
        <span class="badge rounded-pill px-3 py-2" style="background: rgba(139, 107, 80, 0.12); color: var(--color-mocha); font-size: 0.78rem;">
            <i class="bi bi-clock me-1"></i> Jam Operasional: 09.00 - 19.00 WIB
        </span>
    </div>
</div>

<!-- Admin Stat Bento Grid -->
<div class="row g-4 mb-4">
    <!-- Pending Payments -->
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.payments.index') }}" class="text-decoration-none">
            <x-card interactive class="p-4 border-0 shadow h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold text-secondary" style="font-size: 0.8rem;">Verifikasi Pembayaran</span>
                    <div class="p-2 rounded-3" style="background: rgba(245, 158, 11, 0.12); color: #D97706;">
                        <i class="bi bi-credit-card fs-5"></i>
                    </div>
                </div>
                <h3 class="display-6 fw-bold font-heading text-dark mb-0">{{ $pendingPaymentsCount }}</h3>
                <span class="small text-danger" style="font-size: 0.75rem;">Perlu ditinjau segera</span>
            </x-card>
        </a>
    </div>

    <!-- Pending Cancellations -->
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.cancellations.index') }}" class="text-decoration-none">
            <x-card interactive class="p-4 border-0 shadow h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold text-secondary" style="font-size: 0.8rem;">Permohonan Batal</span>
                    <div class="p-2 rounded-3" style="background: rgba(239, 68, 68, 0.12); color: #DC2626;">
                        <i class="bi bi-x-octagon fs-5"></i>
                    </div>
                </div>
                <h3 class="display-6 fw-bold font-heading text-dark mb-0">{{ $pendingCancellationsCount }}</h3>
                <span class="small text-muted" style="font-size: 0.75rem;">Menunggu persetujuan</span>
            </x-card>
        </a>
    </div>

    <!-- Orders to Process -->
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.orders.index', ['status' => \App\Models\Order::STATUS_PAYMENT_VERIFIED]) }}" class="text-decoration-none">
            <x-card interactive class="p-4 border-0 shadow h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold text-secondary" style="font-size: 0.8rem;">Perlu Diproses</span>
                    <div class="p-2 rounded-3" style="background: rgba(147, 51, 234, 0.12); color: #7C3AED;">
                        <i class="bi bi-box-seam fs-5"></i>
                    </div>
                </div>
                <h3 class="display-6 fw-bold font-heading text-dark mb-0">{{ $ordersToProcessCount }}</h3>
                <span class="small text-muted" style="font-size: 0.75rem;">Belanja / Dikemas</span>
            </x-card>
        </a>
    </div>

    <!-- Orders Shipped -->
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.orders.index', ['status' => \App\Models\Order::STATUS_SHIPPED]) }}" class="text-decoration-none">
            <x-card interactive class="p-4 border-0 shadow h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold text-secondary" style="font-size: 0.8rem;">Sedang Dikirim</span>
                    <div class="p-2 rounded-3" style="background: rgba(20, 184, 166, 0.12); color: #0D9488;">
                        <i class="bi bi-truck fs-5"></i>
                    </div>
                </div>
                <h3 class="display-6 fw-bold font-heading text-dark mb-0">{{ $ordersShippedCount }}</h3>
                <span class="small text-muted" style="font-size: 0.75rem;">Dalam perjalanan ekspedisi</span>
            </x-card>
        </a>
    </div>
</div>

<!-- Quick Action Alert (If Payments Pending) -->
@if($pendingPaymentsCount > 0)
    <div class="alert p-3 mb-4 rounded-4 d-flex align-items-center justify-content-between shadow-sm border-0" style="background: rgba(244, 236, 227, 0.8); border-left: 4px solid var(--color-mocha) !important;">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: #FFFFFF; color: var(--color-mocha); box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
                <i class="bi bi-shield-check fs-5"></i>
            </div>
            <div>
                <strong class="text-dark">Tugas Pemeriksaan Pembayaran Manual BCA Merchant</strong>
                <div class="small text-secondary">Terdapat <strong>{{ $pendingPaymentsCount }}</strong> bukti transfer yang menunggu pencocokan mutasi bank dan konfirmasi verifikasi.</div>
            </div>
        </div>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-mocha px-3 py-2 shadow-sm">
            Buka Pemeriksaan &rarr;
        </a>
    </div>
@endif

<!-- Recent Orders Table -->
<x-card class="p-4 p-md-5 border-0 shadow">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom" style="border-color: rgba(216, 197, 174, 0.3) !important;">
        <div>
            <h5 class="fw-bold font-heading mb-1 text-dark">Pesanan Masuk Terbaru</h5>
            <span class="small text-secondary">Daftar transaksi pelanggan terkini yang memerlukan tindakan operasional.</span>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-mocha px-3 py-2 mt-2 mt-sm-0">
            Lihat Seluruh Pesanan <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="small" style="background: #FAF8F5; color: var(--color-charcoal);">
                <tr>
                    <th class="border-0 rounded-start-3 py-3">No. Faktur</th>
                    <th class="border-0 py-3">Nama Pelanggan</th>
                    <th class="border-0 py-3">No. WhatsApp</th>
                    <th class="border-0 py-3">Total Tagihan</th>
                    <th class="border-0 py-3">Status Pesanan</th>
                    <th class="border-0 py-3">Verifikasi Bayar</th>
                    <th class="border-0 rounded-end-3 py-3 text-end">Aksi Admin</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOrders as $order)
                    <tr style="border-bottom: 1px solid rgba(216, 197, 174, 0.25);">
                        <td class="py-3">
                            <strong class="text-dark font-heading">{{ $order->order_number }}</strong>
                            <div class="text-muted small" style="font-size: 0.72rem;">{{ $order->created_at->format('d M Y, H:i') }}</div>
                        </td>
                        <td class="py-3 fw-semibold text-dark">{{ $order->customer?->full_name ?? $order->recipient_name }}</td>
                        <td class="py-3 small text-secondary">{{ $order->recipient_phone }}</td>
                        <td class="py-3 fw-bold font-heading text-dark">
                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3"><x-status-badge :status="$order->status" /></td>
                        <td class="py-3">
                            @if($order->payment && $order->payment->proof_file_path)
                                <span class="badge rounded-pill px-3 py-1 {{ $order->payment->isValid() ? 'bg-success' : ($order->payment->isRejected() ? 'bg-danger' : 'bg-primary') }}">
                                    {{ $order->payment->verification_status }}
                                </span>
                            @else
                                <span class="badge rounded-pill px-3 py-1" style="background: rgba(245, 158, 11, 0.15); color: #B45309;">
                                    <i class="bi bi-clock me-1"></i> Menunggu Bukti
                                </span>
                            @endif
                        </td>
                        <td class="py-3 text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary px-2 py-1">
                                    Detail
                                </a>
                                @if($order->payment && $order->payment->proof_file_path && $order->payment->isPending())
                                    <a href="{{ route('admin.payments.verify', $order->payment->id) }}" class="btn btn-sm btn-mocha px-2 py-1 shadow-sm">
                                        Periksa Bukti
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            Belum ada pesanan terbaru dalam sistem.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-card>
@endsection
