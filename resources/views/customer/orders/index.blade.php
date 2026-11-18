@extends('layouts.app')

@section('title', 'Pesanan Saya')

@section('content')
<div class="container py-5 customer-orders-page">
    <nav aria-label="Breadcrumb" class="customer-checkout-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Pesanan Saya</li>
        </ol>
    </nav>

    <x-customer-portal-nav />

    <header class="customer-orders-header">
        <div>
            <p class="home-eyebrow">Aktivitas akun</p>
            <h1>Pesanan saya</h1>
            <p>Lacak status pesanan, lihat ringkasan pembayaran, dan buka detail tiap transaksi.</p>
        </div>
        <a href="{{ route('catalog') }}" class="customer-orders-new-link">Jelajahi katalog <span aria-hidden="true">→</span></a>
    </header>

    <nav class="customer-orders-filters" aria-label="Filter status pesanan">
        <span>Filter status</span>
        <a href="{{ route('customer.orders.index') }}" @class(['active' => !request('status')]) @if(!request('status')) aria-current="page" @endif>Semua</a>
        <a href="{{ route('customer.orders.index', ['status' => \App\Models\Order::STATUS_WAITING_PAYMENT]) }}" @class(['active' => request('status') === \App\Models\Order::STATUS_WAITING_PAYMENT]) @if(request('status') === \App\Models\Order::STATUS_WAITING_PAYMENT) aria-current="page" @endif>Menunggu pembayaran</a>
        <a href="{{ route('customer.orders.index', ['status' => \App\Models\Order::STATUS_PAYMENT_VERIFIED]) }}" @class(['active' => request('status') === \App\Models\Order::STATUS_PAYMENT_VERIFIED]) @if(request('status') === \App\Models\Order::STATUS_PAYMENT_VERIFIED) aria-current="page" @endif>Terverifikasi</a>
        <a href="{{ route('customer.orders.index', ['status' => \App\Models\Order::STATUS_PROCESSING]) }}" @class(['active' => request('status') === \App\Models\Order::STATUS_PROCESSING]) @if(request('status') === \App\Models\Order::STATUS_PROCESSING) aria-current="page" @endif>Diproses</a>
        <a href="{{ route('customer.orders.index', ['status' => \App\Models\Order::STATUS_SHIPPED]) }}" @class(['active' => request('status') === \App\Models\Order::STATUS_SHIPPED]) @if(request('status') === \App\Models\Order::STATUS_SHIPPED) aria-current="page" @endif>Dikirim</a>
        <a href="{{ route('customer.orders.index', ['status' => \App\Models\Order::STATUS_COMPLETED]) }}" @class(['active' => request('status') === \App\Models\Order::STATUS_COMPLETED]) @if(request('status') === \App\Models\Order::STATUS_COMPLETED) aria-current="page" @endif>Selesai</a>
        <a href="{{ route('customer.orders.index', ['status' => \App\Models\Order::STATUS_CANCELLED]) }}" @class(['active' => request('status') === \App\Models\Order::STATUS_CANCELLED]) @if(request('status') === \App\Models\Order::STATUS_CANCELLED) aria-current="page" @endif>Dibatalkan</a>
    </nav>

    <section class="customer-orders-table-panel" aria-label="Daftar pesanan">
        @if($orders->count())
            <div class="table-responsive">
                <table class="table customer-orders-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Nomor pesanan</th>
                            <th scope="col">Periode PO</th>
                            <th scope="col">Tanggal dibuat</th>
                            <th scope="col">Subtotal parfum</th>
                            <th scope="col">Status pesanan</th>
                            <th scope="col">Bukti pembayaran</th>
                            <th scope="col"><span class="visually-hidden">Tindakan</span></th>
                        </tr>
                    </thead>
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
                                <td><a href="{{ route('customer.orders.show', $order->id) }}" class="customer-order-number" title="{{ $order->order_number }}">{{ $order->order_number }}</a></td>
                                <td>{{ $order->poPeriod?->name ?? '—' }}</td>
                                <td>{{ $order->created_at->copy()->timezone('Asia/Jakarta')->format('d M Y') }} WIB</td>
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
                <p class="home-eyebrow">Tidak ada hasil</p>
                <h2>{{ request('status') ? 'Belum ada pesanan pada status ini' : 'Belum ada pesanan' }}</h2>
                <p>{{ request('status') ? 'Coba pilih filter status lain untuk melihat transaksi Anda.' : 'Pesanan yang dibuat melalui katalog akan muncul di sini.' }}</p>
                @if(request('status'))
                    <a href="{{ route('customer.orders.index') }}">Lihat semua pesanan</a>
                @else
                    <a href="{{ route('catalog') }}">Jelajahi katalog <span aria-hidden="true">→</span></a>
                @endif
            </div>
        @endif
    </section>

    @if($orders->hasPages())
        <nav class="customer-orders-pagination" aria-label="Navigasi halaman pesanan">{{ $orders->withQueryString()->links() }}</nav>
    @endif
</div>
@endsection
