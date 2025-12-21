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
        ['name'=>'NÂU HOT TREND','link'=>'#','img'=>'https://storage.30shine.com/stylist-vote/14187_3/2.jpeg'],
        ['name'=>'XOĂN HÀN QUỐC','link'=>'#','img'=>'https://storage.30shine.com/stylist-vote/12822_1/2.jpeg'],
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
          // Helper function để tính discount - giống với trang dịch vụ
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

            foreach ($activePromotions ?? [] as $promo) {
              // Chỉ áp dụng giảm trực tiếp vào dịch vụ khi khuyến mãi được cấu hình "Theo dịch vụ"
              if ($promo->apply_scope !== 'service') {
                continue;
              }
              if ($promo->status !== 'active') continue;
              if ($promo->start_date && $promo->start_date > $now) continue;
              if ($promo->end_date && $promo->end_date < $now) continue;
              
              // Check usage_limit - if promotion has reached its limit, skip it
              if ($promo->usage_limit) {
                $totalUsage = \App\Models\PromotionUsage::where('promotion_id', $promo->id)->count();
                if ($totalUsage >= $promo->usage_limit) {
                  continue; // Skip this promotion, use original price
                }
              }
              
              // Check per_user_limit - if user has reached their limit, skip it
              // CHỈ đếm các PromotionUsage có appointment đã thanh toán
              if ($promo->per_user_limit) {
                $userId = auth()->id();
                if ($userId) {
                  $userUsage = \App\Models\PromotionUsage::where('promotion_id', $promo->id)
                    ->where('user_id', $userId)
                    ->whereHas('appointment', function($query) {
                        $query->where('status', 'Đã thanh toán');
                    })
                    ->count();
                  if ($userUsage >= $promo->per_user_limit) {
                    continue; // Skip this promotion, use original price
                  }
                }
              }

              $applies = false;

              if ($itemType === 'service') {
                $hasSpecificServices = ($promo->services && $promo->services->count() > 0)
                  || ($promo->combos && $promo->combos->count() > 0)
                  || ($promo->serviceVariants && $promo->serviceVariants->count() > 0);
                $applyToAll = !$hasSpecificServices ||
                  (($promo->services ? $promo->services->count() : 0) +
                   ($promo->combos ? $promo->combos->count() : 0) +
                   ($promo->serviceVariants ? $promo->serviceVariants->count() : 0)) >= 20;
                if ($applyToAll) {
                  $applies = true;
                } elseif ($promo->services && $promo->services->contains('id', $item->id)) {
                  $applies = true;
                }
              } elseif ($itemType === 'variant') {
                $hasSpecificServices = ($promo->services && $promo->services->count() > 0)
                  || ($promo->combos && $promo->combos->count() > 0)
                  || ($promo->serviceVariants && $promo->serviceVariants->count() > 0);
                $applyToAll = !$hasSpecificServices ||
                  (($promo->services ? $promo->services->count() : 0) +
                   ($promo->combos ? $promo->combos->count() : 0) +
                   ($promo->serviceVariants ? $promo->serviceVariants->count() : 0)) >= 20;
                if ($applyToAll) {
                  $applies = true;
                } elseif ($promo->serviceVariants && $promo->serviceVariants->contains('id', $item->id)) {
                  $applies = true;
                }
              } elseif ($itemType === 'combo') {
                $hasSpecificServices = ($promo->services && $promo->services->count() > 0)
                  || ($promo->combos && $promo->combos->count() > 0)
                  || ($promo->serviceVariants && $promo->serviceVariants->count() > 0);
                $applyToAll = !$hasSpecificServices ||
                  (($promo->services ? $promo->services->count() : 0) +
                   ($promo->combos ? $promo->combos->count() : 0) +
                   ($promo->serviceVariants ? $promo->serviceVariants->count() : 0)) >= 20;
                if ($applyToAll) {
                  $applies = true;
                } elseif ($promo->combos && $promo->combos->contains('id', $item->id)) {
                  $applies = true;
                }
              }

              if ($applies) {
                // Tính mức giảm cho promo hiện tại
                $currentDiscount = 0;
                $currentTag = '';

                if ($promo->discount_type === 'percent') {
                  $currentDiscount = ($originalPrice * ($promo->discount_percent ?? 0)) / 100;
                  if ($promo->max_discount_amount) {
                    $currentDiscount = min($currentDiscount, $promo->max_discount_amount);
                  }
                  $currentTag = '-' . ($promo->discount_percent ?? 0) . '%';
                } else {
                  $currentDiscount = min($promo->discount_amount ?? 0, $originalPrice);
                  $currentTag = '-' . number_format($currentDiscount / 1000, 0) . 'k';
                }

                // Ưu tiên khuyến mãi cho mức giảm tiền nhiều nhất
                if ($currentDiscount > $discount) {
                  $discount = $currentDiscount;
                  $promotion = $promo;
                  $discountTag = $currentTag;
                }
              }
            }

            $finalPrice = max(0, $originalPrice - $discount);

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
            // Tính discount cho từng variant và lấy giá tốt nhất (giá sau discount thấp nhất)
            $bestPrice = null;
            $bestDiscount = null;
            $bestOriginalPrice = null;
            
            if ($service->serviceVariants && $service->serviceVariants->count() > 0) {
              // Nếu có variants, tính discount cho từng variant và lấy giá tốt nhất
              foreach ($service->serviceVariants->where('is_active', true) as $variant) {
                $variantDiscount = calculateDiscountForService($variant, 'variant', $activePromotions ?? collect());
                $variantFinalPrice = $variantDiscount['finalPrice'] > 0 ? $variantDiscount['finalPrice'] : $variant->price;
                
                if ($bestPrice === null || $variantFinalPrice < $bestPrice) {
                  $bestPrice = $variantFinalPrice;
                  $bestDiscount = $variantDiscount;
                  $bestOriginalPrice = $variantDiscount['originalPrice'];
                }
              }
              
              // Nếu không có variant active, lấy từ tất cả variants
              if ($bestPrice === null) {
                foreach ($service->serviceVariants as $variant) {
                  $variantDiscount = calculateDiscountForService($variant, 'variant', $activePromotions ?? collect());
                  $variantFinalPrice = $variantDiscount['finalPrice'] > 0 ? $variantDiscount['finalPrice'] : $variant->price;
                  
                  if ($bestPrice === null || $variantFinalPrice < $bestPrice) {
                    $bestPrice = $variantFinalPrice;
                    $bestDiscount = $variantDiscount;
                    $bestOriginalPrice = $variantDiscount['originalPrice'];
                  }
                }
              }
            } else {
              // Nếu không có variant, tính discount cho service
              $serviceDiscount = calculateDiscountForService($service, 'service', $activePromotions ?? collect());
              $bestPrice = $serviceDiscount['finalPrice'] > 0 ? $serviceDiscount['finalPrice'] : ($service->base_price ?? 0);
              $bestDiscount = $serviceDiscount;
              $bestOriginalPrice = $serviceDiscount['originalPrice'];
            }
            
            // Fallback nếu không có giá
            if ($bestPrice === null) {
              $bestPrice = $service->base_price ?? 0;
              $bestDiscount = ['discount' => 0, 'discountTag' => '', 'originalPrice' => $bestPrice];
              $bestOriginalPrice = $bestPrice;
            }
            
            $displayPrice = $bestPrice;
            $serviceDiscount = $bestDiscount;

            // Format giá tiền
            $formattedPrice = number_format($displayPrice, 0, ',', '.') . 'vnđ';
            $formattedOriginalPrice = ($serviceDiscount['discount'] ?? 0) > 0 ? number_format($bestOriginalPrice ?? $serviceDiscount['originalPrice'] ?? 0, 0, ',', '.') . 'vnđ' : '';

            // Đường dẫn ảnh
            $imagePath = $service->image
                ? asset('legacy/images/products/' . $service->image)
                : asset('legacy/images/products/default.jpg');

            // Link đến trang chi tiết
            $serviceLink = route('site.services.show', $service->id);

            // Tạo booking params cho nút đặt lịch
            $bookingParams = [];
            $hasVariants = false;
            $variantsData = [];
            
            if ($service->serviceVariants && $service->serviceVariants->count() > 0) {
                $hasVariants = true;
                // Lấy danh sách variants active để hiển thị trong modal
                $activeVariants = $service->serviceVariants->where('is_active', true);
                if ($activeVariants->count() == 0) {
                    $activeVariants = $service->serviceVariants;
                }
                foreach ($activeVariants as $variant) {
                    // Load variant attributes if not already loaded
                    if (!$variant->relationLoaded('variantAttributes')) {
                        $variant->load('variantAttributes');
                    }
                    
                    $attributes = [];
                    foreach ($variant->variantAttributes as $attr) {
                        $attributes[] = [
                            'name' => $attr->attribute_name,
                            'value' => $attr->attribute_value,
                        ];
                    }
                    
                    // Tính discount cho variant này
                    $variantDiscount = calculateDiscountForService($variant, 'variant', $activePromotions ?? collect());
                    
                    $variantsData[] = [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'price' => $variant->price, // Giá gốc
                        'originalPrice' => $variantDiscount['originalPrice'], // Giá gốc (để đảm bảo)
                        'finalPrice' => $variantDiscount['finalPrice'], // Giá đã giảm
                        'discount' => $variantDiscount['discount'], // Số tiền giảm
                        'discountTag' => $variantDiscount['discountTag'], // Badge giảm giá
                        'duration' => $variant->duration,
                        'is_default' => $variant->is_default ?? false,
                        'attributes' => $attributes,
                        'notes' => $variant->notes ?? null,
                    ];
                }
                // Nếu chỉ có 1 variant, không cần modal, redirect trực tiếp
                if ($activeVariants->count() == 1) {
                    $hasVariants = false;
                    $bookingParams['service_variants'] = [$activeVariants->first()->id];
                }
            } else {
                $bookingParams['service_id'] = [$service->id];
            }
          @endphp
          <div class="svc-card" style="position: relative;">
            <a class="svc-img" href="{{ $serviceLink }}" style="position: relative;">
              <img src="{{ $imagePath }}" alt="{{ $service->name }}">
              @if($serviceDiscount['discount'] > 0)
                <span style="position: absolute; top: 8px; left: 8px; background: #ff4444; color: #fff; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; z-index: 10; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">{{ $serviceDiscount['discountTag'] }}</span>
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
                @if($hasVariants)
                  <a class="svc-book select-variant-btn" 
                     href="#" 
                     data-service-name="{{ $service->name }}"
                     data-variants="{{ json_encode($variantsData) }}"
                     onclick="event.preventDefault(); openVariantModal(this);">
                    Đặt lịch ngay
                  </a>
                @else
                  <a class="svc-book" href="{{ route('site.appointment.create', $bookingParams) }}">Đặt lịch ngay</a>
                @endif
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
                    // Lấy danh sách nhân viên từ database, sắp xếp theo số năm kinh nghiệm giảm dần
                    $allEmployees = \App\Models\Employee::with(['user.role'])
                        ->whereNotNull('user_id')
                        ->where('position', 'Stylist')
                        ->where('status', '!=', 'Vô hiệu hóa')
                        ->whereHas('user', function($query) {
                            $query->where('role_id', '!=', 1); // Loại trừ admin
                        })
                        ->orderBy('experience_years', 'desc')
                        ->orderBy('id', 'desc') // Nếu cùng số năm kinh nghiệm thì sắp xếp theo id
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

    <!-- 4 FEEDBACK KHÁCH HÀNG -->
