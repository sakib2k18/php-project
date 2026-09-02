@props([
    'name',
    'label',
    'options' => [],
    'value' => null,
    'placeholder' => null,
    'help' => null,
    'rules' => null,
    'required' => false,
])

@php
    $id = $attributes->get('id') ?? $name;
    $current = old($name, $value);
    $hasError = $errors->has($name);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>
    <label for="{{ $id }}" class="label">
        {{ $label }}
        @if ($required)<span class="text-rose-500" aria-hidden="true">*</span>@endif
    </label>

    <select
        id="{{ $id }}"
        name="{{ $name }}"
        @if ($rules) data-rules="{{ $rules }}" data-label="{{ $label }}" @endif
        @if ($required) required @endif
        @if ($hasError) aria-invalid="true" @endif
        aria-describedby="{{ $id }}-error"
        {{ $attributes->except(['class', 'id'])->merge(['class' => 'field'.($hasError ? ' field-error' : '')]) }}
    >
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) $current === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>

    @if ($help)
        <p class="help">{{ $help }}</p>
    @endif

    <p id="{{ $id }}-error" class="error-text" @unless ($hasError) hidden @endunless
       @if ($hasError) data-server="true" @endif>{{ $errors->first($name) }}</p>
</div>
