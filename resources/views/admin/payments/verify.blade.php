@extends('layouts.admin')

@section('title', 'Verifikasi Bukti Pembayaran #' . ($payment->order?->order_number ?? ''))

@section('content')
<!-- Header Breadcrumbs -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom" style="border-color: rgba(216, 197, 174, 0.3) !important;">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none" style="color: var(--color-mocha);">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}" class="text-decoration-none" style="color: var(--color-mocha);">Pembayaran</a></li>
                <li class="breadcrumb-item active text-secondary" aria-current="page">Verifikasi {{ $payment->order?->order_number ?? '' }}</li>
            </ol>
        </nav>
        <h2 class="h3 fw-bold font-heading mb-0 text-dark" style="letter-spacing: -0.02em;">Layar Pemeriksaan Bukti Pembayaran</h2>
    </div>
    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary btn-sm py-2 px-3">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Antrean
    </a>
</div>

<div class="row g-4">
    <!-- Left: Payment Proof Image Inspection -->
    <div class="col-lg-7">
        <x-card class="p-4 border-0 shadow">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom" style="border-color: rgba(216, 197, 174, 0.3) !important;">
                <h5 class="fw-bold font-heading mb-0 text-dark">Berkas Bukti Transfer Pelanggan</h5>
                <span class="badge rounded-pill px-3 py-1" style="background: rgba(139, 107, 80, 0.12); color: var(--color-mocha); font-size: 0.75rem;">
                    <i class="bi bi-file-earmark-image me-1"></i> {{ basename($payment->proof_file_path ?? 'bukti.jpg') }}
                </span>
            </div>

            <div class="p-3 rounded-4 text-center overflow-hidden mb-3" style="background: #FAF8F5; border: 1.5px solid rgba(216, 197, 174, 0.45); min-height: 400px; display: flex; align-items: center; justify-content: center;">
                @if($payment->proof_file_path)
                    @php
                        $imageSrc = str_starts_with($payment->proof_file_path, 'http')
                            ? $payment->proof_file_path
                            : asset('storage/' . $payment->proof_file_path);
                    @endphp
                    <a href="{{ $imageSrc }}" target="_blank" title="Klik untuk membuka ukuran penuh">
                        <img src="{{ $imageSrc }}" alt="Bukti Transfer BCA" class="img-fluid rounded-3 shadow-sm" style="max-height: 480px; object-fit: contain;">
                    </a>
                @else
                    <div class="py-5 text-muted">
                        <i class="bi bi-image fs-1 d-block mb-2" style="color: var(--color-mocha);"></i>
                        Tidak ada berkas bukti yang diunggah.
                    </div>
                @endif
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center small text-secondary">
                <span>Waktu Unggah: <strong>{{ $payment->uploaded_at ? $payment->uploaded_at->format('d F Y, H:i') : '-' }} WIB</strong></span>
                <span class="badge rounded-pill" style="background: rgba(45,36,30,0.06); color: var(--color-charcoal); font-size: 0.75rem;">
                    <i class="bi bi-shield-check me-1"></i> Kebijakan Retensi Aktif (&le; 30 hari)
                </span>
            </div>
        </x-card>
    </div>

    <!-- Right: Verification Form & Data Match -->
    <div class="col-lg-5">
        <!-- Order & Amount Comparison Bento -->
        <x-card class="p-4 mb-4 border-0 shadow">
            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom" style="border-color: rgba(216, 197, 174, 0.3) !important;">
                <i class="bi bi-clipboard-data fs-5" style="color: var(--color-mocha);"></i>
                <h6 class="fw-bold font-heading mb-0 text-dark">Pencocokan Data Mutasi BCA</h6>
            </div>

            <div class="p-3 rounded-4 mb-3 small" style="background: #FAF8F5; border: 1.5px solid rgba(216, 197, 174, 0.45);">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Nomor Faktur:</span>
                    <strong class="text-dark font-heading">{{ $payment->order?->order_number ?? '-' }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Nama Pemesan:</span>
                    <strong class="text-dark">{{ $payment->order?->customer?->full_name ?? $payment->order?->recipient_name }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Total Tagihan Sistem:</span>
                    <strong class="text-dark font-heading fs-6">Rp {{ number_format($payment->order?->total_amount ?? 0, 0, ',', '.') }}</strong>
                </div>
                <div class="d-flex justify-content-between pt-2 border-top" style="border-color: rgba(216, 197, 174, 0.3) !important;">
                    <span class="text-secondary">Nominal Dilaporkan:</span>
                    <strong class="text-success font-heading fs-5">Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong>
                </div>
            </div>

            <div class="small text-secondary" style="font-size: 0.78rem; line-height: 1.5;">
                <i class="bi bi-info-circle me-1" style="color: var(--color-mocha);"></i> Pastikan nama pengirim dan jam transaksi pada gambar sesuai dengan mutasi internet banking BCA Merchant Anda.
            </div>
        </x-card>

        <!-- Verification Action Form -->
        <x-card class="p-4 border-0 shadow">
            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom" style="border-color: rgba(216, 197, 174, 0.3) !important;">
                <i class="bi bi-check2-square fs-5" style="color: var(--color-mocha);"></i>
                <h6 class="fw-bold font-heading mb-0 text-dark">Catat Keputusan Verifikasi</h6>
            </div>

            <form action="{{ route('admin.payments.verify.post', $payment->id) }}" method="POST">
                @csrf

                <!-- Decision Radios -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary mb-2">Status Penetapan <span class="text-danger">*</span></label>
                    <div class="d-flex gap-2">
                        <div class="flex-fill">
                            <input class="btn-check" type="radio" name="verification_status" id="statusValid" value="Valid"
                                {{ old('verification_status', $payment->verification_status) === 'Valid' ? 'checked' : '' }} autocomplete="off">
                            <label class="btn btn-outline-success w-100 p-2 rounded-3 text-center fw-bold small" for="statusValid">
                                <i class="bi bi-check-circle me-1"></i> Valid (Diterima)
                            </label>
                        </div>
                        <div class="flex-fill">
                            <input class="btn-check" type="radio" name="verification_status" id="statusReject" value="Ditolak"
                                {{ old('verification_status', $payment->verification_status) === 'Ditolak' ? 'checked' : '' }} autocomplete="off">
                            <label class="btn btn-outline-danger w-100 p-2 rounded-3 text-center fw-bold small" for="statusReject">
                                <i class="bi bi-x-circle me-1"></i> Tolak Bukti
                            </label>
                        </div>
                    </div>
                    @error('verification_status')
                        <div class="text-danger small mt-1 ps-1" style="font-size: 0.82rem;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Admin Notes -->
                <div class="mb-4">
                    <label for="admin_notes" class="form-label small fw-semibold text-secondary mb-1">Catatan Operasional Admin</label>
                    <div class="input-group-uyuy p-1">
                        <textarea class="form-control border-0 bg-transparent shadow-none" id="admin_notes" name="admin_notes" rows="3" placeholder="Contoh: Mutasi BCA masuk lunas sesuai nominal tagihan.">{{ old('admin_notes', $payment->admin_notes) }}</textarea>
                    </div>
                    <div class="form-text small mt-1">Wajib diisi jika menolak bukti transfer (alasan penolakan).</div>
                </div>

                <x-button type="submit" variant="mocha" class="w-100 py-3 shadow-sm d-flex align-items-center justify-content-center gap-2" icon="bi bi-check2">
                    Simpan Hasil Verifikasi
                </x-button>
            </form>
        </x-card>
    </div>
</div>
@endsection
