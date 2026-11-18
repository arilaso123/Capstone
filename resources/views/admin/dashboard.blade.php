@extends('layouts.admin')

@section('title', 'Ringkasan Operasional Admin')

@section('content')
<section class="admin-dashboard-page" aria-labelledby="adminDashboardTitle">
    <nav class="workspace-breadcrumb" aria-label="Breadcrumb">
        <span>Admin</span>
        <span aria-hidden="true">/</span>
        <span>Ringkasan Operasional</span>
    </nav>

    <header class="admin-dashboard-header">
        <div>
            <p class="owner-recap-eyebrow">ADMIN / OPERASIONAL</p>
            <h1 id="adminDashboardTitle">Ringkasan operasional</h1>
            <p class="admin-dashboard-header-subtitle">Tugas verifikasi pembayaran, pesanan yang perlu diproses, dan permohonan pembatalan.</p>
        </div>
        <span class="admin-dashboard-hours"><i class="bi bi-clock" aria-hidden="true"></i> Jam verifikasi: Selasa–Minggu, 09.00–19.00 WIB</span>
    </header>

    <div class="admin-dashboard-metrics" aria-label="Tugas operasional saat ini">
        <a href="{{ route('admin.payments.index') }}" class="admin-dashboard-metric {{ $pendingPaymentsCount > 0 ? 'is-priority' : '' }}">
            <div class="admin-metric-top">
                <span class="admin-metric-label">Menunggu verifikasi pembayaran</span>
                <span class="admin-metric-icon" aria-hidden="true"><i class="bi bi-credit-card"></i></span>
            </div>
            <div class="admin-metric-value-row">
                <strong>{{ number_format($pendingPaymentsCount, 0, ',', '.') }}</strong>
                @if($pendingPaymentsCount > 0)
                    <span class="admin-metric-pill">Prioritas</span>
                @endif
            </div>
            <span class="admin-dashboard-metric-action">Buka antrean <span aria-hidden="true">→</span></span>
        </a>
        <a href="{{ route('admin.cancellations.index') }}" class="admin-dashboard-metric {{ $pendingCancellationsCount > 0 ? 'is-priority' : '' }}">
            <div class="admin-metric-top">
                <span class="admin-metric-label">Permohonan pembatalan</span>
                <span class="admin-metric-icon" aria-hidden="true"><i class="bi bi-x-circle"></i></span>
            </div>
            <div class="admin-metric-value-row">
                <strong>{{ number_format($pendingCancellationsCount, 0, ',', '.') }}</strong>
                @if($pendingCancellationsCount > 0)
                    <span class="admin-metric-pill">Prioritas</span>
                @endif
            </div>
            <span class="admin-dashboard-metric-action">Tinjau permohonan <span aria-hidden="true">→</span></span>
        </a>
        <a href="{{ route('admin.orders.index', ['status' => \App\Models\Order::STATUS_PAYMENT_VERIFIED]) }}" class="admin-dashboard-metric {{ $ordersToProcessCount > 0 ? 'is-priority' : '' }}">
            <div class="admin-metric-top">
                <span class="admin-metric-label">Pesanan perlu diproses</span>
                <span class="admin-metric-icon" aria-hidden="true"><i class="bi bi-box-seam"></i></span>
            </div>
            <div class="admin-metric-value-row">
                <strong>{{ number_format($ordersToProcessCount, 0, ',', '.') }}</strong>
                @if($ordersToProcessCount > 0)
                    <span class="admin-metric-pill">Prioritas</span>
                @endif
            </div>
            <span class="admin-dashboard-metric-action">Lihat pesanan <span aria-hidden="true">→</span></span>
        </a>
        <a href="{{ route('admin.orders.index', ['status' => \App\Models\Order::STATUS_SHIPPED]) }}" class="admin-dashboard-metric">
            <div class="admin-metric-top">
                <span class="admin-metric-label">Pesanan sedang dikirim</span>
                <span class="admin-metric-icon" aria-hidden="true"><i class="bi bi-truck"></i></span>
            </div>
            <div class="admin-metric-value-row">
                <strong>{{ number_format($ordersShippedCount, 0, ',', '.') }}</strong>
            </div>
            <span class="admin-dashboard-metric-action">Pantau pengiriman <span aria-hidden="true">→</span></span>
        </a>
    </div>

    @if($pendingPaymentsCount > 0)
        <aside class="admin-dashboard-priority" aria-label="Pekerjaan prioritas">
            <div class="admin-dashboard-priority-body">
                <span class="admin-priority-icon" aria-hidden="true"><i class="bi bi-exclamation-diamond"></i></span>
                <div>
                    <p class="owner-recap-eyebrow">TINDAKAN DIPERLUKAN</p>
                    <h2>{{ number_format($pendingPaymentsCount, 0, ',', '.') }} bukti pembayaran menunggu pemeriksaan</h2>
                    <p>Periksa informasi transfer dan lampiran bukti sebelum menetapkan hasil verifikasi.</p>
                </div>
            </div>
            <a href="{{ route('admin.payments.index') }}" class="btn btn-dark">Buka verifikasi <span aria-hidden="true">→</span></a>
        </aside>
    @endif

    <section class="admin-dashboard-orders" aria-labelledby="recentOrdersTitle">
        <header class="admin-dashboard-orders-header">
            <div>
                <p class="owner-recap-eyebrow">AKTIVITAS TERBARU</p>
                <h2 id="recentOrdersTitle">Pesanan terbaru</h2>
                <p>Menampilkan hingga lima pesanan yang paling baru dibuat.</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">Semua pesanan <span aria-hidden="true">→</span></a>
        </header>

        <div class="table-responsive admin-dashboard-table-wrap">
            <table class="table admin-dashboard-table align-middle mb-0">
                <caption class="visually-hidden">Pesanan terbaru beserta pelanggan, nilai transaksi, status, dan tindakan</caption>
                <thead>
                    <tr>
                        <th scope="col">Nomor pesanan</th>
                        <th scope="col">Pelanggan</th>
                        <th scope="col">Kontak penerima</th>
                        <th scope="col" class="text-end">Total</th>
                        <th scope="col">Status pesanan</th>
                        <th scope="col">Bukti pembayaran</th>
                        <th scope="col" class="text-end">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                        @php
                            $proofStatus = $order->payment?->proof_file_path ? $order->payment->verification_status : null;
                            $proofClass = match($proofStatus) {
                                \App\Models\Payment::STATUS_VALID => 'is-valid',
                                \App\Models\Payment::STATUS_PENDING => 'is-pending',
                                \App\Models\Payment::STATUS_REJECTED => 'is-rejected',
                                default => 'is-empty',
                            };
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="admin-dashboard-order-number" title="{{ $order->order_number }}">{{ $order->order_number }}</a>
                                <span class="admin-dashboard-order-date">{{ $order->created_at->copy()->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</span>
                            </td>
                            <td>{{ $order->customer?->full_name ?? $order->recipient_name }}</td>
                            <td class="admin-table-phone">{{ $order->recipient_phone }}</td>
                            <td class="text-end admin-table-amount">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            <td><x-status-badge :status="$order->status" /></td>
                            <td>
                                <span class="admin-payment-status {{ $proofClass }}">{{ $proofStatus ?? 'Bukti belum diunggah' }}</span>
                            </td>
                            <td class="text-end">
                                <div class="admin-dashboard-row-actions">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                                    @if($order->payment && $order->payment->proof_file_path && $order->payment->isPending())
                                        <a href="{{ route('admin.payments.verify', $order->payment->id) }}" class="btn btn-sm btn-dark">Periksa bukti</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="admin-dashboard-empty">
                                    <i class="bi bi-inbox" aria-hidden="true"></i>
                                    <strong>Belum ada pesanan.</strong>
                                    <span>Pesanan terbaru akan tampil di sini.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</section>
@endsection
