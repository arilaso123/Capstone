@props([
    'wrapperClass' => '',
    'validationTitle' => 'Mohon periksa kembali isian Anda:',
])

@php
    $notifications = [
        'success' => ['tone' => 'success', 'icon' => 'bi-check-circle-fill', 'role' => 'status', 'live' => 'polite'],
        'error' => ['tone' => 'danger', 'icon' => 'bi-exclamation-triangle-fill', 'role' => 'alert', 'live' => 'assertive'],
        'warning' => ['tone' => 'warning', 'icon' => 'bi-exclamation-circle-fill', 'role' => 'status', 'live' => 'polite'],
        'info' => ['tone' => 'info', 'icon' => 'bi-info-circle-fill', 'role' => 'status', 'live' => 'polite'],
    ];

    $hasFlash = $errors->any() || collect(array_keys($notifications))->contains(fn ($type) => session()->has($type));
@endphp

@if($hasFlash)
    <div class="ui-flash-stack {{ $wrapperClass }}">
        @foreach($notifications as $type => $notification)
            @if(session()->has($type))
                <div class="alert alert-{{ $notification['tone'] }} alert-dismissible fade show card-uyuy border-{{ $notification['tone'] }}"
                     role="{{ $notification['role'] }}" aria-live="{{ $notification['live'] }}" aria-atomic="true">
                    <i class="bi {{ $notification['icon'] }} me-2" aria-hidden="true"></i>
                    {{ session($type) }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup notifikasi"></button>
                </div>
            @endif
        @endforeach

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show card-uyuy border-danger" role="alert" aria-live="assertive" aria-atomic="true">
                <i class="bi bi-x-circle-fill me-2" aria-hidden="true"></i>
                <strong>{{ $validationTitle }}</strong>
                <ul class="mb-0 mt-1 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup notifikasi"></button>
            </div>
        @endif
    </div>
@endif
