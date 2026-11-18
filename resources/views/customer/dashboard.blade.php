@extends('layouts.app')

@section('title', 'Dashboard Pelanggan')

@section('content')
<div class="container py-5 customer-dashboard-page">
    <nav aria-label="Breadcrumb" class="customer-checkout-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
            <li class="breadcrumb-item active" aria-current="page">Dashboard pelanggan</li>
        </ol>
    </nav>

    <x-customer-portal-nav />

    <header class="customer-dashboard-header">
        <div>
            <p class="home-eyebrow">Portal pelanggan</p>
            <h1>Halo, {{ auth()->user()->name }}</h1>
            <p>Ringkasan pesanan dan pembaruan status akun Anda.</p>
        </div>
        <div class="customer-dashboard-actions">
            <a href="{{ route('catalog') }}" class="customer-dashboard-primary">Jelajahi katalog <span aria-hidden="true">→</span></a>
            <a href="{{ route('customer.orders.index') }}">Semua pesanan</a>
        </div>
    </header>

    @if($activePo)
        <section class="customer-dashboard-period" aria-label="Informasi periode pre-order">
            <div>
                <p class="home-eyebrow">Periode pre-order · {{ $activePo->name }}</p>
                <h2>{{ $activePo->start_date->format('d M Y') }} — {{ $activePo->end_date->format('d M Y') }} WIB</h2>
                <p>
                    @if($activePo->isOrderableNow())
                        Pesanan baru dapat dibuat selama periode ini masih berada dalam rentang tanggal WIB.
                    @else
                        Status periode tercatat {{ $activePo->status }}; checkout hanya tersedia jika status dan rentang tanggal sama-sama memenuhi aturan.
                    @endif
                </p>
            </div>
            <x-status-badge :status="$activePo->status" />
            <a href="{{ route('po.info') }}">Informasi PO <span aria-hidden="true">→</span></a>
        </section>
    @else
        <section class="customer-dashboard-period customer-dashboard-period-closed" aria-label="Informasi periode pre-order">
            <div>
                <p class="home-eyebrow">Periode pre-order</p>
                <h2>Belum ada periode PO aktif</h2>
                <p>Anda tetap dapat melihat katalog. Checkout akan tersedia ketika periode pre-order dibuka dalam rentang tanggalnya.</p>
            </div>
            <a href="{{ route('po.info') }}">Lihat informasi PO <span aria-hidden="true">→</span></a>
        </section>
    @endif

    <section class="customer-dashboard-metrics" aria-label="Ringkasan pesanan">
        <article>
            <div class="customer-metric-head">
                <span>Total pesanan</span>
                <span class="admin-metric-icon" aria-hidden="true"><i class="bi bi-bag-check"></i></span>
            </div>
            <strong>{{ $totalOrders }}</strong>
            <small>Semua transaksi akun</small>
        </article>
        <article class="{{ $waitingPayment > 0 ? 'is-attention' : '' }}">
            <div class="customer-metric-head">
                <span>Menunggu pembayaran</span>
                <span class="admin-metric-icon" aria-hidden="true"><i class="bi bi-clock-history"></i></span>
            </div>
            <strong>{{ $waitingPayment }}</strong>
            <small>Perlu menyelesaikan pembayaran</small>
        </article>
        <article>
            <div class="customer-metric-head">
                <span>Sedang diproses</span>
                <span class="admin-metric-icon" aria-hidden="true"><i class="bi bi-box-seam"></i></span>
            </div>
            <strong>{{ $processing }}</strong>
            <small>Pesanan dalam proses pemenuhan</small>
        </article>
        <article>
            <div class="customer-metric-head">
                <span>Pesanan selesai</span>
                <span class="admin-metric-icon" aria-hidden="true"><i class="bi bi-check2-circle"></i></span>
            </div>
            <strong>{{ $completed }}</strong>
            <small>Transaksi berstatus selesai</small>
        </article>
    </section>

    <section class="customer-dashboard-orders" aria-labelledby="customer-recent-orders-heading">
        <header>
            <div>
                <p class="home-eyebrow">Riwayat akun</p>
                <h2 id="customer-recent-orders-heading">Pesanan terbaru</h2>
                <p>Periksa nomor pesanan dan buka detail untuk informasi pembayaran atau pengiriman.</p>
            </div>
            <a href="{{ route('customer.orders.index') }}">Lihat semua <span aria-hidden="true">→</span></a>
        </header>

        @if($recentOrders->count())
            <div class="table-responsive">
                <table class="table customer-orders-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Nomor pesanan</th>
                            <th scope="col">Tanggal dibuat</th>
                            <th scope="col">Periode PO</th>
                            <th scope="col">Subtotal parfum</th>
                            <th scope="col">Status</th>
                            <th scope="col">Bukti pembayaran</th>
                            <th scope="col"><span class="visually-hidden">Tindakan</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
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
                                <td><a href="{{ route('customer.orders.show', $order->id) }}" class="customer-order-number" title="{{ $order->order_number }}">{{ $order->order_number }}</a></td>
                                <td>{{ $order->created_at->copy()->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</td>
                                <td>{{ $order->poPeriod?->name ?? '—' }}</td>
                                <td class="customer-order-amount">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                <td><x-status-badge :status="$order->status" /></td>
                                <td>
                                    <span class="customer-proof-status {{ $proofClass }}">{{ $proofStatus ?? 'Belum diunggah' }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="customer-order-actions">
                                        <a href="{{ route('customer.orders.show', $order->id) }}">Detail</a>
                                        @if($order->status === \App\Models\Order::STATUS_WAITING_PAYMENT)
                                            <a href="{{ route('customer.orders.payment', $order->id) }}" class="customer-order-pay-link">Pembayaran</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="customer-orders-empty">
                <p class="home-eyebrow">Belum ada transaksi</p>
                <h3>Pesanan Anda akan muncul di sini</h3>
                <p>Mulai dari katalog untuk memilih parfum dan membuat pesanan saat PO tersedia.</p>
                <a href="{{ route('catalog') }}">Lihat katalog <span aria-hidden="true">→</span></a>
            </div>
        @endif
    </section>
</div>
@endsection
