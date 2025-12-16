@extends('layouts.site')

@section('title', $settings->title ?? 'Trang chủ')

@section('content')
    @include('site.partials.slider')

    <!--1 MẪU TÓC HOT -->

<section class="album-section py-5">
  <div class="container">
    <div class="d-flex align-items-start mb-3">
      <span class="bar mr-2"></span>
      <div>
        <h3 class="title ba-title mb-0">MẪU TÓC HOT</h3>
        <p class="desc">
            Chào mừng Quý khách hàng đến với POLY HAIR Salon,
            nơi mang đến cho bạn trải nghiệm làm đẹp tinh tế và độc đáo.
            Dưới đây là bộ sưu tập những mẫu tóc đẹp nhất năm 2025 giúp nâng tầm vẻ đẹp của bạn lên một tầm cao mới.
        </p>
      </div>
    </div>
    <div class="album-grid">
      @foreach([
        ['name'=>'UỐN SIDE PART','link'=>'#','img'=>'https://storage.30shine.com/stylist-vote/13448_5/1.jpeg'],
        ['name'=>'MÀU HOT TREND','link'=>'#','img'=>'https://storage.30shine.com/stylist-vote/13016_13/1.jpeg'],
        ['name'=>'XOĂN HÀN QUỐC','link'=>'#','img'=>'https://storage.30shine.com/stylist-vote/12822_1/1.png'],
        ['name'=>'KIỂU SHORT QUIFF','link'=>'#','img'=>'https://storage.30shine.com/stylist-vote/10110_2/2.jpeg'],

      ] as $item)
      <div class="album-card">
        <div class="album-img"><img src="{{ $item['img'] }}" alt="{{ $item['name'] }}"></div>
        <div class="album-name">{{ $item['name'] }}</div>

      </div>
      @endforeach
    </div>

  </div>
</section>

