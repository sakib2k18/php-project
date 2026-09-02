@extends('layouts.admin')

@section('title', 'Team members')
@section('heading', 'Team members')
@section('subheading', 'The people shown on the public team page')

@section('content')

    <x-admin.page-header
        title="Team"
        description="Inactive members are kept in the database but hidden from the public team page."
        :count="$members->total()"
    >
        <a href="{{ route('team') }}" target="_blank" rel="noopener" class="btn btn-outline">
            <x-ui.icon name="eye" class="size-4" /> Public page
        </a>
        <a href="{{ route('admin.team.create') }}" class="btn btn-primary">
            <x-ui.icon name="plus" class="size-4" /> Add member
        </a>
    </x-admin.page-header>

    <form method="GET" action="{{ route('admin.team.index') }}" data-filter-form class="panel mb-6 p-4">
        <div class="flex flex-col gap-3 sm:flex-row">
            <div class="relative flex-1">
                <label for="q" class="sr-only">Search team members</label>
                <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-ink-400">
                    <x-ui.icon name="search" class="size-4.5" />
                </span>
                <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                       placeholder="Search by name or position…" class="field pl-10" data-filter-search>
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
            @if (filled($filters['q'] ?? null))
                <button type="button" data-filter-reset="{{ route('admin.team.index') }}" class="btn btn-ghost">Clear</button>
            @endif
        </div>
    </form>

    <div class="panel overflow-hidden">
        @if ($members->isNotEmpty())
            <ul class="divide-y divide-ink-100">
                @foreach ($members as $member)
                    <li class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
                        <x-ui.avatar :src="$member->photo_url" :initials="$member->initials" :name="$member->name" size="md" />

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-bold text-ink-900">{{ $member->name }}</p>
                                @if ($member->is_active)
                                    <x-ui.badge tone="success">Active</x-ui.badge>
                                @else
                                    <x-ui.badge tone="neutral">Hidden</x-ui.badge>
                                @endif
                                <span class="text-[11px] text-ink-400">Order {{ $member->sort_order }}</span>
                            </div>

                            <p class="mt-0.5 text-xs font-semibold text-brand-700">{{ $member->position }}</p>

                            @if ($member->biography)
                                <p class="mt-1 line-clamp-1 text-xs text-ink-500">{{ $member->biography }}</p>
                            @endif
                        </div>

                        <x-admin.row-actions
                            class="shrink-0"
                            :edit="route('admin.team.edit', $member)"
                            :delete="route('admin.team.destroy', $member)"
                            delete-title="Remove this team member?"
                            delete-label="Remove"
                            :delete-confirm="$member->name.' will be removed from the team page and their photo deleted.'"
                        />
                    </li>
                @endforeach
            </ul>

            <div class="px-4 pb-4">{{ $members->links() }}</div>
        @else
            <div class="p-6">
                <x-ui.empty-state icon="users" title="No team members yet"
                                  description="Adding the committee helps supporters see who is behind the organisation.">
                    <a href="{{ route('admin.team.create') }}" class="btn btn-primary btn-sm">
                        <x-ui.icon name="plus" class="size-4" /> Add the first member
                    </a>
                </x-ui.empty-state>
            </div>
        @endif
    </div>

@endsection
