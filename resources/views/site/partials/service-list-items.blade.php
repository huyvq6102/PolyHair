@php
  // Helper function để tính discount
  function calculateDiscountForItem($item, $itemType, $activePromotions) {
    $originalPrice = $item['price'] ?? 0;
    $discount = 0;
    $finalPrice = 0;
    $promotion = null;
    $discountTag = '';

    if ($originalPrice <= 0) {
      return ['originalPrice' => 0, 'discount' => 0, 'finalPrice' => 0, 'promotion' => null, 'discountTag' => ''];
    }

    $now = \Carbon\Carbon::now();

    foreach ($activePromotions ?? [] as $promo) {
      if ($promo->status !== 'active') continue;
      if ($promo->start_date && $promo->start_date > $now) continue;
      if ($promo->end_date && $promo->end_date < $now) continue;

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
        <a class="svc-book" href="{{ route('site.appointment.create') }}">Đặt lịch ngay</a>
      </div>
    </div>
  </div>
@empty
  <div class="col-12 text-center py-5">
    <p>Chưa có dịch vụ hoặc combo nào.</p>
  </div>
@endforelse
