@extends('layouts.app')

@section('title', $post->title.' — '.$site->name())
@section('description', Str::limit($post->excerpt, 155))
@section('og:type', 'article')
@if ($post->image_url)
    @section('og:image', $post->image_url)
@endif

@section('content')

    <x-layout.page-hero
        :eyebrow="$post->category_label"
        :title="$post->title"
        :description="$post->excerpt"
        :breadcrumbs="['News' => route('news.index'), Str::limit($post->title, 40) => null]"
    />

    <section class="section">
        <div class="shell">
            <div class="mx-auto max-w-3xl">
                {{-- Byline --}}
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 border-b border-ink-100 pb-5 text-sm text-ink-500">
                    <span class="flex items-center gap-2">
                        <x-ui.avatar :initials="Str::upper(Str::substr($post->author, 0, 1))" size="xs" />
                        <span class="font-semibold text-ink-800">{{ $post->author }}</span>
                    </span>
                    <span class="flex items-center gap-1.5">
                        <x-ui.icon name="calendar" class="size-3.5" />
                        <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('j F Y') }}</time>
                    </span>
                    <span class="flex items-center gap-1.5">
                        <x-ui.icon name="clock" class="size-3.5" /> {{ $post->reading_minutes }} min read
                    </span>
                    @if ($post->status !== \App\Models\Post::STATUS_PUBLISHED)
                        <x-ui.badge tone="warning" icon="eye">Draft preview (admin only)</x-ui.badge>
                    @endif
                </div>

                <x-ui.media
                    class="mt-7"
                    :src="$post->image_url"
                    :alt="$post->title"
                    :seed="$post->slug"
                    icon="newspaper"
                    :label="$post->category_label"
                    ratio="aspect-[16/8]"
                    rounded="rounded-2xl"
                    :eager="true"
                />

                <article class="prose-kt mt-8">{!! $post->content !!}</article>

                <div class="mt-10 flex flex-wrap items-center justify-between gap-3 border-t border-ink-100 pt-6">
                    <a href="{{ route('news.index') }}" class="link-arrow">
                        <x-ui.icon name="arrow-left" class="size-4" /> Back to all articles
                    </a>

                    <div class="flex gap-2">
                        <button type="button" data-copy="{{ route('news.show', $post) }}"
                                data-copy-message="Article link copied."
                                class="btn btn-outline btn-sm">
                            <x-ui.icon name="clipboard" class="size-4" /> Copy link
                        </button>
                        <a href="{{ route('donations.create') }}" class="btn btn-primary btn-sm">
                            <x-ui.icon name="heart" class="size-4" /> Donate
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="section bg-white">
            <div class="shell">
                <x-ui.section-heading eyebrow="Related" title="More in {{ $post->category_label }}" />
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $item)
                        <x-post-card :post="$item" class="reveal" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endsection
