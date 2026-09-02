@extends('layouts.app')

@section('title', 'Our team — '.$site->name())
@section('description', 'The students, alumni, physicians and teachers who run KUET TRY.')

@section('content')

    <x-layout.page-hero
        eyebrow="The people"
        title="Our team"
        description="Nobody here is paid to do this. Between them they run campaigns, verify donations, drive trucks at midnight and write the reports."
        :breadcrumbs="['About' => route('about'), 'Team' => null]"
    />

    <section class="section">
        <div class="shell">
            @if ($members->isNotEmpty())
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($members as $member)
                        <article class="reveal card card-hover overflow-hidden">
                            <div class="flex items-start gap-4 p-5">
                                <x-ui.avatar :src="$member->photo_url" :initials="$member->initials" :name="$member->name" size="lg" />

                                <div class="min-w-0 flex-1">
                                    <h2 class="text-base font-bold leading-snug text-ink-900">{{ $member->name }}</h2>
                                    <p class="mt-0.5 text-xs font-semibold text-brand-700">{{ $member->position }}</p>

                                    @if ($member->email || $member->facebook_url || $member->linkedin_url || $member->twitter_url)
                                        <div class="mt-2.5 flex items-center gap-1.5">
                                            @if ($member->email)
                                                <a href="mailto:{{ $member->email }}" class="grid size-7 place-items-center rounded-lg bg-ink-50 text-ink-500 transition hover:bg-brand-50 hover:text-brand-700" aria-label="Email {{ $member->name }}">
                                                    <x-ui.icon name="mail" class="size-3.5" />
                                                </a>
                                            @endif
                                            @if ($member->facebook_url)
                                                <a href="{{ $member->facebook_url }}" target="_blank" rel="noopener noreferrer" class="grid size-7 place-items-center rounded-lg bg-ink-50 text-ink-500 transition hover:bg-brand-50 hover:text-brand-700" aria-label="{{ $member->name }} on Facebook">
                                                    <x-ui.icon name="facebook" class="size-3.5" />
                                                </a>
                                            @endif
                                            @if ($member->linkedin_url)
                                                <a href="{{ $member->linkedin_url }}" target="_blank" rel="noopener noreferrer" class="grid size-7 place-items-center rounded-lg bg-ink-50 text-ink-500 transition hover:bg-brand-50 hover:text-brand-700" aria-label="{{ $member->name }} on LinkedIn">
                                                    <x-ui.icon name="linkedin" class="size-3.5" />
                                                </a>
                                            @endif
                                            @if ($member->twitter_url)
                                                <a href="{{ $member->twitter_url }}" target="_blank" rel="noopener noreferrer" class="grid size-7 place-items-center rounded-lg bg-ink-50 text-ink-500 transition hover:bg-brand-50 hover:text-brand-700" aria-label="{{ $member->name }} on X">
                                                    <x-ui.icon name="twitter" class="size-3.5" />
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if ($member->biography)
                                <p class="border-t border-ink-100 p-5 text-sm leading-relaxed text-ink-600">{{ $member->biography }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            @else
                <x-ui.empty-state icon="users" title="Team profiles coming soon"
                                  description="Our administrator is adding the team members." />
            @endif

            {{-- Join us --}}
            <div class="mt-14 overflow-hidden rounded-2xl bg-gradient-to-br from-brand-700 to-brand-950">
                <div class="grid items-center gap-6 p-8 sm:p-10 lg:grid-cols-[1fr_auto]">
                    <div>
                        <p class="eyebrow !text-accent-300">
                            <span class="inline-block h-px w-6 bg-accent-400"></span> Join the team
                        </p>
                        <h2 class="display mt-3 text-2xl text-white sm:text-3xl">There is always more to do than hands to do it.</h2>
                        <p class="mt-3 max-w-xl text-sm leading-relaxed text-white/70">
                            You do not need a particular skill. Turning up on time, counting carefully and carrying things
                            is most of the work — the rest we will teach you at the orientation.
                        </p>
                    </div>

                    <a href="{{ route('volunteer.index') }}" class="btn btn-accent btn-lg shrink-0">
                        <x-ui.icon name="users" class="size-5" /> Apply to volunteer
                    </a>
                </div>
            </div>
        </div>
    </section>

@endsection
