@extends('layouts.admin')

@section('title', 'Verifikasi Bukti Pembayaran #' . ($payment->order?->order_number ?? ''))

@section('content')
<section class="admin-payment-review" aria-labelledby="paymentReviewTitle">
    <nav class="owner-form-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('admin.payments.index') }}">Pembayaran</a>
        <span aria-hidden="true">/</span>
        <span aria-current="page">{{ $payment->order?->order_number ?? 'Verifikasi' }}</span>
    </nav>

    <header class="admin-review-header">
        <div>
            <p class="owner-recap-eyebrow">ADMIN / PEMERIKSAAN PEMBAYARAN</p>
            <h1 id="paymentReviewTitle">Verifikasi bukti pembayaran</h1>
            <p>{{ $payment->order?->order_number ?? 'Pesanan tidak tersedia' }}</p>
        </div>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">Kembali ke antrean</a>
    </header>

    <div class="admin-review-grid">
        <div class="admin-review-proof-column">
            <section class="admin-review-panel" aria-labelledby="proofTitle">
                <header class="admin-review-panel-header">
                    <div>
                        <p class="owner-recap-eyebrow">LAMPIRAN</p>
                        <h2 id="proofTitle">Bukti dari pelanggan</h2>
                    </div>
                    <span class="admin-review-file-name">{{ basename($payment->proof_file_path ?? 'bukti pembayaran') }}</span>
                </header>

                <div class="admin-review-proof-frame">
                    @if($payment->proof_file_path && !str_starts_with($payment->proof_file_path, 'http'))
                        @php($isPdfProof = str_ends_with(strtolower($payment->proof_file_path), '.pdf'))
                        @if($isPdfProof)
                            <div class="admin-proof-preview-wrap">
                                <i class="bi bi-file-earmark-pdf fs-1 text-danger" aria-hidden="true"></i>
                                <a href="{{ route('payments.proof', $payment) }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary">
                                    <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Buka berkas PDF secara aman
                                </a>
                            </div>
                        @else
                            <div class="admin-proof-preview-wrap">
                                <img
                                    src="{{ route('payments.proof', $payment) }}"
                                    alt="Bukti pembayaran pesanan {{ $payment->order?->order_number ?? '' }}"
                                    class="admin-proof-preview-img"
                                    data-bs-toggle="modal"
                                    data-bs-target="#proofZoomModal"
                                    title="Klik untuk memperbesar bukti transfer"
                                >
                                <div class="admin-proof-actions">
                                    <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#proofZoomModal">
                                        <i class="bi bi-zoom-in" aria-hidden="true"></i> Perbesar gambar
                                    </button>
                                    <a href="{{ route('payments.proof', $payment) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Buka tab baru
                                    </a>
                                </div>
                            </div>

                            <!-- Modal Zoom Bukti Transfer (Editorial Stone) -->
                            <div class="modal fade" id="proofZoomModal" tabindex="-1" aria-labelledby="proofZoomModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-xl">
                                    <div class="modal-content border-0 shadow-lg overflow-hidden">
                                        <div class="modal-header" style="background: var(--color-soot); color: var(--color-surface);">
                                            <h6 class="modal-title mb-0" id="proofZoomModalLabel" style="color: var(--color-surface);">
                                                Pratinjau Bukti Pembayaran — {{ $payment->order?->order_number ?? '' }}
                                            </h6>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                        </div>
                                        <div class="modal-body text-center p-4" style="background: var(--color-surface-subtle);">
                                            <img src="{{ route('payments.proof', $payment) }}" alt="Bukti pembayaran ukuran penuh" class="img-fluid" style="max-height: 78vh; object-fit: contain; background: #ffffff; border: 1px solid var(--color-line);">
                                        </div>
                                        <div class="modal-footer justify-content-between" style="background: var(--color-surface);">
                                            <span class="small" style="color: var(--color-muted);">
                                                Tagihan: <strong style="color: var(--color-soot);">Rp {{ number_format($payment->order?->total_amount ?? 0, 0, ',', '.') }}</strong>
                                                &bull; Dilaporkan: <strong style="color: var(--color-soot);">Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong>
                                            </span>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('payments.proof', $payment) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary">Buka resolusi asli</a>
                                                <button type="button" class="btn btn-sm btn-dark" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @elseif($payment->proof_file_path)
                        <div class="admin-review-proof-message" role="status">
                            <i class="bi bi-shield-exclamation" aria-hidden="true"></i>
                            <strong>Path bukti berasal dari URL eksternal.</strong>
                            <span>Minta pelanggan mengunggah ulang bukti melalui halaman pesanan sebelum menetapkan keputusan.</span>
                        </div>
                    @else
                        <div class="admin-review-proof-message" role="status">
                            <i class="bi bi-image" aria-hidden="true"></i>
                            <strong>Bukti pembayaran belum tersedia.</strong>
                        </div>
                    @endif
                </div>

                <footer class="admin-review-proof-meta">
                    <span>Diunggah {{ $payment->uploaded_at?->copy()->timezone('Asia/Jakarta')->format('d M Y, H:i') ?? '—' }} WIB</span>
                    <span>Akses terbatas</span>
                </footer>
            </section>

            @if($payment->attempts->isNotEmpty())
                <section class="admin-review-panel admin-review-history">
                    <details>
                        <summary>Riwayat unggahan ({{ $payment->attempts->count() }})</summary>
                        <div class="admin-review-attempts">
                            @foreach($payment->attempts->sortByDesc('attempt_number') as $attempt)
                                <article class="admin-review-attempt">
                                    <header>
                                        <strong>Unggahan #{{ $attempt->attempt_number }}</strong>
                                        <span class="admin-payment-status">{{ $attempt->verification_status }}</span>
                                    </header>
                                    <p>{{ $attempt->uploaded_at?->copy()->timezone('Asia/Jakarta')->format('d M Y, H:i') ?? '—' }} WIB · Rp {{ number_format((float) $attempt->amount, 0, ',', '.') }}</p>
                                    @if($attempt->admin_notes)
                                        <p>Catatan Admin: {{ $attempt->admin_notes }}</p>
                                    @endif
                                    @if($attempt->proof_file_path && !preg_match('/^https?:\/\//i', $attempt->proof_file_path))
                                        <a href="{{ route('payments.attempt-proof', $attempt) }}" target="_blank" rel="noopener noreferrer">Buka berkas unggahan #{{ $attempt->attempt_number }}</a>
                                    @elseif($attempt->proof_file_path)
                                        <span class="admin-review-history-note">Path bukti lama bersumber eksternal.</span>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </details>
                </section>
            @endif
        </div>

        <div class="admin-review-decision-column">
            <section class="admin-review-panel" aria-labelledby="paymentMatchTitle">
                <header class="admin-review-panel-header">
                    <div>
                        <p class="owner-recap-eyebrow">DATA PESANAN</p>
                        <h2 id="paymentMatchTitle">Cocokkan pembayaran</h2>
                    </div>
                </header>
                <dl class="admin-review-data">
                    <div><dt>Nomor pesanan</dt><dd>{{ $payment->order?->order_number ?? '—' }}</dd></div>
                    <div><dt>Nama pemesan</dt><dd>{{ $payment->order?->customer?->full_name ?? $payment->order?->recipient_name ?? '—' }}</dd></div>
                    <div><dt>Total tagihan</dt><dd>Rp {{ number_format($payment->order?->total_amount ?? 0, 0, ',', '.') }}</dd></div>
                    <div><dt>Nominal pembayaran</dt><dd>Rp {{ number_format($payment->amount, 0, ',', '.') }}</dd></div>
                </dl>
                <p class="admin-review-instruction">Cocokkan nominal, waktu transaksi, dan informasi pengirim pada bukti dengan data pembayaran sebelum menetapkan hasil.</p>
            </section>

            <section class="admin-review-panel" aria-labelledby="decisionTitle">
                <header class="admin-review-panel-header">
                    <div>
                        <p class="owner-recap-eyebrow">KEPUTUSAN</p>
                        <h2 id="decisionTitle">Catat hasil verifikasi</h2>
                    </div>
                </header>

                @if($payment->isPending() && $payment->order?->status === \App\Models\Order::STATUS_WAITING_PAYMENT && $payment->proof_file_path && !str_starts_with($payment->proof_file_path, 'http'))
                    <form action="{{ route('admin.payments.verify.post', $payment->id) }}" method="POST" class="admin-review-form">
                        @csrf
                        <fieldset class="admin-review-status-fieldset" @if($errors->has('verification_status')) aria-describedby="verification-status-error" @endif>
                            <legend>Status verifikasi <span aria-hidden="true">*</span></legend>
                            <div class="admin-review-status-options">
                                <label class="admin-review-status-option">
                                    <input type="radio" name="verification_status" id="statusValid" value="Valid" {{ old('verification_status', $payment->verification_status) === 'Valid' ? 'checked' : '' }} required aria-invalid="{{ $errors->has('verification_status') ? 'true' : 'false' }}" @if($errors->has('verification_status')) aria-describedby="verification-status-error" @endif>
                                    <span>Diterima</span>
                                </label>
                                <label class="admin-review-status-option">
                                    <input type="radio" name="verification_status" id="statusReject" value="Ditolak" {{ old('verification_status', $payment->verification_status) === 'Ditolak' ? 'checked' : '' }} aria-invalid="{{ $errors->has('verification_status') ? 'true' : 'false' }}" @if($errors->has('verification_status')) aria-describedby="verification-status-error" @endif>
                                    <span>Ditolak</span>
                                </label>
                            </div>
                            @error('verification_status')<p class="admin-review-error" id="verification-status-error" role="alert">{{ $message }}</p>@enderror
                        </fieldset>

                        <div class="admin-review-notes-field">
                            <label for="admin_notes">Catatan Admin</label>
                            <textarea class="form-control @error('admin_notes') is-invalid @enderror" id="admin_notes" name="admin_notes" rows="4" maxlength="1000" placeholder="Catat hasil pencocokan atau alasan penolakan." aria-invalid="{{ $errors->has('admin_notes') ? 'true' : 'false' }}" @if($errors->has('admin_notes')) aria-describedby="admin-notes-error" @endif>{{ old('admin_notes', $payment->admin_notes) }}</textarea>
                            <span>Alasan wajib dicatat jika bukti ditolak.</span>
                            @error('admin_notes')<p class="admin-review-error" id="admin-notes-error" role="alert">{{ $message }}</p>@enderror
                        </div>

                        <button type="submit" class="btn btn-dark admin-review-submit">Simpan hasil verifikasi</button>
                    </form>
                @else
                    <div class="admin-review-unavailable" role="status">
                        Keputusan tidak tersedia. Pembayaran harus menunggu verifikasi, memiliki bukti lokal yang dapat diakses, dan pesanannya masih menunggu pembayaran.
                    </div>
                @endif
            </section>
        </div>
    </div>
</section>
@endsection
