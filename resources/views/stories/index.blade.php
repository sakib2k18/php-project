@extends('layouts.app')

@section('title', 'Impact stories — '.$site->name())
@section('description', 'The families behind the numbers: stories of the people KUET TRY has supported, told with their permission and in their own words where possible.')

@section('content')

    <x-layout.page-hero
        eyebrow="Real people, real outcomes"
        title="Impact stories"
        description="Numbers on a dashboard do not tell you whether a family got their roof back. These do."
        :breadcrumbs="['Stories' => null]"
    />

    <section class="section">
        <div class="shell">

            {{-- Featured story --}}
            @if ($featured && $stories->currentPage() === 1 && blank($filters['q'] ?? null))
                <article class="card overflow-hidden lg:grid lg:grid-cols-2">
                    <x-ui.media
                        :src="$featured->image_url"
                        :alt="$featured->title"
                        :seed="$featured->slug"
                        icon="heart"
                        label="Featured story"
                        ratio="aspect-[16/10] lg:aspect-auto lg:h-full"
                        rounded="rounded-none"
                        :eager="true"
                    />

                    <div class="flex flex-col justify-center p-6 sm:p-9">
                        <x-ui.badge tone="accent" icon="sparkles" class="w-fit">Featured story</x-ui.badge>

                        <h2 class="display mt-3.5 text-2xl text-ink-900 sm:text-3xl">
                            <a href="{{ route('stories.show', $featured) }}" class="transition hover:text-brand-700">{{ $featured->title }}</a>
                        </h2>

                        <p class="mt-3 line-clamp-4 text-[15px] leading-relaxed text-ink-600">
                            {{ Str::limit(strip_tags($featured->story), 300) }}
                        </p>

                        <div class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-ink-500">
                            @if ($featured->beneficiary_name)
                                <span class="flex items-center gap-1.5">
                                    <x-ui.icon name="user" class="size-3.5" /> {{ $featured->beneficiary_name }}
                                </span>
                            @endif
                            @if ($featured->location)
                                <span class="flex items-center gap-1.5">
                                    <x-ui.icon name="map-pin" class="size-3.5" /> {{ $featured->location }}
                                </span>
                            @endif
                            <span class="flex items-center gap-1.5">
                                <x-ui.icon name="calendar" class="size-3.5" /> {{ $featured->story_date->format('F Y') }}
                            </span>
                        </div>

                        <a href="{{ route('stories.show', $featured) }}" class="btn btn-primary mt-6 w-fit">
                            Read the full story <x-ui.icon name="arrow-right" class="size-4" />
                        </a>
                    </div>
                </article>
            @endif

            {{-- Search --}}
            <form method="GET" action="{{ route('stories.index') }}" data-filter-form class="panel mt-8 p-4 sm:p-5">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <div class="relative flex-1">
                        <label for="q" class="sr-only">Search stories</label>
                        <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-ink-400">
                            <x-ui.icon name="search" class="size-4.5" />
                        </span>
                        <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                               placeholder="Search stories by title, name or location…" class="field pl-10" data-filter-search>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <x-ui.icon name="search" class="size-4" /> Search
                    </button>
                    @if (filled($filters['q'] ?? null))
                        <button type="button" data-filter-reset="{{ route('stories.index') }}" class="btn btn-ghost">Clear</button>
                    @endif
                </div>
            </form>

            @if ($stories->isNotEmpty())
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($stories as $story)
                        <x-story-card :story="$story" class="reveal" />
                    @endforeach
                </div>

                {{ $stories->links() }}
            @else
                <x-ui.empty-state
                    class="mt-8"
                    icon="heart"
                    title="No stories match your search"
                    description="Try a different name or place, or read all of the stories we have published."
                >
                    <a href="{{ route('stories.index') }}" class="btn btn-primary btn-sm">Show all stories</a>
                </x-ui.empty-state>
            @endif
        </div>
    </section>

@endsection
