@php
    $links = [
        ['label' => 'Home',        'route' => 'home',            'patterns' => ['home']],
        ['label' => 'About',       'route' => 'about',           'patterns' => ['about', 'team']],
        ['label' => 'Campaigns',   'route' => 'campaigns.index', 'patterns' => ['campaigns.*']],
        ['label' => 'Projects',    'route' => 'projects.index',  'patterns' => ['projects.*']],
        ['label' => 'Events',      'route' => 'events.index',    'patterns' => ['events.*']],
        ['label' => 'Get Involved','route' => 'get-involved',    'patterns' => ['get-involved', 'volunteer.*']],
        ['label' => 'Stories',     'route' => 'stories.index',   'patterns' => ['stories.*']],
        ['label' => 'News',        'route' => 'news.index',      'patterns' => ['news.*']],
        ['label' => 'Gallery',     'route' => 'gallery.index',   'patterns' => ['gallery.*']],
        ['label' => 'Contact',     'route' => 'contact.create',  'patterns' => ['contact.*']],
    ];

    $user = auth()->user();
    $unread = $user ? $user->unreadNotifications()->count() : 0;
@endphp

<header
    data-sticky-header
    class="sticky top-0 z-50 border-b border-transparent bg-sand/85 backdrop-blur-lg transition-all
           data-[scrolled]:border-ink-100 data-[scrolled]:bg-sand/95 data-[scrolled]:shadow-[0_1px_16px_-6px_rgba(16,20,19,.18)]"
>
    {{-- Emergency ribbon: only rendered when a live emergency campaign exists. --}}
    @php
        $emergency = \App\Models\Campaign::query()->emergency()->latest()->first();
    @endphp

    @if ($emergency)
        <div class="bg-accent-600 text-white">
            <div class="shell flex flex-wrap items-center justify-center gap-x-3 gap-y-1 py-1.5 text-center text-xs">
                <span class="inline-flex items-center gap-1.5 font-bold uppercase tracking-wider">
                    <x-ui.icon name="exclamation" class="size-3.5" /> Emergency appeal
                </span>
                <span class="text-white/90">{{ $emergency->title }}</span>
                <a href="{{ route('campaigns.show', $emergency) }}" class="font-bold underline underline-offset-2 hover:text-white">
                    Support now
                </a>
            </div>
        </div>
    @endif

    <div class="shell">
        <div class="flex h-16 items-center justify-between gap-4 lg:h-[4.5rem]">
            {{-- Brand --}}
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5" aria-label="{{ $site->name() }} — home">
                @if ($site->imageUrl('logo'))
                    <img src="{{ $site->imageUrl('logo') }}" alt="" class="size-10 rounded-xl object-cover">
                @else
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-brand-600 text-white shadow-[0_4px_14px_-4px_rgba(12,66,52,.6)]">
                        <x-ui.icon name="hand-heart" class="size-5.5" />
                    </span>
                @endif

                <span class="min-w-0">
                    <span class="display block text-lg leading-none text-ink-900">{{ $site->name() }}</span>
                    {{-- Hidden between xl and 2xl, where the ten nav links need the room. --}}
                    <span class="mt-0.5 hidden text-[10px] font-semibold uppercase tracking-[0.13em] text-brand-600 sm:block xl:hidden 2xl:block">
                        Humanitarian Organization
                    </span>
                </span>
            </a>

            {{-- Desktop navigation --}}
            <nav class="hidden items-center gap-0.5 xl:flex" aria-label="Main">
                @foreach ($links as $link)
                    <a href="{{ route($link['route']) }}"
                       class="nav-link {{ active_class($link['patterns']) }}"
                       @if (request()->routeIs(...$link['patterns'])) aria-current="page" @endif
                    >{{ $link['label'] }}</a>
                @endforeach
            </nav>

            {{-- Actions --}}
            <div class="flex shrink-0 items-center gap-2">
                <a href="{{ route('search') }}" class="hidden rounded-lg p-2 text-ink-500 transition hover:bg-ink-100 hover:text-ink-900 sm:grid" aria-label="Search the site">
                    <x-ui.icon name="search" class="size-5" />
                </a>

                @guest
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm hidden sm:inline-flex">Sign in</a>
                    <a href="{{ route('donations.create') }}" class="btn btn-primary btn-sm">
                        <x-ui.icon name="heart" class="size-4" />
                        <span class="hidden sm:inline">Donate now</span>
                        <span class="sm:hidden">Donate</span>
                    </a>
                @else
                    <a href="{{ route('notifications.index') }}" class="relative hidden rounded-lg p-2 text-ink-500 transition hover:bg-ink-100 hover:text-ink-900 sm:grid" aria-label="Notifications{{ $unread ? " ({$unread} unread)" : '' }}">
                        <x-ui.icon name="bell" class="size-5" />
                        @if ($unread)
                            <span class="absolute right-1 top-1 grid size-4 place-items-center rounded-full bg-accent-500 text-[9px] font-bold text-white">{{ min($unread, 9) }}</span>
                        @endif
                    </a>

                    <div class="relative hidden sm:block">
                        <button type="button" data-dropdown-toggle="account" aria-expanded="false" aria-haspopup="true"
                                class="flex items-center gap-2 rounded-full border border-ink-200 bg-white py-1 pl-1 pr-2.5 transition hover:border-brand-300">
                            <x-ui.avatar :src="$user->avatar_url" :initials="$user->initials" :name="$user->name" size="xs" />
                            <x-ui.icon name="chevron-down" class="size-3.5 text-ink-400" />
                        </button>

                        <div data-dropdown="account" hidden
                             class="absolute right-0 z-50 mt-2 w-60 overflow-hidden rounded-xl border border-ink-100 bg-white shadow-[var(--shadow-deep)]">
                            <div class="border-b border-ink-100 bg-ink-50 px-4 py-3">
                                <p class="truncate text-sm font-bold text-ink-900">{{ $user->name }}</p>
                                <p class="truncate text-xs text-ink-500">{{ $user->email }}</p>
                            </div>

                            <div class="p-1.5">
                                @if ($user->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-brand-700 transition hover:bg-brand-50">
                                        <x-ui.icon name="shield-check" class="size-4" /> Admin dashboard
                                    </a>
                                @endif
                                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-ink-700 transition hover:bg-ink-100">
                                    <x-ui.icon name="home" class="size-4" /> My dashboard
                                </a>
                                <a href="{{ route('donations.index') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-ink-700 transition hover:bg-ink-100">
                                    <x-ui.icon name="receipt" class="size-4" /> My donations
                                </a>
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-ink-700 transition hover:bg-ink-100">
                                    <x-ui.icon name="user-circle" class="size-4" /> Profile
                                </a>
                            </div>

                            <form method="POST" action="{{ route('logout') }}" class="border-t border-ink-100 p-1.5">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-rose-600 transition hover:bg-rose-50">
                                    <x-ui.icon name="logout" class="size-4" /> Sign out
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Signed-in users already have Donate in the dashboard menu,
                         so the header button only reappears once there is room. --}}
                    <a href="{{ route('donations.create') }}" class="btn btn-primary btn-sm hidden lg:inline-flex xl:hidden 2xl:inline-flex">
                        <x-ui.icon name="heart" class="size-4" /> Donate
                    </a>
                @endguest

                {{-- Mobile menu button --}}
                <button type="button" data-panel-toggle="mobile-nav" aria-expanded="false" aria-controls="mobile-nav"
                        class="grid rounded-lg p-2 text-ink-700 transition hover:bg-ink-100 xl:hidden" aria-label="Open menu">
                    <x-ui.icon name="menu" class="size-6" />
                </button>
            </div>
        </div>
    </div>
