@extends('layouts.app')

@section('title', 'Projects — '.$site->name())
@section('description', 'Completed and ongoing humanitarian work by KUET TRY: winter distribution, flood relief, education support, food parcels, medical camps and clean water.')

@section('content')

    <x-layout.page-hero
        eyebrow="What we have delivered"
        title="Our projects"
        description="Campaigns raise the money. Projects are what that money became — delivered, documented and reviewed afterwards."
        :breadcrumbs="['Projects' => null]"
    >
        <div class="flex items-center gap-6 rounded-2xl border border-white/15 bg-white/10 px-6 py-4 backdrop-blur">
            <div>
                <p class="display text-3xl text-white">{{ $counts['ongoing'] }}</p>
                <p class="mt-0.5 text-[11px] font-bold uppercase tracking-wider text-white/55">Ongoing</p>
            </div>
            <div class="h-10 w-px bg-white/20"></div>
            <div>
                <p class="display text-3xl text-white">{{ $counts['completed'] }}</p>
                <p class="mt-0.5 text-[11px] font-bold uppercase tracking-wider text-white/55">Completed</p>
            </div>
            <div class="h-10 w-px bg-white/20"></div>
            <div>
                <p class="display text-3xl text-white">{{ compact_number($counts['beneficiaries']) }}</p>
                <p class="mt-0.5 text-[11px] font-bold uppercase tracking-wider text-white/55">People reached</p>
            </div>
        </div>
    </x-layout.page-hero>

    <section class="section">
        <div class="shell">
            <form method="GET" action="{{ route('projects.index') }}" data-filter-form class="panel p-4 sm:p-5">
                <div class="grid gap-3 lg:grid-cols-[2fr_1fr_1fr_auto]">
                    <div class="relative">
                        <label for="q" class="sr-only">Search projects</label>
                        <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-ink-400">
                            <x-ui.icon name="search" class="size-4.5" />
                        </span>
                        <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                               placeholder="Search projects…" class="field pl-10" data-filter-search>
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
                            @foreach ($statuses as $key => $label)
                                <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-primary flex-1 lg:flex-none">
                            <x-ui.icon name="filter" class="size-4" /> Apply
                        </button>
                        @if (array_filter($filters))
                            <button type="button" data-filter-reset="{{ route('projects.index') }}" class="btn btn-ghost" aria-label="Clear filters">
                                <x-ui.icon name="x-mark" class="size-4" />
                            </button>
                        @endif
                    </div>
                </div>
            </form>

            @if ($projects->isNotEmpty())
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($projects as $project)
                        <x-project-card :project="$project" class="reveal" />
                    @endforeach
                </div>

                {{ $projects->links() }}
            @else
                <x-ui.empty-state
                    class="mt-8"
                    icon="briefcase"
                    title="No projects match your filters"
                    description="Try a different category or clear the search to see everything we have delivered."
                >
                    <a href="{{ route('projects.index') }}" class="btn btn-primary btn-sm">Show all projects</a>
                </x-ui.empty-state>
            @endif
        </div>
    </section>

@endsection
