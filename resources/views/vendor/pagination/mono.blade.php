@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Страницы" class="flex justify-between gap-4">
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center px-3 py-1 text-xs text-white/30 border border-white/10 cursor-default">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="glitch-hover inline-flex items-center px-3 py-1 text-xs text-white/80 border border-white/20 hover:bg-white/5">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="glitch-hover inline-flex items-center px-3 py-1 text-xs text-white/80 border border-white/20 hover:bg-white/5">
                {!! __('pagination.next') !!}
            </a>
        @else
            <span class="inline-flex items-center px-3 py-1 text-xs text-white/30 border border-white/10 cursor-default">
                {!! __('pagination.next') !!}
            </span>
        @endif
    </nav>
@endif
