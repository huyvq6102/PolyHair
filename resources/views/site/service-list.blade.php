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

        <form method="GET" action="{{ route('site.services.index') }}" id="filterForm">
          <!-- Filter by Type -->
          <div class="filter-group mb-4">
            <h5 class="filter-title mb-2">Loại</h5>
            <div class="filter-options">
              <label class="filter-option">
                <input type="radio" name="filter_type" value="all" {{ ($filterType ?? 'all') == 'all' ? 'checked' : '' }} onchange="this.form.submit()">
                <span>Tất cả</span>
              </label>
              <label class="filter-option">
                <input type="radio" name="filter_type" value="service" {{ ($filterType ?? '') == 'service' ? 'checked' : '' }} onchange="this.form.submit()">
                <span>Dịch vụ</span>
              </label>
              <label class="filter-option">
                <input type="radio" name="filter_type" value="combo" {{ ($filterType ?? '') == 'combo' ? 'checked' : '' }} onchange="this.form.submit()">
                <span>Combo</span>
              </label>
            </div>
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

          <!-- Filter Buttons -->
          <div class="filter-actions">
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
            $typeBadge = $item['type'] == 'service' ? 'Dịch vụ' : 'Combo';
            $typeClass = $item['type'] == 'service' ? 'badge-primary' : 'badge-success';
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
@endsection

