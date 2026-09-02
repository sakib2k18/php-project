@extends('layouts.admin')

@section('title', 'Admin profile')
@section('heading', 'Admin profile')
@section('subheading', 'Your administrator account')

@section('content')

    <x-admin.page-header title="Administrator profile" description="Your own details. The role of this account cannot be changed from anywhere in the application.">
        <a href="{{ route('admin.password.edit') }}" class="btn btn-outline">
            <x-ui.icon name="shield-check" class="size-4" /> Change password
        </a>
    </x-admin.page-header>

    <x-form.errors class="mb-6" />

    <div class="grid gap-6 xl:grid-cols-[1fr_20rem]">

        <div class="panel">
            <div class="panel-head">
                <h3 class="panel-title">Profile details</h3>
                <x-ui.badge tone="accent" icon="shield-check">Administrator</x-ui.badge>
            </div>

            <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" data-validate class="space-y-5 p-6">
                @csrf
                @method('PATCH')

                <x-form.image name="avatar" label="Profile photo" :current="$user->avatar_url" aspect="aspect-square" />

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
                        help="This is the address you sign in with."
                    />
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form.field
                        name="phone" label="Phone number" type="tel"
                        :value="$user->phone" icon="phone" rules="phone" autocomplete="tel"
                    />

                    <x-form.field
                        name="student_id" label="Staff / member ID"
                        :value="$user->student_id"
                    />
                </div>

                <x-form.field
                    name="address" label="Address"
                    :value="$user->address" icon="map-pin" rules="max:255"
                />

                <x-form.textarea
                    name="bio" label="About you"
                    :value="$user->bio" :rows="3" :maxlength="1000"
                />

                <div class="flex flex-wrap gap-3 border-t border-ink-100 pt-5">
                    <button type="submit" class="btn btn-primary" data-loading-label="Saving…">
                        <x-ui.icon name="check" class="size-4" /> Save changes
                    </button>
                </div>
            </form>
        </div>

        <aside class="space-y-6">
            <div class="panel p-5 text-center">
                <x-ui.avatar :src="$user->avatar_url" :initials="$user->initials" :name="$user->name" size="xl" class="mx-auto" />
                <p class="mt-4 text-base font-bold text-ink-900">{{ $user->name }}</p>
                <p class="mt-0.5 text-xs text-ink-500">{{ $user->email }}</p>
                <x-ui.badge tone="accent" icon="shield-check" class="mt-3">Administrator</x-ui.badge>

                <dl class="mt-5 space-y-2.5 border-t border-ink-100 pt-4 text-left text-xs">
                    <div class="flex justify-between gap-3">
                        <dt class="text-ink-500">Account created</dt>
                        <dd class="font-semibold text-ink-800">{{ $user->created_at->format('j M Y') }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-ink-500">Last signed in</dt>
                        <dd class="font-semibold text-ink-800">{{ $user->last_login_at?->format('j M Y') ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <h3 class="panel-title">Your recent actions</h3>
                    <a href="{{ route('admin.activity.index') }}" class="link-arrow !text-xs">
                        All <x-ui.icon name="arrow-right" class="size-3.5" />
                    </a>
                </div>

                @if ($recentActivity->isNotEmpty())
                    <ol class="p-5">
                        @foreach ($recentActivity as $activity)
                            @php
                                $dots = ['brand' => 'bg-brand-500', 'accent' => 'bg-accent-500', 'rose' => 'bg-rose-500', 'ink' => 'bg-ink-300'];
                            @endphp

                            <li class="relative flex gap-3 pb-4 last:pb-0">
                                @unless ($loop->last)
                                    <span class="absolute left-[5px] top-3 h-full w-px bg-ink-100" aria-hidden="true"></span>
                                @endunless

                                <span class="relative mt-1.5 size-2.5 shrink-0 rounded-full {{ $dots[$activity->tone] ?? $dots['ink'] }}"></span>

                                <div class="min-w-0 flex-1">
                                    <p class="text-xs leading-relaxed text-ink-700">{{ $activity->description }}</p>
                                    <p class="mt-0.5 text-[11px] text-ink-400">{{ $activity->created_at->diffForHumans() }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @else
                    <p class="p-6 text-center text-sm text-ink-500">No actions recorded yet.</p>
                @endif
            </div>
        </aside>
    </div>

@endsection
