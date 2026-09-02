@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="mt-8 flex items-center justify-between gap-3 border-t border-ink-100 pt-5">
        @if ($paginator->onFirstPage())
            <span class="btn btn-outline btn-sm opacity-50">Previous</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-outline btn-sm">
                <x-ui.icon name="chevron-left" class="size-3.5" /> Previous
            </a>
        @endif

        <span class="text-xs font-semibold text-ink-500">Page {{ $paginator->currentPage() }}</span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-outline btn-sm">
                Next <x-ui.icon name="chevron-right" class="size-3.5" />
            </a>
        @else
            <span class="btn btn-outline btn-sm opacity-50">Next</span>
        @endif
    </nav>
@endif
