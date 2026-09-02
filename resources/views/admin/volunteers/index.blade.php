@extends('layouts.admin')

@section('title', 'Volunteers')
@section('heading', 'Volunteers')
@section('subheading', 'Review and approve volunteer applications')

@section('content')

    <x-admin.page-header
        title="Volunteer applications"
        description="Approving an application notifies the applicant in their dashboard."
        :count="$volunteers->total()"
    />

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-ui.stat-card label="Awaiting review" icon="clock" tone="warning" :value="number_format($counts['pending'])"
                        :href="route('admin.volunteers.index', ['status' => 'pending'])" />
        <x-ui.stat-card label="Approved" icon="check-badge" tone="success" :value="number_format($counts['approved'])"
                        :href="route('admin.volunteers.index', ['status' => 'approved'])" />
        <x-ui.stat-card label="Rejected" icon="x-circle" tone="neutral" :value="number_format($counts['rejected'])"
                        :href="route('admin.volunteers.index', ['status' => 'rejected'])" />
    </div>

    <form method="GET" action="{{ route('admin.volunteers.index') }}" data-filter-form class="panel mb-6 p-4">
        <div class="grid gap-3 lg:grid-cols-[2fr_1fr_1fr_auto]">
            <div class="relative">
                <label for="q" class="sr-only">Search volunteers</label>
                <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-ink-400">
                    <x-ui.icon name="search" class="size-4.5" />
                </span>
                <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                       placeholder="Search name, email, phone or skills…" class="field pl-10" data-filter-search>
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
                <label for="availability" class="sr-only">Availability</label>
                <select id="availability" name="availability" class="field">
                    <option value="">Any availability</option>
                    @foreach ($availability as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['availability'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1 lg:flex-none">Filter</button>
                @if (array_filter($filters))
                    <button type="button" data-filter-reset="{{ route('admin.volunteers.index') }}" class="btn btn-ghost" aria-label="Clear filters">
                        <x-ui.icon name="x-mark" class="size-4" />
                    </button>
                @endif
            </div>
        </div>
    </form>

    <div class="panel overflow-hidden">
        @if ($volunteers->isNotEmpty())
            <div class="table-wrap hidden lg:block">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Applicant</th>
                            <th scope="col">Preferred activity</th>
                            <th scope="col">Availability</th>
                            <th scope="col">Applied</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($volunteers as $volunteer)
                            <tr>
                                <td class="max-w-[16rem]">
                                    <div class="flex items-center gap-3">
                                        <x-ui.avatar :initials="Str::upper(Str::substr($volunteer->name, 0, 1))" size="sm" />
                                        <div class="min-w-0">
                                            <p class="line-clamp-1 font-semibold text-ink-900">{{ $volunteer->name }}</p>
                                            <p class="truncate text-[11px] text-ink-500">{{ $volunteer->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap text-ink-600">{{ $volunteer->preferred_activity }}</td>
                                <td class="whitespace-nowrap text-ink-600">{{ $volunteer->availability_label }}</td>
                                <td class="whitespace-nowrap text-xs text-ink-500">{{ $volunteer->created_at->format('j M Y') }}</td>
                                <td><x-ui.status-badge :status="$volunteer->status" :label="$volunteer->status_label" /></td>
                                <td>
                                    <div class="flex justify-end gap-1">
                                        @if ($volunteer->status === 'pending')
                                            <form method="POST" action="{{ route('admin.volunteers.review', $volunteer) }}">
                                                @csrf
                                                <input type="hidden" name="decision" value="approve">
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <x-ui.icon name="check" class="size-3.5" /> Approve
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.volunteers.show', $volunteer) }}" class="btn btn-outline btn-sm">View</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <ul class="divide-y divide-ink-100 lg:hidden">
                @foreach ($volunteers as $volunteer)
                    <li class="p-4">
                        <div class="flex items-start gap-3">
                            <x-ui.avatar :initials="Str::upper(Str::substr($volunteer->name, 0, 1))" size="sm" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-ink-900">{{ $volunteer->name }}</p>
                                <p class="truncate text-xs text-ink-500">{{ $volunteer->preferred_activity }}</p>
                            </div>
                            <x-ui.status-badge :status="$volunteer->status" :label="$volunteer->status_label" />
                        </div>

                        <a href="{{ route('admin.volunteers.show', $volunteer) }}" class="btn btn-primary btn-sm btn-block mt-3">Review application</a>
                    </li>
                @endforeach
            </ul>

            <div class="px-4 pb-4">{{ $volunteers->links() }}</div>
        @else
            <div class="p-6">
                <x-ui.empty-state
                    icon="hand-heart"
                    :title="array_filter($filters) ? 'No applications match your filters' : 'No volunteer applications yet'"
                    description="Applications submitted from the Get Involved page appear here for review."
                >
                    @if (array_filter($filters))
                        <a href="{{ route('admin.volunteers.index') }}" class="btn btn-outline btn-sm">Clear filters</a>
                    @endif
                </x-ui.empty-state>
            </div>
        @endif
    </div>

@endsection