<!--2 DỊCH VỤ TÓC -->
<section class="service-section py-5">
  <div class="container">
    <div class="d-flex align-items-start mb-3">
      <span class="bar mr-2"></span>
      <div>
        <h3 class="title ba-title mb-0">DỊCH VỤ TÓC & COMBO</h3>
        <p class="desc">
        Những dịch vụ được khách hàng lựa chọn nhiều nhất tại salon
        </p>
      </div>
    </div>
  </div>
  <div class="container service-wrapper">
    <div class="service_right">

      <div class="service-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
        @php
          // Helper function để tính discount
          function calculateDiscountForService($item, $itemType, $activePromotions) {
            $originalPrice = 0;
            $discount = 0;
            $finalPrice = 0;
            $promotion = null;
            $discountTag = '';

            if ($itemType === 'service') {
              $originalPrice = $item->base_price ?? 0;
            } elseif ($itemType === 'variant') {
              $originalPrice = $item->price ?? 0;
            } elseif ($itemType === 'combo') {
              $originalPrice = $item->price ?? 0;
            }

            if ($originalPrice <= 0) {
              return ['originalPrice' => 0, 'discount' => 0, 'finalPrice' => 0, 'promotion' => null, 'discountTag' => ''];
            }

            $now = \Carbon\Carbon::now();

            foreach ($activePromotions as $promo) {
              if ($promo->status !== 'active') continue;
              if ($promo->start_date && $promo->start_date > $now) continue;
              if ($promo->end_date && $promo->end_date < $now) continue;

              $applies = false;

              if ($itemType === 'service') {
                $hasSpecificServices = ($promo->services && $promo->services->count() > 0)
                  || ($promo->combos && $promo->combos->count() > 0)
                  || ($promo->serviceVariants && $promo->serviceVariants->count() > 0);
                $applyToAll = !$hasSpecificServices ||
                  (($promo->services ? $promo->services->count() : 0) +
                   ($promo->combos ? $promo->combos->count() : 0) +
                   ($promo->serviceVariants ? $promo->serviceVariants->count() : 0)) >= 20;
                if ($promo->apply_scope === 'order' || $applyToAll) {
                  $applies = true;
                } elseif ($promo->services && $promo->services->contains('id', $item->id)) {
                  $applies = true;
                }
              }

              if ($applies) {
                $promotion = $promo;
                if ($promo->discount_type === 'percent') {
                  $discount = ($originalPrice * ($promo->discount_percent ?? 0)) / 100;
                  if ($promo->max_discount_amount) {
                    $discount = min($discount, $promo->max_discount_amount);
                  }
                  $discountTag = '-' . ($promo->discount_percent ?? 0) . '%';
                } else {
                  $discount = min($promo->discount_amount ?? 0, $originalPrice);
                  $discountTag = '-' . number_format($discount / 1000, 0) . 'k';
                }
                $finalPrice = max(0, $originalPrice - $discount);
                break;
              }
            }

            return [
              'originalPrice' => $originalPrice,
              'discount' => $discount,
              'finalPrice' => $finalPrice > 0 ? $finalPrice : $originalPrice,
              'promotion' => $promotion,
              'discountTag' => $discountTag
            ];
          }
        @endphp
        @forelse($services as $service)
          @php
            // Lấy giá từ variant đầu tiên hoặc base_price
            $price = $service->serviceVariants->where('is_active', true)->min('price')
                     ?? $service->serviceVariants->min('price')
                     ?? $service->base_price
                     ?? 0;

            // Tính discount
            $serviceDiscount = calculateDiscountForService($service, 'service', $activePromotions ?? collect());
            $displayPrice = $serviceDiscount['finalPrice'] > 0 ? $serviceDiscount['finalPrice'] : $price;

            // Format giá tiền
            $formattedPrice = number_format($displayPrice, 0, ',', '.') . 'vnđ';
            $formattedOriginalPrice = $serviceDiscount['discount'] > 0 ? number_format($serviceDiscount['originalPrice'], 0, ',', '.') . 'vnđ' : '';

            // Đường dẫn ảnh
            $imagePath = $service->image
                ? asset('legacy/images/products/' . $service->image)
                : asset('legacy/images/products/default.jpg');

            // Link đến trang chi tiết
            $serviceLink = route('site.services.show', $service->id);

            // Tạo booking params cho nút đặt lịch
            $bookingParams = [];
            if ($service->serviceVariants && $service->serviceVariants->count() > 0) {
                $defaultVariant = $service->serviceVariants->where('is_default', true)->first();
                if (!$defaultVariant) {
                    $defaultVariant = $service->serviceVariants->first();
                }
                if ($defaultVariant) {
                    $bookingParams['service_variants'] = [$defaultVariant->id];
                }
            } else {
                $bookingParams['service_id'] = [$service->id];
            }
          @endphp
          <div class="svc-card" style="position: relative;">
            <a class="svc-img" href="{{ $serviceLink }}" style="position: relative;">
              <img src="{{ $imagePath }}" alt="{{ $service->name }}">
              @if($serviceDiscount['discount'] > 0)
                <span style="position: absolute; top: 8px; right: 8px; background: #ff4444; color: #fff; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; z-index: 10; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">{{ $serviceDiscount['discountTag'] }}</span>
              @endif
            </a>
            <div class="svc-body">
              <div class="svc-left">
                <h4 class="svc-name">{{ $service->name }}</h4>
                <div class="svc-price" style="display: flex; flex-direction: column; gap: 3px;">
                  <div style="font-size: 11px; color: #666;">Giá từ:</div>
                  <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                    @if($serviceDiscount['discount'] > 0)
                      <span style="text-decoration: line-through; color: #999; font-size: 12px;">{{ $formattedOriginalPrice }}</span>
                      <span style="color: #BC9321; font-weight: 700; font-size: 14px;">{{ $formattedPrice }}</span>
                    @else
                      <span style="color: #BC9321; font-weight: 700; font-size: 14px;">{{ $formattedPrice }}</span>
                    @endif
                  </div>
                </div>
              </div>
              <div class="svc-right">
                <span class="svc-rating">5 ★ Đánh giá</span>
                <a class="svc-book" href="{{ route('site.appointment.create', $bookingParams) }}">Đặt lịch ngay</a>
              </div>
            </div>
          </div>
        @empty
          <div class="col-12 text-center py-5">
            <p>Chưa có dịch vụ nào.</p>
          </div>
        @endforelse
      </div>
      <div class="text-center mt-3"><a class="btn-view-all" href="{{ route('site.services.index') }}">Xem tất cả</a></div>
    </div>
  </div>