<section class="feedback-section py-5">
  <div class="container">
    <div class="d-flex align-items-start mb-3">
      <span class="fb-bar mr-2"></span>
      <div>
        <h3 class="fb-title mb-1 ba-title mb-0">CÙNG SAO TỎA SÁNG</h3>
        <p class="fb-desc mb-0">Đồng hành cùng Sao - Sẵn sàng tỏa sáng</p>
      </div>
    </div>

    <div class="fb-grid">
      @foreach([
        ['img' => 'https://storage.30shine.com/web/v4/images/sao-toa-sang/240422/2.png', 'name' => 'Dương Gió Tai', 'info' => 'Hot tiktoker Việt Nam'],
        ['img' => 'https://storage.30shine.com/web/v4/images/sao-toa-sang/8.jpg', 'name' => 'Diễn viên Bình An', 'info' => 'Diễn viên điện ảnh Việt Nam'],
        ['img' => 'https://storage.30shine.com/web/v4/images/sao-toa-sang/2.jpg', 'name' => 'Đỗ Kim Phúc', 'info' => 'Nhà Vô Địch tâng bóng nghệ thuật'],
        ['img' => 'https://storage.30shine.com/web/v4/images/sao-toa-sang/7.jpg', 'name' => 'Văn Thanh - Hồng Duy', 'info' => 'Đội tuyển Quốc gia Việt Nam'],
        ['img' => 'https://storage.30shine.com/web/v4/images/sao-toa-sang/5.jpg', 'name' => 'Hồ Tấn Tài', 'info' => 'Đội tuyển Quốc gia Việt Nam'],
        ['img' => 'https://storage.30shine.com/web/v4/images/sao-toa-sang/240422/15.png', 'name' => 'Sơn Đú', 'info' => 'Hot tiktoker Việt Nam'],
      ] as $item)
        <div class="fb-card">
          <div class="fb-img"><img src="{{ $item['img'] }}" alt="Feedback"></div>
          <div class="fb-meta" style="padding: 12px; display: flex; flex-direction: column; gap: 4px;">
            <h3 class="fb-name" style="margin: 0 !important; display: block !important; width: 100% !important; font-size: 16px; font-weight: 600; color: #000;">{{ $item['name'] }}</h3>
            <div style="font-size: 13px; color: #666; display: block !important; width: 100% !important; margin: 0 !important;">
              {{ $item['info'] }}
            </div>
          </div>
        </div>
      @endforeach
    </div>


