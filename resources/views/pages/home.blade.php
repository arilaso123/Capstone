@extends('layouts.app')

@section('title', 'Uyuy Jastip — Katalog Parfum')

@section('content')
<section class="home-hero" aria-labelledby="homeHeroTitle">
    <div class="container home-hero-grid">
        <div class="home-hero-copy">
            <p class="home-eyebrow">UYUY JASTIP <span aria-hidden="true">/</span> JASA TITIP PARFUM</p>
            <h1 id="homeHeroTitle" data-animate="words">Temukan aroma pilihanmu.</h1>
            <p>Pilih parfum, tentukan ukuran botol, lalu ikuti jadwal pre-order dan instruksi pembayaran yang tersedia.</p>
            <div class="home-hero-actions">
                <a href="{{ route('catalog') }}" class="btn btn-dark">Jelajahi katalog <span aria-hidden="true">→</span></a>
                <a href="{{ route('po.info') }}" class="home-secondary-link">Cara kerja PO</a>
            </div>
        </div>

        <div class="home-hero-visual">
            <div class="home-hero-media">
                <img src="{{ asset('images/hero-perfume.jpg') }}" alt="Pilihan parfum yang tersedia melalui Uyuy Jastip" class="home-hero-image" fetchpriority="high">
            </div>
            <aside class="home-period-card" aria-label="Informasi periode pre-order">
                <p class="home-eyebrow">PERIODE PRE-ORDER</p>
                @if($activePo)
                    <div class="home-period-heading">
                        <h2>{{ $activePo->name }}</h2>
                        <x-status-badge :status="$activePo->isOrderableNow() ? 'Dibuka' : 'Ditutup'" />
                    </div>
                    <p class="home-period-date">{{ $activePo->start_date->format('d M Y') }} – {{ $activePo->end_date->format('d M Y') }}</p>
                    @if($activePo->isOrderableNow())
                        <span class="po-countdown-pill" data-po-countdown data-po-end="{{ $activePo->end_date->format('Y-m-d') }}T23:59:59+07:00">
                            <i class="bi bi-clock" aria-hidden="true"></i> Menghitung sisa waktu…
                        </span>
                    @endif
                    <p class="home-period-note">
                        {{ $activePo->isOrderableNow() ? 'Pemesanan tersedia dalam rentang tanggal PO WIB.' : 'Checkout belum tersedia di luar rentang tanggal PO WIB.' }}
                    </p>
                @else
                    <h2>Jadwal berikutnya belum tersedia</h2>
                    <p class="home-period-note">Lihat informasi PO untuk memahami alur pemesanan.</p>
                @endif
                <a href="{{ route('po.info') }}" class="home-period-link">Informasi jadwal PO <span aria-hidden="true">→</span></a>
            </aside>
        </div>
    </div>
</section>

<section class="home-products-section" aria-labelledby="homeProductsTitle">
    <div class="container">
        <header class="home-section-header">
            <div>
                <p class="home-eyebrow">KOLEKSI PARFUM</p>
                <h2 id="homeProductsTitle" data-animate="words">Pilihan dari katalog</h2>
            </div>
            <a href="{{ route('catalog') }}" class="home-text-link">Lihat seluruh produk <span aria-hidden="true">→</span></a>
        </header>

        @if($featuredProducts->isNotEmpty())
            <div class="product-grid">
                @foreach($featuredProducts as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @else
            <div class="home-empty-state">
                <h3>Katalog sedang diperbarui</h3>
                <p>Produk parfum akan tampil di sini setelah tersedia.</p>
                <a href="{{ route('catalog') }}" class="home-text-link">Buka katalog <span aria-hidden="true">→</span></a>
            </div>
        @endif
    </div>
</section>

<section class="home-order-section" aria-labelledby="homeOrderTitle">
    <div class="container">
        <header class="home-section-header">
            <div>
                <p class="home-eyebrow">ALUR PEMESANAN</p>
                <h2 id="homeOrderTitle" data-animate="words">Dari pilih parfum sampai pantau pesanan.</h2>
            </div>
            <a href="{{ route('po.info') }}" class="home-text-link">Baca informasi PO <span aria-hidden="true">→</span></a>
        </header>

        <ol class="home-order-steps">
            <li><span class="home-step-number">01</span><h3>Pilih produk</h3><p>Tentukan parfum dan ukuran botol yang tersedia.</p></li>
            <li><span class="home-step-number">02</span><h3>Buat pesanan</h3><p>Masukkan pilihan ke keranjang dan lengkapi data checkout.</p></li>
            <li><span class="home-step-number">03</span><h3>Ikuti pembayaran</h3><p>Gunakan nominal dan instruksi yang tertera pada pesanan.</p></li>
            <li><span class="home-step-number">04</span><h3>Pantau status</h3><p>Unggah bukti pembayaran dan lihat perkembangan pesanan di akunmu.</p></li>
        </ol>
    </div>
</section>

<section class="home-about-section" aria-labelledby="homeAboutTitle">
    <div class="container home-about-inner">
        <div>
            <p class="home-eyebrow">TENTANG UYUY JASTIP</p>
            <h2 id="homeAboutTitle" data-animate="words">Jastip parfum dengan alur yang jelas.</h2>
        </div>
        <div>
            <p>Uyuy Jastip membantu pelanggan memesan pilihan parfum melalui periode pre-order. Informasi produk, ukuran, jadwal, dan status pesanan tersedia dalam satu alur.</p>
            <a href="{{ route('catalog') }}" class="btn btn-outline-secondary">Jelajahi katalog <span aria-hidden="true">→</span></a>
        </div>
    </div>
</section>
@endsection
