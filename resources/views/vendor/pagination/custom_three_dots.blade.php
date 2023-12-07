@if ($paginator->hasPages())
    <ul class="blog-pagenation">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <a class="prev page-numbers disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                <span aria-hidden="true"> <i class='bx bx-chevrons-left'></i></span>
            </a>
        @else
            <a class="prev page-numbers" href="{{ $paginator->previousPageUrl() }}" rel="prev"
                aria-label="@lang('pagination.previous')"><i class='bx bx-chevrons-left'></i></a>
        @endif

        @if ($paginator->currentPage() > 3)
            <a class="page-numbers" href="{{ $paginator->url(1) }}">1</a>
        @endif
        @if ($paginator->currentPage() > 4)
            <a class="page-numbers">...</a>
        @endif
        @foreach (range(1, $paginator->lastPage()) as $i)
            @if ($i >= $paginator->currentPage() - 2 && $i <= $paginator->currentPage() + 2)
                @if ($i == $paginator->currentPage())
                    <a class="page-numbers current">{{ $i }}</a>
                @else
                    <a class="page-numbers" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                @endif
            @endif
        @endforeach
        @if ($paginator->currentPage() < $paginator->lastPage() - 3)
            <a class="page-numbers">...</a>
        @endif
        @if ($paginator->currentPage() < $paginator->lastPage() - 2)
            <a class="page-numbers"
                href="{{ $paginator->url($paginator->lastPage()) }}">{{ $paginator->lastPage() }}</a>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a class="page-numbers" href="{{ $paginator->nextPageUrl() }}" rel="next"
                aria-label="@lang('pagination.next')"><i class='bx bx-chevrons-right'></i></a>
        @else
            <a class="next page-numbers disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                <span aria-hidden="true"> <i class='bx bx-chevrons-right'></i></span>
            </a>
        @endif
    </ul>
@endif