</section>


<!-- SHINE COLLECTION -->
<section class="shine-collection-section py-5">
    <div class="container">
        <div class="d-flex align-items-start mb-4">
            <span class="shine-bar mr-2"></span>
            <div>
                <h3 class="shine-title ba-title mb-0">POLY COLLECTION - 'VIBE' NÀO CŨNG TOẢ SÁNG</h3>
            </div>
        </div>

        <!-- Hero Banner -->
        <div class="shine-hero-banner">
            <div class="shine-hero-bg">
                <img src="https://storage.30shine.com/web/v4/images/shine-bright/shine-bright_mobile.png" alt="SHINE BRIGHT">
            </div>
            <div class="shine-cloud cloud-left"></div>
            <div class="shine-cloud cloud-right"></div>
        </div>

        <!-- Collection Cards Grid -->
        <div class="shine-collections-grid">
            <div class="shine-collection-card">
                <div class="shine-card-img">
                    <img src="https://storage.30shine.com/web/v4/images/shine-collection/mobile/pc_04.jpg" alt="ANH TRAI SAY HAIR">
                </div>
        
            </div>

            <div class="shine-collection-card">
                <div class="shine-card-img">
                    <img src="https://storage.30shine.com/web/v4/images/shine-collection/mobile/pc_03.jpg" alt="BTS K-PERM">
                </div>

            </div>

            <div class="shine-collection-card">
                <div class="shine-card-img">
                    <img src="https://storage.30shine.com/web/v4/images/shine-collection/mobile/pc_02.jpg" alt="BAD BOY">
                </div>

            </div>
        </div>
    </div>
