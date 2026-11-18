@extends('layouts.owner')

@section('title', 'Tambah Produk Parfum Baru')

@section('content')
<div class="owner-form-page owner-product-create">
<nav class="owner-form-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('owner.dashboard') }}">Dashboard</a>
    <span aria-hidden="true">/</span>
    <a href="{{ route('owner.products.index') }}">Produk</a>
    <span aria-hidden="true">/</span>
    <span>Tambah produk</span>
</nav>

<header class="workspace-page-header">
    <div>
        <p class="owner-recap-eyebrow">OWNER / MASTER PRODUK</p>
        <h1>Tambah produk parfum</h1>
    </div>
    <a href="{{ route('owner.products.index') }}" class="workspace-back-link">
        <i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke daftar
    </a>
</header>

<div class="owner-product-edit-panel p-3 p-md-4">
            <form action="{{ route('owner.products.store') }}" method="POST" enctype="multipart/form-data" class="owner-product-form">
                @csrf

                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="name" class="form-label small fw-semibold">Nama Varian Parfum <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="Nama parfum pada katalog" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" @if($errors->has('name')) aria-describedby="name-error" @endif>
                        @error('name')
                            <div class="invalid-feedback small" id="name-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="brand" class="form-label small fw-semibold">Vendor / Merek</label>
                        <input type="text" class="form-control bg-light" id="brand" name="brand" value="In Parfume" readonly>
                    </div>

                    <div class="col-md-4">
                        <label for="category" class="form-label small fw-semibold">Kategori Aroma</label>
                        <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" aria-invalid="{{ $errors->has('category') ? 'true' : 'false' }}" @if($errors->has('category')) aria-describedby="category-error" @endif>
                            @foreach(['Amber Floral', 'Woody Aromatic', 'Fresh Citrus', 'Sweet Gourmand', 'Fruity Floral', 'Aquatic Fresh', 'Floral Lavender', 'Sweet Vanilla', 'Unisex'] as $categoryOption)
                                <option value="{{ $categoryOption }}" {{ old('category') === $categoryOption ? 'selected' : '' }}>{{ $categoryOption }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <div class="invalid-feedback small" id="category-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <fieldset class="col-12 border-0 p-0" @if($errors->has('variants')) aria-describedby="variants-error" @endif>
                        <legend class="form-label small fw-semibold">Harga dan Modal per Ukuran</legend>
                        <div class="row g-3">
                            @foreach([35, 60, 100] as $size)
                                <div class="col-md-4">
                                    <div class="owner-product-variant h-100" role="group" aria-label="Harga {{ $size }} ml">
                                        <div class="fw-bold mb-2">{{ $size }} ml</div>
                                        <label for="price_{{ $size }}" class="form-label small">Harga jual (Rp)</label>
                                        <input type="number" class="form-control mb-2 @error('variants.'.$size.'.price') is-invalid @enderror" id="price_{{ $size }}" name="variants[{{ $size }}][price]" value="{{ old('variants.'.$size.'.price') }}" min="1" step="1000" placeholder="Kosongkan jika tidak tersedia" aria-invalid="{{ $errors->has('variants.'.$size.'.price') ? 'true' : 'false' }}" @if($errors->has('variants.'.$size.'.price')) aria-describedby="price-{{ $size }}-error" @endif>
                                        @error('variants.'.$size.'.price')<div class="invalid-feedback" id="price-{{ $size }}-error">{{ $message }}</div>@enderror
                                        <label for="cost_{{ $size }}" class="form-label small">Harga modal (Rp, opsional)</label>
                                        <input type="number" class="form-control @error('variants.'.$size.'.cost_price') is-invalid @enderror" id="cost_{{ $size }}" name="variants[{{ $size }}][cost_price]" value="{{ old('variants.'.$size.'.cost_price') }}" min="0" step="1000" aria-invalid="{{ $errors->has('variants.'.$size.'.cost_price') ? 'true' : 'false' }}" @if($errors->has('variants.'.$size.'.cost_price')) aria-describedby="cost-{{ $size }}-error" @endif>
                                        @error('variants.'.$size.'.cost_price')<div class="invalid-feedback" id="cost-{{ $size }}-error">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('variants')<div class="text-danger small mt-2" id="variants-error">{{ $message }}</div>@enderror
                    </fieldset>

                    <div class="col-12">
                        <label for="description" class="form-label small fw-semibold">Deskripsi & Catatan Aroma (Notes)</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Jelaskan top notes, middle notes, base notes, dan karakter aroma parfum..." aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}" @if($errors->has('description')) aria-describedby="description-error" @endif>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback small" id="description-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="image" class="form-label small fw-semibold">Foto Produk Parfum</label>
                        <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/jpeg,image/png,image/webp" aria-invalid="{{ $errors->has('image') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('image') ? 'image-help image-error' : 'image-help' }}">
                        <div class="form-text small" id="image-help">Mendukung format JPG, PNG, atau WEBP. Maksimal 2MB.</div>
                        @error('image')
                            <div class="invalid-feedback small" id="image-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <a href="{{ route('owner.products.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-dark px-4">
                        <i class="bi bi-save me-1" aria-hidden="true"></i> Simpan Produk
                    </button>
                </div>
            </form>
</div>
</div>
@endsection

