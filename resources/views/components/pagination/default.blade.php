@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="mt-8 flex items-center justify-between gap-4 border-t border-ink-100 pt-5">
        {{-- Mobile --}}
        <div class="flex flex-1 items-center justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="btn btn-outline btn-sm opacity-50">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-outline btn-sm">Previous</a>
            @endif

            <span class="text-xs font-semibold text-ink-500">
                Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-outline btn-sm">Next</a>
            @else
                <span class="btn btn-outline btn-sm opacity-50">Next</span>
            @endif
        </div>

        {{-- Desktop --}}
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <p class="text-xs text-ink-500">
                Showing <span class="font-bold text-ink-800">{{ $paginator->firstItem() }}</span>–<span class="font-bold text-ink-800">{{ $paginator->lastItem() }}</span>
                of <span class="font-bold text-ink-800">{{ $paginator->total() }}</span> results
            </p>

            <div class="flex items-center gap-1">
                @if ($paginator->onFirstPage())
                    <span class="grid size-9 place-items-center rounded-lg text-ink-300" aria-disabled="true">
                        <x-ui.icon name="chevron-left" class="size-4" />
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page"
                       class="grid size-9 place-items-center rounded-lg text-ink-600 transition hover:bg-brand-50 hover:text-brand-700">
                        <x-ui.icon name="chevron-left" class="size-4" />
                    </a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="grid size-9 place-items-center text-sm text-ink-400">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="grid size-9 place-items-center rounded-lg bg-brand-600 text-sm font-bold text-white">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" aria-label="Go to page {{ $page }}"
                                   class="grid size-9 place-items-center rounded-lg text-sm font-semibold text-ink-600 transition hover:bg-brand-50 hover:text-brand-700">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page"
                       class="grid size-9 place-items-center rounded-lg text-ink-600 transition hover:bg-brand-50 hover:text-brand-700">
                        <x-ui.icon name="chevron-right" class="size-4" />
                    </a>
                @else
                    <span class="grid size-9 place-items-center rounded-lg text-ink-300" aria-disabled="true">
                        <x-ui.icon name="chevron-right" class="size-4" />
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