</section>



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

<!-- Modal chọn variant -->
<div class="modal fade" id="variantSelectionModal" tabindex="-1" role="dialog" aria-labelledby="variantSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 600px;">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
            <div class="modal-header" style="border-bottom: 1px solid #e5e5e5; padding: 20px 24px; border-radius: 16px 16px 0 0;">
                <h5 class="modal-title" id="variantSelectionModalLabel" style="font-size: 20px; font-weight: 700; color: #333; margin: 0;">
                    Chọn gói dịch vụ
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="closeVariantModal()" style="border: none; background: none; font-size: 28px; color: #999; opacity: 0.7; cursor: pointer; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 24px;">
                <p class="service-name-display" style="font-size: 16px; color: #666; margin-bottom: 20px; font-weight: 600;"></p>
                <div class="variants-list" style="display: flex; flex-direction: column; gap: 12px;">
                    <!-- Variants will be inserted here -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.variant-option {
    border: 2px solid #e5e5e5;
    border-radius: 12px;
    padding: 18px 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #fff;
    position: relative;
}

.variant-option:hover {
    border-color: #d8b26a;
    background: #fefbf5;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(216, 178, 106, 0.12);
}

.variant-option.selected {
    border-color: #d8b26a;
    border-width: 2px;
    background: #fef9f0;
    box-shadow: 0 2px 12px rgba(216, 178, 106, 0.2);
}

