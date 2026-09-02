@extends('layouts.admin')

@section('title', 'Donation reports')
@section('heading', 'Donation reports')
@section('subheading', 'Totals, trends and per-campaign breakdowns straight from the database')

@section('content')

    <x-admin.page-header
        title="Donation reports"
        description="Every figure is a live MySQL aggregate. Use the date range to report on a specific period."
    >
        <button type="button" data-print class="btn btn-outline">
            <x-ui.icon name="printer" class="size-4" /> Print report
        </button>
        <a href="{{ route('admin.donations.index') }}" class="btn btn-outline">
            <x-ui.icon name="banknotes" class="size-4" /> Donation list
        </a>
    </x-admin.page-header>

    {{-- Range filter --}}
    <form method="GET" action="{{ route('admin.donations.reports') }}" class="panel no-print mb-6 p-4">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="from" class="label !mb-1 !text-xs">From date</label>
                <input type="date" id="from" name="from" value="{{ $filters['from'] }}" class="field">
            </div>
            <div>
                <label for="to" class="label !mb-1 !text-xs">To date</label>
                <input type="date" id="to" name="to" value="{{ $filters['to'] }}" class="field">
            </div>
            <div>
                <label for="months" class="label !mb-1 !text-xs">Trend length</label>
                <select id="months" name="months" class="field">
                    @foreach ([6, 12, 18, 24] as $option)
                        <option value="{{ $option }}" @selected($filters['months'] === $option)>Last {{ $option }} months</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn btn-primary flex-1">Apply</button>
                @if ($filters['from'] || $filters['to'])
                    <a href="{{ route('admin.donations.reports') }}" class="btn btn-ghost">Reset</a>
                @endif
            </div>
        </div>

        @if ($filters['from'] || $filters['to'])
            <p class="help mt-3">
                Showing donations
                @if ($filters['from']) from <strong>{{ \Illuminate\Support\Carbon::parse($filters['from'])->format('j M Y') }}</strong> @endif
                @if ($filters['to']) to <strong>{{ \Illuminate\Support\Carbon::parse($filters['to'])->format('j M Y') }}</strong> @endif
            </p>
        @endif
    </form>

    {{-- Summary --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Total records" icon="receipt" tone="neutral" :value="number_format($summary['count'])" />
        <x-ui.stat-card label="Approved amount" icon="check-badge" tone="success"
                        :value="money($summary['approved_amount'])" :hint="$summary['approved_count'].' records'" />
        <x-ui.stat-card label="Pending amount" icon="clock" tone="warning"
                        :value="money($summary['pending_amount'])" :hint="$summary['pending_count'].' records'" />
        <x-ui.stat-card label="Rejected amount" icon="x-circle" tone="danger"
                        :value="money($summary['rejected_amount'])" :hint="$summary['rejected_count'].' records'" />
    </div>

    {{-- Charts --}}
    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <div class="panel xl:col-span-2" data-chart-wrap>
            <div class="panel-head">
                <div>
                    <h3 class="panel-title">Verified donations by month</h3>
                    <p class="mt-0.5 text-xs text-ink-500">Amount and record count over the last {{ $filters['months'] }} months</p>
                </div>
            </div>
            <div class="p-5">
                <div class="h-72">
                    <canvas
                        data-chart="bar"
                        data-chart-label="Amount"
                        data-chart-money="true"
                        data-chart-currency="{{ config('site.currency.symbol') }}"
                        data-chart-labels='@json($trend["labels"])'
                        data-chart-values='@json($trend["amounts"])'
                        aria-label="Bar chart of verified donation amounts by month"
                        role="img"
                    ></canvas>
                </div>
            </div>
        </div>

        <div class="panel" data-chart-wrap>
            <div class="panel-head">
                <h3 class="panel-title">By status</h3>
            </div>
            <div class="p-5">
                <div class="h-72">
                    <canvas
                        data-chart="doughnut"
                        data-chart-palette="status"
                        data-chart-labels='@json($statusBreakdown["labels"])'
                        data-chart-values='@json($statusBreakdown["values"])'
                        aria-label="Doughnut chart of donation records by status"
                        role="img"
                    ></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Category + method --}}
    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <div class="panel" data-chart-wrap>
            <div class="panel-head">
                <h3 class="panel-title">Verified amount by campaign category</h3>
            </div>
            <div class="p-5">
                <div class="h-64">
                    @if (count($categories['values']))
                        <canvas
                            data-chart="bar"
                            data-chart-label="Amount"
                            data-chart-money="true"
                            data-chart-currency="{{ config('site.currency.symbol') }}"
                            data-chart-labels='@json($categories["labels"])'
                            data-chart-values='@json($categories["values"])'
                            aria-label="Bar chart of verified donation amounts by category"
                            role="img"
                        ></canvas>
                    @else
                        <p class="grid h-full place-items-center text-sm text-ink-400">No verified donations in this range.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h3 class="panel-title">Verified donations by method</h3>
            </div>

            @if ($methods->isNotEmpty())
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Method</th>
                                <th scope="col" class="text-right">Records</th>
                                <th scope="col" class="text-right">Amount</th>
                                <th scope="col" class="text-right">Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $methodTotal = max(1, $methods->sum('total'));
                            @endphp

                            @foreach ($methods as $row)
                                <tr>
                                    <td class="font-medium text-ink-900">{{ config("site.donation_methods.{$row->method}", $row->method) }}</td>
                                    <td class="text-right tabular-nums text-ink-600">{{ number_format($row->c) }}</td>
                                    <td class="text-right font-bold tabular-nums text-ink-900">{{ money($row->total) }}</td>
                                    <td class="text-right tabular-nums text-ink-600">{{ number_format(($row->total / $methodTotal) * 100, 1) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="p-6 text-center text-sm text-ink-500">No verified donations in this range.</p>
            @endif
        </div>
    </div>

    {{-- Per campaign --}}
    <div class="panel mt-6">
        <div class="panel-head">
            <h3 class="panel-title">Donations by campaign</h3>
            <x-ui.badge tone="neutral">{{ $perCampaign->count() }} campaigns</x-ui.badge>
        </div>

        @if ($perCampaign->isNotEmpty())
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Campaign</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-right">Records</th>
                            <th scope="col" class="text-right">Verified total</th>
                            <th scope="col" class="text-right">Target</th>
                            <th scope="col" class="text-right">Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($perCampaign as $row)
                            @php
                                $percent = $row->target_amount > 0
                                    ? min(100, round(($row->approved_total / $row->target_amount) * 100, 1))
                                    : 0;
                            @endphp
                            <tr>
                                <td class="max-w-[20rem]">
                                    <a href="{{ route('admin.campaigns.show', $row->id) }}" class="line-clamp-1 font-semibold text-ink-900 transition hover:text-brand-700">
                                        {{ $row->title }}
                                    </a>
                                </td>
                                <td><x-ui.status-badge :status="$row->status" /></td>
                                <td class="text-right tabular-nums text-ink-600">{{ number_format($row->donations_count) }}</td>
                                <td class="text-right font-bold tabular-nums text-ink-900">{{ money($row->approved_total) }}</td>
                                <td class="text-right tabular-nums text-ink-500">{{ money($row->target_amount) }}</td>
                                <td class="text-right">
                                    <span class="font-bold tabular-nums {{ $percent >= 100 ? 'text-brand-700' : 'text-ink-700' }}">
                                        {{ rtrim(rtrim(number_format($percent, 1), '0'), '.') }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-ink-200 bg-ink-50 font-bold">
                            <td colspan="3" class="p-4 text-sm text-ink-900">Total</td>
                            <td class="p-4 text-right text-sm text-brand-700">{{ money($perCampaign->sum('approved_total')) }}</td>
                            <td class="p-4 text-right text-sm text-ink-600">{{ money($perCampaign->sum('target_amount')) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <p class="p-6 text-center text-sm text-ink-500">No campaigns to report on yet.</p>
        @endif
    </div>

    {{-- Recent --}}
    <div class="panel mt-6">
        <div class="panel-head">
            <h3 class="panel-title">Most recent donations in this range</h3>
            <a href="{{ route('admin.donations.index') }}" class="link-arrow !text-xs no-print">
                Full list <x-ui.icon name="arrow-right" class="size-3.5" />
            </a>
        </div>

        @if ($recent->isNotEmpty())
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Reference</th>
                            <th scope="col">Donor</th>
                            <th scope="col">Campaign</th>
                            <th scope="col" class="text-right">Amount</th>
                            <th scope="col">Date</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recent as $donation)
                            <tr>
                                <td class="whitespace-nowrap font-mono text-xs font-semibold text-ink-900">{{ $donation->reference }}</td>
                                <td class="max-w-[12rem]"><span class="line-clamp-1">{{ $donation->public_donor_name }}</span></td>
                                <td class="max-w-[14rem]"><span class="line-clamp-1 text-ink-600">{{ $donation->campaign?->title ?? 'General fund' }}</span></td>
                                <td class="whitespace-nowrap text-right font-bold text-ink-900">{{ money($donation->amount) }}</td>
                                <td class="whitespace-nowrap text-ink-500">{{ $donation->donated_on->format('j M Y') }}</td>
                                <td><x-ui.status-badge :status="$donation->status" :label="$donation->status_label" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="p-6 text-center text-sm text-ink-500">No donations in this range.</p>
        @endif
    </div>

    <p class="mt-6 text-center text-xs text-ink-400">
        Report generated {{ now()->format('j F Y, g:i A') }} · {{ $site->name() }}
    </p>

@endsection
