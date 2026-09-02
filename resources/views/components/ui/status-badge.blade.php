@props([
    'status',
    'label' => null,
])

@php
    // One mapping for every status enum in the app, so a "pending" donation and
    // a "draft" campaign always read the same way wherever they appear.
    $tones = [
        'active'    => ['success', 'check-circle'],
        'completed' => ['brand',   'check-badge'],
        'draft'     => ['neutral', 'pencil'],
        'cancelled' => ['danger',  'x-circle'],
        'approved'  => ['success', 'check-circle'],
        'pending'   => ['warning', 'clock'],
        'rejected'  => ['danger',  'x-circle'],
        'published' => ['success', 'check-circle'],
        'ongoing'   => ['info',    'refresh'],
        'planned'   => ['neutral', 'calendar'],
    ];

    [$tone, $icon] = $tones[$status] ?? ['neutral', null];
@endphp

<x-ui.badge :tone="$tone" :icon="$icon" {{ $attributes }}>
    {{ $label ?? ucfirst(str_replace('_', ' ', $status)) }}
</x-ui.badge>
