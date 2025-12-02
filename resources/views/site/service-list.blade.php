@extends('layouts.site')

@section('title', 'Dịch vụ')

@section('content')

    <!-- service_area_start -->
    <div style="padding-top: 120px;"></div>
    <section class="service-section py-5">
  <div class="container">
    <div class="d-flex align-items-start mb-3">
      <span class="bar mr-2"></span>
      <div>
        <h3 class="title ba-title mb-0">DỊCH VỤ TÓC & COMBO</h3>
        <p class="desc">
            Chào mừng Quý khách hàng đến với POLY HAIR ,
            nơi mang đến cho bạn trải nghiệm làm đẹp tinh tế và độc đáo.
            Dưới đây là danh sách tất cả các dịch vụ làm tóc của chúng tôi.
        </p>
      </div>
    </div>
  </div>
  <div class="container service-wrapper">
    <!-- Sidebar Filter -->
    <div class="service_left">
      <div class="service-left-inner">
        <h4 class="service-title mb-3">Bộ lọc</h4>

        <form method="GET" action="{{ route('site.services.index') }}" id="filterForm" onsubmit="return handleFilterSubmit(event)">
          <!-- Search by Name -->
          <div class="filter-group mb-4">
            <h5 class="filter-title mb-2">Tìm kiếm</h5>
            <input type="text" name="keyword" class="form-control filter-select" placeholder="Nhập tên dịch vụ..." value="{{ $keyword ?? '' }}" onkeyup="if(event.key === 'Enter') this.form.submit()">
          </div>

          <!-- Filter by Type -->
          <div class="filter-group mb-4">
            <h5 class="filter-title mb-2">Loại dịch vụ</h5>
            <select name="filter_type" class="form-control filter-select" onchange="this.form.submit()">
              <option value="all" {{ ($filterType ?? 'all') == 'all' ? 'selected' : '' }}>Tất cả</option>
              <option value="service_single" {{ ($filterType ?? '') == 'service_single' ? 'selected' : '' }}>Dịch vụ đơn</option>
              <option value="service_variant" {{ ($filterType ?? '') == 'service_variant' ? 'selected' : '' }}>Dịch vụ biến thể</option>
              <option value="combo" {{ ($filterType ?? '') == 'combo' ? 'selected' : '' }}>Combo</option>
            </select>
          </div>

          <!-- Filter by Category -->
          @if(isset($categories) && $categories->count() > 0)
          <div class="filter-group mb-4">
            <h5 class="filter-title mb-2">Danh mục</h5>
            <select name="category" class="form-control filter-select" onchange="this.form.submit()">
              <option value="">Tất cả danh mục</option>
              @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ (isset($categoryId) && $categoryId == $category->id) ? 'selected' : '' }}>
                  {{ $category->name }}
                </option>
              @endforeach
            </select>
          </div>
          @endif

          <!-- Filter by Price -->
          <div class="filter-group mb-4">
            <h5 class="filter-title mb-2">Khoảng giá</h5>
            <div class="price-inputs">
              <input type="number" name="min_price" class="form-control filter-select mb-2" placeholder="Giá tối thiểu (vnđ)" value="{{ $minPrice ?? '' }}" min="0" step="1000">
              <input type="number" name="max_price" class="form-control filter-select" placeholder="Giá tối đa (vnđ)" value="{{ $maxPrice ?? '' }}" min="0" step="1000">
            </div>
          </div>

          <!-- Sort -->
          <div class="filter-group mb-4">
            <h5 class="filter-title mb-2">Sắp xếp</h5>
            <select name="sort_by" class="form-control filter-select" onchange="this.form.submit()">
              <option value="id_desc" {{ ($sortBy ?? 'id_desc') == 'id_desc' ? 'selected' : '' }}>Mới nhất</option>
              <option value="name_asc" {{ ($sortBy ?? '') == 'name_asc' ? 'selected' : '' }}>Tên A-Z</option>
              <option value="name_desc" {{ ($sortBy ?? '') == 'name_desc' ? 'selected' : '' }}>Tên Z-A</option>
              <option value="price_asc" {{ ($sortBy ?? '') == 'price_asc' ? 'selected' : '' }}>Giá tăng dần</option>
              <option value="price_desc" {{ ($sortBy ?? '') == 'price_desc' ? 'selected' : '' }}>Giá giảm dần</option>
            </select>
          </div>

          <!-- Filter Buttons -->
          <div class="filter-actions">
            <button type="submit" class="btn btn-primary btn-block mb-2">Áp dụng</button>
            <a href="{{ route('site.services.index') }}" class="btn btn-secondary btn-block">Xóa bộ lọc</a>
          </div>

          <!-- Keep existing type parameter for backward compatibility -->
          @if(isset($typeId))
            <input type="hidden" name="type" value="{{ $typeId }}">
          @endif
        </form>
      </div>
    </div>

    <div class="service_right">

      <div class="service-grid">
        @forelse($items as $item)
          @php
            // Format giá tiền
            $formattedPrice = number_format($item['price'], 0, ',', '.') . 'vnđ';

            // Đường dẫn ảnh
            $imagePath = $item['image']
                ? asset('legacy/images/products/' . $item['image'])
                : asset('legacy/images/products/default.jpg');

            // Badge type
            if ($item['type'] == 'service_single') {
              $typeBadge = 'Dịch vụ đơn';
              $typeClass = 'badge-primary';
            } elseif ($item['type'] == 'service_variant') {
              $typeBadge = 'Dịch vụ biến thể';
              $typeClass = 'badge-info';
            } else {
              $typeBadge = 'Combo';
              $typeClass = 'badge-success';
            }
          @endphp
          <div class="svc-card">
            <a class="svc-img" href="{{ $item['link'] }}">
              <img src="{{ $imagePath }}" alt="{{ $item['name'] }}">
              <span class="badge {{ $typeClass }} position-absolute" style="top: 10px; right: 10px;">{{ $typeBadge }}</span>
            </a>
            <div class="svc-body">
              <div class="svc-left">
                <h4 class="svc-name">{{ $item['name'] }}</h4>
                <div class="svc-price">Giá từ: <span>{{ $formattedPrice }}</span></div>
              </div>
              <div class="svc-right">
                <span class="svc-rating">5 ★ Đánh giá</span>
                <a class="svc-book" href="{{ route('site.appointment.create') }}">Đặt lịch ngay</a>
              </div>
            </div>
          </div>
        @empty
          <div class="col-12 text-center py-5">
            <p>Chưa có dịch vụ hoặc combo nào.</p>
          </div>
        @endforelse
      </div>

      <!-- Pagination -->
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
                <a class="page-link" href="{{ $items->previousPageUrl() }}" rel="prev">&lsaquo; Trước</a>
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
                  <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                </li>
              @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($items->hasMorePages())
              <li class="page-item">
                <a class="page-link" href="{{ $items->nextPageUrl() }}" rel="next">Sau &rsaquo;</a>
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

    </div>
  </div>
