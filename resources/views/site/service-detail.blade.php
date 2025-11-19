@extends('layouts.site')

@section('title', $service->name ?? 'Chi tiết dịch vụ')

@section('content')
    <!-- header_area_start -->
    <div style="padding: 60px 0; margin-top: 80px; margin-bottom: 60px; background: #fff;">
        <div class="container">
            <div class="row mb-3">
                <div class="col-xl-12">
                    <a href="{{ url()->previous() }}" class="back-button" style="display: inline-flex; align-items: center; color: #4A3600; text-decoration: none; font-size: 16px; font-weight: 500; transition: all 0.3s; padding: 8px 15px; border-radius: 5px;" onmouseover="this.style.background='#f0f0f0'; this.style.color='#BC9321';" onmouseout="this.style.background='transparent'; this.style.color='#4A3600';">
                        <i class="fa fa-arrow-left mr-2" style="margin-right: 8px;"></i>
                        Quay lại
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="text-center">
                        <h3 style="color: #4A3600; font-size: 48px; font-weight: 600; margin: 0;">{{ $service->name ?? 'Chi tiết dịch vụ' }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- header_area_end -->

    <!-- service_detail_area_start -->
    <div style="padding: 60px 0; margin-bottom: 100px;">
        <div class="container">
            <div class="row">
                <div class="col-xl-8 col-lg-8">
                    <div class="service_detail_content">
                        <div class="service_detail_image mb-4">
                            <img src="{{ asset('legacy/images/products/' . ($service->image ?? 'default.jpg')) }}" 
                                 alt="{{ $service->name }}" 
                                 class="img-fluid w-100" 
                                 style="border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                        </div>
                        
                        <div class="service_detail_info">
                            <div class="mb-3">
                                <span class="badge" style="background: #4A3600; color: #fff; padding: 8px 15px; border-radius: 5px; font-size: 14px;">
                                    {{ $service->category->name ?? 'Chưa phân loại' }}
                                </span>
                            </div>
                            
                            <h2 style="color: #4A3600; font-size: 36px; font-weight: 600; margin-bottom: 20px;">
                                {{ $service->name }}
                            </h2>
                            
                            <div class="service_description mb-4">
                                <p style="color: #666; font-size: 16px; line-height: 1.8;">
                                    {{ $service->description ?? 'Chưa có mô tả chi tiết.' }}
                                </p>
                            </div>

                            @if($service->serviceVariants && $service->serviceVariants->count() > 0)
                            <div class="service_variants mb-4">
                                <h3 style="color: #4A3600; font-size: 24px; font-weight: 600; margin-bottom: 20px;">
                                    <i class="flaticon-scissors"></i> Các gói dịch vụ
                                </h3>
                                <div class="row">
                                    @foreach($service->serviceVariants as $variant)
                                        <div class="col-md-6 mb-3">
                                            <div class="variant_card" style="border: 2px solid #f0f0f0; border-radius: 10px; padding: 20px; background: #fff; transition: all 0.3s;">
                                                <h4 style="color: #4A3600; font-size: 20px; font-weight: 600; margin-bottom: 15px;">
                                                    {{ $variant->name }}
                                                </h4>
                                                <div class="variant_price mb-2">
                                                    <strong style="color: #BC9321; font-size: 24px; font-weight: 700;">
                                                        {{ number_format($variant->price, 0, ',', '.') }}đ
                                                    </strong>
                                                </div>
                                                @if($variant->duration)
                                                    <div class="variant_duration">
                                                        <i class="flaticon-clock"></i>
                                                        <span style="color: #666; font-size: 14px;">
                                                            Thời gian: {{ $variant->duration }} phút
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-4">
                    <div class="service_sidebar">
                        <div class="booking_widget mb-4" style="background: #f8f8f8; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                            <h3 style="color: #4A3600; font-size: 24px; font-weight: 600; margin-bottom: 20px; text-align: center;">
                                Đặt lịch ngay
                            </h3>
                            <p style="color: #666; text-align: center; margin-bottom: 25px;">
                                Đặt lịch hẹn để được phục vụ tốt nhất
                            </p>
                            <div class="text-center">
                                <a href="#test-form" class="boxed-btn3 popup-with-form" style="display: inline-block; padding: 12px 30px; background: #4A3600; color: #fff; text-decoration: none; border-radius: 5px; font-size: 16px; font-weight: 600;">
                                    Đặt lịch hẹn
                                </a>
                            </div>
                        </div>

                        @if($service->serviceVariants && $service->serviceVariants->count() > 0)
                        <div class="price_summary mb-4" style="background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border: 1px solid #f0f0f0;">
                            <h4 style="color: #4A3600; font-size: 20px; font-weight: 600; margin-bottom: 20px;">
                                Bảng giá
                            </h4>
                            <div class="price_list">
                                @php
                                    $minPrice = $service->serviceVariants->min('price');
                                    $maxPrice = $service->serviceVariants->max('price');
                                @endphp
                                <div class="price_range mb-3">
                                    <p style="margin: 0; color: #666; font-size: 14px;">Giá từ:</p>
                                    @if($minPrice == $maxPrice)
                                        <strong style="color: #BC9321; font-size: 28px; font-weight: 700;">
                                            {{ number_format($minPrice, 0, ',', '.') }}đ
                                        </strong>
                                    @else
                                        <strong style="color: #BC9321; font-size: 28px; font-weight: 700;">
                                            {{ number_format($minPrice, 0, ',', '.') }}đ - {{ number_format($maxPrice, 0, ',', '.') }}đ
                                        </strong>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="service_info" style="background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border: 1px solid #f0f0f0;">
                            <h4 style="color: #4A3600; font-size: 20px; font-weight: 600; margin-bottom: 20px;">
                                Thông tin dịch vụ
                            </h4>
                            <ul style="list-style: none; padding: 0; margin: 0;">
                                <li style="padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                                    <strong style="color: #4A3600;">Danh mục:</strong>
                                    <span style="color: #666; margin-left: 10px;">
                                        {{ $service->category->name ?? 'Chưa phân loại' }}
                                    </span>
                                </li>
                                <li style="padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                                    <strong style="color: #4A3600;">Trạng thái:</strong>
                                    <span style="color: #666; margin-left: 10px;">
                                        {{ $service->status ?? 'Hoạt động' }}
                                    </span>
                                </li>
                                @if($service->serviceVariants && $service->serviceVariants->count() > 0)
                                <li style="padding: 10px 0;">
                                    <strong style="color: #4A3600;">Số gói dịch vụ:</strong>
                                    <span style="color: #666; margin-left: 10px;">
                                        {{ $service->serviceVariants->count() }} gói
                                    </span>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            @if($relatedServices && $relatedServices->count() > 0)
            <div class="related_services mt-5">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="section_title2 text-center mb-50">
                            <i class="flaticon-scissors"></i>
                            <h3>Dịch vụ liên quan</h3>
                        </div>
                    </div>
                </div>
                <div class="row">
                    @foreach($relatedServices->take(4) as $relatedService)
                        <div class="col-xl-3 col-md-6 col-lg-3">
                            <div class="single_service">
                                <div class="service_thumb">
                                    <a href="{{ route('site.services.show', $relatedService->id) }}">
                                        <img src="{{ asset('legacy/images/products/' . ($relatedService->image ?? 'default.jpg')) }}" alt="{{ $relatedService->name }}">
                                    </a>
                                </div>
                                <div class="service_content text-center">
                                    <h3>
                                        <a href="{{ route('site.services.show', $relatedService->id) }}">
                                            {{ $relatedService->name }}
                                        </a>
                                    </h3>
                                    <p>{{ Str::limit($relatedService->description ?? '', 80) }}</p>
                                    @if($relatedService->serviceVariants && $relatedService->serviceVariants->count() > 0)
                                        @php
                                            $minPrice = $relatedService->serviceVariants->min('price');
                                            $maxPrice = $relatedService->serviceVariants->max('price');
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
            @endif
        </div>
    </div>
    <!-- service_detail_area_end -->
@endsection

