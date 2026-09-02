<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-pt-24">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $site->name().' — '.$site->tagline())</title>

    {{-- SEO --}}
    <meta name="description" content="@yield('description', Str::limit(strip_tags($site->get('about', '')), 155))">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#0f7d5a">

    {{-- Open Graph --}}
    <meta property="og:site_name" content="{{ $site->name() }}">
    <meta property="og:type" content="@yield('og:type', 'website')">
    <meta property="og:title" content="@yield('title', $site->name().' — '.$site->tagline())">
    <meta property="og:description" content="@yield('description', Str::limit(strip_tags($site->get('about', '')), 155))">
    <meta property="og:url" content="{{ url()->current() }}">
    @hasSection('og:image')
        <meta property="og:image" content="@yield('og:image')">
    @endif
    <meta name="twitter:card" content="summary_large_image">

    @if ($site->imageUrl('favicon'))
        <link rel="icon" href="{{ $site->imageUrl('favicon') }}">
    @else
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @endif

    {{-- Typography. The stack in app.css falls back gracefully if these fail. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>

<body class="flex min-h-screen flex-col antialiased">
    {{-- Skip link for keyboard and screen-reader users --}}
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-brand-600 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
        Skip to main content
    </a>

    <x-layout.navbar />

    <main id="main" class="flex-1">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <x-layout.footer />

    {{-- Flash messages are handed to the toast module, which renders them safely. --}}
    @include('partials.flash')

    @stack('scripts')
</body>
</html>
