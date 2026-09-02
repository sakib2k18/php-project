@props([
    'name',
    'label',
    'value' => null,
    'placeholder' => null,
    'help' => null,
    'rules' => null,
    'required' => false,
    'rows' => 5,
    'maxlength' => null,
])

@php
    $id = $attributes->get('id') ?? $name;
    $current = old($name, $value);
    $hasError = $errors->has($name);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>
    <div class="flex items-baseline justify-between gap-3">
        <label for="{{ $id }}" class="label">
            {{ $label }}
            @if ($required)<span class="text-rose-500" aria-hidden="true">*</span>@endif
        </label>

        @if ($maxlength)
            <span id="{{ $id }}-count" class="mb-1.5 text-[11px] font-medium tabular-nums text-ink-400"></span>
        @endif
    </div>

    <textarea
        id="{{ $id }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if ($maxlength) maxlength="{{ $maxlength }}" data-char-count="{{ $id }}-count" @endif
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($rules) data-rules="{{ $rules }}" data-label="{{ $label }}" @endif
        @if ($required) required @endif
        @if ($hasError) aria-invalid="true" @endif
        aria-describedby="{{ $id }}-error"
        {{ $attributes->except(['class', 'id'])->merge(['class' => 'field'.($hasError ? ' field-error' : '')]) }}
    >{{ $current }}</textarea>

    @if ($help)
        <p class="help">{{ $help }}</p>
    @endif

    <p id="{{ $id }}-error" class="error-text" @unless ($hasError) hidden @endunless
       @if ($hasError) data-server="true" @endif>{{ $errors->first($name) }}</p>
</div>
