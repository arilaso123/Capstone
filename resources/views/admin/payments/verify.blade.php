@extends('layouts.admin')

@section('title', 'Verifikasi Bukti Pembayaran #' . ($payment->order?->order_number ?? ''))

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">Pembayaran</a></li>
                <li class="breadcrumb-item active" aria-current="page">Verifikasi {{ $payment->order?->order_number ?? '' }}</li>
            </ol>
        </nav>
        <h2 class="h4 fw-bold mb-0 font-heading">Layar Pemeriksaan Bukti Pembayaran</h2>
    </div>
    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
    </a>
</div>

<div class="row g-4">
    <!-- Left: Payment Proof Image Inspection -->
    <div class="col-lg-7">
        <div class="card card-uyuy p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold font-heading mb-0">Berkas Bukti Transfer</h5>
                <span class="badge bg-light text-dark border">
                    <i class="bi bi-file-earmark-image me-1"></i> {{ basename($payment->proof_file_path ?? 'bukti.jpg') }}
                </span>
            </div>

            <div class="p-2 bg-light rounded-3 text-center border overflow-hidden mb-3" style="max-height: 520px;">
                @if($payment->proof_file_path)
                    @php
                        $imageSrc = str_starts_with($payment->proof_file_path, 'http')
                            ? $payment->proof_file_path
                            : asset('storage/' . $payment->proof_file_path);
                    @endphp
                    <a href="{{ $imageSrc }}" target="_blank">
                        <img src="{{ $imageSrc }}" alt="Bukti Transfer BCA" class="img-fluid rounded" style="max-height: 480px; object-fit: contain;">
                    </a>
                @else
                    <div class="py-5 text-muted">
                        <i class="bi bi-image fs-1 d-block mb-2"></i>
                        Tidak ada berkas bukti yang diunggah.
                    </div>
                @endif
            </div>

            <div class="d-flex justify-content-between align-items-center small text-muted">
                <span>Diunggah pada: <strong>{{ $payment->uploaded_at ? $payment->uploaded_at->format('d F Y, H:i') : '-' }} WIB</strong></span>
                <span class="text-secondary"><i class="bi bi-shield-check me-1"></i> Retensi Berkas: Aktif (&le; 30 hari)</span>
            </div>
        </div>
    </div>

    <!-- Right: Verification Form & Data Match -->
    <div class="col-lg-5">
        <!-- Order & Amount Comparison -->
        <div class="card card-uyuy p-4 mb-4">
            <h6 class="fw-bold font-heading mb-3"><i class="bi bi-clipboard-data me-2 text-mocha"></i> Perbandingan Tagihan</h6>

            <div class="p-3 rounded-3 bg-light border mb-3 small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Nomor Pesanan:</span>
                    <strong>{{ $payment->order?->order_number ?? '-' }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Nama Pelanggan:</span>
                    <strong>{{ $payment->order?->customer?->full_name ?? $payment->order?->recipient_name }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Tagihan Sistem:</span>
                    <strong class="text-dark fs-6">Rp {{ number_format($payment->order?->total_amount ?? 0, 0, ',', '.') }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Nominal Dilaporkan:</span>
                    <strong class="text-success fs-6">Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong>
                </div>
            </div>

            <div class="small text-muted">
                <i class="bi bi-info-circle me-1 text-primary"></i> Pastikan nama pengirim dan jam transaksi pada gambar sesuai dengan mutasi BCA Merchant Anda.
            </div>
        </div>

        <!-- Verification Action Form -->
        <div class="card card-uyuy p-4">
            <h6 class="fw-bold font-heading mb-3"><i class="bi bi-check2-square me-2 text-mocha"></i> Catat Hasil Verifikasi</h6>

            <form action="{{ route('admin.payments.verify.post', $payment->id) }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Keputusan Verifikasi <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3">
                        <div class="form-check custom-radio flex-fill">
                            <input class="form-check-input" type="radio" name="verification_status" id="statusValid" value="Valid"
                                {{ old('verification_status', $payment->verification_status) === 'Valid' ? 'checked' : '' }}>
                            <label class="form-check-label p-2 border rounded-3 d-block text-center border-success bg-light text-success fw-bold" for="statusValid">
                                <i class="bi bi-check-circle me-1"></i> Valid (Diterima)
                            </label>
                        </div>
                        <div class="form-check custom-radio flex-fill">
                            <input class="form-check-input" type="radio" name="verification_status" id="statusReject" value="Ditolak"
                                {{ old('verification_status', $payment->verification_status) === 'Ditolak' ? 'checked' : '' }}>
                            <label class="form-check-label p-2 border rounded-3 d-block text-center border-danger bg-light text-danger fw-bold" for="statusReject">
                                <i class="bi bi-x-circle me-1"></i> Tolak Bukti
                            </label>
                        </div>
                    </div>
                    @error('verification_status')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="admin_notes" class="form-label small fw-semibold">Catatan Verifikasi Admin</label>
                    <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3" placeholder="Contoh: Mutasi BCA masuk sesuai nominal tagihan (Lunas).">{{ old('admin_notes', $payment->admin_notes) }}</textarea>
                    <div class="form-text small">Wajib diisi jika menolak bukti transfer (alasan penolakan).</div>
                </div>

                <button type="submit" class="btn btn-mocha w-100 py-2">
                    <i class="bi bi-save me-1"></i> Simpan Hasil Verifikasi
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
