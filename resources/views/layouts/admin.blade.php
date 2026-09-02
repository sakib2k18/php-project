@php
    /*
     * Sidebar badge counts. Cheap aggregate queries, resolved once per request
     * and shared with the sidebar component.
     */
    $sidebarStats = [
        'donations_pending' => \App\Models\Donation::query()->pending()->count(),
        'volunteers_pending' => \App\Models\Volunteer::query()->pending()->count(),
        'messages_unread' => \App\Models\ContactMessage::query()->unread()->count(),
    ];

    $admin = auth()->user();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'Dashboard') — {{ $site->name() }} Admin</title>

    @if ($site->imageUrl('favicon'))
        <link rel="icon" href="{{ $site->imageUrl('favicon') }}">
    @else
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>

<body class="min-h-screen bg-sand antialiased">
    <a href="#admin-main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-brand-600 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
        Skip to content
    </a>

    <div class="lg:flex">

        {{-- Desktop sidebar --}}
        <aside class="fixed inset-y-0 left-0 hidden w-64 lg:block">
            <x-admin.sidebar :stats="$sidebarStats" />
        </aside>

        {{-- Mobile off-canvas sidebar --}}
        <div data-panel-backdrop="admin-sidebar" hidden class="fixed inset-0 z-[55] bg-ink-950/50 backdrop-blur-sm lg:hidden"></div>

        <aside
            data-panel="admin-sidebar"
            class="fixed inset-y-0 left-0 z-[56] w-[min(17rem,85vw)] -translate-x-full transition-transform duration-300 ease-out data-[open=true]:translate-x-0 lg:hidden"
            role="dialog" aria-modal="true" aria-label="Admin navigation"
        >
            <x-admin.sidebar :stats="$sidebarStats" />
        </aside>

        {{-- Content --}}
        <div class="min-w-0 flex-1 lg:ml-64">

            {{-- Top bar --}}
            <header class="sticky top-0 z-40 border-b border-ink-100 bg-sand/90 backdrop-blur-lg">
                <div class="flex h-16 items-center gap-3 px-4 sm:px-6">
                    <button type="button" data-panel-toggle="admin-sidebar" aria-expanded="false"
                            class="grid rounded-lg p-2 text-ink-700 transition hover:bg-ink-100 lg:hidden"
                            aria-label="Open menu">
                        <x-ui.icon name="menu" class="size-5.5" />
                    </button>

                    <div class="min-w-0 flex-1">
                        <h1 class="truncate text-base font-bold text-ink-900 sm:text-lg">@yield('heading', 'Dashboard')</h1>
                        @hasSection('subheading')
                            <p class="truncate text-xs text-ink-500">@yield('subheading')</p>
                        @endif
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        @if ($sidebarStats['donations_pending'] > 0)
                            <a href="{{ route('admin.donations.index', ['status' => 'pending']) }}"
                               class="hidden items-center gap-1.5 rounded-lg bg-accent-50 px-2.5 py-1.5 text-xs font-bold text-accent-700 ring-1 ring-accent-100 transition hover:bg-accent-100 sm:inline-flex">
                                <x-ui.icon name="clock" class="size-3.5" />
                                {{ $sidebarStats['donations_pending'] }} to verify
                            </a>
                        @endif

                        <a href="{{ route('home') }}" target="_blank" rel="noopener"
                           class="hidden rounded-lg p-2 text-ink-500 transition hover:bg-ink-100 hover:text-ink-900 sm:grid"
                           aria-label="Open the public site in a new tab">
                            <x-ui.icon name="globe" class="size-5" />
                        </a>

                        <div class="relative">
                            <button type="button" data-dropdown-toggle="admin-account" aria-expanded="false" aria-haspopup="true"
                                    class="flex items-center gap-2 rounded-full border border-ink-200 bg-white py-1 pl-1 pr-2.5 transition hover:border-brand-300">
                                <x-ui.avatar :src="$admin->avatar_url" :initials="$admin->initials" :name="$admin->name" size="xs" />
                                <span class="hidden text-xs font-semibold text-ink-800 sm:inline">{{ Str::before($admin->name, ' ') }}</span>
                                <x-ui.icon name="chevron-down" class="size-3.5 text-ink-400" />
                            </button>

                            <div data-dropdown="admin-account" hidden
                                 class="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-xl border border-ink-100 bg-white shadow-[var(--shadow-deep)]">
                                <div class="border-b border-ink-100 bg-ink-50 px-4 py-3">
                                    <p class="truncate text-sm font-bold text-ink-900">{{ $admin->name }}</p>
                                    <p class="truncate text-xs text-ink-500">{{ $admin->email }}</p>
                                    <x-ui.badge tone="brand" icon="shield-check" class="mt-2">Administrator</x-ui.badge>
                                </div>

                                <div class="p-1.5">
                                    <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-ink-700 transition hover:bg-ink-100">
                                        <x-ui.icon name="user" class="size-4" /> My profile
                                    </a>
                                    <a href="{{ route('admin.password.edit') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-ink-700 transition hover:bg-ink-100">
                                        <x-ui.icon name="shield-check" class="size-4" /> Change password
                                    </a>
                                    <a href="{{ route('admin.settings.edit') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-ink-700 transition hover:bg-ink-100">
                                        <x-ui.icon name="settings" class="size-4" /> Settings
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
                    </div>
                </div>
            </header>

            <main id="admin-main" class="p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>

            <footer class="border-t border-ink-100 px-4 py-5 text-center text-xs text-ink-400 sm:px-6">
                {{ $site->name() }} administration &middot; Laravel {{ app()->version() }} &middot; PHP {{ PHP_VERSION }}
            </footer>
        </div>
    </div>

    @include('partials.flash')
    @stack('scripts')
</body>
</html>
