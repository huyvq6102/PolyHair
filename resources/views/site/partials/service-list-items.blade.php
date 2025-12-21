@php
  // Helper function để tính discount
  function calculateDiscountForItem($item, $itemType, $activePromotions) {
    $originalPrice = $item['price'] ?? 0;
    $discount = 0;        // Mức giảm cao nhất tìm được
    $finalPrice = 0;      // Giá sau khi áp dụng mức giảm cao nhất
    $promotion = null;    // Khuyến mãi mang lại giảm giá cao nhất
    $discountTag = '';    // Badge hiển thị trên thẻ dịch vụ

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
      if ($promo->per_user_limit) {
        $userId = auth()->id();
        if ($userId) {
          $userUsage = \App\Models\PromotionUsage::where('promotion_id', $promo->id)
            ->where('user_id', $userId)
            ->count();
          if ($userUsage >= $promo->per_user_limit) {
            continue; // Skip this promotion, use original price
          }
        }
      }

      $applies = false;

      if ($itemType === 'service_single') {
        $hasSpecificServices = ($promo->services && $promo->services->count() > 0)
          || ($promo->combos && $promo->combos->count() > 0)
          || ($promo->serviceVariants && $promo->serviceVariants->count() > 0);
        $applyToAll = !$hasSpecificServices ||
          (($promo->services ? $promo->services->count() : 0) +
           ($promo->combos ? $promo->combos->count() : 0) +
           ($promo->serviceVariants ? $promo->serviceVariants->count() : 0)) >= 20;
        if ($promo->apply_scope === 'order' || $applyToAll) {
          $applies = true;
        } elseif (isset($item['id']) && $promo->services && $promo->services->contains('id', $item['id'])) {
          $applies = true;
        }
      } elseif ($itemType === 'service_variant') {
        $hasSpecificServices = ($promo->services && $promo->services->count() > 0)
          || ($promo->combos && $promo->combos->count() > 0)
          || ($promo->serviceVariants && $promo->serviceVariants->count() > 0);
        $applyToAll = !$hasSpecificServices ||
          (($promo->services ? $promo->services->count() : 0) +
           ($promo->combos ? $promo->combos->count() : 0) +
           ($promo->serviceVariants ? $promo->serviceVariants->count() : 0)) >= 20;
        if ($promo->apply_scope === 'order' || $applyToAll) {
          $applies = true;
        } elseif (isset($item['id']) && $promo->serviceVariants && $promo->serviceVariants->contains('id', $item['id'])) {
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
        if ($promo->apply_scope === 'order' || $applyToAll) {
          $applies = true;
        } elseif (isset($item['id']) && $promo->combos && $promo->combos->contains('id', $item['id'])) {
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
@forelse($items as $item)
  @php
    // Tính discount
    $itemDiscount = calculateDiscountForItem($item, $item['type'], $activePromotions ?? collect());
    $displayPrice = $itemDiscount['finalPrice'] > 0 ? $itemDiscount['finalPrice'] : $item['price'];
    
    // Format giá tiền
    $formattedPrice = number_format($displayPrice, 0, ',', '.') . 'vnđ';
    $formattedOriginalPrice = $itemDiscount['discount'] > 0 ? number_format($itemDiscount['originalPrice'], 0, ',', '.') . 'vnđ' : '';

    // Đường dẫn ảnh
    $imagePath = $item['image']
        ? asset('legacy/images/products/' . $item['image'])
        : asset('legacy/images/products/default.jpg');

    // Badge type
    if ($item['type'] == 'service_single') {
      $typeBadge = 'Dịch vụ lẻ';
      $typeClass = 'badge-primary';
    } elseif ($item['type'] == 'service_variant') {
      $typeBadge = 'Gói dịch vụ';
      $typeClass = 'badge-info';
    } else {
      $typeBadge = 'Combo';
      $typeClass = 'badge-success';
    }

    // Tạo booking params cho nút đặt lịch
    $bookingParams = [];
    $hasVariants = false;
    $variantsData = [];
    
    if ($item['type'] == 'combo') {
      $bookingParams['combo_id'] = [$item['id']];
    } elseif ($item['type'] == 'service_variant' && isset($item['serviceVariants']) && $item['serviceVariants']->count() > 0) {
      $hasVariants = true;
      // Lấy danh sách variants active để hiển thị trong modal
      $activeVariants = $item['serviceVariants']->where('is_active', true);
      if ($activeVariants->count() == 0) {
        $activeVariants = $item['serviceVariants'];
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
        
        $variantsData[] = [
          'id' => $variant->id,
          'name' => $variant->name,
          'price' => $variant->price,
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
    } elseif ($item['type'] == 'service_single') {
      $bookingParams['service_id'] = [$item['id']];
    }
  @endphp
  <div class="svc-card" style="position: relative;">
    <a class="svc-img" href="{{ $item['link'] }}" style="position: relative;">
      <img src="{{ $imagePath }}" alt="{{ $item['name'] }}">
      <span class="badge {{ $typeClass }} position-absolute" style="top: 10px; right: 10px; z-index: 5;">{{ $typeBadge }}</span>
      @if($itemDiscount['discount'] > 0)
        <span style="position: absolute; top: 8px; left: 8px; background: #ff4444; color: #fff; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; z-index: 10; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">{{ $itemDiscount['discountTag'] }}</span>
      @endif
    </a>
    <div class="svc-body">
      <div class="svc-left">
        <h4 class="svc-name">{{ $item['name'] }}</h4>
        <div class="svc-price" style="display: flex; flex-direction: column; gap: 3px;">
          <div style="font-size: 11px; color: #666;">Giá từ:</div>
          <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
            @if($itemDiscount['discount'] > 0)
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
             data-service-name="{{ $item['name'] }}"
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
    <p>Chưa có dịch vụ hoặc combo nào.</p>
  </div>
@endforelse
