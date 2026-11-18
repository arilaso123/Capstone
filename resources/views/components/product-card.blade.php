@props(['product'])

@php
    $imageSource = $product->image_path
        ? (str_starts_with($product->image_path, 'http') ? $product->image_path : asset('storage/' . $product->image_path))
        : null;
    $availableSizes = $product->variants->pluck('size_ml')->filter()->implode(' / ');
@endphp

<article class="product-tile">
    <a class="product-tile-image" href="{{ route('product.detail', $product->id) }}" aria-label="Lihat {{ $product->name }}">
        @if($imageSource)
            <img src="{{ $imageSource }}" alt="{{ $product->name }}" loading="lazy">
        @else
            <span class="product-tile-placeholder" aria-hidden="true"><i class="bi bi-droplet"></i></span>
        @endif
        @if($availableSizes)
            <span class="product-tile-size">{{ $availableSizes }} ml</span>
        @endif
    </a>

    <div class="product-tile-content">
        <p class="product-tile-meta">{{ $product->brand ?? 'In Parfume' }} <span aria-hidden="true">·</span> {{ $product->category ?? 'Parfum' }}</p>
        <h3><a href="{{ route('product.detail', $product->id) }}">{{ $product->name }}</a></h3>
        <p class="product-tile-description">{{ $product->description ?: 'Informasi aroma belum tersedia.' }}</p>
        <div class="product-tile-footer">
            <div>
                <span>Mulai dari</span>
                <strong>Rp {{ number_format($product->startingPrice() ?? (float) $product->price, 0, ',', '.') }}</strong>
            </div>
            <a href="{{ route('product.detail', $product->id) }}" class="product-tile-link">Lihat produk <span aria-hidden="true">→</span></a>
        </div>
    </div>
</article>
