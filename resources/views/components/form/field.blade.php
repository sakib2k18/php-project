@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'help' => null,
    'rules' => null,
    'required' => false,
    'icon' => null,
    'prefix' => null,
])

@php
    $id = $attributes->get('id') ?? $name;
    // old() wins so a failed submission never loses what the visitor typed.
    $current = old($name, $value);
    $hasError = $errors->has($name);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>
    <label for="{{ $id }}" class="label">
        {{ $label }}
        @if ($required)<span class="text-rose-500" aria-hidden="true">*</span>@endif
    </label>

    <div class="relative">
        @if ($icon)
            <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-ink-400">
                <x-ui.icon :name="$icon" class="size-4.5" />
            </span>
        @elseif ($prefix)
            <span class="pointer-events-none absolute inset-y-0 left-0 grid w-11 place-items-center text-sm font-semibold text-ink-400">{{ $prefix }}</span>
        @endif

        <input
            type="{{ $type }}"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ $type === 'password' ? '' : $current }}"
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($rules) data-rules="{{ $rules }}" data-label="{{ $label }}" @endif
            @if ($required) required @endif
            @if ($hasError) aria-invalid="true" @endif
            aria-describedby="{{ $id }}-error"
            {{ $attributes->except(['class', 'id'])->merge([
                'class' => 'field'
                    .($icon || $prefix ? ' pl-10' : '')
                    .($hasError ? ' field-error' : ''),
            ]) }}
        >

        {{ $slot }}
    </div>

    @if ($help)
        <p class="help">{{ $help }}</p>
    @endif

    <p id="{{ $id }}-error" class="error-text" @unless ($hasError) hidden @endunless
       @if ($hasError) data-server="true" @endif>{{ $errors->first($name) }}</p>
</div>
