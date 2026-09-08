@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'breadcrumbs' => [],
    'tone' => 'brand',
    'image' => null,
])

<section {{ $attributes->merge(['class' => 'relative overflow-hidden '.($tone === 'light' ? 'border-b border-ink-100 bg-white' : 'bg-brand-950')]) }}>
    @if ($tone !== 'light')
        @php
            // Every dark hero carries the cover photo by default; a page can
            // override it with its own image or pass false to opt out.
            $heroImage = $image === false ? null : ($image ?: asset('images/cover.jpg'));
        @endphp
        <div class="absolute inset-0" aria-hidden="true">
            @if ($heroImage)
                <img src="{{ $heroImage }}" alt="" class="size-full object-cover">
                {{-- The layered gradient keeps the headline readable over the photo. --}}
                <div class="absolute inset-0 bg-[linear-gradient(100deg,#05261ef2_0%,#0c4234e0_45%,#05261e99_100%)]"></div>
            @endif
            <div class="absolute inset-0 bg-[radial-gradient(120%_120%_at_20%_0%,#0f7d5a_0%,#0c4234_50%,#05261e_100%)] {{ $heroImage ? 'opacity-60' : '' }}"></div>
            <div class="grain absolute inset-0 opacity-50"></div>
        </div>
    @endif

    <div class="shell relative py-10 sm:py-14">
        @if ($breadcrumbs)
            <x-ui.breadcrumbs
                :items="$breadcrumbs"
                class="{{ $tone === 'light' ? '' : '[&_a]:text-white/55 [&_a:hover]:text-white [&_span]:text-white/85 [&_svg]:text-white/35' }}"
            />
        @endif

        <div class="mt-5 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                @if ($eyebrow)
                    <p class="eyebrow {{ $tone === 'light' ? '' : '!text-accent-300' }}">
                        <span class="inline-block h-px w-6 {{ $tone === 'light' ? 'bg-brand-400' : 'bg-accent-400' }}"></span>
                        {{ $eyebrow }}
                    </p>
                @endif

                <h1 class="display text-shadow-hero mt-3 text-3xl {{ $tone === 'light' ? 'text-ink-900' : 'text-white' }} sm:text-4xl lg:text-[2.75rem]">
                    {{ $title }}
                </h1>

                @if ($description)
                    <p class="mt-3.5 text-[15px] leading-relaxed {{ $tone === 'light' ? 'text-ink-600' : 'text-white/70' }}">
                        {{ $description }}
                    </p>
                @endif
            </div>

            @if (! $slot->isEmpty())
                <div class="shrink-0">{{ $slot }}</div>
            @endif
        </div>
    </div>
</section>
