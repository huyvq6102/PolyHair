@extends('layouts.site')
@section('content')
<div class="container mb-5" style="margin-top: 175px;">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="alert alert-success">
                <h4 class="alert-heading">Thanh toán thành công!</h4>
                <p>Cảm ơn bạn đã đặt lịch tại PolyHair. Lịch đặt của bạn đã được xử lý thành công.</p>
                <hr>
                <p class="mb-0">Mã hóa đơn: <strong>{{ $invoiceCode }}</strong></p>
                @if (isset($appointmentId) && $appointmentId)
                    <p class="mb-0">Mã lịch hẹn: <strong>{{ $appointmentId }}</strong></p>
                @endif
                
                @if (isset($total))
                    <p class="mb-0 mt-2">Tổng thanh toán: <strong class="text-danger" style="font-size: 1.2em;">{{ number_format($total) }}đ</strong></p>
                @endif

                @if (isset($couponCode) && $couponCode)
                    <p class="mb-0 text-muted"><small>Đã áp dụng mã khuyến mại: {{ $couponCode }}</small></p>
                @endif
            </div>
            <a href="{{ route('site.home') }}" class="btn btn-primary mt-3 success-btn" style="background: #000; border: none; color: #fff; padding: 10px 30px; font-size: 14px; font-weight: 600; border-radius: 8px; transition: all 0.3s ease;">Xong</a>
        </div>
    </div>
</div>
<style>
    .success-btn {
        background: #000 !important;
        border: none !important;
        color: #fff !important;
        padding: 10px 30px !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        border-radius: 8px !important;
        transition: all 0.3s ease !important;
        cursor: pointer !important;
    }
    
    .success-btn:hover {
        background: #FFC107 !important;
        color: #000 !important;
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
    }
</style>
@endsection