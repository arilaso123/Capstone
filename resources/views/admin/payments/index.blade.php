@extends('layouts.admin')

@section('title', 'Pemeriksaan Pembayaran Manual')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 fw-bold mb-1 font-heading">Pemeriksaan Pembayaran Manual (BCA Merchant)</h2>
        <span class="text-muted small">Verifikasi berkas transfer bukti pembayaran dari pelanggan secara manual (FR-11).</span>
    </div>
</div>

<!-- Alert Reminder -->
<div class="alert alert-warning border-warning d-flex align-items-center gap-3 card-uyuy mb-4">
    <i class="bi bi-exclamation-triangle-fill text-warning fs-3"></i>
    <div class="small">
        <strong>Penting untuk Staf Admin:</strong> Uyuy Jastip tidak menggunakan integrasi API BCA otomatis. Setiap bukti transfer harus dicocokkan dengan mutasi rekening BCA Merchant sebelum mengubah status menjadi <strong>Valid</strong>. Berkas bukti disimpan maksimal 1 bulan (FR-22).
    </div>
</div>

<div class="card card-uyuy p-4">
    <!-- Filter Tabs -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('admin.payments.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-dark' : 'btn-outline-secondary' }}">
            Semua Bukti
        </a>
        <a href="{{ route('admin.payments.index', ['status' => \App\Models\Payment::STATUS_PENDING]) }}"
           class="btn btn-sm {{ request('status') === \App\Models\Payment::STATUS_PENDING ? 'btn-dark' : 'btn-outline-secondary' }}">
            Menunggu Verifikasi
        </a>
        <a href="{{ route('admin.payments.index', ['status' => \App\Models\Payment::STATUS_VALID]) }}"
           class="btn btn-sm {{ request('status') === \App\Models\Payment::STATUS_VALID ? 'btn-dark' : 'btn-outline-secondary' }}">
            Valid
        </a>
        <a href="{{ route('admin.payments.index', ['status' => \App\Models\Payment::STATUS_REJECTED]) }}"
           class="btn btn-sm {{ request('status') === \App\Models\Payment::STATUS_REJECTED ? 'btn-dark' : 'btn-outline-secondary' }}">
            Ditolak
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
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
                            <strong class="text-dark">{{ $payment->order?->order_number ?? '-' }}</strong>
                            <div class="small text-muted">{{ $payment->order?->poPeriod?->name ?? '-' }}</div>
                        </td>
                        <td>{{ $payment->order?->customer?->full_name ?? $payment->order?->recipient_name }}</td>
                        <td>{{ $payment->payment_method ?? 'BCA Merchant' }}</td>
                        <td class="fw-bold">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                        <td>{{ $payment->uploaded_at ? $payment->uploaded_at->format('d M Y, H:i') : '-' }}</td>
                        <td>
                            <span class="badge {{ $payment->isValid() ? 'bg-success' : ($payment->isRejected() ? 'bg-danger' : 'bg-warning text-dark') }}">
                                {{ $payment->verification_status }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.payments.verify', $payment->id) }}" class="btn btn-sm {{ $payment->isPending() ? 'btn-mocha' : 'btn-outline-secondary' }}">
                                <i class="bi {{ $payment->isPending() ? 'bi-check-square' : 'bi-eye' }} me-1"></i>
                                {{ $payment->isPending() ? 'Periksa Bukti' : 'Lihat Detail' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            Tidak ada data bukti transfer yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($payments->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $payments->links() }}
        </div>
    @endif
</div>
@endsection
