@extends('layouts.admin')

@section('title', 'User account')
@section('heading', $user->name)
@section('subheading', 'Member since '.$user->created_at->format('F Y'))

@section('content')

    <x-admin.page-header :title="$user->name" :description="$user->email">
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline">
            <x-ui.icon name="arrow-left" class="size-4" /> All users
        </a>
    </x-admin.page-header>

    <div class="grid gap-6 xl:grid-cols-[1fr_22rem]">

        <div class="space-y-6">
            {{-- Profile --}}
            <div class="panel">
                <div class="panel-head">
                    <h3 class="panel-title">Account details</h3>
                    <div class="flex gap-2">
                        @if ($user->isAdmin())
                            <x-ui.badge tone="accent" icon="shield-check">Administrator</x-ui.badge>
                        @else
                            <x-ui.badge tone="neutral">Supporter</x-ui.badge>
                        @endif

                        @if ($user->is_active)
                            <x-ui.badge tone="success">Active</x-ui.badge>
                        @else
                            <x-ui.badge tone="danger">Deactivated</x-ui.badge>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4 border-b border-ink-100 p-5">
                    <x-ui.avatar :src="$user->avatar_url" :initials="$user->initials" :name="$user->name" size="lg" />
                    <div class="min-w-0 flex-1">
                        <p class="text-base font-bold text-ink-900">{{ $user->name }}</p>
                        <p class="mt-0.5 text-sm text-ink-600">{{ $user->email }}</p>
                        @if ($user->bio)
                            <p class="mt-2 max-w-lg text-xs leading-relaxed text-ink-500">{{ $user->bio }}</p>
                        @endif
                    </div>
                </div>

                <dl class="divide-y divide-ink-100">
                    @php
                        $rows = [
                            ['Phone number', $user->phone ?: '—'],
                            ['Student / member ID', $user->student_id ?: '—'],
                            ['Address', $user->address ?: '—'],
                            ['Email verified', $user->email_verified_at?->format('j F Y') ?? 'Not verified'],
                            ['Last signed in', $user->last_login_at?->format('j F Y, g:i A') ?? 'Never'],
                            ['Registered', $user->created_at->format('j F Y, g:i A')],
                        ];
                    @endphp

                    @foreach ($rows as [$label, $value])
                        <div class="flex flex-col gap-1 p-4 sm:flex-row sm:items-center sm:justify-between sm:gap-6">
                            <dt class="shrink-0 text-sm text-ink-500">{{ $label }}</dt>
                            <dd class="text-sm font-medium text-ink-800 sm:max-w-md sm:text-right">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            {{-- Donations --}}
            <div class="panel">
                <div class="panel-head">
                    <h3 class="panel-title">Donation history</h3>
                    <a href="{{ route('admin.donations.index', ['q' => $user->email]) }}" class="link-arrow !text-xs">
                        All records <x-ui.icon name="arrow-right" class="size-3.5" />
                    </a>
                </div>

                @if ($donations->isNotEmpty())
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Reference</th>
                                    <th scope="col">Campaign</th>
                                    <th scope="col" class="text-right">Amount</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Status</th>
                                    <th scope="col"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($donations as $donation)
                                    <tr>
                                        <td class="whitespace-nowrap font-mono text-xs font-semibold text-ink-900">{{ $donation->reference }}</td>
                                        <td class="max-w-[14rem]"><span class="line-clamp-1 text-ink-600">{{ $donation->campaign?->title ?? 'General fund' }}</span></td>
                                        <td class="whitespace-nowrap text-right font-bold text-ink-900">{{ money($donation->amount) }}</td>
                                        <td class="whitespace-nowrap text-ink-500">{{ $donation->donated_on->format('j M Y') }}</td>
                                        <td><x-ui.status-badge :status="$donation->status" :label="$donation->status_label" /></td>
                                        <td class="text-right">
                                            <a href="{{ route('admin.donations.show', $donation) }}" class="btn btn-ghost btn-sm">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="p-6 text-center text-sm text-ink-500">This account has not recorded any donations.</p>
                @endif
            </div>

            {{-- Volunteer --}}
            @if ($volunteer)
                <div class="panel">
                    <div class="panel-head">
                        <h3 class="panel-title">Volunteer application</h3>
                        <x-ui.status-badge :status="$volunteer->status" :label="$volunteer->status_label" />
                    </div>

                    <div class="p-5">
                        <p class="text-sm text-ink-700">
                            <span class="font-semibold">{{ $volunteer->preferred_activity }}</span>
                            <span class="divider-dot">{{ $volunteer->availability_label }}</span>
                        </p>
                        <p class="mt-2 line-clamp-2 text-xs leading-relaxed text-ink-500">{{ $volunteer->motivation }}</p>

                        <a href="{{ route('admin.volunteers.show', $volunteer) }}" class="btn btn-outline btn-sm mt-4">
                            <x-ui.icon name="hand-heart" class="size-4" /> Open application
                        </a>
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <aside class="space-y-6">
            <div class="panel p-5">
                <h3 class="panel-title mb-4">Giving summary</h3>

                <dl class="space-y-3.5 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-ink-500">Records</dt>
                        <dd class="font-bold tabular-nums text-ink-900">{{ number_format($stats['count']) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-t border-ink-100 pt-3.5">
                        <dt class="text-ink-500">Verified total</dt>
                        <dd class="font-bold text-brand-700">{{ money($stats['approved_amount']) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-t border-ink-100 pt-3.5">
                        <dt class="text-ink-500">Awaiting review</dt>
                        <dd class="font-bold tabular-nums text-ink-900">{{ number_format($stats['pending_count']) }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Account controls --}}
            <div class="panel p-5">
                <h3 class="panel-title mb-2">Account controls</h3>

                @if ($user->isAdmin())
                    <p class="rounded-xl bg-accent-50 p-3.5 text-xs leading-relaxed text-accent-800">
                        This is the administrator account. It cannot be deactivated, demoted or deleted — the
                        UserPolicy refuses those actions so the organisation is never left without an administrator.
                    </p>
                @else
                    <p class="mb-4 text-xs leading-relaxed text-ink-500">
                        Deactivating signs the user out on their next request and blocks further sign-ins. Their
                        donation records are unaffected.
                    </p>

                    @can('toggleActive', $user)
                        <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}"
                              data-confirm="{{ $user->is_active ? $user->name.' will be signed out and unable to sign in until reactivated.' : $user->name.' will be able to sign in again.' }}"
                              data-confirm-title="{{ $user->is_active ? 'Deactivate this account?' : 'Reactivate this account?' }}"
                              data-confirm-action="{{ $user->is_active ? 'Deactivate' : 'Reactivate' }}"
                              data-confirm-tone="{{ $user->is_active ? 'danger' : 'primary' }}">
                            @csrf
                            <button type="submit" class="btn {{ $user->is_active ? 'btn-outline !text-rose-600' : 'btn-primary' }} btn-block">
                                <x-ui.icon :name="$user->is_active ? 'x-circle' : 'check-circle'" class="size-4" />
                                {{ $user->is_active ? 'Deactivate account' : 'Reactivate account' }}
                            </button>
                        </form>
                    @endcan
                @endif
            </div>

            @can('delete', $user)
                <div class="panel border-rose-200 p-5">
                    <h3 class="panel-title mb-2 text-rose-700">Delete account</h3>
                    <p class="mb-4 text-xs leading-relaxed text-ink-500">
                        Deleting removes the profile and sign-in details. Donation records are kept — their
                        <code class="rounded bg-ink-100 px-1">user_id</code> is set to null — so the financial history
                        stays complete.
                    </p>

                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                          data-confirm="{{ $user->name }}'s account will be permanently deleted. Their donation records are kept for the organisation's accounts."
                          data-confirm-title="Delete this account?" data-confirm-action="Delete permanently">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <x-ui.icon name="trash" class="size-4" /> Delete account
                        </button>
                    </form>
                </div>
            @endcan
        </aside>
    </div>

@endsection
