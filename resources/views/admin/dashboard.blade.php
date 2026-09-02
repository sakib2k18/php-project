@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', 'Everything on this page is calculated live from the database')

@section('content')

    {{-- ===== Headline statistics ===== --}}
    <section aria-labelledby="stats-heading">
        <h2 id="stats-heading" class="sr-only">Key statistics</h2>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat-card
                label="Verified donations" icon="banknotes" tone="success"
                :value="money($stats['amount_approved'])"
                :hint="number_format($stats['donations_approved']).' approved records'"
                :href="route('admin.donations.index', ['status' => 'approved'])"
            />
            <x-ui.stat-card
                label="Awaiting verification" icon="clock" tone="warning"
                :value="money($stats['amount_pending'])"
                :hint="number_format($stats['donations_pending']).' pending'"
                :href="route('admin.donations.index', ['status' => 'pending'])"
            />
            <x-ui.stat-card
                label="Active campaigns" icon="heart" tone="brand"
                :value="number_format($stats['campaigns_active'])"
                :hint="number_format($stats['campaigns_completed']).' completed'"
                :href="route('admin.campaigns.index', ['status' => 'active'])"
            />
            <x-ui.stat-card
                label="Registered users" icon="users" tone="info"
                :value="number_format($stats['users_total'])"
                :hint="'+'.number_format($stats['users_new_this_month']).' this month'"
                :href="route('admin.users.index')"
            />
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat-card
                label="This month" icon="chart" tone="brand"
                :value="money($stats['amount_this_month'])"
                hint="verified donations"
            />
            <x-ui.stat-card
                label="Volunteers" icon="hand-heart" tone="accent"
                :value="number_format($stats['volunteers_approved'])"
                :hint="number_format($stats['volunteers_pending']).' awaiting review'"
                :href="route('admin.volunteers.index', ['status' => 'pending'])"
            />
            <x-ui.stat-card
                label="Upcoming events" icon="calendar" tone="info"
                :value="number_format($stats['events_upcoming'])"
                :hint="number_format($stats['events_total']).' total'"
                :href="route('admin.events.index', ['when' => 'upcoming'])"
            />
            <x-ui.stat-card
                label="Unread messages" icon="inbox"
                :tone="$stats['messages_unread'] > 0 ? 'danger' : 'neutral'"
                :value="number_format($stats['messages_unread'])"
                :hint="number_format($stats['messages_total']).' total'"
                :href="route('admin.messages.index', ['state' => 'unread'])"
            />
        </div>
    </section>

    {{-- ===== Charts ===== --}}
    <section class="mt-6 grid gap-6 xl:grid-cols-3" aria-labelledby="charts-heading">
        <h2 id="charts-heading" class="sr-only">Charts</h2>

        <div class="panel xl:col-span-2" data-chart-wrap>
            <div class="panel-head">
                <div>
                    <h3 class="panel-title">Verified donations over time</h3>
                    <p class="mt-0.5 text-xs text-ink-500">Last six months, by donation date</p>
                </div>
                <a href="{{ route('admin.donations.reports') }}" class="link-arrow !text-xs">
                    Full report <x-ui.icon name="arrow-right" class="size-3.5" />
                </a>
            </div>

            <div class="p-5">
                <div class="h-64">
                    <canvas
                        data-chart="line"
                        data-chart-label="Amount"
                        data-chart-money="true"
                        data-chart-currency="{{ config('site.currency.symbol') }}"
                        data-chart-labels='@json($donationTrend["labels"])'
                        data-chart-values='@json($donationTrend["amounts"])'
                        aria-label="Line chart of verified donation amounts over the last six months"
                        role="img"
                    ></canvas>
                </div>
            </div>
        </div>

        <div class="panel" data-chart-wrap>
            <div class="panel-head">
                <h3 class="panel-title">Donations by status</h3>
            </div>
            <div class="p-5">
                <div class="h-64">
                    <canvas
                        data-chart="doughnut"
                        data-chart-palette="status"
                        data-chart-labels='@json($statusBreakdown["labels"])'
                        data-chart-values='@json($statusBreakdown["values"])'
                        aria-label="Doughnut chart of donation counts by status"
                        role="img"
                    ></canvas>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-2">
        <div class="panel" data-chart-wrap>
            <div class="panel-head">
                <div>
                    <h3 class="panel-title">Verified amount by category</h3>
                    <p class="mt-0.5 text-xs text-ink-500">Across all campaigns</p>
                </div>
            </div>
            <div class="p-5">
                <div class="h-60">
                    @if (count($categoryBreakdown['values']))
                        <canvas
                            data-chart="bar"
                            data-chart-label="Amount"
                            data-chart-money="true"
                            data-chart-currency="{{ config('site.currency.symbol') }}"
                            data-chart-labels='@json($categoryBreakdown["labels"])'
                            data-chart-values='@json($categoryBreakdown["values"])'
                            aria-label="Bar chart of verified donation totals by campaign category"
                            role="img"
                        ></canvas>
                    @else
                        <p class="grid h-full place-items-center text-sm text-ink-400">No verified donations yet.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="panel" data-chart-wrap>
            <div class="panel-head">
                <div>
                    <h3 class="panel-title">New supporters</h3>
                    <p class="mt-0.5 text-xs text-ink-500">Registrations per month</p>
                </div>
            </div>
            <div class="p-5">
                <div class="h-60">
                    <canvas
                        data-chart="bar"
                        data-chart-label="Sign-ups"
                        data-chart-labels='@json($userGrowth["labels"])'
                        data-chart-values='@json($userGrowth["values"])'
                        aria-label="Bar chart of new user registrations per month"
                        role="img"
                    ></canvas>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== Work queues ===== --}}
    <section class="mt-6 grid gap-6 xl:grid-cols-2">

        {{-- Pending donations --}}
        <div class="panel">
            <div class="panel-head">
                <div class="flex items-center gap-2.5">
                    <h3 class="panel-title">Donations awaiting verification</h3>
                    @if ($stats['donations_pending'])
                        <x-ui.badge tone="warning">{{ $stats['donations_pending'] }}</x-ui.badge>
                    @endif
                </div>
                <a href="{{ route('admin.donations.index', ['status' => 'pending']) }}" class="link-arrow !text-xs">
                    View all <x-ui.icon name="arrow-right" class="size-3.5" />
                </a>
            </div>

            @if ($pendingDonations->isNotEmpty())
                <ul class="divide-y divide-ink-100">
                    @foreach ($pendingDonations as $donation)
                        <li class="flex items-center gap-3 p-4">
                            <x-ui.avatar :initials="Str::upper(Str::substr($donation->donor_name, 0, 1))" size="sm" />

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-ink-900">{{ $donation->donor_name }}</p>
                                <p class="truncate text-xs text-ink-500">
                                    {{ $donation->campaign?->title ?? 'General fund' }}
                                    <span class="divider-dot">{{ $donation->method_label }}</span>
                                </p>
                            </div>

                            <div class="shrink-0 text-right">
                                <p class="text-sm font-bold text-ink-900">{{ money($donation->amount) }}</p>
                                <p class="text-[11px] text-ink-400">{{ $donation->donated_on->format('j M') }}</p>
                            </div>

                            <a href="{{ route('admin.donations.show', $donation) }}" class="btn btn-outline btn-sm shrink-0">Review</a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="p-6">
                    <x-ui.empty-state icon="check-circle" title="Nothing to verify"
                                      description="Every donation record has been reviewed." />
                </div>
            @endif
        </div>

        {{-- Pending volunteers --}}
        <div class="panel">
            <div class="panel-head">
                <div class="flex items-center gap-2.5">
                    <h3 class="panel-title">Volunteer applications</h3>
                    @if ($stats['volunteers_pending'])
                        <x-ui.badge tone="warning">{{ $stats['volunteers_pending'] }}</x-ui.badge>
                    @endif
                </div>
                <a href="{{ route('admin.volunteers.index', ['status' => 'pending']) }}" class="link-arrow !text-xs">
                    View all <x-ui.icon name="arrow-right" class="size-3.5" />
                </a>
            </div>

            @if ($pendingVolunteers->isNotEmpty())
                <ul class="divide-y divide-ink-100">
                    @foreach ($pendingVolunteers as $volunteer)
                        <li class="flex items-center gap-3 p-4">
                            <x-ui.avatar :initials="Str::upper(Str::substr($volunteer->name, 0, 1))" size="sm" />

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-ink-900">{{ $volunteer->name }}</p>
                                <p class="truncate text-xs text-ink-500">
                                    {{ $volunteer->preferred_activity }}
                                    <span class="divider-dot">{{ $volunteer->availability_label }}</span>
                                </p>
                            </div>

                            <a href="{{ route('admin.volunteers.show', $volunteer) }}" class="btn btn-outline btn-sm shrink-0">Review</a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="p-6">
                    <x-ui.empty-state icon="check-circle" title="No applications waiting"
                                      description="All volunteer applications have been reviewed." />
                </div>
            @endif
        </div>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-3">

        {{-- Top campaigns --}}
        <div class="panel xl:col-span-2">
            <div class="panel-head">
                <h3 class="panel-title">Top campaigns by verified total</h3>
                <a href="{{ route('admin.campaigns.index') }}" class="link-arrow !text-xs">
                    Manage <x-ui.icon name="arrow-right" class="size-3.5" />
                </a>
            </div>

            @if ($topCampaigns->isNotEmpty())
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Campaign</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-right">Donors</th>
                                <th scope="col" class="text-right">Verified</th>
                                <th scope="col" class="w-32">Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topCampaigns as $campaign)
                                <tr>
                                    <td class="max-w-[16rem]">
                                        <a href="{{ route('admin.campaigns.show', $campaign) }}" class="line-clamp-1 font-semibold text-ink-900 transition hover:text-brand-700">
                                            {{ $campaign->title }}
                                        </a>
                                        <p class="mt-0.5 text-xs text-ink-500">{{ $campaign->category_label }}</p>
                                    </td>
                                    <td><x-ui.status-badge :status="$campaign->status" :label="$campaign->status_label" /></td>
                                    <td class="text-right tabular-nums text-ink-600">{{ number_format($campaign->approved_count) }}</td>
                                    <td class="text-right font-bold tabular-nums text-ink-900">{{ money($campaign->approved_total) }}</td>
                                    <td>
                                        <x-ui.progress
                                            :raised="$campaign->raised_amount"
                                            :target="$campaign->target_amount"
                                            :show-labels="false"
                                            size="sm"
                                        />
                                        <p class="mt-1 text-[11px] font-semibold text-ink-500">{{ rtrim(rtrim(number_format($campaign->progress_percent, 1), '0'), '.') }}%</p>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-6">
                    <x-ui.empty-state icon="heart" title="No campaigns yet"
                                      description="Create your first campaign to start tracking donations.">
                        <a href="{{ route('admin.campaigns.create') }}" class="btn btn-primary btn-sm">
                            <x-ui.icon name="plus" class="size-4" /> New campaign
                        </a>
                    </x-ui.empty-state>
                </div>
            @endif
        </div>

        {{-- Activity log --}}
        <div class="panel">
            <div class="panel-head">
                <h3 class="panel-title">Recent activity</h3>
                <a href="{{ route('admin.activity.index') }}" class="link-arrow !text-xs">
                    All <x-ui.icon name="arrow-right" class="size-3.5" />
                </a>
            </div>

            @if ($activities->isNotEmpty())
                <ol class="p-5">
                    @foreach ($activities as $activity)
                        @php
                            $dots = ['brand' => 'bg-brand-500', 'accent' => 'bg-accent-500', 'rose' => 'bg-rose-500', 'ink' => 'bg-ink-300'];
                        @endphp

                        <li class="relative flex gap-3 pb-4 last:pb-0">
                            @unless ($loop->last)
                                <span class="absolute left-[5px] top-3 h-full w-px bg-ink-100" aria-hidden="true"></span>
                            @endunless

                            <span class="relative mt-1.5 size-2.5 shrink-0 rounded-full {{ $dots[$activity->tone] ?? $dots['ink'] }}"></span>

                            <div class="min-w-0 flex-1">
                                <p class="text-xs leading-relaxed text-ink-700">{{ $activity->description }}</p>
                                <p class="mt-0.5 text-[11px] text-ink-400">
                                    {{ $activity->user?->name ?? 'System' }}
                                    <span class="divider-dot">{{ $activity->created_at->diffForHumans() }}</span>
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @else
                <p class="p-6 text-center text-sm text-ink-500">No activity recorded yet.</p>
            @endif
        </div>
    </section>

    {{-- ===== Messages + events ===== --}}
    <section class="mt-6 grid gap-6 xl:grid-cols-2">
        <div class="panel">
            <div class="panel-head">
                <div class="flex items-center gap-2.5">
                    <h3 class="panel-title">Unread messages</h3>
                    @if ($stats['messages_unread'])
                        <x-ui.badge tone="danger">{{ $stats['messages_unread'] }}</x-ui.badge>
                    @endif
                </div>
                <a href="{{ route('admin.messages.index') }}" class="link-arrow !text-xs">
                    Inbox <x-ui.icon name="arrow-right" class="size-3.5" />
                </a>
            </div>

            @if ($unreadMessages->isNotEmpty())
                <ul class="divide-y divide-ink-100">
                    @foreach ($unreadMessages as $message)
                        <li>
                            <a href="{{ route('admin.messages.show', $message) }}" class="flex items-start gap-3 p-4 transition hover:bg-brand-50">
                                <x-ui.avatar :initials="Str::upper(Str::substr($message->name, 0, 1))" size="sm" />
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-bold text-ink-900">{{ $message->subject }}</p>
                                    <p class="truncate text-xs text-ink-500">{{ $message->name }} · {{ $message->email }}</p>
                                    <p class="mt-1 line-clamp-1 text-xs text-ink-400">{{ $message->message }}</p>
                                </div>
                                <span class="shrink-0 text-[11px] text-ink-400">{{ $message->created_at->diffForHumans(short: true) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="p-6">
                    <x-ui.empty-state icon="inbox" title="Inbox clear" description="No unread contact messages." />
                </div>
            @endif
        </div>

        <div class="panel">
            <div class="panel-head">
                <h3 class="panel-title">Upcoming events</h3>
                <a href="{{ route('admin.events.index') }}" class="link-arrow !text-xs">
                    Manage <x-ui.icon name="arrow-right" class="size-3.5" />
                </a>
            </div>

            @if ($upcomingEvents->isNotEmpty())
                <ul class="divide-y divide-ink-100">
                    @foreach ($upcomingEvents as $event)
                        <li class="flex items-center gap-3 p-4">
                            <div class="flex size-11 shrink-0 flex-col items-center justify-center rounded-lg bg-brand-600 text-white">
                                <span class="text-[9px] font-bold uppercase">{{ $event->event_date->format('M') }}</span>
                                <span class="text-sm font-bold leading-none">{{ $event->event_date->format('j') }}</span>
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-ink-900">{{ $event->title }}</p>
                                <p class="truncate text-xs text-ink-500">{{ $event->location ?? 'Location to be confirmed' }}</p>
                            </div>

                            <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-ghost btn-sm shrink-0">Edit</a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="p-6">
                    <x-ui.empty-state icon="calendar" title="No upcoming events"
                                      description="Publish an event so supporters know what is happening next.">
                        <a href="{{ route('admin.events.create') }}" class="btn btn-primary btn-sm">
                            <x-ui.icon name="plus" class="size-4" /> New event
                        </a>
                    </x-ui.empty-state>
                </div>
            @endif
        </div>
    </section>

@endsection
