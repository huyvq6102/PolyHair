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
                                        <input type="tel" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" 
                                               value="{{ old('phone') }}" required 
                                               pattern="^0[0-9]{9}$" 
                                               placeholder="0123456789"
                                               maxlength="10"
                                               title="Số điện thoại phải có đúng 10 số và bắt đầu bằng số 0">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
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
