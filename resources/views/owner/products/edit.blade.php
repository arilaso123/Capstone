@extends('layouts.owner')

@section('title', 'Ubah Data Produk #' . $product->id)

@section('content')
<div class="owner-form-page owner-product-edit">
<nav class="owner-form-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('owner.dashboard') }}">Dashboard</a>
    <span aria-hidden="true">/</span>
    <a href="{{ route('owner.products.index') }}">Produk</a>
    <span aria-hidden="true">/</span>
    <span>Edit produk #{{ $product->id }}</span>
</nav>

<header class="workspace-page-header">
    <div>
        <p class="owner-recap-eyebrow">OWNER / MASTER PRODUK</p>
        <h1>Ubah informasi produk parfum</h1>
    </div>
    <a href="{{ route('owner.products.index') }}" class="workspace-back-link">
        <i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke daftar
    </a>
</header>

<div class="owner-product-edit-panel p-3 p-md-4">
            <form action="{{ route('owner.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="owner-product-form">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="name" class="form-label small fw-semibold">Nama Varian Parfum <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" @if($errors->has('name')) aria-describedby="name-error" @endif>
                        @error('name')
                            <div class="invalid-feedback small" id="name-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="brand" class="form-label small fw-semibold">Vendor / Merek</label>
                        <input type="text" class="form-control bg-light" id="brand" name="brand" value="{{ old('brand', $product->brand ?? 'In Parfume') }}" readonly>
                    </div>

                    <div class="col-md-4">
                        <label for="category" class="form-label small fw-semibold">Kategori Aroma</label>
                        <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" aria-invalid="{{ $errors->has('category') ? 'true' : 'false' }}" {{ $errors->has('category') ? 'aria-describedby="category-error"' : '' }}>
                            @foreach(['Amber Floral', 'Woody Aromatic', 'Fresh Citrus', 'Sweet Gourmand', 'Fruity Floral', 'Aquatic Fresh', 'Floral Lavender', 'Sweet Vanilla', 'Unisex'] as $cat)
                                <option value="{{ $cat }}" {{ old('category', $product->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <div class="invalid-feedback small" id="category-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <fieldset class="col-12 border-0 p-0" {{ $errors->has('variants') ? 'aria-describedby="variants-error"' : '' }}>
                        <legend class="form-label small fw-semibold">Harga dan Modal per Ukuran</legend>
                        <div class="row g-3">
                            @foreach([35, 60, 100] as $size)
                                @php
                                    $variant = $product->variantForSize($size);
                                @endphp
                                <div class="col-md-4">
                                    <div class="owner-product-variant h-100" role="group" aria-label="Harga {{ $size }} ml">
                                        <div class="fw-bold mb-2">{{ $size }} ml</div>
                                        <label for="price_{{ $size }}" class="form-label small">Harga jual (Rp)</label>
                                        <input type="number" class="form-control mb-2 @error('variants.'.$size.'.price') is-invalid @enderror" id="price_{{ $size }}" name="variants[{{ $size }}][price]" value="{{ old('variants.'.$size.'.price', $variant?->price) }}" min="1" step="1000" placeholder="Kosongkan jika tidak tersedia" aria-invalid="{{ $errors->has('variants.'.$size.'.price') ? 'true' : 'false' }}" {{ $errors->has('variants.'.$size.'.price') ? 'aria-describedby="price-'.$size.'-error"' : '' }}>
                                        @error('variants.'.$size.'.price')<div class="invalid-feedback" id="price-{{ $size }}-error">{{ $message }}</div>@enderror
                                        <label for="cost_{{ $size }}" class="form-label small">Harga modal (Rp, opsional)</label>
                                        <input type="number" class="form-control @error('variants.'.$size.'.cost_price') is-invalid @enderror" id="cost_{{ $size }}" name="variants[{{ $size }}][cost_price]" value="{{ old('variants.'.$size.'.cost_price', $variant?->cost_price) }}" min="0" step="1000" aria-invalid="{{ $errors->has('variants.'.$size.'.cost_price') ? 'true' : 'false' }}" {{ $errors->has('variants.'.$size.'.cost_price') ? 'aria-describedby="cost-'.$size.'-error"' : '' }}>
                                        @error('variants.'.$size.'.cost_price')<div class="invalid-feedback" id="cost-{{ $size }}-error">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('variants')<div class="text-danger small mt-2" id="variants-error">{{ $message }}</div>@enderror
                    </fieldset>

                    <div class="col-12">
                        <label for="description" class="form-label small fw-semibold">Deskripsi & Catatan Aroma</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}" {{ $errors->has('description') ? 'aria-describedby="description-error"' : '' }}>{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback small" id="description-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="image" class="form-label small fw-semibold">Ganti Foto Produk (Opsional)</label>
                        @if($product->image_path)
                            <div class="mb-2">
                                <img src="{{ str_starts_with($product->image_path, 'http') ? $product->image_path : asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="owner-product-current-image">
                                <span class="small text-muted ms-2">Foto saat ini</span>
                            </div>
                        @endif
                        <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/jpeg,image/png,image/webp" aria-invalid="{{ $errors->has('image') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('image') ? 'image-help image-error' : 'image-help' }}">
                        <div class="form-text small" id="image-help">Biarkan kosong jika tidak ingin mengganti foto produk.</div>
                        @error('image')
                            <div class="invalid-feedback small" id="image-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <span class="form-label small d-block mb-1">Status Ketersediaan</span>
                        <div class="form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="is_active">Tampilkan di katalog pre-order</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <a href="{{ route('owner.products.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-dark px-4">
                        <i class="bi bi-save me-1" aria-hidden="true"></i> Perbarui Produk
                    </button>
                </div>
            </form>
</div>
</div>
@endsection
