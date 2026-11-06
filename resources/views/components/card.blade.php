@props(['class' => '', 'interactive' => false])

<div {{ $attributes->merge(['class' => 'card card-uyuy ' . ($interactive ? 'card-uyuy-interactive ' : '') . $class]) }}>
    {{ $slot }}
</div>

