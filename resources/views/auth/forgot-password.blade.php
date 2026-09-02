@extends('layouts.auth')

@section('title', 'Reset your password')

@section('content')

    <div>
        <h1 class="display text-3xl text-ink-900">Forgot your password?</h1>
        <p class="mt-2 text-sm leading-relaxed text-ink-600">
            Enter the email address on your account and we will send a link to set a new password.
        </p>
    </div>

    @if (session('success'))
        <div class="mt-6 flex gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4" role="status">
            <x-ui.icon name="check-circle" class="size-5 shrink-0 text-emerald-600" />
            <p class="text-sm leading-relaxed text-emerald-800">{{ session('success') }}</p>
        </div>
    @endif

    <x-form.errors class="mt-6" />

    <form method="POST" action="{{ route('password.email') }}" data-validate class="mt-7 space-y-5">
        @csrf

        <x-form.field
            name="email" label="Email address" type="email" :required="true"
            placeholder="you@example.com" icon="mail"
            rules="required|email" autocomplete="username" autofocus
        />

        <button type="submit" class="btn btn-primary btn-lg btn-block" data-loading-label="Sending link…">
            <x-ui.icon name="mail" class="size-5" /> Email me a reset link
        </button>
    </form>

    <div class="mt-6 rounded-xl bg-ink-50 p-4">
        <p class="flex gap-2.5 text-xs leading-relaxed text-ink-600">
            <x-ui.icon name="info" class="size-4 shrink-0 text-ink-400" />
            <span>
                For security, we show the same message whether or not the address is registered.
                In this local installation mail is written to <code class="rounded bg-white px-1 py-0.5 text-[11px]">storage/logs/laravel.log</code>
                rather than sent.
            </span>
        </p>
    </div>

    <p class="mt-7 border-t border-ink-100 pt-6 text-center text-sm text-ink-600">
        Remembered it?
        <a href="{{ route('login') }}" class="font-bold text-brand-700 transition hover:text-brand-800">Back to sign in</a>
    </p>

@endsection