</section>

    <!-- 4 FEEDBACK KHÁCH HÀNG -->
<section class="feedback-section py-5">
  <div class="container">
    <div class="d-flex align-items-start mb-3">
      <span class="fb-bar mr-2"></span>
      <div>
        <h3 class="fb-title mb-1 ba-title mb-0">FEEDBACK KHÁCH HÀNG</h3>
        <p class="fb-desc mb-0">Dưới đây là những chia sẻ và cảm nhận của khách hàng khi sử dụng dịch vụ của POLY HAIR.</p>
      </div>
    </div>

    <div class="fb-grid">
      @foreach([
        'https://storage.30shine.com/web/v4/images/sao-toa-sang/240422/2.png',
        'https://storage.30shine.com/web/v4/images/sao-toa-sang/8.jpg',
        'https://storage.30shine.com/web/v4/images/sao-toa-sang/240422/8.png',
        'https://storage.30shine.com/web/v4/images/sao-toa-sang/7.jpg',
        'https://storage.30shine.com/web/v4/images/sao-toa-sang/5.jpg',
        'https://storage.30shine.com/web/v4/images/sao-toa-sang/240422/15.png',
      ] as $img)
        <a class="fb-card" ">
          <div class="fb-img"><img src="{{ $img }}" alt="Feedback"></div>
        </a>
      @endforeach
    </div>


</section>
<!-- ETRAKY’S STYLIST -->
 <section class="stylist-section py-5">
    <div class="container stylist-wrapper">
        <div class="stylist-left-wrapper">
            <div class="stylist-left">
                <!-- <div class="stylist-letter">T</div> -->
                <h2 class="stylist-title ba-title mb-0"> POLY'S STYLIST</h2>
                <p class="stylist-desc">
                    Chúng tôi tự hào sở hữu một đội ngũ nghệ sĩ tóc tài năng và có kinh nghiệm. Với sự đam mê sáng tạo
                    và kiến thức chuyên sâu về xu hướng làm đẹp, họ không chỉ biến ý tưởng của bạn thành hiện thực
                    mà còn mang lại sự tự tin và phong cách mới cho vẻ ngoại hình của bạn.
                </p>
            </div>


        </div>

        <div class="stylist-right">
            <div class="stylist-grid">
                @php
                    // Lấy danh sách nhân viên từ database
                    $allEmployees = \App\Models\Employee::with(['user.role'])
                        ->whereNotNull('user_id')
                        ->where('position', 'Stylist')
                        ->where('status', '!=', 'Vô hiệu hóa')
                        ->whereHas('user', function($query) {
                            $query->where('role_id', '!=', 1); // Loại trừ admin
                        })
                        ->orderBy('id', 'desc')
                        ->limit(4)
                        ->get();

                    // Ảnh mặc định giữ nguyên
                    $defaultImages = [
                        'https://trakyhairsalon.com/thumbs/375x500x1/upload/news/dsc01646-88180.jpg',
                        'https://trakyhairsalon.com/thumbs/375x500x1/upload/news/e21400bd6a6ece30977f-11410.jpg',
                        'https://trakyhairsalon.com/thumbs/375x500x1/upload/news/c2ddd31d-58b8-4d17-9263-a94a60c4f0ac-1616.jpeg',
                        'https://trakyhairsalon.com/thumbs/375x500x1/upload/news/dsc01326-1-8054.jpg',
                    ];
                @endphp
                @foreach($allEmployees as $index => $employee)
                    @php
                        $employeeName = $employee->user->name ?? 'Nhân viên';
                        // Lấy ảnh từ database nếu có, nếu không thì dùng ảnh mặc định
                        if ($employee->avatar) {
                            $employeeImage = asset('legacy/images/avatars/' . $employee->avatar);
                        } else {
                            $employeeImage = $defaultImages[$index] ?? $defaultImages[0];
                        }
                        
                        // Vị trí nhân viên
                        $position = $employee->position ?? '';
                        
                        // Lấy số năm kinh nghiệm
                        $experienceYears = $employee->experience_years ?? 0;
                    @endphp
                    <div class="stylist-card">
                        <div class="stylist-img">
                            <img src="{{ $employeeImage }}" alt="{{ $employeeName }}">
                        </div>
                        <div class="stylist-meta" style="display: flex !important; flex-direction: row !important; align-items: center !important; justify-content: space-between !important; gap: 12px !important; width: 100% !important;">
                            <div style="flex: 1; display: flex; flex-direction: column; gap: 4px;">
                                <h3 class="stylist-name" style="margin: 0 !important; display: block !important; width: 100% !important; font-size: 16px; font-weight: 600;">{{ $employeeName }}</h3>
                                <div style="font-size: 13px; color: #666; display: block !important; width: 100% !important; margin: 0 !important;">
                                    @php
                                        $infoParts = [];
                                        if($position) {
                                            $infoParts[] = $position;
                                        }
                                        if($experienceYears > 0) {
                                            $infoParts[] = $experienceYears . ' năm kinh nghiệm';
                                        }
                                    @endphp
                                    @if(!empty($infoParts))
                                        {!! implode(' . ', $infoParts) !!}
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('site.appointment.create', ['employee_id' => $employee->id]) }}" 
                               class="stylist-book" 
                               style="padding: 8px 12px; background: linear-gradient(135deg, #d8b26a 0%, #8b5a2b 100%); color: #000; font-weight: 700; border-radius: 999px; text-transform: uppercase; font-size: 12px; text-decoration: none; display: inline-block; flex-shrink: 0; white-space: nowrap;">
                                BookStylist ngay
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<!-- END TRAKY’S STYLIST -->

