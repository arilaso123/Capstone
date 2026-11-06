@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
<!-- Hero Section: Warm Editorial Quiet Luxury (Mockup Replication) -->
<section class="hero-editorial-section">
    <div class="container py-lg-4">
        <div class="row align-items-center">
            <!-- Left Column: Editorial Headline, Subtitle & Single Pill CTA -->
            <div class="col-lg-6 mb-5 mb-lg-0 pe-lg-4">
                <h1 class="hero-headline">
                    Sentuhan Aroma Mewah,<br>
                    Hadir Lebih Dekat.
                </h1>

                <p class="hero-subheadline">
                    <em>Pre-order</em> eksklusif parfum legendaris In Parfume Bandung. Aman dan transparan.
                </p>

                <div class="pt-1">
                    <a href="{{ route('catalog') }}" class="btn-hero-pill">
                        <span>Jelajahi Koleksi</span>
                        <span class="fs-5">&rarr;</span>
                    </a>
                </div>
            </div>

            <!-- Right Column: Elevated Floating PO Card overlapping Perfume Lifestyle Photo -->
            <div class="col-lg-6 position-relative">
                <div class="hero-media-wrapper">
                    <!-- Perfume Photography Frame -->
                    <div class="hero-photo-frame">
                        <img src="{{ asset('images/hero-perfume.jpg') }}" alt="Parfum Mewah In Parfume Bandung" class="hero-photo-img" loading="eager">
                    </div>

                    <!-- Elevated Floating PO Card -->
                    <div class="hero-floating-card">
                        <!-- Top Row: PO Status Badge -->
                        <div class="d-flex justify-content-end mb-3">
                            @if($activePo && $activePo->isOpen())
                                <span class="po-status-badge open">
                                    <span style="font-size: 0.9rem; line-height: 1;">●</span> PO DIBUKA
                                </span>
                            @elseif($activePo)
                                <span class="po-status-badge closed">
                                    <span style="font-size: 0.9rem; line-height: 1;">●</span> PO DITUTUP
                                </span>
                            @else
                                <span class="po-status-badge open">
                                    <span style="font-size: 0.9rem; line-height: 1;">●</span> PO DIBUKA
                                </span>
                            @endif
                        </div>

                        <!-- PO Batch Title -->
                        <h3 class="po-card-title">
                            {{ $activePo->name ?? 'PO Batch 10 Pekan Reguler' }}
                        </h3>

                        <!-- PO Schedule Row -->
                        <div class="po-card-schedule">
                            <i class="bi bi-clock"></i>
                            @if($activePo)
                                <span>{{ $activePo->start_date->format('d M') }} &mdash; {{ $activePo->end_date->format('d M Y') }}</span>
                            @else
                                <span>28 Okt &mdash; 04 Nov 2026</span>
                            @endif
                        </div>

                        <!-- CTA Action Button -->
                        @if($activePo && $activePo->isOpen())
                            <a href="{{ route('catalog') }}" class="btn-po-order">
                                <span>Pesan Sekarang</span>
                                <span>&rarr;</span>
                            </a>
                        @else
                            <a href="{{ route('catalog') }}" class="btn-po-order">
                                <span>Pesan Sekarang</span>
                                <span>&rarr;</span>
                            </a>
                        @endif

                        <!-- Divider -->
                        <div class="po-card-divider"></div>

                        <!-- Bottom Trust Note -->
                        <div class="po-card-trust">
                            <i class="bi bi-check2 text-dark fs-5"></i>
                            <span>Verifikasi manual & transparan.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Active PO Announcement Banner (If Open) -->
@if($activePo && $activePo->isOpen())
    <section class="py-3" style="background-color: var(--color-champagne-light); border-bottom: 1px solid rgba(216, 197, 174, 0.4);">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill bg-white text-dark shadow-sm px-3 py-1">
                    <i class="bi bi-bell-fill me-1" style="color: var(--color-mocha);"></i> Pengumuman
                </span>
                <span class="small fw-semibold text-dark">
                    Pre-Order <strong>{{ $activePo->name }}</strong> aktif hingga <strong>{{ $activePo->end_date->format('d M Y') }}</strong>. Masukkan produk favoritmu ke keranjang sekarang!
                </span>
            </div>
            <a href="{{ route('catalog') }}" class="btn btn-sm btn-outline-mocha px-3 py-1 rounded-pill">
                Belanja PO &rarr;
            </a>
        </div>
    </section>
@endif

