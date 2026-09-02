@props([
    'src' => null,
    'initials' => '?',
    'name' => '',
    'size' => 'md',
])

@php
    $sizes = [
        'xs' => 'size-7 text-[10px]',
        'sm' => 'size-9 text-xs',
        'md' => 'size-11 text-sm',
        'lg' => 'size-16 text-lg',
        'xl' => 'size-24 text-2xl',
    ];

    $classes = $sizes[$size] ?? $sizes['md'];
@endphp

@if ($src)
    <img
        src="{{ $src }}"
        alt="{{ $name }}"
        loading="lazy"
        {{ $attributes->merge(['class' => "{$classes} shrink-0 rounded-full object-cover ring-2 ring-white"]) }}
    >
@else
    <span
        {{ $attributes->merge(['class' => "{$classes} grid shrink-0 place-items-center rounded-full bg-brand-100 font-bold uppercase text-brand-700 ring-2 ring-white"]) }}
        aria-hidden="true"
    >{{ $initials }}</span>
@endif
