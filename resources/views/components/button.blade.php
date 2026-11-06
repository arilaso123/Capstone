@props([
    'type' => 'button',
    'variant' => 'mocha',
    'icon' => null,
    'class' => ''
])

<button type="{{ $type }}" {{ $attributes->merge(['class' => "btn btn-{$variant} " . $class]) }}>
    @if($icon)
        <i class="{{ $icon }} me-1"></i>
    @endif
    {{ $slot }}
</button>

