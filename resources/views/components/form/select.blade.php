@props([
    'name',
    'label',
    'options' => [],
    'value' => null,
    'placeholder' => null,
    'help' => null,
    'rules' => null,
    'required' => false,
    'multiple' => false,
    'size' => null,
])

@php
    $id = $attributes->get('id') ?? $name;
    $current = old($name, $value);

    // A multi-select posts `name[]`, so the bound value is a list. A single
    // value is still tolerated so the component can be reused either way.
    $selected = $multiple
        ? array_map('strval', is_array($current) ? $current : [$current])
        : [];

    // `required|array` fails on the field itself, but a `field.*` rule fails on
    // an indexed key — check both so a tampered value still highlights.
    $hasError = $errors->has($name) || $errors->has("{$name}.*");
    $errorText = $errors->first($name) ?: $errors->first("{$name}.*");
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>
    <label for="{{ $id }}" class="label">
        {{ $label }}
        @if ($required)<span class="text-rose-500" aria-hidden="true">*</span>@endif
    </label>

    <select
        id="{{ $id }}"
        name="{{ $multiple ? $name.'[]' : $name }}"
        @if ($rules) data-rules="{{ $rules }}" data-label="{{ $label }}" @endif
        @if ($required) required @endif
        @if ($multiple) multiple @endif
        @if ($multiple) size="{{ $size ?? 6 }}" @endif
        @if ($hasError) aria-invalid="true" @endif
        aria-describedby="{{ $id }}-error"
        {{ $attributes->except(['class', 'id'])->merge(['class' => 'field'.($hasError ? ' field-error' : '')]) }}
    >
        {{-- A placeholder cannot be "selected" in a multi-select, so omit it. --}}
        @if ($placeholder && ! $multiple)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach ($options as $optionValue => $optionLabel)
            @if ($multiple)
                <option value="{{ $optionValue }}" @selected(in_array((string) $optionValue, $selected, true))>{{ $optionLabel }}</option>
            @else
                <option value="{{ $optionValue }}" @selected((string) $current === (string) $optionValue)>{{ $optionLabel }}</option>
            @endif
        @endforeach
    </select>

    @if ($help)
        <p class="help">{{ $help }}</p>
    @endif

    <p id="{{ $id }}-error" class="error-text" @unless ($hasError) hidden @endunless
       @if ($hasError) data-server="true" @endif>{{ $errorText }}</p>
</div>
