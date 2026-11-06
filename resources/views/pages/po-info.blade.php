@extends('layouts.app')

@section('title', 'Informasi Periode Pre-Order (PO)')

@section('content')
<!-- Header Banner -->
<div class="py-4 border-bottom" style="background: radial-gradient(circle at 10% 20%, #FDFBF7 0%, #F4ECE3 90%); border-color: rgba(216, 197, 174, 0.4) !important;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none" style="color: var(--color-mocha);">Beranda</a></li>
                <li class="breadcrumb-item active text-secondary" aria-current="page">Informasi PO</li>
            </ol>
        </nav>
        <h1 class="h2 fw-bold font-heading mb-1" style="color: var(--color-charcoal); letter-spacing: -0.02em;">Informasi & Ketentuan Pre-Order</h1>
        <p class="text-secondary small mb-0">Panduan lengkap mengenai jadwal siklus PO, mekanisme belanja jastip In Parfume, dan aturan transaksi.</p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-5">
        <!-- Main Column: Active Schedule & FAQs -->
        <div class="col-lg-8">
            <!-- Active Period Schedule Card -->
            <x-card class="p-4 p-md-5 mb-4 border-0 shadow">
                @if($activePo && $activePo->isOpen())
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                        <div>
                            <span class="small fw-bold text-uppercase" style="color: var(--color-mocha); letter-spacing: 0.8px; font-size: 0.72rem;">Siklus Pre-Order Terkini</span>
                            <h4 class="fw-bold font-heading mb-0 text-dark">Jadwal Pre-Order Aktif</h4>
                        </div>
                        <span class="badge-uyuy badge-po-open py-1 px-3 shadow-sm">
                            <i class="bi bi-broadcast"></i> PO DIBUKA
                        </span>
                    </div>

                    <!-- Details Bento Block -->
                    <div class="p-4 rounded-4 mb-4" style="background: #FAF8F5; border: 1.5px solid rgba(216, 197, 174, 0.45);">
                        <div class="row g-4">
                            <div class="col-sm-6">
                                <span class="small text-muted d-block" style="font-size: 0.78rem;">Nama Batch:</span>
                                <span class="fw-bold font-heading fs-5 text-dark">{{ $activePo->name }}</span>
                            </div>
                            <div class="col-sm-6">
                                <span class="small text-muted d-block" style="font-size: 0.78rem;">Durasi Pelayanan:</span>
                                <span class="fw-bold fs-6 text-dark">Siklus 1 Minggu per Batch</span>
                            </div>
                            <div class="col-sm-6">
                                <span class="small text-muted d-block" style="font-size: 0.78rem;">Tanggal Pembukaan:</span>
                                <span class="fw-semibold text-dark">{{ $activePo->start_date->format('d F Y') }}</span>
                            </div>
                            <div class="col-sm-6">
                                <span class="small text-muted d-block" style="font-size: 0.78rem;">Batas Akhir Pemesanan:</span>
                                <span class="fw-bold text-danger">{{ $activePo->end_date->format('d F Y') }} (Pukul 23.59 WIB)</span>
                            </div>
                        </div>
                    </div>

                    @if($activePo->notes)
                        <div class="p-3 rounded-3 small text-secondary mb-4" style="background: rgba(244, 236, 227, 0.5); border-left: 3px solid var(--color-mocha);">
                            <strong>Catatan Tambahan:</strong> {{ $activePo->notes }}
                        </div>
                    @endif

                    <h6 class="fw-bold mb-2 font-heading">Ketentuan Layanan Pre-Order:</h6>
                    <ul class="text-secondary small mb-4 ps-3" style="line-height: 1.7;">
                        <li class="mb-1">Jam operasional verifikasi admin berlangsung setiap <strong>Selasa sampai Minggu</strong> pukul <strong>09.00 – 19.00 WIB</strong>.</li>
                        <li class="mb-1">Hari Senin dialokasikan khusus untuk rekapitulasi data pesanan final dan penyerahan daftar belanjaan ke toko vendor <em>In Parfume</em> Bandung.</li>
                        <li class="mb-1">Kapasitas kuota pesanan ditampung hingga 200 pelanggan setia tiap batch PO untuk menjaga akurasi botol pesanan.</li>
                    </ul>

                    <a href="{{ route('catalog') }}" class="btn btn-mocha py-3 px-4 shadow-sm d-inline-flex align-items-center gap-2">
                        <i class="bi bi-bag-plus"></i> Mulai Pesan Parfum di Batch Ini
                    </a>
                @else
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="fw-bold font-heading mb-0 text-dark">Jadwal Pre-Order</h4>
                        <span class="badge rounded-pill bg-secondary px-3 py-1"><i class="bi bi-lock-fill me-1"></i> STATUS: DITUTUP</span>
                    </div>

                    <div class="p-5 rounded-4 mb-4 text-center" style="background: #FAF8F5; border: 1px dashed rgba(216, 197, 174, 0.7);">
                        <i class="bi bi-clock-history fs-1 text-muted d-block mb-3" style="color: var(--color-mocha) !important;"></i>
                        <h5 class="fw-bold font-heading text-dark">Belum Ada Periode Pre-Order yang Dibuka</h5>
                        <p class="small text-secondary mb-0 mx-auto" style="max-width: 480px;">Owner akan segera mengumumkan dan membuka batch pre-order baru. Silakan jelajahi katalog untuk memilih aroma wewangian favorit Anda.</p>
                    </div>

                    <a href="{{ route('catalog') }}" class="btn btn-outline-mocha px-4 py-2">
                        <i class="bi bi-grid me-1"></i> Jelajahi Koleksi Katalog
                    </a>
                @endif
            </x-card>

            <!-- FAQ Section -->
            <x-card class="p-4 p-md-5 border-0 shadow">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <i class="bi bi-question-circle fs-4" style="color: var(--color-mocha);"></i>
                    <h5 class="fw-bold font-heading mb-0">Pertanyaan yang Sering Diajukan (FAQ)</h5>
                </div>

                <div class="accordion accordion-flush" id="faqAccordion">
                    <!-- Q1 -->
                    <div class="accordion-item mb-2 rounded-3 border-0" style="background: #FAF8F5;">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold rounded-3 shadow-none bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Bagaimana cara pembayaran pre-order di Uyuy Jastip?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body small text-secondary pt-0" style="line-height: 1.65;">
                                Pembayaran dilakukan secara non-tunai via transfer rekening <strong>BCA Merchant</strong> resmi Uyuy Jastip yang tertera pada halaman rincian pesanan Anda. Setelah transfer berhasil, Anda wajib mengunggah foto bukti pembayaran pada website untuk diverifikasi admin.
                            </div>
                        </div>
                    </div>

                    <!-- Q2 -->
                    <div class="accordion-item mb-2 rounded-3 border-0" style="background: #FAF8F5;">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold rounded-3 shadow-none bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Siapa yang memverifikasi bukti pembayaran saya?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body small text-secondary pt-0" style="line-height: 1.65;">
                                Berkas transfer diperiksa langsung secara manual oleh Staf Admin resmi Uyuy Jastip pada jam kerja operasional (Selasa - Minggu, 09.00 - 19.00 WIB). Status pesanan Anda akan otomatis bertransisi menjadi <code>Pembayaran Diverifikasi</code> segera setelah bukti divalidasi.
                            </div>
                        </div>
                    </div>

                    <!-- Q3 -->
                    <div class="accordion-item mb-2 rounded-3 border-0" style="background: #FAF8F5;">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold rounded-3 shadow-none bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Apakah pesanan yang sudah dibuat bisa dibatalkan?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body small text-secondary pt-0" style="line-height: 1.65;">
                                Ya, pelanggan dapat mengajukan permohonan pembatalan melalui tombol khusus pada detail pesanan sebelum barang dikirimkan, disertai alasan pembatalan. Permohonan tersebut akan ditinjau dan diputuskan oleh admin.
                            </div>
                        </div>
                    </div>

                    <!-- Q4 -->
                    <div class="accordion-item rounded-3 border-0" style="background: #FAF8F5;">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold rounded-3 shadow-none bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Berapa lama masa penyimpanan berkas bukti transfer saya?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body small text-secondary pt-0" style="line-height: 1.65;">
                                Demi keamanan privasi dan kepatuhan sistem, bukti transfer disimpan dengan masa retensi maksimal 30 hari kalender (1 bulan) setelah pesanan dinyatakan selesai.
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Sidebar Info: Working Hours & Contact -->
        <div class="col-lg-4">
            <x-card class="p-4 mb-4 border-0 shadow">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-clock-history fs-5" style="color: var(--color-mocha);"></i>
                    <h6 class="fw-bold font-heading mb-0">Jam Kerja Admin</h6>
                </div>
                <div class="small">
                    <div class="d-flex justify-content-between py-2 border-bottom" style="border-color: rgba(216, 197, 174, 0.3) !important;">
                        <span class="text-secondary">Selasa – Minggu</span>
                        <strong class="text-dark">09.00 – 19.00 WIB</strong>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom" style="border-color: rgba(216, 197, 174, 0.3) !important;">
                        <span class="text-secondary">Senin</span>
                        <em class="text-muted">Rekapitulasi Vendor</em>
                    </div>
                </div>
                <div class="p-3 rounded-3 small mt-3" style="background: rgba(244, 236, 227, 0.6); font-size: 0.8rem; line-height: 1.5;">
                    <i class="bi bi-info-circle me-1" style="color: var(--color-mocha);"></i> Pembuatan pesanan di website tetap dapat dilakukan 24 jam selama periode PO berstatus <strong>Dibuka</strong>.
                </div>
            </x-card>

            <!-- Official Channels Help Box -->
            <x-card class="p-4 border-0 shadow">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-headset fs-5" style="color: var(--color-mocha);"></i>
                    <h6 class="fw-bold font-heading mb-0">Bantuan & Konsultasi Aroma</h6>
                </div>
                <p class="small text-secondary mb-3">Butuh rekomendasi wewangian yang cocok dengan gaya Anda? Hubungi admin resmi kami:</p>
                <div class="d-grid gap-2">
                    <a href="https://wa.me" target="_blank" class="btn btn-outline-success btn-sm py-2 d-flex align-items-center justify-content-center gap-2 rounded-3">
                        <i class="bi bi-whatsapp"></i> Chat WhatsApp Admin
                    </a>
                    <a href="https://instagram.com" target="_blank" class="btn btn-outline-secondary btn-sm py-2 d-flex align-items-center justify-content-center gap-2 rounded-3">
                        <i class="bi bi-instagram"></i> Direct Message Instagram
                    </a>
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection
