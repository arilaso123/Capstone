@extends('layouts.owner')

@section('title', 'Rekapitulasi Pesanan Pre-Order')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 fw-bold mb-1 font-heading">Rekapitulasi Pesanan per Periode PO</h2>
        <span class="text-muted small">Wewenang khusus Owner untuk melihat rekap daftar belanja parfum ke vendor <em>In Parfume</em> (FR-17).</span>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak Rekapitulasi
        </button>
    </div>
</div>

<!-- Period Filter (FR-18) -->
<div class="card card-uyuy p-3 mb-4">
    <form action="{{ route('owner.recap.index') }}" method="GET" id="recapFilterForm">
        <div class="row g-3 align-items-center">
            <div class="col-md-5">
                <label class="form-label small text-muted mb-1">Pilih Periode Pre-Order:</label>
                <select class="form-select" name="po_period_id" onchange="document.getElementById('recapFilterForm').submit()">
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
                    <span class="text-muted small">Total Pesanan Tercatat: <strong>{{ $summary['total_orders'] }} Transaksi</strong></span>
                </div>
            @endif
        </div>
    </form>
</div>

@if($selectedPoPeriod)
    <!-- Recap Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--color-champagne-light); color: var(--color-mocha);"><i class="bi bi-boxes"></i></div>
                <div>
                    <div class="stat-value">{{ $summary['total_items'] }} Botol</div>
                    <div class="stat-label">Total Parfum Harus Dipesan</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background: #DCFCE7; color: #166534;"><i class="bi bi-cash-stack"></i></div>
                <div>
                    <div class="stat-value fs-5">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</div>
                    <div class="stat-label">Total Nilai Tagihan (Non-Batal)</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background: #E0F2FE; color: #0369A1;"><i class="bi bi-people"></i></div>
                <div>
                    <div class="stat-value">{{ $summary['total_customers'] }} Orang</div>
                    <div class="stat-label">Jumlah Pelanggan Pemesan</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Aggregated Order List for Vendor In Parfume -->
    <div class="card card-uyuy p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold font-heading mb-0">Daftar Kebutuhan Belanja Parfum (Vendor: In Parfume)</h5>
            <span class="small text-muted">Dikelompokkan per varian aroma & ukuran botol</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th style="width: 50px;">No.</th>
                        <th>Nama Varian Parfum</th>
                        <th>Kategori</th>
                        <th>Ukuran Botol</th>
                        <th class="text-center">Total Jumlah Botol</th>
                        <th class="text-end">Total Nilai Pre-Order</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recapData as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong class="text-dark">{{ $item['product_name'] }}</strong></td>
                            <td>{{ $item['category'] }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $item['size_ml'] }} ml</span></td>
                            <td class="text-center"><span class="badge bg-dark rounded-pill fs-6 px-3">{{ $item['total_quantity'] }} botol</span></td>
                            <td class="text-end fw-bold">Rp {{ number_format($item['total_subtotal'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                Belum ada pesanan parfum pada periode PO ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($recapData->isNotEmpty())
                    <tfoot class="table-secondary fw-bold">
                        <tr>
                            <td colspan="4" class="text-end">Total Belanja {{ $selectedPoPeriod->name }}:</td>
                            <td class="text-center fs-6">{{ $summary['total_items'] }} Botol</td>
                            <td class="text-end fs-6">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@else
    <div class="card card-uyuy p-5 text-center text-muted">
        <i class="bi bi-calendar-x fs-1 mb-3"></i>
        <h5>Pilih atau Buat Periode PO Terlebih Dahulu</h5>
        <p class="small">Belum ada periode Pre-Order yang dipilih untuk menampilkan rekapitulasi data.</p>
    </div>
@endif
@endsection
