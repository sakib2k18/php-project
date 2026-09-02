@extends('layouts.app')

@section('title', $event->title.' — '.$site->name())
@section('description', Str::limit(strip_tags($event->description), 155))
@section('og:type', 'article')

@section('content')

    <x-layout.page-hero
        :eyebrow="$event->is_upcoming ? 'Upcoming event' : 'Past event'"
        :title="$event->title"
        :breadcrumbs="['Events' => route('events.index'), Str::limit($event->title, 40) => null]"
    >
        <div class="rounded-2xl border border-white/15 bg-white/10 px-6 py-4 text-center backdrop-blur">
            <p class="text-[11px] font-bold uppercase tracking-wider text-white/55">{{ $event->event_date->format('l') }}</p>
            <p class="display mt-1 text-3xl text-white">{{ $event->event_date->format('j M') }}</p>
            <p class="mt-0.5 text-xs font-semibold text-white/70">{{ $event->event_date->format('Y') }}</p>
        </div>
    </x-layout.page-hero>

    <section class="section">
        <div class="shell">
            <div class="grid gap-10 lg:grid-cols-[1fr_20rem] lg:gap-12">
                <div class="min-w-0">
                    <x-ui.media
                        :src="$event->image_url"
                        :alt="$event->title"
                        :seed="$event->slug"
                        icon="calendar"
                        label="Event"
                        ratio="aspect-[16/7]"
                        rounded="rounded-2xl"
                        :eager="true"
                    />

                    <div class="prose-kt mt-8">{!! $event->description !!}</div>

                    @if ($event->has_coordinates)
                        <div class="mt-10">
                            <h2 class="display text-2xl text-ink-900">Getting there</h2>
                            <p class="mt-1.5 text-sm text-ink-500">{{ $event->location }}</p>
                            <x-ui.map
                                class="mt-5"
                                :latitude="$event->latitude"
                                :longitude="$event->longitude"
                                :title="$event->title"
                                height="h-72"
                            />
                        </div>
                    @endif
                </div>

                <aside class="lg:sticky lg:top-24 lg:h-fit">
                    <div class="panel p-5">
                        <h2 class="panel-title mb-4">Event details</h2>

                        <dl class="space-y-4 text-sm">
                            <div class="flex items-start gap-3">
                                <x-ui.icon name="calendar" class="mt-0.5 size-4 shrink-0 text-brand-600" />
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-ink-400">Date</dt>
                                    <dd class="mt-0.5 font-semibold text-ink-900">{{ $event->event_date->format('l, j F Y') }}</dd>
                                </div>
                            </div>

                            @if ($event->time_range)
                                <div class="flex items-start gap-3 border-t border-ink-100 pt-4">
                                    <x-ui.icon name="clock" class="mt-0.5 size-4 shrink-0 text-brand-600" />
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wider text-ink-400">Time</dt>
                                        <dd class="mt-0.5 font-semibold text-ink-900">{{ $event->time_range }}</dd>
                                    </div>
                                </div>
                            @endif

                            @if ($event->location)
                                <div class="flex items-start gap-3 border-t border-ink-100 pt-4">
                                    <x-ui.icon name="map-pin" class="mt-0.5 size-4 shrink-0 text-brand-600" />
                                    <div class="min-w-0">
                                        <dt class="text-xs font-semibold uppercase tracking-wider text-ink-400">Location</dt>
                                        <dd class="mt-0.5 font-semibold leading-snug text-ink-900">{{ $event->location }}</dd>
                                    </div>
                                </div>
                            @endif

                            @if ($event->organizer)
                                <div class="flex items-start gap-3 border-t border-ink-100 pt-4">
                                    <x-ui.icon name="users" class="mt-0.5 size-4 shrink-0 text-brand-600" />
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wider text-ink-400">Organiser</dt>
                                        <dd class="mt-0.5 font-semibold text-ink-900">{{ $event->organizer }}</dd>
                                    </div>
                                </div>
                            @endif

                            @if ($event->capacity)
                                <div class="flex items-start gap-3 border-t border-ink-100 pt-4">
                                    <x-ui.icon name="user" class="mt-0.5 size-4 shrink-0 text-brand-600" />
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wider text-ink-400">Capacity</dt>
                                        <dd class="mt-0.5 font-semibold text-ink-900">{{ number_format($event->capacity) }} people</dd>
                                    </div>
                                </div>
                            @endif
                        </dl>

                        @if ($event->is_upcoming)
                            <a href="{{ route('volunteer.index') }}" class="btn btn-primary btn-block mt-6">
                                <x-ui.icon name="users" class="size-4" /> Join as a volunteer
                            </a>
                            <a href="{{ route('contact.create') }}" class="btn btn-outline btn-block mt-2">
                                <x-ui.icon name="mail" class="size-4" /> Ask about this event
                            </a>
                        @else
                            <p class="mt-6 rounded-xl bg-ink-50 p-3.5 text-center text-xs leading-relaxed text-ink-600">
                                This event has already taken place. Have a look at what is coming up next.
                            </p>
                            <a href="{{ route('events.index') }}" class="btn btn-outline btn-block mt-3">See upcoming events</a>
                        @endif
                    </div>
                </aside>
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="section bg-white">
            <div class="shell">
                <x-ui.section-heading eyebrow="Also coming up" title="Other events" />
                <div class="mt-8 grid gap-4 lg:grid-cols-2">
                    @foreach ($related as $item)
                        <x-event-card :event="$item" class="reveal" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endsection
