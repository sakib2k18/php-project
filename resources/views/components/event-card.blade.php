@props(['event', 'past' => false])

<article {{ $attributes->merge(['class' => 'card card-hover group flex gap-4 overflow-hidden p-4 sm:p-5']) }}>
    {{-- Date chip reads at a glance on every screen size. --}}
    <div class="flex size-16 shrink-0 flex-col items-center justify-center rounded-xl {{ $past ? 'bg-ink-100 text-ink-500' : 'bg-brand-600 text-white' }} sm:size-[4.5rem]">
        <span class="text-[10px] font-bold uppercase tracking-wider opacity-80">{{ $event->event_date->format('M') }}</span>
        <span class="display text-2xl leading-none">{{ $event->event_date->format('j') }}</span>
        <span class="text-[10px] font-semibold opacity-70">{{ $event->event_date->format('Y') }}</span>
    </div>

    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-2">
            @if ($past)
                <x-ui.badge tone="neutral" icon="check">Completed</x-ui.badge>
            @else
                <x-ui.badge tone="success" icon="calendar">Upcoming</x-ui.badge>
            @endif

            @if ($event->time_range)
                <span class="inline-flex items-center gap-1 text-xs text-ink-500">
                    <x-ui.icon name="clock" class="size-3.5" /> {{ $event->time_range }}
                </span>
            @endif
        </div>

        <h3 class="mt-2 text-base font-bold leading-snug text-ink-900">
            <a href="{{ route('events.show', $event) }}" class="transition hover:text-brand-700">{{ $event->title }}</a>
        </h3>

        @if ($event->location)
            <p class="mt-1.5 flex items-start gap-1.5 text-xs text-ink-500">
                <x-ui.icon name="map-pin" class="mt-px size-3.5 shrink-0 text-ink-400" />
                <span class="line-clamp-1">{{ $event->location }}</span>
            </p>
        @endif

        <a href="{{ route('events.show', $event) }}" class="link-arrow mt-3">
            Event details <x-ui.icon name="arrow-right" class="size-4" />
        </a>
    </div>
</article>
