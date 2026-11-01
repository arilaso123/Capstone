@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
<!-- Hero Section -->
<section class="py-5" style="background: linear-gradient(135deg, #F7F4EF 0%, #EFE8DE 100%); border-bottom: 1px solid var(--color-soft-gray);">
    <div class="container py-lg-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <img src="{{ asset('images/logo-emblem.png') }}" alt="Uyuy Jastip Emblem" style="height: 32px; width: auto; object-fit: contain;">
                    <span class="badge px-3 py-2 text-uppercase fw-semibold" style="background-color: var(--color-champagne); color: var(--color-charcoal); letter-spacing: 1px; font-size: 0.75rem;">
                        Official Jastip &bull; In Parfume Bandung
                    </span>
                </div>
                <h1 class="display-4 fw-bold mb-3" style="color: var(--color-charcoal); line-height: 1.2;">
                    Aroma Mewah Favoritmu, Hadir Lebih Dekat & Terpercaya.
                </h1>
                <p class="lead text-secondary mb-4" style="font-size: 1.15rem;">
                    Uyuy Jastip melayani pemesanan pre-order parfum pilihan racikan <em>In Parfume</em> dengan pilihan varian lengkap, kualitas terjamin, dan proses pemesanan yang mudah serta transparan.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('catalog') }}" class="btn btn-mocha px-4 py-2 d-inline-flex align-items-center gap-2">
                        <i class="bi bi-bag-check"></i> Jelajahi Koleksi Parfum
                    </a>
                    <a href="{{ route('po.info') }}" class="btn btn-outline-mocha px-4 py-2 d-inline-flex align-items-center gap-2">
                        <i class="bi bi-info-circle"></i> Info Jadwal PO
                    </a>
                </div>

                <!-- Fast Facts -->
                <div class="row g-3 mt-4 pt-3 border-top border-secondary border-opacity-25">
                    <div class="col-4">
                        <div class="fw-bold fs-5 text-dark">1 Minggu</div>
                        <div class="small text-muted">Durasi Tiap Siklus PO</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold fs-5 text-dark">200+</div>
                        <div class="small text-muted">Pelanggan Tiap Batch</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold fs-5 text-dark">BCA Merchant</div>
                        <div class="small text-muted">Metode Pembayaran</div>
                    </div>
                </div>
            </div>

            <!-- Hero Banner Image / Card -->
            <div class="col-lg-5">
                <div class="card card-uyuy p-4 text-center position-relative shadow">
                    <!-- PO Status Card -->
                    <div class="p-3 rounded-3 mb-4" style="background: var(--color-warm-ivory); border: 1px solid var(--color-champagne);">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted fw-semibold text-uppercase">Status Periode Pre-Order</span>
                            @if($activePo && $activePo->isOpen())
                                <span class="badge-uyuy badge-po-open"><i class="bi bi-unlock-fill"></i> PO DIBUKA</span>
                            @else
                                <span class="badge bg-secondary"><i class="bi bi-lock-fill"></i> PO DITUTUP</span>
                            @endif
                        </div>
                        <h5 class="fw-bold mb-1">{{ $activePo->name ?? 'Belum Ada PO Terbuka' }}</h5>
                        <p class="small text-muted mb-3">
                            <i class="bi bi-calendar-event me-1"></i> Periode: 
                            @if($activePo)
                                <strong>{{ $activePo->start_date->format('d M Y') }}</strong> s.d. 
                                <strong>{{ $activePo->end_date->format('d M Y') }}</strong>
                            @else
                                <em>Menunggu pembukaan batch berikutnya</em>
                            @endif
                        </p>
                        @if($activePo && $activePo->isOpen())
                            <a href="{{ route('catalog') }}" class="btn btn-mocha btn-sm w-100">
                                Pesan Sekarang Sebelum Ditutup
                            </a>
                        @else
                            <a href="{{ route('catalog') }}" class="btn btn-outline-secondary btn-sm w-100">
                                Lihat Koleksi Parfum
                            </a>
                        @endif
                    </div>

                    <div class="p-3 bg-light rounded-3 text-start small border">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-shield-check text-success fs-5"></i>
                            <span class="fw-bold">Pemeriksaan Manual & Transparan</span>
                        </div>
                        <p class="text-muted mb-0" style="font-size: 0.8rem;">
                            Seluruh bukti transfer diverifikasi langsung oleh Admin resmi Uyuy Jastip. Status pesanan dapat dipantau real-time oleh pelanggan.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Active PO Alert Bar -->
@if($activePo && $activePo->isOpen())
    <section class="py-3" style="background-color: var(--color-champagne); border-bottom: 1px solid #C9B49B;">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-megaphone-fill text-dark"></i>
                <span class="fw-semibold text-dark">
                    Periode Pre-Order <strong>{{ $activePo->name }}</strong> saat ini sedang <strong>DIBUKA</strong> s.d. {{ $activePo->end_date->format('d M Y') }}.
                </span>
            </div>
            <a href="{{ route('catalog') }}" class="btn btn-dark btn-sm px-3 rounded-pill">
                Lihat Produk PO &rarr;
            </a>
        </div>
    </section>
@endif

