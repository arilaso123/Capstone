@extends('layouts.admin')

@section('title', 'Pemeriksaan Pembayaran Manual')

@section('content')
<div class="admin-payments-page">
<header class="workspace-page-header">
    <div>
        <p class="home-eyebrow">ADMIN / PEMBAYARAN</p>
        <h1>Pemeriksaan pembayaran</h1>
        <p>Cocokkan bukti pelanggan dengan mutasi rekening sebelum memperbarui status.</p>
    </div>
    <a href="{{ route('admin.orders.index') }}" class="workspace-back-link">Semua pesanan <span aria-hidden="true">→</span></a>
</header>

<!-- Alert Reminder -->
<div class="alert admin-payment-reminder d-flex align-items-center gap-3 card-uyuy mb-4" role="note">
    <i class="bi bi-info-circle" aria-hidden="true"></i>
    <div class="small">
        <strong>Penting untuk Staf Admin:</strong> Uyuy Jastip tidak menggunakan integrasi API BCA otomatis. Cocokkan bukti dengan mutasi rekening BCA Merchant sebelum mengubah status menjadi <strong>Valid</strong>. Bukti disimpan privat dan dipertahankan sampai Owner menetapkan kebijakan retensi.
    </div>
</div>

<div class="card card-uyuy p-3 p-md-4">
    <!-- Filter Tabs -->
    <nav class="admin-cancellation-filters d-flex flex-wrap gap-2 mb-3" aria-label="Filter status bukti pembayaran">
        <a href="{{ route('admin.payments.index') }}" class="{{ !request('status') ? 'active' : '' }}" @if(!request('status')) aria-current="page" @endif>
            Semua Bukti
        </a>
        <a href="{{ route('admin.payments.index', ['status' => \App\Models\Payment::STATUS_PENDING]) }}"
           class="{{ request('status') === \App\Models\Payment::STATUS_PENDING ? 'active' : '' }}" @if(request('status') === \App\Models\Payment::STATUS_PENDING) aria-current="page" @endif>
            Menunggu Verifikasi
        </a>
        <a href="{{ route('admin.payments.index', ['status' => \App\Models\Payment::STATUS_VALID]) }}"
           class="{{ request('status') === \App\Models\Payment::STATUS_VALID ? 'active' : '' }}" @if(request('status') === \App\Models\Payment::STATUS_VALID) aria-current="page" @endif>
            Valid
        </a>
        <a href="{{ route('admin.payments.index', ['status' => \App\Models\Payment::STATUS_REJECTED]) }}"
           class="{{ request('status') === \App\Models\Payment::STATUS_REJECTED ? 'active' : '' }}" @if(request('status') === \App\Models\Payment::STATUS_REJECTED) aria-current="page" @endif>
            Ditolak
        </a>
    </nav>

    <div class="table-responsive">
        <table class="table admin-cancellations-table admin-payments-table align-middle mb-0">
            <thead>
                <tr>
                    <th>No. Pesanan</th>
                    <th>Nama Pelanggan</th>
                    <th>Metode</th>
                    <th>Nominal</th>
                    <th>Waktu Unggah</th>
                    <th>Status Verifikasi</th>
                    <th class="text-end">Aksi Pemeriksaan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>
                            <strong>{{ $payment->order?->order_number ?? '-' }}</strong>
                            <small>{{ $payment->order?->poPeriod?->name ?? '-' }}</small>
                        </td>
                        <td>{{ $payment->order?->customer?->full_name ?? $payment->order?->recipient_name }}</td>
                        <td>{{ $payment->payment_method ?? 'Belum ditentukan' }}</td>
                        <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                        <td>{{ $payment->uploaded_at?->copy()->timezone('Asia/Jakarta')->format('d M Y, H:i') ?? '-' }}</td>
                        <td>
                            <x-status-badge :status="$payment->verification_status" />
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.payments.verify', $payment->id) }}" class="btn btn-sm {{ $payment->isPending() ? 'btn-mocha' : 'btn-outline-secondary' }}">
                                <i class="bi {{ $payment->isPending() ? 'bi-check-square' : 'bi-eye' }} me-1" aria-hidden="true"></i>
                                {{ $payment->isPending() ? 'Periksa Bukti' : 'Lihat Detail' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2" aria-hidden="true"></i>
                            Tidak ada data bukti transfer yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($payments->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $payments->withQueryString()->links() }}
        </div>
    @endif
</div>
</div>
@endsection
