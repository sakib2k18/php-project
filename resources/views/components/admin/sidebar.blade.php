@props(['stats' => []])

@php
    // Grouped exactly as the admin panel is meant to be read: what you publish,
    // what you verify, who is involved, who is writing in, and settings.
    $sections = [
        [
            'label' => null,
            'items' => [
                ['label' => 'Dashboard', 'icon' => 'home', 'route' => 'admin.dashboard', 'patterns' => ['admin.dashboard']],
            ],
        ],
        [
            'label' => 'Content',
            'items' => [
                ['label' => 'Campaigns',       'icon' => 'heart',     'route' => 'admin.campaigns.index',     'patterns' => ['admin.campaigns.*']],
                ['label' => 'Projects',        'icon' => 'briefcase', 'route' => 'admin.projects.index',      'patterns' => ['admin.projects.*']],
                ['label' => 'Events',          'icon' => 'calendar',  'route' => 'admin.events.index',        'patterns' => ['admin.events.*']],
                ['label' => 'Success stories', 'icon' => 'sparkles',  'route' => 'admin.stories.index',       'patterns' => ['admin.stories.*']],
                ['label' => 'News / Blog',     'icon' => 'newspaper', 'route' => 'admin.posts.index',         'patterns' => ['admin.posts.*']],
                ['label' => 'Announcements',   'icon' => 'megaphone', 'route' => 'admin.announcements.index', 'patterns' => ['admin.announcements.*']],
                ['label' => 'Gallery',         'icon' => 'photo',     'route' => 'admin.gallery.index',       'patterns' => ['admin.gallery.*']],
                ['label' => 'Team members',    'icon' => 'users',     'route' => 'admin.team.index',          'patterns' => ['admin.team.*']],
            ],
        ],
        [
            'label' => 'Donations',
            'items' => [
                ['label' => 'Donations',        'icon' => 'banknotes', 'route' => 'admin.donations.index',   'patterns' => ['admin.donations.index', 'admin.donations.show'], 'badge' => 'donations_pending'],
                ['label' => 'Donation reports', 'icon' => 'chart',     'route' => 'admin.donations.reports', 'patterns' => ['admin.donations.reports']],
            ],
        ],
        [
            'label' => 'People',
            'items' => [
                ['label' => 'Users',      'icon' => 'user-circle', 'route' => 'admin.users.index',      'patterns' => ['admin.users.*']],
                ['label' => 'Volunteers', 'icon' => 'hand-heart',  'route' => 'admin.volunteers.index', 'patterns' => ['admin.volunteers.*'], 'badge' => 'volunteers_pending'],
            ],
        ],
        [
            'label' => 'Communication',
            'items' => [
                ['label' => 'Contact messages', 'icon' => 'inbox',    'route' => 'admin.messages.index',  'patterns' => ['admin.messages.*'], 'badge' => 'messages_unread'],
                ['label' => 'Activity log',     'icon' => 'clipboard','route' => 'admin.activity.index',  'patterns' => ['admin.activity.*']],
            ],
        ],
        [
            'label' => 'Settings',
            'items' => [
                ['label' => 'Organisation settings', 'icon' => 'settings',    'route' => 'admin.settings.edit', 'patterns' => ['admin.settings.*']],
                ['label' => 'Admin profile',         'icon' => 'user',        'route' => 'admin.profile.edit',  'patterns' => ['admin.profile.*']],
                ['label' => 'Change password',       'icon' => 'shield-check','route' => 'admin.password.edit', 'patterns' => ['admin.password.*']],
            ],
        ],
    ];
@endphp

<div class="flex h-full flex-col bg-ink-950">
    {{-- Brand --}}
    <div class="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-white/10 px-4">
        <a href="{{ route('admin.dashboard') }}" class="flex min-w-0 items-center gap-2.5">
            @if ($site->imageUrl('logo'))
                <img src="{{ $site->imageUrl('logo') }}" alt="" class="size-9 shrink-0 rounded-xl object-cover">
            @else
                <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-brand-600 text-white">
                    <x-ui.icon name="hand-heart" class="size-5" />
                </span>
            @endif
            <span class="min-w-0">
                <span class="display block truncate text-base leading-none text-white">{{ $site->name() }}</span>
                <span class="mt-0.5 block text-[9px] font-bold uppercase tracking-[0.14em] text-brand-400">Administration</span>
            </span>
        </a>

        <button type="button" data-panel-close="admin-sidebar"
                class="rounded-lg p-1.5 text-ink-400 transition hover:bg-white/10 hover:text-white lg:hidden"
                aria-label="Close menu">
            <x-ui.icon name="x-mark" class="size-5" />
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 space-y-5 overflow-y-auto p-3" aria-label="Admin">
        @foreach ($sections as $section)
            <div>
                @if ($section['label'])
                    <p class="side-heading">{{ $section['label'] }}</p>
                @endif

                <ul class="space-y-0.5">
                    @foreach ($section['items'] as $item)
                        @php
                            $count = isset($item['badge']) ? (int) ($stats[$item['badge']] ?? 0) : 0;
                        @endphp

                        <li>
                            <a href="{{ route($item['route']) }}"
                               class="side-link {{ request()->routeIs(...$item['patterns']) ? 'is-active' : '' }}"
                               @if (request()->routeIs(...$item['patterns'])) aria-current="page" @endif>
                                <x-ui.icon :name="$item['icon']" class="size-4 shrink-0" />
                                <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>

                                @if ($count > 0)
                                    <span class="grid min-w-5 shrink-0 place-items-center rounded-full bg-accent-500 px-1.5 text-[10px] font-bold text-white">
                                        {{ $count > 99 ? '99+' : $count }}
                                    </span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>

    {{-- Account --}}
    <div class="shrink-0 border-t border-white/10 p-3">
        <a href="{{ route('home') }}" class="side-link mb-1">
            <x-ui.icon name="globe" class="size-4" />
            <span class="flex-1">View public site</span>
            <x-ui.icon name="arrow-right" class="size-3.5 opacity-50" />
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="side-link w-full !text-rose-300 hover:!bg-rose-500/10 hover:!text-rose-200">
                <x-ui.icon name="logout" class="size-4" />
                <span class="flex-1 text-left">Sign out</span>
            </button>
        </form>
    </div>
</div>
