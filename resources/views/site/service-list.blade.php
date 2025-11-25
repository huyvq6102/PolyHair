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
            Chào mừng Quý khách hàng đến với Traky Hair Salon,
            nơi mang đến cho bạn trải nghiệm làm đẹp tinh tế và độc đáo.
            Dưới đây là danh sách tất cả các dịch vụ làm tóc của chúng tôi.
        </p>
      </div>
    </div>
  </div>
  <div class="container service-wrapper">
    <div class="service_right">

      <div class="service-grid">
        @forelse($services as $service)
          @php
            // Lấy giá từ variant đầu tiên hoặc base_price
            $price = $service->serviceVariants->where('is_active', true)->min('price') 
                     ?? $service->serviceVariants->min('price') 
                     ?? $service->base_price 
                     ?? 0;
            
            // Format giá tiền
            $formattedPrice = number_format($price, 0, ',', '.') . 'vnđ';
            
            // Đường dẫn ảnh
            $imagePath = $service->image 
                ? asset('legacy/images/products/' . $service->image)
                : asset('legacy/images/products/default.jpg');
            
            // Link đến trang chi tiết
            $serviceLink = route('site.services.show', $service->id);
          @endphp
          <div class="svc-card">
            <a class="svc-img" href="{{ $serviceLink }}">
              <img src="{{ $imagePath }}" alt="{{ $service->name }}">
            </a>
            <div class="svc-body">
              <div class="svc-left">
                <h4 class="svc-name">{{ $service->name }}</h4>
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
            <p>Chưa có dịch vụ nào.</p>
          </div>
        @endforelse
      </div>
    
      <!-- Pagination -->
      @if($services->hasPages())
      <div class="d-flex justify-content-center mt-4">
        <nav aria-label="Service pagination">
          <ul class="pagination service-pagination">
            {{-- Previous Page Link --}}
            @if ($services->onFirstPage())
              <li class="page-item disabled" aria-disabled="true">
                <span class="page-link" aria-hidden="true">&lsaquo; Trước</span>
              </li>
            @else
              <li class="page-item">
                <a class="page-link" href="{{ $services->previousPageUrl() }}" rel="prev">&lsaquo; Trước</a>
              </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($services->getUrlRange(1, $services->lastPage()) as $page => $url)
              @if ($page == $services->currentPage())
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
            @if ($services->hasMorePages())
              <li class="page-item">
                <a class="page-link" href="{{ $services->nextPageUrl() }}" rel="next">Sau &rsaquo;</a>
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

