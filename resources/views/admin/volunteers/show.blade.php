@extends('layouts.admin')

@section('title', 'Volunteer application')
@section('heading', $volunteer->name)
@section('subheading', 'Volunteer application submitted '.$volunteer->created_at->format('j F Y'))

@section('content')

    <x-admin.page-header :title="$volunteer->name" :description="$volunteer->preferred_activity.' · '.$volunteer->availability_label">
        <a href="{{ route('admin.volunteers.index') }}" class="btn btn-outline">
            <x-ui.icon name="arrow-left" class="size-4" /> All applications
        </a>
    </x-admin.page-header>

    <div class="grid gap-6 xl:grid-cols-[1fr_22rem]">

        <div class="space-y-6">
            <div class="panel">
                <div class="panel-head">
                    <h3 class="panel-title">Applicant details</h3>
                    <x-ui.status-badge :status="$volunteer->status" :label="$volunteer->status_label" />
                </div>

                <dl class="divide-y divide-ink-100">
                    @php
                        $rows = [
                            ['Full name', $volunteer->name],
                            ['Email address', $volunteer->email],
                            ['Phone number', $volunteer->phone],
                            ['Student / organisation ID', $volunteer->student_id ?: '—'],
                            ['Institution', $volunteer->institution ?: '—'],
                            ['Address', $volunteer->address ?: '—'],
                            ['Availability', $volunteer->availability_label],
                            ['Preferred activity', $volunteer->preferred_activity],
                        ];
                    @endphp

                    @foreach ($rows as [$label, $value])
                        <div class="flex flex-col gap-1 p-4 sm:flex-row sm:items-center sm:justify-between sm:gap-6">
                            <dt class="shrink-0 text-sm text-ink-500">{{ $label }}</dt>
                            <dd class="text-sm font-medium text-ink-800 sm:max-w-md sm:text-right">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <div class="panel p-6">
                <h3 class="panel-title mb-3">Skills offered</h3>
                <p class="text-sm leading-relaxed text-ink-700">{{ $volunteer->skills }}</p>
            </div>

            <div class="panel p-6">
                <h3 class="panel-title mb-3">Why they want to volunteer</h3>
                <p class="rounded-xl bg-ink-50 p-4 text-sm leading-relaxed text-ink-700">{{ $volunteer->motivation }}</p>
            </div>

            @if ($volunteer->reviewed_at)
                <div class="panel p-6">
                    <h3 class="panel-title mb-3">Review history</h3>
                    <p class="text-sm text-ink-600">
                        Reviewed by <span class="font-semibold text-ink-900">{{ $volunteer->reviewer?->name ?? 'Administrator' }}</span>
                        on {{ $volunteer->reviewed_at->format('j F Y, g:i A') }}.
                    </p>
                    @if ($volunteer->admin_note)
                        <div class="mt-3 rounded-xl bg-ink-50 p-3.5">
                            <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Note</p>
                            <p class="mt-1 text-sm leading-relaxed text-ink-700">{{ $volunteer->admin_note }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <aside class="space-y-6">
            <div class="panel p-5">
                <h3 class="panel-title mb-1">Decision</h3>
                <p class="mb-5 text-xs leading-relaxed text-ink-500">
                    Approving or rejecting sends the applicant a notification in their dashboard.
                </p>

                <form method="POST" action="{{ route('admin.volunteers.review', $volunteer) }}" class="space-y-4">
                    @csrf

                    <x-form.textarea
                        name="admin_note" label="Note to the applicant (optional)"
                        :value="$volunteer->admin_note"
                        placeholder="e.g. Welcome — the next orientation is on the 14th."
                        :rows="3" :maxlength="500"
                    />

                    <div class="space-y-2">
                        @if ($volunteer->status !== 'approved')
                            <button type="submit" name="decision" value="approve" class="btn btn-primary btn-block">
                                <x-ui.icon name="check-badge" class="size-4" /> Approve application
                            </button>
                        @endif

                        @if ($volunteer->status !== 'rejected')
                            <button type="submit" name="decision" value="reject" class="btn btn-outline btn-block !text-rose-600 hover:!border-rose-300 hover:!bg-rose-50">
                                <x-ui.icon name="x-circle" class="size-4" /> Reject application
                            </button>
                        @endif

                        @if ($volunteer->status !== 'pending')
                            <button type="submit" name="decision" value="pending" class="btn btn-ghost btn-block">
                                <x-ui.icon name="refresh" class="size-4" /> Move back to pending
                            </button>
                        @endif
                    </div>
                </form>
            </div>

            @if ($volunteer->user)
                <div class="panel p-5">
                    <h3 class="panel-title mb-4">Linked account</h3>
                    <div class="flex items-center gap-3">
                        <x-ui.avatar :src="$volunteer->user->avatar_url" :initials="$volunteer->user->initials" :name="$volunteer->user->name" size="sm" />
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-ink-900">{{ $volunteer->user->name }}</p>
                            <p class="truncate text-xs text-ink-500">{{ $volunteer->user->email }}</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.users.show', $volunteer->user) }}" class="btn btn-outline btn-sm btn-block mt-4">
                        <x-ui.icon name="user-circle" class="size-4" /> View account
                    </a>
                </div>
            @endif

            <div class="panel border-rose-200 p-5">
                <h3 class="panel-title mb-2 text-rose-700">Delete application</h3>
                <p class="mb-4 text-xs leading-relaxed text-ink-500">
                    Rejecting keeps the record for your files. Deleting removes it permanently.
                </p>

                <form method="POST" action="{{ route('admin.volunteers.destroy', $volunteer) }}"
                      data-confirm="The volunteer application from {{ $volunteer->name }} will be permanently deleted."
                      data-confirm-title="Delete this application?" data-confirm-action="Delete permanently">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <x-ui.icon name="trash" class="size-4" /> Delete application
                    </button>
                </form>
            </div>
        </aside>
    </div>

@endsection
