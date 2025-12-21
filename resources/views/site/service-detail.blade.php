@extends('layouts.site')

@section('title', $service->name ?? 'Chi tiết dịch vụ')

@section('content')
    <div class="service-detail-page" style="min-height: calc(100vh - 120px); margin-top: 120px; padding: 0; background: transparent;">
        <div class="container-fluid" style="max-width: 100%; padding: 0;">
            <!-- Back Button -->
            <div class="container" style="padding: 20px 15px 0;">
                <a href="{{ route('site.services.index') }}" class="back-button" style="display: inline-flex; align-items: center; color: #4A3600; text-decoration: none; font-size: 14px; font-weight: 500; padding: 6px 12px; border-radius: 5px; transition: all 0.3s; margin-bottom: 20px;" onmouseover="this.style.background='#f0f0f0'; this.style.color='#BC9321';" onmouseout="this.style.background='transparent'; this.style.color='#4A3600';">
                    <i class="fa fa-arrow-left mr-2" style="margin-right: 6px;"></i>
                    Quay lại
                </a>
            </div>

            <!-- Banner Section với 3 ảnh ngẫu nhiên -->
            <div class="container" style="padding: 20px 15px;">
                <div class="service-banner" style="background: linear-gradient(135deg, #1a4d7a 0%, #2c5f8f 50%, #1a4d7a 100%); padding: 30px 20px; position: relative; overflow: hidden; border-radius: 20px;">
                    <!-- Decorative elements -->
                    <div style="position: absolute; left: 0; top: 0; width: 150px; height: 100%; background: linear-gradient(90deg, rgba(74, 144, 226, 0.3) 0%, transparent 100%); transform: skewX(-20deg);"></div>
                    <div style="position: absolute; right: 0; top: 0; width: 150px; height: 100%; background: linear-gradient(90deg, transparent 0%, rgba(74, 144, 226, 0.3) 100%); transform: skewX(20deg);"></div>

                    <div style="position: relative; z-index: 1;">
                        <!-- Title -->
                        <div class="text-center mb-4">
                            <h1 style="color: #fff; font-size: 32px; font-weight: 800; margin: 0; text-transform: uppercase; text-shadow: 2px 2px 8px rgba(0,0,0,0.3);">
                                {{ $service->name ?? 'Chi tiết dịch vụ' }}
                            </h1>
                        </div>

                        <!-- 3 ảnh ngẫu nhiên -->
                        <div class="banner-images" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; max-width: 800px; margin: 0 auto;">
                            @php
                                $bannerImages = $randomImages ?? [];
                                // Nếu không có đủ 3 ảnh, lặp lại hoặc dùng ảnh mặc định
                                while (count($bannerImages) < 3) {
                                    if (count($bannerImages) > 0) {
                                        $bannerImages[] = $bannerImages[count($bannerImages) - 1];
                                    } else {
                                        $bannerImages[] = 'default.jpg';
                                    }
                                }
                                $bannerImages = array_slice($bannerImages, 0, 3);
                            @endphp
                            @php
                                $categoryName = strtolower($service->category->name ?? '');
                                $serviceName = strtolower($service->name ?? '');
                                $isGoiService = (strpos($categoryName, 'gội') !== false || strpos($serviceName, 'gội') !== false);
                                $isNhuomService = (strpos($categoryName, 'nhuộm') !== false || strpos($serviceName, 'nhuộm') !== false);
                                $isUonService = (strpos($categoryName, 'uốn') !== false || strpos($serviceName, 'uốn') !== false);

                                if ($isUonService) {
                                    $bannerFolder = 'uon';
                                } elseif ($isNhuomService) {
                                    $bannerFolder = 'nhuom';
                                } elseif ($isGoiService) {
                                    $bannerFolder = 'goi';
                                } else {
                                    $bannerFolder = 'cat';
                                }
                            @endphp
                            @foreach($bannerImages as $bannerImg)
                                <div class="banner-image-card" style="border-radius: 12px; overflow: hidden; box-shadow: 0 8px 25px rgba(0,0,0,0.3); transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)';" onmouseout="this.style.transform='translateY(0)';">
                                    <img src="{{ asset('legacy/images/' . $bannerFolder . '/' . $bannerImg) }}"
                                         alt="Banner image"
                                         style="width: 100%; height: 200px; object-fit: cover; display: block;"
                                         onerror="this.src='{{ asset('legacy/images/products/default.jpg') }}';">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Service Process Section -->
            <div class="service-process-section" style="padding: 60px 0; background: transparent;">
                <div class="container">
                    <div class="d-flex align-items-center mb-4">
                        <span class="process-bar" style="display: inline-block; width: 8px; height: 40px; background: #000; margin-right: 12px; border-radius: 4px;"></span>
                        <h2 class="process-title" style="font-size: 32px; font-weight: 800; color: #000; margin: 0; text-transform: uppercase;">
                            QUY TRÌNH DỊCH VỤ
                        </h2>
                    </div>
                    <p class="process-desc" style="color: #666; font-size: 16px; line-height: 1.6; margin-bottom: 40px; padding-left: 20px;">
                        @php
                            $categoryName = strtolower($service->category->name ?? '');
                            $serviceName = strtolower($service->name ?? '');
                            $isGoiService = (strpos($categoryName, 'gội') !== false || strpos($serviceName, 'gội') !== false);
                            $isNhuomService = (strpos($categoryName, 'nhuộm') !== false || strpos($serviceName, 'nhuộm') !== false);
                            $isUonService = (strpos($categoryName, 'uốn') !== false || strpos($serviceName, 'uốn') !== false);
                        @endphp
                        @if($isUonService)
                            Dịch vụ uốn tóc chuyên nghiệp mang đến kiểu tóc xoăn tự nhiên, bền đẹp và phù hợp với phong cách cá nhân.
                        @elseif($isNhuomService)
                            Dịch vụ nhuộm tóc chuyên nghiệp mang đến màu sắc hiện đại, bền màu và phù hợp với phong cách cá nhân.
                        @elseif($isGoiService)
                            Dịch vụ gội đầu chuyên nghiệp mang đến trải nghiệm thư giãn và chăm sóc tóc toàn diện.
                        @else
                            Dịch vụ cắt xả mang đến kiểu tóc hiện đại, gọn gàng và phù hợp phong cách cá nhân.
                        @endif
                    </p>

                    <div class="process-steps-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px;">
                        @php
                            // Kiểm tra category hoặc tên dịch vụ để quyết định hiển thị bước nào
                            $categoryName = strtolower($service->category->name ?? '');
                            $serviceName = strtolower($service->name ?? '');
                            $isGoiService = (strpos($categoryName, 'gội') !== false || strpos($serviceName, 'gội') !== false);
                            $isNhuomService = (strpos($categoryName, 'nhuộm') !== false || strpos($serviceName, 'nhuộm') !== false);
                            $isUonService = (strpos($categoryName, 'uốn') !== false || strpos($serviceName, 'uốn') !== false);

                            if ($isUonService) {
                                // Dịch vụ uốn
                                $steps = [
                                    ['image' => 'uonb1.jpg', 'title' => 'Kiểm tra & đánh giá chất tóc', 'folder' => 'uon'],
                                    ['image' => 'uonb2.jpg', 'title' => 'Uốn tóc', 'folder' => 'uon'],
                                    ['image' => 'uonb3.jpg', 'title' => 'Xả tóc', 'folder' => 'uon'],
                                    ['image' => 'uonb4.jpg', 'title' => 'Sấy vuốt tạo kiểu', 'folder' => 'uon'],
                                ];
                            } elseif ($isNhuomService) {
                                // Dịch vụ nhuộm
                                $steps = [
                                    ['image' => 'nhuomb1.jpg', 'title' => 'Kiểm tra & đánh giá chất tóc', 'folder' => 'nhuom'],
                                    ['image' => 'nhuomb2.jpg', 'title' => 'Nhuộm màu', 'folder' => 'nhuom'],
                                    ['image' => 'nhuomb3.jpg', 'title' => 'Xả tóc', 'folder' => 'nhuom'],
                                    ['image' => 'nhuomb4.jpg', 'title' => 'Sấy vuốt tạo kiểu', 'folder' => 'nhuom'],
                                ];
                            } elseif ($isGoiService) {
                                // Dịch vụ gội
                                $steps = [
                                    ['image' => 'goib1.png', 'title' => 'Rửa mặt', 'folder' => 'goi'],
                                    ['image' => 'goib2.png', 'title' => 'Gội đầu & Thư giãn vùng đầu', 'folder' => 'goi'],
                                    ['image' => 'goib3.png', 'title' => 'Rửa tai bọt & Ngoáy tai', 'folder' => 'goi'],
                                    ['image' => 'goib4.png', 'title' => 'Đắp khăn thư giãn mắt', 'folder' => 'goi'],
                                    ['image' => 'goib5.png', 'title' => 'Sấy tóc', 'folder' => 'goi'],
                                ];
                            } else {
                                // Dịch vụ cắt (mặc định)
                                $steps = [
                                    ['image' => 'catb1.png', 'title' => 'Tư vấn kiểu tóc', 'folder' => 'cat'],
                                    ['image' => 'catb2.png', 'title' => 'Cắt tóc', 'folder' => 'cat'],
                                    ['image' => 'catb3.png', 'title' => 'Xả sạch tóc', 'folder' => 'cat'],
                                    ['image' => 'catb4.png', 'title' => 'Sấy tóc', 'folder' => 'cat'],
                                    ['image' => 'catb5.png', 'title' => 'Tạo kiểu tóc', 'folder' => 'cat'],
                                ];
                            }
                        @endphp
                        @foreach($steps as $step)
                            <div class="process-step-card" style="text-align: center; transition: transform 0.3s ease;">
                                <div class="step-image-wrapper" style="width: 100%; height: 250px; border-radius: 12px; overflow: hidden; margin-bottom: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); background: #f5f5f5;">
                                    @php
                                        $imageFolder = $step['folder'] ?? 'cat';
                                        $publicPath = public_path('legacy/images/' . $imageFolder . '/' . $step['image']);
                                        if (file_exists($publicPath)) {
                                            $imageUrl = asset('legacy/images/' . $imageFolder . '/' . $step['image']);
                                        } else {
                                            $imageUrl = asset('legacy/images/products/default.jpg');
                                        }
                                    @endphp
                                    <img src="{{ $imageUrl }}"
                                         alt="{{ $step['title'] }}"
                                         class="step-image"
                                         style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;"
                                         onerror="this.src='{{ asset('legacy/images/products/default.jpg') }}'; this.style.opacity='0.5';"
                                         onmouseover="this.style.transform='scale(1.05)';"
                                         onmouseout="this.style.transform='scale(1)';">
                                </div>
                                <h3 class="step-title" style="font-size: 16px; font-weight: 600; color: #333; margin: 0;">
                                    {{ $step['title'] }}
                                </h3>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Service Details Section -->
            <div class="service-details-section" style="padding: 40px 0; background: transparent;">
                <div class="container">
                    <div class="row" style="margin: 0;">
                        <!-- Service Variants -->
                        @if($service->serviceVariants && $service->serviceVariants->count() > 0)
                        <div class="col-xl-12" style="padding: 0 15px; margin-bottom: 30px;">
                            <h3 style="color: #4A3600; font-size: 24px; font-weight: 600; margin-bottom: 20px; border-bottom: 2px solid #d8b26a; padding-bottom: 10px;">
                                Các gói dịch vụ
                            </h3>
                            <div class="row" style="margin: 0 -8px;">
                                @foreach($service->serviceVariants as $variant)
                                    <div class="col-md-6" style="padding: 0 8px; margin-bottom: 15px;">
                                        <div class="variant-card" style="border: 1px solid #e5e5e5; border-radius: 8px; padding: 20px; background: #fff; transition: all 0.3s; height: 100%;" onmouseover="this.style.borderColor='#d8b26a'; this.style.boxShadow='0 2px 10px rgba(216,178,106,0.2)';" onmouseout="this.style.borderColor='#e5e5e5'; this.style.boxShadow='none';">
                                            <h4 style="color: #4A3600; font-size: 18px; font-weight: 600; margin: 0 0 10px 0;">
                                                {{ $variant->name }}
                                            </h4>
                                            <div class="variant-price" style="margin-bottom: 8px;">
                                                <strong style="color: #BC9321; font-size: 24px; font-weight: 700;">
                                                    {{ number_format($variant->price, 0, ',', '.') }}đ
                                                </strong>
                                            </div>
                                            @if($variant->duration)
                                                <div class="variant-duration" style="color: #666; font-size: 14px;">
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
                    </div>
                </div>
            </div>

            <!-- Booking Button - Centered -->
            <div class="text-center" style="padding: 20px 0 40px 0;">
                <a href="{{ route('site.appointment.create') }}" class="btn-booking" style="display: inline-block; text-align: center; padding: 18px 50px; background: linear-gradient(135deg, #d8b26a 0%, #8b5a2b 100%); color: #fff; text-decoration: none; border-radius: 50px; font-size: 20px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.3s; box-shadow: 0 4px 15px rgba(216,178,106,0.4);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(216,178,106,0.6)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(216,178,106,0.4)';">
                    <i class="fa fa-calendar-check-o" style="margin-right: 8px;"></i>
                    Đặt lịch ngay
                </a>
            </div>
        </div>
    </div>
@endsection
