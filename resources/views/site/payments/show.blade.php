@extends('layouts.site')
@section('content')
<div class="container py-5">
    <div class="text-center mb-5">
        <h2>Hoàn tất đặt lịch</h2>
        <p class="lead">Chỉ còn vài bước nữa để hoàn tất lịch hẹn của bạn.</p>
    </div>

    <div class="row">
        <!-- Cột thông tin và thanh toán -->
        <div class="col-lg-7 mb-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="mb-4">Thông tin của bạn</h4>
                    <form class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fullName">Họ và tên</label>
                                <input type="text" class="form-control" id="fullName" value="{{ $customer['name'] }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone">Số điện thoại</label>
                                <input type="text" class="form-control" id="phone" value="{{ $customer['phone'] }}" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" value="{{ $customer['email'] }}" required>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="mb-4">Chọn phương thức thanh toán</h4>
                    
                    <div class="payment-methods-container">
                        <div class="row">
                            <!-- ĐÃ THAY THẾ CÁC LINK ẢNH BÊN DƯỚI -->
                            <div class="col-md-6 mb-3">
                                <div class="payment-method-option" data-target="#creditCardForm">
                                    <img src="https://img.lovepik.com/free-png/20210918/lovepik-credit-card-png-image_400179469_wh860.png" alt="Credit Card">
                                    <span>Thẻ Tín dụng/Ghi nợ</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="payment-method-option" data-target="#momoForm">
                                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRYi9dY_9kmpzvAOh5LHQJ9Oz5ez9KZlh3-b5lGKxUXiiL-t4_wclWFXqfFjfaUvOJcIeE&usqp=CAU" alt="MoMo">
                                    <span>Ví MoMo</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="payment-method-option" data-target="#zaloPayForm">
                                    <img src="data:image/png;base64,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    " alt="ZaloPay">
                                    <span>Ví ZaloPay</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="payment-method-option selected" data-target="#cashForm">
                                    <img src="data:image/jpeg;base64,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" alt="Thanh toán tại quầy">
                                    <span>Thanh toán tại quầy</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="creditCardForm" class="payment-details mt-4" style="display: none;">
                        <h6>Chi tiết thẻ</h6>
                        <div class="row">
                            <div class="col-12 mb-3"><input type="text" class="form-control" placeholder="Tên trên thẻ"></div>
                            <div class="col-12 mb-3"><input type="text" class="form-control" placeholder="Số thẻ"></div>
                            <div class="col-md-6 mb-3"><input type="text" class="form-control" placeholder="Ngày hết hạn (MM/YY)"></div>
                            <div class="col-md-6 mb-3"><input type="text" class="form-control" placeholder="CVV"></div>
                        </div>
                    </div>
                    <div id="momoForm" class="payment-details mt-4 text-center" style="display: none;">
                        <p>Quét mã QR bằng ứng dụng MoMo để thanh toán.</p>
                        <img src="https://i.imgur.com/g8f3fT4.png" alt="QR Code MoMo" style="width: 180px;">
                    </div>
                    <div id="zaloPayForm" class="payment-details mt-4 text-center" style="display: none;">
                         <p>Quét mã QR bằng ứng dụng ZaloPay để thanh toán.</p>
                         <img src="https://i.imgur.com/g8f3fT4.png" alt="QR Code ZaloPay" style="width: 180px;">
                    </div>
                    <div id="cashForm" class="payment-details mt-4">
                        <p class="text-muted">Bạn sẽ thanh toán trực tiếp tại quầy sau khi sử dụng dịch vụ.</p>
                    </div>

                </div>
            </div>
        </div>

        <!-- Cột tóm tắt đơn hàng -->
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="mb-4">Tóm tắt đơn hàng</h4>
                    <ul class="list-group list-group-flush">
                        @foreach($services as $s)
                         <li class="list-group-item d-flex justify-content-between align-items-center px-0">{{ $s['name'] }}<span>{{ number_format($s['price']) }}đ</span></li>
                        @endforeach
                        @if($promotion < 0)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 text-success">Khuyến mãi<span>{{ number_format($promotion) }}đ</span></li>
                        @endif
                        <li class="list-group-item d-flex justify-content-between align-items-center border-top pt-3 px-0"><strong>Tổng cộng</strong><strong style="font-size: 1.2rem;">{{ number_format($total) }}đ</strong></li>
                    </ul>
                    <form action="{{ route('site.payments.process') }}" method="POST">
                        @csrf
                       <button class="btn btn-primary btn-lg btn-block mt-4" type="submit">Xác nhận và đặt lịch</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentOptions = document.querySelectorAll('.payment-method-option');
        const paymentDetails = document.querySelectorAll('.payment-details');

        paymentOptions.forEach(option => {
            option.addEventListener('click', function() {
                paymentOptions.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                const targetId = this.dataset.target;
                paymentDetails.forEach(detail => detail.style.display = 'none');
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.style.display = 'block';
                }
            });
        });
    });
</script>
@endsection
