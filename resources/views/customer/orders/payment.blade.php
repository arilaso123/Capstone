@extends('layouts.app')

@section('title', 'Unggah Bukti Pembayaran #' . $order->order_number)

@section('content')
<!-- Header Banner -->
<div class="py-4 border-bottom" style="background: radial-gradient(circle at 10% 20%, #FDFBF7 0%, #F4ECE3 90%); border-color: rgba(216, 197, 174, 0.4) !important;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none" style="color: var(--color-mocha);">Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}" class="text-decoration-none" style="color: var(--color-mocha);">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customer.orders.index') }}" class="text-decoration-none" style="color: var(--color-mocha);">Pesanan Saya</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customer.orders.show', $order->id) }}" class="text-decoration-none" style="color: var(--color-mocha);">{{ $order->order_number }}</a></li>
                <li class="breadcrumb-item active text-secondary" aria-current="page">Unggah Bukti Transfer</li>
            </ol>
        </nav>
        <h1 class="h2 fw-bold font-heading mb-0" style="color: var(--color-charcoal); letter-spacing: -0.02em;">Instruksi & Unggah Bukti Pembayaran</h1>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Payment Instructions Card -->
            <x-card class="p-4 p-md-5 mb-4 border-0 shadow">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom" style="border-color: rgba(216, 197, 174, 0.3) !important;">
                    <i class="bi bi-bank fs-4" style="color: var(--color-mocha);"></i>
                    <h5 class="fw-bold font-heading mb-0 text-dark">Petunjuk Transfer Bank Central Asia (BCA)</h5>
                </div>

                <p class="small text-secondary mb-3" style="line-height: 1.6;">
                    Silakan selesaikan pembayaran tagihan pre-order Anda sesuai total nominal yang tertera melalui rekening merchant resmi Uyuy Jastip di bawah ini:
                </p>

                <!-- Copyable Account Box -->
                <div class="p-4 rounded-4 mb-3" style="background: #FAF8F5; border: 1.5px solid rgba(216, 197, 174, 0.5);">
                    <div class="row g-3 align-items-center">
                        <div class="col-sm-6">
                            <span class="small text-muted d-block" style="font-size: 0.78rem;">Nomor Rekening BCA:</span>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="fs-4 fw-bold font-monospace text-dark" id="rekNumber">8720-1928-33</span>
                                <button type="button" class="btn btn-sm btn-outline-mocha px-2 py-1" onclick="copyRekening()" title="Salin Nomor">
                                    <i class="bi bi-clipboard"></i> Salin
                                </button>
                            </div>
                            <span class="small text-secondary d-block mt-1" style="font-size: 0.78rem;">Atas Nama: <strong>UYUY JASTIP PARFUM</strong></span>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <span class="small text-muted d-block" style="font-size: 0.78rem;">Total yang Wajib Ditransfer:</span>
                            <span class="fs-3 fw-bold font-heading text-dark d-block">
                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                            </span>
                            <span class="badge rounded-pill" style="background: rgba(139, 107, 80, 0.15); color: var(--color-mocha); font-size: 0.75rem;">
                                Faktur #{{ $order->order_number }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="p-3 rounded-3 small text-secondary" style="background: rgba(244, 236, 227, 0.6); border-left: 3px solid var(--color-mocha); line-height: 1.5;">
                    <i class="bi bi-info-circle-fill me-1" style="color: var(--color-mocha);"></i> Mohon cantumkan kode faktur <code>{{ $order->order_number }}</code> pada keterangan transfer agar admin dapat langsung memvalidasi tanpa delay.
                </div>
            </x-card>

            <!-- Upload Form Card -->
            <x-card class="p-4 p-md-5 border-0 shadow">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom" style="border-color: rgba(216, 197, 174, 0.3) !important;">
                    <div>
                        <h5 class="fw-bold font-heading mb-0 text-dark">Formulir Pengunggahan Bukti</h5>
                        <span class="small text-secondary">Kirim berkas foto struk atau screenshot mutasi bank</span>
                    </div>
                    @if($order->payment?->proof_file_path)
                        <span class="badge rounded-pill px-3 py-1 bg-info">
                            Bukti Sebelumnya Tersimpan
                        </span>
                    @endif
                </div>

                <form action="{{ route('customer.orders.payment.post', $order->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Method -->
                    <div class="mb-3">
                        <label for="payment_method" class="form-label small fw-semibold text-secondary mb-1">Metode Pembayaran</label>
                        <div class="input-group-uyuy d-flex align-items-center">
                            <span class="input-group-text"><i class="bi bi-bank"></i></span>
                            <input type="text" class="form-control form-control-uyuy" id="payment_method" name="payment_method" value="BCA Merchant (Transfer Non-Tunai)" readonly style="background-color: #FAF8F5;">
                        </div>
                    </div>

                    <!-- Amount -->
                    <div class="mb-3">
                        <label for="amount" class="form-label small fw-semibold text-secondary mb-1">
                            Jumlah Nominal yang Ditransfer (Rp) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-uyuy d-flex align-items-center @error('amount') border-danger @enderror">
                            <span class="input-group-text fw-bold text-muted">Rp</span>
                            <input type="number" class="form-control form-control-uyuy" id="amount" name="amount" value="{{ old('amount', (int)$order->total_amount) }}" required>
                        </div>
                        @error('amount')
                            <div class="text-danger small mt-1 ps-1" style="font-size: 0.82rem;">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- File Proof -->
                    <div class="mb-4">
                        <label for="proof_file" class="form-label small fw-semibold text-secondary mb-1">
                            Foto Struk / Screenshot Bukti Transfer <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-uyuy d-flex align-items-center p-2 @error('proof_file') border-danger @enderror">
                            <input type="file" class="form-control border-0 bg-transparent shadow-none" id="proof_file" name="proof_file" accept="image/jpeg,image/png,image/jpg,application/pdf" required onchange="previewImage(this)">
                        </div>
                        <span class="text-muted d-block ps-1 mt-1" style="font-size: 0.75rem;">Format didukung: JPG, PNG, atau PDF (maks. 2 MB). Pastikan nominal dan nama rekening terlihat jelas.</span>
                        @error('proof_file')
                            <div class="text-danger small mt-1 ps-1" style="font-size: 0.82rem;">{{ $message }}</div>
                        @enderror

                        <!-- Image Preview Box -->
                        <div id="imagePreviewContainer" class="mt-3 text-center d-none">
                            <span class="small text-muted d-block mb-1">Pratinjau Berkas:</span>
                            <img id="imagePreview" src="#" alt="Pratinjau Bukti" class="rounded-3 shadow-sm border" style="max-height: 220px; max-width: 100%; object-fit: contain;">
                        </div>
                    </div>

                    <!-- Retention Notice -->
                    <div class="p-3 rounded-3 small text-secondary mb-4" style="background: #FAF8F5; border: 1px solid rgba(216, 197, 174, 0.4); line-height: 1.5;">
                        <strong class="text-dark"><i class="bi bi-shield-lock me-1" style="color: var(--color-mocha);"></i> Kebijakan Retensi Berkas:</strong>
                        Sesuai standar operasional dan perlindungan privasi data, berkas bukti transfer Anda disimpan dengan masa simpan maksimal 30 hari kalender setelah transaksi selesai.
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <a href="{{ route('customer.orders.show', $order->id) }}" class="btn btn-outline-secondary py-2 px-3">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Pesanan
                        </a>
                        <x-button type="submit" variant="mocha" class="py-2 px-4 shadow-sm" icon="bi bi-cloud-arrow-up">
                            Kirim Bukti Pembayaran
                        </x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copyRekening() {
        navigator.clipboard.writeText('8720192833').then(() => {
            alert('Nomor rekening BCA (8720192833) berhasil disalin ke clipboard!');
        });
    }

    function previewImage(input) {
        const container = document.getElementById('imagePreviewContainer');
        const preview = document.getElementById('imagePreview');
        if (input.files && input.files[0]) {
            const file = input.files[0];
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    container.classList.remove('d-none');
                }
                reader.readAsDataURL(file);
            } else {
                container.classList.add('d-none');
            }
        }
    }
</script>
@endpush
