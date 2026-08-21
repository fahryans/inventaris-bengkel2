@if ($paginator->hasPages())
    <nav class="d-flex align-items-center justify-content-center gap-1">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="btn btn-sm btn-outline-secondary disabled">
                <i class="fas fa-chevron-left"></i>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-chevron-left"></i>
            </a>
        @endif

        {{-- Page Numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="btn btn-sm btn-outline-secondary disabled">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="btn btn-sm btn-primary">{{ $page }}</span>
                    @else
                        @if ($page == $paginator->currentPage() - 2 || $page == $paginator->currentPage() + 2)
                            <span class="btn btn-sm btn-outline-secondary disabled">...</span>
                        @elseif ($page >= $paginator->currentPage() - 1 && $page <= $paginator->currentPage() + 1)
                            <a href="{{ $url }}" class="btn btn-sm btn-outline-primary">{{ $page }}</a>
                        @endif
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-chevron-right"></i>
            </a>
        @else
            <span class="btn btn-sm btn-outline-secondary disabled">
                <i class="fas fa-chevron-right"></i>
            </span>
        @endif

        {{-- Info --}}
        <span class="text-muted ms-2 small">
            {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} dari {{ $paginator->total() }}
        </span>
    </nav>
@endif
