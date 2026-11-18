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
        <label for="{{ $name }}" class="form-label mb-2">
            {{ $label }} @if($required)<span class="text-danger" aria-hidden="true">*</span>@endif
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
            aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}"
            @if($errors->has($name)) aria-describedby="{{ $name }}-error" @endif
            {{ $required ? 'required' : '' }}
            {{ $attributes->merge(['class' => 'form-control form-control-uyuy']) }}
        >
    </div>

    @error($name)
        <div id="{{ $name }}-error" class="auth-input-error">
            <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
        </div>
    @enderror
</div>

