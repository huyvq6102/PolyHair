@extends('layouts.site')

@section('title', $settings->title ?? 'Trang chủ')

@section('content')
    @include('site.partials.slider')

    <!-- about_area_start -->
    <div class="about_area">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-xl-6 col-lg-6">
                    <div class="about_thumb">
                        <img src="{{ asset('legacy/images/about/about_lft.png') }}" alt="" />
                        <div class="opening_hour text-center">
                            <i class="flaticon-clock"></i>
                            <h3>Giờ hoạt động</h3>
                            <p>
                                Mon-Fri (8.30-20.00) <br />
                                Sat (9.00-5.00)
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-lg-6">
                    <div class="about_info">
                        <div class="section_title mb-20px">
                            <span>Về chúng tôi</span>
                            <h3>{{ $settings->slogan ?? 'Chăm sóc chuyên nghiệp' }}</h3>
                        </div>
                        <p>
                            Truyền cảm hứng cùng sự tận tâm với khách hàng cho các nhân viên
                            của chúng tôi. Chúng tôi đã sẵn sàng mang đến cho bạn dịch vụ
                            tốt nhất từ trước đến nay.
                        </p>
                        <a href="#test-form" class="boxed-btn3 popup-with-form">Đặt Lịch Ngay</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- about_area_end -->

    <!-- service_area_start -->
    <div class="service_area">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="section_title2 text-center mb-90">
                        <i class="flaticon-scissors"></i>
                        <h3>Dịch vụ của chúng tôi</h3>
                    </div>
                </div>
            </div>
            <div class="row">
                @foreach($services as $service)
                    <div class="col-xl-4 col-md-6 col-lg-4">
                        <div class="single_service">
                            <div class="service_thumb">
                                <img src="{{ asset('legacy/images/products/' . ($service->image ?? 'default.jpg')) }}" alt="{{ $service->name }}">
                            </div>
                            <div class="service_content text-center">
                                <h3><a href="{{ route('site.services.show', $service->id) }}">{{ $service->name }}</a></h3>
                                <p>{{ Str::limit($service->description ?? '', 100) }}</p>
                                @if($service->serviceVariants && $service->serviceVariants->count() > 0)
                                    @php
                                        $variant = $service->serviceVariants->first();
                                        $minPrice = $service->serviceVariants->min('price');
                                        $maxPrice = $service->serviceVariants->max('price');
                                    @endphp
                                    <div class="service_price">
                                        @if($minPrice == $maxPrice)
                                            <span>{{ number_format($minPrice, 0, ',', '.') }}đ</span>
                                        @else
                                            <span>{{ number_format($minPrice, 0, ',', '.') }}đ - {{ number_format($maxPrice, 0, ',', '.') }}đ</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <!-- service_area_end -->

    <!-- product_area_start -->
    @if($products->count() > 0)
    <div class="product_area">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="section_title2 text-center mb-90">
                        <i class="flaticon-scissors"></i>
                        <h3>Sản phẩm nổi bật</h3>
                    </div>
                </div>
            </div>
            <div class="row">
                @foreach($products as $product)
                    <div class="col-xl-3 col-md-6 col-lg-3">
                        <div class="single_product">
                            <div class="product_thumb">
                                <a href="{{ route('site.products.show', $product->id) }}">
                                    <img src="{{ asset('legacy/images/products/' . $product->images) }}" alt="{{ $product->name }}">
                                </a>
                                @if($product->sale > 0)
                                    <div class="product_sale">
                                        <span>Sale</span>
                                    </div>
                                @endif
                            </div>
                            <div class="product_content text-center">
                                <h3><a href="{{ route('site.products.show', $product->id) }}">{{ $product->name }}</a></h3>
                                <div class="product_price">
                                    <span>{{ number_format($product->price - $product->sale, 0, ',', '.') }}đ</span>
                                    @if($product->sale > 0)
                                        <span class="old_price">{{ number_format($product->price, 0, ',', '.') }}đ</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
    <!-- product_area_end -->
@endsection
