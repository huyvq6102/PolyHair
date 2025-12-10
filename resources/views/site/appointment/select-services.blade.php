@extends('layouts.site')

@section('title', 'Chọn dịch vụ')

@php
    // Helper function để merge query parameters khi add_more
    function buildServiceUrl($newParams = []) {
        $currentParams = request()->all();
        $isAddMore = isset($currentParams['add_more']) && $currentParams['add_more'];
        
        if ($isAddMore) {
            // Merge với các dịch vụ đã chọn
            $mergedParams = $currentParams;
            
            // Merge service_id
            if (isset($newParams['service_id'])) {
                $existingIds = isset($mergedParams['service_id']) ? (is_array($mergedParams['service_id']) ? $mergedParams['service_id'] : [$mergedParams['service_id']]) : [];
                $newId = is_array($newParams['service_id']) ? $newParams['service_id'][0] : $newParams['service_id'];
                if (!in_array($newId, $existingIds)) {
                    $existingIds[] = $newId;
                }
                $mergedParams['service_id'] = $existingIds;
            }
            
            // Merge service_variants
            if (isset($newParams['service_variants'])) {
                $existingVariants = isset($mergedParams['service_variants']) ? (is_array($mergedParams['service_variants']) ? $mergedParams['service_variants'] : [$mergedParams['service_variants']]) : [];
                $newVariants = is_array($newParams['service_variants']) ? $newParams['service_variants'] : [$newParams['service_variants']];
                foreach ($newVariants as $variant) {
                    if (!in_array($variant, $existingVariants)) {
                        $existingVariants[] = $variant;
                    }
                }
                $mergedParams['service_variants'] = $existingVariants;
            }
            
            // Merge combo_id
            if (isset($newParams['combo_id'])) {
                $existingCombos = isset($mergedParams['combo_id']) ? (is_array($mergedParams['combo_id']) ? $mergedParams['combo_id'] : [$mergedParams['combo_id']]) : [];
                $newCombo = is_array($newParams['combo_id']) ? $newParams['combo_id'][0] : $newParams['combo_id'];
                if (!in_array($newCombo, $existingCombos)) {
                    $existingCombos[] = $newCombo;
                }
                $mergedParams['combo_id'] = $existingCombos;
            }
            
            // Xóa add_more khỏi params
            unset($mergedParams['add_more']);
            
            return route('site.appointment.create', $mergedParams);
        } else {
            // Không có add_more, chỉ dùng params mới
            return route('site.appointment.create', $newParams);
        }
    }
    
    // Get all services for filter tags
    $allServices = \App\Models\Service::whereNull('deleted_at')
        ->where('status', 'Hoạt động')
        ->orderBy('name', 'asc')
        ->get();
    
    // Common filter tags based on service names
    $filterTags = [
        'Cắt tóc', 'Gội đầu', 'Uốn', 'Nhuộm', 'Phục hồi', 
        'Chăm sóc da', 'Massage', 'Tẩy da chết', 'Mặt nạ'
    ];
    
    // Tính toán dịch vụ đã chọn từ URL parameters
    $selectedServiceIds = [];
    $selectedVariantIds = [];
    $selectedComboIds = [];
    $totalPrice = 0;
    $selectedCount = 0;
    
    // Lấy service_id từ query
    if (request('service_id')) {
        $queryServices = request()->query('service_id', []);
        if (!is_array($queryServices)) {
            $queryServices = $queryServices ? [$queryServices] : [];
        }
        $selectedServiceIds = array_filter($queryServices, function($id) {
            return !empty($id) && $id !== '0' && is_numeric($id);
        });
        $selectedServiceIds = array_values(array_unique($selectedServiceIds));
        
        // Tính giá từ services
        foreach ($selectedServiceIds as $serviceId) {
            $service = \App\Models\Service::find($serviceId);
            if ($service) {
                $totalPrice += $service->base_price ?? 0;
                $selectedCount++;
            }
        }
    }
    
    // Lấy service_variants từ query
    if (request()->has('service_variants')) {
        $url = request()->fullUrl();
        $parsedUrl = parse_url($url);
        $queryParams = [];
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryParams);
        }
        
        $queryVariants = [];
        if (isset($queryParams['service_variants']) && is_array($queryParams['service_variants'])) {
            $queryVariants = $queryParams['service_variants'];
        } elseif (isset($queryParams['service_variants'])) {
            $queryVariants = [$queryParams['service_variants']];
        }
        
        // Check for indexed format service_variants[0], etc.
        foreach ($queryParams as $key => $value) {
            if (preg_match('/^service_variants\[(\d+)\]$/', $key, $matches)) {
                $queryVariants[] = $value;
            }
        }
        
        $selectedVariantIds = array_filter($queryVariants, function($id) {
            return !empty($id) && $id !== '0' && is_numeric($id);
        });
        $selectedVariantIds = array_values(array_unique($selectedVariantIds));
        
        // Tính giá từ variants
        foreach ($selectedVariantIds as $variantId) {
            $variant = \App\Models\ServiceVariant::find($variantId);
            if ($variant) {
                $totalPrice += $variant->price ?? 0;
                $selectedCount++;
            }
        }
    }
    
    // Lấy combo_id từ query
    if (request('combo_id')) {
        $queryCombos = request()->query('combo_id', []);
        if (!is_array($queryCombos)) {
            $queryCombos = $queryCombos ? [$queryCombos] : [];
        }
        $selectedComboIds = array_filter($queryCombos, function($id) {
            return !empty($id) && $id !== '0' && is_numeric($id);
        });
        $selectedComboIds = array_values(array_unique($selectedComboIds));
        
        // Tính giá từ combos
        foreach ($selectedComboIds as $comboId) {
            $combo = \App\Models\Combo::find($comboId);
            if ($combo) {
                $totalPrice += $combo->price ?? 0;
                $selectedCount++;
            }
        }
    }
    
    $formattedTotalPrice = number_format($totalPrice, 0, ',', '.');
@endphp

