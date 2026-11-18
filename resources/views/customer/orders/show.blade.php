@extends('layouts.app')

@section('title', 'Detail Pesanan #' . $order->order_number)

@section('content')
<div class="container py-5 customer-order-detail-page">
    <nav aria-label="Breadcrumb" class="customer-checkout-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customer.orders.index') }}">Pesanan Saya</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $order->order_number }}</li>
        </ol>
    </nav>

    <x-customer-portal-nav />

    <header class="customer-order-detail-header">
        <div>
            <p class="home-eyebrow">Detail transaksi</p>
            <h1>{{ $order->order_number }}</h1>
            <p>Dibuat {{ $order->created_at->copy()->timezone('Asia/Jakarta')->format('d F Y, H:i') }} WIB · Periode {{ $order->poPeriod?->name ?? 'PO Umum' }}</p>
        </div>
        <x-status-badge :status="$order->status" />
    </header>

    <section class="customer-order-progress" aria-labelledby="customer-order-progress-heading">
        <header><p class="home-eyebrow">Pembaruan</p><h2 id="customer-order-progress-heading">Perjalanan pesanan</h2></header>
        <x-order-timeline :status="$order->status" />
    </section>

    <div class="customer-order-detail-layout">
        <div class="customer-order-detail-main">
            <section class="customer-order-panel" aria-labelledby="customer-order-items-heading">
                <header class="customer-order-panel-header">
                    <div><p class="home-eyebrow">Ringkasan</p><h2 id="customer-order-items-heading">Item yang dipesan</h2></div>
                    <span>{{ $order->orderDetails->sum('quantity') }} botol</span>
                </header>
                <div class="table-responsive">
                    <table class="table customer-orders-table customer-order-items-table align-middle mb-0">
                        <thead><tr><th scope="col">Produk</th><th scope="col">Ukuran</th><th scope="col">Harga satuan</th><th scope="col">Jumlah</th><th scope="col">Subtotal</th></tr></thead>
                        <tbody>
                            @forelse($order->orderDetails as $detail)
                                <tr>
                                    <td><span class="customer-order-product-name">{{ $detail->product_name }}</span><small>{{ $detail->product?->brand ?? 'Uyuy Jastip' }}</small></td>
                                    <td>{{ $detail->size_ml }} ml</td>
                                    <td>Rp {{ number_format($detail->unit_price, 0, ',', '.') }}</td>
                                    <td>{{ $detail->quantity }}</td>
                                    <td class="customer-order-amount">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="customer-order-no-items">Tidak ada rincian item pada pesanan ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <footer class="customer-order-items-total"><span>Subtotal parfum</span><strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></footer>
            </section>

            <section class="customer-order-panel" aria-labelledby="customer-shipping-heading">
                <header class="customer-order-panel-header"><div><p class="home-eyebrow">Tujuan</p><h2 id="customer-shipping-heading">Informasi pengiriman</h2></div></header>
                <dl class="customer-order-shipping-details">
                    <div><dt>Nama penerima</dt><dd>{{ $order->recipient_name }}</dd></div>
                    <div><dt>Nomor WhatsApp</dt><dd>{{ $order->recipient_phone }}</dd></div>
                    <div class="customer-order-address"><dt>Alamat lengkap</dt><dd>{{ $order->shipping_address }}</dd></div>
                    @if($order->shipping_postal_code)<div><dt>Kode pos</dt><dd>{{ $order->shipping_postal_code }}</dd></div>@endif
                    @if($order->courier || $order->tracking_number || (float) $order->shipping_cost > 0)
                        <div><dt>Kurir</dt><dd>{{ $order->courier ?: 'Belum ditentukan' }}</dd></div>
                        <div><dt>Nomor resi</dt><dd>{{ $order->tracking_number ?: 'Belum tersedia' }}</dd></div>
                        <div><dt>Ongkos kirim</dt><dd>Rp {{ number_format((float) $order->shipping_cost, 0, ',', '.') }}</dd></div>
                    @endif
                    @if($order->customer_notes)<div class="customer-order-address"><dt>Catatan</dt><dd>{{ $order->customer_notes }}</dd></div>@endif
                </dl>
            </section>

            @if($order->statusHistories->isNotEmpty())
                <section class="customer-order-panel" aria-labelledby="customer-order-history-heading">
                    <header class="customer-order-panel-header"><div><p class="home-eyebrow">Catatan sistem</p><h2 id="customer-order-history-heading">Riwayat status</h2></div></header>
                    <ol class="customer-order-history">
                        @foreach($order->statusHistories as $history)
                            <li>
                                <time datetime="{{ $history->created_at->toIso8601String() }}">{{ $history->created_at->copy()->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</time>
                                <strong>{{ $history->new_status }}</strong>
                                @if($history->remarks)<p>{{ $history->remarks }}</p>@endif
                                <small>Oleh {{ $history->changer?->name ?? 'Sistem' }}</small>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif
        </div>

        <aside class="customer-order-detail-aside" aria-label="Pembayaran dan pembatalan">
            <section class="customer-order-panel customer-order-payment-panel" aria-labelledby="customer-order-payment-heading">
                <header class="customer-order-panel-header"><div><p class="home-eyebrow">Pembayaran</p><h2 id="customer-order-payment-heading">Status pembayaran</h2></div></header>
                <dl class="customer-order-payment-details">
                    <div><dt>Metode</dt><dd>{{ $order->payment?->payment_method ?? 'Belum ditentukan' }}</dd></div>
                    <div><dt>Subtotal parfum</dt><dd>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</dd></div>
                    <div><dt>Status bukti</dt><dd>{{ $order->payment?->verification_status ?? 'Belum diunggah' }}</dd></div>
                </dl>
                @if($order->payment?->admin_notes)<p class="customer-order-admin-note"><strong>Catatan Admin</strong>{{ $order->payment->admin_notes }}</p>@endif

                @if($order->payment?->proof_file_path && !preg_match('/^https?:\/\//i', $order->payment->proof_file_path))
                    <a href="{{ route('payments.proof', $order->payment) }}" target="_blank" rel="noopener noreferrer" class="customer-order-panel-link">Buka bukti pembayaran <span aria-hidden="true">↗</span></a>
                @elseif($order->payment?->proof_file_path)
                    <p class="customer-order-note">Bukti historis berasal dari sumber eksternal dan tidak tersedia melalui sistem. Hubungi Admin jika berkas diperlukan.</p>
                @endif

                @if($order->payment?->attempts->isNotEmpty())
                    <details class="customer-order-attempts">
                        <summary>Riwayat unggahan ({{ $order->payment->attempts->count() }})</summary>
                        @foreach($order->payment->attempts->sortByDesc('attempt_number') as $attempt)
                            <div class="customer-order-attempt">
                                <span>Unggahan #{{ $attempt->attempt_number }} · {{ $attempt->uploaded_at?->copy()->timezone('Asia/Jakarta')->format('d M Y, H:i') ?? '—' }} WIB</span>
                                <strong>{{ $attempt->verification_status }}</strong>
                                @if($attempt->admin_notes)<p>{{ $attempt->admin_notes }}</p>@endif
                            </div>
                        @endforeach
                    </details>
                @endif

                @if($order->status === \App\Models\Order::STATUS_WAITING_PAYMENT)
                    <a href="{{ route('customer.orders.payment', $order->id) }}" class="customer-order-primary-action">{{ $order->payment?->proof_file_path ? 'Unggah ulang bukti' : 'Unggah bukti pembayaran' }} <span aria-hidden="true">→</span></a>
                @endif
                <p class="customer-order-note">Bukti pembayaran diverifikasi secara manual oleh Admin.</p>
            </section>

            <section class="customer-order-panel customer-order-cancellation-panel" aria-labelledby="customer-order-cancellation-heading">
                <header class="customer-order-panel-header"><div><p class="home-eyebrow">Bantuan pesanan</p><h2 id="customer-order-cancellation-heading">Pembatalan</h2></div></header>
                @if($order->cancellationRequest && !$order->cancellationRequest->isRejected())
                    <div class="customer-cancellation-status">
                        <strong>Status: {{ $order->cancellationRequest->status }}</strong>
                        <p><b>Alasan</b>{{ $order->cancellationRequest->reason }}</p>
                        @if($order->cancellationRequest->admin_notes)<p><b>Catatan Admin</b>{{ $order->cancellationRequest->admin_notes }}</p>@endif
                        @if($order->cancellationRequest->refunded_at)
                            <p><b>Refund tercatat</b>Rp {{ number_format((float) $order->cancellationRequest->refund_amount, 0, ',', '.') }} · Referensi {{ $order->cancellationRequest->refund_reference }} · {{ $order->cancellationRequest->refunded_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>
                            <a href="{{ route('cancellations.refund-proof', $order->cancellationRequest->id) }}">Lihat bukti refund</a>
                        @endif
                    </div>
                @elseif($order->canBeCancelled())
                    <p class="customer-order-note">Pengajuan akan ditinjau Admin dan tidak langsung mengubah status pesanan. Jika pesanan sudah dibayar, Admin wajib mencatat refund manual sebelum menyetujui pembatalan.</p>
                    <a href="{{ route('customer.orders.cancellation', $order->id) }}" class="customer-order-panel-link">{{ $order->cancellationRequest?->isRejected() ? 'Ajukan ulang pembatalan' : 'Ajukan pembatalan' }} <span aria-hidden="true">→</span></a>
                @else
                    <p class="customer-order-note">Pengajuan pembatalan tidak tersedia pada status pesanan {{ $order->status }}.</p>
                @endif
            </section>
        </aside>
    </div>
</div>
@endsection
