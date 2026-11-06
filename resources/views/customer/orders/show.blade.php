@extends('layouts.app')

@section('title', 'Detail Pesanan #' . $order->order_number)

@section('content')
<!-- Header Banner -->
<div class="py-4 border-bottom" style="background: radial-gradient(circle at 10% 20%, #FDFBF7 0%, #F4ECE3 90%); border-color: rgba(216, 197, 174, 0.4) !important;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none" style="color: var(--color-mocha);">Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}" class="text-decoration-none" style="color: var(--color-mocha);">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customer.orders.index') }}" class="text-decoration-none" style="color: var(--color-mocha);">Pesanan Saya</a></li>
                <li class="breadcrumb-item active text-secondary" aria-current="page">{{ $order->order_number }}</li>
            </ol>
        </nav>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h1 class="h2 fw-bold font-heading mb-1" style="color: var(--color-charcoal); letter-spacing: -0.02em;">
                    Pesanan {{ $order->order_number }}
                </h1>
                <span class="text-secondary small">
                    Dibuat pada {{ $order->created_at->format('d F Y, H:i') }} WIB &bull; Periode: <strong>{{ $order->poPeriod?->name ?? 'PO Umum' }}</strong>
                </span>
            </div>
            <div>
                <x-status-badge :status="$order->status" />
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <!-- Order Fulfillment Stepper Timeline -->
    <x-card class="p-4 p-md-4 mb-4 border-0 shadow">
        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-diagram-3 fs-5" style="color: var(--color-mocha);"></i>
            <h6 class="fw-bold font-heading mb-0 text-dark">Progres Pemenuhan Pesanan (5 Tahap Baku)</h6>
        </div>
        <x-order-timeline :status="$order->status" />
    </x-card>

    <div class="row g-4">
        <!-- Left: Ordered Items, Shipping Info & History -->
        <div class="col-lg-8">
            <!-- Ordered Items Card -->
            <x-card class="p-4 p-md-4 mb-4 border-0 shadow">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom" style="border-color: rgba(216, 197, 174, 0.3) !important;">
                    <h5 class="fw-bold font-heading mb-0 text-dark">Item Parfum yang Dipesan</h5>
                    <span class="badge rounded-pill" style="background: rgba(139, 107, 80, 0.12); color: var(--color-mocha); font-size: 0.78rem;">
                        {{ $order->orderDetails->sum('quantity') }} Botol Total
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="small" style="background: #FAF8F5; color: var(--color-charcoal);">
                            <tr>
                                <th class="border-0 rounded-start-3 py-3">Produk Parfum</th>
                                <th class="border-0 py-3">Ukuran</th>
                                <th class="border-0 py-3">Harga Satuan</th>
                                <th class="text-center border-0 py-3">Jumlah</th>
                                <th class="text-end border-0 rounded-end-3 py-3">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->orderDetails as $detail)
                                <tr style="border-bottom: 1px solid rgba(216, 197, 174, 0.25);">
                                    <td class="py-3">
                                        <strong class="text-dark font-heading">{{ $detail->product_name }}</strong>
                                        <div class="text-muted small" style="font-size: 0.75rem;">{{ $detail->product?->brand ?? 'In Parfume' }}</div>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge rounded-pill px-2 py-1" style="background: #FAF8F5; color: var(--color-charcoal); border: 1px solid rgba(216, 197, 174, 0.5); font-size: 0.75rem;">
                                            {{ $detail->size_ml }} ml
                                        </span>
                                    </td>
                                    <td class="py-3 small text-secondary">Rp {{ number_format($detail->unit_price, 0, ',', '.') }}</td>
                                    <td class="text-center py-3 fw-bold text-dark">{{ $detail->quantity }}</td>
                                    <td class="text-end py-3 fw-bold font-heading text-dark">
                                        Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Tidak ada rincian item.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="border-top" style="border-color: rgba(216, 197, 174, 0.35) !important;">
                            <tr>
                                <th colspan="4" class="text-end pt-3 text-secondary small fw-normal">Total Tagihan Pre-Order:</th>
                                <th class="text-end pt-3 fs-4 text-dark fw-bold font-heading">
                                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-card>

            <!-- Shipping Information Card -->
            <x-card class="p-4 mb-4 border-0 shadow">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom" style="border-color: rgba(216, 197, 174, 0.3) !important;">
                    <i class="bi bi-geo-alt fs-5" style="color: var(--color-mocha);"></i>
                    <h5 class="fw-bold font-heading mb-0 text-dark">Tujuan Pengiriman</h5>
                </div>
                <div class="row g-3 small">
                    <div class="col-md-6">
                        <div class="text-muted mb-1" style="font-size: 0.78rem;">Nama Penerima:</div>
                        <div class="fw-bold font-heading fs-6 text-dark">{{ $order->recipient_name }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted mb-1" style="font-size: 0.78rem;">Nomor WhatsApp:</div>
                        <div class="fw-bold text-dark fs-6">{{ $order->recipient_phone }}</div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted mb-1" style="font-size: 0.78rem;">Alamat Lengkap:</div>
                        <div class="p-3 rounded-3" style="background: #FAF8F5; border: 1px solid rgba(216, 197, 174, 0.4); line-height: 1.6;">
                            {{ $order->shipping_address }}
                        </div>
                    </div>
                    @if($order->customer_notes)
                        <div class="col-12">
                            <div class="text-muted mb-1" style="font-size: 0.78rem;">Catatan Khusus:</div>
                            <div class="p-2 rounded-3 text-muted fst-italic" style="background: #FAF8F5; border: 1px dashed rgba(216, 197, 174, 0.5);">
                                "{{ $order->customer_notes }}"
                            </div>
                        </div>
                    @endif
                </div>
            </x-card>

            <!-- Status History Audit Trail -->
            @if($order->statusHistories->isNotEmpty())
                <x-card class="p-4 border-0 shadow">
                    <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom" style="border-color: rgba(216, 197, 174, 0.3) !important;">
                        <i class="bi bi-clock-history fs-5" style="color: var(--color-mocha);"></i>
                        <h5 class="fw-bold font-heading mb-0 text-dark">Riwayat Perjalanan Pesanan</h5>
                    </div>
                    <div class="ps-2">
                        @foreach($order->statusHistories as $history)
                            <div class="border-start border-2 ps-3 pb-3 position-relative" style="border-color: var(--color-mocha) !important;">
                                <div class="position-absolute top-0 start-0 translate-middle rounded-circle" style="width: 10px; height: 10px; background: var(--color-mocha); margin-left: -1px;"></div>
                                <div class="small text-muted" style="font-size: 0.75rem;">{{ $history->created_at->format('d M Y, H:i') }} WIB</div>
                                <div class="fw-bold text-dark font-heading">{{ $history->new_status }}</div>
                                @if($history->remarks)
                                    <div class="small text-secondary mt-1">{{ $history->remarks }}</div>
                                @endif
                                <div class="small text-muted fst-italic mt-1" style="font-size: 0.72rem;">Oleh: {{ $history->changer?->name ?? 'Sistem' }}</div>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif
        </div>

        <!-- Right Side: Payment & Cancellation Actions -->
        <div class="col-lg-4">
            <!-- Payment Status Box -->
            <x-card class="p-4 mb-4 border-0 shadow">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom" style="border-color: rgba(216, 197, 174, 0.3) !important;">
                    <i class="bi bi-credit-card-2-front fs-5" style="color: var(--color-mocha);"></i>
                    <h6 class="fw-bold font-heading mb-0 text-dark">Status Pembayaran</h6>
                </div>

                <div class="p-3 rounded-4 mb-3" style="background: #FAF8F5; border: 1.5px solid rgba(216, 197, 174, 0.45);">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-secondary">Metode:</span>
                        <strong class="small text-dark">{{ $order->payment?->payment_method ?? 'BCA Merchant' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-secondary">Total Tagihan:</span>
                        <strong class="text-dark font-heading fs-6">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small text-secondary">Status Bukti:</span>
                        @if($order->payment && $order->payment->proof_file_path)
                            <span class="badge rounded-pill px-3 py-1 {{ $order->payment->isValid() ? 'bg-success' : ($order->payment->isRejected() ? 'bg-danger' : 'bg-info') }} small">
                                {{ $order->payment->verification_status }}
                            </span>
                        @else
                            <span class="badge rounded-pill px-3 py-1" style="background: rgba(245, 158, 11, 0.15); color: #B45309;">
                                Belum Diunggah
                            </span>
                        @endif
                    </div>
                    @if($order->payment?->admin_notes)
                        <div class="small text-muted mt-2 pt-2 border-top" style="border-color: rgba(216, 197, 174, 0.3) !important;">
                            <strong>Catatan Admin:</strong> {{ $order->payment->admin_notes }}
                        </div>
                    @endif
                </div>

                @if($order->payment?->proof_file_path)
                    <div class="mb-3 text-center">
                        <span class="small text-muted d-block mb-1" style="font-size: 0.78rem;">Berkas Bukti Transfer:</span>
                        <a href="{{ asset('storage/' . $order->payment->proof_file_path) }}" target="_blank" class="btn btn-sm btn-outline-mocha px-3 py-1 d-inline-flex align-items-center gap-1">
                            <i class="bi bi-image"></i> Lihat Bukti Terunggah
                        </a>
                    </div>
                @endif

                @if($order->status === \App\Models\Order::STATUS_WAITING_PAYMENT)
                    <a href="{{ route('customer.orders.payment', $order->id) }}" class="btn btn-mocha w-100 py-3 shadow-sm d-flex align-items-center justify-content-center gap-2 mb-2">
                        <i class="bi bi-upload"></i> {{ $order->payment?->proof_file_path ? 'Unggah Ulang Bukti' : 'Unggah Bukti Transfer' }}
                    </a>
                @endif

                <span class="text-secondary small text-center d-block mt-2" style="font-size: 0.75rem; line-height: 1.4;">
                    Staf admin memverifikasi mutasi bank secara manual pada jam kerja.
                </span>
            </x-card>

            <!-- Cancellation Box -->
            <x-card class="p-4 border-0 shadow">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-x-octagon text-danger fs-5"></i>
                    <h6 class="fw-bold font-heading mb-0 text-danger">Pengajuan Pembatalan</h6>
                </div>

                @if($order->cancellationRequest)
                    <div class="alert {{ $order->cancellationRequest->isApproved() ? 'alert-success' : ($order->cancellationRequest->isRejected() ? 'alert-danger' : 'alert-warning') }} p-3 mb-0 rounded-4 small border-0 shadow-sm">
                        <div class="fw-bold mb-1">Status: {{ $order->cancellationRequest->status }}</div>
                        <div class="mb-1"><strong>Alasan:</strong> {{ $order->cancellationRequest->reason }}</div>
                        @if($order->cancellationRequest->admin_notes)
                            <div class="mt-2 pt-2 border-top">
                                <strong>Catatan Admin:</strong> {{ $order->cancellationRequest->admin_notes }}
                            </div>
                        @endif
                    </div>
                @elseif($order->canBeCancelled())
                    <p class="small text-secondary mb-3" style="line-height: 1.6;">
                        Apabila terdapat kekeliruan varian sebelum pesanan diproses atau dikirim, Anda dapat mengajukan pembatalan ke staf admin operasional.
                    </p>
                    <a href="{{ route('customer.orders.cancellation', $order->id) }}" class="btn btn-outline-danger btn-sm w-100 py-2 rounded-3">
                        Ajukan Permohonan Batal
                    </a>
                    <div class="small text-muted mt-2 text-center" style="font-size: 0.72rem;">
                        * Permohonan pembatalan memerlukan persetujuan admin.
                    </div>
                @else
                    <p class="small text-muted mb-0" style="line-height: 1.5;">
                        Pesanan ini telah berada pada tahap <strong>{{ $order->status }}</strong> sehingga permohonan pembatalan online sudah dinonaktifkan.
                    </p>
                @endif
            </x-card>
        </div>
    </div>
</div>
@endsection
