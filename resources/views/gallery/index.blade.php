@extends('layouts.app')

@section('title', 'Gallery — '.$site->name())
@section('description', 'Photographs from KUET TRY distributions, medical camps, events and community projects.')

@section('content')

    <x-layout.page-hero
        eyebrow="In pictures"
        title="Gallery"
        description="Moments from distributions, camps and community work. We photograph with consent and never publish a face where the family asked us not to."
        :breadcrumbs="['Gallery' => null]"
    >
        <div class="rounded-2xl border border-white/15 bg-white/10 px-6 py-4 text-center backdrop-blur">
            <p class="display text-3xl text-white">{{ $total }}</p>
            <p class="mt-0.5 text-[11px] font-bold uppercase tracking-wider text-white/55">Photographs</p>
        </div>
    </x-layout.page-hero>

    <section class="section">
        <div class="shell">
            {{-- Category filter --}}
            @if ($categories)
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('gallery.index') }}"
                       class="btn btn-sm {{ blank($filters['category'] ?? null) ? 'btn-primary' : 'btn-outline' }}">
                        All
                        <span class="rounded-full bg-black/10 px-1.5 text-[10px] font-bold">{{ $total }}</span>
                    </a>

                    @foreach ($categories as $key => $label)
                        <a href="{{ route('gallery.index', ['category' => $key]) }}"
                           class="btn btn-sm {{ ($filters['category'] ?? '') === $key ? 'btn-primary' : 'btn-outline' }}">
                            {{ $label }}
                            <span class="rounded-full bg-black/10 px-1.5 text-[10px] font-bold">{{ $counts[$key] ?? 0 }}</span>
                        </a>
                    @endforeach
                </div>
            @endif

            @if ($items->isNotEmpty())
                <p class="mt-6 text-sm text-ink-500">
                    Select any photograph to open it. Use the arrow keys to move between images.
                </p>

                <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($items as $item)
                        <figure class="reveal group">
                            {{-- Only a tile with a real file behind it is clickable: a disabled
                                 button would look interactive and do nothing. --}}
                            @if ($item->image_url)
                                <button
                                    type="button"
                                    data-lightbox
                                    data-lightbox-src="{{ $item->image_url }}"
                                    data-lightbox-title="{{ $item->title }}"
                                    data-lightbox-caption="{{ $item->caption }}"
                                    class="block w-full overflow-hidden rounded-xl text-left"
                                    aria-label="Open photograph: {{ $item->title }}"
                                >
                                    <x-ui.media
                                        :src="$item->image_url"
                                        :alt="$item->title"
                                        :seed="$item->title"
                                        icon="photo"
                                        :label="$item->category_label"
                                        ratio="aspect-square"
                                        rounded="rounded-xl"
                                    >
                                        <span class="pointer-events-none absolute inset-0 bg-gradient-to-t from-ink-950/70 via-transparent to-transparent opacity-0 transition-opacity group-hover:opacity-100"></span>
                                        <span class="pointer-events-none absolute inset-x-0 bottom-0 translate-y-2 p-3 opacity-0 transition-all group-hover:translate-y-0 group-hover:opacity-100">
                                            <span class="line-clamp-2 text-xs font-semibold text-white">{{ $item->title }}</span>
                                        </span>
                                    </x-ui.media>
                                </button>
                            @else
                                <x-ui.media
                                    :src="null"
                                    :alt="$item->title"
                                    :seed="$item->title"
                                    icon="photo"
                                    :label="$item->category_label"
                                    ratio="aspect-square"
                                    rounded="rounded-xl"
                                />
                            @endif

                            <figcaption class="mt-2 px-0.5">
                                <p class="truncate text-xs font-semibold text-ink-800">{{ $item->title }}</p>
                                <p class="mt-0.5 text-[11px] text-ink-500">
                                    {{ $item->category_label }}
                                    @if ($item->taken_on)<span class="divider-dot">{{ $item->taken_on->format('M Y') }}</span>@endif
                                </p>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>

                {{ $items->links() }}
            @else
                <x-ui.empty-state
                    class="mt-8"
                    icon="photo"
                    title="No photographs in this category yet"
                    description="Our team uploads pictures after each distribution and camp. Have a look at the other categories."
                >
                    <a href="{{ route('gallery.index') }}" class="btn btn-primary btn-sm">View the whole gallery</a>
                </x-ui.empty-state>
            @endif
        </div>
    </section>

@endsection
