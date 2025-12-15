@extends('layouts.site')

@section('title', 'Quên mật khẩu')

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
                <span class="text-uppercase" style="letter-spacing:4px;">Khôi phục tài khoản</span>
                <h1 class="mt-3 mb-4">Quên mật khẩu?<br>Không sao cả!</h1>
                <p>
                    Nhập email hoặc số điện thoại của bạn, chúng tôi sẽ gửi mã xác nhận để bạn có thể đặt lại mật khẩu mới.
                </p>
    </div>
            <div class="col-lg-5 ml-auto">
                <div class="auth-form-wrapper">
                    <h3 class="text-center text-white mb-4">Quên mật khẩu</h3>
                        
                    @if(session('status'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('status') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

    <form method="POST" action="{{ route('password.email') }}" id="forgotPasswordForm" novalidate>
        @csrf

                        <div class="form-group">
                            <label for="login">Email hoặc Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" name="login" id="login" class="form-control @error('login') is-invalid @enderror" 
                                   value="{{ old('login') }}" autofocus 
                                   placeholder="Nhập email hoặc số điện thoại">
                            @error('login')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <span class="custom-error-message" id="login-error"></span>
                        </div>

                        <div class="form-group text-center mt-4">
                            <button type="submit" class="boxed-btn3">Gửi mã xác nhận</button>
        </div>

                        <div class="text-center mt-3">
                            <p><a href="{{ route('login') }}">Quay lại đăng nhập</a></p>
                            <p>Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký ngay</a></p>
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
    const form = document.getElementById('forgotPasswordForm');
    const loginInput = document.getElementById('login');

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

    if (loginInput) {
        loginInput.addEventListener('blur', function() {
            if (this.validity.valueMissing) {
                showError('login', 'Vui lòng nhập email hoặc số điện thoại.');
            } else {
                clearError('login');
            }
        });

        loginInput.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                clearError('login');
            }
        });
    }

    form.addEventListener('submit', function(e) {
        if (loginInput && loginInput.validity.valueMissing) {
            e.preventDefault();
            showError('login', 'Vui lòng nhập email hoặc số điện thoại.');
        }
    });
});
</script>
@endpush
@endsection
