@props([
    'latitude' => null,
    'longitude' => null,
    'title' => null,
    'points' => null,
    'zoom' => null,
    'height' => 'h-80',
])

@php
    $enabled = config('apis.map.enabled');
    $lat = $latitude ?? settings()->latitude();
    $lng = $longitude ?? settings()->longitude();
@endphp

@if ($enabled && $lat !== null && $lng !== null)
    <div {{ $attributes->merge(['class' => "relative overflow-hidden rounded-2xl border border-ink-100 bg-ink-50 {$height}"]) }}>
        <div
            data-map
            data-map-lat="{{ $lat }}"
            data-map-lng="{{ $lng }}"
            data-map-zoom="{{ $zoom ?? config('apis.map.default_zoom') }}"
            data-map-max-zoom="{{ config('apis.map.max_zoom') }}"
            data-map-tiles="{{ config('apis.map.tile_url') }}"
            data-map-attribution="{{ config('apis.map.attribution') }}"
            data-map-title="{{ $title ?? settings()->name() }}"
            @if ($points) data-map-points="{{ json_encode($points) }}" @endif
            class="size-full"
        >
            {{-- Shown until Leaflet finishes loading. --}}
            <div data-map-loading class="grid size-full place-items-center">
                <div class="flex flex-col items-center gap-2 text-ink-400">
                    <div class="skeleton size-10 rounded-full"></div>
                    <span class="text-xs font-medium">Loading map…</span>
                </div>
            </div>
        </div>
    </div>
@else
    <div {{ $attributes->merge(['class' => "grid place-items-center rounded-2xl border border-ink-100 bg-ink-50 {$height}"]) }}>
        <div class="flex flex-col items-center gap-2 p-6 text-center text-ink-500">
            <x-ui.icon name="map-pin" class="size-7 text-ink-300" />
            <p class="text-sm font-medium">{{ $title ?? settings()->address() }}</p>
        </div>
    </div>
@endif
