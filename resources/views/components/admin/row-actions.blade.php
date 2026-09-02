@props([
    'edit' => null,
    'view' => null,
    'delete' => null,
    'deleteConfirm' => 'This cannot be undone.',
    'deleteTitle' => 'Delete this record?',
    'deleteLabel' => 'Delete',
])

<div {{ $attributes->merge(['class' => 'flex items-center justify-end gap-1']) }}>
    {{ $slot }}

    @if ($view)
        <a href="{{ $view }}" class="grid size-8 place-items-center rounded-lg text-ink-500 transition hover:bg-brand-50 hover:text-brand-700" aria-label="View">
            <x-ui.icon name="eye" class="size-4" />
        </a>
    @endif

    @if ($edit)
        <a href="{{ $edit }}" class="grid size-8 place-items-center rounded-lg text-ink-500 transition hover:bg-brand-50 hover:text-brand-700" aria-label="Edit">
            <x-ui.icon name="pencil" class="size-4" />
        </a>
    @endif

    @if ($delete)
        {{-- Confirmed client-side and authorised again server-side by the policy. --}}
        <form method="POST" action="{{ $delete }}"
              data-confirm="{{ $deleteConfirm }}"
              data-confirm-title="{{ $deleteTitle }}"
              data-confirm-action="{{ $deleteLabel }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="grid size-8 place-items-center rounded-lg text-ink-500 transition hover:bg-rose-50 hover:text-rose-600" aria-label="Delete">
                <x-ui.icon name="trash" class="size-4" />
            </button>
        </form>
    @endif
</div>
