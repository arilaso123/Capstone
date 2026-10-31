@props(['status'])

@php
    $steps = [
        'Menunggu Pembayaran' => ['icon' => 'bi-receipt', 'label' => 'Menunggu Pembayaran'],
        'Pembayaran Diverifikasi' => ['icon' => 'bi-check-circle', 'label' => 'Pembayaran Diverifikasi'],
        'Diproses' => ['icon' => 'bi-boxes', 'label' => 'Pesanan Diproses'],
        'Barang Dikirim' => ['icon' => 'bi-truck', 'label' => 'Barang Dikirim'],
        'Selesai' => ['icon' => 'bi-stars', 'label' => 'Pesanan Selesai']
    ];

    $stepKeys = array_keys($steps);
    $currentIndex = array_search($status, $stepKeys);
    if ($currentIndex === false) {
        $currentIndex = -1; // If Dibatalkan or other
    }
@endphp

@if($status === 'Dibatalkan')
    <div class="alert alert-danger d-flex align-items-center gap-3 my-3 card-uyuy border-danger">
        <i class="bi bi-x-octagon-fill fs-2 text-danger"></i>
        <div>
            <h6 class="mb-0 fw-bold text-danger">Pesanan Dibatalkan</h6>
            <p class="small mb-0 text-muted">Pesanan ini telah resmi dibatalkan sesuai keputusan verifikasi admin.</p>
        </div>
    </div>
@else
    <div class="order-stepper my-4">
        @foreach($steps as $key => $info)
            @php
                $itemIndex = array_search($key, $stepKeys);
                $isCompleted = ($currentIndex !== false && $currentIndex > $itemIndex);
                $isActive = ($currentIndex !== false && $currentIndex === $itemIndex);
            @endphp
            <div class="stepper-item {{ $isCompleted ? 'completed' : '' }} {{ $isActive ? 'active' : '' }}">
                <div class="stepper-circle">
                    @if($isCompleted)
                        <i class="bi bi-check-lg"></i>
                    @else
                        <i class="bi {{ $info['icon'] }}"></i>
                    @endif
                </div>
                <div class="stepper-label">
                    {{ $info['label'] }}
                </div>
            </div>
        @endforeach
    </div>
@endif

