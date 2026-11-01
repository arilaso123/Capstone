@extends('layouts.app')

@section('title', $product->name . ' — Uyuy Jastip')

@section('content')
<!-- Breadcrumbs -->
<div class="py-3 border-bottom" style="background-color: var(--color-warm-ivory);">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-secondary">Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ route('catalog') }}" class="text-secondary">Katalog Parfum</a></li>
                <li class="breadcrumb-item active text-dark" aria-current="page">{{ $product->name }}</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    <div class="row g-5">
        <!-- Product Image Preview -->
        <div class="col-lg-5">
            <div class="card card-uyuy p-3 text-center">
                <div class="position-relative overflow-hidden rounded-3 mb-3" style="background-color: var(--color-warm-ivory); height: 420px;">
                    @if($product->image_path)
                        @php
                            $imgSrc = str_starts_with($product->image_path, 'http')
                                ? $product->image_path
                                : asset('storage/' . $product->image_path);
                        @endphp
                        <img src="{{ $imgSrc }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover rounded-3">
                    @else
                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                            <i class="bi bi-droplet fs-1"></i>
                        </div>
                    @endif
                    <span class="badge position-absolute top-0 end-0 m-3 px-3 py-2 fw-semibold" style="background-color: var(--color-champagne); color: var(--color-charcoal);">
                        Vendor: {{ $product->brand ?? 'In Parfume' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Product Specs & Order Action -->
        <div class="col-lg-7">
            <div class="ps-lg-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                    @if($activePo && $activePo->isOpen())
                        <span class="badge-uyuy badge-po-open"><i class="bi bi-unlock-fill"></i> {{ $activePo->name }} Dibuka</span>
                    @else
                        <span class="badge bg-secondary"><i class="bi bi-lock-fill"></i> PO Sedang Ditutup</span>
                    @endif
                    @if($product->category)
                        <span class="text-muted small">&bull; Kategori: <strong>{{ $product->category }}</strong></span>
                    @endif
                </div>

                <h1 class="display-6 fw-bold mb-2">{{ $product->name }}</h1>
                <p class="text-muted small mb-3">Diracik secara presisi oleh {{ $product->brand ?? 'In Parfume Bandung' }}</p>

                <div class="p-3 rounded-3 mb-4" style="background: #FFFFFF; border: 1px solid var(--color-soft-gray);">
                    <div class="small text-muted mb-1">Harga Pre-Order (Jastip):</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-2 fw-bold text-dark" id="displayPrice">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                        <span class="text-muted small" id="displayUnit">/ botol (Ukuran {{ $product->size_ml }} ml)</span>
                    </div>
                </div>

                <!-- Form Pemesanan / Keranjang -->
                @if($activePo && $activePo->isOpen() && $product->is_active)
                    <form action="{{ route('customer.cart.add') }}" method="POST" class="mb-4">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        <!-- Size Selection -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Pilih Ukuran Botol:</label>
                            <div class="d-flex gap-3">
                                <div class="form-check custom-option">
                                    <input class="form-check-input" type="radio" name="size_ml" id="size35" value="35" data-price="{{ (int)$product->getPriceForSize(35) }}">
                                    <label class="form-check-label px-3 py-2 border rounded-3 d-block cursor-pointer" for="size35">
                                        <div class="fw-bold">35 ml</div>
                                        <div class="small text-muted">Rp {{ number_format($product->getPriceForSize(35), 0, ',', '.') }}</div>
                                    </label>
                                </div>
                                <div class="form-check custom-option">
                                    <input class="form-check-input" type="radio" name="size_ml" id="size60" value="60" data-price="{{ (int)$product->price }}" checked>
                                    <label class="form-check-label px-3 py-2 border rounded-3 d-block cursor-pointer border-mocha" for="size60" style="border-color: var(--color-mocha) !important; background: var(--color-warm-ivory);">
                                        <div class="fw-bold text-mocha">60 ml (Favorit)</div>
                                        <div class="small text-muted">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                                    </label>
                                </div>
                                <div class="form-check custom-option">
                                    <input class="form-check-input" type="radio" name="size_ml" id="size100" value="100" data-price="{{ (int)$product->getPriceForSize(100) }}">
                                    <label class="form-check-label px-3 py-2 border rounded-3 d-block cursor-pointer" for="size100">
                                        <div class="fw-bold">100 ml</div>
                                        <div class="small text-muted">Rp {{ number_format($product->getPriceForSize(100), 0, ',', '.') }}</div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Quantity Input -->
                        <div class="mb-4" style="max-width: 180px;">
                            <label class="form-label fw-bold small text-uppercase">Jumlah Botol:</label>
                            <div class="input-group">
                                <button class="btn btn-outline-secondary" type="button" id="btnMinus"><i class="bi bi-dash"></i></button>
                                <input type="number" class="form-control text-center" name="quantity" id="qtyInput" value="1" min="1" max="20">
                                <button class="btn btn-outline-secondary" type="button" id="btnPlus"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>

                        <!-- CTA Actions -->
                        <div class="d-flex flex-wrap gap-3">
                            <button type="submit" class="btn btn-mocha px-4 py-2 flex-grow-1 d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-bag-plus"></i> Masukkan ke Keranjang PO
                            </button>
                            <a href="{{ route('customer.cart') }}" class="btn btn-outline-mocha px-4 py-2">
                                <i class="bi bi-cart"></i> Lihat Keranjang
                            </a>
                        </div>
                    </form>
                @elseif(!$product->is_active)
                    <div class="alert alert-secondary p-3 mb-4">
                        <i class="bi bi-exclamation-circle me-1"></i> Produk ini sedang tidak aktif dalam katalog pre-order saat ini.
                    </div>
                @else
                    <div class="alert alert-warning p-3 mb-4">
                        <i class="bi bi-lock me-1"></i> Periode Pre-Order saat ini sedang <strong>DITUTUP</strong>. Pemesanan tidak dapat dilakukan sampai Owner membuka batch berikutnya.
                    </div>
                @endif

                <!-- Description / Scent Character -->
                <div class="card card-uyuy p-4 mt-4">
                    <h5 class="fw-bold mb-3 font-heading">Karakter Aroma & Komposisi</h5>
                    <p class="text-secondary small mb-0">
                        {{ $product->description ?? 'Aroma parfum berkualitas tinggi dari vendor In Parfume Bandung dengan ketahanan wangi yang memuaskan.' }}
                    </p>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const minus = document.getElementById('btnMinus');
        const plus = document.getElementById('btnPlus');
        const qty = document.getElementById('qtyInput');
        const displayPrice = document.getElementById('displayPrice');
        const displayUnit = document.getElementById('displayUnit');
        const radios = document.querySelectorAll('input[name="size_ml"]');

        function updatePrice() {
            const selected = document.querySelector('input[name="size_ml"]:checked');
            if (selected) {
                const basePrice = parseInt(selected.getAttribute('data-price'));
                const size = selected.value;
                const quantity = parseInt(qty.value) || 1;
                const total = basePrice * quantity;
                displayPrice.textContent = 'Rp ' + total.toLocaleString('id-ID');
                if (displayUnit) {
                    displayUnit.textContent = '/ botol (Ukuran ' + size + ' ml)';
                }
            }
        }

        if (minus && plus && qty) {
            minus.addEventListener('click', function() {
                if (parseInt(qty.value) > 1) {
                    qty.value = parseInt(qty.value) - 1;
                    updatePrice();
                }
            });

            plus.addEventListener('click', function() {
                qty.value = parseInt(qty.value) + 1;
                updatePrice();
            });

            radios.forEach(r => r.addEventListener('change', updatePrice));
        }
    });
</script>
@endpush
