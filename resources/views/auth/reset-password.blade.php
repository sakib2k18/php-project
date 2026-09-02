@extends('layouts.auth')

@section('title', 'Choose a new password')

@section('content')

    <div>
        <h1 class="display text-3xl text-ink-900">Choose a new password</h1>
        <p class="mt-2 text-sm leading-relaxed text-ink-600">
            Pick something you have not used elsewhere. Your new password is hashed before it is stored —
            we never see it.
        </p>
    </div>

    <x-form.errors class="mt-6" />

    <form method="POST" action="{{ route('password.store') }}" data-validate class="mt-7 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <x-form.field
            name="email" label="Email address" type="email" :required="true"
            :value="$email" placeholder="you@example.com" icon="mail"
            rules="required|email" autocomplete="username" readonly
        />

        <x-form.password
            name="password" label="New password" id="password"
            rules="required|min:8" autocomplete="new-password" :strength="true"
            help="At least 8 characters, including letters and numbers."
        />

        <x-form.password
            name="password_confirmation" label="Confirm new password" id="password_confirmation"
            rules="required|match:password" autocomplete="new-password"
        />

        <button type="submit" class="btn btn-primary btn-lg btn-block" data-loading-label="Saving…">
            <x-ui.icon name="shield-check" class="size-5" /> Set new password
        </button>
    </form>

    <p class="mt-7 border-t border-ink-100 pt-6 text-center text-sm text-ink-600">
        <a href="{{ route('login') }}" class="font-bold text-brand-700 transition hover:text-brand-800">Back to sign in</a>
    </p>

@endsection
