@extends('layouts.app')

@section('title', 'Keranjang & Checkout Pemesanan')

@section('content')
<!-- Header Banner -->
<div class="py-4 border-bottom" style="background: radial-gradient(circle at 10% 20%, #FDFBF7 0%, #F4ECE3 90%); border-color: rgba(216, 197, 174, 0.4) !important;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none" style="color: var(--color-mocha);">Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ route('catalog') }}" class="text-decoration-none" style="color: var(--color-mocha);">Katalog Parfum</a></li>
                <li class="breadcrumb-item active text-secondary" aria-current="page">Keranjang Pre-Order</li>
            </ol>
        </nav>
        <h1 class="h2 fw-bold font-heading mb-1" style="color: var(--color-charcoal); letter-spacing: -0.02em;">Keranjang & Checkout Pesanan</h1>
        <p class="text-secondary small mb-0">Tinjau item wewangian pilihan Anda dan lengkapi rincian alamat pengiriman.</p>
    </div>
</div>

<div class="container py-5">
    <!-- Active PO Period Status Banner -->
    @if($activePo && $activePo->isOpen())
        <div class="alert p-3 mb-4 rounded-4 d-flex align-items-center justify-content-between shadow-sm border-0" style="background: rgba(220, 252, 231, 0.7); border-left: 4px solid #16A34A !important;" role="alert">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: #FFFFFF; color: #16A34A; box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
                    <i class="bi bi-calendar-check-fill fs-5"></i>
                </div>
                <div>
                    <strong class="text-dark">Periode Pre-Order: {{ $activePo->name }} (DIBUKA)</strong>
                    <div class="small text-secondary">Pesanan Anda akan diikutsertakan pada siklus belanja batch ini (berakhir {{ $activePo->end_date->format('d M Y') }}).</div>
                </div>
            </div>
            <x-status-badge status="Dibuka" />
        </div>
    @else
        <div class="alert p-3 mb-4 rounded-4 d-flex align-items-center justify-content-between shadow-sm border-0" style="background: rgba(244, 236, 227, 0.7); border-left: 4px solid var(--color-mocha) !important;" role="alert">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: #FFFFFF; color: var(--color-mocha); box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
                    <i class="bi bi-lock-fill fs-5"></i>
                </div>
                <div>
                    <strong class="text-dark">Periode Pre-Order Sedang Ditutup</strong>
                    <div class="small text-secondary">Checkout pemesanan dinonaktifkan sementara hingga Owner membuka batch PO berikutnya.</div>
                </div>
            </div>
            <x-status-badge status="Ditutup" />
        </div>
    @endif

    @if(empty($cart))
        <!-- Empty Cart State -->
        <x-card class="p-5 text-center border-0 shadow">
            <div class="d-inline-flex p-4 rounded-circle mb-3" style="background-color: var(--color-champagne-light); color: var(--color-mocha);">
                <i class="bi bi-bag-x fs-1"></i>
            </div>
            <h4 class="fw-bold font-heading mb-2 text-dark">Keranjang Pre-Order Anda Masih Kosong</h4>
            <p class="text-secondary small mb-4 mx-auto" style="max-width: 460px;">Pilih wewangian parfum favorit Anda dari katalog In Parfume untuk mulai membuat pesanan titip.</p>
            <div>
                <a href="{{ route('catalog') }}" class="btn btn-mocha py-3 px-4 shadow-sm">
                    <i class="bi bi-grid me-2"></i> Jelajahi Koleksi Parfum
                </a>
            </div>
        </x-card>
    @else
        <div class="row g-4">
            <!-- Left Side: Order Items List -->
            <div class="col-lg-7">
                <x-card class="p-4 p-md-4 mb-4 border-0 shadow">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom" style="border-color: rgba(216, 197, 174, 0.3) !important;">
                        <h5 class="fw-bold font-heading mb-0 text-dark">Rincian Item Parfum</h5>
                        <span class="badge rounded-pill px-3 py-1" style="background: rgba(139, 107, 80, 0.12); color: var(--color-mocha); font-size: 0.78rem;">
                            {{ count($cart) }} Varian Dipilih
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="small" style="background: #FAF8F5; color: var(--color-charcoal);">
                                <tr>
                                    <th class="border-0 rounded-start-3">Parfum</th>
                                    <th class="border-0">Ukuran</th>
                                    <th class="border-0">Harga</th>
                                    <th class="text-center border-0" style="width: 80px;">Jumlah</th>
                                    <th class="border-0">Subtotal</th>
                                    <th class="border-0 rounded-end-3" style="width: 44px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cart as $key => $item)
                                    <tr style="border-bottom: 1px solid rgba(216, 197, 174, 0.25);">
                                        <td class="py-3">
                                            <div class="fw-bold font-heading text-dark">{{ $item['name'] }}</div>
                                            <div class="small text-muted" style="font-size: 0.75rem;">{{ $item['brand'] ?? 'In Parfume' }}</div>
                                        </td>
                                        <td class="py-3">
                                            <span class="badge rounded-pill px-2 py-1" style="background: #FAF8F5; color: var(--color-charcoal); border: 1px solid rgba(216, 197, 174, 0.5); font-size: 0.75rem;">
                                                {{ $item['size_ml'] }} ml
                                            </span>
                                        </td>
                                        <td class="py-3 small text-secondary">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                                        <td class="text-center py-3 fw-bold text-dark">
                                            {{ $item['quantity'] }}
                                        </td>
                                        <td class="py-3 fw-bold font-heading text-dark">
                                            Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                        </td>
                                        <td class="py-3 text-end">
                                            <form action="{{ route('customer.cart.remove', $key) }}" method="POST" onsubmit="return confirm('Hapus varian ini dari keranjang?')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm text-danger p-1 shadow-none" title="Hapus Item">
                                                    <i class="bi bi-trash fs-6"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-3 border-top" style="border-color: rgba(216, 197, 174, 0.3) !important;">
                        <a href="{{ route('catalog') }}" class="btn btn-sm btn-outline-mocha px-3 py-2 d-inline-flex align-items-center gap-1">
                            <i class="bi bi-arrow-left"></i> Tambah Varian Lain
                        </a>
                        <div class="text-end mt-2 mt-sm-0">
                            <span class="text-muted small d-block" style="font-size: 0.78rem;">Total Belanja Parfum:</span>
                            <h4 class="fw-bold font-heading mb-0 text-dark">Rp {{ number_format($total, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </x-card>
            </div>

            <!-- Right Side: Shipping & Recipient Details Form -->
            <div class="col-lg-5">
                <form action="{{ route('customer.checkout.post') }}" method="POST">
                    @csrf
                    <x-card class="p-4 border-0 shadow">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom" style="border-color: rgba(216, 197, 174, 0.3) !important;">
                            <i class="bi bi-geo-alt fs-5" style="color: var(--color-mocha);"></i>
                            <h5 class="fw-bold font-heading mb-0 text-dark">Informasi Pengiriman</h5>
                        </div>

                        <!-- Recipient Name -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Nama Penerima Paket <span class="text-danger">*</span></label>
                            <div class="input-group-uyuy d-flex align-items-center @error('recipient_name') border-danger @enderror">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control form-control-uyuy" name="recipient_name" value="{{ old('recipient_name', auth()->user()->name) }}" required>
                            </div>
                            @error('recipient_name')
                                <div class="text-danger small mt-1 ps-1" style="font-size: 0.82rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Recipient Phone -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">No. WhatsApp Penerima <span class="text-danger">*</span></label>
                            <div class="input-group-uyuy d-flex align-items-center @error('recipient_phone') border-danger @enderror">
                                <span class="input-group-text"><i class="bi bi-whatsapp"></i></span>
                                <input type="tel" class="form-control form-control-uyuy" name="recipient_phone" value="{{ old('recipient_phone', $customer->phone_number ?? '') }}" required>
                            </div>
                            <span class="text-muted d-block ps-1 mt-1" style="font-size: 0.72rem;">Admin akan mengonfirmasi nomor ini saat verifikasi dan pengiriman.</span>
                            @error('recipient_phone')
                                <div class="text-danger small mt-1 ps-1" style="font-size: 0.82rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Shipping Address -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Alamat Tujuan Pengiriman <span class="text-danger">*</span></label>
                            <div class="input-group-uyuy p-1 @error('shipping_address') border-danger @enderror">
                                <textarea class="form-control border-0 bg-transparent shadow-none" name="shipping_address" rows="3" required placeholder="Jalan, Nomor Rumah, RT/RW, Kecamatan, Kota, Kode Pos...">{{ old('shipping_address', ($customer->default_address ?? '') . ($customer->city ? ', ' . $customer->city : '')) }}</textarea>
                            </div>
                            @error('shipping_address')
                                <div class="text-danger small mt-1 ps-1" style="font-size: 0.82rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Customer Notes -->
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-secondary mb-1">Catatan Khusus (Opsional)</label>
                            <div class="input-group-uyuy p-1">
                                <textarea class="form-control border-0 bg-transparent shadow-none" name="customer_notes" rows="2" placeholder="Contoh: Packing ekstra tebal, titip botol kaca polos...">{{ old('customer_notes') }}</textarea>
                            </div>
                        </div>

                        <!-- Payment Information Summary Box -->
                        <div class="p-3 rounded-4 mb-4" style="background: #FAF8F5; border: 1.5px solid rgba(216, 197, 174, 0.45);">
                            <div class="d-flex justify-content-between small text-secondary mb-2">
                                <span>Subtotal Barang ({{ array_sum(array_column($cart, 'quantity')) }} botol)</span>
                                <strong class="text-dark">Rp {{ number_format($total, 0, ',', '.') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between small text-secondary mb-2">
                                <span>Ongkos Kirim</span>
                                <span class="badge rounded-pill" style="background: rgba(139, 107, 80, 0.12); color: var(--color-mocha);">Ditagihkan Terpisah (JNE/J&T)</span>
                            </div>
                            <hr class="my-2" style="border-color: rgba(216, 197, 174, 0.35);">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-dark">Total Tagihan Pre-Order:</span>
                                <h5 class="fw-bold font-heading text-dark mb-0 fs-4">Rp {{ number_format($total, 0, ',', '.') }}</h5>
                            </div>
                        </div>

                        @if($activePo && $activePo->isOpen())
                            <x-button type="submit" variant="mocha" class="w-100 py-3 shadow-sm d-flex align-items-center justify-content-center gap-2" icon="bi bi-check2-circle">
                                Buat Pesanan Pre-Order
                            </x-button>
                            <div class="text-center mt-2 text-muted" style="font-size: 0.75rem;">
                                Setelah checkout, Anda akan diarahkan ke halaman transfer rekening BCA.
                            </div>
                        @else
                            <button type="button" class="btn btn-secondary w-100 py-3 rounded-3" disabled>
                                <i class="bi bi-lock me-1"></i> Periode PO Sedang Ditutup
                            </button>
                            <div class="text-center mt-2 text-danger small" style="font-size: 0.75rem;">
                                Checkout dinonaktifkan karena saat ini tidak ada periode PO yang terbuka.
                            </div>
                        @endif
                    </x-card>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
