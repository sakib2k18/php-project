@props([
    'src' => null,
    'alt' => '',
    'seed' => '',
    'icon' => 'sparkles',
    'label' => null,
    'ratio' => 'aspect-[16/10]',
    'rounded' => 'rounded-xl',
    'eager' => false,
])

@php
    /*
     * When a record has no uploaded image we render a deterministic, designed
     * placeholder instead of a broken frame: the gradient is derived from a hash
     * of the title, so the same campaign always gets the same colours and the
     * grid of cards still looks intentional.
     */
    $palettes = [
        ['#0c644a', '#1c9d70'],
        ['#0d503d', '#3fb98c'],
        ['#9c3e0f', '#fb8710'],
        ['#0c4234', '#75d3ae'],
        ['#c44e08', '#ffc770'],
        ['#05261e', '#0f7d5a'],
    ];

    $index = abs(crc32((string) ($seed ?: $alt))) % count($palettes);
    [$from, $to] = $palettes[$index];
    $angle = 115 + ($index * 17) % 90;
@endphp

<div {{ $attributes->merge(['class' => "relative overflow-hidden bg-ink-100 {$ratio} {$rounded}"]) }}>
    @if ($src)
        <img
            src="{{ $src }}"
            alt="{{ $alt }}"
            loading="{{ $eager ? 'eager' : 'lazy' }}"
            decoding="async"
            class="size-full object-cover transition-transform duration-500 group-hover:scale-[1.04]"
        >
    @else
        <div
            class="grain flex size-full flex-col items-center justify-center gap-2 p-4 text-center"
            style="background-image: linear-gradient({{ $angle }}deg, {{ $from }}, {{ $to }});"
            role="img"
            aria-label="{{ $alt ?: 'Illustration placeholder' }}"
        >
            <x-ui.icon :name="$icon" class="size-8 text-white/85" />
            @if ($label)
                <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-white/80">{{ $label }}</span>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>
