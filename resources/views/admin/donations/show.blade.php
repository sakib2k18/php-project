@extends('layouts.admin')

@section('title', 'Review donation')
@section('heading', 'Donation '.$donation->reference)
@section('subheading', 'Verify this record against the bank or mobile banking statement')

@section('content')

    <x-admin.page-header :title="'Donation '.$donation->reference" :description="'Recorded '.$donation->created_at->format('j F Y, g:i A')">
        <a href="{{ route('admin.donations.index') }}" class="btn btn-outline">
            <x-ui.icon name="arrow-left" class="size-4" /> All donations
        </a>
    </x-admin.page-header>

    <div class="grid gap-6 xl:grid-cols-[1fr_22rem]">

        {{-- Details --}}
        <div class="space-y-6">
            <div class="panel">
                <div class="panel-head">
                    <h3 class="panel-title">Record details</h3>
                    <x-ui.status-badge :status="$donation->status" :label="$donation->status_label" />
                </div>

                <dl class="divide-y divide-ink-100">
                    @php
                        $rows = [
                            ['Reference', $donation->reference, 'mono'],
                            ['Amount', money($donation->amount), 'amount'],
                            ['Donation method', $donation->method_label, null],
                            ['Transaction reference', $donation->transaction_reference ?: '— not provided —', 'mono'],
                            ['Date of transfer', $donation->donated_on->format('l, j F Y'), null],
                            ['Recorded at', $donation->created_at->format('j F Y, g:i A'), null],
                        ];
                    @endphp

                    @foreach ($rows as [$label, $value, $style])
                        <div class="flex flex-col gap-1 p-4 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                            <dt class="text-sm text-ink-500">{{ $label }}</dt>
                            <dd class="text-sm sm:text-right
                                       {{ $style === 'mono' ? 'font-mono text-xs font-semibold text-ink-900' : '' }}
                                       {{ $style === 'amount' ? 'text-lg font-bold text-brand-700' : 'text-ink-800' }}">
                                {{ $value }}
                            </dd>
                        </div>
                    @endforeach

                    <div class="flex flex-col gap-1 p-4 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                        <dt class="text-sm text-ink-500">Campaign</dt>
                        <dd class="text-sm sm:text-right">
                            @if ($donation->campaign)
                                <a href="{{ route('admin.campaigns.show', $donation->campaign) }}" class="font-semibold text-brand-700 hover:underline">
                                    {{ $donation->campaign->title }}
                                </a>
                            @else
                                <span class="text-ink-400">General fund</span>
                            @endif
                        </dd>
                    </div>

                    <div class="flex flex-col gap-1 p-4 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                        <dt class="text-sm text-ink-500">Counted in campaign total</dt>
                        <dd class="text-sm sm:text-right">
                            @if ($donation->counted_in_campaign)
                                <x-ui.badge tone="success" icon="check">Yes</x-ui.badge>
                            @else
                                <x-ui.badge tone="neutral">No</x-ui.badge>
                            @endif
                        </dd>
                    </div>

                    @if ($donation->message)
                        <div class="p-4">
                            <dt class="text-sm text-ink-500">Donor message</dt>
                            <dd class="mt-2 rounded-xl bg-ink-50 p-3.5 text-sm italic leading-relaxed text-ink-700">
                                &ldquo;{{ $donation->message }}&rdquo;
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Donor --}}
            <div class="panel">
                <div class="panel-head">
                    <h3 class="panel-title">Donor</h3>
                    @if ($donation->is_anonymous)
                        <x-ui.badge tone="neutral" icon="eye">Anonymous publicly</x-ui.badge>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-4 p-5">
                    <x-ui.avatar :src="$donation->user?->avatar_url" :initials="Str::upper(Str::substr($donation->donor_name, 0, 1))" size="lg" />

                    <div class="min-w-0 flex-1">
                        <p class="text-base font-bold text-ink-900">{{ $donation->donor_name }}</p>
                        <p class="mt-0.5 text-sm text-ink-600">{{ $donation->donor_email }}</p>
                        @if ($donation->donor_phone)
                            <p class="text-sm text-ink-600">{{ $donation->donor_phone }}</p>
                        @endif
                    </div>

                    @if ($donation->user)
                        <a href="{{ route('admin.users.show', $donation->user) }}" class="btn btn-outline btn-sm shrink-0">
                            <x-ui.icon name="user-circle" class="size-4" /> View account
                        </a>
                    @else
                        <x-ui.badge tone="neutral">No linked account</x-ui.badge>
                    @endif
                </div>
            </div>

            {{-- Review history --}}
            @if ($donation->reviewed_at)
                <div class="panel p-5">
                    <h3 class="panel-title mb-3">Review history</h3>
                    <p class="text-sm text-ink-600">
                        Last reviewed by <span class="font-semibold text-ink-900">{{ $donation->reviewer?->name ?? 'Administrator' }}</span>
                        on {{ $donation->reviewed_at->format('j F Y, g:i A') }}.
                    </p>
                    @if ($donation->admin_note)
                        <div class="mt-3 rounded-xl bg-ink-50 p-3.5">
                            <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Note</p>
                            <p class="mt-1 text-sm leading-relaxed text-ink-700">{{ $donation->admin_note }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Review actions --}}
        <aside class="space-y-6">
            <div class="panel p-5">
                <h3 class="panel-title mb-1">Verification</h3>
                <p class="mb-5 text-xs leading-relaxed text-ink-500">
                    Approving adds {{ money($donation->amount) }} to the campaign total inside a database transaction
                    and notifies the donor. Rejecting reverses the credit if it was already applied.
                </p>

                <form method="POST" action="{{ route('admin.donations.review', $donation) }}" class="space-y-4">
                    @csrf

                    <x-form.textarea
                        name="admin_note" label="Note (optional)"
                        :value="$donation->admin_note"
                        placeholder="e.g. Matched against bKash statement line 142."
                        :rows="3" :maxlength="500"
                    />

                    <div class="space-y-2">
                        @unless ($donation->is_approved)
                            <button type="submit" name="decision" value="approve" class="btn btn-primary btn-block">
                                <x-ui.icon name="check-badge" class="size-4" /> Approve donation
                            </button>
                        @endunless

                        @if ($donation->status !== 'rejected')
                            <button type="submit" name="decision" value="reject" class="btn btn-outline btn-block !text-rose-600 hover:!border-rose-300 hover:!bg-rose-50">
                                <x-ui.icon name="x-circle" class="size-4" /> Reject donation
                            </button>
                        @endif

                        @unless ($donation->is_pending)
                            <button type="submit" name="decision" value="pending" class="btn btn-ghost btn-block">
                                <x-ui.icon name="refresh" class="size-4" /> Move back to pending
                            </button>
                        @endunless
                    </div>
                </form>
            </div>

            @if ($donation->campaign)
                <div class="panel p-5">
                    <h3 class="panel-title mb-3">Campaign impact</h3>
                    <p class="line-clamp-2 text-sm font-semibold text-ink-800">{{ $donation->campaign->title }}</p>
                    <div class="mt-3">
                        <x-ui.progress :raised="$donation->campaign->raised_amount" :target="$donation->campaign->target_amount" />
                    </div>
                </div>
            @endif

            {{-- Danger --}}
            <div class="panel border-rose-200 p-5">
                <h3 class="panel-title mb-2 text-rose-700">Delete record</h3>
                <p class="mb-4 text-xs leading-relaxed text-ink-500">
                    Prefer rejecting over deleting — a rejected record keeps the audit trail. Deleting also reverses
                    any campaign credit this donation holds.
                </p>

                <form method="POST" action="{{ route('admin.donations.destroy', $donation) }}"
                      data-confirm="Donation {{ $donation->reference }} will be permanently deleted and its campaign credit reversed. Rejecting is usually the better option."
                      data-confirm-title="Delete this donation record?" data-confirm-action="Delete permanently">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <x-ui.icon name="trash" class="size-4" /> Delete record
                    </button>
                </form>
            </div>
        </aside>
    </div>

@endsection