<!-- BEFORE & AFTER -->
 <section class="before-after-section py-5">
    <div class="container">
        <div class="d-flex align-items-center mb-3">
            <span class="ba-bar mr-2"></span>
            <h3 class="ba-title mb-0">BEFORE & AFTER</h3>
        </div>
        <p class="text-muted mb-4">
            Chúng ta thường xuyên nghe về sức mạnh của một kiểu tóc đẹp... trải nghiệm sự thay đổi tại đây.
        </p>

        <div class="ba-slider">
            <div class="ba-viewport">
                <div class="ba-track">
                   @foreach([
    [
        'title' => 'Kiểu Tóc UnderCut ngắn',
        'img'   => 'https://achau.vn/wp-content/uploads/2012/05/988273_406758706091200_754796503_n-390x520.jpg',
        'video' => 'https://www.youtube.com/watch?v=5VOz__vyEhs',
    ],
    [
        'title' => 'Nhuộm Line + Vuốt ngược',
        'img'   => 'https://achau.vn/wp-content/uploads/2014/09/11143342_1606728432899767_4218537422100364876_n-390x520.jpg',
        'video' => 'https://www.youtube.com/watch?v=p87x50eGm8E',
    ],
    [
        'title' => 'Kiểu tóc Top Knot Man',
        'img'   => 'https://achau.vn/wp-content/uploads/2019/05/29594958_1211960602240074_5705512853582689225_n-390x520.jpg',
        'video' => 'https://www.youtube.com/watch?v=woF99xC4qkM',
    ],
    [
        'title' => 'Kiểu UnderCut vuốt ngược',
        'img'   => 'https://achau.vn/wp-content/uploads/2016/03/11193417_135477886790117_6187735647423674751_n-464x618.jpg',
        'video' => 'https://www.youtube.com/watch?v=nqgvBN-PBwM',
    ],
] as $item)

                  <div class="ba-card" data-video="{{ $item['video'] }}">
    <div class="ba-img">
        <img src="{{ $item['img'] }}" alt="{{ $item['title'] }}">
    </div>
    <div class="ba-name">{{ $item['title'] }}</div>
</div>
@endforeach

                </div>
            </div>
        </div>

        <!-- <div class="text-center mt-4">
            <a href="#" class="btn-view-all">Xem tất cả</a>
        </div> -->
    </div>
