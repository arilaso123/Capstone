@extends('layouts.app')

@section('title', $product->name . ' — Uyuy Jastip')

@section('content')
<!-- Breadcrumbs -->
<div class="py-3 border-bottom" style="background: radial-gradient(circle at 10% 20%, #FDFBF7 0%, #F4ECE3 90%); border-color: rgba(216, 197, 174, 0.4) !important;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none" style="color: var(--color-mocha);">Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ route('catalog') }}" class="text-decoration-none" style="color: var(--color-mocha);">Katalog Parfum</a></li>
                <li class="breadcrumb-item active text-secondary" aria-current="page">{{ $product->name }}</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    <div class="row g-5">
        <!-- Product Image Preview -->
        <div class="col-lg-5">
            <x-card class="p-3 border-0 shadow sticky-lg-top" style="top: 90px; z-index: 10;">
                <div class="position-relative overflow-hidden rounded-4 text-center" style="background-color: #FAF8F5; height: 440px;">
                    @if($product->image_path)
                        @php
                            $imgSrc = str_starts_with($product->image_path, 'http')
                                ? $product->image_path
                                : asset('storage/' . $product->image_path);
                        @endphp
                        <img src="{{ $imgSrc }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover rounded-4" style="transition: transform 0.4s ease;">
                    @else
                        <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center" style="background-color: var(--color-champagne-light); color: var(--color-mocha);">
                            <i class="bi bi-droplet-half fs-1 mb-2"></i>
                            <span class="small fw-semibold">In Parfume Bandung</span>
                        </div>
                    @endif

                    <!-- Official Vendor Badge -->
                    <span class="badge rounded-pill position-absolute top-0 end-0 m-3 px-3 py-2 fw-semibold shadow-sm" style="background-color: rgba(255, 255, 255, 0.94); color: var(--color-charcoal); border: 1px solid rgba(216, 197, 174, 0.6); font-size: 0.75rem;">
                        <i class="bi bi-patch-check-fill me-1" style="color: var(--color-mocha);"></i> {{ $product->brand ?? 'In Parfume' }}
                    </span>
                </div>

                <!-- Assurance Micro-Banner -->
                <div class="d-flex justify-content-around text-center pt-3 mt-2 border-top" style="border-color: rgba(216, 197, 174, 0.3) !important;">
                    <div class="px-2">
                        <i class="bi bi-shield-check text-success fs-5 d-block mb-1"></i>
                        <span class="small text-muted" style="font-size: 0.75rem;">100% Original</span>
                    </div>
                    <div class="px-2">
                        <i class="bi bi-box2-heart fs-5 d-block mb-1" style="color: var(--color-mocha);"></i>
                        <span class="small text-muted" style="font-size: 0.75rem;">Extra Bubble Wrap</span>
                    </div>
                    <div class="px-2">
                        <i class="bi bi-truck fs-5 d-block mb-1" style="color: var(--color-charcoal);"></i>
                        <span class="small text-muted" style="font-size: 0.75rem;">Dikirim Rapi</span>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Product Specs & Order Action -->
        <div class="col-lg-7">
            <div class="ps-lg-2">
                <!-- Status & Category Header -->
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    @if($activePo && $activePo->isOpen())
                        <span class="badge-uyuy badge-po-open py-1 px-3 shadow-sm">
                            <i class="bi bi-broadcast"></i> PO {{ $activePo->name }} DIBUKA
                        </span>
                    @else
                        <span class="badge rounded-pill bg-secondary py-1 px-3">
                            <i class="bi bi-lock-fill me-1"></i> PO Sedang Ditutup
                        </span>
                    @endif

                    @if($product->category)
                        <span class="badge rounded-pill px-3 py-1" style="background: rgba(45,36,30,0.06); color: var(--color-charcoal); font-size: 0.78rem;">
                            Aroma: <strong>{{ $product->category }}</strong>
                        </span>
                    @endif
                </div>

                <h1 class="display-6 fw-bold font-heading mb-1" style="color: var(--color-charcoal); letter-spacing: -0.02em;">
                    {{ $product->name }}
                </h1>
                <p class="text-secondary small mb-4">
                    Formulasi aroma mewah terinspirasi desainer dunia, diracik secara presisi oleh vendor <strong>{{ $product->brand ?? 'In Parfume Bandung' }}</strong>.
                </p>

                <!-- Dynamic Price Card -->
                <div class="p-4 rounded-4 mb-4 shadow-sm" style="background: #FFFFFF; border: 1.5px solid rgba(216, 197, 174, 0.45);">
                    <div class="small fw-semibold text-secondary mb-1" style="font-size: 0.78rem;">Harga Titip Pre-Order:</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-2 fw-bold font-heading text-dark" id="displayPrice">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                        <span class="text-secondary small" id="displayUnit">/ botol (Ukuran {{ $product->size_ml }} ml)</span>
                    </div>
                    <div class="small text-muted mt-1" style="font-size: 0.78rem;">
                        <i class="bi bi-info-circle me-1"></i> Harga sudah termasuk biaya jasa titip & pengemasan aman.
                    </div>
                </div>

                <!-- Form Pemesanan / Keranjang -->
                @if($activePo && $activePo->isOpen() && $product->is_active)
                    <form action="{{ route('customer.cart.add') }}" method="POST" class="mb-4">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        <!-- Size Selection Cards -->
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase text-secondary mb-2" style="letter-spacing: 0.5px;">
                                Pilih Ukuran Botol:
                            </label>
                            <div class="row g-2">
                                <div class="col-4">
                                    <input class="btn-check" type="radio" name="size_ml" id="size35" value="35" data-price="{{ (int)$product->getPriceForSize(35) }}" autocomplete="off">
                                    <label class="btn btn-outline-light w-100 p-3 text-start rounded-4 h-100 border text-dark shadow-sm position-relative size-radio-card" for="size35" style="border-color: rgba(216, 197, 174, 0.5) !important;">
                                        <div class="fw-bold font-heading fs-6 mb-1">35 ml</div>
                                        <div class="small text-muted" style="font-size: 0.78rem;">Travel Size</div>
                                        <div class="fw-semibold text-dark mt-2" style="font-size: 0.85rem;">Rp {{ number_format($product->getPriceForSize(35), 0, ',', '.') }}</div>
                                    </label>
                                </div>
                                <div class="col-4">
                                    <input class="btn-check" type="radio" name="size_ml" id="size60" value="60" data-price="{{ (int)$product->price }}" checked autocomplete="off">
                                    <label class="btn btn-outline-light w-100 p-3 text-start rounded-4 h-100 border text-dark shadow-sm position-relative size-radio-card" for="size60" style="border-color: rgba(216, 197, 174, 0.5) !important;">
                                        <span class="badge rounded-pill position-absolute top-0 end-0 m-2 px-2 py-1" style="background: var(--color-champagne); color: var(--color-charcoal); font-size: 0.65rem;">Favorit</span>
                                        <div class="fw-bold font-heading fs-6 mb-1">60 ml</div>
                                        <div class="small text-muted" style="font-size: 0.78rem;">Reguler</div>
                                        <div class="fw-semibold text-dark mt-2" style="font-size: 0.85rem;">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                                    </label>
                                </div>
                                <div class="col-4">
                                    <input class="btn-check" type="radio" name="size_ml" id="size100" value="100" data-price="{{ (int)$product->getPriceForSize(100) }}" autocomplete="off">
                                    <label class="btn btn-outline-light w-100 p-3 text-start rounded-4 h-100 border text-dark shadow-sm position-relative size-radio-card" for="size100" style="border-color: rgba(216, 197, 174, 0.5) !important;">
                                        <div class="fw-bold font-heading fs-6 mb-1">100 ml</div>
                                        <div class="small text-muted" style="font-size: 0.78rem;">Puas / Besar</div>
                                        <div class="fw-semibold text-dark mt-2" style="font-size: 0.85rem;">Rp {{ number_format($product->getPriceForSize(100), 0, ',', '.') }}</div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Quantity Selector -->
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase text-secondary mb-2" style="letter-spacing: 0.5px;">
                                Jumlah Botol:
                            </label>
                            <div class="d-inline-flex align-items-center rounded-pill p-1 shadow-sm" style="background: #FAF8F5; border: 1.5px solid rgba(216, 197, 174, 0.5);">
                                <button class="btn btn-light rounded-circle d-flex align-items-center justify-content-center shadow-none p-0" type="button" id="btnMinus" style="width: 38px; height: 38px; border: 1px solid rgba(216, 197, 174, 0.4);">
                                    <i class="bi bi-dash fs-5"></i>
                                </button>
                                <input type="number" class="form-control border-0 bg-transparent text-center fw-bold fs-6 shadow-none" name="quantity" id="qtyInput" value="1" min="1" max="20" style="width: 60px;">
                                <button class="btn btn-light rounded-circle d-flex align-items-center justify-content-center shadow-none p-0" type="button" id="btnPlus" style="width: 38px; height: 38px; border: 1px solid rgba(216, 197, 174, 0.4);">
                                    <i class="bi bi-plus fs-5"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex flex-wrap gap-3">
                            <x-button type="submit" variant="mocha" class="py-3 px-4 flex-grow-1 shadow-sm d-flex align-items-center justify-content-center gap-2" icon="bi bi-bag-plus">
                                Masukkan ke Keranjang PO
                            </x-button>
                            <a href="{{ route('customer.cart') }}" class="btn btn-outline-mocha py-3 px-4 d-inline-flex align-items-center gap-2">
                                <i class="bi bi-bag"></i> Keranjang
                            </a>
                        </div>
                    </form>
                @elseif(!$product->is_active)
                    <div class="alert p-3 mb-4 rounded-4 shadow-sm border-0" style="background: #F2EFEA; border-left: 4px solid #8A8177 !important;">
                        <i class="bi bi-exclamation-circle me-1"></i> Varian parfum ini sedang tidak aktif dalam siklus pre-order saat ini.
                    </div>
                @else
                    <div class="alert p-4 mb-4 rounded-4 shadow-sm border-0" style="background: rgba(244, 236, 227, 0.8); border-left: 4px solid var(--color-mocha) !important;">
                        <h6 class="fw-bold mb-1 text-dark"><i class="bi bi-lock me-1"></i> Periode Pre-Order Sedang Ditutup</h6>
                        <p class="small text-secondary mb-0">Pemesanan tidak dapat dilakukan saat ini. Silakan pantau pengumuman jadwal pre-order berikutnya dari Owner.</p>
                        <a href="{{ route('po.info') }}" class="btn btn-sm btn-outline-mocha mt-3">Lihat Jadwal PO &rarr;</a>
                    </div>
                @endif

                <!-- Scent Character & Notes Description -->
                <x-card class="p-4 mt-4 border-0 shadow">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-flower1 fs-4" style="color: var(--color-mocha);"></i>
                        <h5 class="fw-bold font-heading mb-0">Karakter Wewangian</h5>
                    </div>
                    <p class="text-secondary small mb-0" style="line-height: 1.7;">
                        {{ $product->description ?? 'Wewangian berkualitas tinggi racikan toko In Parfume Bandung yang memiliki komposisi aroma memikat, proyeksi lembut namun tegas, serta daya tahan wangi yang awet seharian.' }}
                    </p>
                </x-card>

            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .btn-check:checked + .size-radio-card {
        background-color: #FAF8F5 !important;
        border-color: var(--color-mocha) !important;
        box-shadow: 0 0 0 2px var(--color-mocha), 0 6px 16px rgba(139, 107, 80, 0.15) !important;
    }
    .size-radio-card {
        cursor: pointer;
        transition: all 0.25s ease;
    }
    .size-radio-card:hover {
        transform: translateY(-2px);
        border-color: var(--color-mocha) !important;
    }
</style>
@endpush

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
