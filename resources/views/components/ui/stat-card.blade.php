@props([
    'label',
    'value',
    'icon' => 'chart',
    'tone' => 'brand',
    'hint' => null,
    'trend' => null,
    'href' => null,
])

@php
    $tones = [
        'brand'   => 'bg-brand-50 text-brand-600 ring-brand-100',
        'accent'  => 'bg-accent-50 text-accent-600 ring-accent-100',
        'success' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
        'warning' => 'bg-amber-50 text-amber-600 ring-amber-100',
        'danger'  => 'bg-rose-50 text-rose-600 ring-rose-100',
        'info'    => 'bg-sky-50 text-sky-600 ring-sky-100',
        'neutral' => 'bg-ink-100 text-ink-600 ring-ink-200',
    ];

    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'card card-hover group flex items-start gap-3.5 p-4 sm:p-5']) }}
>
    <span class="grid size-10 shrink-0 place-items-center rounded-xl ring-1 {{ $tones[$tone] ?? $tones['brand'] }}">
        <x-ui.icon :name="$icon" class="size-5" />
    </span>

    <div class="min-w-0 flex-1">
        {{-- Wraps rather than truncates: a clipped label like "Verified donatio…"
             is worse than two short lines. --}}
        <p class="text-[11px] font-bold uppercase leading-tight tracking-[0.09em] text-ink-500">{{ $label }}</p>
        <p class="stat-value mt-1 text-2xl leading-none">{{ $value }}</p>

        @if ($hint || $trend)
            <p class="mt-1.5 flex items-center gap-1 text-xs text-ink-500">
                @if ($trend !== null)
                    <span class="inline-flex items-center gap-0.5 font-semibold {{ $trend >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        <x-ui.icon :name="$trend >= 0 ? 'arrow-up' : 'arrow-down'" class="size-3" />
                        {{ abs($trend) }}
                    </span>
                @endif
                @if ($hint)<span class="truncate">{{ $hint }}</span>@endif
            </p>
        @endif
    </div>

    @if ($href)
        <x-ui.icon name="chevron-right" class="mt-1 size-4 shrink-0 text-ink-300 transition group-hover:translate-x-0.5 group-hover:text-brand-600" />
    @endif
</{{ $tag }}>
