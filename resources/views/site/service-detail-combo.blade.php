
<!-- Combo Banner Section -->
<div class="container" style="padding: 20px 15px;">
    <div class="service-banner" style="background: linear-gradient(135deg, #bc913f 0%, #a88235 50%, #bc913f 100%); padding: 30px 20px; position: relative; overflow: hidden; border-radius: 20px;">
        <div style="position: absolute; left: 0; top: 0; width: 150px; height: 100%; background: linear-gradient(90deg, rgba(255, 255, 255, 0.2) 0%, transparent 100%); transform: skewX(-20deg);"></div>
        <div style="position: absolute; right: 0; top: 0; width: 150px; height: 100%; background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.2) 100%); transform: skewX(20deg);"></div>
        
        <div style="position: relative; z-index: 1;">
            <div class="text-center mb-4">
                <h1 style="color: #fff; font-size: 32px; font-weight: 800; margin: 0; text-transform: uppercase; text-shadow: 2px 2px 8px rgba(0,0,0,0.3);">
                    {{ $combo->name }}
                </h1>
                <p style="color: #fff; font-size: 18px; margin-top: 10px; opacity: 0.9;">
                    Combo gồm {{ $combo->comboItems->count() }} dịch vụ
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Combo Services Process Section -->
<div class="service-process-section" style="padding: 60px 0; background: transparent;">
    <div class="container">
        <div class="d-flex align-items-center mb-4">
                            <span class="process-bar" style="display: inline-block; width: 8px; height: 40px; background: #bc913f; margin-right: 12px; border-radius: 4px;"></span>
            <h2 class="process-title" style="font-size: 32px; font-weight: 800; color: #bc913f; margin: 0; text-transform: uppercase;">
                QUY TRÌNH DỊCH VỤ COMBO
            </h2>
        </div>
        <p class="process-desc" style="color: #666; font-size: 16px; line-height: 1.6; margin-bottom: 40px; padding-left: 20px;">
            Combo {{ $combo->name }} bao gồm các dịch vụ được thực hiện theo thứ tự dưới đây:
        </p>
        
        @php
            $serviceIndex = 1;
        @endphp
        
        @foreach($combo->comboItems as $comboItem)
            @php
                $service = $comboItem->serviceVariant ? $comboItem->serviceVariant->service : $comboItem->service;
                if (!$service) continue;
                
                // Xác định loại dịch vụ và quy trình
                $categoryName = strtolower($service->category->name ?? '');
                $serviceName = strtolower($service->name ?? '');
                
                $isUonService = strpos($serviceName, 'uốn') !== false;
                $isNhuomService = strpos($serviceName, 'nhuộm') !== false;
                $isGoiService = strpos($serviceName, 'gội') !== false;
                $isCatService = strpos($serviceName, 'cắt') !== false;
                
                if (!$isUonService && !$isNhuomService && !$isGoiService && !$isCatService) {
                    $isUonService = strpos($categoryName, 'uốn') !== false;
                    $isNhuomService = strpos($categoryName, 'nhuộm') !== false;
                    $isGoiService = strpos($categoryName, 'gội') !== false;
                    $isCatService = strpos($categoryName, 'cắt') !== false;
                }
                
                // Lấy quy trình dịch vụ
                $serviceSteps = [];
                $bannerFolder = 'cat';
                
                if ($isUonService) {
                    $serviceSteps = [
                        ['image' => 'uonb1.jpg', 'title' => 'Kiểm tra & đánh giá chất tóc', 'folder' => 'uon'],
                        ['image' => 'uonb2.jpg', 'title' => 'Uốn tóc', 'folder' => 'uon'],
                        ['image' => 'uonb3.jpg', 'title' => 'Xả tóc', 'folder' => 'uon'],
                        ['image' => 'uonb4.jpg', 'title' => 'Sấy vuốt tạo kiểu', 'folder' => 'uon'],
                    ];
                    $bannerFolder = 'uon';
                } elseif ($isNhuomService) {
                    $serviceSteps = [
                        ['image' => 'nhuomb1.jpg', 'title' => 'Kiểm tra & đánh giá chất tóc', 'folder' => 'nhuom'],
                        ['image' => 'nhuomb2.jpg', 'title' => 'Nhuộm màu', 'folder' => 'nhuom'],
                        ['image' => 'nhuomb3.jpg', 'title' => 'Xả tóc', 'folder' => 'nhuom'],
                        ['image' => 'nhuomb4.jpg', 'title' => 'Sấy vuốt tạo kiểu', 'folder' => 'nhuom'],
                    ];
                    $bannerFolder = 'nhuom';
                } elseif ($isCatService) {
                    $serviceSteps = [
                        ['image' => 'catb1.png', 'title' => 'Tư vấn kiểu tóc', 'folder' => 'cat'],
                        ['image' => 'catb2.png', 'title' => 'Cắt tóc', 'folder' => 'cat'],
                        ['image' => 'catb3.png', 'title' => 'Xả sạch tóc', 'folder' => 'cat'],
                        ['image' => 'catb4.png', 'title' => 'Sấy tóc', 'folder' => 'cat'],
                        ['image' => 'catb5.png', 'title' => 'Tạo kiểu tóc', 'folder' => 'cat'],
                    ];
                    $bannerFolder = 'cat';
                } elseif ($isGoiService) {
                    $serviceSteps = [
                        ['image' => 'goib1.png', 'title' => 'Rửa mặt', 'folder' => 'goi'],
                        ['image' => 'goib2.png', 'title' => 'Gội đầu & Thư giãn vùng đầu', 'folder' => 'goi'],
                        ['image' => 'goib3.png', 'title' => 'Rửa tai bọt & Ngoáy tai', 'folder' => 'goi'],
                        ['image' => 'goib4.png', 'title' => 'Đắp khăn thư giãn mắt', 'folder' => 'goi'],
                        ['image' => 'goib5.png', 'title' => 'Sấy tóc', 'folder' => 'goi'],
                    ];
                    $bannerFolder = 'goi';
                }
                
            @endphp
            
            @if($service && count($serviceSteps) > 0)
                <!-- Service Section -->
                <div class="combo-service-section" style="margin-bottom: 60px; padding: 30px; background: #f9f9f9; border-radius: 15px; border-left: 5px solid #bc913f;">
                    <div class="service-header" style="margin-bottom: 30px;">
                        <div class="d-flex align-items-center" style="display: flex; align-items: center;">
                            <span class="process-bar" style="display: inline-block; width: 8px; height: 40px; background: #bc913f; margin-right: 12px; border-radius: 4px;"></span>
                            <h3 style="font-size: 32px; font-weight: 800; color: #bc913f; margin: 0; text-transform: uppercase;">
                                {{ $serviceIndex }}. {{ strtoupper($service->name) }}
                                @if($comboItem->serviceVariant)
                                    <span style="text-transform: uppercase;">- {{ strtoupper($comboItem->serviceVariant->name) }}</span>
                                @endif
                            </h3>
                        </div>
                    </div>
                    
                    <!-- Service Process Steps -->
                    <div class="process-steps-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px;">
                        @foreach($serviceSteps as $step)
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
                
                @php
                    $serviceIndex++;
                @endphp
            @endif
        @endforeach
    </div>
</div>

<!-- Booking Button -->
<div class="text-center" style="padding: 20px 0 40px 0;">
    <a href="{{ route('site.appointment.create') }}" class="btn-booking" style="display: inline-block; text-align: center; padding: 18px 50px; background: linear-gradient(135deg, #d8b26a 0%, #8b5a2b 100%); color: #fff; text-decoration: none; border-radius: 50px; font-size: 20px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.3s; box-shadow: 0 4px 15px rgba(216,178,106,0.4);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(216,178,106,0.6)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(216,178,106,0.4)';">
        <i class="fa fa-calendar-check-o" style="margin-right: 8px;"></i>
        Đặt lịch ngay
    </a>
</div>

