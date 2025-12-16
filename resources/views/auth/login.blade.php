@extends('layouts.site')

@section('title', 'Đăng nhập')

@section('content')
@push('styles')
<link rel="stylesheet" href="{{ asset('legacy/content/css/auth-pages.css') }}">
<style>
    .divider {
        position: relative;
        text-align: center;
        margin: 20px 0;
    }
    .divider::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: rgba(255, 255, 255, 0.3);
    }
    .divider span {
        position: relative;
        background: #667eea;
        padding: 0 15px;
        color: rgba(255, 255, 255, 0.7);
    }
    .btn-light:hover {
        background: #f8f9fa !important;
        border-color: #ccc !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    /* Làm tiêu đề sáng hơn trên nền tối */
    .auth-hero h1 {
        color: #f6f7fb;
        text-shadow: 0 1px 3px rgba(0,0,0,0.35);
    }
    /* Nút hiện/ẩn mật khẩu dạng icon */
    .password-toggle-btn {
        border-color: #4b5563;
        color: #e5e7eb;
        background: #1f2937;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }
    .password-toggle-btn:hover {
        color: #fff;
        background: #111827;
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
                <span class="text-uppercase" style="letter-spacing:4px;">Trải nghiệm PolyHair</span>
                <h1 class="mt-3 mb-4">Đăng nhập<br>để tiếp tục</h1>
                <p>
                    Khám phá các dịch vụ chăm sóc tóc đẳng cấp, đặt lịch nhanh chóng và nhận ưu đãi cá nhân hoá.
                    Đăng nhập ngay để tiếp tục hành trình trải nghiệm hệ sinh thái làm đẹp chuyên nghiệp tại PolyHair.
                </p>
            </div>
            <div class="col-lg-5 ml-auto">
                <div class="auth-form-wrapper">
                    <h3 class="text-center text-white mb-4">Đăng nhập</h3>
                        
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if(session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('status') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
                            @csrf

                            <div class="form-group">
                                <label for="login">Email hoặc Số điện thoại <span class="text-danger">*</span></label>
                                <input type="text" name="login" id="login" class="form-control @error('login') is-invalid @enderror" 
                                       value="{{ old('login', session('login')) }}" autofocus 
                                       placeholder="Nhập email hoặc số điện thoại">
                                @error('login')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <span class="custom-error-message" id="login-error"></span>
                            </div>

                            <div class="form-group">
                                <label for="password">Mật khẩu <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
                                           value="" autocomplete="current-password" placeholder="Nhập mật khẩu">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary password-toggle-btn" id="toggle-password" aria-label="Hiển thị hoặc ẩn mật khẩu" onclick="togglePasswordVisibility()">
                                            <span class="toggle-icon icon-eye" aria-hidden="true">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                            </span>
                                            <span class="toggle-icon icon-eye-off" aria-hidden="true" style="display: none;">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 19c-7 0-11-7-11-7a21.77 21.77 0 0 1 5.11-6.41"></path>
                                                    <path d="M9.88 9.88a3 3 0 0 0 4.24 4.24"></path>
                                                    <path d="M1 1l22 22"></path>
                                                </svg>
                                            </span>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <span class="custom-error-message" id="password-error"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                    <label class="form-check-label" for="remember">
                                        Ghi nhớ đăng nhập
                                    </label>
                                </div>
                            </div>

                            <div class="form-group text-center mt-4">
                                <button type="submit" class="boxed-btn3">Đăng nhập</button>
                            </div>

                            <div class="text-center mt-3 mb-3">
                                <div class="divider">
                                    <span>hoặc</span>
                                </div>
                            </div>

                            <div class="form-group text-center">
                                <a href="{{ route('google.redirect') }}" class="btn btn-light btn-block" style="display: inline-flex; align-items: center; justify-content: center; padding: 12px 24px; border-radius: 5px; text-decoration: none; color: #333; background: white; border: 1px solid #ddd; transition: all 0.3s;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" style="margin-right: 10px;">
                                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                    </svg>
                                    Đăng nhập với Google
                                </a>
                            </div>

                            <div class="text-center mt-3">
                                @if (Route::has('password.request'))
                                    <p><a href="{{ route('password.request') }}">Quên mật khẩu?</a></p>
                                @endif
                                <p>Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký ngay</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@push('scripts')
<script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const toggleButton = document.getElementById('toggle-password');
        if (!passwordInput || !toggleButton) return;

        const eye = toggleButton.querySelector('.icon-eye');
        const eyeOff = toggleButton.querySelector('.icon-eye-off');

        const isHidden = passwordInput.type === 'password';
        passwordInput.type = isHidden ? 'text' : 'password';

        if (eye && eyeOff) {
            eye.style.display = isHidden ? 'none' : 'inline-flex';
            eyeOff.style.display = isHidden ? 'inline-flex' : 'none';
        }
    }

    // Validation tùy chỉnh cho form đăng nhập
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('loginForm');
        const errorMessages = {
            login: {
                valueMissing: 'Vui lòng nhập email hoặc số điện thoại.'
            },
            password: {
                valueMissing: 'Vui lòng nhập mật khẩu.'
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

            return true;
        }

        // Validate khi blur
        const requiredInputs = ['login', 'password'];
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

        // Validate khi submit
        form.addEventListener('submit', function(e) {
            let isValid = true;

            requiredInputs.forEach(inputId => {
                const input = document.getElementById(inputId);
                if (input && !validateField(input)) {
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });
</script>
@endpush
@endsection
