@extends('layouts.app')

@section('title', 'Katalog Parfum — Uyuy Jastip')

@section('content')
@php($canOrder = $activePo && $activePo->isOrderableNow())

<div class="container catalog-page py-4">
    <nav class="product-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">Beranda</a>
        <span aria-hidden="true">/</span>
        <span aria-current="page">Katalog parfum</span>
    </nav>

    <header class="catalog-header">
        <div>
            <p class="home-eyebrow">KOLEKSI PARFUM</p>
            <h1 data-animate="words">Katalog parfum</h1>
            <p>Jelajahi produk, kategori aroma, dan ukuran botol yang tersedia.</p>
        </div>
        <a href="{{ route('po.info') }}" class="catalog-po-link">Cara kerja pre-order <span aria-hidden="true">→</span></a>
    </header>

    <section class="catalog-po-status" aria-label="Status pemesanan pre-order">
        <div>
            <p class="home-eyebrow">STATUS CHECKOUT</p>
            @if($canOrder)
                <x-status-badge status="Dibuka" />
                <strong>{{ $activePo->name }}</strong>
                <span>{{ $activePo->start_date->format('d M Y') }} – {{ $activePo->end_date->format('d M Y') }}</span>
                <span class="po-countdown-pill" data-po-countdown data-po-end="{{ $activePo->end_date->format('Y-m-d') }}T23:59:59+07:00">
                    <i class="bi bi-clock" aria-hidden="true"></i> Menghitung sisa waktu…
                </span>
            @else
                <x-status-badge status="Ditutup" />
                @if($activePo)
                    <strong>{{ $activePo->name }}</strong>
                    <span>{{ $activePo->start_date->format('d M Y') }} – {{ $activePo->end_date->format('d M Y') }}</span>
                @else
                    <strong>Belum ada periode yang dapat menerima pesanan.</strong>
                @endif
            @endif
        </div>
        <p>{{ $canOrder ? 'Pemesanan tersedia dalam rentang tanggal PO menurut waktu WIB.' : 'Katalog tetap dapat dijelajahi. Checkout akan tersedia sesuai status dan jadwal periode PO WIB.' }}</p>
    </section>

    <section class="catalog-filter-section" aria-label="Cari dan filter produk">
        <form action="{{ route('catalog') }}" method="GET" class="catalog-filter-form">
            <div class="catalog-filter-search">
                <label for="catalogSearch">Cari produk</label>
                <input type="search" id="catalogSearch" name="search" class="form-control" placeholder="Nama parfum, kategori, atau deskripsi" value="{{ request('search') }}" maxlength="100">
            </div>
            <div>
                <label for="catalogCategory">Kategori aroma</label>
                <select id="catalogCategory" name="category" class="form-select">
                    <option value="">Semua kategori</option>
                    <option value="Amber" {{ request('category') === 'Amber' ? 'selected' : '' }}>Amber</option>
                    <option value="Woody" {{ request('category') === 'Woody' ? 'selected' : '' }}>Woody</option>
                    <option value="Fresh" {{ request('category') === 'Fresh' ? 'selected' : '' }}>Fresh</option>
                    <option value="Gourmand" {{ request('category') === 'Gourmand' ? 'selected' : '' }}>Gourmand</option>
                    <option value="Lavender" {{ request('category') === 'Lavender' ? 'selected' : '' }}>Lavender</option>
                    <option value="Vanilla" {{ request('category') === 'Vanilla' ? 'selected' : '' }}>Vanilla</option>
                </select>
            </div>
            <div>
                <label for="catalogSize">Ukuran botol</label>
                <select id="catalogSize" name="size_ml" class="form-select">
                    <option value="">Semua ukuran</option>
                    <option value="35" {{ request('size_ml') == '35' ? 'selected' : '' }}>35 ml</option>
                    <option value="60" {{ request('size_ml') == '60' ? 'selected' : '' }}>60 ml</option>
                    <option value="100" {{ request('size_ml') == '100' ? 'selected' : '' }}>100 ml</option>
                </select>
            </div>
            <div>
                <label for="catalogSort">Urutkan</label>
                <select id="catalogSort" name="sort" class="form-select">
                    <option value="latest" {{ ($sort ?? request('sort', 'latest')) === 'latest' ? 'selected' : '' }}>Terbaru</option>
                    <option value="price_asc" {{ ($sort ?? request('sort')) === 'price_asc' ? 'selected' : '' }}>Harga terendah</option>
                    <option value="price_desc" {{ ($sort ?? request('sort')) === 'price_desc' ? 'selected' : '' }}>Harga tertinggi</option>
                    <option value="name_asc" {{ ($sort ?? request('sort')) === 'name_asc' ? 'selected' : '' }}>Nama A–Z</option>
                </select>
            </div>
            <div class="catalog-filter-actions">
                <button type="submit" class="btn btn-dark">Terapkan filter</button>
                @if(request('search') || request('category') || request('size_ml') || (request('sort') && request('sort') !== 'latest'))
                    <a href="{{ route('catalog') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>

        <div class="catalog-quick-filters" aria-label="Pilih kategori cepat">
            <span>Kategori cepat</span>
            @php($quickCategories = ['Amber', 'Woody', 'Fresh', 'Gourmand', 'Vanilla'])
            @foreach($quickCategories as $quickCategory)
                <a href="{{ route('catalog', ['category' => $quickCategory, 'search' => request('search'), 'size_ml' => request('size_ml')]) }}" @class(['active' => request('category') === $quickCategory]) aria-current="{{ request('category') === $quickCategory ? 'true' : 'false' }}">
                    {{ $quickCategory }}
                </a>
            @endforeach
        </div>
    </section>

    <div class="catalog-results-heading">
        <h2 data-animate="words">Produk parfum</h2>
        <span>{{ number_format($products->total(), 0, ',', '.') }} produk</span>
    </div>

    @if($products->count())
        <div class="product-grid catalog-results-grid">
            @foreach($products as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
        @if($products->hasPages())
            <nav class="catalog-pagination" aria-label="Navigasi halaman katalog">
                {{ $products->links() }}
            </nav>
        @endif
    @else
        <div class="home-empty-state catalog-empty-state">
            <i class="bi bi-search" aria-hidden="true"></i>
            <h3>Produk tidak ditemukan</h3>
            <p>Coba kata kunci atau filter lain untuk melihat produk yang tersedia.</p>
            <a href="{{ route('catalog') }}" class="btn btn-outline-secondary">Hapus semua filter</a>
        </div>
    @endif
</div>
@endsection
