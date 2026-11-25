@extends('layouts.app')

@section('title', $product->name . ' — Uyuy Jastip')

@section('content')
@php
    $requestedSize = (int) old('size_ml', $product->variants->first()?->size_ml ?? $product->size_ml);
    $initialVariant = $product->variants->firstWhere('size_ml', $requestedSize) ?? $product->variants->first();
    $initialSizeMl = $initialVariant?->size_ml ?? $product->size_ml;
    $initialUnitPrice = $initialVariant?->price ?? $product->price;
    $initialQuantity = max(1, min(50, (int) old('quantity', 1)));
@endphp

<div class="container product-detail-page py-4" aria-labelledby="productTitle">
    <nav class="product-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">Beranda</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('catalog') }}">Katalog Parfum</a>
        <span aria-hidden="true">/</span>
        <span aria-current="page">{{ $product->name }}</span>
    </nav>

    <div class="row g-4 g-xl-5 align-items-start">
        <div class="col-lg-5">
            <div class="product-detail-gallery">
                <div class="product-detail-image-frame">
                    @if($product->image_path)
                        @php
                            $imgSrc = str_starts_with($product->image_path, 'http')
                                ? $product->image_path
                                : asset('storage/' . $product->image_path);
                        @endphp
                        <img src="{{ $imgSrc }}" alt="{{ $product->name }}" class="product-detail-image">
                    @else
                        <div class="product-detail-image-placeholder">
                            <i class="bi bi-droplet" aria-hidden="true"></i>
                            <span>Foto produk belum tersedia</span>
                        </div>
                    @endif
                    <span class="product-detail-brand">{{ $product->brand ?? 'In Parfume' }}</span>
                </div>
                <p class="product-detail-image-caption">{{ $product->name }} <span aria-hidden="true">·</span> Foto produk</p>
            </div>
        </div>

        <div class="col-lg-7">
            <section class="product-detail-information">
                <div class="product-detail-meta">
                    @if($activePo && $activePo->isOrderableNow())
                        <x-status-badge status="Dibuka" />
                    @else
                        <x-status-badge status="Ditutup" />
                    @endif
                    @if($product->category)
                        <span class="product-detail-category">{{ $product->category }}</span>
                    @endif
                </div>

                <p class="product-detail-eyebrow">{{ $product->brand ?? 'In Parfume Bandung' }}</p>
                <h1 class="product-detail-title" id="productTitle">{{ $product->name }}</h1>
                <p class="product-detail-intro">Periksa pilihan ukuran, harga per botol, dan jadwal pre-order sebelum menambahkan ke keranjang.</p>

                <section class="product-detail-price" aria-label="Informasi harga" aria-live="polite">
                    <span class="product-detail-price-label">Harga per botol</span>
                    <div class="product-detail-unit-price">
                        <strong id="displayPrice">Rp {{ number_format($initialUnitPrice, 0, ',', '.') }}</strong>
                        <span id="displayUnit">/ {{ $initialSizeMl }} ml</span>
                    </div>
                    <div class="product-detail-total">
                        <span>Subtotal sesuai jumlah</span>
                        <strong id="displayTotal">Rp {{ number_format($initialUnitPrice * $initialQuantity, 0, ',', '.') }}</strong>
                    </div>
                </section>

                @if(session('error'))
                    <div class="product-detail-message" role="alert">{{ session('error') }}</div>
                @endif

                @if($activePo && $activePo->isOrderableNow() && $product->is_active)
                    <form action="{{ route('customer.cart.add') }}" method="POST" class="product-detail-form">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        <fieldset class="product-detail-fieldset" @if($errors->has('size_ml')) aria-describedby="product-size-error" @endif>
                            <legend class="product-detail-label">Pilih ukuran botol</legend>
                            @if($product->variants->isNotEmpty())
                                <div class="product-variant-grid">
                                    @foreach($product->variants as $variant)
                                        <div>
                                            <input
                                                class="product-size-input"
                                                type="radio"
                                                name="size_ml"
                                                id="size{{ $variant->size_ml }}"
                                                value="{{ $variant->size_ml }}"
                                                data-price="{{ (int) $variant->price }}"
                                                {{ (int) $initialSizeMl === (int) $variant->size_ml ? 'checked' : '' }}
                                                aria-invalid="{{ $errors->has('size_ml') ? 'true' : 'false' }}"
                                                @if($errors->has('size_ml')) aria-describedby="product-size-error" @endif
                                                {{ $loop->first ? 'required' : '' }}
                                            >
                                            <label class="product-size-card" for="size{{ $variant->size_ml }}">
                                                <span class="product-size-name">{{ $variant->size_ml }} ml</span>
                                                <span class="product-size-price">Rp {{ number_format($variant->price, 0, ',', '.') }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <input type="hidden" name="size_ml" value="{{ $product->size_ml }}" data-price="{{ (int) $product->price }}">
                                <div class="product-size-fallback">{{ $product->size_ml }} ml <span>Rp {{ number_format($product->price, 0, ',', '.') }}</span></div>
                            @endif
                            @error('size_ml')
                                <p class="product-detail-error" id="product-size-error" role="alert">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        <div class="product-detail-quantity-row">
                            <label class="product-detail-label" for="qtyInput">Jumlah botol</label>
                            <div class="product-quantity-control">
                                <button class="product-quantity-button" type="button" id="btnMinus" aria-label="Kurangi jumlah botol">−</button>
                                <input type="number" name="quantity" id="qtyInput" value="{{ $initialQuantity }}" min="1" max="50" inputmode="numeric" aria-invalid="{{ $errors->has('quantity') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('quantity') ? 'quantityHint quantity-error' : 'quantityHint' }}">
                                <button class="product-quantity-button" type="button" id="btnPlus" aria-label="Tambah jumlah botol">+</button>
                            </div>
                            <span class="product-detail-hint" id="quantityHint">Maksimal 50 botol per produk.</span>
                            @error('quantity')
                                <p class="product-detail-error" id="quantity-error" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="product-detail-actions">
                            <x-button type="submit" variant="mocha" class="product-detail-submit" icon="bi bi-bag-plus">
                                Masukkan ke Keranjang
                            </x-button>
                            <a href="{{ route('customer.cart') }}" class="btn btn-outline-secondary product-detail-cart-link">
                                <i class="bi bi-bag" aria-hidden="true"></i> Lihat keranjang
                            </a>
                        </div>
                    </form>
                @elseif(!$product->is_active)
                    <div class="product-detail-notice" role="status">
                        <strong>Produk sedang tidak tersedia</strong>
                        <span>Varian ini belum dapat dipesan saat ini.</span>
                    </div>
                @else
                    <div class="product-detail-notice" role="status">
                        <strong>Periode pre-order belum dapat menerima pesanan</strong>
                        <span>Pemesanan dibuka sesuai status dan rentang tanggal periode PO dalam waktu WIB.</span>
                        @if($activePo)
                            <span>{{ $activePo->name }} · {{ $activePo->start_date->format('d M Y') }} – {{ $activePo->end_date->format('d M Y') }}</span>
                        @endif
                        <a href="{{ route('po.info') }}">Lihat informasi PO <span aria-hidden="true">→</span></a>
                    </div>
                @endif

                <section class="product-detail-description" aria-labelledby="productDescriptionTitle">
                    <h2 id="productDescriptionTitle" data-animate="words">Tentang produk</h2>
                    <p>{{ $product->description ?: 'Informasi aroma untuk produk ini belum tersedia.' }}</p>
                    @if($product->category)
                        <p class="product-detail-description-category"><span>Kategori aroma</span>{{ $product->category }}</p>
                    @endif
                </section>
            </section>
        </div>
    </div>

    @if(isset($relatedProducts) && $relatedProducts->isNotEmpty())
        <section class="related-products-section" aria-labelledby="relatedProductsTitle">
            <header class="related-products-header">
                <div>
                    <p class="home-eyebrow">REKOMENDASI AROMA</p>
                    <h2 id="relatedProductsTitle">Parfum serupa yang mungkin Anda suka</h2>
                </div>
                <a href="{{ route('catalog') }}" class="home-text-link">Lihat seluruh katalog <span aria-hidden="true">→</span></a>
            </header>
            <div class="product-grid">
                @foreach($relatedProducts as $relatedProduct)
                    <x-product-card :product="$relatedProduct" />
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
