@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="mono-pagination">
        @if ($paginator->onFirstPage())
            <span class="mono-pagination-link is-disabled">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="mono-pagination-link">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="mono-pagination-link">
                {!! __('pagination.next') !!}
            </a>
        @else
            <span class="mono-pagination-link is-disabled">
                {!! __('pagination.next') !!}
            </span>
        @endif
    </nav>
@endif
