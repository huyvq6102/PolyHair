@if($items->hasPages())
<div class="d-flex justify-content-center mt-4">
  <nav aria-label="Service pagination">
    <ul class="pagination service-pagination">
      {{-- Previous Page Link --}}
      @if ($items->onFirstPage())
        <li class="page-item disabled" aria-disabled="true">
          <span class="page-link" aria-hidden="true">&lsaquo; Trước</span>
        </li>
      @else
        <li class="page-item">
          <a class="page-link" href="{{ $items->previousPageUrl() }}" rel="prev" data-ajax-pagination>&lsaquo; Trước</a>
        </li>
      @endif

      {{-- Pagination Elements --}}
      @foreach ($items->getUrlRange(1, $items->lastPage()) as $page => $url)
        @if ($page == $items->currentPage())
          <li class="page-item active" aria-current="page">
            <span class="page-link">{{ $page }}</span>
          </li>
        @else
          <li class="page-item">
            <a class="page-link" href="{{ $url }}" data-ajax-pagination>{{ $page }}</a>
          </li>
        @endif
      @endforeach

      {{-- Next Page Link --}}
      @if ($items->hasMorePages())
        <li class="page-item">
          <a class="page-link" href="{{ $items->nextPageUrl() }}" rel="next" data-ajax-pagination>Sau &rsaquo;</a>
        </li>
      @else
        <li class="page-item disabled" aria-disabled="true">
          <span class="page-link" aria-hidden="true">Sau &rsaquo;</span>
        </li>
      @endif
    </ul>
  </nav>
</div>
@endif

