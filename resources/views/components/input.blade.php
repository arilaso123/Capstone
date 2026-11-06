@props([
    'name',
    'label' => null,
    'type' => 'text',
    'icon' => null,
    'placeholder' => '',
    'value' => '',
    'required' => false
])

<div class="mb-4">
    @if($label)
        <label for="{{ $name }}" class="form-label small fw-semibold text-secondary mb-1">
            {{ $label }} @if($required)<span class="text-danger">*</span>@endif
        </label>
    @endif

    <div class="input-group-uyuy d-flex align-items-center @error($name) border-danger @enderror">
        @if($icon)
            <span class="input-group-text"><i class="{{ $icon }}"></i></span>
        @endif
        <input 
            type="{{ $type }}" 
            name="{{ $name }}" 
            id="{{ $name }}" 
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $attributes->merge(['class' => 'form-control form-control-uyuy']) }}
        >
    </div>

    @error($name)
        <div class="text-danger small mt-1 ps-1" style="font-size: 0.82rem;">
            <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
        </div>
    @enderror
</div>

