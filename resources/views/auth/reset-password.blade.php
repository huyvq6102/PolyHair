@extends('layouts.site')

@section('title', 'Đặt lại mật khẩu')

@section('content')
@push('styles')
<link rel="stylesheet" href="{{ asset('legacy/content/css/auth-pages.css') }}">
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
                        
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

                        <div class="form-group">
                            <label for="password">Mật khẩu mới <span class="text-danger">*</span></label>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
                                   required autofocus autocomplete="new-password" 
                                   placeholder="Nhập mật khẩu mới">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                   required autocomplete="new-password" 
                                   placeholder="Nhập lại mật khẩu mới">
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
@endsection

