@extends('layouts.admin')

@section('title', 'Announcements')
@section('heading', 'Announcements')
@section('subheading', 'Emergency appeals, campaign updates and organisation notices')

@section('content')

    <x-admin.page-header
        title="All announcements"
        description="Published announcements that have not expired appear on the homepage and in supporter dashboards."
        :count="$announcements->total()"
    >
        <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">
            <x-ui.icon name="plus" class="size-4" /> New announcement
        </a>
    </x-admin.page-header>

    <div class="mb-6 grid gap-4 sm:grid-cols-2">
        <x-ui.stat-card label="Live right now" icon="megaphone" tone="brand" :value="number_format($activeCount)"
                        hint="published, released and not expired" />
        <x-ui.stat-card label="Total" icon="clipboard" tone="neutral" :value="number_format($announcements->total())" />
    </div>

    <form method="GET" action="{{ route('admin.announcements.index') }}" data-filter-form class="panel mb-6 p-4">
        <div class="grid gap-3 sm:grid-cols-[1fr_1fr_auto]">
            <div>
                <label for="priority" class="sr-only">Priority</label>
                <select id="priority" name="priority" class="field">
                    <option value="">All priorities</option>
                    @foreach ($priorities as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['priority'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="sr-only">Status</label>
                <select id="status" name="status" class="field">
                    <option value="">All statuses</option>
                    <option value="published" @selected(($filters['status'] ?? '') === 'published')>Published</option>
                    <option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Draft</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1 sm:flex-none">Filter</button>
                @if (array_filter($filters))
                    <button type="button" data-filter-reset="{{ route('admin.announcements.index') }}" class="btn btn-ghost" aria-label="Clear filters">
                        <x-ui.icon name="x-mark" class="size-4" />
                    </button>
                @endif
            </div>
        </div>
    </form>

    <div class="panel overflow-hidden">
        @if ($announcements->isNotEmpty())
            <ul class="divide-y divide-ink-100">
                @foreach ($announcements as $announcement)
                    <li class="flex flex-col gap-3 p-4 sm:flex-row sm:items-start">
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl {{ $announcement->is_urgent ? 'bg-accent-50 text-accent-600' : 'bg-ink-100 text-ink-500' }}">
                            <x-ui.icon name="megaphone" class="size-5" />
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-bold text-ink-900">{{ $announcement->title }}</p>

                                @php
                                    $tones = ['urgent' => 'danger', 'high' => 'warning', 'normal' => 'neutral', 'low' => 'neutral'];
                                @endphp
                                <x-ui.badge :tone="$tones[$announcement->priority] ?? 'neutral'">{{ $announcement->priority_label }}</x-ui.badge>
                                <x-ui.status-badge :status="$announcement->status" />
                            </div>

                            <p class="mt-1.5 line-clamp-2 text-sm leading-relaxed text-ink-600">{{ $announcement->content }}</p>

                            <p class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-ink-400">
                                @if ($announcement->published_at)
                                    <span>Published {{ $announcement->published_at->format('j M Y') }}</span>
                                @else
                                    <span>Not yet published</span>
                                @endif
                                @if ($announcement->expires_at)
                                    <span class="divider-dot">Expires {{ $announcement->expires_at->format('j M Y') }}</span>
                                @endif
                                @if ($announcement->link_url)
                                    <span class="divider-dot">Links to “{{ $announcement->link_label }}”</span>
                                @endif
                            </p>
                        </div>

                        <x-admin.row-actions
                            class="shrink-0"
                            :edit="route('admin.announcements.edit', $announcement)"
                            :delete="route('admin.announcements.destroy', $announcement)"
                            delete-title="Delete this announcement?"
                            :delete-confirm="'“'.$announcement->title.'” will be permanently deleted.'"
                        />
                    </li>
                @endforeach
            </ul>

            <div class="px-4 pb-4">{{ $announcements->links() }}</div>
        @else
            <div class="p-6">
                <x-ui.empty-state icon="megaphone" title="No announcements yet"
                                  description="Use announcements for emergency appeals, volunteer calls and organisation notices.">
                    <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary btn-sm">
                        <x-ui.icon name="plus" class="size-4" /> New announcement
                    </a>
                </x-ui.empty-state>
            </div>
        @endif
    </div>

@endsection
