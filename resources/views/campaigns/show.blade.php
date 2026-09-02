@extends('layouts.app')

@section('title', $campaign->title.' — '.$site->name())
@section('description', Str::limit($campaign->short_description, 155))
@section('og:type', 'article')
@if ($campaign->image_url)
    @section('og:image', $campaign->image_url)
@endif

@section('content')

    @php
        $icons = [
            'poverty_relief' => 'hand-heart', 'natural_disaster' => 'storm',
            'education' => 'academic', 'medical' => 'medical',
            'food_distribution' => 'sprout', 'emergency_relief' => 'shield-check',
            'winter_support' => 'snow', 'orphan_support' => 'users', 'other' => 'sparkles',
        ];
    @endphp

    {{-- ================= HERO ================= --}}
    <section class="relative overflow-hidden bg-brand-950">
        <div class="absolute inset-0" aria-hidden="true">
            @if ($campaign->image_url)
                <img src="{{ $campaign->image_url }}" alt="" class="size-full object-cover opacity-35">
            @else
                <div class="absolute inset-0 bg-[radial-gradient(130%_120%_at_25%_0%,#0f7d5a_0%,#0c4234_50%,#05261e_100%)]"></div>
                <div class="grain absolute inset-0 opacity-50"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-brand-950 via-brand-950/85 to-brand-950/60"></div>
        </div>

        <div class="shell relative py-10 sm:py-14">
            <x-ui.breadcrumbs
                :items="['Campaigns' => route('campaigns.index'), Str::limit($campaign->title, 40) => null]"
                class="[&_a]:text-white/55 [&_a:hover]:text-white [&_span]:text-white/85 [&_svg]:text-white/35"
            />

            <div class="mt-6 flex flex-wrap items-center gap-2">
                <x-ui.badge tone="accent">{{ $campaign->category_label }}</x-ui.badge>
                <x-ui.status-badge :status="$campaign->status" :label="$campaign->status_label" />

                @if ($campaign->is_emergency)
                    <x-ui.badge tone="danger" icon="exclamation">Emergency appeal</x-ui.badge>
                @endif
                @if ($campaign->featured)
                    <x-ui.badge tone="brand" icon="sparkles">Featured</x-ui.badge>
                @endif
            </div>

            <h1 class="display text-shadow-hero mt-4 max-w-3xl text-3xl text-white sm:text-4xl lg:text-5xl">
                {{ $campaign->title }}
            </h1>

            <p class="mt-4 max-w-2xl text-base leading-relaxed text-white/75">{{ $campaign->short_description }}</p>

            <ul class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-white/65">
                @if ($campaign->location)
                    <li class="flex items-center gap-1.5">
                        <x-ui.icon name="map-pin" class="size-4 text-brand-300" /> {{ $campaign->location }}
                    </li>
                @endif
                <li class="flex items-center gap-1.5">
                    <x-ui.icon name="calendar" class="size-4 text-brand-300" />
                    Started {{ $campaign->start_date->format('j F Y') }}
                </li>
                @if ($campaign->end_date)
                    <li class="flex items-center gap-1.5">
                        <x-ui.icon name="clock" class="size-4 text-brand-300" />
                        Ends {{ $campaign->end_date->format('j F Y') }}
                    </li>
                @endif
                @if ($campaign->beneficiaries_count)
                    <li class="flex items-center gap-1.5">
                        <x-ui.icon name="users" class="size-4 text-brand-300" />
                        {{ number_format($campaign->beneficiaries_count) }} people reached
                    </li>
                @endif
            </ul>
        </div>
    </section>

    {{-- ================= BODY ================= --}}
    <section class="section">
        <div class="shell">
            <div class="grid gap-10 lg:grid-cols-[1fr_22rem] lg:gap-12">

                {{-- Main column --}}
                <div class="min-w-0">
                    @unless ($campaign->image_url)
                        <x-ui.media
                            :src="null"
                            :alt="$campaign->title"
                            :seed="$campaign->slug"
                            :icon="$icons[$campaign->category] ?? 'sparkles'"
                            :label="$campaign->category_label"
                            ratio="aspect-[16/7]"
                            rounded="rounded-2xl"
                            class="mb-8"
                        />
                    @endunless

                    <h2 class="display text-2xl text-ink-900">About this appeal</h2>
                    <div class="prose-kt mt-4">{!! $campaign->description !!}</div>

                    {{-- Campaign updates --}}
                    @if ($campaign->updates->isNotEmpty())
                        <div class="mt-12">
                            <h2 class="display text-2xl text-ink-900">Updates from the field</h2>

                            <ol class="mt-6 space-y-6 border-l-2 border-ink-100 pl-6">
                                @foreach ($campaign->updates as $update)
                                    <li class="relative">
                                        <span class="absolute -left-[1.9rem] top-1.5 grid size-4 place-items-center rounded-full bg-brand-600 ring-4 ring-sand"></span>

                                        <time datetime="{{ $update->published_on->toDateString() }}"
                                              class="text-xs font-bold uppercase tracking-wider text-brand-700">
                                            {{ $update->published_on->format('j F Y') }}
                                        </time>

                                        <h3 class="mt-1.5 text-base font-bold text-ink-900">{{ $update->title }}</h3>
                                        <p class="mt-1.5 text-sm leading-relaxed text-ink-600">{{ $update->body }}</p>

                                        @if ($update->image_url)
                                            <img src="{{ $update->image_url }}" alt="" loading="lazy"
                                                 class="mt-3 w-full max-w-md rounded-xl object-cover">
                                        @endif
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    @endif

                    {{-- Recent supporters --}}
                    @if ($recentDonors->isNotEmpty())
                        <div class="mt-12">
                            <h2 class="display text-2xl text-ink-900">Recent supporters</h2>
                            <p class="mt-1.5 text-sm text-ink-500">
                                Verified donations only. Donors who asked to stay anonymous are shown as such.
                            </p>

                            <ul class="mt-5 divide-y divide-ink-100 overflow-hidden rounded-xl border border-ink-100 bg-white">
                                @foreach ($recentDonors as $donor)
                                    <li class="flex items-start gap-3 p-4">
                                        <x-ui.avatar
                                            :initials="$donor->is_anonymous ? '?' : Str::upper(Str::substr($donor->donor_name, 0, 1))"
                                            size="sm"
                                        />
                                        <div class="min-w-0 flex-1">
                                            <p class="flex flex-wrap items-baseline justify-between gap-2">
                                                <span class="text-sm font-bold text-ink-900">{{ $donor->public_donor_name }}</span>
                                                <span class="text-sm font-bold text-brand-700">{{ money($donor->amount) }}</span>
                                            </p>
                                            @if ($donor->message)
                                                <p class="mt-1 text-xs italic leading-relaxed text-ink-600">&ldquo;{{ $donor->message }}&rdquo;</p>
                                            @endif
                                            <p class="mt-1 text-[11px] text-ink-400">{{ $donor->donated_on->diffForHumans() }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Location map --}}
                    @if ($campaign->has_coordinates)
                        <div class="mt-12">
                            <h2 class="display text-2xl text-ink-900">Where this appeal works</h2>
                            <p class="mt-1.5 text-sm text-ink-500">{{ $campaign->location }}</p>
                            <x-ui.map
                                class="mt-5"
                                :latitude="$campaign->latitude"
                                :longitude="$campaign->longitude"
                                :title="$campaign->title"
                                :zoom="11"
                                height="h-72"
                            />
                        </div>
                    @endif
                </div>

                {{-- Sticky donation panel --}}
                <aside class="lg:sticky lg:top-24 lg:h-fit">
                    <div class="panel overflow-hidden">
                        <div class="border-b border-ink-100 bg-gradient-to-br from-brand-50 to-white p-5">
                            <x-ui.progress
                                :raised="$campaign->raised_amount"
                                :target="$campaign->target_amount"
                                :tone="$campaign->is_emergency ? 'accent' : 'brand'"
                            />

                            <dl class="mt-5 grid grid-cols-3 gap-2 text-center">
                                <div>
                                    <dt class="text-[10px] font-bold uppercase tracking-wider text-ink-400">Supporters</dt>
                                    <dd class="mt-0.5 text-base font-bold text-ink-900">{{ $recentDonors->count() ? $campaign->approvedDonations()->count() : 0 }}</dd>
                                </div>
                                <div class="border-x border-ink-200/70">
                                    <dt class="text-[10px] font-bold uppercase tracking-wider text-ink-400">Days left</dt>
                                    <dd class="mt-0.5 text-base font-bold text-ink-900">{{ $campaign->days_left ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[10px] font-bold uppercase tracking-wider text-ink-400">Status</dt>
                                    <dd class="mt-0.5 text-base font-bold text-ink-900">{{ $campaign->status_label }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="p-5">
                            @if ($campaign->is_open)
                                <a href="{{ route('donations.create', ['campaign' => $campaign->slug]) }}" class="btn btn-primary btn-lg btn-block">
                                    <x-ui.icon name="heart" class="size-5" /> Donate to this campaign
                                </a>

                                @guest
                                    <p class="help mt-2.5 text-center">
                                        You will be asked to sign in or create a free account first — that is how you get a receipt.
                                    </p>
                                @endguest
                            @else
                                <div class="rounded-xl border border-ink-100 bg-ink-50 p-4 text-center">
                                    <x-ui.icon name="check-badge" class="mx-auto size-6 text-brand-600" />
                                    <p class="mt-2 text-sm font-bold text-ink-900">
                                        {{ $campaign->status === \App\Models\Campaign::STATUS_COMPLETED ? 'This campaign is complete' : 'This campaign is closed' }}
                                    </p>
                                    <p class="mt-1 text-xs leading-relaxed text-ink-600">
                                        Thank you to everyone who gave. Other appeals still need support.
                                    </p>
                                    <a href="{{ route('campaigns.index', ['status' => 'active']) }}" class="btn btn-outline btn-sm mt-3">
                                        See open campaigns
                                    </a>
                                </div>
                            @endif

                            <div class="mt-4 space-y-2.5 border-t border-ink-100 pt-4 text-xs text-ink-500">
                                <p class="flex gap-2">
                                    <x-ui.icon name="shield-check" class="size-4 shrink-0 text-brand-600" />
                                    <span>Every donation is checked against our bank statement before it counts here.</span>
                                </p>
                                <p class="flex gap-2">
                                    <x-ui.icon name="receipt" class="size-4 shrink-0 text-brand-600" />
                                    <span>You get a printable receipt as soon as yours is verified.</span>
                                </p>
                            </div>

                            <div class="mt-4 flex gap-2 border-t border-ink-100 pt-4">
                                <button type="button" data-copy="{{ route('campaigns.show', $campaign) }}"
                                        data-copy-message="Campaign link copied. Thank you for sharing."
                                        class="btn btn-outline btn-sm flex-1">
                                    <x-ui.icon name="clipboard" class="size-4" /> Copy link
                                </button>
                                <a href="{{ route('contact.create') }}" class="btn btn-outline btn-sm flex-1">
                                    <x-ui.icon name="mail" class="size-4" /> Ask a question
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Recently viewed — read from the cookie set by the middleware --}}
                    @if ($recentlyViewed->isNotEmpty())
                        <div class="panel mt-6 p-5">
                            <h2 class="panel-title mb-3">You looked at these recently</h2>
                            <ul class="space-y-3">
                                @foreach ($recentlyViewed as $recent)
                                    <li>
                                        <a href="{{ route('campaigns.show', $recent) }}" class="group flex items-center gap-3">
                                            <x-ui.media :src="$recent->image_url" :alt="$recent->title" :seed="$recent->slug"
                                                        :icon="$icons[$recent->category] ?? 'sparkles'"
                                                        ratio="aspect-square" rounded="rounded-lg" class="w-12 shrink-0" />
                                            <span class="min-w-0 flex-1">
                                                <span class="line-clamp-2 text-xs font-semibold text-ink-800 transition group-hover:text-brand-700">{{ $recent->title }}</span>
                                                <span class="mt-0.5 block text-[11px] text-ink-500">{{ rtrim(rtrim(number_format($recent->progress_percent, 1), '0'), '.') }}% funded</span>
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </aside>
            </div>
        </div>
    </section>

    {{-- ================= RELATED ================= --}}
    @if ($related->isNotEmpty())
        <section class="section bg-white">
            <div class="shell">
                <x-ui.section-heading eyebrow="Keep going" title="Related campaigns" />

                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $item)
                        <x-campaign-card :campaign="$item" class="reveal" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endsection