</section>

    <!-- service_area_end -->
    
<script>
function handleFilterSubmit(event) {
    // Lưu vị trí scroll hiện tại
    const scrollPosition = window.pageYOffset || document.documentElement.scrollTop;
    sessionStorage.setItem('filterScrollPosition', scrollPosition);
    
    // Cho phép form submit bình thường
    return true;
}

// Sau khi trang load, scroll về vị trí đã lưu
document.addEventListener('DOMContentLoaded', function() {
    const savedPosition = sessionStorage.getItem('filterScrollPosition');
    if (savedPosition) {
        // Scroll về vị trí filter (khoảng 120px từ đầu trang)
        const filterElement = document.querySelector('.service_left');
        if (filterElement) {
            setTimeout(function() {
                const filterTop = filterElement.offsetTop - 120;
                window.scrollTo({
                    top: filterTop,
                    behavior: 'smooth'
                });
                // Xóa vị trí đã lưu sau khi scroll
                sessionStorage.removeItem('filterScrollPosition');
            }, 100);
        } else {
            // Nếu không tìm thấy filter, scroll về vị trí đã lưu
            window.scrollTo({
                top: parseInt(savedPosition),
                behavior: 'smooth'
            });
            sessionStorage.removeItem('filterScrollPosition');
        }
    }
});
</script>
@endsection

