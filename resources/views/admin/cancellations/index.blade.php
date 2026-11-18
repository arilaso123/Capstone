@extends('layouts.admin')

@section('title', 'Permohonan Pembatalan')

@section('content')
<div class="admin-cancellations-page">
    <header class="admin-cancellations-header">
        <div>
            <p class="home-eyebrow">Tugas operasional</p>
            <h1>Permohonan pembatalan</h1>
            <p>Tinjau alasan pelanggan, status pembayaran, dan catatan refund sebelum menetapkan keputusan.</p>
        </div>
    </header>

    <nav class="admin-cancellation-filters" aria-label="Filter permohonan pembatalan">
        <span>Status permohonan</span>
        <a href="{{ route('admin.cancellations.index') }}" @class(['active' => !request('status')]) @if(!request('status')) aria-current="page" @endif>Semua</a>
        <a href="{{ route('admin.cancellations.index', ['status' => \App\Models\CancellationRequest::STATUS_PENDING]) }}" @class(['active' => request('status') === \App\Models\CancellationRequest::STATUS_PENDING]) @if(request('status') === \App\Models\CancellationRequest::STATUS_PENDING) aria-current="page" @endif>Menunggu persetujuan</a>
        <a href="{{ route('admin.cancellations.index', ['status' => \App\Models\CancellationRequest::STATUS_APPROVED]) }}" @class(['active' => request('status') === \App\Models\CancellationRequest::STATUS_APPROVED]) @if(request('status') === \App\Models\CancellationRequest::STATUS_APPROVED) aria-current="page" @endif>Disetujui</a>
        <a href="{{ route('admin.cancellations.index', ['status' => \App\Models\CancellationRequest::STATUS_REJECTED]) }}" @class(['active' => request('status') === \App\Models\CancellationRequest::STATUS_REJECTED]) @if(request('status') === \App\Models\CancellationRequest::STATUS_REJECTED) aria-current="page" @endif>Ditolak</a>
    </nav>

    <section class="admin-cancellations-table-panel" aria-label="Daftar permohonan">
        @if($cancellations->count())
            <div class="table-responsive">
                <table class="table admin-cancellations-table align-middle mb-0">
                    <thead><tr><th scope="col">Pesanan</th><th scope="col">Pelanggan</th><th scope="col">Alasan</th><th scope="col">Waktu diajukan</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Tindakan</span></th></tr></thead>
                    <tbody>
                        @foreach($cancellations as $cancellation)
                            <tr>
                                <td><strong>{{ $cancellation->order?->order_number ?? '—' }}</strong><small>{{ $cancellation->order?->poPeriod?->name ?? '—' }}</small></td>
                                <td>{{ $cancellation->order?->customer?->full_name ?? $cancellation->order?->recipient_name ?? '—' }}</td>
                                <td><span class="admin-cancellation-reason" title="{{ $cancellation->reason }}">{{ $cancellation->reason }}</span></td>
                                <td>{{ $cancellation->created_at->copy()->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</td>
                                <td><span class="admin-cancellation-status">{{ $cancellation->status }}</span></td>
                                <td class="text-end"><a class="admin-cancellation-action" href="{{ route('admin.cancellations.show', $cancellation->id) }}">{{ $cancellation->isPending() ? 'Tinjau permohonan' : 'Lihat detail' }} <span aria-hidden="true">→</span></a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="admin-cancellations-empty"><p class="home-eyebrow">Tidak ada permohonan</p><h2>Daftar ini masih kosong</h2><p>Permohonan pembatalan akan ditampilkan di sini untuk ditinjau.</p></div>
        @endif
    </section>

    @if($cancellations->hasPages())
        <nav class="admin-cancellations-pagination" aria-label="Navigasi halaman permohonan">{{ $cancellations->withQueryString()->links() }}</nav>
    @endif
</div>
@endsection
