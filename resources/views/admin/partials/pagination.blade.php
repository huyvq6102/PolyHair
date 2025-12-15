@if ($paginator->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-3">
        {{-- Text hiển thị thông tin --}}
        <div class="text-muted">
            Đang xem {{ $paginator->firstItem() }} đến {{ $paginator->lastItem() }} trong tổng số {{ $paginator->total() }} mục
        </div>

        {{-- Pagination controls --}}
        <nav>
            <ul class="pagination mb-0">
                {{-- Previous Button --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link">Trước</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">Trước</a>
                    </li>
                @endif

                {{-- Page Numbers --}}
                @php
                    // Sử dụng getUrlRange để lấy danh sách các trang
                    $currentPage = $paginator->currentPage();
                    $lastPage = $paginator->lastPage();
                    
                    // Chỉ hiển thị tối đa 5 trang xung quanh trang hiện tại
                    $start = max(1, $currentPage - 2);
                    $end = min($lastPage, $currentPage + 2);
                    
                    // Điều chỉnh nếu gần đầu hoặc cuối
                    if ($end - $start < 4) {
                        if ($start == 1) {
                            $end = min($lastPage, $start + 4);
                        } else {
                            $start = max(1, $end - 4);
                        }
                    }
                    
                    $pageUrls = $paginator->getUrlRange($start, $end);
                @endphp
                
                @foreach ($pageUrls as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="page-item active" aria-current="page">
                            <span class="page-link">{{ $page }}</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach

                {{-- Next Button --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Tiếp</a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link">Tiếp</span>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
@endif

