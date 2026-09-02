@props(['post', 'horizontal' => false])

@if ($horizontal)
    <article {{ $attributes->merge(['class' => 'card card-hover group grid gap-5 overflow-hidden p-5 sm:grid-cols-[minmax(0,15rem)_1fr] sm:p-6']) }}>
        <a href="{{ route('news.show', $post) }}" tabindex="-1" aria-hidden="true">
            <x-ui.media :src="$post->image_url" :alt="$post->title" :seed="$post->slug" icon="newspaper"
                        :label="$post->category_label" ratio="aspect-[16/10]" />
        </a>

        <div class="flex flex-col justify-center">
            <p class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-ink-500">
                <x-ui.badge tone="brand">{{ $post->category_label }}</x-ui.badge>
                <span>{{ $post->published_at?->format('j F Y') }}</span>
                <span class="divider-dot">{{ $post->reading_minutes }} min read</span>
            </p>

            <h3 class="display mt-2.5 text-xl leading-tight text-ink-900 sm:text-2xl">
                <a href="{{ route('news.show', $post) }}" class="transition hover:text-brand-700">{{ $post->title }}</a>
            </h3>

            <p class="mt-2.5 line-clamp-3 text-sm leading-relaxed text-ink-600">{{ $post->excerpt }}</p>

            <a href="{{ route('news.show', $post) }}" class="link-arrow mt-4">
                Read the article <x-ui.icon name="arrow-right" class="size-4" />
            </a>
        </div>
    </article>
@else
    <article {{ $attributes->merge(['class' => 'card card-hover group flex flex-col overflow-hidden']) }}>
        <a href="{{ route('news.show', $post) }}" tabindex="-1" aria-hidden="true">
            <x-ui.media :src="$post->image_url" :alt="$post->title" :seed="$post->slug" icon="newspaper"
                        :label="$post->category_label" ratio="aspect-[16/10]" rounded="rounded-none" />
        </a>

        <div class="flex flex-1 flex-col p-5">
            <p class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-ink-500">
                <x-ui.badge tone="brand">{{ $post->category_label }}</x-ui.badge>
                <span>{{ $post->published_at?->format('j M Y') }}</span>
            </p>

            <h3 class="mt-2.5 text-base font-bold leading-snug text-ink-900">
                <a href="{{ route('news.show', $post) }}" class="transition hover:text-brand-700">{{ $post->title }}</a>
            </h3>

            <p class="mt-2 line-clamp-2 flex-1 text-sm leading-relaxed text-ink-600">{{ $post->excerpt }}</p>

            <p class="mt-4 flex items-center justify-between gap-2 border-t border-ink-100 pt-3.5 text-xs text-ink-500">
                <span class="truncate">{{ $post->author }}</span>
                <span class="shrink-0">{{ $post->reading_minutes }} min read</span>
            </p>
        </div>
    </article>
@endif
