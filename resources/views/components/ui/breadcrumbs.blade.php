@props(['items' => []])

<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'flex items-center gap-1.5 text-xs']) }}>
    <a href="{{ route('home') }}" class="text-ink-500 transition hover:text-brand-700">Home</a>

    @foreach ($items as $label => $url)
        <x-ui.icon name="chevron-right" class="size-3 shrink-0 text-ink-300" />

        @if ($loop->last || $url === null)
            <span class="truncate font-semibold text-ink-800" aria-current="page">{{ $label }}</span>
        @else
            <a href="{{ $url }}" class="truncate text-ink-500 transition hover:text-brand-700">{{ $label }}</a>
        @endif
    @endforeach
</nav>