</section>


<!-- San phảm chăm sóc tóc -->
<section class="product-section py-5">
  <div class="container">
    <div class="d-flex align-items-start mb-3">
      <span class="prod-bar mr-2"></span>
      <div>
        <h3 class="prod-title mb-1 ba-title mb-0">SẢN PHẨM CHĂM SÓC TÓC</h3>
        <p class="prod-desc mb-0">Chúng tôi cam kết đảm bảo sức khỏe và vẻ đẹp tự nhiên của tóc bạn thông qua những sản phẩm chăm sóc tóc chất lượng cao và tiên tiến.</p>
      </div>
    </div>

    <div class="prod-criteria mb-3">
      @foreach([
        ['name'=>'Giao Hàng Hỏa Tốc','desc'=>'Nhận hàng nhanh chóng trong thời gian ngắn nhất.','img'=>'https://trakyhairsalon.com/thumbs/60x60x2/upload/photo/tc1-42290.png'],
        ['name'=>'Hoàn Tiền 100%','desc'=>'Cam kết hoàn tiền nếu sản phẩm không đạt yêu cầu.','img'=>'https://trakyhairsalon.com/thumbs/60x60x2/upload/photo/tc2-14340.png'],
        ['name'=>'Chính Sách Đổi Trả','desc'=>'Đổi trả dễ dàng và nhanh chóng.','img'=>'https://trakyhairsalon.com/thumbs/60x60x2/upload/photo/tc3-80241.png'],
        ['name'=>'Cam Kết Chính Hãng','desc'=>'Sản phẩm 100% chính hãng, xuất xứ rõ ràng.','img'=>'https://trakyhairsalon.com/thumbs/60x60x2/upload/photo/tc4-60532.png'],
      ] as $tc)
      <div class="tc-card">
        <div class="tc-img"><img src="{{ $tc['img'] }}" alt="{{ $tc['name'] }}"></div>
        <div class="tc-text">
          <div class="tc-name">{{ $tc['name'] }}</div>
          <div class="tc-desc">{{ $tc['desc'] }}</div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>
<!-- End San phảm chăm sóc tóc -->
    <!-- cộng đồng -->
     <section class="community-section py-4">
  <div class="container">
    <div class="community-slider">
      <div class="comm-viewport">
        <div class="comm-track">
          @foreach([
            ['name'=>'Fanpage','count'=>'178.000+','img'=>'https://trakyhairsalon.com/thumbs/100x100x2/upload/photo/facebook-84181.png'],
            ['name'=>'Zalo','count'=>'79.000+','img'=>'https://trakyhairsalon.com/thumbs/100x100x2/upload/photo/zalo-82972.png'],
            ['name'=>'Tiktok channel','count'=>'634.000+','img'=>'https://trakyhairsalon.com/thumbs/100x100x2/upload/photo/tiktok-50110.png'],
            ['name'=>'Instagram','count'=>'140.000+','img'=>'https://trakyhairsalon.com/thumbs/100x100x2/upload/photo/instagram-89822.png'],
            ['name'=>'Chi nhánh','count'=>'63+','img'=>'https://trakyhairsalon.com/thumbs/100x100x2/upload/photo/maps-67360.png'],
          ] as $cd)
          <div class="comm-card">
            <div class="comm-img"><img src="{{ $cd['img'] }}" alt="{{ $cd['name'] }}"></div>
            <div class="comm-desc">{{ $cd['count'] }}</div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>
    <!-- end cộng đồng -->

@endsection

