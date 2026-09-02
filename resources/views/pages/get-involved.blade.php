@extends('layouts.app')

@section('title', 'Get involved — '.$site->name())
@section('description', 'Four ways to support KUET TRY: donate, volunteer, become a regular supporter, or help spread the word.')

@section('content')

    <x-layout.page-hero
        eyebrow="Four ways to help"
        title="Get involved"
        description="Money, time, skills or reach — all four are useful, and none of them is a lesser contribution than the others."
        :breadcrumbs="['Get Involved' => null]"
    />

    {{-- The four routes in --}}
    <section class="section">
        <div class="shell">
            @php
                $ways = [
                    [
                        'icon' => 'heart', 'tone' => 'brand', 'title' => 'Donate',
                        'text' => 'Record a one-off or regular gift against a specific campaign. You choose where it goes, and you get a receipt once we have verified it.',
                        'action' => 'Make a donation', 'url' => route('donations.create'),
                        'points' => ['Bank transfer, bKash, Nagad, Rocket or cash', 'Verified within two working days', 'Printable receipt in your dashboard'],
                    ],
                    [
                        'icon' => 'users', 'tone' => 'accent', 'title' => 'Volunteer',
                        'text' => 'Join a distribution team, teach a coaching session, help at a medical camp or take a shift packing parcels.',
                        'action' => 'Apply to volunteer', 'url' => route('volunteer.index'),
                        'points' => ['Weekends, evenings or flexible', 'One orientation session to start', 'No prior experience needed'],
                    ],
                    [
                        'icon' => 'shield-check', 'tone' => 'success', 'title' => 'Become a regular supporter',
                        'text' => 'A standing monthly gift is what lets us commit to the 200 households on the food list a year in advance.',
                        'action' => 'Support monthly', 'url' => route('donations.create'),
                        'points' => [money(2850).' feeds one household for a month', money(26000).' sponsors one child for a year', 'Cancel any time — just tell us'],
                    ],
                    [
                        'icon' => 'megaphone', 'tone' => 'info', 'title' => 'Spread the word',
                        'text' => 'Most of our donors heard about us from someone they trust. Sharing a campaign costs nothing and works better than any advertisement.',
                        'action' => 'Browse campaigns to share', 'url' => route('campaigns.index'),
                        'points' => ['Share a campaign link', 'Follow us on social media', 'Introduce us to your employer'],
                    ],
                ];
            @endphp

            <div class="grid gap-6 lg:grid-cols-2">
                @foreach ($ways as $way)
                    @php
                        $tones = [
                            'brand' => 'bg-brand-50 text-brand-600 ring-brand-100',
                            'accent' => 'bg-accent-50 text-accent-600 ring-accent-100',
                            'success' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
                            'info' => 'bg-sky-50 text-sky-600 ring-sky-100',
                        ];
                    @endphp

                    <article class="reveal card flex flex-col p-6 sm:p-7">
                        <span class="grid size-12 place-items-center rounded-xl ring-1 {{ $tones[$way['tone']] }}">
                            <x-ui.icon :name="$way['icon']" class="size-6" />
                        </span>

                        <h2 class="display mt-4 text-xl text-ink-900">{{ $way['title'] }}</h2>
                        <p class="mt-2.5 text-sm leading-relaxed text-ink-600">{{ $way['text'] }}</p>

                        <ul class="mt-4 flex-1 space-y-2">
                            @foreach ($way['points'] as $point)
                                <li class="flex gap-2 text-sm text-ink-600">
                                    <x-ui.icon name="check" class="mt-0.5 size-4 shrink-0 text-brand-600" />
                                    <span>{{ $point }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <a href="{{ $way['url'] }}" class="btn btn-primary mt-6 w-fit">
                            {{ $way['action'] }} <x-ui.icon name="arrow-right" class="size-4" />
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- How donations work --}}
    <section class="section bg-white">
        <div class="shell">
            <x-ui.section-heading
                align="center"
                eyebrow="No surprises"
                title="What happens after you give"
                description="This site does not process card payments. You transfer directly, then record it here — which is why every figure on the site can be traced to a statement line."
            />

            @php
                $steps = [
                    ['n' => '1', 'title' => 'Transfer',   'text' => 'Send your contribution by bank transfer, bKash, Nagad, Rocket or cash at our office. Keep the transaction ID.'],
                    ['n' => '2', 'title' => 'Record it',  'text' => 'Sign in, pick the campaign and fill in the short donation form with the amount and reference.'],
                    ['n' => '3', 'title' => 'We verify',  'text' => 'A team member matches your record against our statement — usually within two working days.'],
                    ['n' => '4', 'title' => 'Receipt',    'text' => 'Once approved, the campaign total updates and a printable receipt appears in your donation history.'],
                ];
            @endphp

            <ol class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($steps as $step)
                    <li class="reveal relative">
                        <div class="card h-full p-5">
                            <span class="display grid size-11 place-items-center rounded-xl bg-brand-600 text-lg text-white">{{ $step['n'] }}</span>
                            <h3 class="mt-4 text-sm font-bold text-ink-900">{{ $step['title'] }}</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-ink-600">{{ $step['text'] }}</p>
                        </div>

                        @unless ($loop->last)
                            <span class="absolute -right-3 top-9 hidden text-ink-300 lg:block" aria-hidden="true">
                                <x-ui.icon name="chevron-right" class="size-5" />
                            </span>
                        @endunless
                    </li>
                @endforeach
            </ol>

            {{-- Payment details --}}
            <div class="mt-10 grid gap-6 lg:grid-cols-2">
                <div class="panel p-6">
                    <div class="flex items-center gap-2.5">
                        <x-ui.icon name="banknotes" class="size-5 text-brand-600" />
                        <h3 class="text-base font-bold text-ink-900">Bank transfer</h3>
                    </div>
                    <pre class="mt-4 whitespace-pre-wrap rounded-xl bg-ink-50 p-4 font-sans text-sm leading-relaxed text-ink-700">{{ $site->get('bank_details') }}</pre>
                    <button type="button" data-copy="{{ $site->get('bank_details') }}" data-copy-message="Bank details copied." class="btn btn-outline btn-sm mt-3">
                        <x-ui.icon name="clipboard" class="size-4" /> Copy details
                    </button>
                </div>

                <div class="panel p-6">
                    <div class="flex items-center gap-2.5">
                        <x-ui.icon name="phone" class="size-5 text-brand-600" />
                        <h3 class="text-base font-bold text-ink-900">Mobile banking</h3>
                    </div>
                    <pre class="mt-4 whitespace-pre-wrap rounded-xl bg-ink-50 p-4 font-sans text-sm leading-relaxed text-ink-700">{{ $site->get('mobile_banking_details') }}</pre>
                    <button type="button" data-copy="{{ $site->get('mobile_banking_details') }}" data-copy-message="Mobile banking details copied." class="btn btn-outline btn-sm mt-3">
                        <x-ui.icon name="clipboard" class="size-4" /> Copy details
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- Urgent campaigns --}}
    @if ($urgentCampaigns->isNotEmpty())
        <section class="section">
            <div class="shell">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <x-ui.section-heading eyebrow="Needs support now" title="Where your gift goes furthest today" />
                    <a href="{{ route('campaigns.index') }}" class="btn btn-outline shrink-0">
                        All campaigns <x-ui.icon name="arrow-right" class="size-4" />
                    </a>
                </div>

                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($urgentCampaigns as $campaign)
                        <x-campaign-card :campaign="$campaign" class="reveal" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endsection
