@extends('layouts.app')

@section('title', 'Keranjang & Checkout Pemesanan')

@section('content')
<div class="py-4 border-bottom" style="background-color: var(--color-warm-ivory);">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-secondary">Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ route('catalog') }}" class="text-secondary">Katalog Parfum</a></li>
                <li class="breadcrumb-item active" aria-current="page">Keranjang Pre-Order</li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0">Keranjang & Checkout Pesanan Pre-Order</h1>
    </div>
</div>

<div class="container py-5">
    <!-- Active PO Period Status Banner -->
    @if($activePo && $activePo->isOpen())
        <div class="alert alert-success d-flex align-items-center justify-content-between card-uyuy border-success mb-4" role="alert">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-calendar-check-fill text-success fs-3"></i>
                <div>
                    <strong>Periode Pemesanan: {{ $activePo->name }} (DIBUKA)</strong>
                    <div class="small text-muted">Pesanan Anda akan diikutsertakan pada siklus belanja batch ini (berakhir {{ $activePo->end_date->format('d M Y') }}).</div>
                </div>
            </div>
            <x-status-badge status="Dibuka" />
        </div>
    @else
        <div class="alert alert-warning d-flex align-items-center justify-content-between card-uyuy border-warning mb-4" role="alert">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-lock-fill text-warning fs-3"></i>
                <div>
                    <strong>Periode Pre-Order Sedang Ditutup</strong>
                    <div class="small text-muted">Anda tidak dapat melakukan checkout selama periode PO belum dibuka oleh Owner.</div>
                </div>
            </div>
            <x-status-badge status="Ditutup" />
        </div>
    @endif

    @if(empty($cart))
        <div class="card card-uyuy p-5 text-center">
            <i class="bi bi-bag-x text-muted" style="font-size: 4rem;"></i>
            <h4 class="fw-bold mt-3 mb-2">Keranjang Pre-Order Anda Masih Kosong</h4>
            <p class="text-muted mb-4">Pilih aroma parfum favorit Anda dari katalog In Parfume untuk mulai memesan.</p>
            <div>
                <a href="{{ route('catalog') }}" class="btn btn-mocha px-4">
                    <i class="bi bi-grid me-2"></i>Jelajahi Katalog Parfum
                </a>
            </div>
        </div>
    @else
        <div class="row g-4">
            <!-- Left Side: Order Items List -->
            <div class="col-lg-7">
                <div class="card card-uyuy p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold font-heading mb-0">Rincian Item Parfum</h5>
                        <span class="badge bg-secondary">{{ count($cart) }} Produk Dipilih</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light small">
                                <tr>
                                    <th>Parfum</th>
                                    <th>Ukuran</th>
                                    <th>Harga</th>
                                    <th class="text-center" style="width: 90px;">Jumlah</th>
                                    <th>Subtotal</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cart as $key => $item)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $item['name'] }}</div>
                                            <div class="small text-muted">{{ $item['brand'] ?? 'In Parfume' }}</div>
                                        </td>
                                        <td><span class="badge bg-light text-dark border">{{ $item['size_ml'] }} ml</span></td>
                                        <td>Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                                        <td class="text-center fw-semibold">
                                            {{ $item['quantity'] }}
                                        </td>
                                        <td class="fw-bold">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</td>
                                        <td>
                                            <form action="{{ route('customer.cart.remove', $key) }}" method="POST" onsubmit="return confirm('Hapus item ini dari keranjang?')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="Hapus"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <a href="{{ route('catalog') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Tambah Parfum Lain
                        </a>
                        <div class="text-end">
                            <span class="text-muted small">Total Nilai Barang:</span>
                            <h4 class="fw-bold mb-0 text-dark">Rp {{ number_format($total, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Shipping & Recipient Details Form -->
            <div class="col-lg-5">
                <form action="{{ route('customer.checkout.post') }}" method="POST">
                    @csrf
                    <div class="card card-uyuy p-4">
                        <h5 class="fw-bold font-heading mb-3">Informasi Pengiriman</h5>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Penerima Paket <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('recipient_name') is-invalid @enderror" name="recipient_name" value="{{ old('recipient_name', auth()->user()->name) }}" required>
                            @error('recipient_name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nomor WhatsApp Penerima <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('recipient_phone') is-invalid @enderror" name="recipient_phone" value="{{ old('recipient_phone', $customer->phone_number ?? '') }}" required>
                            <span class="text-muted small" style="font-size: 0.72rem;">Admin akan menghubungi nomor ini jika ada konfirmasi varian atau pengiriman.</span>
                            @error('recipient_phone')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Alamat Tujuan Pengiriman Lengkap <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('shipping_address') is-invalid @enderror" name="shipping_address" rows="3" required placeholder="Jalan, Nomor Rumah, RT/RW, Kecamatan, Kota, Kode Pos...">{{ old('shipping_address', ($customer->default_address ?? '') . ($customer->city ? ', ' . $customer->city : '')) }}</textarea>
                            @error('shipping_address')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-semibold">Catatan Khusus Pemesan (Opsional)</label>
                            <textarea class="form-control" name="customer_notes" rows="2" placeholder="Contoh: Packing bubble wrap tebal, titip botol kaca...">{{ old('customer_notes') }}</textarea>
                        </div>

                        <!-- Payment Information Summary -->
                        <div class="p-3 rounded-3 bg-light border mb-4">
                            <div class="d-flex justify-content-between small mb-2">
                                <span>Subtotal Barang ({{ array_sum(array_column($cart, 'quantity')) }} botol)</span>
                                <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between small mb-2">
                                <span>Ongkos Kirim</span>
                                <span class="badge bg-secondary">Ditagihkan Terpisah / JNE</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Total Pembayaran PO:</span>
                                <h5 class="fw-bold text-dark mb-0">Rp {{ number_format($total, 0, ',', '.') }}</h5>
                            </div>
                        </div>

                        @if($activePo && $activePo->isOpen())
                            <button type="submit" class="btn btn-mocha w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-check-circle"></i> Buat Pesanan Pre-Order
                            </button>
                            <div class="text-center mt-2 text-muted" style="font-size: 0.75rem;">
                                Setelah checkout, status pesanan akan menjadi <strong>Menunggu Pembayaran</strong>.
                            </div>
                        @else
                            <button type="button" class="btn btn-secondary w-100 py-2" disabled>
                                <i class="bi bi-lock me-1"></i> Periode PO Sedang Ditutup
                            </button>
                            <div class="text-center mt-2 text-danger small" style="font-size: 0.75rem;">
                                Checkout dinonaktifkan karena tidak ada periode PO yang terbuka.
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
