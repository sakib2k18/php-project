@extends('layouts.admin')

@section('title', 'Donations')
@section('heading', 'Donations')
@section('subheading', 'Verify donation records and keep campaign totals accurate')

@section('content')

    <x-admin.page-header
        title="Donation records"
        description="A record only counts towards a campaign total once you approve it. Approving and rejecting both run inside a database transaction that also adjusts the campaign."
        :count="$donations->total()"
    >
        <a href="{{ route('admin.donations.reports') }}" class="btn btn-outline">
            <x-ui.icon name="chart" class="size-4" /> Reports
        </a>

        <form method="POST" action="{{ route('admin.donations.recalculate') }}"
              data-confirm="This recalculates every campaign's raised amount from the approved donations on record. Use it if the totals ever look wrong."
              data-confirm-title="Recalculate campaign totals?" data-confirm-action="Recalculate" data-confirm-tone="primary">
            @csrf
            <button type="submit" class="btn btn-outline">
                <x-ui.icon name="refresh" class="size-4" /> Recalculate totals
            </button>
        </form>
    </x-admin.page-header>

    {{-- Summary --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Verified" icon="check-badge" tone="success"
                        :value="money($summary['amount_approved'])" :hint="$summary['donations_approved'].' records'" />
        <x-ui.stat-card label="Pending" icon="clock" tone="warning"
                        :value="money($summary['amount_pending'])" :hint="$summary['donations_pending'].' records'" />
        <x-ui.stat-card label="Rejected" icon="x-circle" tone="danger"
                        :value="money($summary['amount_rejected'])" :hint="$summary['donations_rejected'].' records'" />
        <x-ui.stat-card label="This month" icon="banknotes" tone="brand"
                        :value="money($summary['amount_this_month'])" hint="verified" />
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.donations.index') }}" data-filter-form class="panel my-6 p-4">
        <div class="grid gap-3 lg:grid-cols-3 xl:grid-cols-6">
            <div class="relative xl:col-span-2">
                <label for="q" class="sr-only">Search donations</label>
                <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-ink-400">
                    <x-ui.icon name="search" class="size-4.5" />
                </span>
                <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                       placeholder="Reference, donor name, email or transaction ID…" class="field pl-10" data-filter-search>
            </div>

            <div>
                <label for="status" class="sr-only">Status</label>
                <select id="status" name="status" class="field">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="method" class="sr-only">Method</label>
                <select id="method" name="method" class="field">
                    <option value="">All methods</option>
                    @foreach ($methods as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['method'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="campaign_id" class="sr-only">Campaign</label>
                <select id="campaign_id" name="campaign_id" class="field">
                    <option value="">All campaigns</option>
                    @foreach ($campaigns as $id => $title)
                        <option value="{{ $id }}" @selected((string) ($filters['campaign_id'] ?? '') === (string) $id)>{{ Str::limit($title, 40) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1">Filter</button>
                @if (array_filter($filters))
                    <button type="button" data-filter-reset="{{ route('admin.donations.index') }}" class="btn btn-ghost" aria-label="Clear filters">
                        <x-ui.icon name="x-mark" class="size-4" />
                    </button>
                @endif
            </div>
        </div>

        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:max-w-md">
            <div>
                <label for="from" class="label !mb-1 !text-xs">From date</label>
                <input type="date" id="from" name="from" value="{{ $filters['from'] ?? '' }}" class="field">
            </div>
            <div>
                <label for="to" class="label !mb-1 !text-xs">To date</label>
                <input type="date" id="to" name="to" value="{{ $filters['to'] ?? '' }}" class="field">
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="panel overflow-hidden">
        @if ($donations->isNotEmpty())
            <div class="table-wrap hidden lg:block">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Reference</th>
                            <th scope="col">Donor</th>
                            <th scope="col">Campaign</th>
                            <th scope="col" class="text-right">Amount</th>
                            <th scope="col">Method</th>
                            <th scope="col">Date</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($donations as $donation)
                            <tr>
                                <td class="whitespace-nowrap font-mono text-xs font-semibold text-ink-900">{{ $donation->reference }}</td>
                                <td class="max-w-[12rem]">
                                    <p class="line-clamp-1 font-medium text-ink-900">{{ $donation->donor_name }}</p>
                                    <p class="line-clamp-1 text-[11px] text-ink-500">{{ $donation->donor_email }}</p>
                                </td>
                                <td class="max-w-[14rem]">
                                    <span class="line-clamp-1 text-ink-600">{{ $donation->campaign?->title ?? 'General fund' }}</span>
                                </td>
                                <td class="whitespace-nowrap text-right font-bold text-ink-900">{{ money($donation->amount) }}</td>
                                <td class="whitespace-nowrap text-ink-600">{{ $donation->method_label }}</td>
                                <td class="whitespace-nowrap text-ink-500">{{ $donation->donated_on->format('j M Y') }}</td>
                                <td><x-ui.status-badge :status="$donation->status" :label="$donation->status_label" /></td>
                                <td>
                                    <div class="flex justify-end gap-1">
                                        @if ($donation->is_pending)
                                            <form method="POST" action="{{ route('admin.donations.review', $donation) }}">
                                                @csrf
                                                <input type="hidden" name="decision" value="approve">
                                                <button type="submit" class="btn btn-primary btn-sm" title="Approve this donation">
                                                    <x-ui.icon name="check" class="size-3.5" /> Approve
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.donations.show', $donation) }}" class="btn btn-outline btn-sm">Review</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile cards --}}
            <ul class="divide-y divide-ink-100 lg:hidden">
                @foreach ($donations as $donation)
                    <li class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-mono text-[11px] font-semibold text-ink-500">{{ $donation->reference }}</p>
                                <p class="mt-1 truncate text-sm font-bold text-ink-900">{{ $donation->donor_name }}</p>
                                <p class="mt-0.5 line-clamp-1 text-xs text-ink-500">{{ $donation->campaign?->title ?? 'General fund' }}</p>
                            </div>
                            <x-ui.status-badge :status="$donation->status" :label="$donation->status_label" />
                        </div>

                        <dl class="mt-3 grid grid-cols-3 gap-2 border-t border-ink-100 pt-3 text-xs">
                            <div>
                                <dt class="text-ink-400">Amount</dt>
                                <dd class="mt-0.5 font-bold text-ink-900">{{ money($donation->amount) }}</dd>
                            </div>
                            <div>
                                <dt class="text-ink-400">Method</dt>
                                <dd class="mt-0.5 font-medium text-ink-700">{{ $donation->method_label }}</dd>
                            </div>
                            <div>
                                <dt class="text-ink-400">Date</dt>
                                <dd class="mt-0.5 font-medium text-ink-700">{{ $donation->donated_on->format('j M y') }}</dd>
                            </div>
                        </dl>

                        <a href="{{ route('admin.donations.show', $donation) }}" class="btn btn-primary btn-sm btn-block mt-3">Review</a>
                    </li>
                @endforeach
            </ul>

            <div class="px-4 pb-4">{{ $donations->links() }}</div>
        @else
            <div class="p-6">
                <x-ui.empty-state
                    icon="banknotes"
                    :title="array_filter($filters) ? 'No donations match your filters' : 'No donations recorded yet'"
                    :description="array_filter($filters) ? 'Try a wider date range or a different status.' : 'Donation records appear here as soon as supporters submit them.'"
                >
                    @if (array_filter($filters))
                        <a href="{{ route('admin.donations.index') }}" class="btn btn-outline btn-sm">Clear filters</a>
                    @endif
                </x-ui.empty-state>
            </div>
        @endif
    </div>

@endsection
