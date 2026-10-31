@extends('layouts.owner')

@section('title', 'Manajemen Periode Pre-Order')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 fw-bold mb-1 font-heading">Manajemen Periode Pre-Order (PO)</h2>
        <span class="text-muted small">Wewenang khusus Owner untuk membuat, membuka, dan menutup periode siklus pesanan (FR-04).</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('owner.po-periods.create') }}" class="btn btn-mocha btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Buat Periode PO Baru
        </a>
    </div>
</div>

<div class="card card-uyuy p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
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
                            <strong class="text-dark fs-6">{{ $period->name }}</strong>
                            <div class="text-muted small">ID: #{{ $period->id }} &bull; {{ $period->orders_count }} Pesanan Masuk</div>
                        </td>
                        <td>{{ $period->start_date->format('d M Y') }}</td>
                        <td>{{ $period->end_date->format('d M Y') }}</td>
                        <td><x-status-badge :status="$period->status" /></td>
                        <td class="small text-muted">{{ $period->notes ?? '-' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $period->creator?->name ?? 'Owner' }}</span></td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1 align-items-center">
                                <a href="{{ route('owner.recap.index', ['po_period_id' => $period->id]) }}" class="btn btn-sm btn-outline-secondary" title="Lihat Rekapitulasi">
                                    <i class="bi bi-clipboard-data"></i>
                                </a>
                                <form action="{{ route('owner.po-periods.status.toggle', $period->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @if($period->isOpen())
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tutup periode {{ $period->name }}? Pelanggan tidak akan bisa checkout lagi di batch ini.')">
                                            <i class="bi bi-lock-fill me-1"></i> Tutup PO
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Buka periode {{ $period->name }}? Periode PO yang sedang buka lainnya akan otomatis ditutup.')">
                                            <i class="bi bi-unlock-fill me-1"></i> Buka PO
                                        </button>
                                    @endif
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
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
@endsection
