@extends('layouts.admin')

@section('title', 'Daftar Permohonan Pembatalan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 fw-bold mb-1 font-heading">Permohonan Pembatalan Pesanan</h2>
        <span class="text-muted small">Pemeriksaan dan persetujuan pengajuan pembatalan dari pelanggan (FR-14, FR-15).</span>
    </div>
</div>

<div class="card card-uyuy p-4">
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('admin.cancellations.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-dark' : 'btn-outline-secondary' }}">
            Semua Permohonan
        </a>
        <a href="{{ route('admin.cancellations.index', ['status' => \App\Models\CancellationRequest::STATUS_PENDING]) }}"
           class="btn btn-sm {{ request('status') === \App\Models\CancellationRequest::STATUS_PENDING ? 'btn-dark' : 'btn-outline-secondary' }}">
            Menunggu Persetujuan
        </a>
        <a href="{{ route('admin.cancellations.index', ['status' => \App\Models\CancellationRequest::STATUS_APPROVED]) }}"
           class="btn btn-sm {{ request('status') === \App\Models\CancellationRequest::STATUS_APPROVED ? 'btn-dark' : 'btn-outline-secondary' }}">
            Disetujui
        </a>
        <a href="{{ route('admin.cancellations.index', ['status' => \App\Models\CancellationRequest::STATUS_REJECTED]) }}"
           class="btn btn-sm {{ request('status') === \App\Models\CancellationRequest::STATUS_REJECTED ? 'btn-dark' : 'btn-outline-secondary' }}">
            Ditolak
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>No. Pesanan</th>
                    <th>Nama Pelanggan</th>
                    <th>Alasan Pengajuan</th>
                    <th>Waktu Diajukan</th>
                    <th>Status Permohonan</th>
                    <th class="text-end">Aksi Putusan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cancellations as $cancellation)
                    <tr>
                        <td>
                            <strong class="text-dark">{{ $cancellation->order?->order_number ?? '-' }}</strong>
                            <div class="text-muted small">{{ $cancellation->order?->poPeriod?->name ?? '-' }}</div>
                        </td>
                        <td>{{ $cancellation->order?->customer?->full_name ?? $cancellation->order?->recipient_name }}</td>
                        <td><span class="text-truncate d-inline-block" style="max-width: 280px;">{{ $cancellation->reason }}</span></td>
                        <td>{{ $cancellation->created_at->format('d M Y, H:i') }} WIB</td>
                        <td>
                            <span class="badge {{ $cancellation->isApproved() ? 'bg-success' : ($cancellation->isRejected() ? 'bg-danger' : 'bg-warning text-dark') }}">
                                {{ $cancellation->status }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.cancellations.show', $cancellation->id) }}" class="btn btn-sm {{ $cancellation->isPending() ? 'btn-mocha' : 'btn-outline-secondary' }}">
                                <i class="bi {{ $cancellation->isPending() ? 'bi-clipboard-check' : 'bi-eye' }} me-1"></i>
                                {{ $cancellation->isPending() ? 'Proses Putusan' : 'Lihat Detail' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            Tidak ada permohonan pembatalan yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($cancellations->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $cancellations->links() }}
        </div>
    @endif
</div>
@endsection
