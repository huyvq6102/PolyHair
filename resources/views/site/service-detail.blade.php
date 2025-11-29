@extends('layouts.site')

@section('title', $service->name ?? 'Chi tiết dịch vụ')

@section('content')
    <div class="service-detail-compact" style="min-height: calc(100vh - 120px); margin-top: 120px; padding: 20px 0; background: #fff;">
        <div class="container" style="max-width: 1400px;">
            <!-- Back Button -->
            <div class="mb-3">
                <a href="{{ route('site.services.index') }}" class="back-button" style="display: inline-flex; align-items: center; color: #4A3600; text-decoration: none; font-size: 14px; font-weight: 500; padding: 6px 12px; border-radius: 5px; transition: all 0.3s;" onmouseover="this.style.background='#f0f0f0'; this.style.color='#BC9321';" onmouseout="this.style.background='transparent'; this.style.color='#4A3600';">
                    <i class="fa fa-arrow-left mr-2" style="margin-right: 6px;"></i>
                    Quay lại
                </a>
            </div>

            <div class="row" style="margin: 0;">
                <!-- Left: Image -->
                <div class="col-xl-5 col-lg-5" style="padding: 0 15px;">
                    <div class="service-image-wrapper" style="height: 100%; display: flex; align-items: center;">
                        <div style="width: 100%; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                            <img src="{{ asset('legacy/images/products/' . ($service->image ?? 'default.jpg')) }}" 
                                 alt="{{ $service->name }}" 
                                 class="img-fluid w-100" 
                                 style="display: block; width: 100%; height: auto; max-height: 600px; object-fit: cover;">
                        </div>
                    </div>
                </div>

                <!-- Right: Content -->
                <div class="col-xl-7 col-lg-7" style="padding: 0 15px;">
                    <div style="height: 100%; display: flex; flex-direction: column; justify-content: space-between;">
                        <!-- Header -->
                        <div class="mb-3">
                            <span class="badge" style="background: linear-gradient(135deg, #f6d17a 0%, #d8b26a 50%, #8b5a2b 100%); color: #fff; padding: 6px 15px; border-radius: 20px; font-size: 12px; margin-bottom: 10px; display: inline-block;">
                                {{ $service->category->name ?? 'Chưa phân loại' }}
                            </span>
                            <h1 style="color: #4A3600; font-size: 36px; font-weight: 700; margin: 0 0 15px 0; line-height: 1.2;">
                                {{ $service->name ?? 'Chi tiết dịch vụ' }}
                            </h1>
                        </div>

                        <!-- Description -->
                        <div class="service-description mb-3" style="flex: 1; overflow-y: auto; max-height: 150px;">
                            <p style="color: #333; font-size: 14px; line-height: 1.6; margin: 0;">
                                {{ $service->description ?? 'Chưa có mô tả chi tiết.' }}
                            </p>
                        </div>

                        <!-- Service Variants -->
                        @if($service->serviceVariants && $service->serviceVariants->count() > 0)
                        <div class="service-variants mb-3" style="max-height: 250px; overflow-y: auto;">
                            <h3 style="color: #4A3600; font-size: 18px; font-weight: 600; margin-bottom: 15px; border-bottom: 2px solid #d8b26a; padding-bottom: 8px;">
                                Các gói dịch vụ
                            </h3>
                            <div class="row" style="margin: 0 -8px;">
                                @foreach($service->serviceVariants as $variant)
                                    <div class="col-md-6" style="padding: 0 8px; margin-bottom: 12px;">
                                        <div class="variant-card" style="border: 1px solid #e5e5e5; border-radius: 8px; padding: 15px; background: #fff; transition: all 0.3s; height: 100%;" onmouseover="this.style.borderColor='#d8b26a'; this.style.boxShadow='0 2px 10px rgba(216,178,106,0.2)';" onmouseout="this.style.borderColor='#e5e5e5'; this.style.boxShadow='none';">
                                            <h4 style="color: #4A3600; font-size: 16px; font-weight: 600; margin: 0 0 8px 0;">
                                                {{ $variant->name }}
                                            </h4>
                                            <div class="variant-price" style="margin-bottom: 5px;">
                                                <strong style="color: #BC9321; font-size: 20px; font-weight: 700;">
                                                    {{ number_format($variant->price, 0, ',', '.') }}đ
                                                </strong>
                                            </div>
                                            @if($variant->duration)
                                                <div class="variant-duration" style="color: #666; font-size: 12px;">
                                                    <i class="fa fa-clock-o" style="margin-right: 4px;"></i>
                                                    {{ $variant->duration }} phút
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Service Info & Price -->
                        <div class="service-info-price mb-3" style="background: #f8f8f8; padding: 15px; border-radius: 8px;">
                            <div class="row" style="margin: 0;">
                                @if($service->serviceVariants && $service->serviceVariants->count() > 0)
                                    @php
                                        $minPrice = $service->serviceVariants->min('price');
                                        $maxPrice = $service->serviceVariants->max('price');
                                    @endphp
                                    <div class="col-md-6" style="padding: 0 8px;">
                                        <div style="margin-bottom: 10px;">
                                            <strong style="color: #4A3600; font-size: 12px; display: block; margin-bottom: 3px;">Giá:</strong>
                                            <span style="color: #BC9321; font-weight: 700; font-size: 22px;">
                                                @if($minPrice == $maxPrice)
                                                    {{ number_format($minPrice, 0, ',', '.') }}đ
                                                @else
                                                    {{ number_format($minPrice, 0, ',', '.') }}đ - {{ number_format($maxPrice, 0, ',', '.') }}đ
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-6" style="padding: 0 8px;">
                                    <div style="margin-bottom: 10px;">
                                        <strong style="color: #4A3600; font-size: 12px; display: block; margin-bottom: 3px;">Trạng thái:</strong>
                                        <span style="color: #333; font-size: 14px;">{{ $service->status ?? 'Hoạt động' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Button -->
                        <div class="text-center">
                            <a href="{{ route('site.appointment.create') }}" class="btn-booking" style="display: inline-block; padding: 12px 35px; background: linear-gradient(135deg, #d8b26a 0%, #8b5a2b 100%); color: #fff; text-decoration: none; border-radius: 50px; font-size: 16px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.3s; box-shadow: 0 4px 15px rgba(216,178,106,0.4); width: 100%;">
                                <i class="fa fa-calendar-check-o" style="margin-right: 8px;"></i>
                                Đặt lịch ngay
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

