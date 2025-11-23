@extends('layouts.site')
@section('content')
<div class="container mb-5" style="margin-top: 175px;">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="alert alert-success">
                <h4 class="alert-heading">Thanh toán thành công!</h4>
                <p>Cảm ơn bạn đã mua hàng tại PolyHair. Đơn hàng của bạn đã được xử lý thành công.</p>
                <hr>
                <p class="mb-0">Mã giao dịch của bạn: <strong>{{ $appointmentId }}</strong></p>
            </div>
            <a href="{{ route('site.home') }}" class="btn btn-primary mt-3">Quay về trang chủ</a>
        </div>
    </div>
</div>
@endsection