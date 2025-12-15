@extends('admin.layouts.app')
@section('content')
<style>
    .payment-method-option {
        cursor: pointer;
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.2s;
    }
    .payment-method-option:hover {
        background-color: #f8f9fa;
        border-color: #0d6efd;
    }
    .payment-method-option.selected {
        border-color: #0d6efd;
        background-color: #e7f1ff;
        box-shadow: 0 0 0 1px #0d6efd;
    }
    .payment-method-option img {
        height: 30px;
        object-fit: contain;
    }
</style>
    <div class="container py-5" style="margin-top: 100px;">
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
                                    <input type="text" disabled class="form-control" id="fullName" value="{{ $customer['name'] }}"
                                        required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone">Số điện thoại</label>
                                    <input type="text" disabled class="form-control" id="phone" value="{{ $customer['phone'] }}"
                                        required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="email">Email</label>
                                <input type="email" disabled class="form-control" id="email" value="{{ $customer['email'] }}"
                                    required>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="mb-4">Chọn phương thức thanh toán</h4>

                        <div class="payment-methods-container">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="payment-method-option" data-target="#vnpayForm" data-method="vnpay">
                                        <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAOEAAADhCAMAAAAJbSJIAAABgFBMVXL////tGyQAWakAW6r//v////38/////f/qAAD//v3///sAUqb9//3tAAClttf4v7+gt9brHCb90tXtFyHzg4Tq7vUAVqcBn9wASaMAT6XtKCwXY68AQaEAXKQAoNoAW632j47E1OoAPaHsABMASqMARqQAktaHpMwChccBlNL4AAAAV67vGijuAA73xcPpHB/H1+n85+MAc7r5r7AAbbf54uEEjs4Dcrv8xLsASaG03e/u+/4Amtuu1vG1yOB7msX2T1P97/PtMDnxVlrwZmHxeHv6paL849r82NknaKz6ucH93uEwZrVOTZEDcsNMYpxNjcDe5e9WTJBOgrv1iIzxQzgFpdpwRoPx0t48NIW2MlX/0cezAD5qZZo5UpOAdJ7cIyqnOWWDQXZdS4OWP3bIK0DwXl34fohZhL9wkcWNpdjwWVVhiL+tvOB9oMhDebYANKHS6vZvuNuAwOhes+dZuuHA5fGCveiTz+4crdgAkN+R0erG4/sAm85Vv+PW7vM6RGCHAAAcQElEQVR4nO1dC1vbRroeW9JIM5JCHMUyhthOYnBwbDABkjTGTgMBGpp2d9l2Oedsz/3Sk+52t0sg1Gmzh79+3m90NbdA4mCSx2+fYkc3z6vvPjMaMTbCCCOMMMIII4wwwggjjDDCCCOMMMIII4wwwgeB4zi65Nxgkotht+XDQHAm6dPQTX3YbfkwcASbv3LlsQkZOsNuywfCY3vcLk5Nf87EpyhDKdidG1k/C9ibXEpTDrtFA4bU2Z3pUkUjhg1/0mHS/MQ01QBBLVsigvjrf4U5+8QYkgT9iiKYbVSy/iQ3jGE3aXDQHVJRzc+mAEUVjul8Ih5HssQGIZT8Ta5/Mu5GpmwwQkmzJzli/7DbNiBcuaFFNhjLUKtAip+ECKVzxAZjWwTFs9ii1A0EUMeQuriE/jcdBw8DcZEzwd4qSGlynTOGAEMflw3OMTYY2yLiokP34C3QBefm2OLiPYj8Itp8TqTi4BEZVuBunLfL0OHsuuXOzlpPVi6ZDLk8GgeP2OJXKi6eKBsTwZSz1WoG+FWtL/gpx148jouDR21xk5+WozoC1fKTqkcMvQnPus+dS1Q9G6fYYMoWJ1H2n9hq3THZ06qXUzLMeTnrPrtMMZRU9AQbjGWo+RQ0TsxRDZOvBRIkNL2Me/+S2OJhG9Q09Yf++rbv2zb+ZH3829d8ZDfHxkUYoCn5uhvxa+WannW3eo8Zl8EUD9ugIghm2sOZeqdQLhc6D2YWKkVb00CT4uIxOSp1XLGbMUFQdJ9co7jIL0NtKZQE+23Q9hfq4BajXK4v+JBhpaJC/xEZcoT6DasZ66hn3eTsi8XFOXYp0r0jNuj7M2XFrzajUFdcyzPYUVEdG8fYIv/Mgn+JGLobXDyxqpbV/GLItkiN7bdBfLUXiE9nZpIskGD7SjPYUCiQHLWwXoxbjhwU369ZsY9pee4G25mlwJjzEBf1YUaNw7koTM3P1rcLhfqS3ae1tr1UB+0HflYL42IiG45U5pkVa2iraS3ye7Pq381c1b3PhpmFQ4LjfXEQEiLnsmTDfWaTBEAjv7oEZS1P+mG9GMuFS8G+cFuxhuZAcMVtBYxzGYqLw/Q2h3JRzV7a7hRmbGKnKZdKiEKIPVMulJfCejGxRU4EEy9DErSCwJ/xvGbGG1pc5JL156L46i9BTAu+Ep6fLU4ukKNZmARjUmDNXtgulOkEeFThIA8lG5RQ0Vh+maa7yO65MV3a1nKHlKMezUW17CSEtGAHDLMPk3DxYNJWOutDU4lhBR5VGEKGNpiw8awvSYIpgpSjusPJUQ/Xg6SShcL2kq0FBlhUcbAcxI3yg0mKhxp8jjq21Jh0kGjrsMFnKRv0rDH+... [truncated]
                                            alt="Thanh toán tại quầy">
                                        <span>Thanh toán tại quầy</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="creditCardForm" class="payment-details mt-4" style="display: none;">
                            <h6>Chi tiết thẻ</h6>
                            <div class="row">
                                <div class="col-12 mb-3"><input type="text" class="form-control" placeholder="Tên trên thẻ">
                                </div>
                                <div class="col-12 mb-3"><input type="text" class="form-control" placeholder="Số thẻ"></div>
                                <div class="col-md-6 mb-3"><input type="text" class="form-control"
                                        placeholder="Ngày hết hạn (MM/YY)"></div>
                                <div class="col-md-6 mb-3"><input type="text" class="form-control" placeholder="CVV"></div>
                            </div>
                        </div>
                        <div id="vnpayForm" class="payment-details mt-4 text-center" style="display: none;">
                            <p class="text-muted">Bạn sẽ được chuyển hướng đến cổng thanh toán VNPAY.</p>
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

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">Đóng</button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <ul class="list-group list-group-flush">
                            @foreach($services as $s)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    {{ $s['name'] }}<span>{{ number_format($s['price']) }}đ</span></li>
                            @endforeach

                            @if($promotion > 0)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 text-success">
                                    Khuyến mãi
                                    <span>-{{ number_format($promotion) }}đ</span>
                                </li>
                            @endif

                            @php
                                // Tính lại nếu chưa có
                                $displayTaxablePrice = $taxablePrice ?? ($subtotal - $promotion);
                                // $displayVAT = $vat ?? ($displayTaxablePrice * 0.1);
                            @endphp

                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>Tạm tính</span>
                                <span>{{ number_format($displayTaxablePrice) }}đ</span>
                            </li>
                            {{-- <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>VAT (10%)</span>
                                <span>{{ number_format($displayVAT) }}đ</span>
                            </li> --}}

                            <li
                                class="list-group-item d-flex justify-content-between align-items-center border-top pt-3 px-0">
                                <strong>Tổng cộng</strong><strong
                                    style="font-size: 1.2rem;">{{ number_format($total) }}đ</strong></li>
                        </ul>
                        <form action="{{ route('site.payments.process') }}" method="POST" id="paymentForm">
                            @csrf
                            <input type="hidden" name="payment_method" id="selectedPaymentMethod" value="cash"> {{-- Giá trị
                            mặc định --}}
                            <button class="btn btn-primary btn-lg btn-block mt-4" type="submit">Xác nhận và đặt
                                lịch</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const paymentOptions = document.querySelectorAll('.payment-method-option');
            const paymentDetails = document.querySelectorAll('.payment-details');
            const selectedPaymentMethodInput = document.getElementById('selectedPaymentMethod');

            // Function to update payment details display and hidden input
            function updatePaymentSelection(selectedOption) {
                paymentOptions.forEach(opt => opt.classList.remove('selected'));
                selectedOption.classList.add('selected');
                const targetId = selectedOption.dataset.target;
                const method = selectedOption.dataset.method;

                paymentDetails.forEach(detail => detail.style.display = 'none');

                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.style.display = 'block';
                }

                if (selectedPaymentMethodInput && method) {
                    selectedPaymentMethodInput.value = method;
                }
            }
            console.log(paymentOptions);
            
            // Add click listeners to all payment options
            paymentOptions.forEach(option => {
                option.addEventListener('click', function () {
                    updatePaymentSelection(this);
                });
            });

            // Set initial state based on default value in hidden input or 'cash' if not set
            const initialMethod = selectedPaymentMethodInput ? selectedPaymentMethodInput.value : 'cash';
            const initialOption = document.querySelector(`.payment-method-option[data-method="${initialMethod}"]`);

            if (initialOption) {
                updatePaymentSelection(initialOption);
            } else {
                // Fallback to cash if no initial option found (e.g., if value is not 'cash')
                const cashOption = document.querySelector(`.payment-method-option[data-method="cash"]`);
                if (cashOption) {
                    updatePaymentSelection(cashOption);
                }
            }
        });
    </script>
@endpush