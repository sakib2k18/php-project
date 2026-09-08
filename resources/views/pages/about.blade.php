@extends('layouts.app')

@section('title', 'About us — '.$site->name())
@section('description', 'Who KUET TRY is, what we believe, how we work and what we have delivered since 2016.')

@section('content')

    <x-layout.page-hero
        eyebrow="Who we are"
        title="A student-led organisation that shows its working"
        description="KUET TRY began with a table outside the library and a cardboard box. It now reaches families across the south-west of Bangladesh — and still publishes every taka."
        :breadcrumbs="['About' => null]"
    />

    {{-- Impact band --}}
    <section class="border-b border-ink-100 bg-white">
        <div class="shell py-10">
            <dl class="grid grid-cols-2 gap-6 sm:grid-cols-4">
                @php
                    $figures = [
                        ['label' => 'People helped',       'value' => $impact['people_helped'],       'compact' => true,  'prefix' => '', 'suffix' => '+'],
                        ['label' => 'Funds raised',        'value' => $impact['funds_raised'],        'compact' => true,  'prefix' => config('site.currency.symbol').' ', 'suffix' => ''],
                        ['label' => 'Projects delivered',  'value' => $impact['projects_delivered'],  'compact' => false, 'prefix' => '', 'suffix' => ''],
                        ['label' => 'Active volunteers',   'value' => $impact['volunteers'],          'compact' => false, 'prefix' => '', 'suffix' => ''],
                    ];
                @endphp

                @foreach ($figures as $figure)
                    <div class="text-center">
                        <dd class="display text-3xl text-brand-700 sm:text-4xl"
                            data-count-to="{{ $figure['value'] }}"
                            data-count-prefix="{{ $figure['prefix'] }}"
                            data-count-suffix="{{ $figure['suffix'] }}"
                            data-count-compact="{{ $figure['compact'] ? 'true' : 'false' }}"
                        >{{ $figure['prefix'] }}{{ $figure['compact'] ? compact_number($figure['value']) : number_format($figure['value']) }}{{ $figure['suffix'] }}</dd>
                        <dt class="mt-1.5 text-[11px] font-bold uppercase tracking-[0.1em] text-ink-500">{{ $figure['label'] }}</dt>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    {{-- Who we are --}}
    <section class="section">
        <div class="shell">
            <div class="grid gap-12 lg:grid-cols-[1.1fr_0.9fr] lg:items-start">
                <div>
                    <x-ui.section-heading eyebrow="Our story" title="How KUET TRY started" />

                    <div class="prose-kt mt-6">
                        <p>{{ $site->get('about') }}</p>
                        <p>
                            We are not a large NGO. We are students, alumni and teachers who decided that
                            &ldquo;someone should do something&rdquo; was not an answer. That shapes how we work: small
                            overheads, direct delivery, and a rule that nothing is claimed unless it can be shown.
                        </p>
                    </div>

                    <div class="mt-8 grid gap-4 sm:grid-cols-2">
                        <div class="card p-5">
                            <span class="grid size-10 place-items-center rounded-xl bg-brand-50 text-brand-600 ring-1 ring-brand-100">
                                <x-ui.icon name="sparkles" class="size-5" />
                            </span>
                            <h3 class="mt-3.5 text-sm font-bold text-ink-900">Our mission</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-ink-600">{{ $site->get('mission') }}</p>
                        </div>

                        <div class="card p-5">
                            <span class="grid size-10 place-items-center rounded-xl bg-accent-50 text-accent-600 ring-1 ring-accent-100">
                                <x-ui.icon name="globe" class="size-5" />
                            </span>
                            <h3 class="mt-3.5 text-sm font-bold text-ink-900">Our vision</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-ink-600">{{ $site->get('vision') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Values --}}
                <div>
                    <div class="panel p-6 sm:p-7">
                        <h2 class="display text-xl text-ink-900">What we hold ourselves to</h2>

                        @php
                            $values = [
                                ['icon' => 'shield-check', 'title' => 'Verified before it counts',   'text' => 'A donation stays pending until it is matched against our bank or mobile banking statement. No total on this site is an estimate.'],
                                ['icon' => 'document',     'title' => 'Published expenditure',       'text' => 'Every campaign ends in a field report listing what was bought, at what price, and for how many households.'],
                                ['icon' => 'user-circle',  'title' => 'Dignity first',               'text' => 'We deliver to homes where we can, we take consent before photographing, and we do not publish a face to raise money.'],
                                ['icon' => 'users',        'title' => 'Local verification',          'text' => 'Beneficiary lists are prepared with union parishad members and head teachers who actually know the households.'],
                                ['icon' => 'refresh',      'title' => 'We publish what went wrong',  'text' => 'A report that only contains successes is advertising. Ours include the mistakes and what we changed.'],
                            ];
                        @endphp

                        <ul class="mt-6 space-y-5">
                            @foreach ($values as $value)
                                <li class="flex gap-3.5">
                                    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-600">
                                        <x-ui.icon :name="$value['icon']" class="size-4.5" />
                                    </span>
                                    <div>
                                        <h3 class="text-sm font-bold text-ink-900">{{ $value['title'] }}</h3>
                                        <p class="mt-1 text-xs leading-relaxed text-ink-600">{{ $value['text'] }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Home university --}}
                    <div class="mt-6 flex items-center gap-4 rounded-xl border border-ink-100 bg-white p-4">
                        <img src="{{ asset('images/kuet-logo.jpg') }}" alt="Khulna University of Engineering & Technology logo"
                             class="size-14 shrink-0 rounded-lg object-contain">
                        <div>
                            <h3 class="text-sm font-bold text-ink-900">Based at KUET, Khulna</h3>
                            <p class="mt-1 text-xs leading-relaxed text-ink-600">
                                Founded by students, alumni and teachers of Khulna University of
                                Engineering &amp; Technology — and still run from campus.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- What we do --}}
    <section class="section bg-white">
        <div class="shell">
            <x-ui.section-heading
                align="center"
                eyebrow="What we do"
                title="Six areas of work"
                description="Each one runs continuously. When a disaster hits, everything else pauses and the response team moves."
            />

            @php
                $areas = [
                    ['icon' => 'storm',      'title' => 'Disaster relief',       'text' => 'Response teams reach affected unions within 48 hours with dry food, tarpaulins, water purification and, later, roofing materials.', 'category' => 'natural_disaster'],
                    ['icon' => 'sprout',     'title' => 'Food distribution',     'text' => 'Two hundred households receive a four-week staple parcel on the first Friday of every month.', 'category' => 'food_distribution'],
                    ['icon' => 'medical',    'title' => 'Medical assistance',    'text' => 'Quarterly camps, free medicine and a fund that settles surgery bills directly with the hospital.', 'category' => 'medical'],
                    ['icon' => 'academic',   'title' => 'Education support',     'text' => 'Books, bags, fees and coaching for children at risk of dropping out for financial reasons.', 'category' => 'education'],
                    ['icon' => 'snow',       'title' => 'Winter support',        'text' => 'Blankets and warm clothing distributed at night, when the people who need them are actually reachable.', 'category' => 'winter_support'],
                    ['icon' => 'hand-heart', 'title' => 'Poverty relief',        'text' => 'Cash grants and livelihood support — a replaced sewing machine does more than a year of parcels.', 'category' => 'poverty_relief'],
                ];
            @endphp

            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($areas as $area)
                    <a href="{{ route('campaigns.index', ['category' => $area['category']]) }}" class="reveal card card-hover group p-6">
                        <span class="grid size-11 place-items-center rounded-xl bg-brand-50 text-brand-600 ring-1 ring-brand-100 transition group-hover:bg-brand-600 group-hover:text-white">
                            <x-ui.icon :name="$area['icon']" class="size-5.5" />
                        </span>
                        <h3 class="mt-4 text-base font-bold text-ink-900">{{ $area['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-ink-600">{{ $area['text'] }}</p>
                        <span class="link-arrow mt-4">
                            See campaigns <x-ui.icon name="arrow-right" class="size-4" />
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Timeline built from real project data --}}
    @if ($milestones->isNotEmpty())
        <section class="section">
            <div class="shell">
                <x-ui.section-heading
                    eyebrow="Our journey"
                    title="Year by year"
                    description="Built from the project records in our database — it grows as we deliver."
                />

                <ol class="mt-10 space-y-8 border-l-2 border-ink-200 pl-6 sm:pl-8">
                    @foreach ($milestones as $milestone)
                        <li class="reveal relative">
                            <span class="absolute -left-[2.05rem] top-1 grid size-5 place-items-center rounded-full bg-brand-600 ring-4 ring-sand sm:-left-[2.55rem]">
                                <span class="size-1.5 rounded-full bg-white"></span>
                            </span>

                            <div class="flex flex-wrap items-center gap-3">
                                <span class="display text-2xl text-brand-700">{{ $milestone->year }}</span>
                                <x-ui.badge tone="neutral">{{ $milestone->count }} {{ Str::plural('project', $milestone->count) }}</x-ui.badge>
                                <x-ui.badge tone="brand">{{ compact_number($milestone->beneficiaries) }} people reached</x-ui.badge>
                            </div>

                            @if ($milestone->highlight)
                                <h3 class="mt-2 text-base font-bold text-ink-900">
                                    <a href="{{ route('projects.show', $milestone->highlight) }}" class="transition hover:text-brand-700">
                                        {{ $milestone->highlight->title }}
                                    </a>
                                </h3>
                                <p class="mt-1.5 max-w-2xl text-sm leading-relaxed text-ink-600">{{ $milestone->highlight->summary }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    {{-- Team preview --}}
    @if ($team->isNotEmpty())
        <section class="section bg-white">
            <div class="shell">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <x-ui.section-heading eyebrow="The people" title="Who runs KUET TRY" />
                    <a href="{{ route('team') }}" class="btn btn-outline shrink-0">
                        Meet the whole team <x-ui.icon name="arrow-right" class="size-4" />
                    </a>
                </div>

                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($team as $member)
                        <div class="reveal card card-hover p-5 text-center">
                            <x-ui.avatar :src="$member->photo_url" :initials="$member->initials" :name="$member->name" size="xl" class="mx-auto" />
                            <h3 class="mt-4 text-sm font-bold text-ink-900">{{ $member->name }}</h3>
                            <p class="mt-0.5 text-xs font-semibold text-brand-700">{{ $member->position }}</p>
                            @if ($member->biography)
                                <p class="mt-2 line-clamp-3 text-xs leading-relaxed text-ink-600">{{ $member->biography }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Location --}}
    <section class="section">
        <div class="shell">
            <div class="grid gap-8 lg:grid-cols-[0.85fr_1.15fr] lg:items-center">
                <div>
                    <x-ui.section-heading eyebrow="Find us" title="Our office" />

                    <ul class="mt-6 space-y-4 text-sm">
                        <li class="flex gap-3">
                            <x-ui.icon name="map-pin" class="mt-0.5 size-4.5 shrink-0 text-brand-600" />
                            <span class="leading-relaxed text-ink-700">{{ $site->address() }}</span>
                        </li>
                        <li class="flex gap-3">
                            <x-ui.icon name="mail" class="mt-0.5 size-4.5 shrink-0 text-brand-600" />
                            <a href="mailto:{{ $site->email() }}" class="break-all text-ink-700 transition hover:text-brand-700">{{ $site->email() }}</a>
                        </li>
                        <li class="flex gap-3">
                            <x-ui.icon name="phone" class="mt-0.5 size-4.5 shrink-0 text-brand-600" />
                            <span class="text-ink-700">{{ $site->phone() }}</span>
                        </li>
                        @if ($site->get('office_hours'))
                            <li class="flex gap-3">
                                <x-ui.icon name="clock" class="mt-0.5 size-4.5 shrink-0 text-brand-600" />
                                <span class="text-ink-700">{{ $site->get('office_hours') }}</span>
                            </li>
                        @endif
                    </ul>

                    <a href="{{ route('contact.create') }}" class="btn btn-primary mt-7">
                        <x-ui.icon name="mail" class="size-4" /> Get in touch
                    </a>
                </div>

                <x-ui.map
                    :latitude="$site->latitude()"
                    :longitude="$site->longitude()"
                    :title="$site->name()"
                    height="h-96"
                />
            </div>
        </div>
    </section>

@endsection
