@extends('layouts.admin')

@section('title', 'Success stories')
@section('heading', 'Success stories')
@section('subheading', 'The families behind the numbers')

@section('content')

    <x-admin.page-header
        title="All stories"
        description="Publish a story only with the family's permission. Unpublished stories are hidden from the public site."
        :count="$stories->total()"
    >
        @if ($trashedCount && ! request()->boolean('trashed'))
            <a href="{{ route('admin.stories.index', ['trashed' => 1]) }}" class="btn btn-outline">
                <x-ui.icon name="trash" class="size-4" /> Archive ({{ $trashedCount }})
            </a>
        @elseif (request()->boolean('trashed'))
            <a href="{{ route('admin.stories.index') }}" class="btn btn-outline">
                <x-ui.icon name="arrow-left" class="size-4" /> Back to list
            </a>
        @endif

        <a href="{{ route('admin.stories.create') }}" class="btn btn-primary">
            <x-ui.icon name="plus" class="size-4" /> New story
        </a>
    </x-admin.page-header>

    <form method="GET" action="{{ route('admin.stories.index') }}" data-filter-form class="panel mb-6 p-4">
        @if (request()->boolean('trashed'))
            <input type="hidden" name="trashed" value="1">
        @endif

        <div class="flex flex-col gap-3 sm:flex-row">
            <div class="relative flex-1">
                <label for="q" class="sr-only">Search stories</label>
                <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-ink-400">
                    <x-ui.icon name="search" class="size-4.5" />
                </span>
                <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                       placeholder="Search by title, beneficiary or location…" class="field pl-10" data-filter-search>
            </div>

            <button type="submit" class="btn btn-primary">Search</button>
            @if (filled($filters['q'] ?? null))
                <button type="button" data-filter-reset="{{ route('admin.stories.index') }}" class="btn btn-ghost">Clear</button>
            @endif
        </div>
    </form>

    <div class="panel overflow-hidden">
        @if ($stories->isNotEmpty())
            <div class="table-wrap hidden lg:block">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Story</th>
                            <th scope="col">Beneficiary</th>
                            <th scope="col">Campaign</th>
                            <th scope="col">Date</th>
                            <th scope="col">Visibility</th>
                            <th scope="col"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stories as $story)
                            <tr>
                                <td class="max-w-[18rem]">
                                    <div class="flex items-center gap-3">
                                        <x-ui.media :src="$story->image_url" :alt="$story->title" :seed="$story->slug"
                                                    icon="heart" ratio="aspect-square" rounded="rounded-lg" class="w-10 shrink-0" />
                                        <div class="min-w-0">
                                            <p class="line-clamp-1 font-semibold text-ink-900">{{ $story->title }}</p>
                                            @if ($story->featured)
                                                <x-ui.badge tone="accent" class="mt-0.5">Featured</x-ui.badge>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="max-w-[10rem]">
                                    <p class="line-clamp-1 text-ink-800">{{ $story->beneficiary_name ?? '—' }}</p>
                                    <p class="truncate text-[11px] text-ink-400">{{ $story->location }}</p>
                                </td>
                                <td class="max-w-[12rem]">
                                    <span class="line-clamp-1 text-ink-600">{{ $story->campaign?->title ?? '—' }}</span>
                                </td>
                                <td class="whitespace-nowrap text-xs text-ink-500">{{ $story->story_date->format('j M Y') }}</td>
                                <td>
                                    @if ($story->is_published)
                                        <x-ui.badge tone="success" icon="eye">Published</x-ui.badge>
                                    @else
                                        <x-ui.badge tone="neutral">Hidden</x-ui.badge>
                                    @endif
                                </td>
                                <td>
                                    @if ($story->trashed())
                                        <form method="POST" action="{{ route('admin.stories.restore', $story->id) }}" class="flex justify-end">
                                            @csrf
                                            <button type="submit" class="btn btn-outline btn-sm">
                                                <x-ui.icon name="refresh" class="size-3.5" /> Restore
                                            </button>
                                        </form>
                                    @else
                                        <x-admin.row-actions
                                            :view="route('stories.show', $story)"
                                            :edit="route('admin.stories.edit', $story)"
                                            :delete="route('admin.stories.destroy', $story)"
                                            delete-title="Archive this story?"
                                            delete-label="Archive"
                                            :delete-confirm="'“'.$story->title.'” will be hidden from the public site. It can be restored later.'"
                                        />
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <ul class="divide-y divide-ink-100 lg:hidden">
                @foreach ($stories as $story)
                    <li class="p-4">
                        <div class="flex items-start gap-3">
                            <x-ui.media :src="$story->image_url" :alt="$story->title" :seed="$story->slug"
                                        icon="heart" ratio="aspect-square" rounded="rounded-lg" class="w-12 shrink-0" />
                            <div class="min-w-0 flex-1">
                                <p class="line-clamp-2 text-sm font-bold text-ink-900">{{ $story->title }}</p>
                                <p class="mt-0.5 truncate text-xs text-ink-500">{{ $story->beneficiary_name }}</p>
                            </div>
                        </div>

                        <div class="mt-3 flex gap-2">
                            @if ($story->trashed())
                                <form method="POST" action="{{ route('admin.stories.restore', $story->id) }}" class="flex-1">
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-sm btn-block">Restore</button>
                                </form>
                            @else
                                <a href="{{ route('admin.stories.edit', $story) }}" class="btn btn-primary btn-sm flex-1">Edit</a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="px-4 pb-4">{{ $stories->links() }}</div>
        @else
            <div class="p-6">
                <x-ui.empty-state icon="heart" title="No stories yet"
                                  description="A single well-told story does more for a campaign than any statistic.">
                    <a href="{{ route('admin.stories.create') }}" class="btn btn-primary btn-sm">
                        <x-ui.icon name="plus" class="size-4" /> New story
                    </a>
                </x-ui.empty-state>
            </div>
        @endif
    </div>

@endsection
