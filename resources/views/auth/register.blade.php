@extends('layouts.auth')

@section('title', 'Create an account')

@section('content')

    <div>
        <h1 class="display text-3xl text-ink-900">Create your account</h1>
        <p class="mt-2 text-sm text-ink-600">
            It takes a minute and it is free. An account lets you record donations, track verification and
            download receipts.
        </p>
    </div>

    <x-form.errors class="mt-6" />

    <form method="POST" action="{{ route('register') }}" data-validate class="mt-7 space-y-5">
        @csrf

        {{--
            There is no role field here and none is accepted server-side:
            RegisteredUserController assigns User::ROLE_USER explicitly.
        --}}

        <x-form.field
            name="name" label="Full name" :required="true"
            placeholder="Your full name" icon="user"
            rules="required|min:3|max:120" autocomplete="name" autofocus
        />

        <x-form.field
            name="email" label="Email address" type="email" :required="true"
            placeholder="you@example.com" icon="mail"
            rules="required|email|max:150" autocomplete="email"
        />

        <div class="grid gap-5 sm:grid-cols-2">
            <x-form.field
                name="phone" label="Phone number" type="tel"
                placeholder="+880 17XX-XXXXXX" icon="phone"
                rules="phone" autocomplete="tel"
                help="Optional"
            />

            <x-form.field
                name="student_id" label="Student / member ID"
                placeholder="e.g. 1807042"
                help="Optional"
            />
        </div>

        <x-form.password
            name="password" label="Password" id="password"
            rules="required|min:8" autocomplete="new-password" :strength="true"
            help="At least 8 characters, including letters and numbers."
        />

        <x-form.password
            name="password_confirmation" label="Confirm password" id="password_confirmation"
            rules="required|match:password" autocomplete="new-password"
        />

        <x-form.checkbox name="terms" label="I agree to how KUET TRY handles my information" rules="required">
            <span class="mt-1 block text-xs leading-relaxed text-ink-500">
                Your details are used only to manage your account and your donation records. They are never
                sold, shared or published.
            </span>
        </x-form.checkbox>

        <button type="submit" class="btn btn-primary btn-lg btn-block" data-loading-label="Creating account…">
            <x-ui.icon name="user-circle" class="size-5" /> Create account
        </button>
    </form>

    <p class="mt-7 border-t border-ink-100 pt-6 text-center text-sm text-ink-600">
        Already have an account?
        <a href="{{ route('login') }}" class="font-bold text-brand-700 transition hover:text-brand-800">Sign in instead</a>
    </p>

@endsection
