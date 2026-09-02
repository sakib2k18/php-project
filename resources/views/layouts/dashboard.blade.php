@php
    $user = auth()->user();
    $unread = $user->unreadNotifications()->count();
    $pendingDonations = $user->donations()->where('status', \App\Models\Donation::STATUS_PENDING)->count();

    $navigation = [
        ['label' => 'Dashboard',     'icon' => 'home',        'route' => 'dashboard',           'patterns' => ['dashboard']],
        ['label' => 'My profile',    'icon' => 'user-circle', 'route' => 'profile.edit',        'patterns' => ['profile.*']],
        ['label' => 'My donations',  'icon' => 'receipt',     'route' => 'donations.index',     'patterns' => ['donations.index', 'donations.show', 'donations.receipt'], 'badge' => $pendingDonations],
        ['label' => 'Make a donation','icon' => 'heart',      'route' => 'donations.create',    'patterns' => ['donations.create']],
        ['label' => 'Volunteer',     'icon' => 'users',       'route' => 'volunteer.index',     'patterns' => ['volunteer.*']],
        ['label' => 'Notifications', 'icon' => 'bell',        'route' => 'notifications.index', 'patterns' => ['notifications.*'], 'badge' => $unread],
    ];
@endphp

@extends('layouts.app')

@section('content')

    <div class="border-b border-ink-100 bg-white">
        <div class="shell py-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <x-ui.avatar :src="$user->avatar_url" :initials="$user->initials" :name="$user->name" size="lg" class="ring-brand-100" />
                    <div class="min-w-0">
                        <h1 class="display truncate text-2xl text-ink-900">@yield('heading', 'My dashboard')</h1>
                        <p class="mt-0.5 truncate text-sm text-ink-500">@yield('subheading', $user->email)</p>
                    </div>
                </div>

                <div class="flex shrink-0 flex-wrap gap-2">
                    @yield('actions')
                </div>
            </div>
        </div>
    </div>

    <div class="shell py-8">
        <div class="grid gap-8 lg:grid-cols-[15rem_1fr] lg:gap-10">

            {{-- Sidebar --}}
            <aside class="lg:sticky lg:top-24 lg:h-fit">
                {{-- Mobile: horizontal scroller. Desktop: vertical list. --}}
                <nav aria-label="Dashboard" class="-mx-5 overflow-x-auto px-5 lg:mx-0 lg:overflow-visible lg:px-0">
                    <ul class="flex gap-1.5 lg:flex-col lg:gap-0.5">
                        @foreach ($navigation as $item)
                            @php
                                $isActive = request()->routeIs(...$item['patterns']);
                            @endphp

                            <li class="shrink-0">
                                <a href="{{ route($item['route']) }}"
                                   class="flex items-center gap-2.5 whitespace-nowrap rounded-xl px-3.5 py-2.5 text-sm font-semibold transition
                                          {{ $isActive ? 'bg-brand-600 text-white shadow-[0_4px_14px_-4px_rgba(12,66,52,.5)]' : 'text-ink-600 hover:bg-white hover:text-brand-700' }}"
                                   @if ($isActive) aria-current="page" @endif>
                                    <x-ui.icon :name="$item['icon']" class="size-4 shrink-0" />
                                    <span class="flex-1">{{ $item['label'] }}</span>

                                    @if (! empty($item['badge']))
                                        <span class="grid min-w-5 place-items-center rounded-full px-1.5 text-[10px] font-bold
                                                     {{ $isActive ? 'bg-white/20 text-white' : 'bg-accent-100 text-accent-700' }}">
                                            {{ $item['badge'] }}
                                        </span>
                                    @endif
                                </a>
                            </li>
                        @endforeach

                        <li class="shrink-0 lg:mt-3 lg:border-t lg:border-ink-100 lg:pt-3">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="flex w-full items-center gap-2.5 whitespace-nowrap rounded-xl px-3.5 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">
                                    <x-ui.icon name="logout" class="size-4 shrink-0" />
                                    <span>Sign out</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </nav>
            </aside>

            {{-- Panel --}}
            <div class="min-w-0">
                @yield('panel')
            </div>
        </div>
    </div>

@endsection
