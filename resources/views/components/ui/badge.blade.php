@props([
    'tone' => 'neutral',
    'icon' => null,
])

<span {{ $attributes->merge(['class' => "badge badge-{$tone}"]) }}>
    @if ($icon)
        <x-ui.icon :name="$icon" class="size-3" />
    @endif
    {{ $slot }}
</span>
