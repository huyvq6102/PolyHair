@extends('layouts.site')

@section('title', 'Quên mật khẩu')

@section('content')
@push('styles')
<link rel="stylesheet" href="{{ asset('legacy/content/css/auth-pages.css') }}">
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

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

                        <div class="form-group">
                            <label for="login">Email hoặc Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" name="login" id="login" class="form-control @error('login') is-invalid @enderror" 
                                   value="{{ old('login') }}" required autofocus 
                                   placeholder="Nhập email hoặc số điện thoại">
                            @error('login')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
@endsection