<!-- Featured Products Section -->
<section class="py-5">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
            <div>
                <span class="text-uppercase small text-muted fw-bold">Pilihan Terbaik</span>
                <h2 class="display-6 fw-bold mb-0">Parfum Terpopuler</h2>
            </div>
            <a href="{{ route('catalog') }}" class="btn btn-outline-mocha">
                Lihat Seluruh Katalog <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            @forelse($featuredProducts as $p)
                <div class="col-sm-6 col-lg-3">
                    <div class="card card-uyuy card-uyuy-interactive h-100 d-flex flex-column">
                        <div class="product-card-img-wrapper" style="height: 220px;">
                            @if($p->image_path)
                                @php
                                    $img = str_starts_with($p->image_path, 'http')
                                        ? $p->image_path
                                        : asset('storage/' . $p->image_path);
                                @endphp
                                <img src="{{ $img }}" alt="{{ $p->name }}" class="product-card-img" loading="lazy">
                            @else
                                <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light">
                                    <i class="bi bi-droplet fs-1 text-muted"></i>
                                </div>
                            @endif
                            <span class="badge bg-dark bg-opacity-75 position-absolute top-0 end-0 m-2 small">
                                {{ $p->size_ml }} ml
                            </span>
                        </div>
                        <div class="card-body d-flex flex-column p-3">
                            <div class="small text-muted mb-1">{{ $p->brand ?? 'In Parfume' }} &bull; {{ $p->category ?? 'Parfum' }}</div>
                            <h6 class="fw-bold mb-2">{{ $p->name }}</h6>
                            <p class="small text-secondary mb-3 flex-grow-1" style="font-size: 0.83rem;">
                                {{ Str::limit($p->description, 70) }}
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                                <span class="fw-bold text-dark fs-6">
                                    Rp {{ number_format($p->price, 0, ',', '.') }}
                                </span>
                                <a href="{{ route('product.detail', $p->id) }}" class="btn btn-sm btn-mocha">
                                    Detail <i class="bi bi-chevron-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 py-4 text-center text-muted">
                    Belum ada produk unggulan di katalog.
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- How to Order Section -->
<section class="py-5" style="background-color: #FFFFFF; border-top: 1px solid var(--color-soft-gray); border-bottom: 1px solid var(--color-soft-gray);">
    <div class="container">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="text-uppercase small text-muted fw-bold">Alur Pemesanan</span>
            <h2 class="display-6 fw-bold">Bagaimana Cara Kerja Pre-Order di Uyuy Jastip?</h2>
            <p class="text-muted">Proses yang terstruktur memastikan setiap pesanan parfum tercatat aman dan transparan.</p>
        </div>

        <div class="row g-4 text-center">
            <div class="col-md">
                <div class="p-3">
                    <div class="stat-icon mx-auto mb-3" style="width: 56px; height: 56px; font-size: 1.5rem;">
                        <i class="bi bi-grid"></i>
                    </div>
                    <h6 class="fw-bold">1. Pilih Parfum</h6>
                    <p class="small text-muted">Buka katalog dan pilih aroma serta ukuran botol parfum yang kamu inginkan selama PO dibuka.</p>
                </div>
            </div>
            <div class="col-md">
                <div class="p-3">
                    <div class="stat-icon mx-auto mb-3" style="width: 56px; height: 56px; font-size: 1.5rem;">
                        <i class="bi bi-cart-check"></i>
                    </div>
                    <h6 class="fw-bold">2. Checkout Pesanan</h6>
                    <p class="small text-muted">Lengkapi data pengiriman dan buat pesanan resmi melalui sistem website.</p>
                </div>
            </div>
            <div class="col-md">
                <div class="p-3">
                    <div class="stat-icon mx-auto mb-3" style="width: 56px; height: 56px; font-size: 1.5rem;">
                        <i class="bi bi-credit-card"></i>
                    </div>
                    <h6 class="fw-bold">3. Bayar BCA Merchant</h6>
                    <p class="small text-muted">Lakukan transfer sesuai total tagihan ke rekening / merchant BCA resmi Uyuy Jastip.</p>
                </div>
            </div>
            <div class="col-md">
                <div class="p-3">
                    <div class="stat-icon mx-auto mb-3" style="width: 56px; height: 56px; font-size: 1.5rem;">
                        <i class="bi bi-upload"></i>
                    </div>
                    <h6 class="fw-bold">4. Upload Bukti</h6>
                    <p class="small text-muted">Unggah foto bukti transfer pada menu detail pesanan untuk diverifikasi manual oleh admin.</p>
                </div>
            </div>
            <div class="col-md">
                <div class="p-3">
                    <div class="stat-icon mx-auto mb-3" style="width: 56px; height: 56px; font-size: 1.5rem;">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h6 class="fw-bold">5. Pantau Status</h6>
                    <p class="small text-muted">Pantau progres pesanan secara real-time hingga parfum tiba di alamat tujuanmu.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Brand Story & Assurance -->
<section class="py-5" style="background-color: var(--color-warm-ivory);">
    <div class="container">
        <div class="card card-uyuy p-4 p-md-5 border-0" style="background: linear-gradient(135deg, #302B27 0%, #201C19 100%); color: #F7F4EF;">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <span class="badge bg-warning text-dark px-3 py-2 text-uppercase mb-2">Original & Pilihan</span>
                    <h3 class="display-6 fw-bold text-white mb-3">Mengapa Memilih Jastip di Uyuy Jastip?</h3>
                    <p class="text-white-50 mb-0">
                        Kami memesan langsung parfum racikan terbaik dari <strong>In Parfume</strong>. Setiap siklus pre-order melayani ratusan pelanggan setia dengan pencatatan digital yang teratur, memastikan botol parfum pesanan Anda tidak tertukar, tidak terlewat, dan dikemas dengan ekstra proteksi.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('catalog') }}" class="btn btn-champagne btn-lg px-4">
                        Mulai Belanja Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