</header>

{{-- Mobile off-canvas navigation --}}
<div data-panel-backdrop="mobile-nav" hidden class="fixed inset-0 z-[55] bg-ink-950/45 backdrop-blur-sm xl:hidden"></div>

<div
    id="mobile-nav"
    data-panel="mobile-nav"
    class="fixed inset-y-0 right-0 z-[56] flex w-[min(20rem,88vw)] translate-x-full flex-col bg-white shadow-[var(--shadow-deep)]
           transition-transform duration-300 ease-out data-[open=true]:translate-x-0 xl:hidden"
    role="dialog"
    aria-modal="true"
    aria-label="Site navigation"
>
    <div class="flex items-center justify-between border-b border-ink-100 px-5 py-4">
        <span class="display text-lg text-ink-900">{{ $site->name() }}</span>
        <button type="button" data-panel-close="mobile-nav" class="rounded-lg p-2 text-ink-500 transition hover:bg-ink-100" aria-label="Close menu">
            <x-ui.icon name="x-mark" class="size-5" />
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto p-4" aria-label="Mobile">
        <ul class="space-y-0.5">
            @foreach ($links as $link)
                <li>
                    <a href="{{ route($link['route']) }}"
                       class="flex items-center justify-between rounded-lg px-3 py-2.5 text-sm font-semibold transition
                              {{ request()->routeIs(...$link['patterns']) ? 'bg-brand-50 text-brand-700' : 'text-ink-700 hover:bg-ink-100' }}">
                        {{ $link['label'] }}
                        <x-ui.icon name="chevron-right" class="size-4 text-ink-300" />
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="my-4 border-t border-ink-100"></div>

        @auth
            <p class="side-heading !text-ink-400">My account</p>
            <ul class="space-y-0.5">
                @if (auth()->user()->isAdmin())
                    <li><a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-semibold text-brand-700 hover:bg-brand-50"><x-ui.icon name="shield-check" class="size-4" /> Admin dashboard</a></li>
                @endif
                <li><a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-semibold text-ink-700 hover:bg-ink-100"><x-ui.icon name="home" class="size-4" /> Dashboard</a></li>
                <li><a href="{{ route('donations.index') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-semibold text-ink-700 hover:bg-ink-100"><x-ui.icon name="receipt" class="size-4" /> My donations</a></li>
                <li><a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-semibold text-ink-700 hover:bg-ink-100"><x-ui.icon name="user-circle" class="size-4" /> Profile</a></li>
                <li><a href="{{ route('notifications.index') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-semibold text-ink-700 hover:bg-ink-100"><x-ui.icon name="bell" class="size-4" /> Notifications @if ($unread)<span class="badge badge-accent">{{ $unread }}</span>@endif</a></li>
            </ul>
        @endauth
    </nav>

    <div class="space-y-2 border-t border-ink-100 p-4">
        <a href="{{ route('donations.create') }}" class="btn btn-primary btn-block">
            <x-ui.icon name="heart" class="size-4" /> Donate now
        </a>

        @guest
            <div class="grid grid-cols-2 gap-2">
                <a href="{{ route('login') }}" class="btn btn-outline">Sign in</a>
                <a href="{{ route('register') }}" class="btn btn-outline">Register</a>
            </div>
        @else
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline btn-block text-rose-600">
                    <x-ui.icon name="logout" class="size-4" /> Sign out
                </button>
            </form>
        @endguest
    </div>
</div>
