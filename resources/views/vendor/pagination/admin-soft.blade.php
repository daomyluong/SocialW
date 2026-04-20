@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="d-flex justify-content-center">
        <ul class="pagination mb-0" style="gap: 6px;">
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="page-link" aria-hidden="true" style="border-radius: 999px; border: 1px solid #e2e8f0; color: #94a3b8; background: #f8fafc;">&lsaquo;</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')" style="border-radius: 999px; border: 1px solid #dbeafe; color: #2563eb; background: #ffffff;">&lsaquo;</a>
                </li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link" style="border-radius: 999px; border: 1px solid #e2e8f0; color: #94a3b8;">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span class="page-link" style="border-radius: 999px; border: none; color: #ffffff; background: linear-gradient(135deg, #43e97b 0%, #4facfe 100%); box-shadow: 0 6px 16px rgba(79, 172, 254, 0.25);">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $url }}" style="border-radius: 999px; border: 1px solid #dbeafe; color: #2563eb; background: #ffffff;">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')" style="border-radius: 999px; border: 1px solid #dbeafe; color: #2563eb; background: #ffffff;">&rsaquo;</a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="page-link" aria-hidden="true" style="border-radius: 999px; border: 1px solid #e2e8f0; color: #94a3b8; background: #f8fafc;">&rsaquo;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
