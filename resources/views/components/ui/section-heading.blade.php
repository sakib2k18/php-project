@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'align' => 'left',
    'level' => 'h2',
])

<div {{ $attributes->merge(['class' => 'max-w-2xl '.($align === 'center' ? 'mx-auto text-center' : '')]) }}>
    @if ($eyebrow)
        <p class="eyebrow {{ $align === 'center' ? 'justify-center' : '' }}">
            <span class="inline-block h-px w-6 bg-brand-400"></span>
            {{ $eyebrow }}
        </p>
    @endif

    <{{ $level }} class="display mt-3 text-3xl text-ink-900 sm:text-4xl">{{ $title }}</{{ $level }}>

    @if ($description)
        <p class="lede mt-3.5">{{ $description }}</p>
    @endif

    {{ $slot }}
</div>
