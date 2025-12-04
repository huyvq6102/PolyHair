@extends('layouts.site')

@section('title', 'Lịch hẹn')

@push('styles')
<style>
    .cart-section {
        padding: 140px 0 60px;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        min-height: 70vh;
    }
    
    .cart-container {
        display: block;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .cart-left {
        width: 100%;
        background: #fff;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    
    .cart-title {
        font-size: 22px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 3px solid #4A3600;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .cart-title::before {
        content: "📅";
        font-size: 24px;
    }
    
    .cart-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 15px;
        background: transparent;
    }
    
    .cart-table thead {
        background: transparent;
    }
    
    .cart-table thead tr {
        background: transparent;
    }
    
    .cart-table th {
        padding: 12px 10px;
        text-align: center;
        font-weight: 600;
        color: #333;
        border: none;
        font-size: 13px;
        white-space: nowrap;
        background: transparent;
    }
    
    .cart-table tbody tr {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.2s ease;
    }
    
    .cart-table tbody tr:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }
    
    .cart-table td {
        padding: 20px 15px;
        border: none;
        vertical-align: middle;
        font-size: 13px;
        text-align: center;
        background: transparent;
    }
    
    .cart-table tbody tr td:first-child {
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
        text-align: left;
    }
    
    .cart-table tbody tr td:last-child {
        border-top-right-radius: 12px;
        border-bottom-right-radius: 12px;
    }
    
    .cart-table td:nth-child(4) {
        text-align: left;
    }
    
    .service-items {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .service-item {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .service-image {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e8ecef;
        flex-shrink: 0;
    }
    
    .service-name {
        font-size: 13px;
        color: #333;
        line-height: 1.4;
    }
    
    .cart-table tbody tr {
        transition: all 0.2s ease;
    }
    
    .cart-table tbody tr:hover {
        background: #f8f9fa;
    }
    
    .cart-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .product-info-wrapper {
        display: flex;
        align-items: flex-start;
        gap: 15px;
    }
    
    .product-image-wrapper {
        flex-shrink: 0;
    }
    
    .product-details {
        flex: 1;
    }
    
    .appointment-info-list {
        margin-top: 10px;
        margin-bottom: 10px;
    }
    
    .info-row {
        display: flex;
        align-items: flex-start;
        margin-bottom: 6px;
        font-size: 13px;
        line-height: 1.5;
    }
    
    .info-label {
        font-weight: 600;
        color: #495057;
        min-width: 90px;
        flex-shrink: 0;
    }
    
    .info-text {
        color: #333;
        flex: 1;
    }
    
    .delete-link {
        color: #fff;
        background: #dc3545;
        text-decoration: none;
        font-size: 13px;
        padding: 8px 16px;
        border-radius: 6px;
        display: inline-block;
        transition: all 0.3s;
        font-weight: 500;
        border: 1px solid #dc3545;
    }
    
    .delete-link:hover {
        background: #c82333;
        border-color: #bd2130;
        color: #fff;
        text-decoration: none;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
    }
    
    .unit-price,
    .total-price {
        text-align: center;
    }
    
    .price-value {
        color: #28a745;
        font-weight: 600;
        font-size: 15px;
    }
    
    .product-image {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #e8ecef;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .product-name {
        font-size: 15px;
        color: #2c3e50;
        line-height: 1.4;
        font-weight: 600;
        margin-bottom: 8px;
    }
    
    .quantity-control {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0;
    }
    
    .quantity-btn {
        width: 35px;
        height: 35px;
        border: 1px solid #dee2e6;
        background: #fff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: #333;
        transition: all 0.2s;
        font-weight: 500;
    }
    
    .quantity-btn.minus-btn {
        border-radius: 4px 0 0 4px;
        border-right: none;
    }
    
    .quantity-btn.plus-btn {
        border-radius: 0 4px 4px 0;
        border-left: none;
    }
    
    .quantity-btn:hover {
        background: #4A3600;
        border-color: #4A3600;
        color: #fff;
    }
    
    .quantity-input {
        width: 60px;
        height: 35px;
        text-align: center;
        border: 1px solid #dee2e6;
        border-left: none;
        border-right: none;
        font-size: 14px;
        font-weight: 500;
        -moz-appearance: textfield;
    }
    
    .quantity-input::-webkit-outer-spin-button,
    .quantity-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    
    .product-price {
        font-size: 15px;
        font-weight: 700;
        color: #dc3545;
    }
    
    .delete-btn {
        padding: 6px 14px;
        border-radius: 6px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        cursor: pointer;
        color: #6c757d;
        transition: all 0.2s;
        font-size: 12px;
        font-weight: 500;
    }
    
    .delete-btn:hover {
        background: #dc3545;
        border-color: #dc3545;
        color: #fff;
        transform: scale(1.05);
    }
    
    .cart-total {
        margin-top: 20px;
        padding: 18px 20px;
        background: linear-gradient(135deg, #4A3600 0%, #6B4E1F 100%);
        border-radius: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 12px rgba(74, 54, 0, 0.2);
    }
    
    .total-label {
        font-size: 18px;
        font-weight: 600;
        color: #fff;
    }
    
    .total-value {
        font-size: 26px;
        font-weight: 700;
        color: #fff;
    }
    
    .appointment-details {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-radius: 10px;
        padding: 16px;
        margin-top: 12px;
        border-left: 4px solid #4A3600;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .appointment-details-title {
        font-size: 14px;
        font-weight: 700;
        color: #4A3600;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .appointment-details-title i {
        color: #BC9321;
        font-size: 16px;
    }
    
    .appointment-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px;
        margin-top: 12px;
    }
    
    .info-item-box {
        background: #fff;
        border: 1px solid #e8ecef;
        border-radius: 8px;
        padding: 10px 12px;
        border-left: 3px solid #4A3600;
        transition: all 0.2s;
    }
    
    .info-item-box:hover {
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        transform: translateY(-1px);
    }
    
    .info-item-label {
        font-size: 10px;
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .info-item-value {
        font-size: 13px;
        color: #2c3e50;
        font-weight: 600;
        line-height: 1.4;
    }
    
    .info-item-value.status {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 500;
    }
    
    .service-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .service-list li {
        padding: 4px 0;
        font-size: 12px;
        color: #495057;
        line-height: 1.5;
    }
    
    .service-list li:before {
        content: "✓ ";
        color: #28a745;
        font-weight: bold;
        margin-right: 6px;
    }
    
    .checkout-btn {
        padding: 14px 36px;
        background: linear-gradient(135deg, #ff6600 0%, #ff8533 100%);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(255, 102, 0, 0.3);
    }
    
    .checkout-btn:hover {
        background: linear-gradient(135deg, #e55a00 0%, #ff6600 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 102, 0, 0.4);
        color: #fff;
        text-decoration: none;
    }
    
    .checkout-btn i {
        font-size: 16px;
    }
    
    .empty-cart {
        text-align: center;
        padding: 60px 20px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    
    .empty-cart-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .empty-cart-icon i {
        font-size: 50px;
        color: #adb5bd;
    }
    
    .empty-cart-title {
        font-size: 22px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 12px;
    }
    
    .empty-cart-message {
        color: #6c757d;
        font-size: 15px;
        margin-bottom: 25px;
        line-height: 1.6;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .cart-section {
            padding: 80px 0 40px;
        }
        
        .cart-left {
            padding: 16px;
        }
        
        .cart-title {
            font-size: 18px;
        }
        
        .product-image {
            width: 60px;
            height: 60px;
        }
        
        .appointment-info-grid {
            grid-template-columns: 1fr;
        }
        
        .cart-total {
            flex-direction: column;
            gap: 10px;
            text-align: center;
        }
        
        .total-value {
            font-size: 22px;
        }
    }
</style>
@endpush

@section('content')
<div class="cart-section">
    <div class="container">
        @if(isset($items) && count($items) > 0)
        <div class="cart-container">
            <!-- Left: Cart Items -->
            <div class="cart-left">
                <h2 class="cart-title">LỊCH HẸN CỦA BẠN</h2>
                
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Dịch vụ</th>
                            <th>Ngày đặt</th>
                            <th>Giờ đặt</th>
                            <th>Nhân viên</th>
                            <th>Ghi chú</th>
                            <th>Thành tiền</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        @php
                            $unitPrice = $item['subtotal'] ?? 0;
                            $quantity = $item['quantity'] ?? 1;
                            $totalPrice = $unitPrice * $quantity;
                        @endphp
                        <tr data-key="{{ $item['key'] }}">
                            <td>
                                @if($item['type'] === 'appointment' && isset($item['appointment']))
                                    @if(isset($item['appointment']->appointmentDetails) && $item['appointment']->appointmentDetails->count() > 0)
                                        <div class="service-items">
                                            @foreach($item['appointment']->appointmentDetails as $detail)
                                                @php
                                                    $serviceImage = 'default.jpg';
                                                    $serviceName = 'Dịch vụ';
                                                    
                                                    if($detail->serviceVariant && $detail->serviceVariant->service) {
                                                        // Có serviceVariant -> lấy từ service
                                                        $service = $detail->serviceVariant->service;
                                                        $serviceImage = $service->image ?? 'default.jpg';
                                                        $serviceName = $detail->serviceVariant->name ?? $service->name ?? 'Dịch vụ';
                                                    } elseif($detail->combo_id && $detail->combo) {
                                                        // Combo - lấy hình từ combo item đầu tiên hoặc default
                                                        $serviceImage = 'default.jpg';
                                                        $serviceName = $detail->combo->name ?? ($detail->notes ?? 'Combo');
                                                    } else {
                                                        // Dịch vụ đơn - tìm service từ notes
                                                        $serviceName = $detail->notes ?? 'Dịch vụ';
                                                        // Thử tìm service theo tên
                                                        $service = \App\Models\Service::where('name', $serviceName)->first();
                                                        if($service && $service->image) {
                                                            $serviceImage = $service->image;
                                                        }
                                                    }
                                                    
                                                    $imagePath = asset('legacy/images/products/' . $serviceImage);
                                                @endphp
                                                <div class="service-item">
                                                    <img src="{{ $imagePath }}" 
                                                         alt="{{ $serviceName }}" 
                                                         class="service-image"
                                                         onerror="this.src='{{ asset('legacy/images/products/default.jpg') }}'">
                                                    <span class="service-name">
                                                        @if($detail->combo_id && $detail->combo)
                                                            Combo: {{ $serviceName }} - {{ number_format($detail->price_snapshot ?? 0, 0, ',', '.') }}đ ({{ $detail->duration ?? 60 }} phút)
                                                        @else
                                                            {{ $serviceName }} - {{ number_format($detail->price_snapshot ?? 0, 0, ',', '.') }}đ ({{ $detail->duration ?? 60 }} phút)
                                                        @endif
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        {{ $item['services'] ?? 'N/A' }}
                                    @endif
                                @else
                                    {{ $item['name'] }}
                                @endif
                            </td>
                            <td>
                                @if($item['type'] === 'appointment' && isset($item['appointment']))
                                    {{ $item['appointment_date'] ?? 'N/A' }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($item['type'] === 'appointment' && isset($item['appointment']))
                                    {{ $item['appointment_time'] ?? 'N/A' }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($item['type'] === 'appointment' && isset($item['appointment']))
                                    {{ $item['employee_name'] ?? 'Chưa xác định' }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($item['type'] === 'appointment' && isset($item['appointment']->note) && $item['appointment']->note)
                                    {{ $item['appointment']->note }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="total-price">
                                <span class="price-value">{{ number_format($totalPrice, 0, ',', '.') }}₫</span>
                            </td>
                            <td>
                                <a href="javascript:void(0);" class="delete-link" onclick="removeItem('{{ $item['key'] }}', '{{ $item['type'] ?? '' }}')">
                                    Hủy lịch
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
        </div>
        @else
        <div class="empty-cart">
            <div class="empty-cart-icon">
                <i class="fa fa-shopping-bag"></i>
            </div>
            <h2 class="empty-cart-title">Lịch hẹn trống</h2>
            <p class="empty-cart-message">Bạn chưa có lịch hẹn nào. Hãy đặt lịch hoặc thêm dịch vụ vào lịch hẹn!</p>
            <a href="{{ route('site.home') }}" class="checkout-btn" style="display: inline-block; text-decoration: none; width: auto; padding: 12px 30px;">
                <i class="fa fa-arrow-left"></i> Về trang chủ
            </a>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function removeItem(key, type) {
        var message = '';
        if (type === 'appointment') {
            message = 'Bạn có chắc chắn muốn hủy lịch hẹn này?';
        } else {
            message = 'Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?';
        }
        
        if (!confirm(message)) {
            return;
        }
        
        $.ajax({
            url: '{{ route("site.cart.remove", ":key") }}'.replace(':key', key),
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function() {
                alert('Có lỗi xảy ra. Vui lòng thử lại.');
            }
        });
    }
    
    function updateQuantity(key, quantity) {
        if (quantity < 1) {
            quantity = 1;
        }
        
        $.ajax({
            url: '{{ route("site.cart.update", ":key") }}'.replace(':key', key),
            method: 'PUT',
            data: {
                quantity: quantity
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function() {
                alert('Có lỗi xảy ra. Vui lòng thử lại.');
            }
        });
    }
    
    function checkout() {
        // TODO: Implement checkout logic
        alert('Tính năng thanh toán đang được phát triển. Vui lòng liên hệ với chúng tôi để hoàn tất đơn hàng.');
    }
</script>
@endpush
