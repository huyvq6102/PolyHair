@extends('layouts.site')

@section('title', 'Lịch hẹn')

@push('styles')
<style>
    .cart-section {
        padding: 120px 0 80px;
        background: #f8f9fa;
        min-height: 70vh;
    }
    
    .cart-container {
        display: block;
    }
    
    .cart-left {
        width: 100%;
        background: #fff;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .cart-title {
        font-size: 24px;
        font-weight: 600;
        color: #333;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .cart-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .cart-table td {
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
        font-size: 14px;
    }
    
    .cart-table tbody tr:hover {
        background: #f8f9fa;
    }
    
    .cart-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .product-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
    }
    
    .product-name {
        font-size: 14px;
        color: #333;
        line-height: 1.5;
        max-width: 400px;
    }
    
    .quantity-control {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .quantity-btn {
        width: 30px;
        height: 30px;
        border: 1px solid #ddd;
        background: #fff;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: #666;
        transition: all 0.3s;
    }
    
    .quantity-btn:hover {
        background: #f0f0f0;
        border-color: #4A3600;
        color: #4A3600;
    }
    
    .quantity-input {
        width: 60px;
        height: 30px;
        text-align: center;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .product-price {
        font-size: 16px;
        font-weight: 600;
        color: #333;
    }
    
    .delete-btn {
        padding: 8px 16px;
        border-radius: 6px;
        background: #f0f0f0;
        border: none;
        cursor: pointer;
        color: #666;
        transition: all 0.3s;
        font-size: 14px;
        font-weight: 500;
    }
    
    .delete-btn:hover {
        background: #dc3545;
        color: #fff;
    }
    
    .cart-total {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px solid #f0f0f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .total-label {
        font-size: 18px;
        font-weight: 600;
        color: #333;
    }
    
    .total-value {
        font-size: 24px;
        font-weight: 700;
        color: #dc3545;
    }
    
    .appointment-details {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
        border-left: 4px solid #4A3600;
    }
    
    .appointment-details-title {
        font-size: 16px;
        font-weight: 600;
        color: #4A3600;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .appointment-details-title i {
        color: #BC9321;
    }
    
    .appointment-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }
    
    .info-item-box {
        background: #f8f9fa;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 15px;
        border-left: 3px solid #4A3600;
    }
    
    .info-item-label {
        font-size: 12px;
        color: #666;
        font-weight: 500;
        margin-bottom: 5px;
        text-transform: uppercase;
    }
    
    .info-item-value {
        font-size: 14px;
        color: #333;
        font-weight: 600;
    }
    
    .info-item-value.status {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .service-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .service-list li {
        padding: 5px 0;
        font-size: 13px;
        color: #555;
    }
    
    .service-list li:before {
        content: "• ";
        color: #BC9321;
        font-weight: bold;
        margin-right: 5px;
    }
    
    .checkout-btn {
        padding: 15px 40px;
        background: #ff6600;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    
    .checkout-btn:hover {
        background: #e55a00;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 102, 0, 0.3);
    }
    
    .checkout-btn i {
        font-size: 18px;
    }
    
    .empty-cart {
        text-align: center;
        padding: 80px 20px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .empty-cart-icon {
        width: 120px;
        height: 120px;
        background: #f0f0f0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 30px;
    }
    
    .empty-cart-icon i {
        font-size: 60px;
        color: #999;
    }
    
    .empty-cart-title {
        font-size: 24px;
        font-weight: 600;
        color: #333;
        margin-bottom: 15px;
    }
    
    .empty-cart-message {
        color: #666;
        font-size: 16px;
        margin-bottom: 30px;
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
                    <tbody>
                        @foreach($items as $item)
                        <tr data-key="{{ $item['key'] }}">
                            <td>
                                @if($item['type'] === 'appointment')
                                    <img src="{{ asset('legacy/images/about/about_lft.png') }}" alt="Đặt lịch" class="product-image">
                                @else
                                    <img src="{{ asset('legacy/images/products/' . ($item['variant']->service->image ?? 'default.jpg')) }}" alt="{{ $item['name'] }}" class="product-image">
                                @endif
                            </td>
                            <td>
                                <div class="product-name">{{ $item['name'] }}</div>
                                @if($item['type'] === 'appointment' && isset($item['appointment']))
                                <div class="appointment-details">
                                    <h4 class="appointment-details-title">
                                        <i class="fa fa-calendar"></i>
                                        Chi tiết lịch hẹn
                                    </h4>
                                    <div class="appointment-info-grid">
                                        <div class="info-item-box">
                                            <div class="info-item-label">Mã lịch hẹn</div>
                                            <div class="info-item-value">#{{ str_pad($item['appointment']->id, 6, '0', STR_PAD_LEFT) }}</div>
                                        </div>
                                        <div class="info-item-box">
                                            <div class="info-item-label">Ngày đặt</div>
                                            <div class="info-item-value">{{ $item['appointment_date'] ?? 'N/A' }}</div>
                                        </div>
                                        <div class="info-item-box">
                                            <div class="info-item-label">Giờ đặt</div>
                                            <div class="info-item-value">{{ $item['appointment_time'] ?? 'N/A' }}</div>
                                        </div>
                                        <div class="info-item-box">
                                            <div class="info-item-label">Nhân viên</div>
                                            <div class="info-item-value">{{ $item['employee_name'] ?? 'Chưa xác định' }}</div>
                                        </div>
                                        <div class="info-item-box" style="grid-column: span 2;">
                                            <div class="info-item-label">Dịch vụ</div>
                                            <div class="info-item-value">
                                                @if(isset($item['appointment']->appointmentDetails) && $item['appointment']->appointmentDetails->count() > 0)
                                                    <ul class="service-list">
                                                        @foreach($item['appointment']->appointmentDetails as $detail)
                                                            @if($detail->serviceVariant)
                                                                <li>{{ $detail->serviceVariant->name }} - {{ number_format($detail->price_snapshot ?? 0, 0, ',', '.') }}đ ({{ $detail->duration ?? 60 }} phút)</li>
                                                            @endif
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    {{ $item['services'] ?? 'N/A' }}
                                                @endif
                                            </div>
                                        </div>
                                        @if(isset($item['appointment']->note) && $item['appointment']->note)
                                        <div class="info-item-box" style="grid-column: span 2;">
                                            <div class="info-item-label">Ghi chú</div>
                                            <div class="info-item-value">{{ $item['appointment']->note }}</div>
                                        </div>
                                        @endif
                                        <div class="info-item-box" style="grid-column: span 2; background: #fff3cd; border-left-color: #ffc107;">
                                            <div class="info-item-label">Tổng tiền</div>
                                            <div class="info-item-value" style="font-size: 18px; color: #dc3545;">{{ number_format($item['subtotal'] ?? 0, 0, ',', '.') }}đ</div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <button type="button" class="delete-btn" onclick="removeItem('{{ $item['key'] }}', '{{ $item['type'] ?? '' }}')" title="{{ $item['type'] === 'appointment' ? 'Hủy lịch hẹn' : 'Xóa' }}">
                                    {{ $item['type'] === 'appointment' ? 'Hủy lịch hẹn' : 'Xóa' }}
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <div class="cart-total">
                    <span class="total-label">Tổng tiền:</span>
                    <span class="total-value">{{ number_format($total ?? 0, 0, ',', '.') }}đ</span>
                </div>
                
                <div style="margin-top: 30px; text-align: right;">
                    <button type="button" class="checkout-btn" onclick="checkout()" style="width: auto; padding: 15px 40px; display: inline-block;">
                        <i class="fa fa-credit-card"></i> THANH TOÁN
                    </button>
                </div>
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
