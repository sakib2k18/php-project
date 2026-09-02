@extends('layouts.admin')

@section('title', 'News & blog')
@section('heading', 'News / Blog')
@section('subheading', 'Field reports, announcements and organisation news')

@section('content')

    <x-admin.page-header
        title="All articles"
        description="A published article needs a publish date. Drafts are visible only to you."
        :count="$posts->total()"
    >
        @if ($trashedCount && ! request()->boolean('trashed'))
            <a href="{{ route('admin.posts.index', ['trashed' => 1]) }}" class="btn btn-outline">
                <x-ui.icon name="trash" class="size-4" /> Archive ({{ $trashedCount }})
            </a>
        @elseif (request()->boolean('trashed'))
            <a href="{{ route('admin.posts.index') }}" class="btn btn-outline">
                <x-ui.icon name="arrow-left" class="size-4" /> Back to list
            </a>
        @endif

        <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">
            <x-ui.icon name="plus" class="size-4" /> New article
        </a>
    </x-admin.page-header>

    <form method="GET" action="{{ route('admin.posts.index') }}" data-filter-form class="panel mb-6 p-4">
        @if (request()->boolean('trashed'))
            <input type="hidden" name="trashed" value="1">
        @endif

        <div class="grid gap-3 lg:grid-cols-[2fr_1fr_1fr_auto]">
            <div class="relative">
                <label for="q" class="sr-only">Search articles</label>
                <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-ink-400">
                    <x-ui.icon name="search" class="size-4.5" />
                </span>
                <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                       placeholder="Search title or content…" class="field pl-10" data-filter-search>
            </div>

            <div>
                <label for="status" class="sr-only">Status</label>
                <select id="status" name="status" class="field">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="category" class="sr-only">Category</label>
                <select id="category" name="category" class="field">
                    <option value="">All categories</option>
                    @foreach ($categories as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['category'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1 lg:flex-none">Filter</button>
                @if (array_filter(Arr::except($filters, 'trashed')))
                    <button type="button" data-filter-reset="{{ route('admin.posts.index') }}" class="btn btn-ghost" aria-label="Clear filters">
                        <x-ui.icon name="x-mark" class="size-4" />
                    </button>
                @endif
            </div>
        </div>
    </form>

    <div class="panel overflow-hidden">
        @if ($posts->isNotEmpty())
            <div class="table-wrap hidden lg:block">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Article</th>
                            <th scope="col">Category</th>
                            <th scope="col">Author</th>
                            <th scope="col">Status</th>
                            <th scope="col">Published</th>
                            <th scope="col" class="text-right">Views</th>
                            <th scope="col"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($posts as $post)
                            <tr>
                                <td class="max-w-[20rem]">
                                    <div class="flex items-center gap-3">
                                        <x-ui.media :src="$post->image_url" :alt="$post->title" :seed="$post->slug"
                                                    icon="newspaper" ratio="aspect-square" rounded="rounded-lg" class="w-10 shrink-0" />
                                        <div class="min-w-0">
                                            <p class="line-clamp-1 font-semibold text-ink-900">{{ $post->title }}</p>
                                            @if ($post->featured)
                                                <x-ui.badge tone="accent" class="mt-0.5">Featured</x-ui.badge>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap text-ink-600">{{ $post->category_label }}</td>
                                <td class="max-w-[10rem]"><span class="line-clamp-1 text-ink-600">{{ $post->author }}</span></td>
                                <td><x-ui.status-badge :status="$post->status" :label="$post->status_label" /></td>
                                <td class="whitespace-nowrap text-xs text-ink-500">{{ $post->published_at?->format('j M Y') ?? '—' }}</td>
                                <td class="text-right tabular-nums text-ink-600">{{ number_format($post->views) }}</td>
                                <td>
                                    @if ($post->trashed())
                                        <form method="POST" action="{{ route('admin.posts.restore', $post->id) }}" class="flex justify-end">
                                            @csrf
                                            <button type="submit" class="btn btn-outline btn-sm">
                                                <x-ui.icon name="refresh" class="size-3.5" /> Restore
                                            </button>
                                        </form>
                                    @else
                                        <x-admin.row-actions
                                            :view="route('news.show', $post)"
                                            :edit="route('admin.posts.edit', $post)"
                                            :delete="route('admin.posts.destroy', $post)"
                                            delete-title="Archive this article?"
                                            delete-label="Archive"
                                            :delete-confirm="'“'.$post->title.'” will be hidden from the public site. It can be restored later.'"
                                        />
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <ul class="divide-y divide-ink-100 lg:hidden">
                @foreach ($posts as $post)
                    <li class="p-4">
                        <div class="flex items-start gap-3">
                            <x-ui.media :src="$post->image_url" :alt="$post->title" :seed="$post->slug"
                                        icon="newspaper" ratio="aspect-square" rounded="rounded-lg" class="w-12 shrink-0" />
                            <div class="min-w-0 flex-1">
                                <p class="line-clamp-2 text-sm font-bold text-ink-900">{{ $post->title }}</p>
                                <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                    <x-ui.status-badge :status="$post->status" :label="$post->status_label" />
                                    <span class="text-[11px] text-ink-500">{{ $post->category_label }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 flex gap-2">
                            @if ($post->trashed())
                                <form method="POST" action="{{ route('admin.posts.restore', $post->id) }}" class="flex-1">
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-sm btn-block">Restore</button>
                                </form>
                            @else
                                <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-primary btn-sm flex-1">Edit</a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="px-4 pb-4">{{ $posts->links() }}</div>
        @else
            <div class="p-6">
                <x-ui.empty-state icon="newspaper" title="No articles yet"
                                  description="Publish a field report so donors can see where their money went.">
                    <a href="{{ route('admin.posts.create') }}" class="btn btn-primary btn-sm">
                        <x-ui.icon name="plus" class="size-4" /> New article
                    </a>
                </x-ui.empty-state>
            </div>
        @endif
    </div>

@endsection
