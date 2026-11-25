@extends('layouts.owner')

@section('title', 'Rekapitulasi Pesanan Pre-Order')

@section('content')
<section class="owner-recap-page" aria-labelledby="ownerRecapTitle">
    <header class="owner-recap-header">
        <div>
            <p class="owner-recap-eyebrow">OWNER / LAPORAN OPERASIONAL</p>
            <h1 class="owner-recap-title" id="ownerRecapTitle">Rekapitulasi belanja PO</h1>
            <p class="owner-recap-description">Konsolidasi kebutuhan parfum berdasarkan pesanan dengan pembayaran yang telah diverifikasi.</p>
        </div>
        <div class="owner-recap-actions">
            @if($selectedPoPeriod)
                <a href="{{ route('owner.recap.export', ['po_period_id' => $selectedPoPeriod->id]) }}" class="btn btn-dark owner-recap-print">
                    <i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i> Unduh CSV
                </a>
            @endif
            <button type="button" class="btn btn-outline-secondary owner-recap-print" data-print-trigger>
                <i class="bi bi-printer" aria-hidden="true"></i> Cetak rekap
            </button>
        </div>
    </header>

    <section class="owner-recap-filter" aria-label="Filter periode pre-order">
        <form action="{{ route('owner.recap.index') }}" method="GET" class="owner-recap-filter-form">
            <div class="owner-recap-filter-control">
                <label class="owner-recap-label" for="poPeriodSelect">Pilih periode PO</label>
                <select class="form-select" name="po_period_id" id="poPeriodSelect" {{ $poPeriods->isEmpty() ? 'disabled' : '' }}>
                    @forelse($poPeriods as $period)
                        <option value="{{ $period->id }}" {{ $selectedPoPeriod && $selectedPoPeriod->id === $period->id ? 'selected' : '' }}>
                            {{ $period->name }} ({{ $period->start_date->format('d M Y') }} – {{ $period->end_date->format('d M Y') }})
                        </option>
                    @empty
                        <option value="">Belum ada periode PO</option>
                    @endforelse
                </select>
            </div>
            <button class="btn btn-outline-secondary owner-recap-filter-submit" type="submit" {{ $poPeriods->isEmpty() ? 'disabled' : '' }}>
                Tampilkan rekap
            </button>
            @if($selectedPoPeriod)
                <div class="owner-recap-period-summary">
                    <x-status-badge :status="$selectedPoPeriod->status" />
                    <span>{{ $summary['total_orders'] }} pesanan dalam periode</span>
                </div>
            @endif
        </form>
    </section>

    @if($selectedPoPeriod)
        <section class="owner-recap-period-heading" aria-label="Periode yang dipilih">
            <div>
                <p class="owner-recap-eyebrow">PERIODE TERPILIH</p>
                <h2>{{ $selectedPoPeriod->name }}</h2>
            </div>
            <p>{{ $selectedPoPeriod->start_date->format('d M Y') }} – {{ $selectedPoPeriod->end_date->format('d M Y') }}</p>
        </section>

        <dl class="owner-recap-metrics" aria-label="Ringkasan periode PO">
            <div class="owner-recap-metric">
                <dt>Unit terverifikasi</dt>
                <dd>{{ number_format($summary['total_items'], 0, ',', '.') }} <span>botol</span></dd>
                <p>Dari pesanan dengan pembayaran valid.</p>
            </div>
            <div class="owner-recap-metric">
                <dt>Modal tercatat</dt>
                <dd>Rp {{ number_format($summary['total_cost'], 0, ',', '.') }}</dd>
                <p>{{ number_format($summary['cost_tracked_items'], 0, ',', '.') }} dari {{ number_format($summary['total_items'], 0, ',', '.') }} botol memiliki data modal.</p>
            </div>
            <div class="owner-recap-metric">
                <dt>Pembayaran valid</dt>
                <dd>Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</dd>
                <p>Jumlah pembayaran yang telah diverifikasi.</p>
            </div>
            <div class="owner-recap-metric">
                <dt>Pelanggan terverifikasi</dt>
                <dd>{{ number_format($summary['total_customers'], 0, ',', '.') }} <span>pelanggan</span></dd>
                <p>Memiliki pesanan dengan pembayaran valid.</p>
            </div>
            <div class="owner-recap-metric">
                <dt>Menunggu pembayaran</dt>
                <dd>{{ number_format($summary['unpaid_orders'], 0, ',', '.') }} <span>pesanan</span></dd>
                <p>Belum masuk kebutuhan belanja terverifikasi.</p>
            </div>
        </dl>

        <section class="owner-recap-table-section" aria-labelledby="ownerRecapTableTitle">
            <header class="owner-recap-table-header">
                <div>
                    <p class="owner-recap-eyebrow">PENGADAAN PRODUK</p>
                    <h2 id="ownerRecapTableTitle">Rincian varian parfum</h2>
                    <p>Jumlah kebutuhan dan nilai pesanan dihitung dari item pada pesanan terverifikasi.</p>
                </div>
                <span class="owner-recap-variant-count">{{ number_format($recapData->count(), 0, ',', '.') }} varian</span>
            </header>

            <div class="table-responsive owner-recap-table-wrap">
                <table class="table owner-recap-table align-middle mb-0">
                    <caption class="visually-hidden">Rincian varian, jumlah botol, nilai pesanan, dan modal tercatat</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="owner-recap-number">No.</th>
                            <th scope="col">Nama produk</th>
                            <th scope="col">Kategori aroma</th>
                            <th scope="col">Ukuran</th>
                            <th scope="col" class="text-end">Jumlah botol</th>
                            <th scope="col" class="text-end">Nilai pesanan</th>
                            <th scope="col" class="text-end">Modal tercatat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recapData as $index => $item)
                            <tr>
                                <td class="owner-recap-number">{{ $index + 1 }}</td>
                                <td class="owner-recap-product-name">{{ $item['product_name'] }}</td>
                                <td>{{ $item['category'] }}</td>
                                <td>{{ $item['size_ml'] }} ml</td>
                                <td class="text-end">{{ number_format($item['total_quantity'], 0, ',', '.') }} botol</td>
                                <td class="text-end owner-recap-currency">Rp {{ number_format($item['total_subtotal'], 0, ',', '.') }}</td>
                                <td class="text-end owner-recap-currency">
                                    Rp {{ number_format($item['total_cost'], 0, ',', '.') }}
                                    @if($item['cost_tracked_quantity'] < $item['total_quantity'])
                                        <span class="owner-recap-table-note">Modal tercatat untuk {{ number_format($item['cost_tracked_quantity'], 0, ',', '.') }} dari {{ number_format($item['total_quantity'], 0, ',', '.') }} botol.</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="owner-recap-empty-row">
                                        <i class="bi bi-inbox" aria-hidden="true"></i>
                                        <strong>Belum ada pembayaran valid pada periode ini.</strong>
                                        <span>Rincian belanja akan muncul setelah pembayaran pesanan diverifikasi.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @else
        <div class="owner-recap-empty-state">
            <i class="bi bi-calendar2-week" aria-hidden="true"></i>
            <h2>Periode PO belum tersedia</h2>
            <p>Buat periode PO untuk mulai melihat rekap belanja dan pembayaran.</p>
            <a class="btn btn-dark" href="{{ route('owner.po-periods.create') }}">Buat periode PO</a>
        </div>
    @endif
</section>
@endsection
