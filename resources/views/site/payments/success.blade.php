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
            <a href="{{ route('site.home') }}" class="btn btn-primary mt-3">Quay về trang chủ</a>
        </div>
    </div>
</div>
@endsection