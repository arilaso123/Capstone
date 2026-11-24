@if ($paginator->hasPages())
    <nav class="editorial-pagination-wrapper" role="navigation" aria-label="Navigasi Halaman">
        <div class="editorial-pagination-info">
            Menampilkan
            <strong>{{ number_format($paginator->firstItem(), 0, ',', '.') }}</strong>
            &ndash;
            <strong>{{ number_format($paginator->lastItem(), 0, ',', '.') }}</strong>
            dari
            <strong>{{ number_format($paginator->total(), 0, ',', '.') }}</strong>
            data
        </div>

        <div class="d-flex d-sm-none align-items-center gap-2">
            @if ($paginator->onFirstPage())
                <span class="editorial-page-btn disabled" aria-disabled="true">
                    <i class="bi bi-chevron-left" aria-hidden="true"></i> Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="editorial-page-btn">
                    <i class="bi bi-chevron-left" aria-hidden="true"></i> Sebelumnya
                </a>
            @endif

            <span class="editorial-pagination-info px-1">
                <strong>{{ $paginator->currentPage() }}</strong> / {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="editorial-page-btn">
                    Berikutnya <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </a>
            @else
                <span class="editorial-page-btn disabled" aria-disabled="true">
                    Berikutnya <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </span>
            @endif
        </div>

        <ul class="editorial-pagination-list d-none d-sm-flex">
            @if ($paginator->onFirstPage())
                <li>
                    <span class="editorial-page-btn editorial-page-icon disabled" aria-disabled="true" aria-label="Halaman Sebelumnya">
                        <i class="bi bi-chevron-left" aria-hidden="true"></i>
                    </span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="editorial-page-btn editorial-page-icon" aria-label="Halaman Sebelumnya" title="Halaman Sebelumnya">
                        <i class="bi bi-chevron-left" aria-hidden="true"></i>
                    </a>
                </li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="editorial-page-dots" aria-disabled="true">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li><span class="editorial-page-btn active" aria-current="page">{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}" class="editorial-page-btn" aria-label="Halaman {{ $page }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="editorial-page-btn editorial-page-icon" aria-label="Halaman Berikutnya" title="Halaman Berikutnya">
                        <i class="bi bi-chevron-right" aria-hidden="true"></i>
                    </a>
                </li>
            @else
                <li>
                    <span class="editorial-page-btn editorial-page-icon disabled" aria-disabled="true" aria-label="Halaman Berikutnya">
                        <i class="bi bi-chevron-right" aria-hidden="true"></i>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
