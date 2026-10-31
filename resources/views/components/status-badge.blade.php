@props(['status'])

@php
    $class = match($status) {
        'Dibuka' => 'badge-po-open',
        'Ditutup' => 'badge-po-closed',
        'Menunggu Pembayaran' => 'badge-status-waiting',
        'Pembayaran Diverifikasi' => 'badge-status-verified',
        'Diproses' => 'badge-status-processing',
        'Barang Dikirim' => 'badge-status-shipped',
        'Selesai' => 'badge-status-completed',
        'Dibatalkan' => 'badge-status-cancelled',
        'Menunggu Persetujuan' => 'badge-status-waiting',
        'Disetujui' => 'badge-status-completed',
        'Ditolak' => 'badge-status-cancelled',
        'Menunggu Verifikasi' => 'badge-status-waiting',
        'Valid' => 'badge-status-completed',
        default => 'badge-po-closed'
    };

    $icon = match($status) {
        'Dibuka' => 'bi-unlock-fill',
        'Ditutup' => 'bi-lock-fill',
        'Menunggu Pembayaran' => 'bi-clock-history',
        'Pembayaran Diverifikasi' => 'bi-check2-circle',
        'Diproses' => 'bi-box-seam',
        'Barang Dikirim' => 'bi-truck',
        'Selesai' => 'bi-patch-check-fill',
        'Dibatalkan' => 'bi-x-circle-fill',
        'Menunggu Persetujuan' => 'bi-hourglass-split',
        'Disetujui' => 'bi-check-lg',
        'Ditolak' => 'bi-slash-circle',
        'Menunggu Verifikasi' => 'bi-search',
        'Valid' => 'bi-check2-all',
        default => 'bi-info-circle'
    };
@endphp

<span class="badge-uyuy {{ $class }}">
    <i class="bi {{ $icon }}"></i>
    {{ $status }}
</span>

