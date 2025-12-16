@extends('layouts.site')

@section('title', 'Tài khoản của tôi')

@push('styles')
<link rel="stylesheet" href="{{ asset('legacy/content/css/auth-pages.css') }}">
<link rel="stylesheet" href="{{ asset('legacy/content/css/profile-pages.css') }}">
<style>
    /* Override CSS cho input trong profile page */
    .profile-section .form-control {
        background: #fff !important;
        border: 1px solid #ddd !important;
        color: #000 !important;
        padding: 14px 18px !important;
        font-size: 16px !important;
        height: auto !important;
        min-height: 48px !important;
    }
    .profile-section .form-control:focus {
        background: #fff !important;
        border-color: #c89c5c !important;
        color: #000 !important;
        box-shadow: 0 0 0 0.2rem rgba(200, 156, 92, 0.25) !important;
        padding: 14px 18px !important;
    }
    .profile-section .form-control::placeholder {
        color: #999 !important;
    }
    .profile-section select.form-control,
    .profile-section select#gender {
        background: #fff !important;
        background-color: #fff !important;
        color: #000 !important;
        padding: 14px 18px !important;
        font-size: 16px !important;
        height: auto !important;
        min-height: 48px !important;
        -webkit-appearance: menulist !important;
        -moz-appearance: menulist !important;
        appearance: menulist !important;
    }
    .profile-section select.form-control:focus,
    .profile-section select#gender:focus {
        background: #fff !important;
        background-color: #fff !important;
        color: #000 !important;
    }
    .profile-section select.form-control option,
    .profile-section select#gender option {
        background: #fff !important;
        background-color: #fff !important;
        color: #000 !important;
    }
    .profile-section select.form-control option:checked,
    .profile-section select#gender option:checked {
        background: #f8f9fa !important;
        background-color: #f8f9fa !important;
        color: #000 !important;
    }
    .profile-section select.form-control option:hover,
    .profile-section select#gender option:hover {
        background: #e9ecef !important;
        background-color: #e9ecef !important;
        color: #000 !important;
    }
    /* Đảm bảo text trong select hiển thị - override mọi CSS khác */
    .profile-section select,
    .profile-section select.form-control,
    .profile-section select#gender {
        color: #000 !important;
    }
    .profile-section select option,
    .profile-section select.form-control option,
    .profile-section select#gender option {
        color: #000 !important;
    }
    .profile-section .custom-file-label {
        background: #fff !important;
        border: 1px solid #ddd !important;
        color: #000 !important;
        padding: 14px 18px !important;
        font-size: 16px !important;
        height: auto !important;
        min-height: 48px !important;
        line-height: 1.5 !important;
    }
    .profile-section .custom-file-label::after {
        background: #f8f9fa !important;
        border-left: 1px solid #ddd !important;
        color: #000 !important;
    }
    .profile-section label {
        color: #333 !important;
    }
    /* Sửa text mô tả bị che - thêm margin-top */
    .profile-section .form-text,
    .profile-section small.form-text,
    .profile-section .text-muted {
        margin-top: 16px !important;
        display: block !important;
        margin-bottom: 0 !important;
        padding-top: 8px !important;
    }
    /* Đảm bảo form-group có đủ khoảng cách */
    .profile-section .form-group {
        margin-bottom: 24px !important;
    }
    /* Đảm bảo invalid-feedback có khoảng cách */
    .profile-section .invalid-feedback {
        margin-top: 6px !important;
        display: block !important;
    }
    /* Đảm bảo custom-file có khoảng cách với text mô tả */
    .profile-section .custom-file {
        margin-bottom: 0 !important;
    }
    /* Thông báo lỗi tùy chỉnh màu đỏ */
    .profile-section .custom-error-message {
        color: #dc3545 !important;
        font-size: 0.875rem !important;
        margin-top: 6px !important;
        display: block !important;
    }
    .profile-section .form-control.is-invalid-custom {
        border-color: #dc3545 !important;
    }
    /* Sửa màu chữ thông báo thành công thành màu đen */
    .alert-success {
        color: #000 !important;
    }
    .alert-success * {
        color: #000 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Hiển thị tên file khi chọn
        $('#avatar').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $('#avatar-label').text(fileName || 'Chọn tệp');
            
            // Preview ảnh
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var previewImg = $('.form-group:has(#avatar) .img-thumbnail');
                    if (previewImg.length) {
                        previewImg.attr('src', e.target.result);
                    } else {
                        // Tạo preview mới nếu chưa có
                        var previewDiv = $('<div class="mb-3"></div>');
                        previewDiv.html('<img src="' + e.target.result + '" class="img-thumbnail rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">');
                        $('.form-group:has(#avatar) .custom-file').before(previewDiv);
                    }
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

    });

    // Validation tùy chỉnh cho form profile
    document.addEventListener('DOMContentLoaded', function() {
        const profileForm = document.getElementById('profileForm');
        const passwordForm = document.getElementById('passwordForm');

        const errorMessages = {
            name: {
                valueMissing: 'Vui lòng nhập họ và tên.'
            },
            email: {
                valueMissing: 'Vui lòng nhập email.',
                typeMismatch: 'Email không đúng định dạng.'
            },
            update_password_current_password: {
                valueMissing: 'Vui lòng nhập mật khẩu hiện tại.'
            },
            update_password_password: {
                valueMissing: 'Vui lòng nhập mật khẩu mới.',
                tooShort: 'Mật khẩu phải có ít nhất 8 ký tự.'
            },
            update_password_password_confirmation: {
                valueMissing: 'Vui lòng xác nhận mật khẩu mới.',
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

            // Kiểm tra valueMissing trước
            if (input.validity.valueMissing) {
                const message = errorMessages[inputId]?.valueMissing || 'Vui lòng điền thông tin này.';
                showError(inputId, message);
                return false;
            }

            // Kiểm tra typeMismatch cho email
            if (input.validity.typeMismatch && input.type === 'email') {
                showError(inputId, errorMessages[inputId]?.typeMismatch || 'Email không đúng định dạng.');
                return false;
            }

            // Kiểm tra tooShort (đặc biệt cho mật khẩu)
            if (input.validity.tooShort) {
                const message = errorMessages[inputId]?.tooShort || 'Giá trị quá ngắn.';
                showError(inputId, message);
                return false;
            }

            // Kiểm tra độ dài mật khẩu tối thiểu (nếu có minlength attribute)
            if (inputId === 'update_password_password' && input.value.length > 0 && input.value.length < 8) {
                showError(inputId, 'Mật khẩu phải có ít nhất 8 ký tự.');
                return false;
            }

            // Kiểm tra xác nhận mật khẩu
            if (inputId === 'update_password_password_confirmation') {
                const password = document.getElementById('update_password_password');
                if (password && password.value.trim() !== '' && input.value !== password.value) {
                    showError(inputId, errorMessages[inputId]?.customMismatch || 'Xác nhận mật khẩu không khớp.');
                    return false;
                }
            }

            return true;
        }

        // Validate cho form profile
        if (profileForm) {
            const requiredInputs = ['name', 'email'];
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

            profileForm.addEventListener('submit', function(e) {
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
        }

        // Validate cho form password
        if (passwordForm) {
            const requiredInputs = ['update_password_current_password', 'update_password_password', 'update_password_password_confirmation'];
            
            // Validate mật khẩu mới khi blur
            const passwordInput = document.getElementById('update_password_password');
            if (passwordInput) {
                passwordInput.addEventListener('blur', function() {
                    validateField(this);
                    // Nếu xác nhận mật khẩu đã có giá trị, kiểm tra lại
                    const passwordConfirmation = document.getElementById('update_password_password_confirmation');
                    if (passwordConfirmation && passwordConfirmation.value.trim() !== '') {
                        if (this.value !== passwordConfirmation.value) {
                            showError('update_password_password_confirmation', 'Xác nhận mật khẩu không khớp.');
                        } else {
                            clearError('update_password_password_confirmation');
                        }
                    }
                });
                passwordInput.addEventListener('input', function() {
                    if (this.value.trim() !== '') {
                        clearError('update_password_password');
                    }
                });
            }

            // Validate xác nhận mật khẩu khi blur
            const passwordConfirmationInput = document.getElementById('update_password_password_confirmation');
            if (passwordConfirmationInput) {
                passwordConfirmationInput.addEventListener('blur', function() {
                    const password = document.getElementById('update_password_password');
                    if (password && password.value.trim() !== '') {
                        if (this.value !== password.value) {
                            showError('update_password_password_confirmation', 'Xác nhận mật khẩu không khớp.');
                        } else {
                            clearError('update_password_password_confirmation');
                        }
                    } else {
                        validateField(this);
                    }
                });
                passwordConfirmationInput.addEventListener('input', function() {
                    const password = document.getElementById('update_password_password');
                    if (password && this.value === password.value) {
                        clearError('update_password_password_confirmation');
                    }
                });
            }

            // Validate mật khẩu hiện tại khi blur
            const currentPasswordInput = document.getElementById('update_password_current_password');
            if (currentPasswordInput) {
                currentPasswordInput.addEventListener('blur', function() {
                    validateField(this);
                });
                currentPasswordInput.addEventListener('input', function() {
                    if (this.value.trim() !== '') {
                        clearError('update_password_current_password');
                    }
                });
            }

            passwordForm.addEventListener('submit', function(e) {
                let isValid = true;

                requiredInputs.forEach(inputId => {
                    const input = document.getElementById(inputId);
                    if (input && !validateField(input)) {
                        isValid = false;
                    }
                });

                // Kiểm tra xác nhận mật khẩu
                const password = document.getElementById('update_password_password');
                const passwordConfirmation = document.getElementById('update_password_password_confirmation');
                if (password && passwordConfirmation && password.value !== passwordConfirmation.value) {
                    showError('update_password_password_confirmation', 'Xác nhận mật khẩu không khớp.');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        }
    });

</script>
@endpush

@section('content')
<section class="profile-hero" style="padding: 150px 0 80px; background: #f8f9fa; min-height: 100vh;">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="text-center mb-5">
                    <h1 class="mb-3" style="color: #333;">Tài khoản của tôi</h1>
                </div>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-12">
                @if(session('status') === 'profile-updated')
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Thông tin đã được cập nhật thành công!
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('status') === 'password-updated' || (session('status') && session('status') !== 'profile-updated'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') === 'password-updated' ? 'Mật khẩu đã được cập nhật thành công!' : session('status') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="profile-section" style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); height: 100%;">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="profile-section" style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); height: 100%;">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
