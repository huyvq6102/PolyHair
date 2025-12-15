@extends('admin.layouts.app')

@section('title', 'Thanh toán thành công')

@push('styles')
<style>
    .success-section {
        padding: 0 0 60px;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .success-card {
        background: #fff;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        text-align: center;
        max-width: 600px;
        width: 100%;
        border-top: 5px solid #28a745;
    }
    
    .success-icon {
        width: 80px;
        height: 80px;
        background: #d4edda;
        color: #28a745;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        margin: 0 auto 24px;
    }
    
    .success-title {
        font-size: 28px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 12px;
    }
    
    .success-message {
        color: #6c757d;
        font-size: 16px;
        margin-bottom: 30px;
        line-height: 1.6;
    }
    
    .order-details {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 30px;
        text-align: left;
    }
    
    .detail-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 14px;
    }
    
    .detail-row:last-child {
        margin-bottom: 0;
    }
    
    .detail-label {
        color: #6c757d;
        font-weight: 500;
    }
    
    .detail-value {
        color: #2c3e50;
        font-weight: 600;
    }
    
    .home-btn {
        display: inline-block;
        padding: 14px 36px;
        background: #4A3600;
        color: #fff;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s;
    }
    
    .home-btn:hover {
        background: #2c2000;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(74, 54, 0, 0.2);
        color: #fff;
    }
</style>
@endpush

@section('content')
<div class="success-section">
    <div class="success-card">
        <div class="success-icon">
            <i class="fa fa-check"></i>
        </div>
        
        <h1 class="success-title">Thanh toán thành công!</h1>
        <p class="success-message">Cảm ơn bạn đã sử dụng dịch vụ của PolyBarber. Đơn hàng của bạn đã được xác nhận.</p>
        
        <div class="order-details">
            <div class="detail-row">
                <span class="detail-label">Mã hóa đơn:</span>
                <span class="detail-value">#{{ $invoiceCode }}</span>
            </div>
            
            @if(isset($appointmentId) && $appointmentId)
            <div class="detail-row">
                <span class="detail-label">Mã lịch hẹn:</span>
                <span class="detail-value">#{{ $appointmentId }}</span>
            </div>
            @endif
            
            @if(isset($couponCode) && $couponCode)
            <div class="detail-row">
                <span class="detail-label">Mã giảm giá:</span>
                <span class="detail-value">{{ $couponCode }}</span>
            </div>
            @endif
            
            <div class="detail-row" style="margin-top: 15px; border-top: 1px solid #dee2e6; padding-top: 15px;">
                <span class="detail-label" style="font-size: 16px; color: #2c3e50;">Tổng thanh toán:</span>
                <span class="detail-value" style="font-size: 18px; color: #28a745;">{{ number_format($total, 0, ',', '.') }}đ</span>
            </div>
        </div>
        
        <a href="{{ route('site.services.index') }}" class="home-btn">
            <i class="fas fa-cut"></i> Chọn dịch vụ
        </a>
    </div>
</div>
@endsection
