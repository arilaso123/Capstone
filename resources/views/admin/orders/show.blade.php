@extends('layouts.admin')

@section('title', 'Kelola Pesanan #' . $order->order_number)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Pesanan</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $order->order_number }}</li>
            </ol>
        </nav>
        <h2 class="h4 fw-bold mb-0 font-heading">Detail & Pembaruan Status Pesanan</h2>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Order details & Status Transition Engine -->
    <div class="col-lg-8">
        <!-- Order Fulfillment Stepper -->
        <div class="card card-uyuy p-4 mb-4">
            <h6 class="fw-bold text-uppercase small text-muted mb-2">Alur Status Pesanan Baku (5 Tahapan)</h6>
            <x-order-timeline :status="$order->status" />
        </div>

        <!-- Status Transition Controller for Admin -->
        <div class="card card-uyuy p-4 mb-4 border-mocha" style="background: #FFFDFB;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold font-heading mb-0 text-dark">
                    <i class="bi bi-arrow-repeat me-1 text-mocha"></i> Perbarui Status Pesanan
                </h5>
                <x-status-badge :status="$order->status" />
            </div>

            <p class="small text-muted mb-3">
                Perubahan status pesanan harus dilakukan sesuai alur operasional yang baku. Setiap perubahan akan dicatat dalam riwayat perubahan status pesanan.
            </p>

            <form action="{{ route('admin.orders.status.update', $order->id) }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="new_status" class="form-label small fw-semibold">Pilih Status Baru:</label>
                        <select class="form-select @error('new_status') is-invalid @enderror" id="new_status" name="new_status" required>
                            <option value="">-- Pilih Transisi Status --</option>
                            @foreach(\App\Models\Order::$statuses as $statusKey)
                                <option value="{{ $statusKey }}" {{ $order->status === $statusKey ? 'disabled' : '' }}>
                                    {{ $statusKey }} {{ $order->status === $statusKey ? '(Status Saat Ini)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('new_status')
                            <div class="invalid-feedback small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="remarks" class="form-label small fw-semibold">Catatan Perubahan (Keterangan):</label>
                        <input type="text" class="form-control" id="remarks" name="remarks" placeholder="Contoh: No. Resi JNE / Catatan pengemasan...">
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-mocha px-4">
                            <i class="bi bi-check2-circle me-1"></i> Simpan Pembaruan Status
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Ordered Items -->
        <div class="card card-uyuy p-4 mb-4">
            <h5 class="fw-bold font-heading mb-3">Item Parfum yang Dipesan</h5>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Nama Varian</th>
                            <th>Vendor</th>
                            <th>Ukuran</th>
                            <th>Harga Satuan</th>
                            <th class="text-center">Jumlah</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($order->orderDetails as $detail)
                            <tr>
                                <td><strong class="text-dark">{{ $detail->product_name }}</strong></td>
                                <td>{{ $detail->product?->brand ?? 'In Parfume' }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $detail->size_ml }} ml</span></td>
                                <td>Rp {{ number_format($detail->unit_price, 0, ',', '.') }}</td>
                                <td class="text-center">{{ $detail->quantity }}</td>
                                <td class="text-end fw-bold">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Tidak ada rincian item.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="border-top">
                        <tr>
                            <th colspan="5" class="text-end pt-3">Total Tagihan Pre-Order:</th>
                            <th class="text-end pt-3 fs-5 text-dark fw-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Order Status Histories -->
        <div class="card card-uyuy p-4">
            <h5 class="fw-bold font-heading mb-3"><i class="bi bi-clock-history me-2 text-mocha"></i> Riwayat Perubahan Status (Audit Trail)</h5>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu Transisi</th>
                            <th>Status Sebelumnya</th>
                            <th>Status Baru</th>
                            <th>Dicatat Oleh</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($order->statusHistories as $history)
                            <tr>
                                <td>{{ $history->created_at->format('d M Y, H:i') }} WIB</td>
                                <td>{{ $history->previous_status ?? '-' }}</td>
                                <td><x-status-badge :status="$history->new_status" /></td>
                                <td>{{ $history->changer?->name ?? 'System' }}</td>
                                <td>{{ $history->remarks ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada riwayat perubahan status.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Customer Info, Payment & Cancellation Status -->
    <div class="col-lg-4">
        <!-- Customer Details -->
        <div class="card card-uyuy p-4 mb-4">
            <h6 class="fw-bold font-heading mb-3"><i class="bi bi-person-lines-fill me-2 text-mocha"></i> Data Pemesan</h6>
            <div class="small">
                <div class="mb-2">
                    <span class="text-muted d-block">Nama Lengkap:</span>
                    <strong class="text-dark fs-6">{{ $order->recipient_name }}</strong>
                </div>
                <div class="mb-2">
                    <span class="text-muted d-block">WhatsApp:</span>
                    <a href="https://wa.me/{{ preg_replace('/^0/', '62', $order->recipient_phone) }}" target="_blank" class="text-success text-decoration-none fw-semibold">
                        <i class="bi bi-whatsapp me-1"></i> {{ $order->recipient_phone }}
                    </a>
                </div>
                <div class="mb-2">
                    <span class="text-muted d-block">Email Akun:</span>
                    <span class="text-dark">{{ $order->customer?->user?->email ?? '-' }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted d-block">Alamat Pengiriman:</span>
                    <div class="p-2 bg-light rounded border mt-1">
                        {{ $order->shipping_address }}
                    </div>
                </div>
                @if($order->customer_notes)
                    <div class="mb-0">
                        <span class="text-muted d-block">Catatan Pemesan:</span>
                        <div class="p-2 bg-light rounded border mt-1 fst-italic">
                            "{{ $order->customer_notes }}"
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Payment Check Link -->
        <div class="card card-uyuy p-4 mb-4">
            <h6 class="fw-bold font-heading mb-3"><i class="bi bi-credit-card me-2 text-mocha"></i> Pemeriksaan Pembayaran</h6>
            <div class="p-3 rounded bg-light border mb-3 small">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Metode:</span>
                    <strong>{{ $order->payment?->payment_method ?? 'BCA Merchant' }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Status Bukti:</span>
                    @if($order->payment && $order->payment->proof_file_path)
                        <span class="badge {{ $order->payment->isValid() ? 'bg-success' : ($order->payment->isRejected() ? 'bg-danger' : 'bg-primary') }}">
                            {{ $order->payment->verification_status }}
                        </span>
                    @else
                        <span class="badge bg-warning text-dark">Belum Diunggah</span>
                    @endif
                </div>
                @if($order->payment?->uploaded_at)
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Diunggah:</span>
                        <span>{{ $order->payment->uploaded_at->format('d M Y, H:i') }}</span>
                    </div>
                @endif
            </div>
            @if($order->payment)
                <a href="{{ route('admin.payments.verify', $order->payment->id) }}" class="btn btn-mocha btn-sm w-100">
                    <i class="bi bi-search me-1"></i> Buka Layar Verifikasi
                </a>
            @endif
        </div>

        <!-- Cancellation Request Status if any -->
        @if($order->cancellationRequest)
            <div class="card card-uyuy p-4 border-danger">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold font-heading mb-0 text-danger"><i class="bi bi-x-octagon me-1"></i> Permohonan Batal</h6>
                    <span class="badge {{ $order->cancellationRequest->isApproved() ? 'bg-success' : ($order->cancellationRequest->isRejected() ? 'bg-danger' : 'bg-warning text-dark') }}">
                        {{ $order->cancellationRequest->status }}
                    </span>
                </div>
                <p class="small text-muted mb-2">
                    <strong>Alasan:</strong> {{ $order->cancellationRequest->reason }}
                </p>
                @if($order->cancellationRequest->admin_notes)
                    <p class="small text-muted mb-2">
                        <strong>Catatan Admin:</strong> {{ $order->cancellationRequest->admin_notes }}
                    </p>
                @endif
                <a href="{{ route('admin.cancellations.show', $order->cancellationRequest->id) }}" class="btn btn-outline-danger btn-sm w-100">
                    Lihat Permohonan
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
