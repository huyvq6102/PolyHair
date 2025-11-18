@extends('layouts.site')

@section('title', 'Đăng nhập')

@section('content')
<!-- breadcrumb_area_start -->
<div class="breadcrumb_area">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="breadcrumb_content text-center">
                    <h3>Đăng nhập</h3>
                    <div class="breadcrumb">
                        <ul>
                            <li><a href="{{ route('site.home') }}">Trang chủ</a></li>
                            <li>Đăng nhập</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- breadcrumb_area_end -->

<!-- login_area_start -->
<div class="login_area">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <h3 class="text-center mb-4">Đăng nhập</h3>
                        
                        @if(session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('status') }}
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

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="form-group">
                                <label for="email">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email') }}" required autofocus>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password">Mật khẩu <span class="text-danger">*</span></label>
                                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
                                       required autocomplete="current-password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
</div>
<!-- login_area_end -->
@endsection
