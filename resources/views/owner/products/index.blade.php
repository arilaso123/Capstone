@extends('layouts.owner')

@section('title', 'Manajemen Master Produk')

@section('content')
<section class="owner-products-page" aria-labelledby="ownerProductsTitle">
<header class="workspace-page-header">
    <div>
        <p class="owner-recap-eyebrow">OWNER / MASTER PRODUK</p>
        <h1 id="ownerProductsTitle">Katalog produk parfum</h1>
        <p>Kelola master varian produk parfum, ukuran, penetapan harga, dan ketersediaan (FR-05).</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('owner.products.create') }}" class="btn btn-dark">
            <i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah Produk Baru
        </a>
    </div>
</header>

<div class="card card-uyuy owner-products-panel p-3 p-md-4">
    <div class="table-responsive">
        <table class="table owner-products-table align-middle mb-0">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Nama Varian Parfum</th>
                    <th>Vendor</th>
                    <th>Kategori Aroma</th>
                    <th>Ukuran</th>
                    <th>Harga Pre-Order</th>
                    <th>Status Ketersediaan</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>
                            @if($product->image_path)
                                @php
                                    $img = str_starts_with($product->image_path, 'http')
                                        ? $product->image_path
                                        : asset('storage/' . $product->image_path);
                                @endphp
                                <img src="{{ $img }}" alt="{{ $product->name }}" class="owner-product-thumb">
                            @else
                                <div class="owner-product-thumb owner-product-thumb-empty">
                                    <i class="bi bi-droplet text-muted" aria-hidden="true"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $product->name }}</strong>
                        </td>
                        <td>{{ $product->brand ?? 'In Parfume' }}</td>
                        <td><span class="badge-uyuy badge-po-closed">{{ $product->category ?? '-' }}</span></td>
                        <td>{{ $product->variants->pluck('size_ml')->implode(', ') ?: $product->size_ml }} ml</td>
                        <td>Mulai Rp {{ number_format($product->startingPrice() ?? (float) $product->price, 0, ',', '.') }}</td>
                        <td>
                            @if($product->is_active)
                                <span class="badge-uyuy badge-po-open"><i class="bi bi-check-lg" aria-hidden="true"></i> Aktif</span>
                            @else
                                <span class="badge-uyuy badge-po-closed"><i class="bi bi-pause-circle" aria-hidden="true"></i> Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex flex-wrap justify-content-end gap-1">
                                <a href="{{ route('owner.products.edit', $product->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form action="{{ route('owner.products.toggle', $product->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $product->is_active ? 'btn-outline-secondary' : 'btn-dark' }}">
                                        {{ $product->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-box-seam fs-2 d-block mb-2" aria-hidden="true"></i>
                            Belum ada produk parfum di katalog.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $products->links() }}
        </div>
    @endif
</div>
</section>
@endsection
