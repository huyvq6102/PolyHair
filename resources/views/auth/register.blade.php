@extends('layouts.site')

@section('title', 'Đăng ký tài khoản')

@section('content')
@push('styles')
<link rel="stylesheet" href="{{ asset('legacy/content/css/auth-pages.css') }}">
<style>
    .register-title {
        color: #ffffff !important;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
        font-weight: 700;
        font-size: 2.5rem;
    }
    @media (prefers-color-scheme: light) {
        .register-title {
            color: #1a1a1a !important;
            text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.9);
        }
    }
    .register-hero .container,
    .register-hero .row,
    .register-hero .col-md-6,
    .register-hero .col-lg-7 {
        overflow: visible !important;
    }
    .register-hero .col-md-6 {
        padding-left: 15px !important;
        padding-right: 15px !important;
        width: 50% !important;
        flex: 0 0 50% !important;
        max-width: 50% !important;
    }
    .register-hero .form-group {
        width: 100% !important;
        max-width: 100% !important;
        overflow: visible !important;
        position: relative !important;
        margin-bottom: 16px !important;
    }
    .register-hero .form-group select,
    .register-hero #gender {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        overflow: visible !important;
        box-sizing: border-box !important;
        display: block !important;
        position: relative !important;
        -webkit-appearance: menulist !important;
        -moz-appearance: menulist !important;
        appearance: menulist !important;
    }
    .register-hero .form-group select option {
        white-space: normal !important;
        padding: 8px !important;
        display: block !important;
    }
    .register-hero .form-control {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    .register-form-wrapper {
        overflow: visible !important;
        width: 100% !important;
    }
    .register-form-wrapper form {
        overflow: visible !important;
        width: 100% !important;
    }
    .register-form-wrapper .row {
        margin-left: -15px !important;
        margin-right: -15px !important;
    }
</style>
@endpush

<section class="register-hero">
    <div class="container">
        <div class="row align-items-start">
            <div class="col-lg-5 mb-5 mb-lg-0">
                <span class="text-uppercase" style="letter-spacing:4px;">Khách hàng mới</span>
                <h1 class="mt-3 register-title">Gia nhập PolyHair</h1>
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

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Họ và tên <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                               value="{{ old('name') }}" required autofocus>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                                               value="{{ old('email') }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone">Số điện thoại <span class="text-danger">*</span></label>
                                        <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" 
                                               value="{{ old('phone') }}" required>
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6" style="overflow: visible !important;">
                                    <div class="form-group" style="width: 100% !important; overflow: visible !important;">
                                        <label for="gender">Giới tính</label>
                                        <select name="gender" id="gender" class="form-control @error('gender') is-invalid @enderror" style="width: 100% !important; max-width: 100% !important; min-width: 100% !important; overflow: visible !important; box-sizing: border-box !important;">
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
                                               required autocomplete="new-password">
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password_confirmation">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" 
                                               required autocomplete="new-password">
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
@endsection
