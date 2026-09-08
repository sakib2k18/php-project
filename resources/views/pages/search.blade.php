@extends('layouts.app')

@section('title', ($term ? "Search: {$term}" : 'Search').' — '.$site->name())
@section('description', 'Search campaigns, projects and stories across the KUET TRY website.')

@section('content')

    <x-layout.page-hero
        eyebrow="Find something"
        title="Search"
        :description="$term ? null : 'Search across campaigns, projects and impact stories.'"
        :breadcrumbs="['Search' => null]"
    />

    <section class="section">
        <div class="shell">
            <form method="GET" action="{{ route('search') }}" class="panel p-4 sm:p-5">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <div class="relative flex-1">
                        <label for="q" class="sr-only">Search the site</label>
                        <span class="pointer-events-none absolute inset-y-0 left-0 grid w-11 place-items-center text-ink-400">
                            <x-ui.icon name="search" class="size-5" />
                        </span>
                        <input type="search" id="q" name="q" value="{{ $term }}" autofocus
                               placeholder="Campaigns, projects, stories…" class="field pl-11 text-base">
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg">Search</button>
                </div>
            </form>

            @if (filled($term))
                <p class="mt-6 text-sm text-ink-500">
                    <span class="font-bold text-ink-800">{{ $total }}</span>
                    {{ Str::plural('result', $total) }} for &ldquo;<span class="font-semibold text-ink-800">{{ $term }}</span>&rdquo;
                </p>

                @if ($total === 0)
                    <x-ui.empty-state
                        class="mt-6"
                        icon="search"
                        title="Nothing matched that search"
                        description="Try a shorter phrase, a place name, or browse the sections directly."
                    >
                        <a href="{{ route('campaigns.index') }}" class="btn btn-outline btn-sm">Campaigns</a>
                        <a href="{{ route('projects.index') }}" class="btn btn-outline btn-sm">Projects</a>
                        <a href="{{ route('stories.index') }}" class="btn btn-outline btn-sm">Stories</a>
                    </x-ui.empty-state>
                @endif

                @if ($results['campaigns']->isNotEmpty())
                    <div class="mt-10">
                        <div class="flex items-center gap-3">
                            <h2 class="display text-xl text-ink-900">Campaigns</h2>
                            <x-ui.badge tone="brand">{{ $results['campaigns']->count() }}</x-ui.badge>
                        </div>
                        <div class="mt-5 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($results['campaigns'] as $campaign)
                                <x-campaign-card :campaign="$campaign" />
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($results['projects']->isNotEmpty())
                    <div class="mt-12">
                        <div class="flex items-center gap-3">
                            <h2 class="display text-xl text-ink-900">Projects</h2>
                            <x-ui.badge tone="brand">{{ $results['projects']->count() }}</x-ui.badge>
                        </div>
                        <div class="mt-5 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($results['projects'] as $project)
                                <x-project-card :project="$project" />
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($results['stories']->isNotEmpty())
                    <div class="mt-12">
                        <div class="flex items-center gap-3">
                            <h2 class="display text-xl text-ink-900">Impact stories</h2>
                            <x-ui.badge tone="brand">{{ $results['stories']->count() }}</x-ui.badge>
                        </div>
                        <div class="mt-5 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($results['stories'] as $story)
                                <x-story-card :story="$story" />
                            @endforeach
                        </div>
                    </div>
                @endif
            @else
                <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @php
                        $shortcuts = [
                            ['icon' => 'heart',     'label' => 'Campaigns', 'url' => route('campaigns.index')],
                            ['icon' => 'briefcase', 'label' => 'Projects',  'url' => route('projects.index')],
                            ['icon' => 'sparkles',  'label' => 'Stories',   'url' => route('stories.index')],
                        ];
                    @endphp

                    @foreach ($shortcuts as $shortcut)
                        <a href="{{ $shortcut['url'] }}" class="card card-hover group flex items-center gap-3 p-5">
                            <span class="grid size-10 place-items-center rounded-xl bg-brand-50 text-brand-600 ring-1 ring-brand-100">
                                <x-ui.icon :name="$shortcut['icon']" class="size-5" />
                            </span>
                            <span class="flex-1 text-sm font-bold text-ink-900">{{ $shortcut['label'] }}</span>
                            <x-ui.icon name="chevron-right" class="size-4 text-ink-300 transition group-hover:translate-x-0.5 group-hover:text-brand-600" />
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

@endsection
