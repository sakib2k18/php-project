@extends('layouts.dashboard')

@section('title', 'Notifications — '.$site->name())
@section('heading', 'Notifications')
@section('subheading', $unreadCount ? $unreadCount.' unread' : 'You are all caught up')

@section('actions')
    @if ($unreadCount)
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button type="submit" class="btn btn-outline">
                <x-ui.icon name="check" class="size-4" /> Mark all as read
            </button>
        </form>
    @endif
@endsection

@section('panel')

    <div class="panel overflow-hidden">
        @if ($notifications->isNotEmpty())
            <ul class="divide-y divide-ink-100">
                @foreach ($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $isUnread = $notification->read_at === null;
                        $tones = [
                            'success' => 'bg-emerald-50 text-emerald-600',
                            'warning' => 'bg-amber-50 text-amber-600',
                            'info' => 'bg-sky-50 text-sky-600',
                        ];
                    @endphp

                    <li class="flex items-start gap-3.5 p-4 transition {{ $isUnread ? 'bg-brand-50/50' : '' }}">
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl {{ $tones[$data['tone'] ?? 'info'] ?? $tones['info'] }}">
                            <x-ui.icon :name="$data['icon'] ?? 'info'" class="size-5" />
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-bold text-ink-900">{{ $data['title'] ?? 'Notification' }}</p>
                                @if ($isUnread)
                                    <x-ui.badge tone="brand">New</x-ui.badge>
                                @endif
                            </div>

                            <p class="mt-1 text-sm leading-relaxed text-ink-600">{{ $data['message'] ?? '' }}</p>
                            <p class="mt-1.5 text-xs text-ink-400">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>

                        <div class="flex shrink-0 items-center gap-1">
                            @if (! empty($data['url']))
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-ghost btn-sm" aria-label="Open and mark as read">
                                        <x-ui.icon name="arrow-right" class="size-4" />
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}"
                                  data-confirm="Delete this notification?" data-confirm-title="Delete notification" data-confirm-action="Delete">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm !text-rose-600" aria-label="Delete notification">
                                    <x-ui.icon name="trash" class="size-4" />
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="px-4 pb-4">{{ $notifications->links() }}</div>
        @else
            <div class="p-6">
                <x-ui.empty-state
                    icon="bell"
                    title="No notifications yet"
                    description="We will let you know here when a donation is verified, a volunteer application is reviewed, or an important announcement is published."
                >
                    <a href="{{ route('dashboard') }}" class="btn btn-outline btn-sm">Back to dashboard</a>
                </x-ui.empty-state>
            </div>
        @endif
    </div>

@endsection
