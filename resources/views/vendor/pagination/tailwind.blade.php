@if ($paginator->hasPages())
    <nav class="app-pagination" role="navigation" aria-label="Paginacion">
        <style>
            .app-pagination {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
                padding: 18px 4px 6px;
                color: #102033;
                font-family: "Segoe UI", Tahoma, sans-serif;
            }

            .app-pagination__summary {
                color: #64748b;
                font-size: 13px;
                font-weight: 800;
            }

            .app-pagination__links {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 8px;
            }

            .app-pagination__link,
            .app-pagination__disabled,
            .app-pagination__active {
                min-width: 40px;
                min-height: 40px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0 13px;
                border-radius: 10px;
                border: 1px solid #bfdbfe;
                background: #ffffff;
                color: #1d4ed8;
                font-size: 13px;
                font-weight: 900;
                line-height: 1;
                text-decoration: none;
                box-shadow: 0 8px 18px rgba(15, 23, 42, .06);
                transition: transform .16s ease, background .16s ease, color .16s ease, box-shadow .16s ease;
            }

            .app-pagination__link:hover {
                background: #eff6ff;
                color: #0f3f88;
                transform: translateY(-1px);
                box-shadow: 0 12px 24px rgba(37, 99, 235, .14);
            }

            .app-pagination__active {
                background: #2563eb;
                border-color: #1d4ed8;
                color: #ffffff;
                box-shadow: 0 12px 24px rgba(37, 99, 235, .22);
            }

            .app-pagination__disabled {
                background: #f1f5f9;
                border-color: #e2e8f0;
                color: #94a3b8;
                box-shadow: none;
            }

            .app-pagination__arrow {
                min-width: 92px;
            }

            @media (max-width: 640px) {
                .app-pagination {
                    display: grid;
                    justify-content: stretch;
                }

                .app-pagination__summary {
                    text-align: center;
                }

                .app-pagination__links {
                    justify-content: center;
                }

                .app-pagination__arrow {
                    min-width: 44px;
                }
            }
        </style>

        <div class="app-pagination__summary">
            Mostrando {{ $paginator->firstItem() }} a {{ $paginator->lastItem() }} de {{ $paginator->total() }} registros
        </div>

        <div class="app-pagination__links">
            @if ($paginator->onFirstPage())
                <span class="app-pagination__disabled app-pagination__arrow">Anterior</span>
            @else
                <a class="app-pagination__link app-pagination__arrow" href="{{ $paginator->previousPageUrl() }}" rel="prev">Anterior</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="app-pagination__disabled">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="app-pagination__active" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="app-pagination__link" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="app-pagination__link app-pagination__arrow" href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente</a>
            @else
                <span class="app-pagination__disabled app-pagination__arrow">Siguiente</span>
            @endif
        </div>
    </nav>
@endif
