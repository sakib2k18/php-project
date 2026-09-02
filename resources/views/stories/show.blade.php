@extends('layouts.app')

@section('title', $story->title.' — '.$site->name())
@section('description', Str::limit(strip_tags($story->story), 155))
@section('og:type', 'article')

@section('content')

    <x-layout.page-hero
        eyebrow="Impact story"
        :title="$story->title"
        :breadcrumbs="['Stories' => route('stories.index'), Str::limit($story->title, 40) => null]"
    />

    <section class="section">
        <div class="shell">
            <div class="mx-auto max-w-3xl">
                <x-ui.media
                    :src="$story->image_url"
                    :alt="$story->title"
                    :seed="$story->slug"
                    icon="heart"
                    label="Impact story"
                    ratio="aspect-[16/8]"
                    rounded="rounded-2xl"
                    :eager="true"
                />

                {{-- Beneficiary card --}}
                @if ($story->beneficiary_name)
                    <div class="card mt-8 flex flex-wrap items-center gap-4 p-5">
                        <x-ui.avatar :initials="Str::upper(Str::substr($story->beneficiary_name, 0, 1))" size="lg" />
                        <div class="min-w-0 flex-1">
                            <p class="text-base font-bold text-ink-900">{{ $story->beneficiary_name }}</p>
                            @if ($story->beneficiary_description)
                                <p class="mt-0.5 text-sm leading-relaxed text-ink-600">{{ $story->beneficiary_description }}</p>
                            @endif
                            <p class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-ink-500">
                                @if ($story->location)
                                    <span class="flex items-center gap-1.5"><x-ui.icon name="map-pin" class="size-3.5" /> {{ $story->location }}</span>
                                @endif
                                <span class="flex items-center gap-1.5"><x-ui.icon name="calendar" class="size-3.5" /> {{ $story->story_date->format('F Y') }}</span>
                            </p>
                        </div>
                    </div>
                @endif

                <div class="prose-kt mt-8">{!! $story->story !!}</div>

                {{-- Linked campaign --}}
                @if ($story->campaign)
                    <div class="mt-10 rounded-2xl border border-brand-100 bg-brand-50 p-5 sm:p-6">
                        <p class="eyebrow">This was made possible by</p>
                        <h2 class="mt-2 text-lg font-bold text-ink-900">
                            <a href="{{ route('campaigns.show', $story->campaign) }}" class="transition hover:text-brand-700">
                                {{ $story->campaign->title }}
                            </a>
                        </h2>
                        <p class="mt-2 text-sm leading-relaxed text-ink-600">{{ $story->campaign->short_description }}</p>

                        <div class="mt-4">
                            <x-ui.progress :raised="$story->campaign->raised_amount" :target="$story->campaign->target_amount" />
                        </div>

                        <div class="mt-5 flex flex-wrap gap-2">
                            <a href="{{ route('campaigns.show', $story->campaign) }}" class="btn btn-outline btn-sm">View campaign</a>
                            @if ($story->campaign->is_open)
                                <a href="{{ route('donations.create', ['campaign' => $story->campaign->slug]) }}" class="btn btn-primary btn-sm">
                                    <x-ui.icon name="heart" class="size-4" /> Support it
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Share --}}
                <div class="mt-10 flex flex-wrap items-center justify-between gap-3 border-t border-ink-100 pt-6">
                    <p class="text-sm text-ink-500">Stories like this one only happen because people give.</p>
                    <div class="flex gap-2">
                        <button type="button" data-copy="{{ route('stories.show', $story) }}"
                                data-copy-message="Story link copied."
                                class="btn btn-outline btn-sm">
                            <x-ui.icon name="clipboard" class="size-4" /> Copy link
                        </button>
                        <a href="{{ route('donations.create') }}" class="btn btn-primary btn-sm">
                            <x-ui.icon name="heart" class="size-4" /> Donate
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="section bg-white">
            <div class="shell">
                <x-ui.section-heading eyebrow="Keep reading" title="More stories" />
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $item)
                        <x-story-card :story="$item" class="reveal" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endsection
