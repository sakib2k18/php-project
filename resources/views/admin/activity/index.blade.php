@extends('layouts.admin')

@section('title', 'Activity log')
@section('heading', 'Activity log')
@section('subheading', 'An audit trail of every administrative action')

@section('content')

    <x-admin.page-header
        title="Activity log"
        description="Written automatically by App\Services\ActivityLogger whenever content is created, edited or deleted, or a donation is reviewed."
        :count="$logs->total()"
    />

    <form method="GET" action="{{ route('admin.activity.index') }}" data-filter-form class="panel mb-6 p-4">
        <div class="grid gap-3 sm:grid-cols-[2fr_1fr_auto]">
            <div class="relative">
                <label for="q" class="sr-only">Search activity</label>
                <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-ink-400">
                    <x-ui.icon name="search" class="size-4.5" />
                </span>
                <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                       placeholder="Search descriptions…" class="field pl-10" data-filter-search>
            </div>

            <div>
                <label for="action" class="sr-only">Action type</label>
                <select id="action" name="action" class="field">
                    <option value="">All actions</option>
                    @foreach ($actions as $action)
                        <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ $action }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1 sm:flex-none">Filter</button>
                @if (array_filter($filters))
                    <button type="button" data-filter-reset="{{ route('admin.activity.index') }}" class="btn btn-ghost" aria-label="Clear filters">
                        <x-ui.icon name="x-mark" class="size-4" />
                    </button>
                @endif
            </div>
        </div>
    </form>

    <div class="panel overflow-hidden">
        @if ($logs->isNotEmpty())
            <div class="table-wrap hidden sm:block">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">When</th>
                            <th scope="col">Action</th>
                            <th scope="col">Description</th>
                            <th scope="col">By</th>
                            <th scope="col">IP address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            @php
                                $dots = ['brand' => 'bg-brand-500', 'accent' => 'bg-accent-500', 'rose' => 'bg-rose-500', 'ink' => 'bg-ink-300'];
                            @endphp

                            <tr>
                                <td class="whitespace-nowrap">
                                    <p class="text-xs font-medium text-ink-800">{{ $log->created_at->format('j M Y') }}</p>
                                    <p class="text-[11px] text-ink-400">{{ $log->created_at->format('g:i A') }}</p>
                                </td>
                                <td class="whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="size-2 rounded-full {{ $dots[$log->tone] ?? $dots['ink'] }}"></span>
                                        <code class="text-[11px] font-semibold text-ink-600">{{ $log->action }}</code>
                                    </span>
                                </td>
                                <td class="max-w-md"><span class="line-clamp-2 text-ink-700">{{ $log->description }}</span></td>
                                <td class="whitespace-nowrap text-ink-600">{{ $log->user?->name ?? 'System' }}</td>
                                <td class="whitespace-nowrap font-mono text-[11px] text-ink-400">{{ $log->ip_address ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <ol class="divide-y divide-ink-100 sm:hidden">
                @foreach ($logs as $log)
                    <li class="flex gap-3 p-4">
                        @php
                            $dots = ['brand' => 'bg-brand-500', 'accent' => 'bg-accent-500', 'rose' => 'bg-rose-500', 'ink' => 'bg-ink-300'];
                        @endphp
                        <span class="mt-1.5 size-2.5 shrink-0 rounded-full {{ $dots[$log->tone] ?? $dots['ink'] }}"></span>

                        <div class="min-w-0 flex-1">
                            <p class="text-sm leading-relaxed text-ink-700">{{ $log->description }}</p>
                            <p class="mt-1 text-[11px] text-ink-400">
                                {{ $log->user?->name ?? 'System' }}
                                <span class="divider-dot">{{ $log->created_at->format('j M Y, g:i A') }}</span>
                            </p>
                        </div>
                    </li>
                @endforeach
            </ol>

            <div class="px-4 pb-4">{{ $logs->links() }}</div>
        @else
            <div class="p-6">
                <x-ui.empty-state icon="clipboard" title="No activity recorded yet"
                                  description="Administrative actions are logged here automatically as you work." />
            </div>
        @endif
    </div>

@endsection
