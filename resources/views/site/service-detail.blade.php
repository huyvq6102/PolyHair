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
                <div class="service-banner" style="background: linear-gradient(135deg, #bc913f 0%, #a88235 50%, #bc913f 100%); padding: 30px 20px; position: relative; overflow: hidden; border-radius: 20px;">
                    <!-- Decorative elements -->
                    <div style="position: absolute; left: 0; top: 0; width: 150px; height: 100%; background: linear-gradient(90deg, rgba(188, 145, 63, 0.3) 0%, transparent 100%); transform: skewX(-20deg);"></div>
                    <div style="position: absolute; right: 0; top: 0; width: 150px; height: 100%; background: linear-gradient(90deg, transparent 0%, rgba(188, 145, 63, 0.3) 100%); transform: skewX(20deg);"></div>

                    <div style="position: relative; z-index: 1;">
                        <!-- Title -->
                        <div class="text-center mb-4">
                            <h1 style="color: #fff; font-size: 32px; font-weight: 800; margin: 0; text-transform: uppercase; text-shadow: 2px 2px 8px rgba(0,0,0,0.3);">
                                {{ $service->name ?? 'Chi tiết dịch vụ' }}
                            </h1>
                        </div>

                        <!-- 3 ảnh ngẫu nhiên từ các thư mục uốn, nhuộm, cắt, gội -->
                        <div class="banner-images" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; max-width: 800px; margin: 0 auto;">
                            @php
                                // Lấy ảnh ngẫu nhiên từ tất cả các thư mục: uon, nhuom, cat, goi
                                $bannerFolders = ['uon', 'nhuom', 'cat', 'goi'];
                                $allBannerImages = [];
                                
                                // Lấy tất cả ảnh từ các thư mục
                                foreach ($bannerFolders as $folder) {
                                    $imageDir = public_path('legacy/images/' . $folder);
                                    if (is_dir($imageDir)) {
                                        $images = array_filter(scandir($imageDir), function($file) {
                                            return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']);
                                        });
                                        
                                        foreach ($images as $img) {
                                            $allBannerImages[] = [
                                                'image' => $img,
                                                'folder' => $folder
                                            ];
                                        }
                                    }
                                }
                                
                                // Xáo trộn và lấy 3 ảnh ngẫu nhiên
                                shuffle($allBannerImages);
                                $bannerImages = array_slice($allBannerImages, 0, 3);
                                
                                // Nếu không có đủ 3 ảnh, lặp lại hoặc dùng ảnh mặc định
                                while (count($bannerImages) < 3) {
                                    if (count($bannerImages) > 0 && count($allBannerImages) > 0) {
                                        $bannerImages[] = $allBannerImages[array_rand($allBannerImages)];
                                    } else {
                                        $bannerImages[] = ['image' => 'default.jpg', 'folder' => 'products'];
                                    }
                                }
                                $bannerImages = array_slice($bannerImages, 0, 3);
                            @endphp
                            @foreach($bannerImages as $bannerImg)
                                <div class="banner-image-card" style="border-radius: 12px; overflow: hidden; box-shadow: 0 8px 25px rgba(0,0,0,0.3); transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)';" onmouseout="this.style.transform='translateY(0)';">
                                    <img src="{{ asset('legacy/images/' . $bannerImg['folder'] . '/' . $bannerImg['image']) }}"
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
                        <span class="process-bar" style="display: inline-block; width: 8px; height: 40px; background: #bc913f; margin-right: 12px; border-radius: 4px;"></span>
                        <h2 class="process-title" style="font-size: 32px; font-weight: 800; color: #bc913f; margin: 0; text-transform: uppercase;">
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
                        @php
                            // Helper function để tính discount cho một item (service/variant/combo)
                            function calculateDiscount($item, $itemType, $activePromotions) {
                                $originalPrice = 0;
                                $discount = 0;
                                $finalPrice = 0;
                                $promotion = null;
                                $discountTag = '';

                                if ($itemType === 'service') {
                                    $originalPrice = $item->base_price ?? 0;
                                } elseif ($itemType === 'variant') {
                                    $originalPrice = $item->price ?? 0;
                                } elseif ($itemType === 'combo') {
                                    $originalPrice = $item->price ?? 0;
                                }

                                if ($originalPrice <= 0) {
                                    return [
                                        'originalPrice' => 0,
                                        'discount' => 0,
                                        'finalPrice' => 0,
                                        'promotion' => null,
                                        'discountTag' => ''
                                    ];
                                }

                                $now = \Carbon\Carbon::now();

                                foreach ($activePromotions ?? [] as $promo) {
                                    if ($promo->apply_scope !== 'service') {
                                        continue;
                                    }
                                    if ($promo->status !== 'active') continue;
                                    if ($promo->start_date && $promo->start_date > $now) continue;
                                    if ($promo->end_date && $promo->end_date < $now) continue;
                                    
                                    if ($promo->usage_limit) {
                                        $totalUsage = \App\Models\PromotionUsage::where('promotion_id', $promo->id)->count();
                                        if ($totalUsage >= $promo->usage_limit) {
                                            continue;
                                        }
                                    }
                                    
                                    if ($promo->per_user_limit) {
                                        $userId = auth()->id();
                                        if ($userId) {
                                            $userUsage = \App\Models\PromotionUsage::where('promotion_id', $promo->id)
                                                ->where('user_id', $userId)
                                                ->whereHas('appointment', function($query) {
                                                    $query->where('status', 'Đã thanh toán');
                                                })
                                                ->count();
                                            if ($userUsage >= $promo->per_user_limit) {
                                                continue;
                                            }
                                        }
                                    }

                                    $applies = false;

                                    if ($itemType === 'variant') {
                                        $hasSpecificServices = ($promo->services && $promo->services->count() > 0)
                                            || ($promo->combos && $promo->combos->count() > 0)
                                            || ($promo->serviceVariants && $promo->serviceVariants->count() > 0);
                                        $applyToAll = !$hasSpecificServices ||
                                            (($promo->services ? $promo->services->count() : 0) +
                                             ($promo->combos ? $promo->combos->count() : 0) +
                                             ($promo->serviceVariants ? $promo->serviceVariants->count() : 0)) >= 20;
                                        if ($applyToAll) {
                                            $applies = true;
                                        } elseif ($promo->serviceVariants && $promo->serviceVariants->contains('id', $item->id)) {
                                            $applies = true;
                                        } elseif ($item->service_id && $promo->services && $promo->services->contains('id', $item->service_id)) {
                                            $applies = true;
                                        }
                                    }

                                    if ($applies) {
                                        $currentDiscount = 0;

                                        if ($promo->discount_type === 'percent') {
                                            $currentDiscount = ($originalPrice * ($promo->discount_percent ?? 0)) / 100;
                                            if ($promo->max_discount_amount) {
                                                $currentDiscount = min($currentDiscount, $promo->max_discount_amount);
                                            }
                                            $currentTag = '-' . ($promo->discount_percent ?? 0) . '%';
                                        } else {
                                            $currentDiscount = min($promo->discount_amount ?? 0, $originalPrice);
                                            $currentTag = '';
                                        }

                                        if ($currentDiscount > $discount) {
                                            $discount = $currentDiscount;
                                            $promotion = $promo;
                                            $discountTag = $currentTag;
                                        }
                                    }
                                }
                                
                                $finalPrice = max(0, $originalPrice - $discount);
                                
                                return [
                                    'originalPrice' => $originalPrice,
                                    'discount' => $discount,
                                    'finalPrice' => $finalPrice,
                                    'promotion' => $promotion,
                                    'discountTag' => $discountTag
                                ];
                            }
                        @endphp
                        <div class="col-xl-12" style="padding: 0 15px; margin-bottom: 30px;">
                            <h3 style="color: #bc913f; font-size: 24px; font-weight: 600; margin-bottom: 20px; border-bottom: 2px solid #bc913f; padding-bottom: 10px;">
                                Các gói dịch vụ
                            </h3>
                            <div class="row" style="margin: 0 -8px;">
                                @foreach($service->serviceVariants as $variant)
                                    @php
                                        // Load variant attributes nếu chưa được load
                                        if (!$variant->relationLoaded('variantAttributes')) {
                                            $variant->load('variantAttributes');
                                        }
                                        
                                        // Tính discount cho variant
                                        $variantDiscount = calculateDiscount($variant, 'variant', $activePromotions ?? collect());
                                        $formattedVariantOriginalPrice = number_format($variantDiscount['originalPrice'], 0, ',', '.');
                                        $formattedVariantFinalPrice = number_format($variantDiscount['finalPrice'], 0, ',', '.');
                                    @endphp
                                    <div class="col-md-6" style="padding: 0 8px; margin-bottom: 15px;">
                                        <div class="variant-card" data-variant-id="{{ $variant->id }}" style="border: 1px solid #e5e5e5; border-radius: 8px; padding: 20px; background: #fff; transition: all 0.3s; height: 100%; cursor: pointer; position: relative;" onmouseover="if(!this.classList.contains('selected')) { this.style.borderColor='#d8b26a'; this.style.boxShadow='0 2px 10px rgba(216,178,106,0.2)'; }" onmouseout="if(!this.classList.contains('selected')) { this.style.borderColor='#e5e5e5'; this.style.boxShadow='none'; }" onclick="selectVariant({{ $variant->id }}, this);">
                                            <!-- Tên variant và badge discount cùng hàng -->
                                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                                <h4 style="color: #4A3600; font-size: 18px; font-weight: 600; margin: 0; flex: 1;">
                                                    {{ $variant->name }}
                                                </h4>
                                                @if($variantDiscount['discount'] > 0 && $variantDiscount['discountTag'])
                                                    <span style="background: #ff4444; color: #fff; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; white-space: nowrap; margin-left: 10px;">{{ $variantDiscount['discountTag'] }}</span>
                                                @endif
                                            </div>
                                            
                                            <!-- Variant Attributes -->
                                            @if($variant->variantAttributes && $variant->variantAttributes->count() > 0)
                                                <div class="variant-attributes" style="margin-bottom: 12px; display: flex; flex-wrap: wrap; gap: 6px;">
                                                    @foreach($variant->variantAttributes as $attr)
                                                        <span style="display: inline-block; background: #f5f5f5; color: #666; font-size: 12px; padding: 4px 10px; border-radius: 6px; border: 1px solid #e5e5e5;">
                                                            <strong style="color: #333;">{{ $attr->attribute_name }}:</strong> {{ $attr->attribute_value }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                            
                                            <div class="variant-price" style="margin-bottom: 8px;">
                                                @if($variantDiscount['discount'] > 0)
                                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                                        <span style="text-decoration: line-through; color: #999; font-size: 14px;">{{ $formattedVariantOriginalPrice }}đ</span>
                                                        <strong style="color: #BC9321; font-size: 24px; font-weight: 700;">
                                                            {{ $formattedVariantFinalPrice }}đ
                                                        </strong>
                                                    </div>
                                                @else
                                                    <strong style="color: #BC9321; font-size: 24px; font-weight: 700;">
                                                        {{ $formattedVariantOriginalPrice }}đ
                                                    </strong>
                                                @endif
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
                @php
                    // Nếu dịch vụ có variants, sẽ dùng service_variants khi đã chọn
                    // Nếu không có variants, truyền service_id để đặt dịch vụ đơn
                    $bookingParams = ['service_id' => [$service->id]];
                @endphp
                @if($service->serviceVariants && $service->serviceVariants->count() > 0)
                    <a href="#" id="mainBookingBtn" class="btn-booking" onclick="handleBookingClick(event); return false;" style="display: inline-block; text-align: center; padding: 18px 50px; background: linear-gradient(135deg, #d8b26a 0%, #8b5a2b 100%); color: #fff; text-decoration: none; border-radius: 50px; font-size: 20px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.3s; box-shadow: 0 4px 15px rgba(216,178,106,0.4); cursor: pointer;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(216,178,106,0.6)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(216,178,106,0.4)';">
                        <i class="fa fa-calendar-check-o" style="margin-right: 8px;"></i>
                        Đặt lịch ngay
                    </a>
                @else
                    <a href="{{ route('site.appointment.create', $bookingParams) }}" id="mainBookingBtn" class="btn-booking" style="display: inline-block; text-align: center; padding: 18px 50px; background: linear-gradient(135deg, #d8b26a 0%, #8b5a2b 100%); color: #fff; text-decoration: none; border-radius: 50px; font-size: 20px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.3s; box-shadow: 0 4px 15px rgba(216,178,106,0.4);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(216,178,106,0.6)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(216,178,106,0.4)';">
                        <i class="fa fa-calendar-check-o" style="margin-right: 8px;"></i>
                        Đặt lịch ngay
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let selectedVariantId = null;
    const baseBookingUrl = '{{ route("site.appointment.create") }}';
    
    function selectVariant(variantId, element) {
        // Remove selected class from all variant cards
        document.querySelectorAll('.variant-card').forEach(card => {
            card.classList.remove('selected');
            card.style.borderColor = '#e5e5e5';
            card.style.boxShadow = 'none';
            card.style.background = '#fff';
        });
        
        // Add selected class to clicked card
        element.classList.add('selected');
        element.style.borderColor = '#BC9321';
        element.style.boxShadow = '0 4px 15px rgba(188, 145, 33, 0.4)';
        element.style.background = '#fffef5';
        
        selectedVariantId = variantId;
    }
    
    function handleBookingClick(event) {
        event.preventDefault();
        
        if (!selectedVariantId) {
            alert('Vui lòng chọn một gói dịch vụ trước khi đặt lịch!');
            return false;
        }
        
        // Redirect to booking page with selected variant ID
        const bookingUrl = baseBookingUrl + '?service_variants[]=' + selectedVariantId;
        window.location.href = bookingUrl;
        
        return false;
    }
    
    // Initialize: if there's only one variant, auto-select it
    document.addEventListener('DOMContentLoaded', function() {
        const variantCards = document.querySelectorAll('.variant-card');
        if (variantCards.length === 1) {
            // Auto-select the only variant
            const firstCard = variantCards[0];
            const variantId = firstCard.getAttribute('data-variant-id');
            if (variantId) {
                selectVariant(variantId, firstCard);
            }
        }
    });
</script>

<style>
    .variant-card.selected {
        border-color: #BC9321 !important;
        box-shadow: 0 4px 15px rgba(188, 145, 33, 0.4) !important;
        background: #fffef5 !important;
    }
    
    .variant-card.selected:hover {
        border-color: #BC9321 !important;
        box-shadow: 0 4px 15px rgba(188, 145, 33, 0.4) !important;
    }
</style>
@endpush
