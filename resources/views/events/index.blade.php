@extends('layouts.app')

@section('title', 'Events — '.$site->name())
@section('description', 'Upcoming and past KUET TRY events: volunteer orientations, medical camps, blood donation drives, packing days and fundraising evenings.')

@section('content')

    <x-layout.page-hero
        eyebrow="Come and join us"
        title="Events"
        description="Orientation sessions, medical camps, packing days and fundraising evenings. Most of our events are open — you do not need to be a KUET student to take part."
        :breadcrumbs="['Events' => null]"
    >
        <a href="{{ route('volunteer.index') }}" class="btn btn-accent btn-lg">
            <x-ui.icon name="users" class="size-5" /> Volunteer with us
        </a>
    </x-layout.page-hero>

    <section class="section">
        <div class="shell">
            <form method="GET" action="{{ route('events.index') }}" data-filter-form class="panel p-4 sm:p-5">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <div class="relative flex-1">
                        <label for="q" class="sr-only">Search events</label>
                        <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-ink-400">
                            <x-ui.icon name="search" class="size-4.5" />
                        </span>
                        <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                               placeholder="Search by title, location or organiser…" class="field pl-10" data-filter-search>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <x-ui.icon name="search" class="size-4" /> Search
                    </button>

                    @if (filled($filters['q'] ?? null))
                        <button type="button" data-filter-reset="{{ route('events.index') }}" class="btn btn-ghost">Clear</button>
                    @endif
                </div>
            </form>

            {{-- Upcoming --}}
            <div class="mt-10">
                <div class="flex items-center gap-3">
                    <h2 class="display text-2xl text-ink-900">Upcoming events</h2>
                    <x-ui.badge tone="success">{{ $upcoming->count() }}</x-ui.badge>
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-2">
                    @forelse ($upcoming as $event)
                        <x-event-card :event="$event" class="reveal" />
                    @empty
                        <div class="lg:col-span-2">
                            <x-ui.empty-state
                                icon="calendar"
                                title="Nothing scheduled right now"
                                description="Our next orientation, camp or distribution day will be announced here and on our stories page."
                            >
                                <a href="{{ route('stories.index') }}" class="btn btn-outline btn-sm">Read the latest stories</a>
                            </x-ui.empty-state>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Past --}}
            @if ($past->isNotEmpty())
                <div class="mt-14">
                    <div class="flex items-center gap-3">
                        <h2 class="display text-2xl text-ink-900">Past events</h2>
                        <x-ui.badge tone="neutral">{{ $past->total() }}</x-ui.badge>
                    </div>

                    <div class="mt-6 grid gap-4 lg:grid-cols-2">
                        @foreach ($past as $event)
                            <x-event-card :event="$event" :past="true" class="reveal" />
                        @endforeach
                    </div>

                    {{ $past->links() }}
                </div>
            @endif
        </div>
    </section>

@endsection
