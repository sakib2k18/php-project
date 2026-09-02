@props([
    'raised' => 0,
    'target' => 0,
    'percent' => null,
    'showLabels' => true,
    'tone' => 'brand',
    'size' => 'default',
])

@php
    $target = (float) $target;
    $raised = (float) $raised;
    // Never above 100%, so an over-funded campaign cannot break the bar.
    $value = $percent !== null
        ? min(100, max(0, (float) $percent))
        : ($target > 0 ? min(100, round(($raised / $target) * 100, 1)) : 0);
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if ($showLabels)
        <div class="mb-1.5 flex items-baseline justify-between gap-3">
            <span class="text-sm font-bold text-ink-900">{{ money($raised) }}</span>
            <span class="text-xs font-semibold text-ink-500">
                of {{ money($target) }}
            </span>
        </div>
    @endif

    <div
        class="progress-track {{ $size === 'sm' ? 'h-1.5' : '' }}"
        role="progressbar"
        aria-valuenow="{{ $value }}"
        aria-valuemin="0"
        aria-valuemax="100"
        aria-label="{{ $value }}% of the target raised"
    >
        <div
            class="progress-fill {{ $tone === 'accent' ? 'progress-fill-accent' : '' }}"
            data-progress="{{ $value }}"
        ></div>
    </div>

    @if ($showLabels)
        <div class="mt-1.5 flex items-center justify-between gap-3 text-xs">
            <span class="font-bold text-brand-700">{{ rtrim(rtrim(number_format($value, 1), '0'), '.') }}% funded</span>
            @if ($target > $raised)
                <span class="text-ink-500">{{ money($target - $raised) }} to go</span>
            @else
                <span class="font-semibold text-brand-600">Target reached</span>
            @endif
        </div>
    @endif
</div>
