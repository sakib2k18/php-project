@extends('layouts.app')

@section('title', $project->title.' — '.$site->name())
@section('description', Str::limit($project->summary, 155))
@section('og:type', 'article')

@section('content')

    @php
        $icons = [
            'poverty_relief' => 'hand-heart', 'natural_disaster' => 'storm',
            'education' => 'academic', 'medical' => 'medical',
            'food_distribution' => 'sprout', 'emergency_relief' => 'shield-check',
            'winter_support' => 'snow', 'orphan_support' => 'users', 'other' => 'globe',
        ];
    @endphp

    <x-layout.page-hero
        :eyebrow="$project->category_label"
        :title="$project->title"
        :description="$project->summary"
        :breadcrumbs="['Projects' => route('projects.index'), Str::limit($project->title, 40) => null]"
    />

    <section class="section">
        <div class="shell">
            <div class="grid gap-10 lg:grid-cols-[1fr_20rem] lg:gap-12">
                <div class="min-w-0">
                    <x-ui.media
                        :src="$project->image_url"
                        :alt="$project->title"
                        :seed="$project->slug"
                        :icon="$icons[$project->category] ?? 'globe'"
                        :label="$project->category_label"
                        ratio="aspect-[16/7]"
                        rounded="rounded-2xl"
                        :eager="true"
                    />

                    <div class="prose-kt mt-8">{!! $project->description !!}</div>

                    @if ($project->has_coordinates)
                        <div class="mt-10">
                            <h2 class="display text-2xl text-ink-900">Project location</h2>
                            <p class="mt-1.5 text-sm text-ink-500">{{ $project->location }}</p>
                            <x-ui.map
                                class="mt-5"
                                :latitude="$project->latitude"
                                :longitude="$project->longitude"
                                :title="$project->title"
                                :zoom="11"
                                height="h-72"
                            />
                        </div>
                    @endif
                </div>

                <aside class="lg:sticky lg:top-24 lg:h-fit">
                    <div class="panel p-5">
                        <h2 class="panel-title mb-4">Project summary</h2>

                        <dl class="space-y-4 text-sm">
                            <div class="flex items-start justify-between gap-3">
                                <dt class="text-ink-500">Status</dt>
                                <dd><x-ui.status-badge :status="$project->status" :label="$project->status_label" /></dd>
                            </div>
                            <div class="flex items-start justify-between gap-3 border-t border-ink-100 pt-4">
                                <dt class="text-ink-500">Category</dt>
                                <dd class="text-right font-semibold text-ink-900">{{ $project->category_label }}</dd>
                            </div>
                            @if ($project->location)
                                <div class="flex items-start justify-between gap-3 border-t border-ink-100 pt-4">
                                    <dt class="text-ink-500">Location</dt>
                                    <dd class="text-right font-semibold text-ink-900">{{ $project->location }}</dd>
                                </div>
                            @endif
                            <div class="flex items-start justify-between gap-3 border-t border-ink-100 pt-4">
                                <dt class="text-ink-500">Started</dt>
                                <dd class="text-right font-semibold text-ink-900">{{ $project->start_date->format('j M Y') }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-3 border-t border-ink-100 pt-4">
                                <dt class="text-ink-500">{{ $project->end_date ? 'Completed' : 'Ends' }}</dt>
                                <dd class="text-right font-semibold text-ink-900">{{ $project->end_date?->format('j M Y') ?? 'Ongoing' }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-3 border-t border-ink-100 pt-4">
                                <dt class="text-ink-500">People reached</dt>
                                <dd class="text-right font-bold text-brand-700">{{ number_format($project->beneficiaries_count) }}</dd>
                            </div>
                        </dl>

                        <a href="{{ route('donations.create') }}" class="btn btn-primary btn-block mt-6">
                            <x-ui.icon name="heart" class="size-4" /> Fund work like this
                        </a>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="section bg-white">
            <div class="shell">
                <x-ui.section-heading eyebrow="Related work" title="More {{ Str::lower($project->category_label) }} projects" />

                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $item)
                        <x-project-card :project="$item" class="reveal" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endsection
