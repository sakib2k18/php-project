@props(['story'])

<article {{ $attributes->merge(['class' => 'card card-hover group flex flex-col overflow-hidden']) }}>
    <a href="{{ route('stories.show', $story) }}" tabindex="-1" aria-hidden="true">
        <x-ui.media
            :src="$story->image_url"
            :alt="$story->title"
            :seed="$story->slug"
            icon="heart"
            label="Impact story"
            ratio="aspect-[16/10]"
            rounded="rounded-none"
        />
    </a>

    <div class="flex flex-1 flex-col p-5">
        <p class="flex flex-wrap items-center gap-x-2 text-xs text-ink-500">
            <span class="font-semibold text-brand-700">{{ $story->story_date->format('F Y') }}</span>
            @if ($story->location)
                <span class="divider-dot">{{ $story->location }}</span>
            @endif
        </p>

        <h3 class="mt-2 text-base font-bold leading-snug text-ink-900">
            <a href="{{ route('stories.show', $story) }}" class="transition hover:text-brand-700">{{ $story->title }}</a>
        </h3>

        <p class="mt-2 line-clamp-3 flex-1 text-sm leading-relaxed text-ink-600">
            {{ Str::limit(strip_tags($story->story), 160) }}
        </p>

        @if ($story->beneficiary_name)
            <div class="mt-4 flex items-center gap-2 border-t border-ink-100 pt-4">
                <x-ui.avatar :initials="Str::upper(Str::substr($story->beneficiary_name, 0, 1))" size="xs" />
                <p class="min-w-0 flex-1 truncate text-xs">
                    <span class="font-bold text-ink-800">{{ $story->beneficiary_name }}</span>
                    @if ($story->beneficiary_description)
                        <span class="text-ink-500"> · {{ Str::limit($story->beneficiary_description, 40) }}</span>
                    @endif
                </p>
            </div>
        @endif
    </div>
</article>