@section('content')
<div class="select-services-page" style="padding: 140px 0 80px; background: #f8f9fa; min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-7">
                <!-- Main Container -->
                <div class="select-services-container" style="background: #fff; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 25px; margin-bottom: 20px;">
                    
                    <!-- Header with Back Button -->
                    <div class="header-with-back mb-4" style="display: flex; align-items: center; justify-content: center; position: relative; margin-bottom: 20px;">
                        <!-- Back Arrow Button -->
                        <a href="{{ route('site.appointment.create') }}" 
                           class="back-arrow-btn" 
                           style="position: absolute; left: 0; display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; color: #000; text-decoration: none; border-radius: 50%; transition: all 0.3s ease; background: transparent;">
                            <i class="fa fa-arrow-left" style="font-size: 18px;"></i>
                        </a>
                        
                        <!-- Title -->
                        <div class="text-center" style="flex: 1;">
                            <h2 class="fw-bold mb-2" style="color: #000; font-size: 20px; margin-bottom: 8px;">
                                <i class="fa fa-scissors"></i> CHỌN DỊCH VỤ
                            </h2>
                            <p class="text-muted mb-0" style="font-size: 13px; color: #666; margin: 0;">
                                Vui lòng chọn dịch vụ bạn muốn đặt lịch
                            </p>
                        </div>
                    </div>

                    <!-- Search and Filter Section -->
                    <div class="search-filter-section mb-4" style="margin-bottom: 25px;">
                        <!-- Search Bar -->
                        <div class="search-bar mb-3" style="margin-bottom: 15px;">
                            <form method="GET" action="{{ route('site.appointment.select-services') }}" id="searchForm">
                                <div class="input-group">
                                    <input type="text" 
                                           name="search" 
                                           id="searchInput"
                                           class="form-control" 
                                           placeholder="Tìm kiếm dịch vụ..." 
                                           value="{{ request('search') }}"
                                           style="font-size: 13px; padding: 10px 15px; border: 1px solid #ddd; border-radius: 8px 0 0 8px; height: 42px;">
                                    <button type="submit" 
                                            class="btn btn-primary" 
                                            style="background: #000; border: 1px solid #000; color: #fff; padding: 10px 20px; border-radius: 0 8px 8px 0; height: 42px; font-size: 13px; font-weight: 600;">
                                        <i class="fa fa-search"></i> Tìm kiếm
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Filter Tags -->
                        <div class="filter-tags" style="display: flex; flex-wrap: wrap; gap: 8px;">
                            @foreach($filterTags as $tag)
                                <button type="button" 
                                        class="filter-tag-btn" 
                                        data-tag="{{ strtolower($tag) }}"
                                        style="padding: 6px 14px; font-size: 12px; border: 1px solid #ddd; background: #fff; color: #333; border-radius: 20px; cursor: pointer; transition: all 0.3s ease; font-weight: 500;">
                                    {{ $tag }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Services by Category -->
                    @forelse($categories ?? [] as $category)
                        @php
                            $hasServices = $category->services && $category->services->count() > 0;
                            $hasCombos = isset($category->combos) && $category->combos->count() > 0;
                            
                            // Filter by search if exists
                            $searchTerm = request('search');
                            if ($searchTerm) {
                                $hasServices = $category->services->filter(function($service) use ($searchTerm) {
                                    return stripos($service->name, $searchTerm) !== false;
                                })->count() > 0;
                            }
                        @endphp
                        @if($hasServices || $hasCombos)
                            <!-- Category Header -->
                            <div class="category-section" style="margin-top: 30px; margin-bottom: 20px;">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="bar mr-2" style="width: 4px; height: 24px; background: #000; border-radius: 2px;"></span>
                                    <h3 class="category-title mb-0" style="font-size: 18px; font-weight: 700; text-transform: uppercase; color: #000;">
                                        {{ $category->name }}
                                    </h3>
                                </div>
                                
                                <!-- Service Grid for this Category -->
                                <div class="service-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 15px;">
                                    <!-- Services in this Category -->
                                    @foreach($category->services as $service)
                                        @php
                                            // Filter by search
                                            if ($searchTerm && stripos($service->name, $searchTerm) === false) {
                                                continue;
                                            }
                                            
                                            $imagePath = $service->image ? 'legacy/images/products/' . $service->image : null;
                                            $formattedPrice = number_format($service->base_price ?? 0, 0, ',', '.');
                                            $hasVariants = $service->serviceVariants && $service->serviceVariants->count() > 0;
                                            
                                            // Tính duration
                                            $serviceDuration = $service->base_duration ?? 60;
                                            if ($hasVariants && $service->serviceVariants->count() > 0) {
                                                // Lấy duration tối thiểu từ các variants
                                                $serviceDuration = $service->serviceVariants->min('duration') ?? 60;
                                            }
                                        @endphp
                                        <div class="svc-card service-card-wrapper" 
                                             data-service-id="{{ $service->id }}"
                                             data-service-name="{{ strtolower($service->name) }}"
                                             style="border: 1px solid #e0e0e0; box-shadow: 0 2px 8px rgba(0,0,0,0.08); background: #fff; display: flex; flex-direction: column; border-radius: 8px; overflow: hidden; position: relative; transition: all 0.3s ease;">
                                            
                                            <!-- Card Image -->
                                            <div class="svc-img" style="overflow: hidden; display: block; height: 180px; background: #f5f5f5; position: relative;">
                                                <!-- Duration Badge -->
                                                <div class="duration-badge" style="position: absolute; top: 8px; left: 8px; background: #0066cc; color: #fff; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; z-index: 10; box-shadow: 0 2px 6px rgba(0,0,0,0.2);">
                                                    {{ $serviceDuration }}p
                                                </div>
                                                @if($imagePath && file_exists(public_path($imagePath)))
                                                    <img src="{{ asset($imagePath) }}" alt="{{ $service->name }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                                                @elseif($service->image)
                                                    <img src="{{ asset('legacy/images/products/' . $service->image) }}" alt="{{ $service->name }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onerror="this.src='{{ asset('legacy/images/products/default.jpg') }}'; this.onerror=null;">
                                                @else
                                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);">
                                                        <i class="fa fa-image" style="font-size: 36px; color: #ccc;"></i>
                                                    </div>
                                                @endif
                                                
                                                <!-- Optional: Heart icon (can be added later) -->
                                                <div class="card-overlay-icons" style="position: absolute; top: 8px; right: 8px; display: flex; gap: 6px; z-index: 10;">
                                                    <!-- Can add heart/share icons here -->
                                                </div>
                                            </div>
                                            
                                            <!-- Card Body -->
                                            <div class="svc-body" style="padding: 12px; display: flex; flex-direction: column; flex-grow: 1;">
                                                <div class="svc-info" style="flex-grow: 1; margin-bottom: 10px;">
                                                    <h4 class="svc-name" style="margin: 0 0 8px 0; font-weight: 600; font-size: 14px; line-height: 1.3; color: #000; min-height: 36px;">
                                                        <a href="{{ route('site.services.show', $service->id) }}" style="color: inherit; text-decoration: none;">{{ $service->name }}</a>
                                                    </h4>
                                                    @if(!$hasVariants)
                                                        <div class="svc-price" style="font-size: 13px; color: #333; font-weight: 600;">
                                                            <span style="color: #BC9321; font-weight: 700;">{{ $formattedPrice }} VND</span>
                                                        </div>
                                                    @else
                                                        <div class="svc-variants" style="font-size: 11px; color: #666; margin-bottom: 4px;">
                                                            <i class="fa fa-info-circle"></i> Có {{ $service->serviceVariants->count() }} dịch vụ
                                                        </div>
                                                        <div class="svc-price" style="font-size: 12px; color: #666;">
                                                            Từ <span style="color: #BC9321; font-weight: 700;">{{ $formattedPrice }} VND</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                <!-- Card Actions -->
                                                <div class="svc-actions" style="margin-top: auto; position: relative;">
                                                    @if($hasVariants)
                                                        <!-- Variants Tooltip -->
                                                        <div class="variants-tooltip" 
                                                             data-service-id="{{ $service->id }}"
                                                             style="display: none; position: absolute; left: calc(100% + 8px); top: 50%; transform: translateY(-50%); background: #fff; border: 2px solid #007bff; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.2); z-index: 1000; min-width: 320px; max-width: 400px; padding: 0; overflow: hidden;">
                                                            <!-- Tooltip Arrow -->
                                                            <div style="position: absolute; left: -10px; top: 50%; transform: translateY(-50%); width: 0; height: 0; border-top: 10px solid transparent; border-bottom: 10px solid transparent; border-right: 10px solid #007bff;"></div>
                                                            <div style="position: absolute; left: -8px; top: 50%; transform: translateY(-50%); width: 0; height: 0; border-top: 10px solid transparent; border-bottom: 10px solid transparent; border-right: 10px solid #fff;"></div>
                                                            
                                                            <!-- Hover Bridge -->
                                                            <div class="hover-bridge" style="position: absolute; left: -8px; top: 0; bottom: 0; width: 8px; z-index: 999; pointer-events: auto;"></div>
                                                            
                                                            <!-- Header -->
                                                            <div class="tooltip-header" style="background: #007bff; padding: 12px 16px; border-bottom: 2px solid #007bff;">
                                                                <h5 style="margin: 0; font-size: 14px; font-weight: 700; color: #fff; text-transform: uppercase;">
                                                                    <i class="fa fa-list-ul" style="margin-right: 6px;"></i> Các dịch vụ
                                                                </h5>
                                                                <p style="margin: 4px 0 0 0; font-size: 11px; color: #e0f0ff;">{{ $service->name }}</p>
                                                            </div>
                                                            
                                                            <!-- Body -->
                                                            <div class="tooltip-body" style="padding: 10px; max-height: 280px; overflow-y: auto;">
                                                                @foreach($service->serviceVariants as $variant)
                                                                    @php
                                                                        $variantPrice = number_format($variant->price ?? 0, 0, ',', '.');
                                                                    @endphp
                                                                    <div class="variant-item-link" 
                                                                         data-variant-id="{{ $variant->id }}"
                                                                         data-variant-price="{{ $variant->price ?? 0 }}"
                                                                         data-variant-name="{{ $service->name }} - {{ $variant->name }}"
                                                                         style="text-decoration: none; display: block; cursor: pointer;">
                                                                        <div class="variant-item" style="padding: 10px; margin-bottom: 6px; background: #fff; border: 1px solid #e0e0e0; border-radius: 6px; transition: all 0.3s ease; cursor: pointer;">
                                                                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                                                                <div style="flex: 1;">
                                                                                    <div style="display: flex; align-items: center; margin-bottom: 4px;">
                                                                                        <div style="width: 4px; height: 4px; background: #007bff; border-radius: 50%; margin-right: 6px;"></div>
                                                                                        <div style="font-weight: 600; color: #000; font-size: 13px; line-height: 1.3;">{{ $variant->name }}</div>
                                                                                    </div>
                                                                                    <div style="display: flex; align-items: center; gap: 10px; margin-top: 4px;">
                                                                                        <div style="display: flex; align-items: center; font-size: 11px; color: #666; background: #f5f5f5; padding: 3px 6px; border-radius: 4px;">
                                                                                            <i class="fa fa-clock-o" style="margin-right: 3px; color: #888;"></i>
                                                                                            <span style="font-weight: 500;">{{ $variant->duration ?? 60 }} phút</span>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div style="text-align: right; margin-left: 10px;">
                                                                                    <div style="font-weight: 700; color: #007bff; font-size: 14px; line-height: 1.2;">
                                                                                        {{ $variantPrice }}<span style="font-size: 11px; font-weight: 600;">VND</span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                    
                                                    @if($hasVariants)
                                                        <button type="button"
                                                                class="btn btn-primary w-100 select-service-btn" 
                                                                data-has-variants="true"
                                                                data-service-id="{{ $service->id }}"
                                                                style="background: #000; border: 1px solid #000; color: #fff; padding: 8px 12px; font-size: 12px; font-weight: 600; border-radius: 6px; transition: all 0.3s ease; text-decoration: none; display: inline-block; text-align: center; position: relative; z-index: 1; cursor: pointer; width: 100%;">
                                                            <i class="fa fa-check"></i> Chọn
                                                        </button>
                                                    @else
                                                        <button type="button"
                                                                class="btn btn-primary w-100 select-service-btn" 
                                                                data-has-variants="false"
                                                                data-service-id="{{ $service->id }}"
                                                                data-service-price="{{ $service->base_price ?? 0 }}"
                                                                data-service-name="{{ $service->name }}"
                                                                style="background: #000; border: 1px solid #000; color: #fff; padding: 8px 12px; font-size: 12px; font-weight: 600; border-radius: 6px; transition: all 0.3s ease; text-decoration: none; display: inline-block; text-align: center; position: relative; z-index: 1; cursor: pointer; width: 100%;">
                                                            <i class="fa fa-check"></i> Chọn
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    
                                    <!-- Combos in this Category -->
                                    @if($category->combos && $category->combos->count() > 0)
                                        @foreach($category->combos as $combo)
                                            @php
                                                // Filter by search
                                                if ($searchTerm && stripos($combo->name, $searchTerm) === false) {
                                                    continue;
                                                }
                                                
                                                $imagePath = $combo->image ? 'legacy/images/products/' . $combo->image : null;
                                                $formattedPrice = number_format($combo->price ?? 0, 0, ',', '.');
                                                // Tính duration từ combo items
                                                $comboDuration = 60;
                                                if ($combo->comboItems && $combo->comboItems->count() > 0) {
                                                    $comboDuration = $combo->comboItems->sum(function($item) {
                                                        return $item->serviceVariant ? ($item->serviceVariant->duration ?? 60) : 60;
                                                    });
                                                }
                                            @endphp
                                            <div class="svc-card service-card-wrapper" 
                                                 data-combo-id="{{ $combo->id }}"
                                                 data-service-name="{{ strtolower($combo->name) }}"
                                                 style="border: 1px solid #e0e0e0; box-shadow: 0 2px 8px rgba(0,0,0,0.08); background: #fff; display: flex; flex-direction: column; border-radius: 8px; overflow: hidden; position: relative; transition: all 0.3s ease;">
                                                <div class="svc-img" style="overflow: hidden; display: block; height: 180px; background: #f5f5f5; position: relative;">
                                                    <!-- Duration Badge -->
                                                    <div class="duration-badge" style="position: absolute; top: 8px; left: 8px; background: #0066cc; color: #fff; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; z-index: 10; box-shadow: 0 2px 6px rgba(0,0,0,0.2);">
                                                        {{ $comboDuration }}p
                                                    </div>
                                                    @if($imagePath && file_exists(public_path($imagePath)))
                                                        <img src="{{ asset($imagePath) }}" alt="{{ $combo->name }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                                                    @elseif($combo->image)
                                                        <img src="{{ asset('legacy/images/products/' . $combo->image) }}" alt="{{ $combo->name }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onerror="this.src='{{ asset('legacy/images/products/default.jpg') }}'; this.onerror=null;">
                                                    @else
                                                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);">
                                                            <i class="fa fa-image" style="font-size: 36px; color: #ccc;"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="svc-body" style="padding: 12px; display: flex; flex-direction: column; flex-grow: 1;">
                                                    <div class="svc-info" style="flex-grow: 1; margin-bottom: 10px;">
                                                        <h4 class="svc-name" style="margin: 0 0 8px 0; font-weight: 600; font-size: 14px; line-height: 1.3; color: #000; min-height: 36px;">
                                                            <a href="#" style="color: inherit; text-decoration: none;">{{ $combo->name }}</a>
                                                            <span style="color: #BC9321; font-size: 11px; font-weight: 600; margin-left: 4px;">(COMBO)</span>
                                                        </h4>
                                                        <div class="svc-price" style="font-size: 13px; color: #333; font-weight: 600; margin-bottom: 4px;">
                                                            <span style="color: #BC9321; font-weight: 700;">{{ $formattedPrice }} VND</span>
                                                        </div>
                                                        <div style="font-size: 11px; color: #666;">
                                                            <i class="fa fa-clock-o"></i> Thời gian: <strong>{{ $comboDuration }} phút</strong>
                                                        </div>
                                                    </div>
                                                    <div class="svc-actions" style="margin-top: auto; position: relative;">
                                                        <button type="button"
                                                                class="btn btn-primary w-100 select-service-btn" 
                                                                data-has-variants="false"
                                                                data-combo-id="{{ $combo->id }}"
                                                                data-combo-price="{{ $combo->price ?? 0 }}"
                                                                data-combo-name="{{ $combo->name }}"
                                                                style="background: #000; border: 1px solid #000; color: #fff; padding: 8px 12px; font-size: 12px; font-weight: 600; border-radius: 6px; transition: all 0.3s ease; text-decoration: none; display: inline-block; text-align: center; position: relative; z-index: 1; cursor: pointer; width: 100%;">
                                                            <i class="fa fa-check"></i> Chọn
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="col-12 text-center" style="margin-top: 40px; padding: 40px;">
                            <p class="text-muted" style="font-size: 14px; color: #666;">Chưa có dịch vụ nào</p>
                        </div>
                    @endforelse

                    <!-- Combos Without Category Section (if any) -->
                    @if(isset($combosWithoutCategory) && $combosWithoutCategory->count() > 0)
                        <div class="category-section" style="margin-top: 30px; margin-bottom: 20px;">
                            <div class="d-flex align-items-center mb-3">
                                <span class="bar mr-2" style="width: 4px; height: 24px; background: #000; border-radius: 2px;"></span>
                                <h3 class="category-title mb-0" style="font-size: 18px; font-weight: 700; text-transform: uppercase; color: #000;">
                                    COMBO
                                </h3>
                            </div>
                            
                            <!-- Combo Grid -->
                            <div class="service-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 15px;">
                                @foreach($combosWithoutCategory as $combo)
                                    @php
                                        $imagePath = $combo->image ? 'legacy/images/products/' . $combo->image : null;
                                        $formattedPrice = number_format($combo->price ?? 0, 0, ',', '.');
                                        // Tính duration từ combo items
                                        $comboDuration = 60;
                                        if ($combo->comboItems && $combo->comboItems->count() > 0) {
                                            $comboDuration = $combo->comboItems->sum(function($item) {
                                                return $item->serviceVariant ? ($item->serviceVariant->duration ?? 60) : 60;
                                            });
                                        }
                                    @endphp
                                    <div class="svc-card service-card-wrapper" 
                                         data-combo-id="{{ $combo->id }}"
                                         data-service-name="{{ strtolower($combo->name) }}"
                                         style="border: 1px solid #e0e0e0; box-shadow: 0 2px 8px rgba(0,0,0,0.08); background: #fff; display: flex; flex-direction: column; border-radius: 8px; overflow: hidden; position: relative; transition: all 0.3s ease;">
                                        <div class="svc-img" style="overflow: hidden; display: block; height: 180px; background: #f5f5f5; position: relative;">
                                            <!-- Duration Badge -->
                                            <div class="duration-badge" style="position: absolute; top: 8px; left: 8px; background: #0066cc; color: #fff; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; z-index: 10; box-shadow: 0 2px 6px rgba(0,0,0,0.2);">
                                                {{ $comboDuration }}p
                                            </div>
                                            @if($imagePath && file_exists(public_path($imagePath)))
                                                <img src="{{ asset($imagePath) }}" alt="{{ $combo->name }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                                            @elseif($combo->image)
                                                <img src="{{ asset('legacy/images/products/' . $combo->image) }}" alt="{{ $combo->name }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onerror="this.src='{{ asset('legacy/images/products/default.jpg') }}'; this.onerror=null;">
                                            @else
                                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);">
                                                    <i class="fa fa-image" style="font-size: 36px; color: #ccc;"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="svc-body" style="padding: 12px; display: flex; flex-direction: column; flex-grow: 1;">
                                            <div class="svc-info" style="flex-grow: 1; margin-bottom: 10px;">
                                                <h4 class="svc-name" style="margin: 0 0 8px 0; font-weight: 600; font-size: 14px; line-height: 1.3; color: #000; min-height: 36px;">
                                                    <a href="#" style="color: inherit; text-decoration: none;">{{ $combo->name }}</a>
                                                    <span style="color: #BC9321; font-size: 11px; font-weight: 600; margin-left: 4px;">(COMBO)</span>
                                                </h4>
                                                <div class="svc-price" style="font-size: 13px; color: #333; font-weight: 600; margin-bottom: 4px;">
                                                    <span style="color: #BC9321; font-weight: 700;">{{ $formattedPrice }} VND</span>
                                                </div>
                                                <div style="font-size: 11px; color: #666;">
                                                    <i class="fa fa-clock-o"></i> Thời gian: <strong>{{ $comboDuration }} phút</strong>
                                                </div>
                                            </div>
                                            <div class="svc-actions" style="margin-top: auto; position: relative;">
                                                <button type="button"
                                                        class="btn btn-primary w-100 select-service-btn" 
                                                        data-has-variants="false"
                                                        data-combo-id="{{ $combo->id }}"
                                                        data-combo-price="{{ $combo->price ?? 0 }}"
                                                        style="background: #000; border: 1px solid #000; color: #fff; padding: 8px 12px; font-size: 12px; font-weight: 600; border-radius: 6px; transition: all 0.3s ease; text-decoration: none; display: inline-block; text-align: center; position: relative; z-index: 1; cursor: pointer; width: 100%;">
                                                    <i class="fa fa-check"></i> Chọn
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Summary Container (Fixed above footer, same width as service cards) -->
<div class="summary-wrapper" style="position: fixed; bottom: 0; left: 0; right: 0; z-index: 1000; pointer-events: none;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-7">
                <div class="summary-container" style="position: relative; pointer-events: auto;">
                    <!-- Summary Bar (Always Visible at Bottom) -->
                    <div class="summary-bar" id="summaryBar" style="background: #fff; border-top: 1px solid #e0e0e0; padding: 12px 20px; box-shadow: 0 -2px 10px rgba(0,0,0,0.1); border-radius: 0 0 15px 15px; display: flex; align-items: center; justify-content: space-between; position: relative; z-index: 2;">
                        <div class="summary-left" style="display: flex; align-items: center;">
                            <a href="#" id="selectedServicesLink" style="color: #666; text-decoration: underline; font-size: 14px; cursor: pointer;">
                                Đã chọn <span id="selectedCount">{{ $selectedCount }}</span> dịch vụ
                            </a>
                        </div>
                        <div class="summary-right" style="display: flex; align-items: center; gap: 20px;">
                            <div class="total-price-section" style="text-align: right;">
                                <div style="font-size: 12px; color: #666; margin-bottom: 2px;">Tổng thanh toán</div>
                                <div id="totalPrice" style="font-size: 18px; font-weight: 700; color: #333;">
                                    {{ $formattedTotalPrice }} VNĐ
                                </div>
                            </div>
                            <button type="button"
                                    id="doneButton"
                                    class="btn-done {{ $selectedCount > 0 ? 'active' : '' }}" 
                                    style="padding: 10px 24px; border-radius: 6px; font-size: 14px; font-weight: 600; transition: all 0.3s ease; border: none; cursor: pointer; {{ $selectedCount > 0 ? 'background: #007bff; color: #fff;' : 'background: #e0e0e0; color: #666;' }}">
                                Xong
                            </button>
                        </div>
                    </div>
                    
                    <!-- Selected Services List (Toggle Show/Hide) -->
                    <div class="selected-services-list" id="selectedServicesList" style="position: absolute; bottom: 100%; left: 0; right: 0; background: #fff; border-top: 1px solid #e0e0e0; border-bottom: 1px solid #e0e0e0; max-height: 400px; overflow-y: auto; display: none; z-index: 3; box-shadow: 0 -4px 12px rgba(0,0,0,0.1);">
                        <div class="selected-services-header" style="padding: 12px 20px; border-bottom: 1px solid #e0e0e0; display: flex; align-items: center; justify-content: space-between; background: #f8f9fa; position: sticky; top: 0; z-index: 1; cursor: pointer;">
                            <span style="font-size: 14px; font-weight: 600; color: #333;">Ẩn dịch vụ đã chọn</span>
                            <i class="fa fa-chevron-down" style="color: #666; transition: transform 0.3s;" id="toggleServicesList"></i>
                        </div>
                        <div class="selected-services-body" id="selectedServicesBody" style="padding: 8px 0;">
                            <!-- Services will be dynamically added here -->
                        </div>
                    </div>
                    
                    <!-- Offers Section (Hide/Show on Scroll - Behind Summary Bar) -->
                    <div class="offers-section" id="offersSection" style="position: absolute; bottom: 100%; left: 0; right: 0; background: #fff; border-top: 1px solid #e0e0e0; border-bottom: 1px solid #e0e0e0; padding: 12px 20px; border-radius: 15px 15px 0 0; transition: transform 0.3s ease-in-out; z-index: 1;">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; background: #ffc107; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <span style="color: #fff; font-size: 20px; font-weight: 700;">%</span>
                                </div>
                                <div>
                                    <div style="font-size: 14px; font-weight: 600; color: #0066cc; margin-bottom: 2px;">Ưu đãi của anh</div>
                                    <div style="font-size: 11px; color: #999;">Nhân viên sẽ giúp anh chọn dịch vụ tại cửa hàng</div>
                                </div>
                            </div>
                            <a href="#" style="display: flex; align-items: center; color: #0066cc; text-decoration: none; font-size: 14px; font-weight: 600; gap: 4px;">
                                Chọn ưu đãi
                                <i class="fa fa-chevron-right" style="font-size: 12px;"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .select-services-container {
        animation: fadeIn 0.5s ease-in;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Card Hover Effects */
    .svc-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.12) !important;
        border-color: #000 !important;
    }
    
    .svc-img:hover img {
        transform: scale(1.05);
    }
    
    .btn-primary:hover {
        background: #FFC107 !important;
        color: #000 !important;
        border-color: #FFC107 !important;
    }
    
    /* Back Arrow Button Styles */
    .back-arrow-btn:hover {
        background: #f0f0f0 !important;
        color: #000 !important;
        transform: translateX(-2px);
    }
    
    /* Filter Tag Active State */
    .filter-tag-btn:hover {
        background: #000 !important;
        border-color: #000 !important;
        color: #fff !important;
    }
    
    .filter-tag-btn.active {
        background: #000 !important;
        border-color: #000 !important;
        color: #fff !important;
    }
    
    /* Variants Tooltip Styles */
    .svc-actions {
        position: relative;
    }
    
    .select-service-btn:hover + .variants-tooltip,
    .select-service-btn:hover ~ .variants-tooltip,
    .svc-actions:hover .variants-tooltip,
    .hover-bridge:hover + .variants-tooltip,
    .variants-tooltip:hover {
        display: block !important;
        animation: slideRightFadeIn 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    @keyframes slideRightFadeIn {
        from {
            opacity: 0;
            transform: translateY(-50%) translateX(-10px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(-50%) translateX(0) scale(1);
        }
    }
    
    .hover-bridge {
        pointer-events: auto;
        background: transparent;
    }
    
    .svc-actions:hover .hover-bridge,
    .svc-actions:hover .variants-tooltip {
        display: block !important;
    }
    
    .variants-tooltip {
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    
    .variant-item {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .variant-item:hover {
        background: #e3f2fd !important;
        border-color: #007bff !important;
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2) !important;
    }
    
    .variant-item-link {
        text-decoration: none !important;
        display: block;
    }
    
    .variant-item-link:hover .variant-item {
        background: #e3f2fd !important;
        border-color: #007bff !important;
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2) !important;
    }
    
    .variant-item:last-child {
        margin-bottom: 0 !important;
    }
    
    /* Scrollbar styling for tooltip */
    .variants-tooltip .tooltip-body::-webkit-scrollbar {
        width: 6px;
    }
    
    .variants-tooltip .tooltip-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
        margin: 4px 0;
    }
    
    .variants-tooltip .tooltip-body::-webkit-scrollbar-thumb {
        background: #007bff;
        border-radius: 10px;
    }
    
    .variants-tooltip .tooltip-body::-webkit-scrollbar-thumb:hover {
        background: #0056b3;
    }
    
    /* Summary Container Styles */
    .summary-container {
        will-change: transform;
    }
    
    /* Offers Section Styles */
    .offers-section {
        will-change: transform;
        transform: translateY(100%);
    }
    
    .offers-section.visible {
        transform: translateY(0) !important;
    }
    
    .offers-section.hidden {
        transform: translateY(100%) !important;
    }
    
    /* Ensure summary container has relative positioning for absolute children */
    .summary-container {
        position: relative;
    }
    
    /* Summary Bar Styles */
    .summary-bar {
        /* Always visible, no transform needed */
    }
    
    /* Ensure summary container matches service cards width */
    .summary-container {
        max-width: 100%;
    }
    
    .select-services-container {
        position: relative;
    }
    
    /* Responsive for offers section */
    @media (max-width: 768px) {
        .offers-section {
            padding: 10px 15px !important;
        }
        
        .offers-section > div > div:first-child {
            gap: 8px !important;
        }
        
        .offers-section > div > div:first-child > div:first-child {
            width: 32px !important;
            height: 32px !important;
        }
        
        .offers-section > div > div:first-child > div:first-child > span {
            font-size: 16px !important;
        }
        
        .summary-bar {
            padding: 10px 15px !important;
        }
    }
    
    .btn-done {
        transition: all 0.3s ease;
    }
    
    .btn-done:hover {
        background: #d0d0d0 !important;
        color: #333 !important;
    }
    
    .btn-done.active {
        background: #007bff !important;
        color: #fff !important;
    }
    
    .btn-done.active:hover {
        background: #0056b3 !important;
    }
    
    #selectedServicesLink:hover {
        color: #000 !important;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .service-grid {
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)) !important;
            gap: 12px !important;
        }
        
        .variants-tooltip {
            min-width: 280px !important;
            max-width: 90vw !important;
        }
        
        .summary-bar {
            padding: 10px 15px !important;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .summary-left {
            width: 100%;
            margin-bottom: 8px;
        }
        
        .summary-right {
            width: 100%;
            justify-content: space-between;
        }
        
        .total-price-section {
            flex: 1;
        }
        
        .btn-done {
            padding: 10px 20px !important;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter tags functionality
    const filterTags = document.querySelectorAll('.filter-tag-btn');
    const serviceCards = document.querySelectorAll('.svc-card');
    
    filterTags.forEach(function(tagBtn) {
        tagBtn.addEventListener('click', function() {
            const tag = this.getAttribute('data-tag');
            
            // Toggle active state
            this.classList.toggle('active');
            
            // Filter cards
            serviceCards.forEach(function(card) {
                const serviceName = card.getAttribute('data-service-name') || '';
                const cardCategory = card.closest('.category-section');
                
                if (serviceName.includes(tag) || tag === 'all') {
                    card.style.display = 'flex';
                    if (cardCategory) {
                        cardCategory.style.display = 'block';
                    }
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Hide empty categories
            document.querySelectorAll('.category-section').forEach(function(section) {
                const visibleCards = section.querySelectorAll('.svc-card[style*="display: flex"], .svc-card:not([style*="display: none"])');
                if (visibleCards.length === 0) {
                    section.style.display = 'none';
                } else {
                    section.style.display = 'block';
                }
            });
        });
    });
    
    // Handle tooltip positioning when hovering over select button
    const selectButtons = document.querySelectorAll('.select-service-btn');
    
    selectButtons.forEach(function(button) {
        const hasVariants = button.getAttribute('data-has-variants') === 'true';
        if (!hasVariants) return;
        
        const svcActions = button.closest('.svc-actions');
        const tooltip = svcActions.querySelector('.variants-tooltip');
        if (!tooltip) return;
        
        let tooltipTimeout;
        
        // Show tooltip on button hover
        button.addEventListener('mouseenter', function() {
            clearTimeout(tooltipTimeout);
            tooltip.style.display = 'block';
            
            // Force reflow to get accurate dimensions
            void tooltip.offsetWidth;
            
            const buttonRect = button.getBoundingClientRect();
            const tooltipRect = tooltip.getBoundingClientRect();
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            
            // Default: position to the right
            tooltip.style.left = 'calc(100% + 8px)';
            tooltip.style.right = 'auto';
            tooltip.style.top = '50%';
            tooltip.style.bottom = 'auto';
            tooltip.style.transform = 'translateY(-50%)';
            
            // Calculate tooltip dimensions
            const tooltipWidth = tooltipRect.width || 320;
            const tooltipHeight = tooltipRect.height || 200;
            const buttonCenterY = buttonRect.top + buttonRect.height / 2;
            
            // Check if tooltip goes off right edge (show on left instead)
            if (buttonRect.right + tooltipWidth + 8 > viewportWidth - 10) {
                tooltip.style.left = 'auto';
                tooltip.style.right = 'calc(100% + 8px)';
                // Update arrow position to point right
                const arrows = tooltip.querySelectorAll('div[style*="border"]');
                if (arrows.length >= 2) {
                    // Outer arrow
                    arrows[0].style.left = 'auto';
                    arrows[0].style.right = '-10px';
                    arrows[0].style.top = '50%';
                    arrows[0].style.transform = 'translateY(-50%)';
                                                    arrows[0].style.borderRight = 'none';
                    arrows[0].style.borderLeft = '10px solid #000';
                    arrows[0].style.borderTop = '10px solid transparent';
                    arrows[0].style.borderBottom = '10px solid transparent';
                    // Inner arrow
                    arrows[1].style.left = 'auto';
                    arrows[1].style.right = '-8px';
                    arrows[1].style.top = '50%';
                    arrows[1].style.transform = 'translateY(-50%)';
                    arrows[1].style.borderRight = 'none';
                    arrows[1].style.borderLeft = '10px solid #fff';
                    arrows[1].style.borderTop = '10px solid transparent';
                    arrows[1].style.borderBottom = '10px solid transparent';
                }
                // Update hover bridge
                const hoverBridge = tooltip.querySelector('.hover-bridge');
                if (hoverBridge) {
                    hoverBridge.style.left = 'auto';
                    hoverBridge.style.right = '-8px';
                }
            } else {
                // Reset arrow to point left (default)
                const arrows = tooltip.querySelectorAll('div[style*="border"]');
                if (arrows.length >= 2) {
                    // Outer arrow
                    arrows[0].style.left = '-10px';
                    arrows[0].style.right = 'auto';
                    arrows[0].style.top = '50%';
                    arrows[0].style.transform = 'translateY(-50%)';
                    arrows[0].style.borderLeft = 'none';
                    arrows[0].style.borderRight = '10px solid #000';
                    arrows[0].style.borderTop = '10px solid transparent';
                    arrows[0].style.borderBottom = '10px solid transparent';
                    // Inner arrow
                    arrows[1].style.left = '-8px';
                    arrows[1].style.right = 'auto';
                    arrows[1].style.top = '50%';
                    arrows[1].style.transform = 'translateY(-50%)';
                    arrows[1].style.borderLeft = 'none';
                    arrows[1].style.borderRight = '10px solid #fff';
                    arrows[1].style.borderTop = '10px solid transparent';
                    arrows[1].style.borderBottom = '10px solid transparent';
                }
                // Reset hover bridge
                const hoverBridge = tooltip.querySelector('.hover-bridge');
                if (hoverBridge) {
                    hoverBridge.style.left = '-8px';
                    hoverBridge.style.right = 'auto';
                }
            }
            
            // Adjust vertical position if tooltip goes off screen
            const tooltipTop = buttonCenterY - tooltipHeight / 2;
            const tooltipBottom = buttonCenterY + tooltipHeight / 2;
            
            if (tooltipTop < 10) {
                const offset = 10 - tooltipTop;
                tooltip.style.transform = `translateY(calc(-50% + ${offset}px))`;
            } else if (tooltipBottom > viewportHeight - 10) {
                const offset = viewportHeight - 10 - tooltipBottom;
                tooltip.style.transform = `translateY(calc(-50% + ${offset}px))`;
            }
        });
        
        // Hide tooltip when mouse leaves button
        button.addEventListener('mouseleave', function() {
            tooltipTimeout = setTimeout(function() {
                tooltip.style.display = 'none';
            }, 200);
        });
        
        // Keep tooltip visible when hovering over tooltip or bridge
        tooltip.addEventListener('mouseenter', function() {
            clearTimeout(tooltipTimeout);
        });
        
        tooltip.addEventListener('mouseleave', function() {
            tooltipTimeout = setTimeout(function() {
                tooltip.style.display = 'none';
            }, 200);
        });
        
        const hoverBridge = tooltip.querySelector('.hover-bridge');
        if (hoverBridge) {
            hoverBridge.addEventListener('mouseenter', function() {
                clearTimeout(tooltipTimeout);
                tooltip.style.display = 'block';
            });
        }
        
        // Keep tooltip visible when hovering over it
        tooltip.addEventListener('mouseenter', function() {
            tooltip.style.display = 'block';
        });
        
        // Hide tooltip when leaving button or tooltip (with delay)
        let hideTimeout;
        svcActions.addEventListener('mouseleave', function() {
            hideTimeout = setTimeout(function() {
                tooltip.style.display = 'none';
            }, 200);
        });
        
        // Cancel hide if mouse enters again
        svcActions.addEventListener('mouseenter', function() {
            if (hideTimeout) {
                clearTimeout(hideTimeout);
                hideTimeout = null;
            }
        });
    });
    
    // Store selected services in sessionStorage
    let selectedServices = {
        serviceIds: [],
        variantIds: [],
        comboIds: [],
        prices: {},
        names: {} // Store service names for display
    };
    
    // Load from sessionStorage or URL
    function loadSelectedServices() {
        const stored = sessionStorage.getItem('selectedServices');
        if (stored) {
            try {
                const parsed = JSON.parse(stored);
                selectedServices = {
                    serviceIds: parsed.serviceIds || [],
                    variantIds: parsed.variantIds || [],
                    comboIds: parsed.comboIds || [],
                    prices: parsed.prices || {},
                    names: parsed.names || {}
                };
            } catch (e) {
                // If parsing fails, reset
                selectedServices = {
                    serviceIds: [],
                    variantIds: [],
                    comboIds: [],
                    prices: {},
                    names: {}
                };
            }
        } else {
            // Load from URL params
            const urlParams = new URLSearchParams(window.location.search);
            selectedServices.serviceIds = urlParams.getAll('service_id[]').filter(id => id && id !== '0');
            
            // Get variants
            urlParams.getAll('service_variants[]').forEach(id => {
                if (id && id !== '0' && !selectedServices.variantIds.includes(id)) {
                    selectedServices.variantIds.push(id);
                }
            });
            for (let i = 0; i < 100; i++) {
                const param = urlParams.get(`service_variants[${i}]`);
                if (param && param !== '0' && !selectedServices.variantIds.includes(param)) {
                    selectedServices.variantIds.push(param);
                } else if (i > 10) break;
            }
            
            selectedServices.comboIds = urlParams.getAll('combo_id[]').filter(id => id && id !== '0');
            
            // Try to get names from DOM elements if available
            selectedServices.serviceIds.forEach(id => {
                const btn = document.querySelector(`[data-service-id="${id}"]`);
                if (btn && !selectedServices.names['service_' + id]) {
                    selectedServices.names['service_' + id] = btn.getAttribute('data-service-name') || 'Dịch vụ #' + id;
                    selectedServices.prices['service_' + id] = parseFloat(btn.getAttribute('data-service-price') || 0);
                }
            });
            
            selectedServices.comboIds.forEach(id => {
                const btn = document.querySelector(`[data-combo-id="${id}"]`);
                if (btn && !selectedServices.names['combo_' + id]) {
                    selectedServices.names['combo_' + id] = btn.getAttribute('data-combo-name') || 'Combo #' + id;
                    selectedServices.prices['combo_' + id] = parseFloat(btn.getAttribute('data-combo-price') || 0);
                }
            });
        }
        saveSelectedServices();
    }
    
    function saveSelectedServices() {
        sessionStorage.setItem('selectedServices', JSON.stringify(selectedServices));
    }
    
    // Update summary bar
    function updateSummaryBar() {
        const totalCount = selectedServices.serviceIds.length + selectedServices.variantIds.length + selectedServices.comboIds.length;
        let totalPrice = 0;
        
        // Calculate total price from stored prices
        Object.values(selectedServices.prices).forEach(price => {
            totalPrice += parseFloat(price) || 0;
        });
        
        const selectedCountEl = document.getElementById('selectedCount');
        const totalPriceEl = document.getElementById('totalPrice');
        const doneButton = document.getElementById('doneButton');
        
        if (selectedCountEl) {
            selectedCountEl.textContent = totalCount;
        }
        
        if (totalPriceEl) {
            totalPriceEl.textContent = formatPrice(totalPrice) + ' VNĐ';
        }
        
        // Update button style
        if (doneButton) {
            if (totalCount > 0) {
                doneButton.classList.add('active');
                doneButton.style.background = '#007bff';
                doneButton.style.color = '#fff';
            } else {
                doneButton.classList.remove('active');
                doneButton.style.background = '#e0e0e0';
                doneButton.style.color = '#666';
            }
        }
    }
    
    function formatPrice(price) {
        return Math.round(price).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
    
    // Handle service selection
    function handleServiceSelection(serviceId, price, type, name = '') {
        const id = serviceId.toString();
        
        if (type === 'service') {
            if (selectedServices.serviceIds.includes(id)) {
                // Remove if already selected
                selectedServices.serviceIds = selectedServices.serviceIds.filter(sid => sid !== id);
                delete selectedServices.prices['service_' + id];
                delete selectedServices.names['service_' + id];
            } else {
                selectedServices.serviceIds.push(id);
                selectedServices.prices['service_' + id] = price;
                selectedServices.names['service_' + id] = name || 'Dịch vụ #' + id;
            }
        } else if (type === 'variant') {
            if (selectedServices.variantIds.includes(id)) {
                selectedServices.variantIds = selectedServices.variantIds.filter(vid => vid !== id);
                delete selectedServices.prices['variant_' + id];
                delete selectedServices.names['variant_' + id];
            } else {
                selectedServices.variantIds.push(id);
                selectedServices.prices['variant_' + id] = price;
                selectedServices.names['variant_' + id] = name || 'Biến thể #' + id;
            }
        } else if (type === 'combo') {
            if (selectedServices.comboIds.includes(id)) {
                selectedServices.comboIds = selectedServices.comboIds.filter(cid => cid !== id);
                delete selectedServices.prices['combo_' + id];
                delete selectedServices.names['combo_' + id];
            } else {
                selectedServices.comboIds.push(id);
                selectedServices.prices['combo_' + id] = price;
                selectedServices.names['combo_' + id] = name || 'Combo #' + id;
            }
        }
        
        // Remove duplicates
        selectedServices.serviceIds = [...new Set(selectedServices.serviceIds)];
        selectedServices.variantIds = [...new Set(selectedServices.variantIds)];
        selectedServices.comboIds = [...new Set(selectedServices.comboIds)];
        
        saveSelectedServices();
        updateSummaryBar();
        updateSelectedServicesList();
    }
    
    // Remove service from selection
    function removeService(id, type) {
        if (type === 'service') {
            selectedServices.serviceIds = selectedServices.serviceIds.filter(sid => sid !== id);
            delete selectedServices.prices['service_' + id];
            delete selectedServices.names['service_' + id];
        } else if (type === 'variant') {
            selectedServices.variantIds = selectedServices.variantIds.filter(vid => vid !== id);
            delete selectedServices.prices['variant_' + id];
            delete selectedServices.names['variant_' + id];
        } else if (type === 'combo') {
            selectedServices.comboIds = selectedServices.comboIds.filter(cid => cid !== id);
            delete selectedServices.prices['combo_' + id];
            delete selectedServices.names['combo_' + id];
        }
        
        saveSelectedServices();
        updateSummaryBar();
        updateSelectedServicesList();
    }
    
    // Update selected services list display
    function updateSelectedServicesList() {
        const listBody = document.getElementById('selectedServicesBody');
        if (!listBody) return;
        
        // Clear existing items
        listBody.innerHTML = '';
        
        // Get all selected items
        const allItems = [];
        
        // Add services
        selectedServices.serviceIds.forEach(id => {
            allItems.push({
                id: id,
                type: 'service',
                name: selectedServices.names['service_' + id] || 'Dịch vụ #' + id,
                price: selectedServices.prices['service_' + id] || 0
            });
        });
        
        // Add variants
        selectedServices.variantIds.forEach(id => {
            allItems.push({
                id: id,
                type: 'variant',
                name: selectedServices.names['variant_' + id] || 'Biến thể #' + id,
                price: selectedServices.prices['variant_' + id] || 0
            });
        });
        
        // Add combos
        selectedServices.comboIds.forEach(id => {
            allItems.push({
                id: id,
                type: 'combo',
                name: selectedServices.names['combo_' + id] || 'Combo #' + id,
                price: selectedServices.prices['combo_' + id] || 0
            });
        });
        
        if (allItems.length === 0) {
            listBody.innerHTML = '<div style="padding: 20px; text-align: center; color: #999; font-size: 14px;">Chưa có dịch vụ nào được chọn</div>';
            return;
        }
        
        // Render items
        allItems.forEach(item => {
            const itemDiv = document.createElement('div');
            itemDiv.style.cssText = 'padding: 12px 20px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; justify-content: space-between; transition: background 0.2s;';
            itemDiv.onmouseenter = function() { this.style.background = '#f8f9fa'; };
            itemDiv.onmouseleave = function() { this.style.background = '#fff'; };
            
            const nameSpan = document.createElement('span');
            nameSpan.style.cssText = 'font-size: 14px; color: #0066cc; flex: 1;';
            nameSpan.textContent = item.name;
            
            const priceSpan = document.createElement('span');
            priceSpan.style.cssText = 'font-size: 14px; color: #333; font-weight: 600; margin-right: 12px;';
            const priceInK = Math.round(item.price / 1000);
            priceSpan.textContent = priceInK + 'K';
            
            const removeBtn = document.createElement('button');
            removeBtn.style.cssText = 'background: none; border: none; color: #0066cc; cursor: pointer; padding: 4px 8px; font-size: 16px; line-height: 1;';
            removeBtn.innerHTML = '<i class="fa fa-times"></i>';
            removeBtn.onclick = function(e) {
                e.preventDefault();
                removeService(item.id, item.type);
            };
            
            itemDiv.appendChild(nameSpan);
            itemDiv.appendChild(priceSpan);
            itemDiv.appendChild(removeBtn);
            listBody.appendChild(itemDiv);
        });
    }
    
    // Handle done button click
    function handleDoneClick() {
        const params = new URLSearchParams();
        
        // Add service IDs
        selectedServices.serviceIds.forEach(id => {
            params.append('service_id[]', id);
        });
        
        // Add variant IDs
        selectedServices.variantIds.forEach(id => {
            params.append('service_variants[]', id);
        });
        
        // Add combo IDs
        selectedServices.comboIds.forEach(id => {
            params.append('combo_id[]', id);
        });
        
        // Keep search parameter if exists
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('search')) {
            params.append('search', urlParams.get('search'));
        }
        
        const queryString = params.toString();
        const createUrl = '{{ route("site.appointment.create") }}' + (queryString ? '?' + queryString : '');
        window.location.href = createUrl;
    }
    
    // Toggle selected services list
    const selectedServicesLink = document.getElementById('selectedServicesLink');
    const selectedServicesList = document.getElementById('selectedServicesList');
    const toggleServicesListBtn = document.getElementById('toggleServicesList');
    
    function toggleSelectedServicesList() {
        if (selectedServicesList) {
            const isVisible = selectedServicesList.style.display === 'block';
            selectedServicesList.style.display = isVisible ? 'none' : 'block';
            if (toggleServicesListBtn) {
                toggleServicesListBtn.style.transform = isVisible ? 'rotate(0deg)' : 'rotate(180deg)';
            }
            // Update list content when opening
            if (!isVisible) {
                updateSelectedServicesList();
            }
        }
    }
    
    if (selectedServicesLink) {
        selectedServicesLink.addEventListener('click', function(e) {
            e.preventDefault();
            toggleSelectedServicesList();
        });
    }
    
    if (toggleServicesListBtn) {
        toggleServicesListBtn.addEventListener('click', function(e) {
            e.preventDefault();
            toggleSelectedServicesList();
        });
    }
    
    // Close list when clicking outside
    document.addEventListener('click', function(e) {
        if (selectedServicesList && selectedServicesLink && 
            !selectedServicesList.contains(e.target) && 
            !selectedServicesLink.contains(e.target)) {
            selectedServicesList.style.display = 'none';
            if (toggleServicesListBtn) {
                toggleServicesListBtn.style.transform = 'rotate(0deg)';
            }
        }
    });
    
    // Initialize
    loadSelectedServices();
    updateSummaryBar();
    updateSelectedServicesList();
    
    // Handle service button clicks
    document.querySelectorAll('.select-service-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const serviceId = this.getAttribute('data-service-id');
            const variantId = this.getAttribute('data-variant-id');
            const comboId = this.getAttribute('data-combo-id');
            const servicePrice = parseFloat(this.getAttribute('data-service-price') || 0);
            const variantPrice = parseFloat(this.getAttribute('data-variant-price') || 0);
            const comboPrice = parseFloat(this.getAttribute('data-combo-price') || 0);
            const serviceName = this.getAttribute('data-service-name') || '';
            const comboName = this.getAttribute('data-combo-name') || '';
            
            if (variantId) {
                handleServiceSelection(variantId, variantPrice, 'variant');
            } else if (comboId) {
                handleServiceSelection(comboId, comboPrice, 'combo', comboName);
            } else if (serviceId) {
                handleServiceSelection(serviceId, servicePrice, 'service', serviceName);
            }
        });
    });
    
    // Handle variant item clicks
    document.querySelectorAll('.variant-item-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const variantId = this.getAttribute('data-variant-id');
            const variantPrice = parseFloat(this.getAttribute('data-variant-price') || 0);
            if (variantId) {
                const variantName = this.getAttribute('data-variant-name') || 'Biến thể #' + variantId;
                handleServiceSelection(variantId, variantPrice, 'variant', variantName);
                // Close tooltip after selection
                const tooltip = this.closest('.svc-actions')?.querySelector('.variants-tooltip');
                if (tooltip) {
                    tooltip.style.display = 'none';
                }
            }
        });
    });
    
    // Handle done button
    const doneButton = document.getElementById('doneButton');
    if (doneButton) {
        doneButton.addEventListener('click', handleDoneClick);
    }
    
    // Hide/Show offers section on scroll (summary bar stays fixed)
    let lastScrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const offersSection = document.getElementById('offersSection');
    
    function handleScroll() {
        const currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Only trigger if scrolled enough (avoid flickering)
        if (Math.abs(lastScrollTop - currentScrollTop) < 5) {
            return;
        }
        
        if (offersSection) {
            if (currentScrollTop > lastScrollTop && currentScrollTop > 100) {
                // Scrolling down - hide offers section
                offersSection.classList.remove('visible');
                offersSection.classList.add('hidden');
            } else if (currentScrollTop < lastScrollTop || currentScrollTop <= 100) {
                // Scrolling up or at top - show offers section
                offersSection.classList.remove('hidden');
                offersSection.classList.add('visible');
            }
        }
        
        lastScrollTop = currentScrollTop <= 0 ? 0 : currentScrollTop;
    }
    
    // Throttle scroll events for performance
    let scrollThrottle = false;
    window.addEventListener('scroll', function() {
        if (!scrollThrottle) {
            window.requestAnimationFrame(function() {
                handleScroll();
                scrollThrottle = false;
            });
            scrollThrottle = true;
        }
    }, { passive: true });
    
    // Initialize offers section as visible on page load
    if (offersSection) {
        // Remove any inline transform and add visible class
        offersSection.style.transform = '';
        offersSection.classList.remove('hidden');
        offersSection.classList.add('visible');
        
        // Also check scroll position on load
        const currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;
        if (currentScrollTop <= 100) {
            offersSection.classList.remove('hidden');
            offersSection.classList.add('visible');
        } else {
            offersSection.classList.remove('visible');
            offersSection.classList.add('hidden');
        }
    }
    
    // Adjust summary container position to sit above footer
    function adjustSummaryPosition() {
        const footer = document.querySelector('.footer');
        const summaryWrapper = document.querySelector('.summary-wrapper');
        
        if (!footer || !summaryWrapper) return;
        
        const footerTop = footer.getBoundingClientRect().top;
        const windowHeight = window.innerHeight;
        const summaryBar = document.getElementById('summaryBar');
        const summaryHeight = summaryBar ? summaryBar.offsetHeight : 70;
        
        // Calculate distance from bottom of viewport to footer
        const distanceToFooter = footerTop - windowHeight;
        
        // If footer is above or at the bottom of viewport (footer visible)
        if (distanceToFooter <= 0) {
            // Position summary above footer (negative distance means footer is above viewport bottom)
            summaryWrapper.style.bottom = Math.abs(distanceToFooter) + 'px';
        } else {
            // Footer is below viewport, position summary at bottom
            summaryWrapper.style.bottom = '0px';
        }
    }
    
    // Adjust on scroll and resize
    let adjustThrottle = false;
    function handleAdjustPosition() {
        if (!adjustThrottle) {
            window.requestAnimationFrame(function() {
                adjustSummaryPosition();
                adjustThrottle = false;
            });
            adjustThrottle = true;
        }
    }
    
    window.addEventListener('scroll', handleAdjustPosition, { passive: true });
    window.addEventListener('resize', handleAdjustPosition);
    
    // Initial adjustment
    setTimeout(adjustSummaryPosition, 100);
});
</script>
@endsection
