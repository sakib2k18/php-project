@extends('layouts.admin')

@section('title', 'Events')
@section('heading', 'Events')
@section('subheading', 'Orientations, camps, packing days and fundraisers')

@section('content')

    <x-admin.page-header
        title="All events"
        description="Only published events appear on the public site. Drafts stay private until you are ready."
        :count="$events->total()"
    >
        @if ($trashedCount && ! request()->boolean('trashed'))
            <a href="{{ route('admin.events.index', ['trashed' => 1]) }}" class="btn btn-outline">
                <x-ui.icon name="trash" class="size-4" /> Archive ({{ $trashedCount }})
            </a>
        @elseif (request()->boolean('trashed'))
            <a href="{{ route('admin.events.index') }}" class="btn btn-outline">
                <x-ui.icon name="arrow-left" class="size-4" /> Back to list
            </a>
        @endif

        <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
            <x-ui.icon name="plus" class="size-4" /> New event
        </a>
    </x-admin.page-header>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-ui.stat-card label="Upcoming" icon="calendar" tone="success" :value="number_format($upcomingCount)" />
        <x-ui.stat-card label="Total events" icon="clipboard" tone="neutral" :value="number_format($events->total())" />
        <x-ui.stat-card label="Archived" icon="trash" tone="neutral" :value="number_format($trashedCount)" />
    </div>

    <form method="GET" action="{{ route('admin.events.index') }}" data-filter-form class="panel mb-6 p-4">
        @if (request()->boolean('trashed'))
            <input type="hidden" name="trashed" value="1">
        @endif

        <div class="grid gap-3 lg:grid-cols-[2fr_1fr_1fr_auto]">
            <div class="relative">
                <label for="q" class="sr-only">Search events</label>
                <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-ink-400">
                    <x-ui.icon name="search" class="size-4.5" />
                </span>
                <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                       placeholder="Search title, location or organiser…" class="field pl-10" data-filter-search>
            </div>

            <div>
                <label for="status" class="sr-only">Status</label>
                <select id="status" name="status" class="field">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="when" class="sr-only">When</label>
                <select id="when" name="when" class="field">
                    <option value="">Any date</option>
                    <option value="upcoming" @selected(($filters['when'] ?? '') === 'upcoming')>Upcoming only</option>
                    <option value="past" @selected(($filters['when'] ?? '') === 'past')>Past only</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1 lg:flex-none">Filter</button>
                @if (array_filter(Arr::except($filters, 'trashed')))
                    <button type="button" data-filter-reset="{{ route('admin.events.index') }}" class="btn btn-ghost" aria-label="Clear filters">
                        <x-ui.icon name="x-mark" class="size-4" />
                    </button>
                @endif
            </div>
        </div>
    </form>

    <div class="panel overflow-hidden">
        @if ($events->isNotEmpty())
            <div class="table-wrap hidden lg:block">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Event</th>
                            <th scope="col">Date</th>
                            <th scope="col">Time</th>
                            <th scope="col">Location</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($events as $event)
                            <tr>
                                <td class="max-w-[18rem]">
                                    <p class="line-clamp-1 font-semibold text-ink-900">{{ $event->title }}</p>
                                    <p class="truncate text-[11px] text-ink-400">{{ $event->organizer }}</p>
                                </td>
                                <td class="whitespace-nowrap">
                                    <span class="font-medium text-ink-800">{{ $event->event_date->format('j M Y') }}</span>
                                    @if ($event->is_upcoming)
                                        <x-ui.badge tone="success" class="ml-1.5">Upcoming</x-ui.badge>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap text-ink-600">{{ $event->time_range ?? '—' }}</td>
                                <td class="max-w-[14rem]"><span class="line-clamp-1 text-ink-600">{{ $event->location ?? '—' }}</span></td>
                                <td><x-ui.status-badge :status="$event->status" :label="$event->status_label" /></td>
                                <td>
                                    @if ($event->trashed())
                                        <form method="POST" action="{{ route('admin.events.restore', $event->id) }}" class="flex justify-end">
                                            @csrf
                                            <button type="submit" class="btn btn-outline btn-sm">
                                                <x-ui.icon name="refresh" class="size-3.5" /> Restore
                                            </button>
                                        </form>
                                    @else
                                        <x-admin.row-actions
                                            :view="route('events.show', $event)"
                                            :edit="route('admin.events.edit', $event)"
                                            :delete="route('admin.events.destroy', $event)"
                                            delete-title="Archive this event?"
                                            delete-label="Archive"
                                            :delete-confirm="'“'.$event->title.'” will be hidden from the public site. It can be restored later.'"
                                        />
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <ul class="divide-y divide-ink-100 lg:hidden">
                @foreach ($events as $event)
                    <li class="flex items-start gap-3 p-4">
                        <div class="flex size-12 shrink-0 flex-col items-center justify-center rounded-lg {{ $event->is_upcoming ? 'bg-brand-600 text-white' : 'bg-ink-100 text-ink-500' }}">
                            <span class="text-[9px] font-bold uppercase">{{ $event->event_date->format('M') }}</span>
                            <span class="text-base font-bold leading-none">{{ $event->event_date->format('j') }}</span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="line-clamp-2 text-sm font-bold text-ink-900">{{ $event->title }}</p>
                            <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                <x-ui.status-badge :status="$event->status" :label="$event->status_label" />
                                <span class="truncate text-[11px] text-ink-500">{{ $event->location }}</span>
                            </div>

                            <div class="mt-2.5 flex gap-2">
                                @if ($event->trashed())
                                    <form method="POST" action="{{ route('admin.events.restore', $event->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline btn-sm">Restore</button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-primary btn-sm">Edit</a>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="px-4 pb-4">{{ $events->links() }}</div>
        @else
            <div class="p-6">
                <x-ui.empty-state icon="calendar" title="No events yet"
                                  description="Publish an event so supporters and volunteers know what is happening next.">
                    <a href="{{ route('admin.events.create') }}" class="btn btn-primary btn-sm">
                        <x-ui.icon name="plus" class="size-4" /> New event
                    </a>
                </x-ui.empty-state>
            </div>
        @endif
    </div>

@endsection
