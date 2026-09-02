@extends('layouts.dashboard')

@section('title', 'Record a donation — '.$site->name())
@section('heading', 'Record a donation')
@section('subheading', 'Transfer first, then record it here so we can verify it and issue your receipt.')

@section('panel')

    {{-- How this works --}}
    <div class="mb-6 rounded-2xl border border-brand-100 bg-brand-50 p-5">
        <div class="flex gap-3">
            <x-ui.icon name="info" class="mt-0.5 size-5 shrink-0 text-brand-600" />
            <div class="min-w-0">
                <h2 class="text-sm font-bold text-brand-900">This site does not take card payments</h2>
                <p class="mt-1.5 text-sm leading-relaxed text-brand-800">
                    You transfer your contribution directly by bank, bKash, Nagad, Rocket or cash — then record it
                    below with the transaction reference. A team member matches it against our statement, usually
                    within two working days. Only then does it count towards a campaign total.
                </p>
                <a href="{{ route('get-involved') }}" class="link-arrow mt-2.5 !text-xs">
                    See our bank &amp; mobile banking details <x-ui.icon name="arrow-right" class="size-3.5" />
                </a>
            </div>
        </div>
    </div>

    <x-form.errors class="mb-6" />

    @if ($campaigns->isEmpty())
        <x-ui.empty-state
            icon="heart"
            title="No campaigns are open for donations right now"
            description="New appeals are published as soon as a need is verified. Please check back shortly."
        >
            <a href="{{ route('campaigns.index') }}" class="btn btn-primary btn-sm">Browse all campaigns</a>
        </x-ui.empty-state>
    @else
        <form method="POST" action="{{ route('donations.store') }}" data-validate class="panel p-6 sm:p-8">
            @csrf

            {{-- Campaign --}}
            <fieldset>
                <legend class="display text-lg text-ink-900">Which campaign?</legend>
                <p class="mt-1 text-sm text-ink-600">Only campaigns currently accepting donations are listed.</p>

                <div class="mt-4">
                    <x-form.select
                        name="campaign_id" label="Campaign" :required="true"
                        :value="$selected?->id"
                        placeholder="Choose a campaign…"
                        rules="required"
                        :options="$campaigns->mapWithKeys(fn ($c) => [$c->id => $c->title.'  ·  '.config('site.campaign_categories.'.$c->category)])->all()"
                    />
                </div>

                @if ($selected)
                    <div class="mt-4 rounded-xl border border-ink-100 bg-ink-50 p-4">
                        <p class="text-sm font-bold text-ink-900">{{ $selected->title }}</p>
                        <p class="mt-1 text-xs leading-relaxed text-ink-600">{{ $selected->short_description }}</p>
                        <div class="mt-3">
                            <x-ui.progress :raised="$selected->raised_amount" :target="$selected->target_amount" size="sm" />
                        </div>
                    </div>
                @endif
            </fieldset>

            {{-- Amount and method --}}
            <fieldset class="mt-8 border-t border-ink-100 pt-8">
                <legend class="display text-lg text-ink-900">How much, and how did you send it?</legend>

                <div class="mt-4 grid gap-5 sm:grid-cols-2">
                    <x-form.field
                        name="amount" label="Amount" type="number" :required="true"
                        :prefix="config('site.currency.symbol')"
                        placeholder="1000" min="50" step="1"
                        rules="required|numeric|minval:50|maxval:10000000"
                        help="Minimum {{ money(50) }}."
                    />

                    <x-form.select
                        name="method" label="Donation method" :required="true"
                        :options="$methods" placeholder="Choose a method…"
                        rules="required"
                    />
                </div>

                {{-- Quick amounts: a convenience only; the field is still validated server-side. --}}
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="text-xs font-semibold text-ink-500">Common amounts:</span>
                    @foreach ([500, 1000, 2850, 5000, 10000, 26000] as $preset)
                        <button type="button"
                                onclick="document.getElementById('amount').value={{ $preset }};document.getElementById('amount').dispatchEvent(new Event('input'))"
                                class="btn btn-outline btn-sm">{{ money($preset) }}</button>
                    @endforeach
                </div>

                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <x-form.field
                        name="transaction_reference" label="Transaction / reference number"
                        placeholder="e.g. 8N4K2P9X1M"
                        help="From your bank slip or mobile banking SMS. It is how we match your record."
                    />

                    <x-form.field
                        name="donated_on" label="Date of transfer" type="date" :required="true"
                        :value="old('donated_on', now()->toDateString())"
                        :max="now()->toDateString()"
                        rules="required|notfuture"
                    />
                </div>
            </fieldset>

            {{-- Donor details --}}
            <fieldset class="mt-8 border-t border-ink-100 pt-8">
                <legend class="display text-lg text-ink-900">Your details</legend>
                <p class="mt-1 text-sm text-ink-600">Pre-filled from your account — correct them if the transfer was made under a different name.</p>

                <div class="mt-4 grid gap-5 sm:grid-cols-2">
                    <x-form.field
                        name="donor_name" label="Full name" :required="true"
                        :value="auth()->user()->name" icon="user"
                        rules="required|min:3|max:120" autocomplete="name"
                    />

                    <x-form.field
                        name="donor_email" label="Email address" type="email" :required="true"
                        :value="auth()->user()->email" icon="mail"
                        rules="required|email" autocomplete="email"
                    />
                </div>

                <div class="mt-5">
                    <x-form.field
                        name="donor_phone" label="Phone number" type="tel"
                        :value="auth()->user()->phone" icon="phone"
                        rules="phone" autocomplete="tel"
                        help="Optional — helps us reach you if a reference does not match."
                    />
                </div>

                <div class="mt-5">
                    <x-form.textarea
                        name="message" label="Message (optional)"
                        placeholder="Anything you would like the team to know, or a dedication."
                        :rows="3" :maxlength="1000"
                    />
                </div>

                <div class="mt-5">
                    <x-form.checkbox
                        name="is_anonymous" label="Show my donation as anonymous"
                        help="Your name is still recorded for our accounts and your receipt, but it will not appear on the public supporters list."
                    />
                </div>
            </fieldset>

            {{-- Submit --}}
            <div class="mt-8 border-t border-ink-100 pt-6">
                <div class="rounded-xl bg-ink-50 p-4">
                    <p class="flex gap-2.5 text-xs leading-relaxed text-ink-600">
                        <x-ui.icon name="shield-check" class="size-4 shrink-0 text-brand-600" />
                        <span>
                            Your record is saved as <strong>pending</strong>. Neither the amount nor the status can be
                            changed after submission — only the administrator can verify it. That is what makes the
                            totals on this site trustworthy.
                        </span>
                    </p>
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    <button type="submit" class="btn btn-primary btn-lg" data-loading-label="Recording…">
                        <x-ui.icon name="heart" class="size-5" /> Record this donation
                    </button>
                    <a href="{{ route('donations.index') }}" class="btn btn-outline btn-lg">Cancel</a>
                </div>
            </div>
        </form>
    @endif

@endsection
