@extends('layouts.dashboard')

@section('title', 'My donations — '.$site->name())
@section('heading', 'My donations')
@section('subheading', 'Only your own records are shown here — nobody else can see them.')

@section('actions')
    <a href="{{ route('donations.create') }}" class="btn btn-primary">
        <x-ui.icon name="heart" class="size-4" /> Record a donation
    </a>
@endsection

@section('panel')

    {{-- Summary --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <x-ui.stat-card label="Records" icon="receipt" tone="brand" :value="number_format($summary['count'])" />
        <x-ui.stat-card label="Verified total" icon="check-badge" tone="success" :value="money($summary['approved_amount'])" />
        <x-ui.stat-card label="Awaiting verification" icon="clock" tone="warning" :value="money($summary['pending_amount'])" />
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('donations.index') }}" data-filter-form class="panel mt-6 p-4">
        <div class="grid gap-3 sm:grid-cols-[2fr_1fr_auto]">
            <div class="relative">
                <label for="q" class="sr-only">Search your donations</label>
                <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-ink-400">
                    <x-ui.icon name="search" class="size-4.5" />
                </span>
                <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                       placeholder="Search by reference or campaign…" class="field pl-10" data-filter-search>
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

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1 sm:flex-none">Filter</button>
                @if (array_filter($filters))
                    <button type="button" data-filter-reset="{{ route('donations.index') }}" class="btn btn-ghost" aria-label="Clear filters">
                        <x-ui.icon name="x-mark" class="size-4" />
                    </button>
                @endif
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="panel mt-6 overflow-hidden">
        @if ($donations->isNotEmpty())
            {{-- Desktop table --}}
            <div class="table-wrap hidden sm:block">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Reference</th>
                            <th scope="col">Campaign</th>
                            <th scope="col">Amount</th>
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
                                <td class="max-w-[16rem]">
                                    @if ($donation->campaign)
                                        <a href="{{ route('campaigns.show', $donation->campaign) }}" class="line-clamp-1 font-medium text-ink-800 transition hover:text-brand-700">
                                            {{ $donation->campaign->title }}
                                        </a>
                                    @else
                                        <span class="text-ink-400">General fund</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap font-bold text-ink-900">{{ money($donation->amount) }}</td>
                                <td class="whitespace-nowrap text-ink-600">{{ $donation->method_label }}</td>
                                <td class="whitespace-nowrap text-ink-500">{{ $donation->donated_on->format('j M Y') }}</td>
                                <td><x-ui.status-badge :status="$donation->status" :label="$donation->status_label" /></td>
                                <td>
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('donations.show', $donation) }}" class="btn btn-ghost btn-sm">View</a>
                                        @if ($donation->is_approved)
                                            <a href="{{ route('donations.receipt', $donation) }}" class="btn btn-outline btn-sm" target="_blank" rel="noopener">
                                                <x-ui.icon name="receipt" class="size-3.5" /> Receipt
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile cards --}}
            <ul class="divide-y divide-ink-100 sm:hidden">
                @foreach ($donations as $donation)
                    <li class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-mono text-[11px] font-semibold text-ink-500">{{ $donation->reference }}</p>
                                <p class="mt-1 line-clamp-2 text-sm font-bold text-ink-900">
                                    {{ $donation->campaign?->title ?? 'General fund' }}
                                </p>
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
                                <dd class="mt-0.5 font-medium text-ink-700">{{ $donation->donated_on->format('j M Y') }}</dd>
                            </div>
                        </dl>

                        <div class="mt-3 flex gap-2">
                            <a href="{{ route('donations.show', $donation) }}" class="btn btn-outline btn-sm flex-1">View</a>
                            @if ($donation->is_approved)
                                <a href="{{ route('donations.receipt', $donation) }}" class="btn btn-primary btn-sm flex-1" target="_blank" rel="noopener">
                                    <x-ui.icon name="receipt" class="size-3.5" /> Receipt
                                </a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="px-4 pb-4">{{ $donations->links() }}</div>
        @else
            <div class="p-6">
                <x-ui.empty-state
                    icon="receipt"
                    :title="array_filter($filters) ? 'No donations match your filters' : 'No donations yet'"
                    :description="array_filter($filters)
                        ? 'Try a different status or clear the search box.'
                        : 'Once you record a donation it will appear here, along with its verification status and receipt.'"
                >
                    @if (array_filter($filters))
                        <a href="{{ route('donations.index') }}" class="btn btn-outline btn-sm">Clear filters</a>
                    @endif
                    <a href="{{ route('donations.create') }}" class="btn btn-primary btn-sm">
                        <x-ui.icon name="heart" class="size-4" /> Record a donation
                    </a>
                </x-ui.empty-state>
            </div>
        @endif
    </div>

@endsection
