@extends('layouts.owner')

@section('title', 'Ubah Data Produk #' . $product->id)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('owner.products.index') }}">Produk</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Produk #{{ $product->id }}</li>
            </ol>
        </nav>
        <h2 class="h4 fw-bold mb-0 font-heading">Ubah Informasi Produk Parfum</h2>
    </div>
    <a href="{{ route('owner.products.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card card-uyuy p-4">
            <form action="{{ route('owner.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="name" class="form-label small fw-semibold">Nama Varian Parfum <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="brand" class="form-label small fw-semibold">Vendor / Merek</label>
                        <input type="text" class="form-control bg-light" id="brand" name="brand" value="{{ old('brand', $product->brand ?? 'In Parfume') }}" readonly>
                    </div>

                    <div class="col-md-4">
                        <label for="category" class="form-label small fw-semibold">Kategori Aroma</label>
                        <select class="form-select @error('category') is-invalid @enderror" id="category" name="category">
                            @php
                                $categories = ['Amber Floral', 'Woody Aromatic', 'Fresh Citrus', 'Sweet Gourmand', 'Fruity Floral', 'Aquatic Fresh', 'Floral Lavender', 'Sweet Vanilla', 'Unisex'];
                            @endphp
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ old('category', $product->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <div class="invalid-feedback small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="size_ml" class="form-label small fw-semibold">Ukuran Botol (ml) <span class="text-danger">*</span></label>
                        <select class="form-select @error('size_ml') is-invalid @enderror" id="size_ml" name="size_ml" required>
                            <option value="35" {{ old('size_ml', $product->size_ml) == 35 ? 'selected' : '' }}>35 ml</option>
                            <option value="60" {{ old('size_ml', $product->size_ml) == 60 ? 'selected' : '' }}>60 ml</option>
                            <option value="100" {{ old('size_ml', $product->size_ml) == 100 ? 'selected' : '' }}>100 ml</option>
                        </select>
                        @error('size_ml')
                            <div class="invalid-feedback small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="price" class="form-label small fw-semibold">Harga Pre-Order (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', (int)$product->price) }}" required step="1000">
                        @error('price')
                            <div class="invalid-feedback small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="description" class="form-label small fw-semibold">Deskripsi & Catatan Aroma</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="image" class="form-label small fw-semibold">Ganti Foto Produk (Opsional)</label>
                        @if($product->image_path)
                            <div class="mb-2">
                                @php
                                    $img = str_starts_with($product->image_path, 'http')
                                        ? $product->image_path
                                        : asset('storage/' . $product->image_path);
                                @endphp
                                <img src="{{ $img }}" alt="{{ $product->name }}" class="rounded border" style="max-height: 80px;">
                                <span class="small text-muted ms-2">Foto saat ini</span>
                            </div>
                        @endif
                        <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                        <div class="form-text small">Biarkan kosong jika tidak ingin mengganti foto produk.</div>
                        @error('image')
                            <div class="invalid-feedback small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold">Status Ketersediaan</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="is_active">Tampilkan di katalog pre-order</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <a href="{{ route('owner.products.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-mocha px-4">
                        <i class="bi bi-save me-1"></i> Perbarui Produk
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
