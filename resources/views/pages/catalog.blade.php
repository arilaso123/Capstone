@extends('layouts.app')

@section('title', 'Katalog Parfum')

@section('content')
<!-- Header Banner -->
<div class="py-4 border-bottom" style="background-color: var(--color-champagne-light);">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-secondary">Beranda</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Katalog Parfum</li>
                    </ol>
                </nav>
                <h1 class="h3 fw-bold mb-0">Koleksi Parfum Uyuy Jastip</h1>
                <p class="text-secondary small mb-0">Semua varian wewangian resmi vendor <em>In Parfume</em>.</p>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="small text-muted">Status PO:</span>
                @if($activePo && $activePo->isOpen())
                    <span class="badge-uyuy badge-po-open"><i class="bi bi-unlock-fill"></i> DIBUKA</span>
                @else
                    <span class="badge bg-secondary"><i class="bi bi-lock-fill"></i> DITUTUP</span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Main Catalog Content -->
<div class="container py-5">
    <!-- Filter & Search Bar -->
    <div class="card card-uyuy p-3 mb-4">
        <form action="{{ route('catalog') }}" method="GET">
            <div class="row g-3 align-items-center">
                <div class="col-lg-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Cari varian parfum, aroma, atau brand..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <select name="category" class="form-select">
                        <option value="">Semua Kategori Aroma</option>
                        <option value="Amber" {{ request('category') === 'Amber' ? 'selected' : '' }}>Amber / Floral</option>
                        <option value="Woody" {{ request('category') === 'Woody' ? 'selected' : '' }}>Woody / Aromatic</option>
                        <option value="Fresh" {{ request('category') === 'Fresh' ? 'selected' : '' }}>Fresh / Aquatic</option>
                        <option value="Gourmand" {{ request('category') === 'Gourmand' ? 'selected' : '' }}>Sweet / Gourmand</option>
                        <option value="Lavender" {{ request('category') === 'Lavender' ? 'selected' : '' }}>Floral / Lavender</option>
                        <option value="Vanilla" {{ request('category') === 'Vanilla' ? 'selected' : '' }}>Vanilla</option>
                    </select>
                </div>
                <div class="col-md-4 col-lg-2">
                    <select name="size_ml" class="form-select">
                        <option value="">Semua Ukuran</option>
                        <option value="35" {{ request('size_ml') == '35' ? 'selected' : '' }}>35 ml</option>
                        <option value="60" {{ request('size_ml') == '60' ? 'selected' : '' }}>60 ml</option>
                        <option value="100" {{ request('size_ml') == '100' ? 'selected' : '' }}>100 ml</option>
                    </select>
                </div>
                <div class="col-md-4 col-lg-2 d-flex gap-1">
                    <button type="submit" class="btn btn-mocha w-100">
                        <i class="bi bi-funnel"></i> Terapkan
                    </button>
                    @if(request('search') || request('category') || request('size_ml'))
                        <a href="{{ route('catalog') }}" class="btn btn-outline-secondary" title="Reset">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Active PO Alert -->
    @if($activePo && $activePo->isOpen())
        <div class="alert alert-success d-flex align-items-center gap-3 card-uyuy border-success mb-4" role="alert">
            <i class="bi bi-check-circle-fill text-success fs-3"></i>
            <div>
                <strong>Periode Pre-Order {{ $activePo->name }} Sedang Dibuka!</strong>
                <div class="small">Pesanan akan diproses dan dibelanjakan ke vendor <em>In Parfume</em> setelah periode PO ditutup ({{ $activePo->end_date->format('d M Y') }}). Anda dapat memilih beberapa varian dalam 1 pesanan.</div>
            </div>
        </div>
    @else
        <div class="alert alert-secondary d-flex align-items-center gap-3 card-uyuy mb-4" role="alert">
            <i class="bi bi-lock-fill fs-3 text-secondary"></i>
            <div>
                <strong>Saat ini belum ada periode Pre-Order yang dibuka.</strong>
                <div class="small">Katalog dapat dilihat untuk referensi varian parfum, namun pemesanan baru dapat dilakukan saat Owner membuka batch PO berikutnya.</div>
            </div>
        </div>
    @endif

    <!-- Product Grid -->
    <div class="row g-4">
        @forelse($products as $product)
            <div class="col-sm-6 col-lg-4 col-xl-3">
                <div class="card card-uyuy product-card h-100 d-flex flex-column">
                    <div class="product-card-img-wrapper position-relative overflow-hidden" style="height: 240px; background-color: var(--color-warm-ivory);">
                        @if($product->image_path)
                            @php
                                $imgSrc = str_starts_with($product->image_path, 'http')
                                    ? $product->image_path
                                    : asset('storage/' . $product->image_path);
                            @endphp
                            <img src="{{ $imgSrc }}" alt="{{ $product->name }}" class="product-card-img" loading="lazy">
                        @else
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                                <i class="bi bi-droplet fs-1"></i>
                            </div>
                        @endif
                        <span class="badge position-absolute top-0 end-0 m-2 bg-light text-dark border">
                            {{ $product->size_ml }} ml
                        </span>
                        @if($product->category)
                            <span class="badge position-absolute bottom-0 start-0 m-2 bg-dark bg-opacity-75 text-white">
                                {{ $product->category }}
                            </span>
                        @endif
                    </div>

                    <div class="p-4 d-flex flex-column flex-grow-1">
                        <span class="text-uppercase fw-semibold text-muted mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">{{ $product->brand ?? 'In Parfume' }}</span>
                        <h5 class="fw-bold mb-2 font-heading">
                            <a href="{{ route('product.detail', $product->id) }}" class="text-decoration-none text-dark hover-mocha">
                                {{ $product->name }}
                            </a>
                        </h5>
                        <p class="text-secondary small mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $product->description }}
                        </p>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                            <div>
                                <span class="text-muted small d-block" style="font-size: 0.75rem;">Mulai dari</span>
                                <span class="fw-bold text-dark fs-5">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                            </div>
                            <a href="{{ route('product.detail', $product->id) }}" class="btn btn-mocha btn-sm d-flex align-items-center gap-1">
                                Detail <i class="bi bi-chevron-right" style="font-size: 0.75rem;"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 py-5 text-center text-muted">
                <i class="bi bi-search fs-1 d-block mb-3"></i>
                <h5>Tidak ada varian parfum yang cocok dengan pencarian Anda.</h5>
                <p class="small">Coba gunakan kata kunci lain atau reset filter pencarian.</p>
                <a href="{{ route('catalog') }}" class="btn btn-outline-secondary btn-sm mt-2">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Pencarian
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($products->hasPages())
        <div class="d-flex justify-content-center mt-5">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
