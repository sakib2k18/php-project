@extends('layouts.auth')

@section('title', 'Sign in')

@section('content')

    <div>
        <h1 class="display text-3xl text-ink-900">Welcome back</h1>
        <p class="mt-2 text-sm text-ink-600">
            Sign in to record a donation, download a receipt or check your volunteer application.
        </p>
    </div>

    <x-form.errors class="mt-6" />

    <form method="POST" action="{{ route('login') }}" data-validate class="mt-7 space-y-5">
        @csrf

        <x-form.field
            name="email" label="Email address" type="email" :required="true"
            placeholder="you@example.com" icon="mail"
            rules="required|email" autocomplete="username" autofocus
        />

        <div>
            <x-form.password
                name="password" label="Password"
                rules="required" autocomplete="current-password"
            />

            <p class="mt-2 text-right">
                <a href="{{ route('password.request') }}" class="text-xs font-semibold text-brand-700 transition hover:text-brand-800">
                    Forgot your password?
                </a>
            </p>
        </div>

        {{-- Laravel's remember-me: stores a rotating token in a cookie, never the password. --}}
        <x-form.checkbox
            name="remember" label="Keep me signed in on this device"
            help="Uses a secure, rotating token — your password is never stored in the cookie."
        />

        <button type="submit" class="btn btn-primary btn-lg btn-block" data-loading-label="Signing in…">
            <x-ui.icon name="login" class="size-5" /> Sign in
        </button>
    </form>

    <p class="mt-7 border-t border-ink-100 pt-6 text-center text-sm text-ink-600">
        New to {{ $site->name() }}?
        <a href="{{ route('register') }}" class="font-bold text-brand-700 transition hover:text-brand-800">Create a free account</a>
    </p>

@endsection
