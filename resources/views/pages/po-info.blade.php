@extends('layouts.app')

@section('title', 'Informasi Periode Pre-Order (PO)')

@section('content')
<div class="py-4 border-bottom" style="background-color: var(--color-champagne-light);">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-secondary">Beranda</a></li>
                <li class="breadcrumb-item active" aria-current="page">Informasi PO</li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0">Informasi & Ketentuan Pre-Order Parfum</h1>
        <p class="text-secondary small mb-0">Panduan lengkap jadwal siklus pre-order, pembayaran, dan operasional Uyuy Jastip.</p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-5">
        <!-- Active Period Schedule -->
        <div class="col-lg-8">
            <div class="card card-uyuy p-4 mb-4">
                @if($activePo && $activePo->isOpen())
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 font-heading">Jadwal Periode Pre-Order Aktif</h5>
                        <span class="badge-uyuy badge-po-open"><i class="bi bi-unlock-fill"></i> STATUS: DIBUKA</span>
                    </div>

                    <div class="p-3 rounded-3 mb-4 bg-light border">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <span class="small text-muted d-block">Nama Batch:</span>
                                <span class="fw-bold fs-6">{{ $activePo->name }}</span>
                            </div>
                            <div class="col-sm-6">
                                <span class="small text-muted d-block">Durasi Pelayanan:</span>
                                <span class="fw-bold fs-6">Siklus PO 1 Minggu</span>
                            </div>
                            <div class="col-sm-6">
                                <span class="small text-muted d-block">Tanggal Dibuka:</span>
                                <span class="text-dark fw-semibold">{{ $activePo->start_date->format('d F Y') }}</span>
                            </div>
                            <div class="col-sm-6">
                                <span class="small text-muted d-block">Tanggal Ditutup:</span>
                                <span class="text-danger fw-semibold">{{ $activePo->end_date->format('d F Y') }} (Pukul 23.59 WIB)</span>
                            </div>
                        </div>
                    </div>

                    @if($activePo->notes)
                        <div class="alert alert-light border small text-muted mb-4">
                            <strong>Catatan Tambahan:</strong> {{ $activePo->notes }}
                        </div>
                    @endif

                    <h6 class="fw-bold mb-2">Catatan Operasional Mitra:</h6>
                    <ul class="text-secondary small mb-4">
                        <li class="mb-1">Kegiatan operasional admin berlangsung setiap <strong>Selasa sampai Minggu</strong> pukul <strong>09.00 – 19.00 WIB</strong>.</li>
                        <li class="mb-1">Pada hari Senin, admin fokus pada rekapitulasi data pesanan dan penyerahan daftar belanjaan ke vendor <em>In Parfume</em>.</li>
                        <li class="mb-1">Jumlah pelanggan dilayani berkisar hingga 200 pemesan per periode PO.</li>
                    </ul>

                    <a href="{{ route('catalog') }}" class="btn btn-mocha">
                        <i class="bi bi-bag-plus me-1"></i> Mulai Pesan Parfum di Batch Ini
                    </a>
                @else
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 font-heading">Jadwal Periode Pre-Order</h5>
                        <span class="badge bg-secondary"><i class="bi bi-lock-fill"></i> STATUS: DITUTUP</span>
                    </div>

                    <div class="p-4 rounded-3 mb-4 bg-light text-center">
                        <i class="bi bi-lock fs-1 text-muted d-block mb-2"></i>
                        <h5 class="fw-bold">Saat ini belum ada periode Pre-Order yang dibuka</h5>
                        <p class="small text-muted mb-0">Owner akan membuka periode pre-order baru segera. Pantau terus kanal komunikasi kami atau cek halaman ini secara berkala.</p>
                    </div>

                    <a href="{{ route('catalog') }}" class="btn btn-outline-mocha">
                        <i class="bi bi-grid me-1"></i> Lihat Katalog Produk
                    </a>
                @endif
            </div>

            <!-- Past PO Periods -->
            @if(isset($pastPoPeriods) && $pastPoPeriods->isNotEmpty())
                <div class="card card-uyuy p-4 mb-4">
                    <h5 class="fw-bold mb-3 font-heading"><i class="bi bi-clock-history me-2 text-mocha"></i> Riwayat Periode Pre-Order Sebelumnya</h5>
                    <div class="list-group list-group-flush small">
                        @foreach($pastPoPeriods as $past)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <div>
                                    <strong class="text-dark">{{ $past->name }}</strong>
                                    <div class="text-muted small">
                                        {{ $past->start_date->format('d M') }} — {{ $past->end_date->format('d M Y') }}
                                    </div>
                                </div>
                                <span class="badge bg-secondary">Ditutup</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Pre-Order FAQ & Guidelines -->
            <div class="card card-uyuy p-4">
                <h5 class="fw-bold mb-3 font-heading">Pertanyaan Umum (FAQ) & Ketentuan</h5>

                <div class="accordion accordion-flush" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Bagaimana cara pembayaran pre-order di Uyuy Jastip?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body small text-secondary">
                                Pembayaran dilakukan secara non-tunai melalui rekening / merchant <strong>BCA Merchant</strong> yang tertera pada detail pesanan Anda. Setelah transfer berhasil, Anda wajib mengunggah bukti pembayaran berupa foto/tangkapan layar yang jelas pada website.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Siapa yang memverifikasi pembayaran saya?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body small text-secondary">
                                Bukti transfer diperiksa secara manual oleh Staf Admin Uyuy Jastip. Verifikasi dilakukan pada jam operasional kerja (Selasa - Minggu 09.00 - 19.00 WIB). Status pesanan Anda akan berubah menjadi <code>Pembayaran Diverifikasi</code> segera setelah disetujui.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Apakah pesanan bisa dibatalkan?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body small text-secondary">
                                Customer dapat mengajukan permohonan pembatalan melalui halaman detail pesanan. Permohonan pembatalan tersebut harus melalui pemeriksaan dan persetujuan Admin terlebih dahulu sebelum status pesanan resmi dibatalkan.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Berapa lama masa penyimpanan bukti pembayaran saya?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body small text-secondary">
                                Sesuai batasan operasional mitra, berkas bukti pembayaran disimpan dengan masa retensi maksimal 1 (satu) bulan demi keamanan dan perlindungan privasi data transaksi Anda.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side Info -->
        <div class="col-lg-4">
            <div class="card card-uyuy p-4 mb-4">
                <h6 class="fw-bold mb-3 font-heading"><i class="bi bi-clock-history me-1 text-mocha"></i> Jam Operasional Admin</h6>
                <div class="small">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span>Selasa - Minggu</span>
                        <strong>09.00 - 19.00 WIB</strong>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom text-muted">
                        <span>Senin</span>
                        <em>Rekapitulasi Khusus</em>
                    </div>
                </div>
                <div class="alert alert-light border small mt-3 mb-0">
                    <i class="bi bi-chat-dots me-1 text-primary"></i> Pesanan tetap dapat dibuat kapan saja di website selama periode PO masih berstatus <strong>Dibuka</strong>.
                </div>
            </div>

            <!-- Social Help -->
            <div class="card card-uyuy p-4">
                <h6 class="fw-bold mb-2 font-heading">Butuh Informasi Tambahan?</h6>
                <p class="small text-secondary mb-3">Hubungi saluran komunikasi resmi kami untuk rekomendasi aroma atau kendala lainnya:</p>
                <div class="d-grid gap-2">
                    <a href="https://wa.me" target="_blank" class="btn btn-outline-success btn-sm d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-whatsapp"></i> Chat WhatsApp Admin
                    </a>
                    <a href="https://instagram.com" target="_blank" class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-instagram"></i> Direct Message Instagram
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
