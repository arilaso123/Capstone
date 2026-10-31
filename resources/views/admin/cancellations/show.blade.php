@extends('layouts.admin')

@section('title', 'Putusan Permohonan Pembatalan #' . ($cancellation->order?->order_number ?? ''))

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.cancellations.index') }}">Pembatalan</a></li>
                <li class="breadcrumb-item active" aria-current="page">Keputusan {{ $cancellation->order?->order_number ?? '' }}</li>
            </ol>
        </nav>
        <h2 class="h4 fw-bold mb-0 font-heading">Pemeriksaan Permohonan Pembatalan Pesanan</h2>
    </div>
    <a href="{{ route('admin.cancellations.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
    </a>
</div>

<div class="row g-4 justify-content-center">
    <div class="col-lg-8">
        <!-- Cancellation Request Summary -->
        <div class="card card-uyuy p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold font-heading mb-0">Informasi Pengajuan Pembatalan</h5>
                <span class="badge {{ $cancellation->isApproved() ? 'bg-success' : ($cancellation->isRejected() ? 'bg-danger' : 'bg-warning text-dark') }}">
                    {{ $cancellation->status }}
                </span>
            </div>

            <div class="p-3 rounded-3 bg-light border mb-4 small">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Nomor Pesanan:</span>
                        <strong>{{ $cancellation->order?->order_number ?? '-' }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Pelanggan Pemohon:</span>
                        <strong>{{ $cancellation->order?->customer?->full_name ?? $cancellation->order?->recipient_name }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Waktu Pengajuan:</span>
                        <span>{{ $cancellation->created_at->format('d F Y, H:i') }} WIB</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Status Pesanan Terkini:</span>
                        <x-status-badge :status="$cancellation->order?->status ?? 'Menunggu Pembayaran'" />
                    </div>
                </div>
            </div>

            <div class="mb-2">
                <label class="form-label small fw-bold text-dark">Alasan Pengajuan oleh Pelanggan:</label>
                <div class="p-3 rounded-2 bg-light border small">
                    "{{ $cancellation->reason }}"
                </div>
            </div>
        </div>

        <!-- Decision Action Form -->
        <div class="card card-uyuy p-4">
            <h5 class="fw-bold font-heading mb-3"><i class="bi bi-check2-circle me-2 text-mocha"></i> Catat Keputusan Admin</h5>
            <p class="small text-muted mb-3">
                Menyetujui pembatalan akan mengubah status pesanan utama menjadi <strong>Dibatalkan</strong>.
            </p>

            <form action="{{ route('admin.cancellations.decide.post', $cancellation->id) }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Pilihan Keputusan <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3">
                        <div class="form-check custom-radio flex-fill">
                            <input class="form-check-input" type="radio" name="status" id="decisionApprove" value="Disetujui"
                                {{ old('status', $cancellation->status) === 'Disetujui' ? 'checked' : '' }}>
                            <label class="form-check-label p-3 border rounded-3 d-block text-center border-success bg-light text-success fw-bold" for="decisionApprove">
                                <i class="bi bi-check-circle-fill me-1"></i> Setujui Pembatalan
                            </label>
                        </div>
                        <div class="form-check custom-radio flex-fill">
                            <input class="form-check-input" type="radio" name="status" id="decisionReject" value="Ditolak"
                                {{ old('status', $cancellation->status) === 'Ditolak' ? 'checked' : '' }}>
                            <label class="form-check-label p-3 border rounded-3 d-block text-center border-danger bg-light text-danger fw-bold" for="decisionReject">
                                <i class="bi bi-x-circle-fill me-1"></i> Tolak Pembatalan
                            </label>
                        </div>
                    </div>
                    @error('status')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="admin_notes" class="form-label small fw-semibold">Catatan Keputusan Admin untuk Pelanggan</label>
                    <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3" placeholder="Contoh: Pengajuan disetujui. Silakan lakukan pemesanan ulang varian yang sesuai di katalog...">{{ old('admin_notes', $cancellation->admin_notes) }}</textarea>
                    <div class="form-text small">Catatan ini akan dapat dilihat oleh pelanggan di riwayat pesanannya.</div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('admin.cancellations.index') }}" class="btn btn-outline-secondary">Kembali</a>
                    <button type="submit" class="btn btn-mocha px-4">
                        <i class="bi bi-send-check me-1"></i> Simpan & Kirim Keputusan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
