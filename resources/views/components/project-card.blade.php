@props(['project'])

@php
    $icons = [
        'poverty_relief' => 'hand-heart', 'natural_disaster' => 'storm',
        'education' => 'academic', 'medical' => 'medical',
        'food_distribution' => 'sprout', 'emergency_relief' => 'shield-check',
        'winter_support' => 'snow', 'orphan_support' => 'users', 'other' => 'globe',
    ];
@endphp

<article {{ $attributes->merge(['class' => 'card card-hover group flex flex-col overflow-hidden']) }}>
    <a href="{{ route('projects.show', $project) }}" class="relative block" tabindex="-1" aria-hidden="true">
        <x-ui.media
            :src="$project->image_url"
            :alt="$project->title"
            :seed="$project->slug"
            :icon="$icons[$project->category] ?? 'globe'"
            :label="$project->category_label"
            ratio="aspect-[16/10]"
            rounded="rounded-none"
        />
        <div class="absolute left-3 top-3">
            <x-ui.status-badge :status="$project->status" :label="$project->status_label" class="bg-white/95 backdrop-blur" />
        </div>
    </a>

    <div class="flex flex-1 flex-col p-5">
        <p class="eyebrow text-[11px]">{{ $project->category_label }}</p>

        <h3 class="mt-2 text-base font-bold leading-snug text-ink-900">
            <a href="{{ route('projects.show', $project) }}" class="transition hover:text-brand-700">{{ $project->title }}</a>
        </h3>

        <p class="mt-2 line-clamp-2 flex-1 text-sm leading-relaxed text-ink-600">{{ $project->summary }}</p>

        <dl class="mt-4 grid grid-cols-2 gap-3 border-t border-ink-100 pt-4 text-xs">
            <div>
                <dt class="font-semibold uppercase tracking-wider text-ink-400">Reached</dt>
                <dd class="mt-0.5 text-sm font-bold text-ink-900">{{ compact_number($project->beneficiaries_count) }} people</dd>
            </div>
            <div>
                <dt class="font-semibold uppercase tracking-wider text-ink-400">Location</dt>
                <dd class="mt-0.5 truncate text-sm font-bold text-ink-900">{{ $project->location ?? '—' }}</dd>
            </div>
        </dl>

        <a href="{{ route('projects.show', $project) }}" class="link-arrow mt-4">
            Read the full story <x-ui.icon name="arrow-right" class="size-4" />
        </a>
    </div>
</article>
