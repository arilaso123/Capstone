@extends('layouts.app')

@section('title', 'Pembayaran #' . $order->order_number)

@section('content')
<div class="container py-5 customer-payment-page">
    <nav aria-label="Breadcrumb" class="customer-checkout-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customer.orders.index') }}">Pesanan Saya</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customer.orders.show', $order->id) }}">{{ $order->order_number }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Pembayaran</li>
        </ol>
    </nav>

    <x-customer-portal-nav />

    <header class="customer-payment-header">
        <div>
            <p class="home-eyebrow">Pembayaran pesanan</p>
            <h1>Instruksi transfer</h1>
            <p>Gunakan detail transaksi ini, lalu kirim bukti pembayaran untuk diperiksa Admin.</p>
        </div>
        <x-status-badge :status="$order->status" />
    </header>

    <div class="customer-payment-layout">
        <div class="customer-payment-instructions">
            <section class="customer-payment-panel" aria-labelledby="customer-bank-details-heading">
                <header><p class="home-eyebrow">Transfer bank</p><h2 id="customer-bank-details-heading">Rekening pembayaran</h2></header>
                <p class="customer-payment-intro">Transfer sesuai jumlah yang tertera dan pastikan nomor pesanan dicantumkan pada keterangan transaksi.</p>

                @if(config('jastip.bca_account'))
                    <div class="customer-payment-account">
                        <div>
                            <span>Bank</span>
                            <strong>BCA</strong>
                        </div>
                        <div>
                            <span>Nomor rekening</span>
                            <div class="customer-payment-account-number">
                                <strong id="customerBankAccount">{{ config('jastip.bca_account') }}</strong>
                                <button type="button" id="copyBankAccount" data-copy-account="{{ preg_replace('/[^0-9]/', '', (string) config('jastip.bca_account')) }}" aria-label="Salin nomor rekening BCA"><i class="bi bi-copy me-1" id="copyIcon" aria-hidden="true"></i><span id="copyButtonText">Salin</span></button>
                            </div>
                        </div>
                        <div>
                            <span>Atas nama</span>
                            <strong>{{ config('jastip.bca_name') ?: 'Uyuy Jastip' }}</strong>
                        </div>
                    </div>
                    <p class="customer-payment-copy-status" id="copyBankAccountStatus" role="status" aria-live="polite"></p>
                @else
                    <div class="customer-payment-notice" role="status"><strong>Detail rekening belum tersedia.</strong> Hubungi Admin untuk memperoleh instruksi pembayaran sebelum melakukan transfer.</div>
                @endif

                <div class="customer-payment-amount">
                    <div><span>Nomor pesanan</span><strong>{{ $order->order_number }}</strong></div>
                    <div><span>Jumlah tagihan</span><strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></div>
                </div>

                @if($order->payment_deadline_at)
                    <div class="customer-payment-deadline-badge" role="region" aria-label="Informasi batas pembayaran">
                        <div class="customer-payment-deadline-icon" aria-hidden="true">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="customer-payment-deadline-content">
                            <span class="customer-payment-deadline-label">Batas pembayaran</span>
                            <strong class="customer-payment-deadline-time">{{ $order->payment_deadline_at->timezone('Asia/Jakarta')->format('d F Y, H:i') }} WIB</strong>
                            <p class="customer-payment-deadline-desc">Selesaikan transfer dan unggah bukti sebelum batas waktu berakhir. Pesanan yang belum dibayar akan diproses sesuai batas waktu {{ config('jastip.payment_deadline_hours', 24) }} jam sejak dibuat.</p>
                        </div>
                    </div>
                @endif
                <p class="customer-payment-note">Jika berlaku, ongkos kirim dikonfirmasi terpisah oleh Admin. Pastikan jumlah yang ditransfer sesuai tagihan pesanan dan instruksi pembayaran.</p>
            </section>

            <section class="customer-payment-panel customer-payment-upload-help" aria-labelledby="customer-payment-steps-heading">
                <header><p class="home-eyebrow">Langkah berikutnya</p><h2 id="customer-payment-steps-heading">Setelah transfer</h2></header>
                <ol>
                    <li>Simpan bukti transfer yang menampilkan nominal dan waktu transaksi.</li>
                    <li>Isi nominal dan unggah berkas pada formulir di samping.</li>
                    <li>Admin akan memeriksa bukti secara manual dan statusnya tampil di detail pesanan.</li>
                </ol>
                <p>Bukti pembayaran disimpan sesuai kebijakan retensi yang ditetapkan Owner dan hanya dapat diakses pihak berwenang.</p>
            </section>
        </div>

        <section class="customer-payment-panel customer-payment-form-panel" aria-labelledby="customer-payment-upload-heading">
            <header>
                <p class="home-eyebrow">Kirim bukti</p>
                <h2 id="customer-payment-upload-heading">Unggah pembayaran</h2>
                <p>Format JPG, PNG, atau PDF. Maksimal 2 MB.</p>
            </header>

            @if($order->payment?->proof_file_path)
                @if(preg_match('/^https?:\/\//i', $order->payment->proof_file_path))
                    <p class="customer-payment-notice">Bukti lama berasal dari sumber eksternal. Unggah bukti baru agar Admin dapat memeriksanya di sistem.</p>
                @else
                    <p class="customer-payment-notice">Bukti sebelumnya sudah tersimpan. Anda dapat mengunggah bukti baru jika diperlukan.</p>
                @endif
            @endif

            <form action="{{ route('customer.orders.payment.post', $order->id) }}" method="POST" enctype="multipart/form-data" class="customer-payment-form" id="customerPaymentForm">
                @csrf
                <div class="customer-checkout-field">
                    <label for="payment_method">Metode pembayaran</label>
                    <input type="text" id="payment_method" name="payment_method" value="BCA Merchant (Transfer Non-Tunai)" readonly>
                </div>

                <div class="customer-checkout-field">
                    <label for="amount_display">Jumlah yang ditransfer <span aria-hidden="true">*</span></label>
                    <div class="customer-payment-amount-box">
                        <span class="customer-payment-currency-tag" aria-hidden="true">IDR</span>
                        <input type="text" id="amount_display" class="customer-payment-amount-input" value="Rp {{ number_format($order->total_amount, 0, ',', '.') }}" readonly aria-label="Jumlah yang ditransfer dalam Rupiah">
                    </div>
                    <input type="hidden" id="amount" name="amount" value="{{ number_format((float) $order->total_amount, 2, '.', '') }}">
                    <small class="customer-payment-amount-hint">Nominal transfer harus tepat sesuai tagihan pesanan.</small>
                    @error('amount')<p class="customer-checkout-error" id="amount-error">{{ $message }}</p>@enderror
                </div>

                <div class="customer-checkout-field">
                    <label for="proof_file" id="proofFileLabel">Berkas bukti pembayaran <span aria-hidden="true">*</span></label>
                    <div class="customer-payment-dropzone" id="paymentDropzone" role="button" tabindex="0" aria-labelledby="proofFileLabel" aria-describedby="proof-file-help">
                        <div class="customer-payment-dropzone-icon" aria-hidden="true">
                            <i class="bi bi-cloud-arrow-up"></i>
                        </div>
                        <div class="customer-payment-dropzone-text">
                            <strong>Tarik & letakkan berkas bukti di sini</strong>
                            <span>atau <span class="customer-payment-dropzone-browse">pilih berkas dari perangkat</span></span>
                        </div>
                        <span class="customer-payment-dropzone-meta" id="proof-file-help">Format JPG, PNG, atau PDF. Maksimal 2 MB.</span>
                        <input type="file" id="proof_file" name="proof_file" accept="image/jpeg,image/png,image/jpg,application/pdf" class="customer-payment-file-input" required aria-invalid="{{ $errors->has('proof_file') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('proof_file') ? 'proof-file-help proof-file-error' : 'proof-file-help' }}">
                    </div>
                    @error('proof_file')<p class="customer-checkout-error" id="proof-file-error">{{ $message }}</p>@enderror
                </div>

                <div class="customer-payment-preview-card" id="customerPaymentPreview" hidden aria-live="polite">
                    <div class="customer-payment-preview-header">
                        <span class="customer-payment-preview-title">
                            <i class="bi bi-check-circle-fill text-success me-2" aria-hidden="true"></i> Berkas siap diunggah
                        </span>
                        <button type="button" id="removePaymentPreview" class="customer-payment-preview-remove" aria-label="Ganti berkas">
                            <i class="bi bi-arrow-repeat me-1" aria-hidden="true"></i> Ganti
                        </button>
                    </div>
                    <div class="customer-payment-preview-body">
                        <div class="customer-payment-preview-thumb-box">
                            <img id="customerPaymentPreviewImage" src="" alt="Pratinjau bukti pembayaran" class="customer-payment-preview-img" hidden>
                            <div id="customerPaymentPreviewPdf" class="customer-payment-preview-pdf" hidden aria-hidden="true">
                                <i class="bi bi-file-earmark-pdf-fill"></i>
                            </div>
                        </div>
                        <div class="customer-payment-preview-info">
                            <p id="customerPaymentPreviewName" class="customer-payment-preview-filename"></p>
                            <span id="customerPaymentPreviewSize" class="customer-payment-preview-filesize"></span>
                        </div>
                    </div>
                </div>

                <p class="customer-payment-retention"><strong>Privasi berkas.</strong> Akses bukti transfer dibatasi kepada pihak yang berwenang. Masa simpan mengikuti kebijakan operasional Owner.</p>

                <div class="customer-payment-form-actions">
                    <a href="{{ route('customer.orders.show', $order->id) }}">Kembali ke pesanan</a>
                    <button type="submit">Kirim bukti pembayaran <span aria-hidden="true">→</span></button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
