@extends('layouts.site')

@section('title', 'Đặt lại mật khẩu')

@section('content')
@push('styles')
<link rel="stylesheet" href="{{ asset('legacy/content/css/auth-pages.css') }}">
<style>
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
                <span class="text-uppercase" style="letter-spacing:4px;">Tạo mật khẩu mới</span>
                <h1 class="mt-3 mb-4">Đặt lại mật khẩu<br>của bạn</h1>
                <p>
                    Vui lòng nhập mật khẩu mới cho tài khoản của bạn. Mật khẩu phải có ít nhất 8 ký tự và bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt.
                </p>
            </div>
            <div class="col-lg-5 ml-auto">
                <div class="auth-form-wrapper">
                    <h3 class="text-center text-white mb-4">Đặt lại mật khẩu</h3>

    <form method="POST" action="{{ route('password.store') }}" id="resetPasswordForm" novalidate>
        @csrf

                        <div class="form-group">
                            <label for="password">Mật khẩu mới <span class="text-danger">*</span></label>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
                                   autofocus autocomplete="new-password" 
                                   placeholder="Nhập mật khẩu mới">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <span class="custom-error-message" id="password-error"></span>
        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                   autocomplete="new-password" 
                                   placeholder="Nhập lại mật khẩu mới">
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <span class="custom-error-message" id="password_confirmation-error"></span>
        </div>

                        <div class="form-group text-center mt-4">
                            <button type="submit" class="boxed-btn3">Đặt lại mật khẩu</button>
        </div>

                        <div class="text-center mt-3">
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
    const form = document.getElementById('resetPasswordForm');
    const errorMessages = {
        password: {
            valueMissing: 'Vui lòng nhập mật khẩu.',
            tooShort: 'Mật khẩu phải có ít nhất 8 ký tự.'
        },
        password_confirmation: {
            valueMissing: 'Vui lòng xác nhận mật khẩu.',
            customMismatch: 'Xác nhận mật khẩu không khớp.'
        }
    };

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

    function validateField(input) {
        const inputId = input.id;
        clearError(inputId);

        if (input.validity.valueMissing) {
            const message = errorMessages[inputId]?.valueMissing || 'Vui lòng điền thông tin này.';
            showError(inputId, message);
            return false;
        }

        if (input.validity.tooShort) {
            const message = errorMessages[inputId]?.tooShort || 'Giá trị quá ngắn.';
            showError(inputId, message);
            return false;
        }

        // Kiểm tra xác nhận mật khẩu
        if (inputId === 'password_confirmation') {
            const password = document.getElementById('password');
            if (password && input.value !== password.value) {
                showError(inputId, errorMessages[inputId]?.customMismatch || 'Xác nhận mật khẩu không khớp.');
                return false;
            }
        }

        return true;
    }

    const requiredInputs = ['password', 'password_confirmation'];
    requiredInputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('blur', function() {
                validateField(this);
            });

            input.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    clearError(inputId);
                }
            });
        }
    });

    form.addEventListener('submit', function(e) {
        let isValid = true;

        requiredInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input && !validateField(input)) {
                isValid = false;
            }
        });

        // Kiểm tra xác nhận mật khẩu
        const password = document.getElementById('password');
        const passwordConfirmation = document.getElementById('password_confirmation');
        if (password && passwordConfirmation && password.value !== passwordConfirmation.value) {
            showError('password_confirmation', 'Xác nhận mật khẩu không khớp.');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            e.stopPropagation();
        }
    });
});
</script>
@endpush
@endsection

