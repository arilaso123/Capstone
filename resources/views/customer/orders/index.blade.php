@extends('layouts.app')

@section('title', 'Riwayat Pesanan Saya')

@section('content')
<div class="py-4 border-bottom" style="background-color: var(--color-warm-ivory);">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-secondary">Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}" class="text-secondary">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pesanan Saya</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 fw-bold mb-0">Riwayat Pesanan Pre-Order</h1>
            <a href="{{ route('catalog') }}" class="btn btn-mocha btn-sm"><i class="bi bi-plus-lg me-1"></i> Pesan Baru</a>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="card card-uyuy p-4">
        <!-- Filter Tabs -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 border-bottom pb-3">
            <div class="d-flex flex-wrap gap-1">
                <a href="{{ route('customer.orders.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-dark' : 'btn-outline-secondary' }}">
                    Semua
                </a>
                <a href="{{ route('customer.orders.index', ['status' => \App\Models\Order::STATUS_WAITING_PAYMENT]) }}"
                   class="btn btn-sm {{ request('status') === \App\Models\Order::STATUS_WAITING_PAYMENT ? 'btn-dark' : 'btn-outline-secondary' }}">
                    Menunggu Bayar
                </a>
                <a href="{{ route('customer.orders.index', ['status' => \App\Models\Order::STATUS_PAYMENT_VERIFIED]) }}"
                   class="btn btn-sm {{ request('status') === \App\Models\Order::STATUS_PAYMENT_VERIFIED ? 'btn-dark' : 'btn-outline-secondary' }}">
                    Terverifikasi
                </a>
                <a href="{{ route('customer.orders.index', ['status' => \App\Models\Order::STATUS_PROCESSING]) }}"
                   class="btn btn-sm {{ request('status') === \App\Models\Order::STATUS_PROCESSING ? 'btn-dark' : 'btn-outline-secondary' }}">
                    Diproses
                </a>
                <a href="{{ route('customer.orders.index', ['status' => \App\Models\Order::STATUS_SHIPPED]) }}"
                   class="btn btn-sm {{ request('status') === \App\Models\Order::STATUS_SHIPPED ? 'btn-dark' : 'btn-outline-secondary' }}">
                    Dikirim
                </a>
                <a href="{{ route('customer.orders.index', ['status' => \App\Models\Order::STATUS_COMPLETED]) }}"
                   class="btn btn-sm {{ request('status') === \App\Models\Order::STATUS_COMPLETED ? 'btn-dark' : 'btn-outline-secondary' }}">
                    Selesai
                </a>
                <a href="{{ route('customer.orders.index', ['status' => \App\Models\Order::STATUS_CANCELLED]) }}"
                   class="btn btn-sm {{ request('status') === \App\Models\Order::STATUS_CANCELLED ? 'btn-dark' : 'btn-outline-secondary' }}">
                    Dibatalkan
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th>Nomor Pesanan</th>
                        <th>Periode PO</th>
                        <th>Tanggal Pesan</th>
                        <th>Total Tagihan</th>
                        <th>Status Pemenuhan</th>
                        <th>Status Pembayaran</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>
                                <strong class="text-dark">{{ $order->order_number }}</strong>
                            </td>
                            <td>{{ $order->poPeriod?->name ?? '-' }}</td>
                            <td>{{ $order->created_at->format('d M Y') }}</td>
                            <td class="fw-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            <td><x-status-badge :status="$order->status" /></td>
                            <td>
                                @if($order->payment && $order->payment->proof_file_path)
                                    <span class="badge {{ $order->payment->isValid() ? 'bg-success' : ($order->payment->isRejected() ? 'bg-danger' : 'bg-info') }}">
                                        {{ $order->payment->verification_status }}
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i> Belum Diunggah</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('customer.orders.show', $order->id) }}" class="btn btn-outline-secondary">Detail</a>
                                    @if($order->status === \App\Models\Order::STATUS_WAITING_PAYMENT)
                                        <a href="{{ route('customer.orders.payment', $order->id) }}" class="btn btn-mocha">Bayar</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Tidak ada data pesanan yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
