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
        <form action="{{ route('catalog') }}" method="GET" id="catalog-filter-form">
            <div class="row g-3 align-items-end">
                <!-- Kolom Pencarian -->
                <div class="col-lg-4">
                    <label class="form-label small fw-semibold mb-1">Cari Parfum</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Nama, aroma, atau brand..." value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Filter Jenis / Kategori Aroma -->
                <div class="col-md-4 col-lg-3">
                    <label class="form-label small fw-semibold mb-1">
                        <i class="bi bi-tags me-1"></i>Jenis / Kategori Aroma
                    </label>
                    <select name="category" class="form-select" aria-label="Filter berdasarkan kategori aroma">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ $selectedCategory === $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Ukuran -->
                <div class="col-md-4 col-lg-3">
                    <label class="form-label small fw-semibold mb-1">
                        <i class="bi bi-droplet me-1"></i>Tampilkan Harga Ukuran
                    </label>
                    <select name="size_ml" class="form-select" aria-label="Tampilkan harga menurut ukuran">
                        <option value="">Harga ukuran acuan (60 ml)</option>
                        @foreach($availableSizes as $size)
                            <option value="{{ $size }}" {{ (int) $selectedSize === $size ? 'selected' : '' }}>
                                Harga {{ $size }} ml
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tombol Aksi -->
                <div class="col-md-4 col-lg-2 d-flex gap-1 align-items-end">
                    <button type="submit" class="btn btn-mocha flex-grow-1">
                        <i class="bi bi-funnel"></i> Terapkan
                    </button>
                    @if(request('search') || $selectedCategory || $selectedSize)
                        <a href="{{ route('catalog') }}" class="btn btn-outline-secondary" title="Reset semua filter">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </div>

            <div class="small text-muted mt-2">
                <i class="bi bi-info-circle me-1"></i>
                Filter <strong>Jenis</strong> menyaring produk berdasarkan kategori aroma.
                Filter <strong>Ukuran</strong> hanya mengubah harga tampil di kartu (semua parfum tersedia dalam 35, 60, dan 100 ml).
            </div>
        </form>
    </div>

    <!-- Kategori Cepat (Quick Filters) -->
    @if($categories->isNotEmpty())
        <div class="d-flex flex-wrap gap-2 mb-4 align-items-center">
            <span class="small text-muted fw-semibold me-1">Jenis:</span>
            <a href="{{ route('catalog', array_filter(['search' => request('search'), 'size_ml' => $selectedSize])) }}"
               class="btn btn-sm {{ !$selectedCategory ? 'btn-mocha' : 'btn-outline-secondary' }}">
                Semua
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('catalog', array_filter(['search' => request('search'), 'category' => $cat, 'size_ml' => $selectedSize])) }}"
                   class="btn btn-sm {{ $selectedCategory === $cat ? 'btn-mocha' : 'btn-outline-secondary' }}">
                    {{ $cat }}
                </a>
            @endforeach
        </div>
    @endif

    <!-- Tombol Ukuran Cepat -->
    <div class="d-flex flex-wrap gap-2 mb-4 align-items-center">
        <span class="small text-muted fw-semibold me-1">Ukuran:</span>
        <a href="{{ route('catalog', array_filter(['search' => request('search'), 'category' => $selectedCategory])) }}"
           class="btn btn-sm {{ !$selectedSize ? 'btn-dark' : 'btn-outline-secondary' }}">
            Acuan
        </a>
        @foreach($availableSizes as $size)
            <a href="{{ route('catalog', array_filter(['search' => request('search'), 'category' => $selectedCategory, 'size_ml' => $size])) }}"
               class="btn btn-sm {{ (int) $selectedSize === $size ? 'btn-dark' : 'btn-outline-secondary' }}">
                {{ $size }} ml
            </a>
        @endforeach
    </div>

    <!-- Ringkasan hasil filter -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <p class="small text-muted mb-0">
            Menampilkan <strong>{{ $products->total() }}</strong> parfum
            @if($selectedCategory)
                · Kategori: <strong>{{ $selectedCategory }}</strong>
            @endif
            @if($selectedSize)
                · Harga <strong>{{ $selectedSize }} ml</strong>
            @endif
        </p>
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
            @php
                $cardSize  = $selectedSize ?: $product->size_ml;
                $cardPrice = $selectedSize ? $product->getPriceForSize($selectedSize) : $product->price;
                $detailUrl = route('product.detail', $product->id) . ($selectedSize ? '?size_ml=' . $selectedSize : '');
            @endphp
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
                        <!-- Badge ukuran -->
                        <span class="badge position-absolute top-0 end-0 m-2 bg-light text-dark border">
                            {{ $cardSize }} ml
                        </span>
                        <!-- Badge kategori -->
                        @if($product->category)
                            <a href="{{ route('catalog', array_filter(['category' => $product->category, 'size_ml' => $selectedSize])) }}"
                               class="badge position-absolute bottom-0 start-0 m-2 bg-dark bg-opacity-75 text-white text-decoration-none"
                               title="Filter kategori {{ $product->category }}">
                                {{ $product->category }}
                            </a>
                        @endif
                    </div>

                    <div class="p-4 d-flex flex-column flex-grow-1">
                        <span class="text-uppercase fw-semibold text-muted mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">{{ $product->brand ?? 'In Parfume' }}</span>
                        <h5 class="fw-bold mb-2 font-heading">
                            <a href="{{ $detailUrl }}" class="text-decoration-none text-dark hover-mocha">
                                {{ $product->name }}
                            </a>
                        </h5>
                        <p class="text-secondary small mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $product->description }}
                        </p>

                        <!-- Pilihan ukuran cepat di kartu -->
                        <div class="d-flex gap-1 mb-3 flex-wrap">
                            @foreach($availableSizes as $sz)
                                <a href="{{ route('catalog', array_filter(['search' => request('search'), 'category' => $selectedCategory, 'size_ml' => $sz])) . '#top' }}"
                                   class="badge text-decoration-none {{ (int) $selectedSize === $sz ? 'bg-dark text-white' : 'bg-light text-dark border' }}"
                                   title="Lihat harga {{ $sz }} ml">
                                    {{ $sz }} ml
                                </a>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                            <div>
                                <span class="text-muted small d-block" style="font-size: 0.75rem;">{{ $selectedSize ? 'Harga ' . $selectedSize . ' ml' : 'Harga acuan (60 ml)' }}</span>
                                <span class="fw-bold text-dark fs-5">Rp {{ number_format($cardPrice, 0, ',', '.') }}</span>
                            </div>
                            <a href="{{ $detailUrl }}" class="btn btn-mocha btn-sm d-flex align-items-center gap-1">
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
