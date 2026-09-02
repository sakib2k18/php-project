@extends('layouts.admin')

@section('title', 'Gallery')
@section('heading', 'Gallery')
@section('subheading', 'Photographs from distributions, camps and events')

@section('content')

    <x-admin.page-header
        title="Gallery images"
        description="Uploads are validated on MIME type, extension, byte size and pixel dimensions, and stored under a random filename."
        :count="$total"
    >
        <a href="{{ route('gallery.index') }}" target="_blank" rel="noopener" class="btn btn-outline">
            <x-ui.icon name="eye" class="size-4" /> Public gallery
        </a>
        <a href="{{ route('admin.gallery.create') }}" class="btn btn-primary">
            <x-ui.icon name="plus" class="size-4" /> Add image
        </a>
    </x-admin.page-header>

    <form method="GET" action="{{ route('admin.gallery.index') }}" data-filter-form class="panel mb-6 p-4">
        <div class="grid gap-3 sm:grid-cols-[2fr_1fr_auto]">
            <div class="relative">
                <label for="q" class="sr-only">Search gallery</label>
                <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-ink-400">
                    <x-ui.icon name="search" class="size-4.5" />
                </span>
                <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                       placeholder="Search by title…" class="field pl-10" data-filter-search>
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
                <button type="submit" class="btn btn-primary flex-1 sm:flex-none">Filter</button>
                @if (array_filter($filters))
                    <button type="button" data-filter-reset="{{ route('admin.gallery.index') }}" class="btn btn-ghost" aria-label="Clear filters">
                        <x-ui.icon name="x-mark" class="size-4" />
                    </button>
                @endif
            </div>
        </div>
    </form>

    @if ($items->isNotEmpty())
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($items as $item)
                <article class="card overflow-hidden">
                    <x-ui.media
                        :src="$item->image_url"
                        :alt="$item->title"
                        :seed="$item->title"
                        icon="photo"
                        :label="$item->category_label"
                        ratio="aspect-[4/3]"
                        rounded="rounded-none"
                    >
                        <span class="absolute right-2 top-2">
                            @if ($item->is_published)
                                <x-ui.badge tone="success" class="bg-white/95 backdrop-blur">Published</x-ui.badge>
                            @else
                                <x-ui.badge tone="neutral" class="bg-white/95 backdrop-blur">Hidden</x-ui.badge>
                            @endif
                        </span>
                    </x-ui.media>

                    <div class="p-4">
                        <p class="line-clamp-1 text-sm font-bold text-ink-900">{{ $item->title }}</p>
                        <p class="mt-0.5 text-[11px] text-ink-500">
                            {{ $item->category_label }}
                            @if ($item->taken_on)<span class="divider-dot">{{ $item->taken_on->format('M Y') }}</span>@endif
                        </p>

                        @if ($item->caption)
                            <p class="mt-2 line-clamp-2 text-xs leading-relaxed text-ink-600">{{ $item->caption }}</p>
                        @endif

                        <div class="mt-3 flex items-center justify-between gap-2 border-t border-ink-100 pt-3">
                            <span class="text-[11px] text-ink-400">Order {{ $item->sort_order }}</span>

                            <x-admin.row-actions
                                :edit="route('admin.gallery.edit', $item)"
                                :delete="route('admin.gallery.destroy', $item)"
                                delete-title="Delete this image?"
                                :delete-confirm="'“'.$item->title.'” and its uploaded file will be permanently deleted.'"
                            />
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {{ $items->links() }}
    @else
        <div class="panel p-6">
            <x-ui.empty-state icon="photo" title="No images yet"
                              description="Photographs make the campaigns real. Upload from your last distribution or camp.">
                <a href="{{ route('admin.gallery.create') }}" class="btn btn-primary btn-sm">
                    <x-ui.icon name="plus" class="size-4" /> Add the first image
                </a>
            </x-ui.empty-state>
        </div>
    @endif

@endsection