<style>
/* Ẩn dòng kẻ ngang DƯỚI phần DỊCH VỤ TÓC & COMBO */
.service-section > .container:first-of-type {
    border-bottom: none !important;
    border-top: none !important;
    padding-bottom: 0 !important;
    margin-bottom: 0 !important;
}
.service-section > .container:first-of-type > *,
.service-section > .container:first-of-type > * > *,
.service-section > .container:first-of-type > * > * > * {
    border-bottom: none !important;
    border-top: none !important;
}
.service-section > .container:first-of-type .d-flex {
    border-bottom: none !important;
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}
.service-section > .container:first-of-type .desc {
    border-bottom: none !important;
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}
.service-section > .container:first-of-type::after,
.service-section > .container:first-of-type::before,
.service-section > .container:first-of-type .d-flex::after,
.service-section > .container:first-of-type .d-flex::before,
.service-section > .container:first-of-type .desc::after,
.service-section > .container:first-of-type .desc::before {
    display: none !important;
    content: none !important;
}
/* Ẩn tất cả border trong container đầu tiên */
.service-section .container:first-child {
    border: none !important;
}
.service-section .container:first-child * {
    border-bottom: none !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const viewport = document.querySelector('.ba-viewport');
  const track = document.querySelector('.ba-track');
  const cards = Array.from(track.children);
  if (!cards.length) return;
  const gap = parseFloat(getComputedStyle(track).gap || 0);
  const step = () => cards[0].getBoundingClientRect().width + gap;

  let autoPlayInterval;
  let isPaused = false;

  const nextSlide = () => {
    const maxScroll = track.scrollWidth - viewport.clientWidth;
    const currentScroll = viewport.scrollLeft;

    if (currentScroll >= maxScroll - 10) {
      // Nếu đã đến cuối, quay về đầu
      viewport.scrollTo({ left: 0, behavior: 'smooth' });
    } else {
      // Chạy slide tiếp theo
      viewport.scrollBy({ left: step(), behavior: 'smooth' });
    }
  };

  const startAutoPlay = () => {
    if (autoPlayInterval) clearInterval(autoPlayInterval);
    autoPlayInterval = setInterval(() => {
      if (!isPaused) {
        nextSlide();
      }
    }, 4000); // Tự động chạy sau mỗi 4 giây
  };

  const stopAutoPlay = () => {
    if (autoPlayInterval) {
      clearInterval(autoPlayInterval);
      autoPlayInterval = null;
    }
  };


  // Tạm dừng khi hover vào slider
  const slider = document.querySelector('.ba-slider');
  if (slider) {
    slider.addEventListener('mouseenter', () => {
      isPaused = true;
    });
    slider.addEventListener('mouseleave', () => {
      isPaused = false;
    });
  }

  // click card mở video
  cards.forEach(c => c.addEventListener('click', () => {
    const url = c.dataset.video;
    if (url) window.open(url, '_blank');
  }));

  // Bắt đầu auto play
  startAutoPlay();
});
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const viewport = document.querySelector('.salon-viewport');
  const track = document.querySelector('.salon-track');
  const cards = Array.from(track.children);
  if (!cards.length) return;
  const gap = parseFloat(getComputedStyle(track).gap || 0);
  const step = () => cards[0].getBoundingClientRect().width + gap;

  document.querySelector('.salon-nav.prev').onclick = () => viewport.scrollBy({ left: -step(), behavior: 'smooth' });
  document.querySelector('.salon-nav.next').onclick = () => viewport.scrollBy({ left: step(), behavior: 'smooth' });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const viewport = document.querySelector('.partner-viewport');
  const track = document.querySelector('.partner-track');
  const cards = Array.from(track.children);
  if (!cards.length) return;
  const gap = parseFloat(getComputedStyle(track).gap || 0);
  const step = () => cards[0].getBoundingClientRect().width + gap;

  document.querySelector('.partner-nav.prev').onclick = () => viewport.scrollBy({ left: -step(), behavior: 'smooth' });
  document.querySelector('.partner-nav.next').onclick = () => viewport.scrollBy({ left: step(), behavior: 'smooth' });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const viewport = document.querySelector('.fb-viewport');
  const track = document.querySelector('.fb-track');
  const cards = Array.from(track.children);
  if (!cards.length) return;
  const gap = parseFloat(getComputedStyle(track).gap || 0);
  const step = () => cards[0].getBoundingClientRect().width + gap;

  document.querySelector('.fb-nav.prev').onclick = () => viewport.scrollBy({ left: -step(), behavior: 'smooth' });
  document.querySelector('.fb-nav.next').onclick = () => viewport.scrollBy({ left: step(), behavior: 'smooth' });
});
</script>
