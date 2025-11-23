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
        <h3 class="title ba-title mb-0">MẪU TÓC HOT</h3>
        <p class="desc">
            Chào mừng Quý khách hàng đến với Traky Hair Salon,
            nơi mang đến cho bạn trải nghiệm làm đẹp tinh tế và độc đáo.
            Dưới đây là bộ sưu tập những mẫu tóc đẹp nhất năm 2024 giúp nâng tầm vẻ đẹp của bạn lên một tầm cao mới.
        </p>
      </div>
    </div>
  </div>
  <div class="container service-wrapper">
    <div class="service_right">

      <div class="service-grid">
        @foreach([
          ['name'=>'NỐI TÓC','price'=>'15.000vnđ','link'=>'#','img'=>'https://trakyhairsalon.com/thumbs/375x500x1/upload/news/375-x-500-5-7605.png'],
          ['name'=>'PHỤC HỒI','price'=>'300.000vnđ','link'=>'#','img'=>'https://trakyhairsalon.com/thumbs/375x500x1/upload/news/375-x-500-3-4269.png'],
          ['name'=>'CẮT TÓC','price'=>'200.000vnđ','link'=>'#','img'=>'https://trakyhairsalon.com/thumbs/375x500x1/upload/news/375-x-500-2-2717.png'],
          ['name'=>'CẮT TÓC','price'=>'200.000vnđ','link'=>'#','img'=>'https://trakyhairsalon.com/thumbs/375x500x1/upload/news/375-x-500-2-2717.png'],
        ] as $svc)
        <div class="svc-card">
          <a class="svc-img" href="{{ $svc['link'] }}"><img src="{{ $svc['img'] }}" alt="{{ $svc['name'] }}"></a>
          <div class="svc-body">
            <div class="svc-left">
              <h4 class="svc-name">{{ $svc['name'] }}</h4>
              <div class="svc-price">Giá từ: <span>{{ $svc['price'] }}</span></div>
            </div>
            <div class="svc-right">
              <span class="svc-rating">5 ★ Đánh giá</span>
              <a class="svc-book" href="{{ $svc['link'] }}">Đặt lịch ngay</a>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    
    </div>
  </div>
</section>

    <!-- service_area_end -->
@endsection

