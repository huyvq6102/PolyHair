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

