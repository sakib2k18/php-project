@extends('layouts.app')

@section('title', 'News &amp; field reports — '.$site->name())
@section('description', 'Field reports, expenditure breakdowns and organisation news from KUET TRY.')

@section('content')

    <x-layout.page-hero
        eyebrow="From the field"
        title="News &amp; reports"
        description="What we delivered, what it cost, and what did not go to plan. We publish the reports because that is the only reason a stranger should trust us with their money."
        :breadcrumbs="['News' => null]"
    />

    <section class="section">
        <div class="shell">
            <div class="grid gap-10 lg:grid-cols-[1fr_18rem] lg:gap-12">

                <div class="min-w-0">
                    {{-- Featured lead article --}}
                    @if ($featured)
                        <x-post-card :post="$featured" :horizontal="true" class="mb-8" />
                    @endif

                    {{-- Filters --}}
                    <form method="GET" action="{{ route('news.index') }}" data-filter-form class="panel p-4 sm:p-5">
                        <div class="grid gap-3 sm:grid-cols-[2fr_1fr_auto]">
                            <div class="relative">
                                <label for="q" class="sr-only">Search articles</label>
                                <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-ink-400">
                                    <x-ui.icon name="search" class="size-4.5" />
                                </span>
                                <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                                       placeholder="Search articles…" class="field pl-10" data-filter-search>
                            </div>

                            <div>
                                <label for="category" class="sr-only">Category</label>
                                <select id="category" name="category" class="field">
                                    <option value="">All categories</option>
                                    @foreach ($categories as $key => $label)
                                        <option value="{{ $key }}" @selected(($filters['category'] ?? '') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex gap-2">
                                <button type="submit" class="btn btn-primary flex-1 sm:flex-none">Search</button>
                                @if (array_filter($filters))
                                    <button type="button" data-filter-reset="{{ route('news.index') }}" class="btn btn-ghost" aria-label="Clear filters">
                                        <x-ui.icon name="x-mark" class="size-4" />
                                    </button>
                                @endif
                            </div>
                        </div>
                    </form>

                    @if ($posts->isNotEmpty())
                        <div class="mt-8 grid gap-6 sm:grid-cols-2">
                            @foreach ($posts as $post)
                                <x-post-card :post="$post" class="reveal" />
                            @endforeach
                        </div>

                        {{ $posts->links() }}
                    @else
                        <x-ui.empty-state
                            class="mt-8"
                            icon="newspaper"
                            title="No articles match your search"
                            description="Try a different keyword or browse every category."
                        >
                            <a href="{{ route('news.index') }}" class="btn btn-primary btn-sm">Show all articles</a>
                        </x-ui.empty-state>
                    @endif
                </div>

                {{-- Sidebar --}}
                <aside class="space-y-6 lg:sticky lg:top-24 lg:h-fit">
                    <div class="panel p-5">
                        <h2 class="panel-title mb-4">Categories</h2>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('news.index') }}"
                                   class="flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium transition {{ blank($filters['category'] ?? null) ? 'bg-brand-50 text-brand-700' : 'text-ink-600 hover:bg-ink-50' }}">
                                    All articles <x-ui.icon name="chevron-right" class="size-3.5" />
                                </a>
                            </li>
                            @foreach ($categories as $key => $label)
                                <li>
                                    <a href="{{ route('news.index', ['category' => $key]) }}"
                                       class="flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium transition {{ ($filters['category'] ?? '') === $key ? 'bg-brand-50 text-brand-700' : 'text-ink-600 hover:bg-ink-50' }}">
                                        {{ $label }} <x-ui.icon name="chevron-right" class="size-3.5" />
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    @if ($recent->isNotEmpty())
                        <div class="panel p-5">
                            <h2 class="panel-title mb-4">Recent articles</h2>
                            <ul class="space-y-3.5">
                                @foreach ($recent as $item)
                                    <li>
                                        <a href="{{ route('news.show', $item) }}" class="group block">
                                            <span class="line-clamp-2 text-sm font-semibold leading-snug text-ink-800 transition group-hover:text-brand-700">{{ $item->title }}</span>
                                            <span class="mt-1 block text-[11px] text-ink-500">{{ $item->published_at?->format('j M Y') }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="rounded-2xl bg-gradient-to-br from-brand-700 to-brand-900 p-5 text-white">
                        <x-ui.icon name="heart" class="size-6 text-accent-300" />
                        <h2 class="display mt-3 text-lg">Reports are only possible because people give.</h2>
                        <p class="mt-2 text-xs leading-relaxed text-white/70">
                            Every campaign we run ends in a report like these.
                        </p>
                        <a href="{{ route('donations.create') }}" class="btn btn-accent btn-sm mt-4 w-full">Donate now</a>
                    </div>
                </aside>
            </div>
        </div>
    </section>

@endsection
