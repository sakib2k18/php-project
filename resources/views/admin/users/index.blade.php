@extends('layouts.admin')

@section('title', 'Users')
@section('heading', 'Users')
@section('subheading', 'Supporter accounts and the single administrator')

@section('content')

    <x-admin.page-header
        title="All users"
        description="There is exactly one administrator. The policy prevents that account from being deactivated, demoted or deleted — including by itself."
        :count="$users->total()"
    />

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Total accounts" icon="users" tone="brand" :value="number_format($counts['total'])" />
        <x-ui.stat-card label="Active" icon="check-badge" tone="success" :value="number_format($counts['active'])"
                        :href="route('admin.users.index', ['state' => 'active'])" />
        <x-ui.stat-card label="Deactivated" icon="x-circle" tone="neutral" :value="number_format($counts['inactive'])"
                        :href="route('admin.users.index', ['state' => 'inactive'])" />
        <x-ui.stat-card label="Administrators" icon="shield-check" tone="accent" :value="number_format($counts['admins'])"
                        hint="exactly one by design" />
    </div>

    <form method="GET" action="{{ route('admin.users.index') }}" data-filter-form class="panel mb-6 p-4">
        <div class="grid gap-3 lg:grid-cols-[2fr_1fr_1fr_auto]">
            <div class="relative">
                <label for="q" class="sr-only">Search users</label>
                <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-ink-400">
                    <x-ui.icon name="search" class="size-4.5" />
                </span>
                <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                       placeholder="Search name, email, phone or student ID…" class="field pl-10" data-filter-search>
            </div>

            <div>
                <label for="role" class="sr-only">Role</label>
                <select id="role" name="role" class="field">
                    <option value="">All roles</option>
                    <option value="user" @selected(($filters['role'] ?? '') === 'user')>Supporters</option>
                    <option value="admin" @selected(($filters['role'] ?? '') === 'admin')>Administrator</option>
                </select>
            </div>

            <div>
                <label for="state" class="sr-only">Account state</label>
                <select id="state" name="state" class="field">
                    <option value="">Any state</option>
                    <option value="active" @selected(($filters['state'] ?? '') === 'active')>Active</option>
                    <option value="inactive" @selected(($filters['state'] ?? '') === 'inactive')>Deactivated</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1 lg:flex-none">Filter</button>
                @if (array_filter($filters))
                    <button type="button" data-filter-reset="{{ route('admin.users.index') }}" class="btn btn-ghost" aria-label="Clear filters">
                        <x-ui.icon name="x-mark" class="size-4" />
                    </button>
                @endif
            </div>
        </div>
    </form>

    <div class="panel overflow-hidden">
        @if ($users->isNotEmpty())
            <div class="table-wrap hidden lg:block">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">User</th>
                            <th scope="col">Role</th>
                            <th scope="col">State</th>
                            <th scope="col" class="text-right">Donations</th>
                            <th scope="col" class="text-right">Verified total</th>
                            <th scope="col">Joined</th>
                            <th scope="col"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td class="max-w-[18rem]">
                                    <div class="flex items-center gap-3">
                                        <x-ui.avatar :src="$user->avatar_url" :initials="$user->initials" :name="$user->name" size="sm" />
                                        <div class="min-w-0">
                                            <p class="line-clamp-1 font-semibold text-ink-900">{{ $user->name }}</p>
                                            <p class="truncate text-[11px] text-ink-500">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($user->isAdmin())
                                        <x-ui.badge tone="accent" icon="shield-check">Administrator</x-ui.badge>
                                    @else
                                        <x-ui.badge tone="neutral">Supporter</x-ui.badge>
                                    @endif
                                </td>
                                <td>
                                    @if ($user->is_active)
                                        <x-ui.badge tone="success">Active</x-ui.badge>
                                    @else
                                        <x-ui.badge tone="danger">Deactivated</x-ui.badge>
                                    @endif
                                </td>
                                <td class="text-right tabular-nums text-ink-600">
                                    {{ number_format($user->donations_count) }}
                                    <span class="text-[11px] text-ink-400">({{ number_format($user->approved_donations_count) }} ok)</span>
                                </td>
                                <td class="text-right font-bold tabular-nums text-ink-900">{{ money($user->approved_amount ?? 0) }}</td>
                                <td class="whitespace-nowrap text-xs text-ink-500">{{ $user->created_at->format('j M Y') }}</td>
                                <td>
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-ghost btn-sm">View</a>

                                        @can('toggleActive', $user)
                                            <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}"
                                                  data-confirm="{{ $user->is_active ? $user->name.' will be signed out and unable to sign in again until reactivated.' : $user->name.' will be able to sign in again.' }}"
                                                  data-confirm-title="{{ $user->is_active ? 'Deactivate this account?' : 'Reactivate this account?' }}"
                                                  data-confirm-action="{{ $user->is_active ? 'Deactivate' : 'Reactivate' }}"
                                                  data-confirm-tone="{{ $user->is_active ? 'danger' : 'primary' }}">
                                                @csrf
                                                <button type="submit" class="grid size-8 place-items-center rounded-lg text-ink-500 transition hover:bg-ink-100"
                                                        aria-label="{{ $user->is_active ? 'Deactivate account' : 'Reactivate account' }}">
                                                    <x-ui.icon :name="$user->is_active ? 'x-circle' : 'check-circle'" class="size-4" />
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <ul class="divide-y divide-ink-100 lg:hidden">
                @foreach ($users as $user)
                    <li class="p-4">
                        <div class="flex items-start gap-3">
                            <x-ui.avatar :src="$user->avatar_url" :initials="$user->initials" :name="$user->name" size="sm" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-ink-900">{{ $user->name }}</p>
                                <p class="truncate text-xs text-ink-500">{{ $user->email }}</p>
                                <div class="mt-1.5 flex flex-wrap gap-1.5">
                                    @if ($user->isAdmin())
                                        <x-ui.badge tone="accent" icon="shield-check">Administrator</x-ui.badge>
                                    @endif
                                    @if (! $user->is_active)
                                        <x-ui.badge tone="danger">Deactivated</x-ui.badge>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline btn-sm btn-block mt-3">View account</a>
                    </li>
                @endforeach
            </ul>

            <div class="px-4 pb-4">{{ $users->links() }}</div>
        @else
            <div class="p-6">
                <x-ui.empty-state icon="users" title="No users match your filters"
                                  description="Try a different role or state, or clear the search box.">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline btn-sm">Clear filters</a>
                </x-ui.empty-state>
            </div>
        @endif
    </div>

@endsection
