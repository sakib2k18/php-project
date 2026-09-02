@extends('layouts.admin')

@section('title', 'Contact messages')
@section('heading', 'Contact messages')
@section('subheading', 'Messages sent through the public contact form')

@section('content')

    <x-admin.page-header
        title="Inbox"
        description="Messages are stored in the database and never shown publicly. Opening one marks it as read."
        :count="$counts['total']"
    />

    <div class="mb-6 grid gap-4 sm:grid-cols-2">
        <x-ui.stat-card label="Unread" icon="inbox" :tone="$counts['unread'] ? 'danger' : 'neutral'"
                        :value="number_format($counts['unread'])"
                        :href="route('admin.messages.index', ['state' => 'unread'])" />
        <x-ui.stat-card label="Total received" icon="mail" tone="neutral" :value="number_format($counts['total'])" />
    </div>

    <form method="GET" action="{{ route('admin.messages.index') }}" data-filter-form class="panel mb-6 p-4">
        <div class="grid gap-3 sm:grid-cols-[2fr_1fr_auto]">
            <div class="relative">
                <label for="q" class="sr-only">Search messages</label>
                <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-ink-400">
                    <x-ui.icon name="search" class="size-4.5" />
                </span>
                <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                       placeholder="Search name, email or subject…" class="field pl-10" data-filter-search>
            </div>

            <div>
                <label for="state" class="sr-only">Read state</label>
                <select id="state" name="state" class="field">
                    <option value="">All messages</option>
                    <option value="unread" @selected(($filters['state'] ?? '') === 'unread')>Unread only</option>
                    <option value="read" @selected(($filters['state'] ?? '') === 'read')>Read only</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1 sm:flex-none">Filter</button>
                @if (array_filter($filters))
                    <button type="button" data-filter-reset="{{ route('admin.messages.index') }}" class="btn btn-ghost" aria-label="Clear filters">
                        <x-ui.icon name="x-mark" class="size-4" />
                    </button>
                @endif
            </div>
        </div>
    </form>

    <div class="panel overflow-hidden">
        @if ($messages->isNotEmpty())
            <ul class="divide-y divide-ink-100">
                @foreach ($messages as $message)
                    <li class="flex items-start gap-3.5 p-4 transition {{ $message->is_read ? '' : 'bg-brand-50/50' }}">
                        <x-ui.avatar :initials="Str::upper(Str::substr($message->name, 0, 1))" size="sm" />

                        <a href="{{ route('admin.messages.show', $message) }}" class="min-w-0 flex-1 group">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-bold text-ink-900 transition group-hover:text-brand-700">{{ $message->subject }}</p>
                                @unless ($message->is_read)
                                    <x-ui.badge tone="brand">Unread</x-ui.badge>
                                @endunless
                            </div>

                            <p class="mt-0.5 truncate text-xs text-ink-500">
                                {{ $message->name }} · {{ $message->email }}
                                @if ($message->phone)<span class="divider-dot">{{ $message->phone }}</span>@endif
                            </p>

                            <p class="mt-1.5 line-clamp-2 text-xs leading-relaxed text-ink-600">{{ $message->message }}</p>
                            <p class="mt-1.5 text-[11px] text-ink-400">{{ $message->created_at->format('j M Y, g:i A') }}</p>
                        </a>

                        <div class="flex shrink-0 items-center gap-1">
                            <form method="POST" action="{{ route('admin.messages.toggle-read', $message) }}">
                                @csrf
                                <button type="submit" class="grid size-8 place-items-center rounded-lg text-ink-500 transition hover:bg-brand-50 hover:text-brand-700"
                                        aria-label="{{ $message->is_read ? 'Mark as unread' : 'Mark as read' }}">
                                    <x-ui.icon :name="$message->is_read ? 'inbox' : 'check'" class="size-4" />
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.messages.destroy', $message) }}"
                                  data-confirm="This message from {{ $message->name }} will be permanently deleted."
                                  data-confirm-title="Delete this message?" data-confirm-action="Delete">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="grid size-8 place-items-center rounded-lg text-ink-500 transition hover:bg-rose-50 hover:text-rose-600" aria-label="Delete message">
                                    <x-ui.icon name="trash" class="size-4" />
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="px-4 pb-4">{{ $messages->links() }}</div>
        @else
            <div class="p-6">
                <x-ui.empty-state
                    icon="inbox"
                    :title="array_filter($filters) ? 'No messages match your filters' : 'Inbox empty'"
                    description="Messages sent through the public contact form arrive here."
                >
                    @if (array_filter($filters))
                        <a href="{{ route('admin.messages.index') }}" class="btn btn-outline btn-sm">Clear filters</a>
                    @endif
                </x-ui.empty-state>
            </div>
        @endif
    </div>

@endsection
