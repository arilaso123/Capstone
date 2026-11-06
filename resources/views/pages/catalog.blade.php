@extends('layouts.app')

@section('title', 'Katalog Parfum')

@section('content')
<!-- Header Banner: Warm Editorial Breadcrumb & Status -->
<div class="py-4 border-bottom" style="background: radial-gradient(circle at 10% 20%, #FDFBF7 0%, #F4ECE3 90%); border-color: rgba(216, 197, 174, 0.4) !important;">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none" style="color: var(--color-mocha);">Beranda</a></li>
                        <li class="breadcrumb-item active text-secondary" aria-current="page">Katalog Parfum</li>
                    </ol>
                </nav>
                <h1 class="h2 fw-bold font-heading mb-1" style="color: var(--color-charcoal); letter-spacing: -0.02em;">Koleksi Parfum In Parfume</h1>
                <p class="text-secondary small mb-0">Temukan wewangian artisanal pilihan dengan formulasi aroma mewah dan ketahanan optimal.</p>
            </div>

            <!-- PO Status Indicator -->
            <div class="d-flex align-items-center gap-2 p-2 px-3 rounded-pill" style="background: #FFFFFF; border: 1px solid rgba(216, 197, 174, 0.6); box-shadow: 0 2px 8px rgba(45,36,30,0.04);">
                <span class="small fw-semibold text-secondary" style="font-size: 0.78rem;">Status PO:</span>
                @if($activePo && $activePo->isOpen())
                    <span class="badge-uyuy badge-po-open py-1 px-3 shadow-sm">
                        <i class="bi bi-broadcast"></i> DIBUKA
                    </span>
                @else
                    <span class="badge rounded-pill bg-secondary py-1 px-3">
                        <i class="bi bi-lock-fill"></i> DITUTUP
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Main Catalog Container -->
<div class="container py-5">
    
    <!-- Filter & Search Toolbar -->
    <x-card class="p-4 mb-4 border-0 shadow">
        <form action="{{ route('catalog') }}" method="GET">
            <div class="row g-3 align-items-center">
                <!-- Search Box -->
                <div class="col-lg-5">
                    <div class="input-group-uyuy d-flex align-items-center">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control form-control-uyuy" placeholder="Cari varian aroma, notes, atau brand..." value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Category Dropdown -->
                <div class="col-md-4 col-lg-3">
                    <select name="category" class="form-select form-control-uyuy">
                        <option value="">Semua Kategori Aroma</option>
                        <option value="Amber" {{ request('category') === 'Amber' ? 'selected' : '' }}>Amber / Floral</option>
                        <option value="Woody" {{ request('category') === 'Woody' ? 'selected' : '' }}>Woody / Aromatic</option>
                        <option value="Fresh" {{ request('category') === 'Fresh' ? 'selected' : '' }}>Fresh / Aquatic</option>
                        <option value="Gourmand" {{ request('category') === 'Gourmand' ? 'selected' : '' }}>Sweet / Gourmand</option>
                        <option value="Lavender" {{ request('category') === 'Lavender' ? 'selected' : '' }}>Floral / Lavender</option>
                        <option value="Vanilla" {{ request('category') === 'Vanilla' ? 'selected' : '' }}>Vanilla</option>
                    </select>
                </div>

                <!-- Size Dropdown -->
                <div class="col-md-4 col-lg-2">
                    <select name="size_ml" class="form-select form-control-uyuy">
                        <option value="">Semua Ukuran</option>
                        <option value="35" {{ request('size_ml') == '35' ? 'selected' : '' }}>35 ml</option>
                        <option value="60" {{ request('size_ml') == '60' ? 'selected' : '' }}>60 ml</option>
                        <option value="100" {{ request('size_ml') == '100' ? 'selected' : '' }}>100 ml</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="col-md-4 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-mocha w-100 py-2 d-flex align-items-center justify-content-center gap-1">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    @if(request('search') || request('category') || request('size_ml'))
                        <a href="{{ route('catalog') }}" class="btn btn-outline-mocha py-2 px-3" title="Reset Filter">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Quick Filter Pills -->
            <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-3 border-top" style="border-color: rgba(216, 197, 174, 0.3) !important;">
                <span class="small fw-semibold text-secondary me-1" style="font-size: 0.78rem;">Pilihan Cepat:</span>
                @php
                    $quickCategories = ['Amber', 'Woody', 'Fresh', 'Gourmand', 'Vanilla'];
                @endphp
                @foreach($quickCategories as $qc)
                    <a href="{{ route('catalog', ['category' => $qc]) }}" class="btn btn-sm demo-pill-btn {{ request('category') === $qc ? 'active' : '' }}" style="{{ request('category') === $qc ? 'background: var(--color-mocha); color: #fff; border-color: var(--color-mocha);' : '' }}">
                        {{ $qc }}
                    </a>
                @endforeach
            </div>
        </form>
    </x-card>

    <!-- Active PO Status Alert -->
    @if($activePo && $activePo->isOpen())
        <div class="alert p-3 mb-4 rounded-4 d-flex align-items-center gap-3 shadow-sm border-0" style="background: rgba(220, 252, 231, 0.7); border-left: 4px solid #16A34A !important;" role="alert">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: #FFFFFF; color: #16A34A; box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
                <i class="bi bi-check2-circle fs-5"></i>
            </div>
            <div>
                <strong class="text-dark">Periode Pre-Order {{ $activePo->name }} Sedang DIBUKA!</strong>
                <div class="small text-secondary">
                    Pesanan Anda akan dibelanjakan ke gerai <em>In Parfume</em> setelah periode PO ditutup ({{ $activePo->end_date->format('d M Y') }}). Anda dapat memesan lebih dari satu botol dalam 1 transaksi.
                </div>
            </div>
        </div>
    @else
        <div class="alert p-3 mb-4 rounded-4 d-flex align-items-center gap-3 shadow-sm border-0" style="background: rgba(244, 236, 227, 0.7); border-left: 4px solid var(--color-mocha) !important;" role="alert">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: #FFFFFF; color: var(--color-mocha); box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
                <i class="bi bi-lock-fill fs-5"></i>
            </div>
            <div>
                <strong class="text-dark">Saat ini belum ada periode Pre-Order yang dibuka.</strong>
                <div class="small text-secondary">
                    Katalog dapat dilihat untuk referensi varian dan aroma parfum, namun checkout pemesanan baru dapat dilakukan setelah Owner membuka batch PO berikutnya.
                </div>
            </div>
        </div>
    @endif

    <!-- Product Grid -->
    <div class="row g-4">
        @forelse($products as $product)
            <div class="col-sm-6 col-lg-4 col-xl-3">
                <x-card interactive class="h-100 d-flex flex-column border-0 shadow">
                    <!-- Image Wrapper -->
                    <div class="product-card-img-wrapper position-relative" style="height: 240px; background-color: #FAF8F5; border-top-left-radius: 20px; border-top-right-radius: 20px;">
                        @if($product->image_path)
                            @php
                                $imgSrc = str_starts_with($product->image_path, 'http')
                                    ? $product->image_path
                                    : asset('storage/' . $product->image_path);
                            @endphp
                            <img src="{{ $imgSrc }}" alt="{{ $product->name }}" class="product-card-img" loading="lazy">
                        @else
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center" style="background-color: var(--color-champagne-light); color: var(--color-mocha);">
                                <i class="bi bi-droplet fs-1"></i>
                            </div>
                        @endif

                        <!-- Size Pill -->
                        <span class="badge rounded-pill position-absolute top-0 end-0 m-3 px-3 py-1 shadow-sm" style="background: rgba(255, 255, 255, 0.92); color: var(--color-charcoal); font-size: 0.75rem; border: 1px solid rgba(216, 197, 174, 0.6);">
                            {{ $product->size_ml }} ml
                        </span>

                        <!-- Category Tag -->
                        @if($product->category)
                            <span class="badge rounded-pill position-absolute bottom-0 start-0 m-3 px-3 py-1 shadow-sm" style="background: rgba(45, 36, 30, 0.85); color: #FFFFFF; font-size: 0.72rem; letter-spacing: 0.3px;">
                                {{ $product->category }}
                            </span>
                        @endif
                    </div>

                    <!-- Content -->
                    <div class="p-4 d-flex flex-column flex-grow-1">
                        <span class="text-uppercase fw-semibold mb-1" style="color: var(--color-mocha); font-size: 0.72rem; letter-spacing: 0.5px;">
                            {{ $product->brand ?? 'In Parfume' }}
                        </span>

                        <h5 class="fw-bold font-heading mb-2">
                            <a href="{{ route('product.detail', $product->id) }}" class="text-decoration-none text-dark hover-mocha">
                                {{ $product->name }}
                            </a>
                        </h5>

                        <p class="text-secondary small mb-3 flex-grow-1" style="line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $product->description }}
                        </p>

                        <!-- Price & CTA -->
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto" style="border-color: rgba(216, 197, 174, 0.3) !important;">
                            <div>
                                <span class="text-muted small d-block" style="font-size: 0.72rem;">Mulai dari</span>
                                <span class="fw-bold font-heading text-dark fs-5">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </span>
                            </div>
                            <a href="{{ route('product.detail', $product->id) }}" class="btn btn-mocha btn-sm px-3 py-2 d-inline-flex align-items-center gap-1">
                                <span>Detail</span>
                                <i class="bi bi-chevron-right" style="font-size: 0.75rem;"></i>
                            </a>
                        </div>
                    </div>
                </x-card>
            </div>
        @empty
            <div class="col-12 py-5 text-center text-muted">
                <div class="p-5 rounded-4 mx-auto" style="max-width: 500px; background: #FAF8F5; border: 1px dashed rgba(216, 197, 174, 0.7);">
                    <i class="bi bi-search fs-1 d-block mb-3" style="color: var(--color-mocha);"></i>
                    <h5 class="fw-bold font-heading text-dark">Tidak Ada Varian yang Cocok</h5>
                    <p class="small text-secondary mb-3">Varian parfum yang Anda cari tidak ditemukan. Coba gunakan kata kunci lain atau reset filter pencarian.</p>
                    <a href="{{ route('catalog') }}" class="btn btn-outline-mocha btn-sm px-4 py-2">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Pencarian
                    </a>
                </div>
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
