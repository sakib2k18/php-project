@extends('layouts.app')

@section('title', $site->name().' — '.$site->tagline())
@section('description', 'KUET TRY is a humanitarian organisation working for families in crisis across Bangladesh — emergency relief, food, medical assistance and education support, with every taka published.')

@section('content')

    {{-- ================================================================
         HERO
         ================================================================ --}}
    <section class="relative overflow-hidden bg-brand-950">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="absolute inset-0 bg-[radial-gradient(120%_100%_at_15%_0%,#0f7d5a_0%,#0c4234_45%,#05261e_100%)]"></div>
            <div class="grain absolute inset-0 opacity-60"></div>
            <div class="absolute -right-24 -top-24 size-[32rem] rounded-full bg-brand-500/20 blur-3xl"></div>
            <div class="absolute -bottom-32 -left-24 size-[26rem] rounded-full bg-accent-500/10 blur-3xl"></div>
        </div>

        <div class="shell relative py-14 sm:py-16 lg:py-20">
            <div class="grid items-center gap-10 lg:grid-cols-[1.05fr_0.95fr]">
                <div>
                    <p class="eyebrow !text-accent-300">
                        <span class="inline-block h-px w-7 bg-accent-400"></span>
                        Humanitarian organisation · Since {{ config('site.organization.founded_year') }}
                    </p>

                    <h1 class="display text-shadow-hero mt-5 text-4xl text-white sm:text-5xl lg:text-6xl">
                        Together, We Can<br class="hidden sm:block"> Make a Difference.
                    </h1>

                    <p class="mt-5 max-w-xl text-base leading-relaxed text-white/75 sm:text-lg">
                        {{ $site->name() }} brings people together to support communities, help families in need,
                        and respond when help matters most — from cyclone shelters on the Khulna coast to
                        a school bag that keeps a child in class.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('campaigns.index') }}" class="btn btn-accent btn-lg">
                            <x-ui.icon name="sparkles" class="size-5" /> Explore campaigns
                        </a>
                        <a href="{{ route('donations.create') }}" class="btn btn-on-dark btn-lg">
                            <x-ui.icon name="heart" class="size-5" /> Donate now
                        </a>
                    </div>

                    {{-- Trust markers --}}
                    <ul class="mt-9 flex flex-wrap items-center gap-x-6 gap-y-3 text-xs text-white/60">
                        <li class="flex items-center gap-1.5">
                            <x-ui.icon name="shield-check" class="size-4 text-brand-300" />
                            Every donation verified before it counts
                        </li>
                        <li class="flex items-center gap-1.5">
                            <x-ui.icon name="document" class="size-4 text-brand-300" />
                            Expenditure published per campaign
                        </li>
                        <li class="flex items-center gap-1.5">
                            <x-ui.icon name="users" class="size-4 text-brand-300" />
                            Student &amp; alumni volunteers
                        </li>
                        {{-- TRY is a student organisation of KUET, so the university
                             mark sits beside the other trust markers. --}}
                        <li class="flex items-center gap-1.5">
                            <img src="{{ asset('images/kuet-logo.jpg') }}" alt="" class="size-4 rounded-sm object-contain">
                            Run by KUET students
                        </li>
                    </ul>
                </div>

                {{-- Emergency / featured spotlight --}}
                @php
                    $spotlight = $emergency ?? $featuredCampaigns->first();
                @endphp

                @if ($spotlight)
                    <div class="relative">
                        <div class="rounded-2xl border border-white/15 bg-white/95 p-5 shadow-[0_28px_60px_-16px_rgba(5,38,30,.6)] backdrop-blur sm:p-6">
                            <div class="flex items-center justify-between gap-3">
                                @if ($spotlight->is_emergency)
                                    <span class="badge badge-danger pulse-ring">
                                        <x-ui.icon name="exclamation" class="size-3" /> Emergency appeal
                                    </span>
                                @else
                                    <span class="badge badge-accent">
                                        <x-ui.icon name="sparkles" class="size-3" /> Featured appeal
                                    </span>
                                @endif

                                <span class="text-xs font-semibold text-ink-500">{{ $spotlight->category_label }}</span>
                            </div>

                            <h2 class="mt-3.5 text-lg font-bold leading-snug text-ink-900">
                                <a href="{{ route('campaigns.show', $spotlight) }}" class="transition hover:text-brand-700">
                                    {{ $spotlight->title }}
                                </a>
                            </h2>

                            <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-ink-600">
                                {{ $spotlight->short_description }}
                            </p>

                            <div class="mt-5">
                                <x-ui.progress
                                    :raised="$spotlight->raised_amount"
                                    :target="$spotlight->target_amount"
                                    :tone="$spotlight->is_emergency ? 'accent' : 'brand'"
                                />
                            </div>

                            <dl class="mt-5 grid grid-cols-3 gap-2 border-t border-ink-100 pt-4 text-center">
                                <div>
                                    <dt class="text-[10px] font-bold uppercase tracking-wider text-ink-400">Days left</dt>
                                    <dd class="mt-0.5 text-sm font-bold text-ink-900">{{ $spotlight->days_left ?? '—' }}</dd>
                                </div>
                                <div class="border-x border-ink-100">
                                    <dt class="text-[10px] font-bold uppercase tracking-wider text-ink-400">Supporters</dt>
                                    <dd class="mt-0.5 text-sm font-bold text-ink-900">{{ $spotlight->approvedDonations()->count() }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[10px] font-bold uppercase tracking-wider text-ink-400">To reach</dt>
                                    <dd class="mt-0.5 text-sm font-bold text-ink-900">{{ compact_number($spotlight->beneficiaries_count) }}</dd>
                                </div>
                            </dl>

                            <div class="mt-5 flex gap-2">
                                <a href="{{ route('donations.create', ['campaign' => $spotlight->slug]) }}" class="btn btn-primary flex-1">
                                    <x-ui.icon name="heart" class="size-4" /> Support this appeal
                                </a>
                                <a href="{{ route('campaigns.show', $spotlight) }}" class="btn btn-outline" aria-label="Read more about {{ $spotlight->title }}">
                                    <x-ui.icon name="arrow-right" class="size-4" />
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Impact statistics band — all figures come from MySQL --}}
        <div class="relative border-t border-white/10 bg-white/5 backdrop-blur">
            <div class="shell">
                <dl class="grid grid-cols-2 divide-white/10 sm:grid-cols-4 sm:divide-x">
                    @php
                        $stats = [
                            ['label' => 'People helped',      'value' => $impact['people_helped'],       'suffix' => '+', 'compact' => true,  'prefix' => ''],
                            ['label' => 'Campaigns completed','value' => $impact['campaigns_completed'], 'suffix' => '',  'compact' => false, 'prefix' => ''],
                            ['label' => 'Funds raised',       'value' => $impact['funds_raised'],        'suffix' => '',  'compact' => true,  'prefix' => config('site.currency.symbol').' '],
                            ['label' => 'Active volunteers',  'value' => $impact['volunteers'],          'suffix' => '',  'compact' => false, 'prefix' => ''],
                        ];
                    @endphp

                    @foreach ($stats as $stat)
                        <div class="px-2 py-6 text-center sm:py-8">
                            {{-- The real figure is rendered server-side so it is correct
                                 without JavaScript; the counter module animates up to it. --}}
                            <dd class="display text-3xl text-white sm:text-4xl"
                                data-count-to="{{ $stat['value'] }}"
                                data-count-prefix="{{ $stat['prefix'] }}"
                                data-count-suffix="{{ $stat['suffix'] }}"
                                data-count-compact="{{ $stat['compact'] ? 'true' : 'false' }}"
                            >{{ $stat['prefix'] }}{{ $stat['compact'] ? compact_number($stat['value']) : number_format($stat['value']) }}{{ $stat['suffix'] }}</dd>
                            <dt class="mt-1.5 text-[11px] font-bold uppercase tracking-[0.12em] text-white/55">{{ $stat['label'] }}</dt>
                        </div>
                    @endforeach
                </dl>
            </div>
        </div>
    </section>

    {{-- ================================================================
         ANNOUNCEMENTS
         ================================================================ --}}
    @if ($announcements->isNotEmpty())
        <section class="border-b border-ink-100 bg-white">
            <div class="shell py-8">
                <div class="grid gap-4 md:grid-cols-{{ min($announcements->count(), 3) }}">
                    @foreach ($announcements as $announcement)
                        <div class="reveal flex gap-3 rounded-xl border p-4 {{ $announcement->is_urgent ? 'border-accent-200 bg-accent-50/60' : 'border-ink-100 bg-ink-50/60' }}">
                            <span class="grid size-9 shrink-0 place-items-center rounded-lg {{ $announcement->is_urgent ? 'bg-accent-100 text-accent-700' : 'bg-white text-ink-500' }}">
                                <x-ui.icon name="megaphone" class="size-4.5" />
                            </span>

                            <div class="min-w-0 flex-1">
                                <p class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-ink-900">{{ $announcement->title }}</span>
                                    @if ($announcement->is_urgent)
                                        <x-ui.badge tone="danger">{{ $announcement->priority_label }}</x-ui.badge>
                                    @endif
                                </p>
                                <p class="mt-1 line-clamp-2 text-xs leading-relaxed text-ink-600">{{ $announcement->content }}</p>

                                @if ($announcement->link_url)
                                    <a href="{{ $announcement->link_url }}" class="link-arrow mt-2 !text-xs">
                                        {{ $announcement->link_label }} <x-ui.icon name="arrow-right" class="size-3.5" />
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ================================================================
         FEATURED CAMPAIGNS
         ================================================================ --}}
    <section class="section">
        <div class="shell">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <x-ui.section-heading
                    eyebrow="Where help is needed"
                    title="Campaigns you can support today"
                    description="Each appeal shows its target, what has been raised so far and who it reaches. Nothing is rounded up, nothing is estimated."
                />

                <a href="{{ route('campaigns.index') }}" class="btn btn-outline shrink-0">
                    All campaigns <x-ui.icon name="arrow-right" class="size-4" />
                </a>
            </div>

            @php
                $showcase = $featuredCampaigns->concat($activeCampaigns)->take(6);
            @endphp

            @if ($showcase->isNotEmpty())
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($showcase as $campaign)
                        <x-campaign-card :campaign="$campaign" class="reveal" />
                    @endforeach
                </div>
            @else
                <x-ui.empty-state
                    class="mt-10"
                    icon="sparkles"
                    title="No campaigns are open right now"
                    description="New appeals are published as soon as a need is verified. Follow our stories page to hear first."
                >
                    <a href="{{ route('stories.index') }}" class="btn btn-outline btn-sm">Read the latest stories</a>
                </x-ui.empty-state>
            @endif
        </div>
    </section>

    {{-- ================================================================
         ABOUT + OUR WORK
         ================================================================ --}}
    <section class="section bg-white">
        <div class="shell">
            <div class="grid gap-12 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
                <div>
                    <x-ui.section-heading
                        eyebrow="Who we are"
                        title="A student-led organisation that shows its working"
                        :description="Str::limit(strip_tags($site->get('about', '')), 340)"
                    />

                    <div class="mt-7 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-ink-100 bg-sand p-4">
                            <x-ui.icon name="sparkles" class="size-5 text-brand-600" />
                            <h3 class="mt-2.5 text-sm font-bold text-ink-900">Our mission</h3>
                            <p class="mt-1 text-xs leading-relaxed text-ink-600">{{ Str::limit($site->get('mission', ''), 130) }}</p>
                        </div>
                        <div class="rounded-xl border border-ink-100 bg-sand p-4">
                            <x-ui.icon name="globe" class="size-5 text-brand-600" />
                            <h3 class="mt-2.5 text-sm font-bold text-ink-900">Our vision</h3>
                            <p class="mt-1 text-xs leading-relaxed text-ink-600">{{ Str::limit($site->get('vision', ''), 130) }}</p>
                        </div>
                    </div>

                    <a href="{{ route('about') }}" class="btn btn-primary mt-7">
                        More about {{ $site->name() }} <x-ui.icon name="arrow-right" class="size-4" />
                    </a>
                </div>

                {{-- Our work --}}
                <div class="grid gap-4 sm:grid-cols-2">
                    @php
                        $work = [
                            ['icon' => 'academic',   'title' => 'Education',            'text' => 'Books, bags, fees and coaching that keep children in school.', 'category' => 'education'],
                            ['icon' => 'sprout',     'title' => 'Food distribution',    'text' => 'Monthly staple parcels for households with no reliable income.', 'category' => 'food_distribution'],
                            ['icon' => 'medical',    'title' => 'Medical support',      'text' => 'Camps, medicine and surgery bills paid directly to hospitals.', 'category' => 'medical'],
                            ['icon' => 'storm',      'title' => 'Disaster relief',      'text' => 'Teams on the ground within 48 hours of a cyclone or flood.', 'category' => 'natural_disaster'],
                            ['icon' => 'hand-heart', 'title' => 'Poverty relief',       'text' => 'Cash grants and livelihood support that restore independence.', 'category' => 'poverty_relief'],
                            ['icon' => 'users',      'title' => 'Community development','text' => 'Clean water, tree planting and disaster-preparedness training.', 'category' => null],
                        ];
                    @endphp

                    @foreach ($work as $item)
                        <a
                            href="{{ $item['category'] ? route('campaigns.index', ['category' => $item['category']]) : route('projects.index') }}"
                            class="reveal card card-hover group p-5"
                        >
                            <span class="grid size-10 place-items-center rounded-xl bg-brand-50 text-brand-600 ring-1 ring-brand-100 transition group-hover:bg-brand-600 group-hover:text-white">
                                <x-ui.icon :name="$item['icon']" class="size-5" />
                            </span>
                            <h3 class="mt-3.5 text-sm font-bold text-ink-900">{{ $item['title'] }}</h3>
                            <p class="mt-1.5 text-xs leading-relaxed text-ink-600">{{ $item['text'] }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ================================================================
         IMPACT STORIES
         ================================================================ --}}
    @if ($stories->isNotEmpty())
        <section class="section">
            <div class="shell">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <x-ui.section-heading
                        eyebrow="Real people, real outcomes"
                        title="Stories from the families we walked beside"
                        description="Every story here is told with the family's permission and names their own words where we could record them."
                    />
                    <a href="{{ route('stories.index') }}" class="btn btn-outline shrink-0">
                        All stories <x-ui.icon name="arrow-right" class="size-4" />
                    </a>
                </div>

                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($stories as $story)
                        <x-story-card :story="$story" class="reveal" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ================================================================
         UPCOMING EVENTS
         ================================================================ --}}
    <section class="section bg-white">
        <div class="shell">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <x-ui.section-heading
                    eyebrow="Diary"
                    title="Upcoming events"
                    description="Orientations, medical camps and distribution days our volunteers are preparing for."
                />
                <a href="{{ route('events.index') }}" class="btn btn-outline shrink-0">
                    All events <x-ui.icon name="arrow-right" class="size-4" />
                </a>
            </div>

            @if ($events->isNotEmpty())
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($events as $event)
                        <x-event-card :event="$event" class="reveal" />
                    @endforeach
                </div>
            @else
                <x-ui.empty-state
                    class="mt-10"
                    icon="calendar"
                    title="No events scheduled"
                    description="Our next orientation, camp or distribution day will be announced here."
                />
            @endif
        </div>
    </section>

    {{-- ================================================================
         WEATHER + MAP
         ================================================================ --}}
    <section class="section">
        <div class="shell">
            <x-ui.section-heading
                align="center"
                eyebrow="Where we work"
                title="On the ground in Khulna"
                description="Conditions on the coast decide when our response teams move. We watch the forecast so we can be packed before the warning becomes an evacuation."
            />

            <div class="mt-10 grid gap-6 lg:grid-cols-[minmax(0,22rem)_1fr]">
                <x-ui.weather-card :weather="$weather" class="reveal" />

                <div class="reveal">
                    <x-ui.map
                        :latitude="$site->latitude()"
                        :longitude="$site->longitude()"
                        :title="$site->name()"
                        height="h-full min-h-[22rem]"
                    />
                </div>
            </div>
        </div>
    </section>

    {{-- ================================================================
         GALLERY STRIP
         ================================================================ --}}
    @if ($gallery->isNotEmpty())
        <section class="section bg-white">
            <div class="shell">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <x-ui.section-heading eyebrow="In pictures" title="Moments from our work" />
                    <a href="{{ route('gallery.index') }}" class="btn btn-outline shrink-0">
                        Open the gallery <x-ui.icon name="arrow-right" class="size-4" />
                    </a>
                </div>

                <div class="mt-10 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                    @foreach ($gallery as $item)
                        @if ($item->image_url)
                            <button
                                type="button"
                                data-lightbox
                                data-lightbox-src="{{ $item->image_url }}"
                                data-lightbox-title="{{ $item->title }}"
                                data-lightbox-caption="{{ $item->caption }}"
                                class="group reveal overflow-hidden rounded-xl"
                                aria-label="Open photograph: {{ $item->title }}"
                            >
                                <x-ui.media
                                    :src="$item->image_url"
                                    :alt="$item->title"
                                    :seed="$item->title"
                                    icon="photo"
                                    ratio="aspect-square"
                                    rounded="rounded-xl"
                                />
                            </button>
                        @else
                            <x-ui.media
                                class="reveal"
                                :src="null"
                                :alt="$item->title"
                                :seed="$item->title"
                                icon="photo"
                                ratio="aspect-square"
                                rounded="rounded-xl"
                            />
                        @endif
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endsection
