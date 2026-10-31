@extends('layouts.owner')

@section('title', 'Manajemen Master Produk')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 fw-bold mb-1 font-heading">Katalog Produk Parfum (In Parfume)</h2>
        <span class="text-muted small">Kelola master varian produk parfum, ukuran, penetapan harga, dan ketersediaan (FR-05).</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('owner.products.create') }}" class="btn btn-mocha btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Tambah Produk Baru
        </a>
    </div>
</div>

<div class="card card-uyuy p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
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
                                <img src="{{ $img }}" alt="{{ $product->name }}" class="rounded border" style="width: 48px; height: 48px; object-fit: cover;">
                            @else
                                <div class="rounded border bg-light d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="bi bi-droplet text-muted"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <strong class="text-dark">{{ $product->name }}</strong>
                        </td>
                        <td>{{ $product->brand ?? 'In Parfume' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $product->category ?? '-' }}</span></td>
                        <td>{{ $product->size_ml }} ml</td>
                        <td class="fw-bold">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                        <td>
                            @if($product->is_active)
                                <span class="badge bg-success">Aktif di Katalog</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('owner.products.edit', $product->id) }}" class="btn btn-outline-secondary">Edit</a>
                                <form action="{{ route('owner.products.toggle', $product->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn {{ $product->is_active ? 'btn-outline-warning text-dark' : 'btn-outline-success' }}">
                                        {{ $product->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-box-seam fs-2 d-block mb-2"></i>
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
@endsection
