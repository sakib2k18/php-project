@extends('layouts.dashboard')

@section('title', 'My dashboard — '.$site->name())
@section('heading', 'Welcome back, '.Str::before(auth()->user()->name, ' '))
@section('subheading', 'Here is where your support stands today.')

@section('actions')
    <a href="{{ route('donations.create') }}" class="btn btn-primary">
        <x-ui.icon name="heart" class="size-4" /> Record a donation
    </a>
@endsection

@section('panel')

    {{-- Unread notifications --}}
    @if ($unreadNotifications->isNotEmpty())
        <div class="mb-6 space-y-2">
            @foreach ($unreadNotifications as $notification)
                @php
                    $data = $notification->data;
                    $tones = [
                        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
                        'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
                        'info' => 'border-sky-200 bg-sky-50 text-sky-900',
                    ];
                @endphp

                <div class="flex items-start gap-3 rounded-xl border p-4 {{ $tones[$data['tone'] ?? 'info'] ?? $tones['info'] }}">
                    <x-ui.icon :name="$data['icon'] ?? 'info'" class="mt-0.5 size-5 shrink-0" />
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold">{{ $data['title'] ?? 'Notification' }}</p>
                        <p class="mt-0.5 text-sm leading-relaxed opacity-90">{{ $data['message'] ?? '' }}</p>
                    </div>

                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="shrink-0">
                        @csrf
                        <button type="submit" class="rounded-lg p-1.5 opacity-60 transition hover:bg-black/5 hover:opacity-100" aria-label="Mark as read and open">
                            <x-ui.icon name="arrow-right" class="size-4" />
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card
            label="Total donations" icon="receipt" tone="brand"
            :value="number_format($stats['total'])"
            :hint="$stats['total'] === 1 ? 'record' : 'records'"
            :href="route('donations.index')"
        />
        <x-ui.stat-card
            label="Verified amount" icon="check-badge" tone="success"
            :value="money($stats['approved_amount'])"
            :hint="$stats['approved_count'].' approved'"
        />
        <x-ui.stat-card
            label="Awaiting verification" icon="clock" tone="warning"
            :value="money($stats['pending_amount'])"
            :hint="$stats['pending_count'].' pending'"
            :href="route('donations.index', ['status' => 'pending'])"
        />
        <x-ui.stat-card
            label="Volunteer status" icon="users"
            :tone="$volunteer ? ($volunteer->status === 'approved' ? 'success' : ($volunteer->status === 'rejected' ? 'danger' : 'warning')) : 'neutral'"
            :value="$volunteer ? $volunteer->status_label : 'Not applied'"
            :hint="$volunteer ? 'Applied '.$volunteer->created_at->diffForHumans() : 'Apply any time'"
            :href="route('volunteer.index')"
        />
    </div>

    {{-- Recent donations --}}
    <div class="panel mt-6">
        <div class="panel-head">
            <h2 class="panel-title">Recent donations</h2>
            <a href="{{ route('donations.index') }}" class="link-arrow !text-xs">
                View all <x-ui.icon name="arrow-right" class="size-3.5" />
            </a>
        </div>

        @if ($recentDonations->isNotEmpty())
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Reference</th>
                            <th scope="col">Campaign</th>
                            <th scope="col">Amount</th>
                            <th scope="col">Date</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentDonations as $donation)
                            <tr>
                                <td class="font-mono text-xs font-semibold text-ink-900">{{ $donation->reference }}</td>
                                <td class="max-w-[14rem]">
                                    @if ($donation->campaign)
                                        <a href="{{ route('campaigns.show', $donation->campaign) }}" class="line-clamp-1 font-medium text-ink-800 transition hover:text-brand-700">
                                            {{ $donation->campaign->title }}
                                        </a>
                                    @else
                                        <span class="text-ink-400">General fund</span>
                                    @endif
                                </td>
                                <td class="font-bold text-ink-900">{{ money($donation->amount) }}</td>
                                <td class="whitespace-nowrap text-ink-500">{{ $donation->donated_on->format('j M Y') }}</td>
                                <td><x-ui.status-badge :status="$donation->status" :label="$donation->status_label" /></td>
                                <td class="text-right">
                                    <a href="{{ route('donations.show', $donation) }}" class="btn btn-ghost btn-sm">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-6">
                <x-ui.empty-state
                    icon="receipt"
                    title="You have not recorded a donation yet"
                    description="Transfer your contribution, then record it here with the transaction reference so we can verify it and issue your receipt."
                >
                    <a href="{{ route('donations.create') }}" class="btn btn-primary btn-sm">
                        <x-ui.icon name="heart" class="size-4" /> Record your first donation
                    </a>
                    <a href="{{ route('campaigns.index') }}" class="btn btn-outline btn-sm">Browse campaigns</a>
                </x-ui.empty-state>
            </div>
        @endif
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        {{-- Campaigns to support --}}
        <div class="panel">
            <div class="panel-head">
                <h2 class="panel-title">Campaigns that need support</h2>
                <a href="{{ route('campaigns.index') }}" class="link-arrow !text-xs">
                    All <x-ui.icon name="arrow-right" class="size-3.5" />
                </a>
            </div>

            <div class="divide-y divide-ink-100">
                @forelse ($campaigns as $campaign)
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="line-clamp-1 text-sm font-bold text-ink-900">
                                    <a href="{{ route('campaigns.show', $campaign) }}" class="transition hover:text-brand-700">{{ $campaign->title }}</a>
                                </h3>
                                <p class="mt-0.5 text-xs text-ink-500">{{ $campaign->category_label }}</p>
                            </div>

                            @if ($campaign->is_emergency)
                                <x-ui.badge tone="danger">Urgent</x-ui.badge>
                            @endif
                        </div>

                        <div class="mt-3">
                            <x-ui.progress :raised="$campaign->raised_amount" :target="$campaign->target_amount" size="sm" />
                        </div>

                        <a href="{{ route('donations.create', ['campaign' => $campaign->slug]) }}" class="btn btn-outline btn-sm mt-3">
                            <x-ui.icon name="heart" class="size-3.5" /> Support this
                        </a>
                    </div>
                @empty
                    <p class="p-6 text-center text-sm text-ink-500">No open campaigns right now.</p>
                @endforelse
            </div>
        </div>

        {{-- Events + announcements --}}
        <div class="space-y-6">
            <div class="panel">
                <div class="panel-head">
                    <h2 class="panel-title">Upcoming events</h2>
                    <a href="{{ route('events.index') }}" class="link-arrow !text-xs">
                        All <x-ui.icon name="arrow-right" class="size-3.5" />
                    </a>
                </div>

                <div class="divide-y divide-ink-100">
                    @forelse ($events as $event)
                        <a href="{{ route('events.show', $event) }}" class="flex items-center gap-3 p-4 transition hover:bg-brand-50">
                            <div class="flex size-12 shrink-0 flex-col items-center justify-center rounded-lg bg-brand-600 text-white">
                                <span class="text-[9px] font-bold uppercase">{{ $event->event_date->format('M') }}</span>
                                <span class="text-base font-bold leading-none">{{ $event->event_date->format('j') }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="line-clamp-1 text-sm font-bold text-ink-900">{{ $event->title }}</p>
                                <p class="mt-0.5 line-clamp-1 text-xs text-ink-500">{{ $event->location ?? $event->time_range }}</p>
                            </div>
                            <x-ui.icon name="chevron-right" class="size-4 shrink-0 text-ink-300" />
                        </a>
                    @empty
                        <p class="p-6 text-center text-sm text-ink-500">Nothing scheduled right now.</p>
                    @endforelse
                </div>
            </div>

            @if ($announcements->isNotEmpty())
                <div class="panel">
                    <div class="panel-head">
                        <h2 class="panel-title">Announcements</h2>
                    </div>
                    <div class="divide-y divide-ink-100">
                        @foreach ($announcements as $announcement)
                            <div class="p-4">
                                <div class="flex items-center gap-2">
                                    <x-ui.icon name="megaphone" class="size-4 shrink-0 {{ $announcement->is_urgent ? 'text-accent-600' : 'text-ink-400' }}" />
                                    <p class="min-w-0 flex-1 text-sm font-bold text-ink-900">{{ $announcement->title }}</p>
                                    @if ($announcement->is_urgent)
                                        <x-ui.badge tone="warning">{{ $announcement->priority_label }}</x-ui.badge>
                                    @endif
                                </div>
                                <p class="mt-1.5 text-xs leading-relaxed text-ink-600">{{ $announcement->content }}</p>
                                @if ($announcement->link_url)
                                    <a href="{{ $announcement->link_url }}" class="link-arrow mt-2 !text-xs">
                                        {{ $announcement->link_label }} <x-ui.icon name="arrow-right" class="size-3.5" />
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection
