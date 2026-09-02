@props([
    'campaign',
    'compact' => false,
])

@php
    $icons = [
        'poverty_relief' => 'hand-heart',
        'natural_disaster' => 'storm',
        'education' => 'academic',
        'medical' => 'medical',
        'food_distribution' => 'sprout',
        'emergency_relief' => 'shield-check',
        'winter_support' => 'snow',
        'orphan_support' => 'users',
        'other' => 'sparkles',
    ];
@endphp

<article {{ $attributes->merge(['class' => 'card card-hover group flex flex-col overflow-hidden']) }}>
    <a href="{{ route('campaigns.show', $campaign) }}" class="relative block" tabindex="-1" aria-hidden="true">
        <x-ui.media
            :src="$campaign->image_url"
            :alt="$campaign->title"
            :seed="$campaign->slug"
            :icon="$icons[$campaign->category] ?? 'sparkles'"
            :label="$campaign->category_label"
            ratio="aspect-[16/10]"
            rounded="rounded-none"
        />

        <div class="absolute inset-x-0 top-0 flex items-start justify-between gap-2 p-3">
            <x-ui.badge tone="brand" class="bg-white/95 backdrop-blur">{{ $campaign->category_label }}</x-ui.badge>

            @if ($campaign->is_emergency)
                <x-ui.badge tone="danger" icon="exclamation" class="bg-white/95 backdrop-blur">Emergency</x-ui.badge>
            @elseif ($campaign->featured)
                <x-ui.badge tone="accent" icon="sparkles" class="bg-white/95 backdrop-blur">Featured</x-ui.badge>
            @endif
        </div>
    </a>

    <div class="flex flex-1 flex-col p-5">
        <div class="mb-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-ink-500">
            <x-ui.status-badge :status="$campaign->status" :label="$campaign->status_label" />

            @if ($campaign->location)
                <span class="inline-flex min-w-0 items-center gap-1">
                    <x-ui.icon name="map-pin" class="size-3.5 shrink-0 text-ink-400" />
                    <span class="truncate">{{ $campaign->location }}</span>
                </span>
            @endif
        </div>

        <h3 class="text-base font-bold leading-snug text-ink-900">
            <a href="{{ route('campaigns.show', $campaign) }}" class="transition hover:text-brand-700">
                {{ $campaign->title }}
            </a>
        </h3>

        @unless ($compact)
            <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-ink-600">{{ $campaign->short_description }}</p>
        @endunless

        <div class="mt-4">
            <x-ui.progress
                :raised="$campaign->raised_amount"
                :target="$campaign->target_amount"
                :tone="$campaign->is_emergency ? 'accent' : 'brand'"
            />
        </div>

        <div class="mt-4 flex items-center justify-between gap-2 border-t border-ink-100 pt-4 text-xs text-ink-500">
            @if ($campaign->days_left !== null && $campaign->is_open)
                <span class="inline-flex items-center gap-1 font-semibold {{ $campaign->days_left <= 7 ? 'text-accent-600' : '' }}">
                    <x-ui.icon name="clock" class="size-3.5" />
                    {{ $campaign->days_left }} {{ Str::plural('day', $campaign->days_left) }} left
                </span>
            @elseif ($campaign->status === \App\Models\Campaign::STATUS_COMPLETED)
                <span class="inline-flex items-center gap-1 font-semibold text-brand-700">
                    <x-ui.icon name="check-badge" class="size-3.5" /> Completed
                </span>
            @else
                <span class="inline-flex items-center gap-1">
                    <x-ui.icon name="calendar" class="size-3.5" />
                    {{ $campaign->start_date->format('j M Y') }}
                </span>
            @endif

            @if ($campaign->beneficiaries_count)
                <span class="inline-flex items-center gap-1">
                    <x-ui.icon name="users" class="size-3.5" />
                    {{ compact_number($campaign->beneficiaries_count) }} helped
                </span>
            @endif
        </div>

        <div class="mt-4 flex gap-2">
            <a href="{{ route('campaigns.show', $campaign) }}" class="btn btn-outline btn-sm flex-1">
                View details
            </a>

            @if ($campaign->is_open)
                <a href="{{ route('donations.create', ['campaign' => $campaign->slug]) }}" class="btn btn-primary btn-sm flex-1">
                    <x-ui.icon name="heart" class="size-4" /> Donate
                </a>
            @endif
        </div>
    </div>
</article>
