@props([
    'fallback' => '',
    'label' => 'Kembali',
])

<button
    type="button"
    {{ $attributes->merge(['class' => 'editorial-back-btn']) }}
    data-fallback-url="{{ $fallback }}"
    aria-label="Kembali ke halaman sebelumnya"
>
    <i class="bi bi-arrow-left" aria-hidden="true"></i>
    <span>{{ $label }}</span>
</button>
