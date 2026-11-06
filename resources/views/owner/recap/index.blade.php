@extends('layouts.owner')

@section('title', 'Rekapitulasi Pesanan Pre-Order')

@section('content')
<!-- Header Banner -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom" style="border-color: rgba(216, 197, 174, 0.3) !important;">
    <div>
        <h2 class="h3 fw-bold font-heading mb-1 text-dark" style="letter-spacing: -0.02em;">Rekapitulasi Belanja Vendor In Parfume</h2>
        <span class="text-secondary small">Wewenang khusus Owner untuk melihat konsolidasi daftar belanjaan parfum ke gerai In Parfume Bandung (FR-17).</span>
    </div>
    <div class="d-flex gap-2 mt-2 mt-sm-0">
        <button type="button" class="btn btn-outline-secondary btn-sm py-2 px-3 rounded-3" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak Lembar Belanja
        </button>
    </div>
</div>

<!-- Period Filter Bento -->
<x-card class="p-4 mb-4 border-0 shadow">
    <form action="{{ route('owner.recap.index') }}" method="GET" id="recapFilterForm">
        <div class="row g-3 align-items-center">
            <div class="col-md-5">
                <label class="form-label small fw-semibold text-secondary mb-1">Pilih Batch Periode Pre-Order:</label>
                <select class="form-select form-control-uyuy" name="po_period_id" onchange="document.getElementById('recapFilterForm').submit()">
                    @forelse($poPeriods as $period)
                        <option value="{{ $period->id }}" {{ $selectedPoPeriod && $selectedPoPeriod->id === $period->id ? 'selected' : '' }}>
                            {{ $period->name }} ({{ $period->status }}: {{ $period->start_date->format('d M') }} - {{ $period->end_date->format('d M Y') }})
                        </option>
                    @empty
                        <option value="">Belum ada periode PO</option>
                    @endforelse
                </select>
            </div>
            @if($selectedPoPeriod)
                <div class="col-md-7 text-md-end pt-3">
                    <span class="me-2"><x-status-badge :status="$selectedPoPeriod->status" /></span>
                    <span class="text-secondary small">Total Pesanan Tercatat: <strong class="text-dark">{{ $summary['total_orders'] }} Transaksi</strong></span>
                </div>
            @endif
        </div>
    </form>
</x-card>

@if($selectedPoPeriod)
    <!-- Recap Summary Bento Grid -->
    <div class="row g-4 mb-4">
        <!-- Total Bottles -->
        <div class="col-md-4">
            <x-card class="p-4 border-0 shadow h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold text-secondary" style="font-size: 0.8rem;">Kebutuhan Belanja</span>
                    <div class="p-2 rounded-3" style="background: var(--color-champagne-light); color: var(--color-mocha);">
                        <i class="bi bi-boxes fs-5"></i>
                    </div>
                </div>
                <h3 class="display-6 fw-bold font-heading text-dark mb-0">{{ $summary['total_items'] }} <span class="fs-5 fw-normal text-muted">Botol</span></h3>
                <span class="small text-muted" style="font-size: 0.75rem;">Total botol harus dibeli</span>
            </x-card>
        </div>

        <!-- Gross Value -->
        <div class="col-md-4">
            <x-card class="p-4 border-0 shadow h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold text-secondary" style="font-size: 0.8rem;">Total Omzet Valid</span>
                    <div class="p-2 rounded-3" style="background: rgba(22, 163, 74, 0.12); color: #16A34A;">
                        <i class="bi bi-cash-stack fs-5"></i>
                    </div>
                </div>
                <h4 class="fw-bold font-heading text-dark mb-0 fs-3">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</h4>
                <span class="small text-muted" style="font-size: 0.75rem;">Total nilai tagihan terkonfirmasi</span>
            </x-card>
        </div>

        <!-- Total Customers -->
        <div class="col-md-4">
            <x-card class="p-4 border-0 shadow h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold text-secondary" style="font-size: 0.8rem;">Jumlah Pelanggan</span>
                    <div class="p-2 rounded-3" style="background: rgba(3, 105, 161, 0.12); color: #0369A1;">
                        <i class="bi bi-people fs-5"></i>
                    </div>
                </div>
                <h3 class="display-6 fw-bold font-heading text-dark mb-0">{{ $summary['total_customers'] }} <span class="fs-5 fw-normal text-muted">Orang</span></h3>
                <span class="small text-muted" style="font-size: 0.75rem;">Pelanggan aktif batch ini</span>
            </x-card>
        </div>
    </div>

    <!-- Aggregated Order List for Vendor In Parfume -->
    <x-card class="p-4 p-md-5 border-0 shadow">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom" style="border-color: rgba(216, 197, 174, 0.3) !important;">
            <div>
                <h5 class="fw-bold font-heading mb-1 text-dark">Daftar Rekapitulasi Pembelanjaan Botol Parfum</h5>
                <span class="small text-secondary">Konsolidasi varian aroma & volume botol untuk pembelian di toko In Parfume Bandung.</span>
            </div>
            <span class="badge rounded-pill px-3 py-1" style="background: rgba(139, 107, 80, 0.12); color: var(--color-mocha);">
                {{ count($recapData) }} Varian Terdaftar
            </span>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="small" style="background: #FAF8F5; color: var(--color-charcoal);">
                    <tr>
                        <th class="border-0 rounded-start-3 py-3 text-center" style="width: 50px;">No.</th>
                        <th class="border-0 py-3">Nama Varian Parfum</th>
                        <th class="border-0 py-3">Kategori Aroma</th>
                        <th class="border-0 py-3">Ukuran Botol</th>
                        <th class="border-0 py-3 text-center">Total Kuantitas</th>
                        <th class="border-0 rounded-end-3 py-3 text-end">Total Nilai Pre-Order</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recapData as $index => $item)
                        <tr style="border-bottom: 1px solid rgba(216, 197, 174, 0.25);">
                            <td class="text-center py-3 text-muted small">{{ $index + 1 }}</td>
                            <td class="py-3">
                                <strong class="text-dark font-heading fs-6">{{ $item['product_name'] }}</strong>
                            </td>
                            <td class="py-3">
                                <span class="badge rounded-pill px-2 py-1" style="background: rgba(45,36,30,0.06); color: var(--color-charcoal); font-size: 0.72rem;">
                                    {{ $item['category'] }}
                                </span>
                            </td>
                            <td class="py-3">
                                <span class="badge rounded-pill px-3 py-1" style="background: #FAF8F5; border: 1px solid rgba(216, 197, 174, 0.5); color: var(--color-charcoal); font-size: 0.78rem;">
                                    {{ $item['size_ml'] }} ml
                                </span>
                            </td>
                            <td class="text-center py-3">
                                <span class="badge rounded-pill px-3 py-1" style="background: rgba(139, 107, 80, 0.15); color: var(--color-mocha); font-size: 0.85rem; font-weight: 700;">
                                    {{ $item['total_quantity'] }} Botol
                                </span>
                            </td>
                            <td class="text-end py-3 fw-bold font-heading text-dark">
                                Rp {{ number_format($item['total_subtotal'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                Tidak ada item pesanan yang valid untuk periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
@endif
@endsection