.variant-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}

.variant-name {
    font-size: 16px;
    font-weight: 700;
    color: #333;
    flex: 1;
    margin-right: 12px;
    line-height: 1.4;
}

.variant-price-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
}

.variant-price {
    font-size: 18px;
    font-weight: 700;
    color: #BC9321;
    white-space: nowrap;
}

.variant-checkmark {
    display: none;
    width: 22px;
    height: 22px;
    background: linear-gradient(135deg, #d8b26a 0%, #8b5a2b 100%);
    color: #fff;
    border-radius: 50%;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    line-height: 1;
}

.variant-option.selected .variant-checkmark {
    display: flex;
}

.variant-duration {
    font-size: 13px;
    color: #999;
    margin-top: 4px;
}

.variant-default-badge {
    display: inline-block;
    background: linear-gradient(135deg, #d8b26a 0%, #8b5a2b 100%);
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 4px;
    margin-left: 8px;
    text-transform: uppercase;
}

#variantSelectionModal .modal-content {
    overflow: hidden;
}

#variantSelectionModal .close:hover {
    opacity: 1;
    color: #333;
}

#variantSelectionModal .modal-header {
    border-bottom: 1px solid #e5e5e5;
}

#variantSelectionModal .service-name-display {
    font-size: 18px;
    font-weight: 700;
    color: #333;
    margin-bottom: 20px;
}

.variant-option.selected .variant-attr-badge {
    background: #fff !important;
    border-color: #d8b26a !important;
    color: #333 !important;
}

.variant-option.selected .variant-notes {
    background: #fff !important;
    border-left-color: #d8b26a !important;
}
</style>

