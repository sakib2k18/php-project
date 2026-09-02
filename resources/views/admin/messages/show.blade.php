@extends('layouts.admin')

@section('title', 'Message')
@section('heading', $message->subject)
@section('subheading', 'Received '.$message->created_at->format('j F Y, g:i A'))

@section('content')

    <x-admin.page-header :title="$message->subject" :description="'From '.$message->name">
        <a href="{{ route('admin.messages.index') }}" class="btn btn-outline">
            <x-ui.icon name="arrow-left" class="size-4" /> Back to inbox
        </a>
    </x-admin.page-header>

    <div class="grid gap-6 xl:grid-cols-[1fr_20rem]">

        <div class="panel">
            <div class="panel-head">
                <h3 class="panel-title">Message</h3>
                <x-ui.badge :tone="$message->is_read ? 'neutral' : 'brand'">
                    {{ $message->is_read ? 'Read' : 'Unread' }}
                </x-ui.badge>
            </div>

            <div class="flex flex-wrap items-center gap-4 border-b border-ink-100 p-5">
                <x-ui.avatar :initials="Str::upper(Str::substr($message->name, 0, 1))" size="md" />
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-ink-900">{{ $message->name }}</p>
                    <p class="mt-0.5 truncate text-xs text-ink-600">{{ $message->email }}</p>
                    @if ($message->phone)
                        <p class="text-xs text-ink-600">{{ $message->phone }}</p>
                    @endif
                </div>
            </div>

            <div class="p-6">
                {{-- Escaped output: this is untrusted text from a public form. --}}
                <p class="whitespace-pre-line text-sm leading-relaxed text-ink-700">{{ $message->message }}</p>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="panel p-5">
                <h3 class="panel-title mb-4">Reply</h3>

                <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.$message->subject) }}" class="btn btn-primary btn-block">
                    <x-ui.icon name="mail" class="size-4" /> Reply by email
                </a>

                @if ($message->phone)
                    <a href="tel:{{ preg_replace('/\s+/', '', $message->phone) }}" class="btn btn-outline btn-block mt-2">
                        <x-ui.icon name="phone" class="size-4" /> Call {{ $message->phone }}
                    </a>
                @endif

                <button type="button" data-copy="{{ $message->email }}" data-copy-message="Email address copied."
                        class="btn btn-ghost btn-block mt-2">
                    <x-ui.icon name="clipboard" class="size-4" /> Copy email address
                </button>
            </div>

            <div class="panel p-5">
                <h3 class="panel-title mb-4">Details</h3>

                <dl class="space-y-3.5 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-ink-500">Received</dt>
                        <dd class="text-right font-medium text-ink-800">{{ $message->created_at->format('j M Y') }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-t border-ink-100 pt-3.5">
                        <dt class="text-ink-500">Time</dt>
                        <dd class="text-right font-medium text-ink-800">{{ $message->created_at->format('g:i A') }}</dd>
                    </div>
                    @if ($message->read_at)
                        <div class="flex items-center justify-between gap-3 border-t border-ink-100 pt-3.5">
                            <dt class="text-ink-500">First read</dt>
                            <dd class="text-right font-medium text-ink-800">{{ $message->read_at->format('j M Y') }}</dd>
                        </div>
                    @endif
                    @if ($message->ip_address)
                        <div class="flex items-center justify-between gap-3 border-t border-ink-100 pt-3.5">
                            <dt class="text-ink-500">IP address</dt>
                            <dd class="text-right font-mono text-xs text-ink-600">{{ $message->ip_address }}</dd>
                        </div>
                    @endif
                </dl>

                <form method="POST" action="{{ route('admin.messages.toggle-read', $message) }}" class="mt-5 border-t border-ink-100 pt-4">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm btn-block">
                        <x-ui.icon :name="$message->is_read ? 'inbox' : 'check'" class="size-4" />
                        Mark as {{ $message->is_read ? 'unread' : 'read' }}
                    </button>
                </form>
            </div>

            <div class="panel border-rose-200 p-5">
                <h3 class="panel-title mb-2 text-rose-700">Delete message</h3>
                <p class="mb-4 text-xs leading-relaxed text-ink-500">This cannot be undone.</p>

                <form method="POST" action="{{ route('admin.messages.destroy', $message) }}"
                      data-confirm="This message from {{ $message->name }} will be permanently deleted."
                      data-confirm-title="Delete this message?" data-confirm-action="Delete permanently">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <x-ui.icon name="trash" class="size-4" /> Delete message
                    </button>
                </form>
            </div>
        </aside>
    </div>

@endsection
