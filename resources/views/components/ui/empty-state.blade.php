@props([
    'icon' => 'inbox',
    'title' => 'Nothing here yet',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center rounded-2xl border border-dashed border-ink-200 bg-white/60 px-6 py-14 text-center']) }}>
    <span class="grid size-14 place-items-center rounded-2xl bg-ink-50 text-ink-400">
        <x-ui.icon :name="$icon" class="size-7" />
    </span>

    <h3 class="mt-4 text-base font-bold text-ink-900">{{ $title }}</h3>

    @if ($description)
        <p class="mt-1.5 max-w-sm text-sm leading-relaxed text-ink-500">{{ $description }}</p>
    @endif

    @if (! $slot->isEmpty())
        <div class="mt-5 flex flex-wrap items-center justify-center gap-2">{{ $slot }}</div>
    @endif
</div>
