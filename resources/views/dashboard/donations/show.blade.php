@extends('layouts.dashboard')

@section('title', 'Donation '.$donation->reference.' — '.$site->name())
@section('heading', 'Donation '.$donation->reference)
@section('subheading', 'Recorded '.$donation->created_at->format('j F Y'))

@section('actions')
    @if ($donation->is_approved)
        <a href="{{ route('donations.receipt', $donation) }}" class="btn btn-primary" target="_blank" rel="noopener">
            <x-ui.icon name="receipt" class="size-4" /> View receipt
        </a>
    @endif
    <a href="{{ route('donations.index') }}" class="btn btn-outline">
        <x-ui.icon name="arrow-left" class="size-4" /> All donations
    </a>
@endsection

@section('panel')

    {{-- Status banner --}}
    @php
        $banners = [
            'pending' => [
                'tone' => 'border-amber-200 bg-amber-50 text-amber-900',
                'icon' => 'clock',
                'title' => 'Awaiting verification',
                'body' => 'A team member is matching this record against our bank and mobile banking statements. This usually takes up to two working days. Nothing further is needed from you.',
            ],
            'approved' => [
                'tone' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
                'icon' => 'check-badge',
                'title' => 'Verified — thank you',
                'body' => 'This donation has been checked and added to the campaign total. Your receipt is ready to print or save.',
            ],
            'rejected' => [
                'tone' => 'border-rose-200 bg-rose-50 text-rose-900',
                'icon' => 'x-circle',
                'title' => 'Could not be verified',
                'body' => 'We were unable to match this record against our statements. If you believe this is a mistake, please contact us with your transaction reference.',
            ],
        ];

        $banner = $banners[$donation->status] ?? $banners['pending'];
    @endphp

    <div class="rounded-2xl border p-5 {{ $banner['tone'] }}">
        <div class="flex gap-3.5">
            <x-ui.icon :name="$banner['icon']" class="mt-0.5 size-6 shrink-0" />
            <div class="min-w-0 flex-1">
                <h2 class="text-base font-bold">{{ $banner['title'] }}</h2>
                <p class="mt-1.5 text-sm leading-relaxed opacity-90">{{ $banner['body'] }}</p>

                @if ($donation->admin_note)
                    <div class="mt-3 rounded-xl bg-white/60 p-3">
                        <p class="text-xs font-bold uppercase tracking-wider opacity-70">Note from the team</p>
                        <p class="mt-1 text-sm leading-relaxed">{{ $donation->admin_note }}</p>
                    </div>
                @endif

                @if ($donation->status === 'rejected')
                    <a href="{{ route('contact.create') }}" class="btn btn-outline btn-sm mt-4">
                        <x-ui.icon name="mail" class="size-4" /> Contact us about this
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_18rem]">

        {{-- Details --}}
        <div class="panel">
            <div class="panel-head">
                <h2 class="panel-title">Donation details</h2>
                <button type="button" data-copy="{{ $donation->reference }}" data-copy-message="Reference copied."
                        class="btn btn-ghost btn-sm">
                    <x-ui.icon name="clipboard" class="size-3.5" /> Copy reference
                </button>
            </div>

            <dl class="divide-y divide-ink-100">
                @php
                    $rows = [
                        ['Reference', $donation->reference, 'mono'],
                        ['Amount', money($donation->amount), 'strong'],
                        ['Method', $donation->method_label, null],
                        ['Transaction reference', $donation->transaction_reference ?: '—', 'mono'],
                        ['Date of transfer', $donation->donated_on->format('l, j F Y'), null],
                        ['Recorded', $donation->created_at->format('j F Y, g:i A'), null],
                    ];
                @endphp

                @foreach ($rows as [$label, $value, $style])
                    <div class="flex flex-col gap-1 p-4 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                        <dt class="text-sm text-ink-500">{{ $label }}</dt>
                        <dd class="text-sm sm:text-right
                                   {{ $style === 'mono' ? 'font-mono text-xs font-semibold text-ink-900' : '' }}
                                   {{ $style === 'strong' ? 'text-base font-bold text-brand-700' : 'text-ink-800' }}">
                            {{ $value }}
                        </dd>
                    </div>
                @endforeach

                <div class="flex flex-col gap-1 p-4 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                    <dt class="text-sm text-ink-500">Campaign</dt>
                    <dd class="text-sm sm:text-right">
                        @if ($donation->campaign)
                            <a href="{{ route('campaigns.show', $donation->campaign) }}" class="font-semibold text-brand-700 transition hover:text-brand-800">
                                {{ $donation->campaign->title }}
                            </a>
                        @else
                            <span class="text-ink-400">General fund</span>
                        @endif
                    </dd>
                </div>

                <div class="flex flex-col gap-1 p-4 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                    <dt class="text-sm text-ink-500">Public listing</dt>
                    <dd class="text-sm sm:text-right">
                        @if ($donation->is_anonymous)
                            <x-ui.badge tone="neutral" icon="eye">Shown as anonymous</x-ui.badge>
                        @else
                            <span class="text-ink-800">Shown as {{ $donation->donor_name }}</span>
                        @endif
                    </dd>
                </div>

                @if ($donation->message)
                    <div class="p-4">
                        <dt class="text-sm text-ink-500">Your message</dt>
                        <dd class="mt-2 rounded-xl bg-ink-50 p-3.5 text-sm italic leading-relaxed text-ink-700">
                            &ldquo;{{ $donation->message }}&rdquo;
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Sidebar --}}
        <aside class="space-y-6">
            <div class="panel p-5 text-center">
                <x-ui.status-badge :status="$donation->status" :label="$donation->status_label" class="mx-auto" />
                <p class="display mt-4 text-3xl text-ink-900">{{ money($donation->amount) }}</p>
                <p class="mt-1 text-xs text-ink-500">{{ $donation->donated_on->format('j F Y') }}</p>

                @if ($donation->is_approved)
                    <a href="{{ route('donations.receipt', $donation) }}" class="btn btn-primary btn-block mt-5" target="_blank" rel="noopener">
                        <x-ui.icon name="printer" class="size-4" /> Print receipt
                    </a>
                @else
                    <p class="mt-5 rounded-xl bg-ink-50 p-3 text-xs leading-relaxed text-ink-500">
                        A receipt becomes available once this donation has been verified.
                    </p>
                @endif
            </div>

            @if ($donation->campaign)
                <div class="panel p-5">
                    <h2 class="panel-title mb-3">Campaign progress</h2>
                    <p class="line-clamp-2 text-sm font-semibold text-ink-800">{{ $donation->campaign->title }}</p>
                    <div class="mt-3">
                        <x-ui.progress :raised="$donation->campaign->raised_amount" :target="$donation->campaign->target_amount" size="sm" />
                    </div>
                    <a href="{{ route('campaigns.show', $donation->campaign) }}" class="link-arrow mt-4 !text-xs">
                        View campaign <x-ui.icon name="arrow-right" class="size-3.5" />
                    </a>
                </div>
            @endif
        </aside>
    </div>

@endsection
