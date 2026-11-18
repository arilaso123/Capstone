@extends('layouts.admin')

@section('title', 'Pesanan Operasional')

@section('content')
<div class="admin-orders-page">
    <header class="admin-orders-header">
        <div><p class="home-eyebrow">Tugas operasional</p><h1>Pesanan pre-order</h1><p>Cari pesanan pelanggan, pantau status, dan buka proses pemeriksaan yang diperlukan.</p></div>
    </header>

    <section class="admin-orders-filters" aria-label="Pencarian dan filter pesanan">
        <form action="{{ route('admin.orders.index') }}" method="GET">
            <div class="admin-orders-search-field">
                <label for="order-search">Cari pesanan</label>
                <input id="order-search" type="search" name="search" value="{{ request('search') }}" placeholder="Nomor pesanan, nama, atau nomor WhatsApp" autocomplete="off">
            </div>
            <div class="admin-orders-status-field">
                <label for="order-status">Status pesanan</label>
                <select id="order-status" name="status">
                    <option value="">Semua status</option>
                    @foreach(\App\Models\Order::$statuses as $statusKey)
                        <option value="{{ $statusKey }}" @selected(request('status') === $statusKey)>{{ $statusKey }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-orders-filter-actions">
                <button type="submit">Terapkan filter <span aria-hidden="true">→</span></button>
                @if(request('search') || request('status'))<a href="{{ route('admin.orders.index') }}">Bersihkan</a>@endif
            </div>
        </form>
    </section>

    <section class="admin-orders-table-panel" aria-label="Daftar pesanan">
        @if($orders->count())
            <div class="table-responsive">
                <table class="table admin-orders-table align-middle mb-0">
                    <thead><tr><th scope="col">Pesanan</th><th scope="col">Pelanggan</th><th scope="col">WhatsApp</th><th scope="col">Jumlah</th><th scope="col">Subtotal parfum</th><th scope="col">Status pesanan</th><th scope="col">Bukti pembayaran</th><th scope="col"><span class="visually-hidden">Tindakan</span></th></tr></thead>
                    <tbody>
                        @foreach($orders as $order)
                            @php
                                $proofStatus = $order->payment?->proof_file_path ? $order->payment->verification_status : null;
                                $proofClass = match($proofStatus) {
                                    \App\Models\Payment::STATUS_VALID => 'is-valid',
                                    \App\Models\Payment::STATUS_PENDING => 'is-pending',
                                    \App\Models\Payment::STATUS_REJECTED => 'is-rejected',
                                    default => 'customer-proof-status-empty is-empty',
                                };
                            @endphp
                            <tr>
                                <td><a href="{{ route('admin.orders.show', $order->id) }}" class="admin-dashboard-order-number" title="{{ $order->order_number }}"><strong>{{ $order->order_number }}</strong></a><small>{{ $order->created_at->copy()->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</small></td>
                                <td>{{ $order->customer?->full_name ?? $order->recipient_name }}</td>
                                <td class="admin-table-phone">{{ $order->recipient_phone }}</td>
                                <td>{{ $order->orderDetails->sum('quantity') }} botol</td>
                                <td class="admin-order-list-amount">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                <td><x-status-badge :status="$order->status" /></td>
                                <td>
                                    <span class="customer-proof-status {{ $proofClass }}">{{ $proofStatus ?? 'Belum diunggah' }}</span>
                                </td>
                                <td class="text-end"><div class="admin-order-list-actions"><a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary">Kelola</a>@if($order->payment?->proof_file_path && $order->payment->isPending())<a href="{{ route('admin.payments.verify', $order->payment->id) }}" class="btn btn-sm btn-dark">Verifikasi</a>@endif</div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="admin-cancellations-empty"><p class="home-eyebrow">Tidak ada hasil</p><h2>{{ request('search') || request('status') ? 'Pesanan tidak ditemukan' : 'Belum ada pesanan' }}</h2><p>{{ request('search') || request('status') ? 'Ubah kata pencarian atau hapus filter status untuk mencoba kembali.' : 'Pesanan pelanggan akan ditampilkan di sini.' }}</p>@if(request('search') || request('status'))<a href="{{ route('admin.orders.index') }}">Hapus filter</a>@endif</div>
        @endif
    </section>

    @if($orders->hasPages())
        <nav class="admin-cancellations-pagination" aria-label="Navigasi halaman pesanan">{{ $orders->withQueryString()->links() }}</nav>
    @endif
</div>
@endsection
