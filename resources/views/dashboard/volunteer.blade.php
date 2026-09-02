@extends('layouts.dashboard')

@section('title', 'Volunteer — '.$site->name())
@section('heading', 'Volunteer with us')
@section('subheading', $volunteer ? 'Your application and its status' : 'Tell us how you would like to help')

@section('panel')

    <x-form.errors class="mb-6" />

    @if ($volunteer)
        {{-- ===== Existing application ===== --}}
        @php
            $banners = [
                'pending' => ['border-amber-200 bg-amber-50 text-amber-900', 'clock', 'Under review',
                    'Our volunteer coordinator is reviewing your application. You can still correct your details below while it is pending.'],
                'approved' => ['border-emerald-200 bg-emerald-50 text-emerald-900', 'check-badge', 'Welcome to the team',
                    'Your application has been approved. The coordinator will contact you about the next orientation and the teams that need people.'],
                'rejected' => ['border-rose-200 bg-rose-50 text-rose-900', 'x-circle', 'Not approved this time',
                    'We could not place you this term. This is almost always about availability rather than the person — you are very welcome to apply again next season.'],
            ];

            [$tone, $icon, $title, $body] = $banners[$volunteer->status] ?? $banners['pending'];
        @endphp

        <div class="rounded-2xl border p-5 {{ $tone }}">
            <div class="flex gap-3.5">
                <x-ui.icon :name="$icon" class="mt-0.5 size-6 shrink-0" />
                <div class="min-w-0 flex-1">
                    <h2 class="text-base font-bold">{{ $title }}</h2>
                    <p class="mt-1.5 text-sm leading-relaxed opacity-90">{{ $body }}</p>

                    @if ($volunteer->admin_note)
                        <div class="mt-3 rounded-xl bg-white/60 p-3">
                            <p class="text-xs font-bold uppercase tracking-wider opacity-70">Note from the coordinator</p>
                            <p class="mt-1 text-sm leading-relaxed">{{ $volunteer->admin_note }}</p>
                        </div>
                    @endif

                    <p class="mt-3 text-xs opacity-70">
                        Applied {{ $volunteer->created_at->format('j F Y') }}
                        @if ($volunteer->reviewed_at)
                            · Reviewed {{ $volunteer->reviewed_at->format('j F Y') }}
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="panel mt-6">
            <div class="panel-head">
                <h2 class="panel-title">Your application</h2>
                @if ($volunteer->status === 'pending')
                    <x-ui.badge tone="warning" icon="pencil">Editable while pending</x-ui.badge>
                @endif
            </div>

            @if ($volunteer->status === 'pending')
                <form method="POST" action="{{ route('volunteer.update', $volunteer) }}" data-validate class="space-y-5 p-6">
                    @csrf
                    @method('PUT')
                    @include('dashboard.partials.volunteer-fields', ['volunteer' => $volunteer, 'availability' => $availability, 'activities' => $activities])

                    <div class="flex flex-wrap gap-3 border-t border-ink-100 pt-5">
                        <button type="submit" class="btn btn-primary" data-loading-label="Saving…">
                            <x-ui.icon name="check" class="size-4" /> Update my application
                        </button>
                    </div>
                </form>
            @else
                {{-- Read-only view once a decision has been made --}}
                <dl class="divide-y divide-ink-100">
                    @php
                        $rows = [
                            ['Name', $volunteer->name],
                            ['Email', $volunteer->email],
                            ['Phone', $volunteer->phone],
                            ['Student / organisation ID', $volunteer->student_id ?: '—'],
                            ['Institution', $volunteer->institution ?: '—'],
                            ['Address', $volunteer->address ?: '—'],
                            ['Availability', $volunteer->availability_label],
                            ['Preferred activity', $volunteer->preferred_activity],
                            ['Skills', $volunteer->skills],
                        ];
                    @endphp

                    @foreach ($rows as [$label, $value])
                        <div class="flex flex-col gap-1 p-4 sm:flex-row sm:justify-between sm:gap-6">
                            <dt class="shrink-0 text-sm text-ink-500">{{ $label }}</dt>
                            <dd class="text-sm font-medium text-ink-800 sm:max-w-md sm:text-right">{{ $value }}</dd>
                        </div>
                    @endforeach

                    <div class="p-4">
                        <dt class="text-sm text-ink-500">Motivation</dt>
                        <dd class="mt-2 rounded-xl bg-ink-50 p-3.5 text-sm leading-relaxed text-ink-700">{{ $volunteer->motivation }}</dd>
                    </div>
                </dl>
            @endif
        </div>

    @else
        {{-- ===== New application ===== --}}
        <div class="grid gap-6 lg:grid-cols-[1fr_18rem]">
            <div class="panel">
                <div class="panel-head">
                    <h2 class="panel-title">Volunteer application</h2>
                </div>

                <form method="POST" action="{{ route('volunteer.store') }}" data-validate class="space-y-5 p-6">
                    @csrf
                    @include('dashboard.partials.volunteer-fields', ['volunteer' => null, 'availability' => $availability, 'activities' => $activities])

                    <div class="rounded-xl bg-ink-50 p-4">
                        <p class="flex gap-2.5 text-xs leading-relaxed text-ink-600">
                            <x-ui.icon name="info" class="size-4 shrink-0 text-ink-400" />
                            <span>
                                Everyone joining a field team attends one half-day orientation and safety briefing
                                before their first distribution. We will tell you the next date once your application
                                is reviewed.
                            </span>
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3 border-t border-ink-100 pt-5">
                        <button type="submit" class="btn btn-primary btn-lg" data-loading-label="Submitting…">
                            <x-ui.icon name="users" class="size-5" /> Submit my application
                        </button>
                    </div>
                </form>
            </div>

            <aside class="space-y-6">
                <div class="panel p-5">
                    <h2 class="panel-title mb-3">Right now</h2>
                    <p class="display text-3xl text-brand-700">{{ number_format($approvedCount) }}</p>
                    <p class="mt-1 text-xs text-ink-500">approved volunteers on the team</p>
                </div>

                <div class="panel p-5">
                    <h2 class="panel-title mb-3">What we need most</h2>
                    <ul class="space-y-2.5 text-sm text-ink-600">
                        @foreach (['People who can lift and count carefully', 'Drivers with a licence', 'Teachers for evening coaching', 'Medical students for camps', 'Anyone free on weekends'] as $need)
                            <li class="flex gap-2">
                                <x-ui.icon name="check" class="mt-0.5 size-4 shrink-0 text-brand-600" />
                                <span>{{ $need }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="rounded-2xl bg-gradient-to-br from-brand-700 to-brand-950 p-5 text-white">
                    <x-ui.icon name="sparkles" class="size-6 text-accent-300" />
                    <p class="mt-3 text-sm font-bold">No experience needed</p>
                    <p class="mt-1.5 text-xs leading-relaxed text-white/70">
                        Turning up on time, counting carefully and carrying things is most of the work.
                        We will teach you the rest.
                    </p>
                </div>
            </aside>
        </div>
    @endif

@endsection