<script>
function openVariantModal(button) {
    const serviceName = button.getAttribute('data-service-name');
    const variantsJson = button.getAttribute('data-variants');
    const variants = JSON.parse(variantsJson);
    
    // Set service name
    document.querySelector('.service-name-display').textContent = serviceName;
    
    // Clear previous variants
    const variantsList = document.querySelector('.variants-list');
    variantsList.innerHTML = '';
    
    // Add variants
    variants.forEach((variant, index) => {
        const variantOption = document.createElement('div');
        variantOption.className = 'variant-option';
        variantOption.dataset.variantId = variant.id;
        
        // Tính giá hiển thị - sử dụng finalPrice nếu có discount, nếu không thì dùng price
        const displayPrice = variant.finalPrice || variant.price;
        const originalPrice = variant.originalPrice || variant.price;
        const hasDiscount = variant.discount && variant.discount > 0;
        
        const formattedPrice = new Intl.NumberFormat('vi-VN').format(displayPrice) + 'vnđ';
        const formattedOriginalPrice = hasDiscount ? new Intl.NumberFormat('vi-VN').format(originalPrice) + 'vnđ' : '';
        const durationText = variant.duration ? `Thời gian: ${variant.duration} phút` : '';
        
        // Build discount badge HTML
        let discountBadgeHTML = '';
        if (hasDiscount && variant.discountTag) {
            discountBadgeHTML = `<span style="position: absolute; top: 8px; right: 8px; background: linear-gradient(135deg, #ff4444 0%, #cc0000 100%); color: #fff; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; z-index: 10; box-shadow: 0 2px 6px rgba(255, 68, 68, 0.3); letter-spacing: 0.3px; white-space: nowrap;">${variant.discountTag}</span>`;
        }
        
        // Build price HTML - hiển thị giá gốc (strikethrough) và giá sau discount
        let priceHTML = '';
        if (hasDiscount) {
            priceHTML = `
                <div class="variant-price-wrapper" style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="text-decoration: line-through; color: #999; font-size: 13px;">${formattedOriginalPrice}</span>
                        <span class="variant-price">${formattedPrice}</span>
                    </div>
                    <span class="variant-checkmark">✓</span>
                </div>
            `;
        } else {
            priceHTML = `
                <div class="variant-price-wrapper">
                    <span class="variant-price">${formattedPrice}</span>
                    <span class="variant-checkmark">✓</span>
                </div>
            `;
        }
        
        // Build attributes HTML
        let attributesHTML = '';
        if (variant.attributes && variant.attributes.length > 0) {
            attributesHTML = '<div class="variant-attributes" style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 8px;">';
            variant.attributes.forEach(attr => {
                attributesHTML += `<span class="variant-attr-badge" style="display: inline-block; background: #f5f5f5; color: #666; font-size: 12px; padding: 4px 10px; border-radius: 6px; border: 1px solid #e5e5e5;">
                    <strong style="color: #333;">${attr.name}:</strong> ${attr.value}
                </span>`;
            });
            attributesHTML += '</div>';
        }
        
        // Build notes HTML
        let notesHTML = '';
        if (variant.notes) {
            notesHTML = `<div class="variant-notes" style="margin-top: 8px; font-size: 13px; color: #666; font-style: italic; padding: 8px; background: #f9f9f9; border-radius: 6px; border-left: 3px solid #d8b26a;">
                ${variant.notes}
            </div>`;
        }
        
        variantOption.innerHTML = `
            <div style="position: relative;">
                ${discountBadgeHTML}
                <div class="variant-header">
                    <div style="flex: 1;">
                        <span class="variant-name">${variant.name}</span>
                        ${variant.is_default ? '<span class="variant-default-badge">Mặc định</span>' : ''}
                    </div>
                    ${priceHTML}
                </div>
                ${durationText ? `<div class="variant-duration">${durationText}</div>` : ''}
                ${attributesHTML}
                ${notesHTML}
            </div>
        `;
        
        // Click handler
        variantOption.addEventListener('click', function() {
            // Remove selected class from all
            document.querySelectorAll('.variant-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // Add selected class to clicked
            this.classList.add('selected');
            
            // Enable continue button
            const continueBtn = document.getElementById('continueBookingBtn');
            if (continueBtn) {
                continueBtn.disabled = false;
                continueBtn.style.opacity = '1';
                continueBtn.style.cursor = 'pointer';
            }
        });
        
        variantsList.appendChild(variantOption);
        
        // Select first variant by default
        if (index === 0) {
            variantOption.click();
        }
    });
    
    // Show modal
    $('#variantSelectionModal').modal('show');
}

// Function to close modal
function closeVariantModal() {
    // Try Bootstrap modal first
    if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
        jQuery('#variantSelectionModal').modal('hide');
    } else {
        // Fallback: manually hide modal
        const modal = document.getElementById('variantSelectionModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
            document.body.classList.remove('modal-open');
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }
    }
}

// Handle continue button
document.addEventListener('DOMContentLoaded', function() {
    // Create continue button if not exists
    let continueBtn = document.getElementById('continueBookingBtn');
    if (!continueBtn) {
        const modalBody = document.querySelector('#variantSelectionModal .modal-body');
        continueBtn = document.createElement('button');
        continueBtn.id = 'continueBookingBtn';
        continueBtn.className = 'btn btn-primary btn-block';
        continueBtn.style.cssText = 'margin-top: 20px; padding: 12px 24px; font-size: 16px; font-weight: 700; border-radius: 8px; background: linear-gradient(135deg, #d8b26a 0%, #8b5a2b 100%); border: none; color: #fff; transition: all 0.3s ease;';
        continueBtn.textContent = 'Tiếp tục đặt lịch';
        continueBtn.disabled = true;
        continueBtn.style.opacity = '0.5';
        continueBtn.style.cursor = 'not-allowed';
        
        continueBtn.addEventListener('click', function() {
            const selectedVariant = document.querySelector('.variant-option.selected');
            if (selectedVariant) {
                const variantId = selectedVariant.dataset.variantId;
                const bookingUrl = '{{ route("site.appointment.create") }}?service_variants[]=' + variantId;
                window.location.href = bookingUrl;
            }
        });
        
        modalBody.appendChild(continueBtn);
    }
    
    // Reset modal when closed
    $('#variantSelectionModal').on('hidden.bs.modal', function() {
        document.querySelectorAll('.variant-option').forEach(opt => {
            opt.classList.remove('selected');
        });
        if (continueBtn) {
            continueBtn.disabled = true;
            continueBtn.style.opacity = '0.5';
            continueBtn.style.cursor = 'not-allowed';
        }
    });
    
    // Add click handler for close button (backup)
    const closeBtn = document.querySelector('#variantSelectionModal .close');
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            closeVariantModal();
        });
    }
    
    // Close modal when clicking outside (on backdrop)
    const modal = document.getElementById('variantSelectionModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeVariantModal();
            }
        });
    }
});
</script>

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

