@extends('layouts.app')

@section('title', 'Campaigns — '.$site->name())
@section('description', 'Browse every open and completed KUET TRY appeal. Each campaign shows its target, what has been raised and who it reaches.')

@section('content')

    <x-layout.page-hero
        eyebrow="Where help is needed"
        title="Campaigns"
        description="Every appeal below is verified before it is published, and its total is updated only when a donation has been checked against our statements."
        :breadcrumbs="['Campaigns' => null]"
    >
        <div class="flex items-center gap-6 rounded-2xl border border-white/15 bg-white/10 px-6 py-4 backdrop-blur">
            <div>
                <p class="display text-3xl text-white">{{ $totalActive }}</p>
                <p class="mt-0.5 text-[11px] font-bold uppercase tracking-wider text-white/55">Open now</p>
            </div>
            <div class="h-10 w-px bg-white/20"></div>
            <div>
                <p class="display text-3xl text-white">{{ $campaigns->total() }}</p>
                <p class="mt-0.5 text-[11px] font-bold uppercase tracking-wider text-white/55">Total listed</p>
            </div>
        </div>
    </x-layout.page-hero>

    <section class="section">
        <div class="shell">

            {{-- ---------------------------------------------------------
                 Filters. Submitted with GET so results stay bookmarkable
                 and the query is executed by Eloquent on the server.
                 --------------------------------------------------------- --}}
            <form method="GET" action="{{ route('campaigns.index') }}" data-filter-form class="panel p-4 sm:p-5">
                <div class="grid gap-3 lg:grid-cols-[1.5fr_1fr_1fr_1fr_auto]">
                    <div>
                        <label for="q" class="sr-only">Search campaigns</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-ink-400">
                                <x-ui.icon name="search" class="size-4.5" />
                            </span>
                            <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                                   placeholder="Search by title, description or location…"
                                   class="field pl-10" data-filter-search>
                        </div>
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

                    <div>
                        <label for="status" class="sr-only">Status</label>
                        <select id="status" name="status" class="field">
                            <option value="">Any status</option>
                            <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                            <option value="completed" @selected(($filters['status'] ?? '') === 'completed')>Completed</option>
                        </select>
                    </div>

                    <div>
                        <label for="sort" class="sr-only">Sort by</label>
                        <select id="sort" name="sort" class="field">
                            <option value="recent" @selected(($filters['sort'] ?? '') === 'recent')>Most recent</option>
                            <option value="ending" @selected(($filters['sort'] ?? '') === 'ending')>Ending soonest</option>
                            <option value="progress" @selected(($filters['sort'] ?? '') === 'progress')>Closest to target</option>
                            <option value="target" @selected(($filters['sort'] ?? '') === 'target')>Largest target</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-primary flex-1 lg:flex-none">
                            <x-ui.icon name="filter" class="size-4" /> Apply
                        </button>

                        @if (array_filter(Arr::except($filters, 'sort')))
                            <button type="button" data-filter-reset="{{ route('campaigns.index') }}" class="btn btn-ghost" aria-label="Clear all filters">
                                <x-ui.icon name="x-mark" class="size-4" />
                            </button>
                        @endif
                    </div>
                </div>

                <noscript>
                    <p class="help mt-2">Choose your filters and press Apply.</p>
                </noscript>
            </form>

            {{-- Result summary --}}
            <p class="mt-6 text-sm text-ink-500">
                @if ($campaigns->total())
                    Showing <span class="font-bold text-ink-800">{{ $campaigns->firstItem() }}–{{ $campaigns->lastItem() }}</span>
                    of <span class="font-bold text-ink-800">{{ $campaigns->total() }}</span> campaigns
                    @if (filled($filters['q'] ?? null))
                        for &ldquo;<span class="font-semibold text-ink-800">{{ $filters['q'] }}</span>&rdquo;
                    @endif
                @endif
            </p>

            @if ($campaigns->isNotEmpty())
                <div class="mt-5 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($campaigns as $campaign)
                        <x-campaign-card :campaign="$campaign" class="reveal" />
                    @endforeach
                </div>

                {{ $campaigns->links() }}
            @else
                <x-ui.empty-state
                    class="mt-5"
                    icon="search"
                    title="No campaigns match your filters"
                    description="Try a different category, clear the search box, or browse everything we are currently running."
                >
                    <a href="{{ route('campaigns.index') }}" class="btn btn-primary btn-sm">Show all campaigns</a>
                    <a href="{{ route('projects.index') }}" class="btn btn-outline btn-sm">Browse projects</a>
                </x-ui.empty-state>
            @endif
        </div>
    </section>

@endsection
