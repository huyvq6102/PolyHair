@extends('layouts.site')

@section('title', 'Xác nhận mã OTP')

@section('content')
@push('styles')
<link rel="stylesheet" href="{{ asset('legacy/content/css/auth-pages.css') }}">
<style>
    .auth-hero h1 {
        color: #f6f7fb;
        text-shadow: 0 1px 3px rgba(0,0,0,0.35);
    }
    /* Thông báo lỗi tùy chỉnh */
    .custom-error-message {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: block;
    }
    .form-control.is-invalid-custom {
        border-color: #dc3545;
    }
</style>
@endpush

<section class="auth-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <span class="text-uppercase" style="letter-spacing:4px;">Xác thực tài khoản</span>
                <h1 class="mt-3 mb-4">Nhập mã xác nhận<br>để tiếp tục</h1>
                <p>
                    Chúng tôi đã gửi mã xác nhận 6 chữ số đến email của bạn. Vui lòng kiểm tra hộp thư và nhập mã để đặt lại mật khẩu.
                </p>
            </div>
            <div class="col-lg-5 ml-auto">
                <div class="auth-form-wrapper">
                    <h3 class="text-center text-white mb-4">Xác nhận mã OTP</h3>
                        
                    @if(session('status'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('status') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.verify-otp.store') }}" id="verifyOtpForm" novalidate>
                        @csrf

                        <div class="form-group">
                            <label for="otp">Mã xác nhận (6 chữ số) <span class="text-danger">*</span></label>
                            <input type="text" name="otp" id="otp" class="form-control @error('otp') is-invalid @enderror text-center" 
                                   value="{{ old('otp') }}" autofocus maxlength="6" 
                                   placeholder="000000" style="font-size: 24px; letter-spacing: 8px;">
                            @error('otp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <span class="custom-error-message" id="otp-error"></span>
                            <small class="form-text text-muted mt-2">Mã xác nhận có hiệu lực trong 10 phút</small>
                        </div>

                        <div class="form-group text-center mt-4">
                            <button type="submit" class="boxed-btn3">Xác nhận</button>
                        </div>

                        <div class="text-center mt-3">
                            <p><a href="{{ route('password.request') }}">Gửi lại mã xác nhận</a></p>
                            <p><a href="{{ route('login') }}">Quay lại đăng nhập</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('verifyOtpForm');
    const otpInput = document.getElementById('otp');

    function showError(inputId, message) {
        const input = document.getElementById(inputId);
        const errorSpan = document.getElementById(inputId + '-error');
        
        if (input) {
            input.classList.add('is-invalid-custom');
        }
        
        if (errorSpan) {
            errorSpan.textContent = message;
            errorSpan.style.display = 'block';
        }
    }

    function clearError(inputId) {
        const input = document.getElementById(inputId);
        const errorSpan = document.getElementById(inputId + '-error');
        
        if (input) {
            input.classList.remove('is-invalid-custom');
        }
        
        if (errorSpan) {
            errorSpan.textContent = '';
            errorSpan.style.display = 'none';
        }
    }

    // Chỉ cho phép nhập số
    if (otpInput) {
        otpInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        otpInput.addEventListener('blur', function() {
            if (this.validity.valueMissing) {
                showError('otp', 'Vui lòng nhập mã xác nhận.');
            } else if (this.value.length !== 6) {
                showError('otp', 'Mã xác nhận phải có đúng 6 chữ số.');
            } else {
                clearError('otp');
            }
        });

        otpInput.addEventListener('input', function() {
            if (this.value.length === 6) {
                clearError('otp');
            }
        });
    }

    form.addEventListener('submit', function(e) {
        if (otpInput) {
            if (otpInput.validity.valueMissing) {
                e.preventDefault();
                showError('otp', 'Vui lòng nhập mã xác nhận.');
            } else if (otpInput.value.length !== 6) {
                e.preventDefault();
                showError('otp', 'Mã xác nhận phải có đúng 6 chữ số.');
            }
        }
    });
});
</script>
@endpush
@endsection