<!-- Featured Best-Seller Perfumes Section -->
<section class="py-5 my-2">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4 pb-2">
            <div>
                <span class="text-uppercase fw-semibold small" style="color: var(--color-mocha); letter-spacing: 1px; font-size: 0.75rem;">
                    Koleksi Kurasi Pilihan
                </span>
                <h2 class="display-6 fw-bold font-heading mb-0" style="letter-spacing: -0.02em;">
                    Parfum Terpopuler
                </h2>
            </div>
            <a href="{{ route('catalog') }}" class="btn btn-outline-mocha px-4 py-2 mt-3 mt-md-0 d-inline-flex align-items-center gap-2">
                <span>Lihat Seluruh Katalog</span>
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="row g-4">
            @forelse($featuredProducts as $p)
                <div class="col-sm-6 col-lg-3">
                    <x-card interactive class="h-100 d-flex flex-column border-0 shadow">
                        <!-- Product Image Box -->
                        <div class="product-card-img-wrapper position-relative" style="height: 230px; background-color: #FAF8F5; border-top-left-radius: 20px; border-top-right-radius: 20px;">
                            @if($p->image_path)
                                @php
                                    $img = str_starts_with($p->image_path, 'http')
                                        ? $p->image_path
                                        : asset('storage/' . $p->image_path);
                                @endphp
                                <img src="{{ $img }}" alt="{{ $p->name }}" class="product-card-img" loading="lazy">
                            @else
                                <div class="w-100 h-100 d-flex align-items-center justify-content-center" style="background-color: var(--color-champagne-light); color: var(--color-mocha);">
                                    <i class="bi bi-droplet fs-1"></i>
                                </div>
                            @endif

                            <span class="badge rounded-pill position-absolute top-0 end-0 m-3 px-3 py-1 shadow-sm" style="background: rgba(255, 255, 255, 0.92); color: var(--color-charcoal); font-size: 0.75rem; border: 1px solid rgba(216, 197, 174, 0.6);">
                                {{ $p->size_ml }} ml
                            </span>
                        </div>

                        <!-- Product Information -->
                        <div class="card-body d-flex flex-column p-4">
                            <div class="d-flex align-items-center gap-1 small text-muted mb-1" style="font-size: 0.78rem;">
                                <span class="fw-semibold text-uppercase" style="color: var(--color-mocha);">{{ $p->brand ?? 'In Parfume' }}</span>
                                <span>&bull;</span>
                                <span>{{ $p->category ?? 'Parfum' }}</span>
                            </div>

                            <h5 class="fw-bold font-heading mb-2">
                                <a href="{{ route('product.detail', $p->id) }}" class="text-decoration-none text-dark hover-mocha">
                                    {{ $p->name }}
                                </a>
                            </h5>

                            <p class="small text-secondary mb-3 flex-grow-1" style="line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ $p->description }}
                            </p>

                            <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top" style="border-color: rgba(216, 197, 174, 0.3) !important;">
                                <div>
                                    <span class="small text-muted d-block" style="font-size: 0.72rem;">Mulai dari</span>
                                    <span class="fw-bold font-heading text-dark fs-5">
                                        Rp {{ number_format($p->price, 0, ',', '.') }}
                                    </span>
                                </div>
                                <a href="{{ route('product.detail', $p->id) }}" class="btn btn-sm btn-mocha px-3 py-2 d-inline-flex align-items-center gap-1">
                                    <span>Pilih</span>
                                    <i class="bi bi-chevron-right" style="font-size: 0.75rem;"></i>
                                </a>
                            </div>
                        </div>
                    </x-card>
                </div>
            @empty
                <div class="col-12 py-5 text-center text-muted">
                    Belum ada produk unggulan di katalog saat ini.
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- How It Works Section: 5-Step Order Flow -->
<section class="py-5" style="background-color: #FFFFFF; border-top: 1px solid rgba(216, 197, 174, 0.4); border-bottom: 1px solid rgba(216, 197, 174, 0.4);">
    <div class="container py-lg-3">
        <div class="text-center mx-auto mb-5" style="max-width: 680px;">
            <span class="text-uppercase fw-semibold small" style="color: var(--color-mocha); letter-spacing: 1px; font-size: 0.75rem;">
                Alur Layanan Pre-Order
            </span>
            <h2 class="display-6 fw-bold font-heading mb-2">Bagaimana Cara Kerja Uyuy Jastip?</h2>
            <p class="text-secondary small">Proses yang terstruktur dan terpadu memastikan setiap botol parfum pesanan Anda dibelanjakan secara aman dan transparan.</p>
        </div>

        <div class="row g-4 text-center">
            <!-- Step 1 -->
            <div class="col-md">
                <div class="p-3 h-100 rounded-4" style="background-color: #FAF8F5; border: 1px solid rgba(216, 197, 174, 0.3);">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 shadow-sm" style="width: 52px; height: 52px; background: linear-gradient(135deg, #8B6B50, #72563F); color: #FFFFFF; font-size: 1.25rem;">
                        <i class="bi bi-grid"></i>
                    </div>
                    <h6 class="fw-bold mb-1">1. Pilih Parfum</h6>
                    <p class="small text-secondary mb-0" style="font-size: 0.8rem; line-height: 1.5;">Pilih wewangian dan ukuran botol di katalog selama periode PO dibuka.</p>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="col-md">
                <div class="p-3 h-100 rounded-4" style="background-color: #FAF8F5; border: 1px solid rgba(216, 197, 174, 0.3);">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 shadow-sm" style="width: 52px; height: 52px; background: linear-gradient(135deg, #8B6B50, #72563F); color: #FFFFFF; font-size: 1.25rem;">
                        <i class="bi bi-cart-check"></i>
                    </div>
                    <h6 class="fw-bold mb-1">2. Buat Pesanan</h6>
                    <p class="small text-secondary mb-0" style="font-size: 0.8rem; line-height: 1.5;">Konfirmasi keranjang belanja dan lengkapi alamat pengiriman Anda.</p>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="col-md">
                <div class="p-3 h-100 rounded-4" style="background-color: #FAF8F5; border: 1px solid rgba(216, 197, 174, 0.3);">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 shadow-sm" style="width: 52px; height: 52px; background: linear-gradient(135deg, #8B6B50, #72563F); color: #FFFFFF; font-size: 1.25rem;">
                        <i class="bi bi-credit-card"></i>
                    </div>
                    <h6 class="fw-bold mb-1">3. Transfer BCA</h6>
                    <p class="small text-secondary mb-0" style="font-size: 0.8rem; line-height: 1.5;">Transfer total nominal ke rekening resmi BCA Merchant Uyuy Jastip.</p>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="col-md">
                <div class="p-3 h-100 rounded-4" style="background-color: #FAF8F5; border: 1px solid rgba(216, 197, 174, 0.3);">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 shadow-sm" style="width: 52px; height: 52px; background: linear-gradient(135deg, #8B6B50, #72563F); color: #FFFFFF; font-size: 1.25rem;">
                        <i class="bi bi-upload"></i>
                    </div>
                    <h6 class="fw-bold mb-1">4. Unggah Bukti</h6>
                    <p class="small text-secondary mb-0" style="font-size: 0.8rem; line-height: 1.5;">Kirim foto bukti transfer untuk diperiksa langsung oleh admin operasional.</p>
                </div>
            </div>

            <!-- Step 5 -->
            <div class="col-md">
                <div class="p-3 h-100 rounded-4" style="background-color: #FAF8F5; border: 1px solid rgba(216, 197, 174, 0.3);">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 shadow-sm" style="width: 52px; height: 52px; background: linear-gradient(135deg, #8B6B50, #72563F); color: #FFFFFF; font-size: 1.25rem;">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <h6 class="fw-bold mb-1">5. Pantau & Terima</h6>
                    <p class="small text-secondary mb-0" style="font-size: 0.8rem; line-height: 1.5;">Pantau status belanja hingga botol parfum aman tiba di tempat Anda.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Brand Story & Quality Assurance Card -->
<section class="py-5" style="background-color: var(--color-warm-ivory);">
    <div class="container py-lg-2">
        <div class="rounded-5 p-4 p-md-5 position-relative overflow-hidden shadow-lg" style="background: linear-gradient(135deg, #2D241E 0%, #1A1512 100%); color: #FDFBF7;">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <span class="badge rounded-pill px-3 py-1 text-uppercase mb-3" style="background: var(--color-champagne); color: var(--color-charcoal); font-size: 0.75rem; letter-spacing: 0.5px;">
                        Kualitas Teruji &bull; Original In Parfume
                    </span>
                    <h3 class="display-6 fw-bold font-heading text-white mb-3" style="letter-spacing: -0.02em;">
                        Mengapa Memilih Jastip di Uyuy Jastip?
                    </h3>
                    <p class="text-white-50 mb-0" style="line-height: 1.7; font-size: 1.05rem;">
                        Kami membelanjakan pesanan parfum Anda langsung dari toko resmi <strong>In Parfume</strong> di Bandung. Setiap siklus pre-order melayani ratusan pelanggan setia dengan pencatatan digital terpadu, memastikan botol parfum pesanan Anda tidak tertukar, tidak terlewat, dan dikemas dengan proteksi ekstra.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('catalog') }}" class="btn btn-champagne btn-lg px-4 py-3 shadow">
                        Mulai Belanja Sekarang <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
