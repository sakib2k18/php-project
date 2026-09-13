@props([
    'name',
    'label',
    'options' => [],
    'value' => null,
    'placeholder' => 'Choose…',
    'help' => null,
    'rules' => null,
    'required' => false,
])

@php
    $id = $attributes->get('id') ?? $name;
    $current = old($name, $value);
    $selected = array_map('strval', is_array($current) ? $current : [$current]);

    // `required|array` fails on the field itself, but a `field.*` rule fails on
    // an indexed key — check both so a tampered value still highlights.
    $hasError = $errors->has($name) || $errors->has("{$name}.*");
    $errorText = $errors->first($name) ?: $errors->first("{$name}.*");

    // Server-render the closed state too, so the button never flashes the
    // placeholder over an existing selection (edit form, or a failed submit).
    $chosen = collect($options)
        ->filter(fn ($optionLabel, $optionValue) => in_array((string) $optionValue, $selected, true))
        ->values();
    $summary = match (true) {
        $chosen->isEmpty() => $placeholder,
        $chosen->count() <= 3 => $chosen->implode(', '),
        default => $chosen->count().' activities selected',
    };
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }} data-multiselect>
    <label id="{{ $id }}-label" class="label">
        {{ $label }}
        @if ($required)<span class="text-rose-500" aria-hidden="true">*</span>@endif
    </label>

    {{--
        The actual form control. It is the single source of truth: the server
        renders the current selection into it and multiselect.js mirrors that
        into the dropdown. Without JavaScript it stays visible and usable, and
        the toggle button below stays hidden.
    --}}
    <select id="{{ $id }}" name="{{ $name }}[]" multiple size="4"
        data-multiselect-native class="field mb-2" aria-label="{{ $label }}"
        @if ($hasError) aria-invalid="true" @endif>
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected(in_array((string) $optionValue, $selected, true))>{{ $optionLabel }}</option>
        @endforeach
    </select>

    <div class="relative">
        {{--
            Carries `data-rules` rather than the hidden <select>, so the shared
            validation module styles and focuses the control the user can
            actually see. multiselect.js keeps button.value in step with the
            selection, which is what `required` reads.
        --}}
        <button type="button"
            data-multiselect-toggle
            value="{{ $chosen->implode(',') }}"
            @if ($rules) data-rules="{{ $rules }}" data-label="{{ $label }}" @endif
            aria-expanded="false" aria-haspopup="listbox" aria-controls="{{ $id }}-panel"
            aria-labelledby="{{ $id }}-label" aria-describedby="{{ $id }}-error"
            @if ($hasError) aria-invalid="true" @endif
            class="field flex items-center justify-between gap-2 text-left {{ $hasError ? 'field-error' : '' }}">
            <span data-multiselect-summary data-placeholder="{{ $placeholder }}"
                  class="truncate @if ($chosen->isEmpty()) text-ink-400 @endif">{{ $summary }}</span>
            <x-ui.icon name="chevron-down" class="size-4 shrink-0 text-ink-400" />
        </button>

        <div id="{{ $id }}-panel" data-multiselect-panel hidden
             class="absolute inset-x-0 z-50 mt-2 max-h-64 overflow-y-auto rounded-xl border border-ink-100 bg-white p-1.5 shadow-[var(--shadow-deep)]">
            @foreach ($options as $optionValue => $optionLabel)
                <label class="flex cursor-pointer items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-ink-700 transition hover:bg-brand-50">
                    <input type="checkbox" value="{{ $optionValue }}" class="checkbox" @checked(in_array((string) $optionValue, $selected, true))>
                    <span>{{ $optionLabel }}</span>
                </label>
            @endforeach
        </div>
    </div>

    @if ($help)
        <p class="help">{{ $help }}</p>
    @endif

    <p id="{{ $id }}-error" class="error-text" @unless ($hasError) hidden @endunless
       @if ($hasError) data-server="true" @endif>{{ $errorText }}</p>
</div>
