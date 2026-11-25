@extends('layouts.app')

@section('title', 'Keranjang & Checkout Pesanan')

@section('content')
<div class="container py-5 customer-checkout-page">
    <nav aria-label="Breadcrumb" class="customer-checkout-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
            <li class="breadcrumb-item"><a href="{{ route('catalog') }}">Katalog Parfum</a></li>
            <li class="breadcrumb-item active" aria-current="page">Keranjang</li>
        </ol>
    </nav>

    @auth
        @if(auth()->user()->role === 'customer')
            <x-customer-portal-nav />
        @endif
    @endauth

    <header class="customer-checkout-header">
        <div>
            <p class="home-eyebrow">Pemesanan</p>
            <h1>Keranjang parfum</h1>
            <p>Tinjau ukuran dan jumlah, lalu lengkapi tujuan pengiriman sebelum membuat pesanan.</p>
        </div>
        <a class="customer-checkout-back" href="{{ route('catalog') }}">Lanjut pilih parfum <span aria-hidden="true">→</span></a>
    </header>

    @if($activePo && $activePo->isOrderableNow())
        <section class="customer-checkout-period customer-checkout-period-open" role="status" aria-label="Periode pre-order dibuka">
            <span class="customer-checkout-period-mark" aria-hidden="true">✓</span>
            <div>
                <p class="home-eyebrow">Periode pre-order · {{ $activePo->name }}</p>
                <p>Checkout tersedia sampai <strong>{{ $activePo->end_date->format('d F Y') }}</strong> (akhir hari WIB). Status dan tanggal periode sama-sama harus mengizinkan pemesanan.</p>
            </div>
            <x-status-badge status="Dibuka" />
        </section>
    @else
        <section class="customer-checkout-period" role="status" aria-label="Periode pre-order ditutup">
            <span class="customer-checkout-period-mark" aria-hidden="true">—</span>
            <div>
                <p class="home-eyebrow">Periode pre-order</p>
                <p>Checkout belum tersedia. Pemesanan dapat dibuat saat status periode dibuka dan tanggal hari ini masih berada dalam rentang PO menurut waktu WIB.</p>
            </div>
            <x-status-badge status="Ditutup" />
        </section>
    @endif

    @if(empty($cart))
        <section class="customer-cart-empty">
            <p class="home-eyebrow">Belum ada pilihan</p>
            <h2>Keranjang masih kosong</h2>
            <p>Jelajahi katalog untuk memilih parfum dan ukuran yang ingin dipesan.</p>
            <a href="{{ route('catalog') }}" class="btn btn-mocha">Lihat katalog parfum <span aria-hidden="true">→</span></a>
        </section>
    @else
        <div class="customer-checkout-layout">
            <section class="customer-cart-panel" aria-labelledby="cart-items-heading">
                <header class="customer-cart-panel-header">
                    <div>
                        <p class="home-eyebrow">Pilihan Anda</p>
                        <h2 id="cart-items-heading">Item keranjang</h2>
                    </div>
                    <span>{{ count($cart) }} varian</span>
                </header>

                <div class="customer-cart-items">
                    @foreach($cart as $key => $item)
                        <article class="customer-cart-item" data-unit-price="{{ (int) $item['price'] }}">
                            <div class="customer-cart-item-image" aria-hidden="true">
                                @if(!empty($item['image_path']))
                                    <img src="{{ str_starts_with($item['image_path'], 'http') ? $item['image_path'] : asset('storage/' . $item['image_path']) }}" alt="" loading="lazy">
                                @else
                                    <span>{{ strtoupper(mb_substr($item['name'], 0, 1)) }}</span>
                                @endif
                            </div>
                            <div class="customer-cart-item-main">
                                <p class="customer-cart-brand">{{ $item['brand'] ?? 'Uyuy Jastip' }}</p>
                                <h3>{{ $item['name'] }}</h3>
                                <p class="customer-cart-size">Ukuran {{ $item['size_ml'] }} ml</p>
                                <div class="customer-cart-item-actions">
                                    <form action="{{ route('customer.cart.update', $key) }}" method="POST" class="customer-cart-quantity-form customer-cart-stepper-form">
                                        @csrf
                                        @method('PUT')
                                        <label for="quantity-{{ $loop->index }}">Jumlah</label>
                                        <div class="customer-cart-stepper">
                                            <button type="button" class="customer-cart-stepper-btn" data-cart-step="minus" {{ $item['quantity'] <= 1 ? 'disabled' : '' }} aria-label="Kurangi jumlah {{ $item['name'] }}">−</button>
                                            <input id="quantity-{{ $loop->index }}" class="customer-cart-stepper-input" type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" max="50" inputmode="numeric" aria-label="Jumlah {{ $item['name'] }} ukuran {{ $item['size_ml'] }} ml">
                                            <button type="button" class="customer-cart-stepper-btn" data-cart-step="plus" {{ $item['quantity'] >= 50 ? 'disabled' : '' }} aria-label="Tambah jumlah {{ $item['name'] }}">+</button>
                                        </div>
                                        <noscript><button type="submit" class="customer-cart-update">Perbarui</button></noscript>
                                    </form>
                                    <form action="{{ route('customer.cart.remove', $key) }}" method="POST" data-confirm="Hapus varian ini dari keranjang?">
                                        @csrf
                                        <button type="submit" class="customer-cart-remove"><span aria-hidden="true">×</span> Hapus</button>
                                    </form>
                                </div>
                            </div>
                            <div class="customer-cart-item-price">
                                <span>Rp {{ number_format($item['price'], 0, ',', '.') }} / botol</span>
                                <strong data-cart-item-subtotal>Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</strong>
                            </div>
                        </article>
                    @endforeach
                </div>

                <footer class="customer-cart-total">
                    <span>Subtotal parfum</span>
                    <strong data-cart-grand-total>Rp {{ number_format($total, 0, ',', '.') }}</strong>
                </footer>
            </section>

            <form action="{{ route('customer.checkout.post') }}" method="POST" class="customer-checkout-form">
                @csrf
                <input type="hidden" name="checkout_token" value="{{ $checkoutToken }}">

                <section class="customer-checkout-form-panel" aria-labelledby="shipping-heading">
                    <header>
                        <p class="home-eyebrow">Data tujuan</p>
                        <h2 id="shipping-heading">Informasi pengiriman</h2>
                        <p>Pastikan nomor aktif dan alamat lengkap agar admin dapat memproses pengiriman.</p>
                    </header>

                    <div class="customer-checkout-field">
                        <label for="recipient_name">Nama penerima <span aria-hidden="true">*</span></label>
                        <input id="recipient_name" type="text" name="recipient_name" value="{{ old('recipient_name', auth()->user()->name) }}" autocomplete="name" required aria-invalid="{{ $errors->has('recipient_name') ? 'true' : 'false' }}" @if($errors->has('recipient_name')) aria-describedby="recipient-name-error" @endif>
                        @error('recipient_name')<p class="customer-checkout-error" id="recipient-name-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="customer-checkout-field">
                        <label for="recipient_phone">Nomor WhatsApp <span aria-hidden="true">*</span></label>
                        <input id="recipient_phone" type="tel" name="recipient_phone" value="{{ old('recipient_phone', $customer?->phone_number ?? '') }}" autocomplete="tel" required aria-invalid="{{ $errors->has('recipient_phone') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('recipient_phone') ? 'recipient-phone-help recipient-phone-error' : 'recipient-phone-help' }}">
                        <small id="recipient-phone-help">Gunakan nomor yang dapat dihubungi untuk konfirmasi pesanan.</small>
                        @error('recipient_phone')<p class="customer-checkout-error" id="recipient-phone-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="customer-checkout-field">
                        <label for="shipping_address">Alamat lengkap <span aria-hidden="true">*</span></label>
                        <textarea id="shipping_address" name="shipping_address" rows="4" autocomplete="street-address" placeholder="Jalan, nomor rumah, RT/RW, kelurahan, kecamatan, dan kota" required aria-invalid="{{ $errors->has('shipping_address') ? 'true' : 'false' }}" @if($errors->has('shipping_address')) aria-describedby="shipping-address-error" @endif>{{ old('shipping_address', trim(($customer?->default_address ?? '') . ($customer?->city ? ', ' . $customer->city : ''), ', ')) }}</textarea>
                        @error('shipping_address')<p class="customer-checkout-error" id="shipping-address-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="customer-checkout-field">
                        <label for="shipping_postal_code">Kode pos <span class="customer-checkout-optional">Opsional</span></label>
                        <input id="shipping_postal_code" type="text" name="shipping_postal_code" value="{{ old('shipping_postal_code', $customer?->postal_code ?? '') }}" inputmode="numeric" maxlength="5" pattern="[0-9]{5}" autocomplete="postal-code" aria-invalid="{{ $errors->has('shipping_postal_code') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('shipping_postal_code') ? 'postal-code-help postal-code-error' : 'postal-code-help' }}">
                        <small id="postal-code-help">Masukkan 5 digit kode pos jika tersedia.</small>
                        @error('shipping_postal_code')<p class="customer-checkout-error" id="postal-code-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="customer-checkout-field">
                        <label for="customer_notes">Catatan <span class="customer-checkout-optional">Opsional</span></label>
                        <textarea id="customer_notes" name="customer_notes" rows="3" placeholder="Catatan untuk pesanan atau pengiriman" aria-invalid="{{ $errors->has('customer_notes') ? 'true' : 'false' }}" @if($errors->has('customer_notes')) aria-describedby="customer-notes-error" @endif>{{ old('customer_notes') }}</textarea>
                        @error('customer_notes')<p class="customer-checkout-error" id="customer-notes-error">{{ $message }}</p>@enderror
                    </div>
                </section>

                <section class="customer-checkout-summary" aria-label="Ringkasan tagihan">
                    <div><span>Subtotal (<span data-cart-total-bottles>{{ array_sum(array_column($cart, 'quantity')) }}</span> botol)</span><strong data-cart-grand-total>Rp {{ number_format($total, 0, ',', '.') }}</strong></div>
                    <div><span>Ongkos kirim</span><span>Dihitung dan dikonfirmasi terpisah</span></div>
                    <p>Jumlah di atas adalah subtotal parfum. Ongkos kirim belum termasuk dan akan dikonfirmasi saat pemrosesan pesanan.</p>
                </section>

                @if($activePo && $activePo->isOrderableNow())
                    <button type="submit" id="checkoutSubmit" class="customer-checkout-submit">
                        Buat pesanan <span aria-hidden="true">→</span>
                    </button>
                    <p class="customer-checkout-submit-note">Setelah dibuat, detail pesanan dan instruksi pembayaran tersedia di halaman pesanan Anda.</p>
                @else
                    <button type="button" class="customer-checkout-submit" disabled>Checkout belum tersedia</button>
                    <p class="customer-checkout-submit-note">Tunggu sampai periode pre-order aktif dan tanggalnya berada dalam rentang pemesanan.</p>
                @endif
            </form>
        </div>
    @endif
</div>
@endsection
