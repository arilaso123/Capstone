@extends('layouts.admin')

@section('title', 'Putusan Permohonan Pembatalan #' . ($cancellation->order?->order_number ?? ''))

@section('content')
<div class="admin-cancellation-detail">
<header class="workspace-page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.cancellations.index') }}">Pembatalan</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $cancellation->order?->order_number ?? '' }}</li>
            </ol>
        </nav>
        <h1>Tinjau permohonan pembatalan</h1>
    </div>
    <a href="{{ route('admin.cancellations.index') }}" class="workspace-back-link">
        <i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke daftar
    </a>
</header>

<div class="row g-4 justify-content-center">
    <div class="col-lg-9 col-xl-8">
        <!-- Cancellation Request Summary -->
        <div class="card card-uyuy admin-cancellation-review-panel p-3 p-md-4 mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h2 class="h5 mb-0">Informasi Pengajuan Pembatalan</h2>
                <x-status-badge :status="$cancellation->status" />
            </div>

            <div class="p-3 bg-light border mb-4 small">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Nomor Pesanan</span>
                        <span>{{ $cancellation->order?->order_number ?? '-' }}</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Pelanggan Pemohon</span>
                        <span>{{ $cancellation->order?->customer?->full_name ?? $cancellation->order?->recipient_name }}</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Waktu Pengajuan</span>
                        <span>{{ $cancellation->created_at->copy()->timezone('Asia/Jakarta')->format('d F Y, H:i') }} WIB</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Status Pesanan Terkini</span>
                        <x-status-badge :status="$cancellation->order?->status ?? 'Menunggu Pembayaran'" />
                    </div>
                </div>
            </div>

            <div class="mb-2">
                <span class="form-label small d-block">Alasan Pengajuan oleh Pelanggan</span>
                <div class="p-3 bg-light border small">
                    "{{ $cancellation->reason }}"
                </div>
            </div>
            @if($cancellation->refunded_at)
                <div class="alert alert-success small mb-0 mt-3">
                    Refund tercatat Rp {{ number_format((float) $cancellation->refund_amount, 0, ',', '.') }}; referensi {{ $cancellation->refund_reference }}; {{ $cancellation->refunded_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB.
                    <a href="{{ route('cancellations.refund-proof', $cancellation->id) }}" class="alert-link">Lihat bukti refund</a>
                </div>
            @endif
        </div>

        <!-- Decision Action Form -->
        <div class="card card-uyuy admin-cancellation-decision-panel p-3 p-md-4">
            <h2 class="h5 mb-2"><i class="bi bi-check2-circle me-2" aria-hidden="true"></i> Catat Keputusan Admin</h2>
            <p class="small text-muted mb-3">
                Menyetujui pembatalan akan mengubah status pesanan utama menjadi <strong>Dibatalkan</strong>.
            </p>

            @if($cancellation->status !== \App\Models\CancellationRequest::STATUS_PENDING)
                <div class="alert alert-info mb-0 small">Permohonan ini sudah diputuskan: <strong>{{ $cancellation->status }}</strong>.</div>
            @else
            @if($cancellation->order?->status === \App\Models\Order::STATUS_PAYMENT_VERIFIED)
                <div class="alert alert-warning small">
                    Pesanan sudah dibayar. Sebelum menyetujui, Admin wajib mencatat refund manual, memasukkan nominal penuh sesuai pembayaran, nomor referensi transfer, dan mengunggah bukti refund.
                </div>
            @elseif($cancellation->order?->payment?->isPending() && $cancellation->order?->payment?->proof_file_path)
                <div class="alert alert-warning small">
                    Bukti transfer masih menunggu verifikasi. Periksa pembayaran terlebih dahulu sebelum menyetujui pembatalan.
                    <a href="{{ route('admin.payments.verify', $cancellation->order->payment->id) }}" class="alert-link">Buka pemeriksaan pembayaran</a>.
                </div>
            @elseif($cancellation->order?->payment?->isRejected() && $cancellation->order?->payment?->proof_file_path)
                <div class="alert alert-info small">
                    Bukti transfer sebelumnya ditolak. Pastikan mutasi bank sudah direkonsiliasi dan isi catatan keputusan sebelum menyetujui pembatalan.
                </div>
            @endif
            <form action="{{ route('admin.cancellations.decide.post', $cancellation->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <span class="form-label small d-block mb-2">Pilihan Keputusan <span aria-hidden="true">*</span></span>
                    <div class="d-flex flex-column flex-sm-row gap-3">
                        @if(!($cancellation->order?->payment?->isPending() && $cancellation->order?->payment?->proof_file_path))
                        <div class="form-check custom-radio flex-fill">
                            <input class="form-check-input" type="radio" name="status" id="decisionApprove" value="Disetujui" aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}" @if($errors->has('status')) aria-describedby="cancellation-status-error" @endif
                                {{ old('status', $cancellation->status) === 'Disetujui' ? 'checked' : '' }}>
                            <label class="form-check-label p-3 border d-block text-center" for="decisionApprove">
                                <i class="bi bi-check-circle-fill me-1" aria-hidden="true"></i> Setujui Pembatalan
                            </label>
                        </div>
                        @endif
                        <div class="form-check custom-radio flex-fill">
                            <input class="form-check-input" type="radio" name="status" id="decisionReject" value="Ditolak" aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}" @if($errors->has('status')) aria-describedby="cancellation-status-error" @endif
                                {{ old('status', $cancellation->status) === 'Ditolak' ? 'checked' : '' }}>
                            <label class="form-check-label p-3 border d-block text-center" for="decisionReject">
                                <i class="bi bi-x-circle-fill me-1" aria-hidden="true"></i> Tolak Pembatalan
                            </label>
                        </div>
                    </div>
                    @error('status')
                        <div class="text-danger small mt-1" id="cancellation-status-error">{{ $message }}</div>
                    @enderror
                </div>

                @if($cancellation->order?->status === \App\Models\Order::STATUS_PAYMENT_VERIFIED)
                    <div class="row g-3 mb-4 p-3 rounded bg-light border">
                        <div class="col-md-6">
                            <label for="refund_amount" class="form-label small fw-semibold">Nominal refund (Rp) <span class="text-danger">*</span></label>
                            <input type="number" id="refund_amount" name="refund_amount" class="form-control @error('refund_amount') is-invalid @enderror" min="0.01" step="0.01" value="{{ old('refund_amount', $cancellation->order->payment?->amount) }}" aria-invalid="{{ $errors->has('refund_amount') ? 'true' : 'false' }}" @if($errors->has('refund_amount')) aria-describedby="refund-amount-error" @endif>
                            @error('refund_amount')<div class="invalid-feedback" id="refund-amount-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="refund_reference" class="form-label small fw-semibold">Referensi transfer refund <span class="text-danger">*</span></label>
                            <input type="text" id="refund_reference" name="refund_reference" class="form-control @error('refund_reference') is-invalid @enderror" maxlength="100" value="{{ old('refund_reference') }}" aria-invalid="{{ $errors->has('refund_reference') ? 'true' : 'false' }}" @if($errors->has('refund_reference')) aria-describedby="refund-reference-error" @endif>
                            @error('refund_reference')<div class="invalid-feedback" id="refund-reference-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="refund_proof" class="form-label small fw-semibold">Bukti transfer refund <span class="text-danger">*</span></label>
                            <input type="file" id="refund_proof" name="refund_proof" class="form-control @error('refund_proof') is-invalid @enderror" accept="image/jpeg,image/png,image/webp" aria-invalid="{{ $errors->has('refund_proof') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('refund_proof') ? 'refund-proof-help refund-proof-error' : 'refund-proof-help' }}">
                            <div class="form-text" id="refund-proof-help">JPG, PNG, atau WEBP. Maksimal 2 MB. Berkas disimpan privat.</div>
                            @error('refund_proof')<div class="invalid-feedback" id="refund-proof-error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                @endif

                <div class="mb-4">
                    @php($reconciliationNoteRequired = $cancellation->order?->payment?->isRejected() && $cancellation->order?->payment?->proof_file_path)
                    <label for="admin_notes" class="form-label small fw-semibold">Catatan Keputusan Admin untuk Pelanggan <span class="text-danger">(wajib jika ditolak{{ $reconciliationNoteRequired ? ' atau menyetujui setelah bukti ditolak' : '' }})</span></label>
                    <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3" {{ $reconciliationNoteRequired ? 'required' : '' }} placeholder="Tuliskan hasil pemeriksaan mutasi atau alasan keputusan..." aria-invalid="{{ $errors->has('admin_notes') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('admin_notes') ? 'admin-notes-help admin-notes-error' : 'admin-notes-help' }}">{{ old('admin_notes', $cancellation->admin_notes) }}</textarea>
                    <div class="form-text small" id="admin-notes-help">Catatan ini akan dapat dilihat oleh pelanggan di riwayat pesanannya.</div>
                    @error('admin_notes')
                        <div class="text-danger small mt-1" id="admin-notes-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('admin.cancellations.index') }}" class="btn btn-outline-secondary">Kembali</a>
                    <button type="submit" class="btn btn-mocha px-4">
                        <i class="bi bi-send-check me-1"></i> Simpan & Kirim Keputusan
                    </button>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>
</div>
@endsection
