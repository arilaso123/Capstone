@extends('layouts.app')

@section('title', 'Pengajuan Pembatalan #' . $order->order_number)

@section('content')
<div class="container py-5 customer-cancellation-page">
    <nav aria-label="Breadcrumb" class="customer-checkout-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customer.orders.index') }}">Pesanan Saya</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customer.orders.show', $order->id) }}">{{ $order->order_number }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Pembatalan</li>
        </ol>
    </nav>

    <x-customer-portal-nav />

    <header class="customer-cancellation-header">
        <p class="home-eyebrow">Bantuan pesanan</p>
        <h1>Ajukan pembatalan</h1>
        <p>Admin akan meninjau permohonan ini dan memberi keputusan melalui status pesanan.</p>
    </header>

    <div class="customer-cancellation-layout">
        <section class="customer-order-panel customer-cancellation-summary" aria-labelledby="customer-cancellation-order-heading">
            <header class="customer-order-panel-header"><div><p class="home-eyebrow">Ringkasan transaksi</p><h2 id="customer-cancellation-order-heading">{{ $order->order_number }}</h2></div></header>
            <dl>
                <div><dt>Subtotal parfum</dt><dd>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</dd></div>
                <div><dt>Status saat ini</dt><dd><x-status-badge :status="$order->status" /></dd></div>
            </dl>
            <p>Pengajuan tidak membatalkan pesanan secara otomatis. Status tetap berjalan sampai Admin mengambil keputusan.</p>
            <p>Jika pesanan telah dibayar, Admin wajib mencatat refund manual sebelum menyetujui pembatalan.</p>
        </section>

        <section class="customer-order-panel customer-cancellation-form-panel" aria-labelledby="customer-cancellation-form-heading">
            <header class="customer-order-panel-header"><div><p class="home-eyebrow">Permohonan Anda</p><h2 id="customer-cancellation-form-heading">Alasan pembatalan</h2></div></header>
            <form action="{{ route('customer.orders.cancellation.post', $order->id) }}" method="POST" class="customer-cancellation-form">
                @csrf
                <div class="customer-checkout-field">
                    <label for="reason">Jelaskan alasan Anda <span aria-hidden="true">*</span></label>
                    <textarea id="reason" name="reason" rows="6" required aria-invalid="{{ $errors->has('reason') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('reason') ? 'cancellation-reason-help cancellation-reason-error' : 'cancellation-reason-help' }}" placeholder="Tuliskan alasan yang membantu Admin meninjau permohonan Anda.">{{ old('reason') }}</textarea>
                    <small id="cancellation-reason-help">Permohonan akan ditinjau oleh Admin. Pengiriman atau proses pesanan tidak langsung berubah setelah formulir dikirim.</small>
                    @error('reason')<p class="customer-checkout-error" id="cancellation-reason-error">{{ $message }}</p>@enderror
                </div>
                <div class="customer-cancellation-actions">
                    <a href="{{ route('customer.orders.show', $order->id) }}">Kembali ke detail</a>
                    <button type="submit">Kirim permohonan <span aria-hidden="true">→</span></button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
