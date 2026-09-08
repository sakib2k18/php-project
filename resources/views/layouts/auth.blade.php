<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'Sign in') — {{ $site->name() }}</title>

    @if ($site->imageUrl('favicon'))
        <link rel="icon" href="{{ $site->imageUrl('favicon') }}">
    @else
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen antialiased">
    <div class="grid min-h-screen lg:grid-cols-2">

        {{-- Brand panel — hidden on small screens where it would just push the form down --}}
        <aside class="relative hidden overflow-hidden bg-brand-950 lg:block">
            <div class="absolute inset-0" aria-hidden="true">
                <img src="{{ asset('images/cover.jpg') }}" alt="" class="size-full object-cover">
                <div class="absolute inset-0 bg-[linear-gradient(120deg,#05261ef2_0%,#0c4234e0_55%,#05261ecc_100%)]"></div>
                <div class="absolute inset-0 bg-[radial-gradient(120%_110%_at_25%_0%,#0f7d5a_0%,#0c4234_50%,#05261e_100%)] opacity-60"></div>
                <div class="grain absolute inset-0 opacity-60"></div>
                <div class="absolute -right-20 top-1/4 size-96 rounded-full bg-brand-500/15 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-16 size-80 rounded-full bg-accent-500/10 blur-3xl"></div>
            </div>

            <div class="relative flex h-full flex-col justify-between p-10 xl:p-14">
                <a href="{{ route('home') }}" class="flex w-fit items-center gap-3">
                    @if ($site->imageUrl('logo'))
                        <img src="{{ $site->imageUrl('logo') }}" alt="" class="size-11 rounded-xl object-cover">
                    @else
                        <span class="grid size-11 place-items-center rounded-xl bg-brand-600 text-white">
                            <x-ui.icon name="hand-heart" class="size-6" />
                        </span>
                    @endif
                    <span>
                        <span class="display block text-xl leading-none text-white">{{ $site->name() }}</span>
                        <span class="mt-1 block text-[10px] font-semibold uppercase tracking-[0.14em] text-brand-300">
                            Humanitarian Organization
                        </span>
                    </span>
                </a>

                <div>
                    <h2 class="display text-shadow-hero text-4xl leading-tight text-white xl:text-[2.75rem]">
                        {{ $site->tagline() }}
                    </h2>
                    <p class="mt-4 max-w-md text-[15px] leading-relaxed text-white/70">
                        Create a free account to record donations, track their verification, download receipts and
                        apply to volunteer.
                    </p>

                    <ul class="mt-8 space-y-3 text-sm text-white/70">
                        <li class="flex items-center gap-2.5">
                            <span class="grid size-6 shrink-0 place-items-center rounded-md bg-white/10">
                                <x-ui.icon name="check" class="size-3.5 text-brand-300" />
                            </span>
                            Every donation verified before it counts
                        </li>
                        <li class="flex items-center gap-2.5">
                            <span class="grid size-6 shrink-0 place-items-center rounded-md bg-white/10">
                                <x-ui.icon name="check" class="size-3.5 text-brand-300" />
                            </span>
                            Printable receipts in your dashboard
                        </li>
                        <li class="flex items-center gap-2.5">
                            <span class="grid size-6 shrink-0 place-items-center rounded-md bg-white/10">
                                <x-ui.icon name="check" class="size-3.5 text-brand-300" />
                            </span>
                            Your data is never shared or published
                        </li>
                    </ul>
                </div>

                <p class="text-xs text-white/40">
                    &copy; {{ now()->year }} {{ $site->name() }}. A university project built with Laravel.
                </p>
            </div>
        </aside>

        {{-- Form panel --}}
        <main class="flex flex-col justify-center px-5 py-10 sm:px-8 lg:px-12 xl:px-20">
            <div class="mx-auto w-full max-w-md">
                {{-- Mobile brand --}}
                <a href="{{ route('home') }}" class="mb-8 flex w-fit items-center gap-2.5 lg:hidden">
                    @if ($site->imageUrl('logo'))
                        <img src="{{ $site->imageUrl('logo') }}" alt="" class="size-10 rounded-xl object-cover">
                    @else
                        <span class="grid size-10 place-items-center rounded-xl bg-brand-600 text-white">
                            <x-ui.icon name="hand-heart" class="size-5.5" />
                        </span>
                    @endif
                    <span class="display text-lg text-ink-900">{{ $site->name() }}</span>
                </a>

                {{ $slot ?? '' }}
                @yield('content')

                <p class="mt-8 text-center text-xs text-ink-400">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 transition hover:text-brand-700">
                        <x-ui.icon name="arrow-left" class="size-3.5" /> Back to the website
                    </a>
                </p>
            </div>
        </main>
    </div>

    @include('partials.flash')
</body>
</html>
