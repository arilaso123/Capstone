@extends('layouts.owner')

@section('title', 'Manajemen Periode Pre-Order')

@section('content')
<section class="owner-dashboard-page" aria-labelledby="ownerPeriodsTitle">
<header class="workspace-page-header">
    <div>
        <p class="owner-recap-eyebrow">OWNER / PERIODE PRE-ORDER</p>
        <h1 id="ownerPeriodsTitle">Manajemen periode pre-order</h1>
        <p>Wewenang khusus Owner untuk membuat, membuka, dan menutup periode siklus pesanan (FR-04).</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('owner.po-periods.create') }}" class="btn btn-dark">
            <i class="bi bi-plus-lg" aria-hidden="true"></i> Buat Periode PO Baru
        </a>
    </div>
</header>

<div class="card card-uyuy p-3 p-md-4">
    <div class="table-responsive">
        <table class="table owner-periods-table align-middle mb-0">
            <thead>
                <tr>
                    <th>Nama Periode</th>
                    <th>Tanggal Mulai</th>
                    <th>Tanggal Selesai</th>
                    <th>Status PO</th>
                    <th>Keterangan / Catatan</th>
                    <th>Dibuat Oleh</th>
                    <th class="text-end">Aksi Kontrol Owner</th>
                </tr>
            </thead>
            <tbody>
                @forelse($poPeriods as $period)
                    <tr>
                        <td>
                            <strong>{{ $period->name }}</strong>
                            <div class="text-muted small">ID #{{ $period->id }} · {{ $period->orders_count }} Pesanan Masuk</div>
                        </td>
                        <td>{{ $period->start_date->format('d M Y') }}</td>
                        <td>{{ $period->end_date->format('d M Y') }}</td>
                        <td>
                            <x-status-badge :status="$period->status" />
                            @if($period->isOrderableNow())
                                <div class="small text-muted mt-1">Checkout aktif</div>
                            @elseif($period->isOpen())
                                <div class="small text-muted mt-1">Di luar rentang tanggal WIB</div>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $period->notes ?? '-' }}</td>
                        <td><span class="badge-uyuy badge-po-closed">{{ $period->creator?->name ?? 'Owner' }}</span></td>
                        <td class="text-end">
                            <div class="d-inline-flex flex-wrap justify-content-end gap-1 align-items-center">
                                <a href="{{ route('owner.po-periods.edit', $period->id) }}" class="btn btn-sm btn-outline-secondary" title="Edit periode">
                                    <i class="bi bi-pencil-square me-1" aria-hidden="true"></i>Edit
                                </a>
                                <a href="{{ route('owner.recap.index', ['po_period_id' => $period->id]) }}" class="btn btn-sm btn-outline-secondary" title="Lihat Rekapitulasi" aria-label="Rekapitulasi {{ $period->name }}">
                                    <i class="bi bi-clipboard-data" aria-hidden="true"></i>
                                </a>
                                <form action="{{ route('owner.po-periods.status.toggle', $period->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @if($period->isOpen())
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" data-confirm="Tutup periode {{ $period->name }}? Pelanggan tidak akan bisa checkout lagi di batch ini.">
                                            <i class="bi bi-lock me-1" aria-hidden="true"></i> Tutup PO
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-sm btn-dark" data-confirm="Buka periode {{ $period->name }}? Periode PO yang sedang buka lainnya akan otomatis ditutup.">
                                            <i class="bi bi-unlock me-1" aria-hidden="true"></i> Buka PO
                                        </button>
                                    @endif
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x fs-2 d-block mb-2" aria-hidden="true"></i>
                            Belum ada data periode pre-order. Silakan buat periode baru.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($poPeriods->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $poPeriods->links() }}
        </div>
    @endif
</div>
</section>
@endsection
