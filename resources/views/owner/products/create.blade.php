@extends('layouts.owner')

@section('title', 'Tambah Produk Parfum Baru')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('owner.products.index') }}">Produk</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tambah Produk</li>
            </ol>
        </nav>
        <h2 class="h4 fw-bold mb-0 font-heading">Tambah Varian Parfum Baru</h2>
    </div>
    <a href="{{ route('owner.products.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card card-uyuy p-4">
            <form action="{{ route('owner.products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="name" class="form-label small fw-semibold">Nama Varian Parfum <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="Contoh: Baccarat Rouge 540 Inspired">
                        @error('name')
                            <div class="invalid-feedback small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="brand" class="form-label small fw-semibold">Vendor / Merek</label>
                        <input type="text" class="form-control bg-light" id="brand" name="brand" value="In Parfume" readonly>
                    </div>

                    <div class="col-md-4">
                        <label for="category" class="form-label small fw-semibold">Kategori Aroma</label>
                        <select class="form-select @error('category') is-invalid @enderror" id="category" name="category">
                            <option value="Amber Floral">Amber Floral</option>
                            <option value="Woody Aromatic">Woody Aromatic</option>
                            <option value="Fresh Citrus">Fresh Citrus</option>
                            <option value="Sweet Gourmand">Sweet Gourmand</option>
                            <option value="Fruity Floral">Fruity Floral</option>
                            <option value="Aquatic Fresh">Aquatic Fresh</option>
                            <option value="Unisex">Unisex</option>
                        </select>
                        @error('category')
                            <div class="invalid-feedback small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="size_ml" class="form-label small fw-semibold">Ukuran Botol (ml) <span class="text-danger">*</span></label>
                        <select class="form-select @error('size_ml') is-invalid @enderror" id="size_ml" name="size_ml" required>
                            <option value="35">35 ml</option>
                            <option value="60" selected>60 ml (Ukuran Standar)</option>
                            <option value="100">100 ml</option>
                        </select>
                        @error('size_ml')
                            <div class="invalid-feedback small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="price" class="form-label small fw-semibold">Harga Pre-Order (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', 95000) }}" required step="1000">
                        @error('price')
                            <div class="invalid-feedback small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="description" class="form-label small fw-semibold">Deskripsi & Catatan Aroma (Notes)</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Jelaskan top notes, middle notes, base notes, dan karakter aroma parfum...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="image" class="form-label small fw-semibold">Foto Produk Parfum</label>
                        <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                        <div class="form-text small">Mendukung format JPG, PNG, atau WEBP. Maksimal 2MB.</div>
                        @error('image')
                            <div class="invalid-feedback small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <a href="{{ route('owner.products.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-mocha px-4">
                        <i class="bi bi-save me-1"></i> Simpan Produk
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

