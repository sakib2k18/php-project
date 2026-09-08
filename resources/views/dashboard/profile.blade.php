@extends('layouts.dashboard')

@section('title', 'My profile — '.$site->name())
@section('heading', 'My profile')
@section('subheading', 'Member since '.$user->created_at->format('F Y'))

@section('panel')

    {{-- Overview --}}
    <div class="panel overflow-hidden">
        <div class="relative h-24 bg-gradient-to-br from-brand-700 to-brand-950">
            <div class="grain absolute inset-0 opacity-60"></div>
        </div>

        <div class="px-6 pb-6">
            {{-- `relative` is required: the banner above is also positioned, and a
                 static avatar row would paint underneath it, clipping the avatar. --}}
            <div class="relative -mt-10 flex flex-wrap items-end gap-4">
                <x-ui.avatar :src="$user->avatar_url" :initials="$user->initials" :name="$user->name" size="xl" class="!ring-4 !ring-white" />

                <div class="min-w-0 flex-1 pb-1">
                    <h2 class="display truncate text-2xl text-ink-900">{{ $user->name }}</h2>
                    <p class="truncate text-sm text-ink-500">{{ $user->email }}</p>
                </div>

                <div class="flex flex-wrap gap-2 pb-1">
                    <x-ui.badge tone="brand" icon="user-circle">Supporter</x-ui.badge>
                    @if ($user->email_verified_at)
                        <x-ui.badge tone="success" icon="check-badge">Verified email</x-ui.badge>
                    @endif
                    @if ($volunteer)
                        <x-ui.status-badge :status="$volunteer->status" :label="'Volunteer: '.$volunteer->status_label" />
                    @endif
                </div>
            </div>

            @if ($user->bio)
                <p class="mt-4 max-w-2xl text-sm leading-relaxed text-ink-600">{{ $user->bio }}</p>
            @endif

            <dl class="mt-6 grid gap-4 border-t border-ink-100 pt-5 sm:grid-cols-3">
                <div>
                    <dt class="text-[11px] font-bold uppercase tracking-wider text-ink-400">Donations recorded</dt>
                    <dd class="stat-value mt-1 text-xl">{{ number_format($stats['donations']) }}</dd>
                </div>
                <div class="sm:border-x sm:border-ink-100 sm:px-4">
                    <dt class="text-[11px] font-bold uppercase tracking-wider text-ink-400">Verified total</dt>
                    <dd class="stat-value mt-1 text-xl text-brand-700">{{ money($stats['approved_amount']) }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-bold uppercase tracking-wider text-ink-400">Awaiting verification</dt>
                    <dd class="stat-value mt-1 text-xl">{{ number_format($stats['pending_count']) }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <x-form.errors class="mt-6" />

    {{-- Edit profile --}}
    <div class="panel mt-6">
        <div class="panel-head">
            <h2 class="panel-title">Profile details</h2>
        </div>

        {{-- Kept outside the profile form: nesting <form> elements is invalid HTML. --}}
        @if ($user->avatar)
            <form method="POST" action="{{ route('profile.avatar.destroy') }}" id="remove-avatar-form"
                  data-confirm="Remove your profile photo? Your initials will be shown instead."
                  data-confirm-title="Remove photo" data-confirm-action="Remove" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" data-validate class="space-y-5 p-6">
            @csrf
            @method('PATCH')

            <x-form.image
                name="avatar" label="Profile photo"
                :current="$user->avatar_url"
                aspect="aspect-square"
            />

            @if ($user->avatar)
                <div class="-mt-2">
                    <button type="submit" form="remove-avatar-form" class="btn btn-ghost btn-sm !text-rose-600">
                        <x-ui.icon name="trash" class="size-3.5" /> Remove current photo
                    </button>
                </div>
            @endif

            <div class="grid gap-5 border-t border-ink-100 pt-5 sm:grid-cols-2">
                <x-form.field
                    name="name" label="Full name" :required="true"
                    :value="$user->name" icon="user"
                    rules="required|min:3|max:120" autocomplete="name"
                />

                <x-form.field
                    name="email" label="Email address" type="email" :required="true"
                    :value="$user->email" icon="mail"
                    rules="required|email|max:150" autocomplete="email"
                    help="Changing this will require you to verify the new address."
                />
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.field
                    name="phone" label="Phone number" type="tel"
                    :value="$user->phone" icon="phone"
                    rules="phone" autocomplete="tel"
                />

                <x-form.field
                    name="student_id" label="Student / member ID"
                    :value="$user->student_id"
                    placeholder="e.g. 1807042"
                />
            </div>

            <x-form.field
                name="address" label="Address"
                :value="$user->address" icon="map-pin"
                rules="max:255" autocomplete="street-address"
            />

            <x-form.textarea
                name="bio" label="About you"
                :value="$user->bio"
                placeholder="A sentence or two — why you support KUET TRY, or what you would like to help with."
                :rows="3" :maxlength="1000"
            />

            {{-- Role is deliberately read-only: it is not accepted by ProfileUpdateRequest. --}}
            <div class="rounded-xl bg-ink-50 p-4">
                <p class="flex gap-2.5 text-xs leading-relaxed text-ink-600">
                    <x-ui.icon name="shield-check" class="size-4 shrink-0 text-ink-400" />
                    <span>
                        Your account type (<strong>Supporter</strong>) and account status are managed by the
                        organisation and cannot be changed from this form.
                    </span>
                </p>
            </div>

            <div class="flex flex-wrap gap-3 border-t border-ink-100 pt-5">
                <button type="submit" class="btn btn-primary" data-loading-label="Saving…">
                    <x-ui.icon name="check" class="size-4" /> Save changes
                </button>
                <a href="{{ route('dashboard') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>

    {{-- Password --}}
    <div class="panel mt-6">
        <div class="panel-head">
            <h2 class="panel-title">Change password</h2>
        </div>

        <form method="POST" action="{{ route('password.update') }}" data-validate class="space-y-5 p-6">
            @csrf
            @method('PUT')

            <p class="text-sm leading-relaxed text-ink-600">
                Passwords are hashed with bcrypt before they are stored — nobody at {{ $site->name() }}, including
                the administrator, can read yours.
            </p>

            <x-form.password
                name="current_password" label="Current password" id="current_password"
                rules="required" autocomplete="current-password"
            />

            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.password
                    name="password" label="New password" id="new_password"
                    rules="required|min:8" autocomplete="new-password" :strength="true"
                    help="At least 8 characters, with letters and numbers."
                />

                <x-form.password
                    name="password_confirmation" label="Confirm new password" id="new_password_confirmation"
                    rules="required|match:new_password" autocomplete="new-password"
                />
            </div>

            <div class="border-t border-ink-100 pt-5">
                <button type="submit" class="btn btn-primary" data-loading-label="Updating…">
                    <x-ui.icon name="shield-check" class="size-4" /> Update password
                </button>
            </div>
        </form>
    </div>

    {{-- Danger zone --}}
    <div class="panel mt-6 border-rose-200">
        <div class="panel-head !border-rose-100">
            <h2 class="panel-title text-rose-700">Close your account</h2>
        </div>

        <div class="p-6">
            <p class="text-sm leading-relaxed text-ink-600">
                Closing your account removes your profile and sign-in details. Your donation records are kept
                (without your account attached) because the organisation must retain a complete financial history.
                This cannot be undone.
            </p>

            <form method="POST" action="{{ route('profile.destroy') }}" class="mt-5 max-w-sm space-y-4"
                  data-confirm="This permanently closes your account and signs you out. Your donation records are kept for our accounts but will no longer be linked to you."
                  data-confirm-title="Close your account?" data-confirm-action="Close my account">
                @csrf
                @method('DELETE')

                <x-form.password
                    name="password" label="Confirm with your password" id="delete_password"
                    rules="required" autocomplete="current-password"
                />

                <button type="submit" class="btn btn-danger">
                    <x-ui.icon name="trash" class="size-4" /> Close my account
                </button>
            </form>
        </div>
    </div>

@endsection
