@extends('layouts.admin')

@section('title', 'Dashboard Operasional Admin')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 fw-bold mb-1 font-heading">Dashboard Operasional Admin</h2>
        <span class="text-muted small">Kelola pemeriksaan bukti transfer, alur pesanan, dan penanganan permohonan pembatalan pelanggan.</span>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-secondary p-2 small"><i class="bi bi-clock me-1"></i> Jam Kerja: 09.00 - 19.00 WIB</span>
    </div>
</div>

<!-- Admin Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.payments.index') }}" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--status-waiting-bg); color: var(--status-waiting-color);">
                    <i class="bi bi-credit-card"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $pendingPaymentsCount }}</div>
                    <div class="stat-label">Pembayaran Perlu Verifikasi</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.cancellations.index') }}" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--status-cancelled-bg); color: var(--status-cancelled-color);">
                    <i class="bi bi-x-octagon"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $pendingCancellationsCount }}</div>
                    <div class="stat-label">Permohonan Pembatalan</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.orders.index', ['status' => \App\Models\Order::STATUS_PAYMENT_VERIFIED]) }}" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--status-processing-bg); color: var(--status-processing-color);">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $ordersToProcessCount }}</div>
                    <div class="stat-label">Pesanan Perlu Diproses</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.orders.index', ['status' => \App\Models\Order::STATUS_SHIPPED]) }}" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--status-shipped-bg); color: var(--status-shipped-color);">
                    <i class="bi bi-truck"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $ordersShippedCount }}</div>
                    <div class="stat-label">Pesanan Sedang Dikirim</div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Quick Action Alert -->
@if($pendingPaymentsCount > 0)
    <div class="alert alert-info d-flex align-items-center justify-content-between card-uyuy border-info mb-4">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-shield-check fs-3 text-info"></i>
            <div>
                <strong>Pemeriksaan Manual BCA Merchant</strong>
                <div class="small">Terdapat {{ $pendingPaymentsCount }} bukti transfer yang menunggu pencocokan mutasi dan pencatatan verifikasi.</div>
            </div>
        </div>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-info text-dark fw-semibold">
            Buka Pemeriksaan &rarr;
        </a>
    </div>
@endif

<!-- Recent Orders Table for Admin -->
<div class="card card-uyuy p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h5 class="fw-bold font-heading mb-0">Pesanan Masuk Terbaru</h5>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">
            Lihat Semua Pesanan <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>No. Pesanan</th>
                    <th>Nama Pelanggan</th>
                    <th>WhatsApp</th>
                    <th>Total</th>
                    <th>Status Pesanan</th>
                    <th>Verifikasi Pembayaran</th>
                    <th class="text-end">Aksi Admin</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOrders as $order)
                    <tr>
                        <td>
                            <strong class="text-dark">{{ $order->order_number }}</strong>
                            <div class="text-muted small">{{ $order->created_at->format('d M Y, H:i') }}</div>
                        </td>
                        <td>{{ $order->customer?->full_name ?? $order->recipient_name }}</td>
                        <td>{{ $order->recipient_phone }}</td>
                        <td class="fw-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        <td><x-status-badge :status="$order->status" /></td>
                        <td>
                            @if($order->payment && $order->payment->proof_file_path)
                                <span class="badge {{ $order->payment->isValid() ? 'bg-success' : ($order->payment->isRejected() ? 'bg-danger' : 'bg-primary') }}">
                                    {{ $order->payment->verification_status }}
                                </span>
                            @else
                                <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i> Menunggu Bukti</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-outline-secondary">Detail</a>
                                @if($order->payment && $order->payment->proof_file_path && $order->payment->isPending())
                                    <a href="{{ route('admin.payments.verify', $order->payment->id) }}" class="btn btn-mocha">Periksa Bukti</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            Belum ada pesanan terbaru.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
