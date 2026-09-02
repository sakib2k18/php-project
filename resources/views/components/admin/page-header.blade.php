@props([
    'title',
    'description' => null,
    'count' => null,
])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between']) }}>
    <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-2.5">
            <h2 class="display text-2xl text-ink-900">{{ $title }}</h2>
            @if ($count !== null)
                <x-ui.badge tone="neutral">{{ number_format($count) }}</x-ui.badge>
            @endif
        </div>

        @if ($description)
            <p class="mt-1.5 max-w-2xl text-sm leading-relaxed text-ink-500">{{ $description }}</p>
        @endif
    </div>

    @if (! $slot->isEmpty())
        <div class="flex shrink-0 flex-wrap gap-2">{{ $slot }}</div>
    @endif
</div>
