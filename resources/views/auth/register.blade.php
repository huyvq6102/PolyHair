@extends('layouts.site')

@section('title', 'Đăng ký tài khoản')

@section('content')
@push('styles')
<link rel="stylesheet" href="{{ asset('legacy/content/css/auth-pages.css') }}">
<style>
    .register-hero h1 {
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

<section class="register-hero">
    <div class="container">
        <div class="row align-items-start">
            <div class="col-lg-5 mb-5 mb-lg-0">
                <span class="text-uppercase" style="letter-spacing:4px;">Khách hàng mới</span>
                <h1 class="mt-3">Gia nhập PolyHair</h1>
                <p>
                    Tạo tài khoản để quản lý lịch hẹn, nhận ưu đãi dành riêng cho bạn và kết nối với đội ngũ stylist chuyên nghiệp.
                    PolyHair luôn sẵn sàng đồng hành trong hành trình chăm sóc tóc của bạn.
                </p>
            </div>
            <div class="col-lg-7 ml-auto">
                <div class="register-form-wrapper">
                    <h3 class="text-white text-center mb-4">Đăng ký tài khoản</h3>
                        
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('register') }}" id="registerForm" novalidate>
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Họ và tên <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                               value="{{ old('name') }}" autofocus>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <span class="custom-error-message" id="name-error"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                                               value="{{ old('email') }}">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <span class="custom-error-message" id="email-error"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone">Số điện thoại <span class="text-danger">*</span></label>
                                        <input type="tel" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" 
                                               value="{{ old('phone') }}" 
                                               pattern="^0[0-9]{9}$" 
                                               placeholder="0123456789"
                                               maxlength="10">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <span class="custom-error-message" id="phone-error"></span>
                                        <small class="form-text text-muted">Ví dụ: 0123456789</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="gender">Giới tính</label>
                                        <select name="gender" id="gender" class="form-control @error('gender') is-invalid @enderror">
                                            <option value="">-- Chọn giới tính --</option>
                                            <option value="Nam" {{ old('gender') == 'Nam' ? 'selected' : '' }}>Nam</option>
                                            <option value="Nữ" {{ old('gender') == 'Nữ' ? 'selected' : '' }}>Nữ</option>
                                            <option value="Khác" {{ old('gender') == 'Khác' ? 'selected' : '' }}>Khác</option>
                                        </select>
                                        @error('gender')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="dob">Ngày sinh</label>
                                        <input type="date" name="dob" id="dob" class="form-control @error('dob') is-invalid @enderror" 
                                               value="{{ old('dob') }}">
                                        @error('dob')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password">Mật khẩu <span class="text-danger">*</span></label>
                                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
                                               autocomplete="new-password">
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <span class="custom-error-message" id="password-error"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password_confirmation">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" 
                                               autocomplete="new-password">
                                        <span class="custom-error-message" id="password_confirmation-error"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group text-center mt-4">
                                <button type="submit" class="boxed-btn3">Đăng ký</button>
                            </div>

                            <div class="text-center mt-3">
                                <p>Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập ngay</a></p>
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
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const errorMessages = {
        name: {
            valueMissing: 'Vui lòng nhập họ và tên.',
            patternMismatch: 'Họ và tên chỉ được chứa chữ cái, số và khoảng trắng.'
        },
        email: {
            valueMissing: 'Vui lòng nhập email.',
            typeMismatch: 'Email không đúng định dạng.'
        },
        phone: {
            valueMissing: 'Vui lòng nhập số điện thoại.',
            patternMismatch: 'Số điện thoại phải có đúng 10 số và bắt đầu bằng số 0.'
        },
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

        if (input.validity.typeMismatch && input.type === 'email') {
            showError(inputId, errorMessages[inputId]?.typeMismatch || 'Email không đúng định dạng.');
            return false;
        }

        if (input.validity.patternMismatch) {
            const message = errorMessages[inputId]?.patternMismatch || 'Giá trị không đúng định dạng.';
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

    // Validate khi blur
    const requiredInputs = ['name', 'email', 'phone', 'password', 'password_confirmation'];
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
