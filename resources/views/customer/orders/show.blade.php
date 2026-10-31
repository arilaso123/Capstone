@extends('layouts.app')

@section('title', 'Detail Pesanan #' . $order->order_number)

@section('content')
<div class="py-4 border-bottom" style="background-color: var(--color-warm-ivory);">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-secondary">Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}" class="text-secondary">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customer.orders.index') }}" class="text-secondary">Pesanan Saya</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $order->order_number }}</li>
            </ol>
        </nav>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h1 class="h3 fw-bold mb-0">Pesanan {{ $order->order_number }}</h1>
                <span class="text-muted small">
                    Dibuat pada {{ $order->created_at->format('d F Y, H:i') }} WIB &bull; Periode {{ $order->poPeriod?->name ?? 'PO Umum' }}
                </span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-status-badge :status="$order->status" />
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <!-- Order Fulfillment Stepper Timeline -->
    <div class="card card-uyuy p-4 mb-4">
        <h6 class="fw-bold text-uppercase small text-muted mb-2">Progres Pemenuhan Pesanan (5 Tahap Baku)</h6>
        <x-order-timeline :status="$order->status" />
    </div>

    <div class="row g-4">
        <!-- Left: Ordered Items, Shipping Info & History -->
        <div class="col-lg-8">
            <div class="card card-uyuy p-4 mb-4">
                <h5 class="fw-bold font-heading mb-3">Item Parfum yang Dipesan</h5>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Produk Parfum</th>
                                <th>Ukuran</th>
                                <th>Harga Satuan</th>
                                <th class="text-center">Jumlah</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->orderDetails as $detail)
                                <tr>
                                    <td>
                                        <strong class="text-dark">{{ $detail->product_name }}</strong>
                                        <div class="text-muted small">{{ $detail->product?->brand ?? 'In Parfume' }}</div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">{{ $detail->size_ml }} ml</span></td>
                                    <td>Rp {{ number_format($detail->unit_price, 0, ',', '.') }}</td>
                                    <td class="text-center">{{ $detail->quantity }}</td>
                                    <td class="text-end fw-bold">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Tidak ada rincian item.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="border-top">
                            <tr>
                                <th colspan="4" class="text-end pt-3">Total Tagihan Pre-Order:</th>
                                <th class="text-end pt-3 fs-5 text-dark fw-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Shipping Information -->
            <div class="card card-uyuy p-4 mb-4">
                <h5 class="fw-bold font-heading mb-3">Tujuan Pengiriman</h5>
                <div class="row g-3 small">
                    <div class="col-md-6">
                        <div class="text-muted mb-1">Nama Penerima:</div>
                        <div class="fw-bold fs-6">{{ $order->recipient_name }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted mb-1">Nomor WhatsApp:</div>
                        <div class="fw-bold fs-6">{{ $order->recipient_phone }}</div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted mb-1">Alamat Lengkap:</div>
                        <div class="p-3 bg-light rounded-2 border">
                            {{ $order->shipping_address }}
                        </div>
                    </div>
                    @if($order->customer_notes)
                        <div class="col-12">
                            <div class="text-muted mb-1">Catatan Pemesan:</div>
                            <div class="p-2 bg-light rounded-2 border text-muted fst-italic">
                                "{{ $order->customer_notes }}"
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Status History Log -->
            @if($order->statusHistories->isNotEmpty())
                <div class="card card-uyuy p-4">
                    <h5 class="fw-bold font-heading mb-3">Riwayat Status Pesanan</h5>
                    <div class="timeline-activity">
                        @foreach($order->statusHistories as $history)
                            <div class="border-start border-2 ps-3 pb-3 position-relative" style="border-color: var(--color-mocha) !important;">
                                <div class="small text-muted">{{ $history->created_at->format('d M Y, H:i') }} WIB</div>
                                <div class="fw-semibold text-dark">{{ $history->new_status }}</div>
                                @if($history->remarks)
                                    <div class="small text-secondary">{{ $history->remarks }}</div>
                                @endif
                                <div class="small text-muted fst-italic">Oleh: {{ $history->changer?->name ?? 'Sistem' }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Right: Payment & Cancellation Actions -->
        <div class="col-lg-4">
            <!-- Payment Status Box -->
            <div class="card card-uyuy p-4 mb-4">
                <h6 class="fw-bold font-heading mb-3"><i class="bi bi-credit-card-2-front me-2 text-mocha"></i> Status Pembayaran</h6>

                <div class="p-3 rounded-3 bg-light border mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small text-muted">Metode:</span>
                        <strong class="small">{{ $order->payment?->payment_method ?? 'BCA Merchant' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small text-muted">Total:</span>
                        <strong class="text-dark">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small text-muted">Status Bukti:</span>
                        @if($order->payment && $order->payment->proof_file_path)
                            <span class="badge {{ $order->payment->isValid() ? 'bg-success' : ($order->payment->isRejected() ? 'bg-danger' : 'bg-info') }} small">
                                {{ $order->payment->verification_status }}
                            </span>
                        @else
                            <span class="badge bg-warning text-dark small">Belum Diunggah</span>
                        @endif
                    </div>
                    @if($order->payment?->admin_notes)
                        <div class="small text-muted mt-2 pt-2 border-top">
                            <strong>Catatan Admin:</strong> {{ $order->payment->admin_notes }}
                        </div>
                    @endif
                </div>

                @if($order->payment?->proof_file_path)
                    <div class="mb-3 text-center">
                        <span class="small text-muted d-block mb-1">Bukti Terunggah:</span>
                        <a href="{{ asset('storage/' . $order->payment->proof_file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-image me-1"></i> Lihat Berkas Bukti
                        </a>
                    </div>
                @endif

                @if($order->status === \App\Models\Order::STATUS_WAITING_PAYMENT)
                    <a href="{{ route('customer.orders.payment', $order->id) }}" class="btn btn-mocha w-100 mb-2">
                        <i class="bi bi-upload me-1"></i> {{ $order->payment?->proof_file_path ? 'Unggah Ulang Bukti' : 'Unggah Bukti Transfer' }}
                    </a>
                @endif

                <span class="text-muted small text-center d-block" style="font-size: 0.72rem;">
                    Admin memeriksa dan memverifikasi bukti pembayaran secara manual.
                </span>
            </div>

            <!-- Cancellation Box -->
            <div class="card card-uyuy p-4">
                <h6 class="fw-bold font-heading mb-2 text-danger"><i class="bi bi-x-octagon me-2"></i> Pengajuan Pembatalan</h6>

                @if($order->cancellationRequest)
                    <div class="alert {{ $order->cancellationRequest->isApproved() ? 'alert-success' : ($order->cancellationRequest->isRejected() ? 'alert-danger' : 'alert-warning') }} p-3 mb-0 small">
                        <div class="fw-bold mb-1">Status: {{ $order->cancellationRequest->status }}</div>
                        <div class="mb-1"><strong>Alasan:</strong> {{ $order->cancellationRequest->reason }}</div>
                        @if($order->cancellationRequest->admin_notes)
                            <div class="mt-2 pt-2 border-top">
                                <strong>Catatan Admin:</strong> {{ $order->cancellationRequest->admin_notes }}
                            </div>
                        @endif
                    </div>
                @elseif($order->canBeCancelled())
                    <p class="small text-secondary mb-3">
                        Apabila ada kendala pesanan sebelum pesanan diproses atau dikirim, Anda dapat mengajukan pembatalan ke staf admin.
                    </p>
                    <a href="{{ route('customer.orders.cancellation', $order->id) }}" class="btn btn-outline-danger btn-sm w-100">
                        Ajukan Permohonan Batal
                    </a>
                    <div class="small text-muted mt-2" style="font-size: 0.72rem;">
                        * Pembatalan memerlukan persetujuan admin terlebih dahulu.
                    </div>
                @else
                    <p class="small text-muted mb-0">
                        Pesanan ini sudah berada pada tahap <strong>{{ $order->status }}</strong> dan tidak dapat diajukan pembatalan melalui sistem.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
