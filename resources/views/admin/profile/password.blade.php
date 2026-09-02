@extends('layouts.admin')

@section('title', 'Change password')
@section('heading', 'Change password')
@section('subheading', 'Update the administrator password')

@section('content')

    <x-admin.page-header title="Change your password" description="Passwords are hashed with bcrypt before storage — the plain text is never written anywhere.">
        <a href="{{ route('admin.profile.edit') }}" class="btn btn-outline">
            <x-ui.icon name="arrow-left" class="size-4" /> Back to profile
        </a>
    </x-admin.page-header>

    <x-form.errors class="mb-6" />

    <div class="grid gap-6 xl:grid-cols-[minmax(0,32rem)_1fr]">
        <div class="panel">
            <div class="panel-head">
                <h3 class="panel-title">New password</h3>
            </div>

            <form method="POST" action="{{ route('password.update') }}" data-validate class="space-y-5 p-6">
                @csrf
                @method('PUT')

                <x-form.password
                    name="current_password" label="Current password" id="current_password"
                    rules="required" autocomplete="current-password"
                />

                <x-form.password
                    name="password" label="New password" id="new_password"
                    rules="required|min:8" autocomplete="new-password" :strength="true"
                    help="At least 8 characters, including letters and numbers."
                />

                <x-form.password
                    name="password_confirmation" label="Confirm new password" id="new_password_confirmation"
                    rules="required|match:new_password" autocomplete="new-password"
                />

                <div class="border-t border-ink-100 pt-5">
                    <button type="submit" class="btn btn-primary" data-loading-label="Updating…">
                        <x-ui.icon name="shield-check" class="size-4" /> Update password
                    </button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="panel p-6">
                <h3 class="panel-title mb-4">Why this matters</h3>

                <ul class="space-y-3.5 text-sm text-ink-600">
                    <li class="flex gap-2.5">
                        <x-ui.icon name="shield-check" class="mt-0.5 size-4 shrink-0 text-brand-600" />
                        <span>
                            This is the only administrator account. Anyone with these credentials can approve donations
                            and edit every page on the site.
                        </span>
                    </li>
                    <li class="flex gap-2.5">
                        <x-ui.icon name="exclamation" class="mt-0.5 size-4 shrink-0 text-accent-600" />
                        <span>
                            The seeded password (<code class="rounded bg-ink-100 px-1 text-xs">Admin@12345</code>) is
                            published in the project README. Change it before the site is used for anything real.
                        </span>
                    </li>
                    <li class="flex gap-2.5">
                        <x-ui.icon name="refresh" class="mt-0.5 size-4 shrink-0 text-brand-600" />
                        <span>
                            Changing your password regenerates the session so other sessions are not silently kept
                            alive.
                        </span>
                    </li>
                    <li class="flex gap-2.5">
                        <x-ui.icon name="clock" class="mt-0.5 size-4 shrink-0 text-brand-600" />
                        <span>
                            Sign-in attempts are rate limited to five per minute per email and IP address.
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

@endsection