/* ==================================== SHINE COLLECTION ================================ */
.shine-collection-section {
    background: #fff;
}

.shine-bar {
    display: inline-block;
    width: 10px;
    height: 28px;
    background: linear-gradient(135deg, #f6d17a 0%, #d8b26a 50%, #8b5a2b 100%);
    border-radius: 2px;
}

.shine-title {
    font-size: 24px;
    font-weight: 800;
    text-transform: uppercase;
    margin-left: 8px;
}

/* Hero Banner */
.shine-hero-banner {
    position: relative;
    width: 100%;
    border-radius: 24px;
    overflow: hidden;
    margin-bottom: 24px;
    min-height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.shine-hero-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}

.shine-hero-bg img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.shine-hero-overlay {
    position: relative;
    z-index: 2;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 40px;
    text-align: center;
}

.shine-hero-top {
    display: flex;
    justify-content: space-between;
    width: 100%;
    margin-bottom: 20px;
    padding: 0 20px;
}

.shine-hero-label {
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.shine-hero-main {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.shine-hero-title {
    font-size: 72px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 4px;
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 30%, #d97706 60%, #b45309 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-shadow: 0 4px 20px rgba(251, 191, 36, 0.3);
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
    margin: 0;
}

/* Decorative Clouds */
.shine-cloud {
    position: absolute;
    z-index: 3;
    width: 200px;
    height: 150px;
    opacity: 0.6;
    pointer-events: none;
}

.cloud-left {
    left: -50px;
    top: 50%;
    transform: translateY(-50%);
    background: radial-gradient(ellipse at center, rgba(251, 191, 36, 0.3) 0%, transparent 70%);
    border-radius: 50%;
    filter: blur(20px);
}

.cloud-right {
    right: -50px;
    top: 50%;
    transform: translateY(-50%);
    background: radial-gradient(ellipse at center, rgba(251, 191, 36, 0.3) 0%, transparent 70%);
    border-radius: 50%;
    filter: blur(20px);
}

/* Collections Grid */
.shine-collections-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.shine-collection-card {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    aspect-ratio: 1;
}

.shine-collection-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.shine-card-img {
    width: 100%;
    height: 100%;
    position: relative;
    overflow: hidden;
}

.shine-card-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.shine-collection-card:hover .shine-card-img img {
    transform: scale(1.1);
}

.shine-card-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 20px;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.4) 50%, transparent 100%);
    color: #fff;
}

.shine-card-label {
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
    opacity: 0.9;
}

.shine-card-title {
    font-size: 24px;
    font-weight: 700;
    text-transform: uppercase;
    line-height: 1.2;
    margin: 0;
}

.shine-card-subtitle {
    font-size: 16px;
    font-weight: 400;
    font-style: italic;
    margin-top: 4px;
    opacity: 0.95;
}

/* Responsive */
@media (max-width: 991px) {
    .shine-hero-title {
        font-size: 48px;
    }

    .shine-collections-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .shine-hero-top {
        flex-direction: column;
        gap: 10px;
        align-items: center;
    }
}

@media (max-width: 767px) {
    .shine-hero-title {
        font-size: 36px;
        letter-spacing: 2px;
    }

    .shine-collections-grid {
        grid-template-columns: 1fr;
    }

    .shine-hero-banner {
        min-height: 300px;
    }

    .shine-cloud {
        width: 120px;
        height: 90px;
    }

    .cloud-left {
        left: -30px;
    }

    .cloud-right {
        right: -30px;
    }
}
/* ==================================== End SHINE COLLECTION ================================ */
</style>

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
