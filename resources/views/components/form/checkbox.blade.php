@props([
    'name',
    'label',
    'checked' => false,
    'help' => null,
    'rules' => null,
    'value' => 1,
])

@php
    $id = $attributes->get('id') ?? $name;
    $isChecked = old($name, $checked);
    $hasError = $errors->has($name);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>
    <label for="{{ $id }}" class="flex cursor-pointer items-start gap-2.5">
        {{-- Hidden default so an unticked box still posts a value. --}}
        <input type="hidden" name="{{ $name }}" value="0">

        <input
            type="checkbox"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ $value }}"
            @checked($isChecked)
            @if ($rules) data-rules="{{ $rules }}" data-label="{{ $label }}" @endif
            aria-describedby="{{ $id }}-error"
            {{ $attributes->except(['class', 'id'])->merge(['class' => 'checkbox mt-0.5']) }}
        >

        <span class="min-w-0 flex-1">
            <span class="block text-sm font-medium leading-snug text-ink-800">{{ $label }}</span>
            @if ($help)
                <span class="mt-0.5 block text-xs leading-relaxed text-ink-500">{{ $help }}</span>
            @endif
            {{ $slot }}
        </span>
    </label>

    <p id="{{ $id }}-error" class="error-text" @unless ($hasError) hidden @endunless
       @if ($hasError) data-server="true" @endif>{{ $errors->first($name) }}</p>
</div>
