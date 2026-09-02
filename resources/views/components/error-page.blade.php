@props([
    'code',
    'title',
    'description',
    'icon' => 'exclamation',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ $code }} — {{ $title }} | {{ settings()->name() }}</title>

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>

<body class="grid min-h-screen place-items-center bg-brand-950 px-5 py-12 antialiased">
    <div class="absolute inset-0" aria-hidden="true">
        <div class="absolute inset-0 bg-[radial-gradient(120%_110%_at_25%_0%,#0f7d5a_0%,#0c4234_50%,#05261e_100%)]"></div>
        <div class="grain absolute inset-0 opacity-50"></div>
    </div>

    <main class="relative w-full max-w-lg text-center">
        <a href="{{ route('home') }}" class="mx-auto flex w-fit items-center gap-2.5">
            <span class="grid size-11 place-items-center rounded-xl bg-brand-600 text-white">
                <x-ui.icon name="hand-heart" class="size-6" />
            </span>
            <span class="display text-xl text-white">{{ settings()->name() }}</span>
        </a>

        <p class="display mt-12 text-[6rem] leading-none text-white/15 sm:text-[8rem]">{{ $code }}</p>

        <span class="-mt-8 mx-auto grid size-16 place-items-center rounded-2xl border border-white/15 bg-white/10 text-white backdrop-blur">
            <x-ui.icon :name="$icon" class="size-8" />
        </span>

        <h1 class="display mt-6 text-3xl text-white sm:text-4xl">{{ $title }}</h1>
        <p class="mx-auto mt-4 max-w-md text-[15px] leading-relaxed text-white/70">{{ $description }}</p>

        {{ $slot }}

        <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="{{ route('home') }}" class="btn btn-accent btn-lg">
                <x-ui.icon name="home" class="size-5" /> Back to the homepage
            </a>
            <a href="{{ route('contact.create') }}" class="btn btn-on-dark btn-lg">
                <x-ui.icon name="mail" class="size-5" /> Contact us
            </a>
        </div>

        <nav class="mt-10 border-t border-white/10 pt-6" aria-label="Useful links">
            <p class="text-[11px] font-bold uppercase tracking-[0.13em] text-white/40">Or try one of these</p>
            <ul class="mt-3 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-sm">
                <li><a href="{{ route('campaigns.index') }}" class="text-white/70 transition hover:text-white">Campaigns</a></li>
                <li><a href="{{ route('projects.index') }}" class="text-white/70 transition hover:text-white">Projects</a></li>
                <li><a href="{{ route('news.index') }}" class="text-white/70 transition hover:text-white">News</a></li>
                <li><a href="{{ route('about') }}" class="text-white/70 transition hover:text-white">About</a></li>
                <li><a href="{{ route('search') }}" class="text-white/70 transition hover:text-white">Search</a></li>
            </ul>
        </nav>
    </main>
</body>
</html>
