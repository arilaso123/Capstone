@extends('layouts.owner')

@section('title', 'Ringkasan Bisnis Owner')

@section('content')
<section class="owner-dashboard-page" aria-labelledby="ownerDashboardTitle">
    <nav class="workspace-breadcrumb" aria-label="Breadcrumb">
        <span>Owner</span>
        <span aria-hidden="true">/</span>
        <span>Dashboard Eksekutif</span>
    </nav>

    <header class="owner-dashboard-header">
        <div>
            <p class="owner-recap-eyebrow">OWNER / RINGKASAN BISNIS</p>
            <h1 id="ownerDashboardTitle">Gambaran usaha</h1>
            <p>Ringkasan pesanan, pembayaran valid, pelanggan, dan katalog produk.</p>
        </div>
        <div class="owner-dashboard-header-actions">
            <a href="{{ route('owner.po-periods.create') }}" class="btn btn-dark"><i class="bi bi-calendar-plus" aria-hidden="true"></i> Buat periode PO</a>
            <a href="{{ route('owner.products.create') }}" class="btn btn-outline-secondary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah produk</a>
        </div>
    </header>

    @if($activePo)
        <section class="owner-dashboard-period" aria-labelledby="ownerActivePeriodTitle">
            <div class="owner-dashboard-period-main">
                <p class="owner-recap-eyebrow">PERIODE PRE-ORDER</p>
                <h2 id="ownerActivePeriodTitle">{{ $activePo->name }}</h2>
                <div class="owner-dashboard-period-meta">
                    <span>{{ $activePo->start_date->format('d M Y') }} – {{ $activePo->end_date->format('d M Y') }}</span>
                    <span>{{ number_format($activePo->orders()->count(), 0, ',', '.') }} pesanan tercatat</span>
                </div>
                <p class="owner-dashboard-period-note">
                    @if($activePo->isOrderableNow())
                        Checkout tersedia selama periode ini masih berada dalam rentang tanggal PO WIB.
                    @else
                        Status administrasi PO adalah {{ $activePo->status }}; checkout tidak tersedia di luar rentang tanggal PO WIB.
                    @endif
                </p>
            </div>
            <div class="owner-dashboard-period-actions">
                <x-status-badge :status="$activePo->status" />
                <form action="{{ route('owner.po-periods.status.toggle', $activePo->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary" data-confirm="Tutup periode PO ini? Pelanggan tidak akan dapat membuat pesanan baru setelah periode ditutup.">
                        <i class="bi bi-lock" aria-hidden="true"></i> Tutup periode
                    </button>
                </form>
            </div>
        </section>
    @else
        <section class="owner-dashboard-period owner-dashboard-period-empty" aria-label="Periode pre-order">
            <div>
                <p class="owner-recap-eyebrow">PERIODE PRE-ORDER</p>
                <h2>Belum ada periode yang dibuka</h2>
                <p>Checkout tidak tersedia sampai periode PO dengan jadwal yang sesuai dibuka.</p>
            </div>
            <a href="{{ route('owner.po-periods.create') }}" class="btn btn-dark">Buat periode PO</a>
        </section>
    @endif

    <dl class="owner-dashboard-metrics" aria-label="Metrik bisnis">
        <div class="owner-dashboard-metric">
            <div class="owner-metric-head">
                <dt>Total pesanan</dt>
                <span class="owner-metric-icon" aria-hidden="true"><i class="bi bi-bag-check"></i></span>
            </div>
            <dd>{{ number_format($totalOrdersCount, 0, ',', '.') }}</dd>
            <p>Akumulasi seluruh periode.</p>
        </div>
        <div class="owner-dashboard-metric">
            <div class="owner-metric-head">
                <dt>Pembayaran valid</dt>
                <span class="owner-metric-icon" aria-hidden="true"><i class="bi bi-wallet2"></i></span>
            </div>
            <dd>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</dd>
            <p>Jumlah pembayaran berstatus valid, seluruh periode.</p>
        </div>
        <div class="owner-dashboard-metric">
            <div class="owner-metric-head">
                <dt>Pelanggan terdaftar</dt>
                <span class="owner-metric-icon" aria-hidden="true"><i class="bi bi-people"></i></span>
            </div>
            <dd>{{ number_format($totalCustomersCount, 0, ',', '.') }}</dd>
            <p>Akun pelanggan yang tersimpan.</p>
            <a href="{{ route('owner.customers.index') }}" class="owner-dashboard-metric-link">Lihat pelanggan <span aria-hidden="true">→</span></a>
        </div>
        <div class="owner-dashboard-metric">
            <div class="owner-metric-head">
                <dt>Produk aktif</dt>
                <span class="owner-metric-icon" aria-hidden="true"><i class="bi bi-grid"></i></span>
            </div>
            <dd>{{ number_format($activeProductsCount, 0, ',', '.') }}</dd>
            <p>Produk yang tampil di katalog.</p>
            <a href="{{ route('owner.products.index') }}" class="owner-dashboard-metric-link">Kelola produk <span aria-hidden="true">→</span></a>
        </div>
    </dl>

    <div class="owner-dashboard-sections">
        <section class="owner-dashboard-panel" aria-labelledby="ownerStatusTitle">
            <header class="owner-dashboard-panel-header">
                <div>
                    <p class="owner-recap-eyebrow">ALUR PESANAN</p>
                    <h2 id="ownerStatusTitle">Distribusi status</h2>
                </div>
                <span>{{ $activePo ? $activePo->name : 'Semua periode' }}</span>
            </header>

            @php
                $statusRows = [
                    ['key' => 'waiting', 'label' => 'Menunggu Pembayaran'],
                    ['key' => 'verified', 'label' => 'Pembayaran Diverifikasi'],
                    ['key' => 'processing', 'label' => 'Diproses'],
                    ['key' => 'shipped', 'label' => 'Barang Dikirim'],
                    ['key' => 'completed', 'label' => 'Selesai'],
                    ['key' => 'cancelled', 'label' => 'Dibatalkan'],
                ];
            @endphp
            <ul class="owner-dashboard-status-list">
                @foreach($statusRows as $row)
                    <li>
                        <x-status-badge :status="$row['label']" />
                        <span>{{ number_format($statusBreakdown[$row['key']], 0, ',', '.') }} pesanan</span>
                    </li>
                @endforeach
            </ul>
        </section>

        <nav class="owner-dashboard-panel owner-dashboard-shortcuts" aria-label="Aksi cepat Owner">
            <header class="owner-dashboard-panel-header">
                <div>
                    <p class="owner-recap-eyebrow">NAVIGASI</p>
                    <h2>Akses pengelolaan</h2>
                </div>
            </header>
            <a href="{{ route('owner.po-periods.index') }}">
                <span><i class="bi bi-calendar2-week" aria-hidden="true"></i> Periode PO</span>
                <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
            </a>
            <a href="{{ route('owner.products.index') }}">
                <span><i class="bi bi-box-seam" aria-hidden="true"></i> Produk dan varian</span>
                <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
            </a>
            <a href="{{ route('owner.customers.index') }}">
                <span><i class="bi bi-people" aria-hidden="true"></i> Data pelanggan</span>
                <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
            </a>
            <a href="{{ route('owner.recap.index') }}">
                <span><i class="bi bi-table" aria-hidden="true"></i> Rekap belanja PO</span>
                <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
            </a>
        </nav>
    </div>
</section>
@endsection
