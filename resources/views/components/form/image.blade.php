@props([
    'name',
    'label',
    'current' => null,
    'help' => null,
    'aspect' => 'aspect-[16/10]',
])

@php
    $id = $attributes->get('id') ?? $name;
    $hasError = $errors->has($name);
    $maxMb = round(((int) config('site.uploads.max_kb')) / 1024, 1);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>
    <label for="{{ $id }}" class="label">{{ $label }}</label>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-start">
        <div class="w-full shrink-0 sm:w-44">
            <div class="overflow-hidden rounded-xl border border-ink-200 bg-ink-50 {{ $aspect }}">
                <img
                    id="{{ $id }}-preview"
                    src="{{ $current ?: '' }}"
                    alt=""
                    class="size-full object-cover {{ $current ? '' : 'hidden' }}"
                >
                @unless ($current)
                    <div class="grid size-full place-items-center text-ink-300">
                        <x-ui.icon name="photo" class="size-7" />
                    </div>
                @endunless
            </div>
        </div>

        <div class="min-w-0 flex-1">
            <input
                type="file"
                id="{{ $id }}"
                name="{{ $name }}"
                accept="image/jpeg,image/png,image/webp"
                data-image-input
                data-image-preview="{{ $id }}-preview"
                data-image-name="{{ $id }}-filename"
                data-max-kb="{{ config('site.uploads.max_kb') }}"
                aria-describedby="{{ $id }}-error"
                class="block w-full cursor-pointer rounded-xl border border-ink-200 bg-white text-sm text-ink-600
                       file:mr-3 file:cursor-pointer file:rounded-l-xl file:border-0 file:bg-ink-50
                       file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-ink-700
                       hover:file:bg-brand-50 hover:file:text-brand-700 {{ $hasError ? 'field-error' : '' }}"
            >

            <p id="{{ $id }}-filename" class="mt-2 truncate text-xs text-ink-500">No file selected</p>

            <p class="help">
                {{ $help ?? "JPG, PNG or WEBP · up to {$maxMb} MB · maximum ".config('site.uploads.max_dimension').'px on the longest side.' }}
            </p>
        </div>
    </div>

    <p id="{{ $id }}-error" class="error-text" @unless ($hasError) hidden @endunless
       @if ($hasError) data-server="true" @endif>{{ $errors->first($name) }}</p>
</div>
