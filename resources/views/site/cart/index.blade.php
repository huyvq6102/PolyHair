@extends('layouts.site')

@section('title', 'Lịch đặt')

@push('styles')
<style>
    .cart-section {
        padding: 120px 0 80px;
        background: #f8f9fa;
        min-height: 70vh;
    }
    
    .cart-header {
        background: #fff;
        padding: 30px;
        border-radius: 10px;
        margin-bottom: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .cart-title {
        font-size: 32px;
        font-weight: 600;
        color: #4A3600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .cart-title i {
        color: #BC9321;
    }
    
    .cart-items {
        background: #fff;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 30px;
    }
    
    .cart-item {
        display: flex;
        padding: 25px;
        border-bottom: 2px solid #f0f0f0;
        transition: all 0.3s;
    }
    
    .cart-item:last-child {
        border-bottom: none;
    }
    
    .cart-item:hover {
        background: #f8f9fa;
    }
    
    .item-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #4A3600 0%, #5a4a00 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 20px;
        flex-shrink: 0;
    }
    
    .item-icon i {
        color: #fff;
        font-size: 24px;
    }
    
    .item-content {
        flex: 1;
    }
    
    .item-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 10px;
    }
    
    .item-name {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        margin: 0 0 5px 0;
    }
    
    .item-type-badge {
        display: inline-block;
        padding: 4px 12px;
        background: #4A3600;
        color: #fff;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        margin-left: 10px;
    }
    
    .item-details {
        color: #666;
        font-size: 14px;
        line-height: 1.6;
        margin-bottom: 10px;
    }
    
    .item-detail-row {
        display: flex;
        gap: 20px;
        margin-bottom: 5px;
    }
    
    .item-detail-label {
        font-weight: 500;
        color: #4A3600;
        min-width: 100px;
    }
    
    .item-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 15px;
    }
    
    .item-price {
        font-size: 20px;
        font-weight: 600;
        color: #BC9321;
    }
    
    .item-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn-action {
        padding: 8px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .btn-remove {
        background: #dc3545;
        color: #fff;
    }
    
    .btn-remove:hover {
        background: #c82333;
        transform: translateY(-2px);
    }
    
    .btn-view {
        background: #4A3600;
        color: #fff;
    }
    
    .btn-view:hover {
        background: #5a4a00;
        transform: translateY(-2px);
    }
    
    .cart-summary {
        background: #fff;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        position: sticky;
        top: 100px;
    }
    
    .summary-title {
        font-size: 20px;
        font-weight: 600;
        color: #4A3600;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        font-size: 16px;
    }
    
    .summary-label {
        color: #666;
    }
    
    .summary-value {
        font-weight: 600;
        color: #333;
    }
    
    .summary-total {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px solid #f0f0f0;
        font-size: 22px;
    }
    
    .summary-total-label {
        font-weight: 600;
        color: #4A3600;
    }
    
    .summary-total-value {
        font-weight: 700;
        color: #BC9321;
        font-size: 24px;
    }
    
    .cart-actions {
        margin-top: 25px;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .btn-cart {
        width: 100%;
        padding: 15px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        text-decoration: none;
    }
    
    .btn-continue {
        background: #6c757d;
        color: #fff;
    }
    
    .btn-continue:hover {
        background: #5a6268;
        transform: translateY(-2px);
    }
    
    .btn-checkout {
        background: linear-gradient(135deg, #4A3600 0%, #5a4a00 100%);
        color: #fff;
    }
    
    .btn-checkout:hover {
        background: linear-gradient(135deg, #5a4a00 0%, #4A3600 100%);
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(74, 54, 0, 0.3);
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
    
    .status-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-confirmed {
        background: #d4edda;
        color: #155724;
    }
    
    .status-processing {
        background: #cfe2ff;
        color: #084298;
    }
    
    .status-completed {
        background: #d1e7dd;
        color: #0f5132;
    }
    
    @media (max-width: 767px) {
        .cart-section {
            padding: 100px 0 40px;
        }
        
        .cart-item {
            flex-direction: column;
        }
        
        .item-icon {
            margin-bottom: 15px;
        }
        
        .item-header {
            flex-direction: column;
        }
        
        .item-footer {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        
        .cart-summary {
            position: relative;
            top: 0;
            margin-top: 30px;
        }
    }
</style>
@endpush

@section('content')
<div class="cart-section">
    <div class="container">
        <div class="cart-header">
            <h1 class="cart-title">
                <i class="fa fa-shopping-bag"></i>
                Lịch đặt của tôi
            </h1>
        </div>
        
        @if(isset($items) && count($items) > 0)
        <div class="row">
            <div class="col-xl-8 col-lg-8">
                <div class="cart-items">
                    @foreach($items as $index => $item)
                        <div class="cart-item" data-key="{{ $item['key'] ?? (($item['type'] === 'appointment' ? 'appointment_' : 'service_variant_') . $item['id']) }}">
                            <div class="item-icon">
                                @if($item['type'] === 'appointment')
                                    <i class="fa fa-calendar-check-o"></i>
                                @else
                                    <i class="fa fa-scissors"></i>
                                @endif
                            </div>
                            
                            <div class="item-content">
                                <div class="item-header">
                                    <div>
                                        <h3 class="item-name">
                                            {{ $item['name'] }}
                                            <span class="item-type-badge">
                                                @if($item['type'] === 'appointment')
                                                    Đặt lịch
                                                @else
                                                    Dịch vụ
                                                @endif
                                            </span>
                                        </h3>
                                    </div>
                                </div>
                                
                                <div class="item-details">
                                    @if($item['type'] === 'appointment')
                                        <div class="item-detail-row">
                                            <span class="item-detail-label">Dịch vụ:</span>
                                            <span>{{ $item['services'] ?? 'N/A' }}</span>
                                        </div>
                                        <div class="item-detail-row">
                                            <span class="item-detail-label">Ngày:</span>
                                            <span>{{ $item['appointment_date'] ?? 'N/A' }}</span>
                                        </div>
                                        <div class="item-detail-row">
                                            <span class="item-detail-label">Giờ:</span>
                                            <span>{{ $item['appointment_time'] ?? 'N/A' }}</span>
                                        </div>
                                        @if(isset($item['employee_name']))
                                        <div class="item-detail-row">
                                            <span class="item-detail-label">Nhân viên:</span>
                                            <span>{{ $item['employee_name'] }}</span>
                                        </div>
                                        @endif
                                        @if(isset($item['status']))
                                        <div class="item-detail-row">
                                            <span class="item-detail-label">Trạng thái:</span>
                                            <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $item['status'])) }}">
                                                {{ $item['status'] }}
                                            </span>
                                        </div>
                                        @endif
                                    @else
                                        <div class="item-detail-row">
                                            <span class="item-detail-label">Danh mục:</span>
                                            <span>{{ $item['service_name'] ?? 'Dịch vụ' }}</span>
                                        </div>
                                        <div class="item-detail-row">
                                            <span class="item-detail-label">Thời lượng:</span>
                                            <span>{{ $item['duration'] ?? 60 }} phút</span>
                                        </div>
                                        <div class="item-detail-row">
                                            <span class="item-detail-label">Số lượng:</span>
                                            <span>{{ $item['quantity'] ?? 1 }}</span>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="item-footer">
                                    <div class="item-price">
                                        {{ number_format($item['subtotal'] ?? 0, 0, ',', '.') }}đ
                                    </div>
                                    <div class="item-actions">
                                        @if($item['type'] === 'appointment')
                                            <a href="{{ route('site.appointment.show', $item['id']) }}" class="btn-action btn-view">
                                                <i class="fa fa-eye"></i> Xem chi tiết
                                            </a>
                                        @endif
                                        <button type="button" class="btn-action btn-remove" onclick="removeItem('{{ $item['key'] ?? (($item['type'] === 'appointment' ? 'appointment_' : 'service_variant_') . $item['id']) }}')">
                                            <i class="fa fa-trash"></i> Xóa
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="col-xl-4 col-lg-4">
                <div class="cart-summary">
                    <h3 class="summary-title">Tóm tắt đơn hàng</h3>
                    
                    <div class="summary-row">
                        <span class="summary-label">Số lượng:</span>
                        <span class="summary-value">{{ isset($items) ? count($items) : 0 }} mục</span>
                    </div>
                    
                    <div class="summary-total">
                        <span class="summary-total-label">Tổng cộng:</span>
                        <span class="summary-total-value">{{ number_format($total ?? 0, 0, ',', '.') }}đ</span>
                    </div>
                    
                    <div class="cart-actions">
                        <a href="{{ route('site.home') }}" class="btn-cart btn-continue">
                            <i class="fa fa-arrow-left"></i> Tiếp tục đặt lịch
                        </a>
                        <button type="button" class="btn-cart btn-checkout" onclick="checkout()">
                            <i class="fa fa-check"></i> Thanh toán
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="empty-cart">
            <div class="empty-cart-icon">
                <i class="fa fa-shopping-bag"></i>
            </div>
            <h2 class="empty-cart-title">Lịch đặt trống</h2>
            <p class="empty-cart-message">Bạn chưa có sản phẩm nào trong lịch đặt. Hãy đặt lịch hoặc thêm dịch vụ vào lịch đặt!</p>
            <div style="display: flex; gap: 15px; justify-content: center; margin-top: 20px; flex-wrap: wrap;">
                <a href="{{ route('site.cart.seed-fake-data') }}" class="btn-cart btn-checkout" style="background: #BC9321; border-color: #BC9321;">
                    <i class="fa fa-plus"></i> Thêm dữ liệu mẫu
                </a>
                <a href="{{ route('site.home') }}" class="btn-cart btn-checkout">
                    <i class="fa fa-arrow-left"></i> Về trang chủ
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function removeItem(key) {
        if (!confirm('Bạn có chắc chắn muốn xóa mục này khỏi lịch đặt?')) {
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
                    // Remove item from DOM
                    $('.cart-item[data-key="' + key + '"]').fadeOut(300, function() {
                        $(this).remove();
                        // Check if cart is empty
                        if ($('.cart-item').length <= 1) {
                            location.reload();
                        } else {
                            // Reload page to update cart
                            location.reload();
                        }
                    });
                    
                    // Update cart count in header
                    updateCartCount();
                    
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message);
                    }
                }
            },
            error: function() {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Có lỗi xảy ra. Vui lòng thử lại.');
                } else {
                    alert('Có lỗi xảy ra. Vui lòng thử lại.');
                }
            }
        });
    }
    
    function checkout() {
        // Redirect to checkout page or show payment form
        alert('Tính năng thanh toán đang được phát triển. Vui lòng liên hệ với chúng tôi để hoàn tất đơn hàng.');
    }
    
    function updateCartCount() {
        $.ajax({
            url: '{{ route("site.cart.count") }}',
            method: 'GET',
            success: function(response) {
                $('.cart-icon .bag').text(response.count);
            }
        });
    }
    
    // Update cart count on page load
    $(document).ready(function() {
        updateCartCount();
    });
</script>
@endpush

