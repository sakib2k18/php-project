@props([
    'name' => 'password',
    'label' => 'Password',
    'rules' => 'required|min:8',
    'autocomplete' => 'current-password',
    'strength' => false,
    'help' => null,
])

@php
    $id = $attributes->get('id') ?? $name;
    $hasError = $errors->has($name);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>
    <label for="{{ $id }}" class="label">{{ $label }} <span class="text-rose-500" aria-hidden="true">*</span></label>

    <div class="relative">
        <input
            type="password"
            id="{{ $id }}"
            name="{{ $name }}"
            autocomplete="{{ $autocomplete }}"
            data-rules="{{ $rules }}"
            data-label="{{ $label }}"
            @if ($strength) data-password-strength="{{ $id }}-strength" @endif
            @if ($hasError) aria-invalid="true" @endif
            aria-describedby="{{ $id }}-error"
            required
            {{ $attributes->except(['class', 'id'])->merge(['class' => 'field pr-11'.($hasError ? ' field-error' : '')]) }}
        >

        <button
            type="button"
            data-password-toggle="{{ $id }}"
            class="absolute inset-y-0 right-0 grid w-11 place-items-center text-ink-400 transition hover:text-ink-700"
            aria-label="Show password"
            aria-pressed="false"
        >
            <x-ui.icon name="eye" class="size-4.5" />
        </button>
    </div>

    @if ($strength)
        <div id="{{ $id }}-strength" class="mt-2">
            <div class="flex gap-1">
                <span data-bar class="h-1 flex-1 rounded-full bg-ink-200"></span>
                <span data-bar class="h-1 flex-1 rounded-full bg-ink-200"></span>
                <span data-bar class="h-1 flex-1 rounded-full bg-ink-200"></span>
                <span data-bar class="h-1 flex-1 rounded-full bg-ink-200"></span>
            </div>
            <p data-strength-label class="mt-1 text-[11px] font-semibold text-ink-500"></p>
        </div>
    @endif

    @if ($help)
        <p class="help">{{ $help }}</p>
    @endif

    <p id="{{ $id }}-error" class="error-text" @unless ($hasError) hidden @endunless
       @if ($hasError) data-server="true" @endif>{{ $errors->first($name) }}</p>
</div>
