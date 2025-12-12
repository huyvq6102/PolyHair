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

    // Tính discount từ promotion
    $discountAmount = 0;
    $finalPrice = $totalPrice;
    if (isset($selectedPromotion) && $selectedPromotion) {
        if ($selectedPromotion->discount_type === 'percent') {
            $discountPercent = $selectedPromotion->discount_percent ?? 0;
            $discountAmount = ($totalPrice * $discountPercent) / 100;
            // Apply max discount if exists
            if ($selectedPromotion->max_discount_amount) {
                $discountAmount = min($discountAmount, $selectedPromotion->max_discount_amount);
            }
        } else {
            $discountAmount = $selectedPromotion->discount_amount ?? 0;
        }
        $finalPrice = max(0, $totalPrice - $discountAmount);
    }

    $formattedTotalPrice = number_format($totalPrice, 0, ',', '.');
    $formattedDiscountAmount = number_format($discountAmount, 0, ',', '.');
    $formattedFinalPrice = number_format($finalPrice, 0, ',', '.');
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
                                <div class="service-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
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
                                             style="border: 1px solid #e0e0e0; box-shadow: 0 2px 8px rgba(0,0,0,0.08); background: #fff; display: flex; flex-direction: column; border-radius: 8px; overflow: visible; position: relative; transition: all 0.3s ease;">

                                            <!-- Card Image -->
                                            <div class="svc-img" style="overflow: hidden; display: block; height: 180px; background: #f5f5f5; position: relative;">
                                                <!-- Duration Badge - Only show if no variants -->
                                                @if(!$hasVariants)
                                                    <div class="duration-badge" style="position: absolute; top: 8px; left: 8px; background: #0066cc; color: #fff; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; z-index: 10; box-shadow: 0 2px 6px rgba(0,0,0,0.2);">
                                                        {{ $serviceDuration }}p
                                                    </div>
                                                @endif
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
                                                <div class="svc-info" style="flex-grow: 1; margin-bottom: 8px;">
                                                    <h4 class="svc-name" style="margin: 0 0 6px 0; font-weight: 600; font-size: 14px; line-height: 1.3; color: #000; text-align: left;">
                                                        <a href="{{ route('site.services.show', $service->id) }}" style="color: inherit; text-decoration: none;">{{ $service->name }}</a>
                                                    </h4>
                                                    @if(!$hasVariants)
                                                        <div class="svc-price" style="font-size: 15px; color: #333; font-weight: 600; text-align: left; margin-bottom: 0;">
                                                            <span style="color: #BC9321; font-weight: 700;">{{ $formattedPrice }} VND</span>
                                                        </div>
                                                    @else
                                                        <!-- Button to show variants -->
                                                        <button type="button"
                                                                class="select-variant-btn"
                                                                data-service-id="{{ $service->id }}"
                                                                style="width: 100%; padding: 10px 16px; margin-bottom: 12px; background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 13px; font-weight: 600; color: #666; cursor: pointer; transition: all 0.3s ease; text-align: left; display: flex; align-items: center; justify-content: space-between;">
                                                            <span>Chọn gói dịch vụ</span>
                                                            <i class="fa fa-chevron-down" style="font-size: 11px; transition: transform 0.3s ease;"></i>
                                                        </button>

                                                        <!-- Display Variants (hidden by default, hiển thị như popup bên ngoài) -->
                                                        <div class="variants-list variants-list-hidden variants-popup"
                                                             data-service-id="{{ $service->id }}"
                                                             style="display: none; position: fixed; z-index: 999999; flex-direction: column; gap: 8px; background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); padding: 8px; max-height: 500px; overflow-y: auto; overflow-x: hidden; width: max-content; max-width: calc(100vw - 40px);">
                                                            @foreach($service->serviceVariants as $variant)
                                                                @php
                                                                    $variantPrice = number_format($variant->price ?? 0, 0, ',', '.');
                                                                    // Load variant attributes if not already loaded
                                                                    $attributes = $variant->variantAttributes ?? collect();
                                                                @endphp
                                                                    <div class="variant-item-box variant-item-link"
                                                                         data-variant-id="{{ $variant->id }}"
                                                                         data-service-id="{{ $service->id }}"
                                                                         data-variant-price="{{ $variant->price ?? 0 }}"
                                                                         data-variant-name="{{ $service->name }} - {{ $variant->name }}"
                                                                         style="background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 8px; padding: 10px 16px; cursor: pointer; transition: all 0.3s ease; display: flex; flex-direction: column; gap: 6px; width: max-content; min-width: max-content; box-sizing: border-box; margin: 0; flex-shrink: 0; word-wrap: break-word; overflow-wrap: break-word;">
                                                                    <!-- Dòng 1: Tên biến thể + Tag thuộc tính -->
                                                                    <div style="display: flex; flex-direction: row; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                                        <!-- Variant Name -->
                                                                        <span style="font-size: 13px; font-weight: 600; color: #000; flex-shrink: 0; word-wrap: break-word; overflow-wrap: break-word; max-width: 100%;">
                                                                            {{ $variant->name }}
                                                                        </span>

                                                                        <!-- Variant Attributes (tags) - always shown, right after name -->
                                                                        @if($attributes && $attributes->count() > 0)
                                                                            <div class="variant-attributes" style="display: flex; flex-wrap: wrap; gap: 4px; flex-shrink: 0; align-items: center;">
                                                                                @foreach($attributes as $attr)
                                                                                    <span style="background: #e3f2fd; color: #0066cc; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: 600; white-space: nowrap;">
                                                                                        {{ $attr->attribute_value }}
                                                                                    </span>
                                                                                @endforeach
                                                                            </div>
                                                                        @endif
                                                                    </div>

                                                                    <!-- Dòng 2: Thời gian + Giá -->
                                                                    <div style="display: flex; flex-direction: row; align-items: center; gap: 8px;">
                                                                        <!-- Duration -->
                                                                        <span style="font-size: 12px; color: #666; display: flex; align-items: center; gap: 4px; flex-shrink: 0;">
                                                                            <i class="fa fa-clock-o" style="font-size: 10px;"></i>
                                                                            <span>{{ $variant->duration ?? 60 }} phút</span>
                                                                        </span>

                                                                        <!-- Separator -->
                                                                        <span style="color: #ccc; font-size: 12px;">•</span>

                                                                        <!-- Price -->
                                                                        <span style="font-size: 14px; font-weight: 700; color: #BC9321; flex-shrink: 0;">
                                                                            {{ $variantPrice }} VNĐ
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- Card Actions -->
                                                <div class="svc-actions" style="margin-top: auto; position: relative; width: 100%; margin-left: 0; margin-right: 0; padding: 0;">
                                                    @if($hasVariants)
                                                        <!-- Button for variants - will be handled by variant-item-link clicks -->
                                                        <button type="button"
                                                                class="btn btn-primary w-100 select-service-btn"
                                                                style="background: #000; border: 1px solid #000; color: #fff; padding: 10px 16px; font-size: 13px; font-weight: 600; border-radius: 6px; transition: all 0.3s ease; text-align: center; cursor: pointer; width: 100%; display: block; box-sizing: border-box; margin: 0;">
                                                            Chọn
                                                        </button>
                                                    @else
                                                        <button type="button"
                                                                class="btn btn-primary w-100 select-service-btn"
                                                                data-has-variants="false"
                                                                data-service-id="{{ $service->id }}"
                                                                data-service-price="{{ $service->base_price ?? 0 }}"
                                                                data-service-name="{{ $service->name }}"
                                                                style="background: #000; border: 1px solid #000; color: #fff; padding: 10px 16px; font-size: 13px; font-weight: 600; border-radius: 6px; transition: all 0.3s ease; text-align: center; position: relative; z-index: 1; cursor: pointer; width: 100%; display: block;">
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
                                                    <div class="svc-info" style="flex-grow: 1; margin-bottom: 12px;">
                                                        <h4 class="svc-name" style="margin: 0 0 8px 0; font-weight: 600; font-size: 14px; line-height: 1.3; color: #000; min-height: 36px; text-align: left;">
                                                            <a href="#" style="color: inherit; text-decoration: none;">{{ $combo->name }}</a>
                                                            <span style="color: #BC9321; font-size: 11px; font-weight: 600; margin-left: 4px;">(COMBO)</span>
                                                        </h4>
                                                        @if($combo->comboItems && $combo->comboItems->count() > 0)
                                                            @php
                                                                $serviceNames = [];
                                                                foreach($combo->comboItems as $item) {
                                                                    $serviceName = null;
                                                                    if ($item->serviceVariant && $item->serviceVariant->service) {
                                                                        $serviceName = $item->serviceVariant->service->name;
                                                                    } elseif ($item->service) {
                                                                        $serviceName = $item->service->name;
                                                                    }
                                                                    if($serviceName) {
                                                                        $serviceNames[] = $serviceName;
                                                                    }
                                                                }
                                                            @endphp
                                                            @if(count($serviceNames) > 0)
                                                                <div class="combo-services-list" style="margin-bottom: 8px; font-size: 12px; line-height: 1.6; color: #666;">
                                                                    <i class="fa fa-check-circle" style="color: #28a745; font-size: 11px; margin-right: 6px;"></i>
                                                                    <span>{{ implode(' + ', $serviceNames) }}</span>
                                                                </div>
                                                            @endif
                                                        @endif
                                                        <div class="svc-price" style="font-size: 15px; color: #333; font-weight: 600; margin-bottom: 8px; text-align: left;">
                                                            <span style="color: #BC9321; font-weight: 700;">{{ $formattedPrice }} VND</span>
                                                        </div>
                                                    </div>
                                                    <div class="svc-actions" style="margin-top: auto; position: relative; width: 100%;">
                                                        <button type="button"
                                                                class="btn btn-primary w-100 select-service-btn"
                                                                data-has-variants="false"
                                                                data-combo-id="{{ $combo->id }}"
                                                                data-combo-price="{{ $combo->price ?? 0 }}"
                                                                data-combo-name="{{ $combo->name }}"
                                                                style="background: #000; border: 1px solid #000; color: #fff; padding: 10px 16px; font-size: 13px; font-weight: 600; border-radius: 6px; transition: all 0.3s ease; text-align: center; position: relative; z-index: 1; cursor: pointer; width: 100%; display: block;">
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
                            <div class="service-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
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
                                                <h4 class="svc-name" style="margin: 0 0 8px 0; font-weight: 600; font-size: 14px; line-height: 1.3; color: #000; min-height: 36px; text-align: left;">
                                                    <a href="#" style="color: inherit; text-decoration: none;">{{ $combo->name }}</a>
                                                    <span style="color: #BC9321; font-size: 11px; font-weight: 600; margin-left: 4px;">(COMBO)</span>
                                                </h4>
                                                @if($combo->comboItems && $combo->comboItems->count() > 0)
                                                    @php
                                                        $serviceNames = [];
                                                        foreach($combo->comboItems as $item) {
                                                            $serviceName = null;
                                                            if ($item->serviceVariant && $item->serviceVariant->service) {
                                                                $serviceName = $item->serviceVariant->service->name;
                                                            } elseif ($item->service) {
                                                                $serviceName = $item->service->name;
                                                            }
                                                            if($serviceName) {
                                                                $serviceNames[] = $serviceName;
                                                            }
                                                        }
                                                    @endphp
                                                    @if(count($serviceNames) > 0)
                                                        <div class="combo-services-list" style="margin-bottom: 8px; font-size: 12px; line-height: 1.6; color: #666;">
                                                            <i class="fa fa-check-circle" style="color: #28a745; font-size: 11px; margin-right: 6px;"></i>
                                                            <span>{{ implode(' + ', $serviceNames) }}</span>
                                                        </div>
                                                    @endif
                                                @endif
                                                <div class="svc-price" style="font-size: 13px; color: #333; font-weight: 600; margin-bottom: 4px; text-align: left;">
                                                    <span style="color: #BC9321; font-weight: 700;">{{ $formattedPrice }} VND</span>
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
                            <div class="total-price-section" style="display: flex; flex-direction: column; align-items: flex-end;">
                                @if(isset($selectedPromotion) && $selectedPromotion && $discountAmount > 0)
                                    <div class="original-price-strike" style="font-size: 11px; color: #999; text-decoration: line-through; line-height: 1.3; margin-bottom: 1px;">
                                        {{ $formattedTotalPrice }} VNĐ
                                    </div>
                                    <div class="discount-amount" style="font-size: 11px; color: #28a745; line-height: 1.3; margin-bottom: 3px;">
                                        Giảm: {{ $formattedDiscountAmount }} VNĐ
                                    </div>
                                @endif
                                <div class="total-label" style="font-size: 11px; color: #666; line-height: 1.3; margin-bottom: 1px;">Tổng thanh toán</div>
                                <div id="totalPrice" style="font-size: 20px; font-weight: 700; color: #000; line-height: 1.2;">
                                    {{ $formattedFinalPrice }} VNĐ
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

                    <!-- Offers Section (Always visible when services are selected) -->
                    <div class="offers-section" id="offersSection" style="position: absolute; bottom: 100%; left: 0; right: 0; background: #fff; border-top: 1px solid #e0e0e0; border-bottom: 1px solid #e0e0e0; padding: 12px 20px; border-radius: 15px 15px 0 0; transition: transform 0.3s ease-in-out; z-index: 1; display: none;">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; background: #ffc107; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <span style="color: #fff; font-size: 20px; font-weight: 700;">%</span>
                                </div>
                                <div>
                                    <div style="font-size: 14px; font-weight: 400; color: #0066cc; margin-bottom: 2px;">
                                        @if(isset($selectedPromotion) && $selectedPromotion)
                                            {{ $selectedPromotion->name ?? 'Ưu đãi' }}
                                        @else
                                            Ưu đãi của anh
                                        @endif
                                    </div>
                                    @if(isset($selectedPromotion) && $selectedPromotion)
                                        <div style="font-size: 12px; color: #666;">
                                            @if($selectedPromotion->discount_type === 'percent')
                                                Giảm {{ $selectedPromotion->discount_percent ?? 0 }}%
                                            @else
                                                Giảm {{ number_format($selectedPromotion->discount_amount ?? 0, 0, ',', '.') }} VNĐ
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <a href="#"
                               id="selectOffersLink"
                               style="display: flex; align-items: center; color: #0066cc; text-decoration: none; font-size: 14px; font-weight: 400; gap: 4px;">
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

    .btn-primary:hover,
    .select-service-btn:hover {
        background: linear-gradient(135deg, #d8b26a 0%, #8b5a2b 100%) !important;
        color: #fff !important;
        border-color: #8b5a2b !important;
    }

    /* Button hover for variant services */
    .svc-actions .btn-primary:hover,
    .svc-actions .select-service-btn:hover {
        background: linear-gradient(135deg, #d8b26a 0%, #8b5a2b 100%) !important;
        color: #fff !important;
        border-color: #8b5a2b !important;
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

    .variant-item-box {
        width: 100% !important;
        min-width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }

    .variant-item-box:hover {
        background: #e3f2fd !important;
        border-color: #0066cc !important;
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0, 102, 204, 0.2) !important;
    }

    /* Toggle button styles */
    .select-variant-btn {
        position: relative;
    }

    .select-variant-btn:hover {
        background: #e9ecef !important;
        border-color: #ccc !important;
    }

    .select-variant-btn.active .fa-chevron-down {
        transform: rotate(180deg);
    }

    .variants-list-hidden {
        display: none !important;
    }

    .variants-list-visible {
        display: flex !important;
    }

    .variants-list {
        width: 100% !important;
        min-width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }

    /* Popup variants list - hiển thị bên ngoài, vừa đủ chiều ngang */
    .variants-popup {
        position: fixed !important;
        z-index: 999999 !important;
        background: #fff !important;
        border: 1px solid #e0e0e0 !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        padding: 8px !important;
        max-height: 500px !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        width: max-content !important;
        min-width: auto !important;
        max-width: calc(100vw - 40px) !important;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
    }

    .variants-popup .variant-item-box {
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        overflow-x: hidden !important;
    }

    .variants-popup .variant-item-box span {
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        max-width: 100% !important;
    }

    /* Đảm bảo card không cắt popup */
    .svc-card,
    .service-card-wrapper,
    .svc-body,
    .service-grid {
        overflow: visible !important;
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
    }

    .offers-section.visible {
        transform: translateY(0) !important;
        display: block !important;
    }

    .offers-section.hidden {
        transform: translateY(100%) !important;
        display: none !important;
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
            grid-template-columns: repeat(2, 1fr) !important;
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
        names: {}, // Store service names for display
        variantParentServices: {} // Store parent service ID for each variant: {variantId: parentServiceId}
    };

    // Load from URL params first (source of truth from appointment form), then sessionStorage
    function loadSelectedServices() {
        const urlParams = new URLSearchParams(window.location.search);

        // Check if URL has service params (means coming from appointment form)
        // Try different ways to check for params
        const hasServiceIds = urlParams.getAll('service_id[]').length > 0 ||
                              urlParams.getAll('service_id').length > 0 ||
                              Array.from(urlParams.keys()).some(key => key.startsWith('service_id'));
        const hasVariants = urlParams.getAll('service_variants[]').length > 0 ||
                            urlParams.getAll('service_variants').length > 0 ||
                            Array.from(urlParams.keys()).some(key => key.startsWith('service_variants'));
        const hasCombos = urlParams.getAll('combo_id[]').length > 0 ||
                          urlParams.getAll('combo_id').length > 0 ||
                          Array.from(urlParams.keys()).some(key => key.startsWith('combo_id'));

        const hasUrlParams = hasServiceIds || hasVariants || hasCombos;

        if (hasUrlParams) {
            // Priority: Load from URL params (from appointment form)
            // Save old prices and names from sessionStorage before reset
            let oldPrices = {};
            let oldNames = {};
            const stored = sessionStorage.getItem('selectedServices');
            if (stored) {
                try {
                    const parsed = JSON.parse(stored);
                    oldPrices = parsed.prices || {};
                    oldNames = parsed.names || {};
                } catch (e) {
                    // Ignore parse errors
                }
            }

            // Reset toàn bộ selectedServices trước khi load từ URL
            selectedServices = {
                serviceIds: [],
                variantIds: [],
                comboIds: [],
                prices: {},
                names: {},
                variantParentServices: {}
            };

            // Get service IDs - try all formats including indexed
            const serviceIds1 = urlParams.getAll('service_id[]');
            const serviceIds2 = urlParams.getAll('service_id');
            let serviceIds = [...serviceIds1, ...serviceIds2].filter(id => id && id !== '0');
            // Check for indexed format service_id[0], etc.
            for (let i = 0; i < 100; i++) {
                const param = urlParams.get(`service_id[${i}]`);
                if (param && param !== '0' && !serviceIds.includes(param)) {
                    serviceIds.push(param);
                } else if (!param && i > 10) break;
            }
            selectedServices.serviceIds = serviceIds;

            // Get variants - try different formats
            const variants1 = urlParams.getAll('service_variants[]');
            const variants2 = urlParams.getAll('service_variants');
            let variantIds = [];
            [...variants1, ...variants2].forEach(id => {
                if (id && id !== '0' && !variantIds.includes(id)) {
                    variantIds.push(id);
                }
            });
            // Check for indexed format service_variants[0], etc.
            for (let i = 0; i < 100; i++) {
                const param = urlParams.get(`service_variants[${i}]`);
                if (param && param !== '0' && !variantIds.includes(param)) {
                    variantIds.push(param);
                } else if (!param && i > 10) break;
            }
            selectedServices.variantIds = variantIds;

            // Get combo IDs - try all formats including indexed
            const combos1 = urlParams.getAll('combo_id[]');
            const combos2 = urlParams.getAll('combo_id');
            let comboIds = [...combos1, ...combos2].filter(id => id && id !== '0');
            // Check for indexed format combo_id[0], etc.
            for (let i = 0; i < 100; i++) {
                const param = urlParams.get(`combo_id[${i}]`);
                if (param && param !== '0' && !comboIds.includes(param)) {
                    comboIds.push(param);
                } else if (!param && i > 10) break;
            }
            selectedServices.comboIds = comboIds;

            // Restore names and prices from old data if available
            // This preserves data when coming back from appointment form
            // QUAN TRỌNG: Restore cho TẤT CẢ IDs đã load từ URL
            // Restore cho services
            selectedServices.serviceIds.forEach(id => {
                const key = 'service_' + id;
                if (oldNames[key]) {
                    selectedServices.names[key] = oldNames[key];
                } else if (!selectedServices.names[key]) {
                    // Set default nếu chưa có
                    selectedServices.names[key] = 'Dịch vụ #' + id;
                }
                if (oldPrices[key] !== undefined) {
                    selectedServices.prices[key] = oldPrices[key];
                } else if (selectedServices.prices[key] === undefined) {
                    // Set default nếu chưa có
                    selectedServices.prices[key] = 0;
                }
            });

            // Restore cho variants
            selectedServices.variantIds.forEach(id => {
                const key = 'variant_' + id;
                if (oldNames[key]) {
                    selectedServices.names[key] = oldNames[key];
                } else if (!selectedServices.names[key]) {
                    // Set default nếu chưa có
                    selectedServices.names[key] = 'Biến thể #' + id;
                }
                if (oldPrices[key] !== undefined) {
                    selectedServices.prices[key] = oldPrices[key];
                } else if (selectedServices.prices[key] === undefined) {
                    // Set default nếu chưa có
                    selectedServices.prices[key] = 0;
                }
                // Restore variantParentServices nếu có
                const stored = sessionStorage.getItem('selectedServices');
                if (stored) {
                    try {
                        const parsed = JSON.parse(stored);
                        if (parsed.variantParentServices && parsed.variantParentServices[id]) {
                            selectedServices.variantParentServices[id] = parsed.variantParentServices[id];
                        }
                    } catch (e) {}
                }
            });

            // Restore cho combos
            selectedServices.comboIds.forEach(id => {
                const key = 'combo_' + id;
                if (oldNames[key]) {
                    selectedServices.names[key] = oldNames[key];
                } else if (!selectedServices.names[key]) {
                    // Set default nếu chưa có
                    selectedServices.names[key] = 'Combo #' + id;
                }
                if (oldPrices[key] !== undefined) {
                    selectedServices.prices[key] = oldPrices[key];
                } else if (selectedServices.prices[key] === undefined) {
                    // Set default nếu chưa có
                    selectedServices.prices[key] = 0;
                }
            });

            // Try to get names and prices from DOM elements if available
            // Use a function to wait for DOM to be ready
            function loadNamesAndPricesFromDOM() {
                let hasUpdates = false;

                selectedServices.serviceIds.forEach(id => {
                    // Try button first, then any element with data-service-id
                    let btn = document.querySelector(`.select-service-btn[data-service-id="${id}"]`) ||
                             document.querySelector(`[data-service-id="${id}"]`);
                    if (btn) {
                        // QUAN TRỌNG: Luôn lấy giá gốc từ DOM (data attributes), không dùng giá đã discount
                        const name = btn.getAttribute('data-service-name') || 'Dịch vụ #' + id;
                        const price = parseFloat(btn.getAttribute('data-service-price') || 0);
                        // Luôn update để đảm bảo dùng giá gốc từ DOM
                        selectedServices.names['service_' + id] = name;
                        selectedServices.prices['service_' + id] = price;
                        hasUpdates = true;
                    } else if (!selectedServices.names['service_' + id]) {
                        // If button not found, still keep the ID and use default name/price
                        selectedServices.names['service_' + id] = 'Dịch vụ #' + id;
                        selectedServices.prices['service_' + id] = 0;
                        hasUpdates = true;
                    }
                });

                selectedServices.variantIds.forEach(id => {
                    // Try variant-item-box first (most common), then any element with data-variant-id
                    let variantBox = document.querySelector(`.variant-item-box[data-variant-id="${id}"]`) ||
                                    document.querySelector(`[data-variant-id="${id}"]`);
                    if (variantBox) {
                        // QUAN TRỌNG: Luôn lấy giá gốc từ DOM (data attributes), không dùng giá đã discount
                        const name = variantBox.getAttribute('data-variant-name') || 'Biến thể #' + id;
                        const price = parseFloat(variantBox.getAttribute('data-variant-price') || 0);
                        const parentServiceId = variantBox.getAttribute('data-service-id');

                        // Luôn update để đảm bảo dùng giá gốc từ DOM
                        selectedServices.names['variant_' + id] = name;
                        selectedServices.prices['variant_' + id] = price;
                        if (parentServiceId) {
                            selectedServices.variantParentServices[id] = parentServiceId;
                        }
                        hasUpdates = true;
                    } else if (!selectedServices.names['variant_' + id]) {
                        // If variant box not found, still keep the ID and use default name/price
                        selectedServices.names['variant_' + id] = 'Biến thể #' + id;
                        selectedServices.prices['variant_' + id] = 0;
                        hasUpdates = true;
                    }
                });

                selectedServices.comboIds.forEach(id => {
                    // Try button first, then any element with data-combo-id
                    let btn = document.querySelector(`.select-service-btn[data-combo-id="${id}"]`) ||
                             document.querySelector(`[data-combo-id="${id}"]`);
                    if (btn) {
                        // QUAN TRỌNG: Luôn lấy giá gốc từ DOM (data attributes), không dùng giá đã discount
                        const name = btn.getAttribute('data-combo-name') || 'Combo #' + id;
                        const price = parseFloat(btn.getAttribute('data-combo-price') || 0);
                        // Luôn update để đảm bảo dùng giá gốc từ DOM
                        selectedServices.names['combo_' + id] = name;
                        selectedServices.prices['combo_' + id] = price;
                        hasUpdates = true;
                    } else if (!selectedServices.names['combo_' + id]) {
                        // If button not found, still keep the ID and use default name/price
                        selectedServices.names['combo_' + id] = 'Combo #' + id;
                        selectedServices.prices['combo_' + id] = 0;
                        hasUpdates = true;
                    }
                });

                // Save after loading names and prices if there were updates
                if (hasUpdates) {
                    saveSelectedServices();
                    updateSummaryBar();
                    updateSelectedServicesList();
                }

                // Update visual state của các nút sau khi load
                updateServiceButtonsDisplay();
                updateVariantBoxesDisplay();
            }

            // Lưu lại ngay sau khi restore để đảm bảo data được lưu
            saveSelectedServices();

            // Update summary bar và list ngay sau khi load từ URL
            // Đảm bảo hiển thị đúng số lượng dịch vụ đã chọn
            updateSummaryBar();
            updateSelectedServicesList();

            // Try immediately
            loadNamesAndPricesFromDOM();

            // Also try after DOM is fully loaded (in case elements load late)
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    loadNamesAndPricesFromDOM();
                });
            }

            // Also try after delays to catch any dynamic content và update lại với giá thật từ DOM
            setTimeout(function() {
                loadNamesAndPricesFromDOM();
                updateServiceButtonsDisplay();
            }, 300);
            setTimeout(function() {
                loadNamesAndPricesFromDOM();
                updateServiceButtonsDisplay();
            }, 1000);
        } else {
            // Trường hợp 2: Không có URL params
            // Kiểm tra xem có phải refresh trang (giữ lại sessionStorage) hay vào trang từ nơi khác (reset)
            const stored = sessionStorage.getItem('selectedServices');
            const pageNavigation = performance.getEntriesByType('navigation')[0];
            const isRefresh = pageNavigation && pageNavigation.type === 'reload';

            if (isRefresh && stored) {
                // Refresh trang: giữ lại sessionStorage để không mất lựa chọn đang chọn
                try {
                    const parsed = JSON.parse(stored);
                    selectedServices = {
                        serviceIds: parsed.serviceIds || [],
                        variantIds: parsed.variantIds || [],
                        comboIds: parsed.comboIds || [],
                        prices: parsed.prices || {},
                        names: parsed.names || {},
                        variantParentServices: parsed.variantParentServices || {}
                    };
                } catch (e) {
                    // Parse error: reset
                    selectedServices = {
                        serviceIds: [],
                        variantIds: [],
                        comboIds: [],
                        prices: {},
                        names: {},
                        variantParentServices: {}
                    };
                    sessionStorage.removeItem('selectedServices');
                }
            } else {
                // Vào trang từ nơi khác (không phải refresh) = thoát ra và vào lại
                // Bắt đầu từ đầu theo yêu cầu: chọn lại từ đầu khi chưa ấn Xong mà thoát ra
                selectedServices = {
                    serviceIds: [],
                    variantIds: [],
                    comboIds: [],
                    prices: {},
                    names: {},
                    variantParentServices: {}
                };
                // Xóa sessionStorage cũ để đảm bảo bắt đầu mới
                sessionStorage.removeItem('selectedServices');
            }
        }
        saveSelectedServices();
    }

    function saveSelectedServices() {
        sessionStorage.setItem('selectedServices', JSON.stringify(selectedServices));
    }

    // Promotion data from server
    @php
        $promotionForJs = null;
        if (isset($selectedPromotion) && $selectedPromotion) {
            // Load relationships if not loaded
            if (!$selectedPromotion->relationLoaded('services')) {
                $selectedPromotion->load('services');
            }
            if (!$selectedPromotion->relationLoaded('combos')) {
                $selectedPromotion->load('combos');
            }
            if (!$selectedPromotion->relationLoaded('serviceVariants')) {
                $selectedPromotion->load('serviceVariants');
            }

            $promotionForJs = [
                'id' => $selectedPromotion->id,
                'name' => $selectedPromotion->name,
                'discount_type' => $selectedPromotion->discount_type,
                'discount_percent' => $selectedPromotion->discount_percent ?? 0,
                'discount_amount' => $selectedPromotion->discount_amount ?? 0,
                'max_discount_amount' => $selectedPromotion->max_discount_amount ?? null,
                'apply_scope' => $selectedPromotion->apply_scope,
                'service_ids' => $selectedPromotion->services->pluck('id')->toArray(),
                'variant_ids' => $selectedPromotion->serviceVariants->pluck('id')->toArray(),
                'combo_ids' => $selectedPromotion->combos->pluck('id')->toArray()
            ];
        }
    @endphp
    const promotionData = @json($promotionForJs);

    // Update offers section visibility and link
    function updateOffersSection() {
        const offersSection = document.getElementById('offersSection');
        const selectOffersLink = document.getElementById('selectOffersLink');
        const totalCount = selectedServices.serviceIds.length + selectedServices.variantIds.length + selectedServices.comboIds.length;

        // Check if there are any services that could be eligible for promotions
        // This is a simple check - we'll let the server filter promotions in select-offers page
        // For now, show offers section if there are services selected
        // The server will filter out promotions that don't apply

        if (offersSection) {
            if (totalCount > 0) {
                // Show offers section when services are selected
                offersSection.style.display = 'block';
                offersSection.classList.add('visible');
                offersSection.classList.remove('hidden');
            } else {
                // Hide offers section when no services selected
                offersSection.style.display = 'none';
            }
        }

        // Update "Chọn ưu đãi" link with current selected services
        if (selectOffersLink && totalCount > 0) {
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

            // Add promotion_id if exists in URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('promotion_id')) {
                params.append('promotion_id', urlParams.get('promotion_id'));
            }

            const offersUrl = '{{ route("site.appointment.select-offers") }}' + '?' + params.toString();
            selectOffersLink.href = offersUrl;
        }
    }

    // Update summary bar
    function updateSummaryBar() {
        const totalCount = selectedServices.serviceIds.length + selectedServices.variantIds.length + selectedServices.comboIds.length;
        let totalPrice = 0;

        // Calculate total price from stored prices
        Object.values(selectedServices.prices).forEach(price => {
            totalPrice += parseFloat(price) || 0;
        });

        // Calculate discount if promotion exists - only on applicable services
        let discountAmount = 0;
        let applicablePrice = 0; // Price of services that match promotion
        let finalPrice = totalPrice;
        let applicableServices = []; // Track which services are eligible
        let nonApplicableServices = []; // Track which services are NOT eligible

        if (promotionData) {
            // Check if promotion applies to all services
            const hasSpecificServices = (promotionData.service_ids && promotionData.service_ids.length > 0)
                || (promotionData.combo_ids && promotionData.combo_ids.length > 0)
                || (promotionData.variant_ids && promotionData.variant_ids.length > 0);

            const applyToAll = !hasSpecificServices ||
                ((promotionData.service_ids?.length || 0) + (promotionData.combo_ids?.length || 0) + (promotionData.variant_ids?.length || 0)) >= 20;

            // Calculate applicable price (only for services that match promotion)
            if (promotionData.apply_scope === 'order' || applyToAll) {
                // Apply to all services
                applicablePrice = totalPrice;
                // All services are applicable
                selectedServices.serviceIds.forEach(id => {
                    applicableServices.push({type: 'service', id: id, name: selectedServices.names['service_' + id] || 'Dịch vụ #' + id});
                });
                selectedServices.variantIds.forEach(id => {
                    applicableServices.push({type: 'variant', id: id, name: selectedServices.names['variant_' + id] || 'Biến thể #' + id});
                });
                selectedServices.comboIds.forEach(id => {
                    applicableServices.push({type: 'combo', id: id, name: selectedServices.names['combo_' + id] || 'Combo #' + id});
                });
            } else {
                // Only apply to matching services
                // Check services
                selectedServices.serviceIds.forEach(id => {
                    if (promotionData.service_ids && promotionData.service_ids.includes(parseInt(id))) {
                        applicablePrice += parseFloat(selectedServices.prices['service_' + id] || 0);
                        applicableServices.push({type: 'service', id: id, name: selectedServices.names['service_' + id] || 'Dịch vụ #' + id});
                    } else {
                        nonApplicableServices.push({type: 'service', id: id, name: selectedServices.names['service_' + id] || 'Dịch vụ #' + id});
                    }
                });

                // Check variants
                selectedServices.variantIds.forEach(id => {
                    let isApplicable = false;

                    // Check direct variant match
                    if (promotionData.variant_ids && promotionData.variant_ids.includes(parseInt(id))) {
                        isApplicable = true;
                    } else {
                        // Check if variant's parent service is in promotion
                        const parentServiceId = selectedServices.variantParentServices[id];
                        if (parentServiceId && promotionData.service_ids && promotionData.service_ids.includes(parseInt(parentServiceId))) {
                            isApplicable = true;
                        }
                    }

                    if (isApplicable) {
                        applicablePrice += parseFloat(selectedServices.prices['variant_' + id] || 0);
                        applicableServices.push({type: 'variant', id: id, name: selectedServices.names['variant_' + id] || 'Biến thể #' + id});
                    } else {
                        nonApplicableServices.push({type: 'variant', id: id, name: selectedServices.names['variant_' + id] || 'Biến thể #' + id});
                    }
                });

                // Check combos
                selectedServices.comboIds.forEach(id => {
                    if (promotionData.combo_ids && promotionData.combo_ids.includes(parseInt(id))) {
                        applicablePrice += parseFloat(selectedServices.prices['combo_' + id] || 0);
                        applicableServices.push({type: 'combo', id: id, name: selectedServices.names['combo_' + id] || 'Combo #' + id});
                    } else {
                        nonApplicableServices.push({type: 'combo', id: id, name: selectedServices.names['combo_' + id] || 'Combo #' + id});
                    }
                });
            }

            // Calculate discount on applicable price only
            // QUAN TRỌNG: CHỈ tính discount để hiển thị, KHÔNG lưu vào selectedServices.prices
            // Giữ nguyên giá gốc trong selectedServices.prices để tránh giá nhảy lung tung
            if (applicablePrice > 0) {
                if (promotionData.discount_type === 'percent') {
                    discountAmount = (applicablePrice * promotionData.discount_percent) / 100;
                    if (promotionData.max_discount_amount) {
                        discountAmount = Math.min(discountAmount, promotionData.max_discount_amount);
                    }
                } else {
                    discountAmount = Math.min(promotionData.discount_amount, applicablePrice);
                }
                finalPrice = Math.max(0, totalPrice - discountAmount);
            }
        }

        const selectedCountEl = document.getElementById('selectedCount');
        const totalPriceEl = document.getElementById('totalPrice');
        const doneButton = document.getElementById('doneButton');

        if (selectedCountEl) {
            selectedCountEl.textContent = totalCount;
        }

        if (totalPriceEl) {
            const priceSection = totalPriceEl.closest('.total-price-section');
            if (!priceSection) return;

            // Find or create label
            let labelEl = priceSection.querySelector('.total-label');
            if (!labelEl) {
                labelEl = document.createElement('div');
                labelEl.className = 'total-label';
                labelEl.style.cssText = 'font-size: 11px; color: #666; line-height: 1.3; margin-bottom: 1px;';
                priceSection.insertBefore(labelEl, totalPriceEl);
            }
            labelEl.textContent = 'Tổng thanh toán';

            // Update discount display if promotion exists
            if (promotionData && discountAmount > 0) {
                let existingStrike = priceSection.querySelector('.original-price-strike');
                let existingDiscount = priceSection.querySelector('.discount-amount');
                let existingServiceList = priceSection.querySelector('.service-discount-list');

                if (!existingStrike) {
                    existingStrike = document.createElement('div');
                    existingStrike.className = 'original-price-strike';
                    existingStrike.style.cssText = 'font-size: 13px; color: #999; text-decoration: line-through; line-height: 1.4; margin-bottom: 3px; font-weight: 500;';
                    priceSection.insertBefore(existingStrike, labelEl);
                }
                existingStrike.textContent = 'Giá gốc: ' + formatPrice(totalPrice) + ' VNĐ';
                existingStrike.style.display = 'block';

                if (!existingDiscount) {
                    existingDiscount = document.createElement('div');
                    existingDiscount.className = 'discount-amount';
                    existingDiscount.style.cssText = 'font-size: 13px; color: #28a745; font-weight: 600; line-height: 1.4; margin-bottom: 6px;';
                    priceSection.insertBefore(existingDiscount, labelEl);
                }
                existingDiscount.textContent = '✓ Giảm: ' + formatPrice(discountAmount) + ' VNĐ';
                existingDiscount.style.display = 'block';

                // Show service list if there are both applicable and non-applicable services
                if (nonApplicableServices.length > 0 && applicableServices.length > 0) {
                    if (!existingServiceList) {
                        existingServiceList = document.createElement('div');
                        existingServiceList.className = 'service-discount-list';
                        existingServiceList.style.cssText = 'background: #f8f9fa; border-left: 3px solid #28a745; padding: 8px 10px; border-radius: 4px; font-size: 12px; line-height: 1.5; margin-top: 6px; margin-bottom: 4px;';
                        priceSection.insertBefore(existingServiceList, labelEl);
                    }
                    let listHtml = '<div style="margin-bottom: 6px;"><span style="color: #28a745; font-weight: 600;">✓ Được giảm giá:</span> ';
                    listHtml += '<span style="color: #333; font-weight: 500;">' + applicableServices.map(s => s.name).join(', ') + '</span>';
                    listHtml += '</div>';
                    listHtml += '<div><span style="color: #999; font-weight: 500;">○ Không được giảm:</span> ';
                    listHtml += '<span style="color: #666;">' + nonApplicableServices.map(s => s.name).join(', ') + '</span>';
                    listHtml += '</div>';
                    existingServiceList.innerHTML = listHtml;
                    existingServiceList.style.display = 'block';
                } else if (existingServiceList) {
                    existingServiceList.style.display = 'none';
                }
            } else {
                // Hide discount display if no promotion
                const existingStrike = priceSection.querySelector('.original-price-strike');
                const existingDiscount = priceSection.querySelector('.discount-amount');
                const existingServiceList = priceSection.querySelector('.service-discount-list');
                if (existingStrike) existingStrike.style.display = 'none';
                if (existingDiscount) existingDiscount.style.display = 'none';
                if (existingServiceList) existingServiceList.style.display = 'none';
            }

            // Update final price
            totalPriceEl.textContent = formatPrice(finalPrice) + ' VNĐ';
            totalPriceEl.style.cssText = 'font-size: 20px; font-weight: 700; color: #000; line-height: 1.2;';
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
    function handleServiceSelection(serviceId, price, type, name = '', serviceParentId = null) {
        const id = serviceId.toString();

        // KHÔNG CHO PHÉP BỎ CHỌN: Nếu dịch vụ đã được chọn, không làm gì cả
        // Chỉ cho phép bỏ chọn từ form "dịch vụ đã chọn" (nút X)
        if (type === 'service') {
            if (selectedServices.serviceIds.includes(id)) {
                // Đã chọn rồi, không cho phép bỏ chọn từ đây
                return;
            } else {
                selectedServices.serviceIds.push(id);
                selectedServices.prices['service_' + id] = price;
                selectedServices.names['service_' + id] = name || 'Dịch vụ #' + id;
            }
        } else if (type === 'variant') {
            if (selectedServices.variantIds.includes(id)) {
                // Đã chọn rồi, không cho phép bỏ chọn từ đây
                return;
            }

            // If selecting a variant, remove all other variants from the same service
            if (serviceParentId) {
                const parentServiceId = serviceParentId.toString();
                // Find all variants of this service and remove them
                const allVariantBoxes = document.querySelectorAll(`[data-service-id="${parentServiceId}"].variant-item-box`);
                allVariantBoxes.forEach(box => {
                    const variantId = box.getAttribute('data-variant-id');
                    if (variantId && variantId !== id) {
                        // Remove other variants from selection
                        selectedServices.variantIds = selectedServices.variantIds.filter(vid => vid !== variantId);
                        delete selectedServices.prices['variant_' + variantId];
                        delete selectedServices.names['variant_' + variantId];
                        delete selectedServices.variantParentServices[variantId];
                        // Reset visual state
                        box.style.background = '#f8f9fa';
                        box.style.borderColor = '#e0e0e0';
                    }
                });
            }

            selectedServices.variantIds.push(id);
            selectedServices.prices['variant_' + id] = price;
            selectedServices.names['variant_' + id] = name || 'Biến thể #' + id;
            // Store parent service ID if available
            if (serviceParentId) {
                selectedServices.variantParentServices[id] = serviceParentId.toString();
            }
        } else if (type === 'combo') {
            if (selectedServices.comboIds.includes(id)) {
                // Đã chọn rồi, không cho phép bỏ chọn từ đây
                return;
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
        updateVariantBoxesDisplay();
        updateServiceButtonsDisplay(); // Update visual state của các nút
        updateOffersSection();
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
            delete selectedServices.variantParentServices[id];
        } else if (type === 'combo') {
            selectedServices.comboIds = selectedServices.comboIds.filter(cid => cid !== id);
            delete selectedServices.prices['combo_' + id];
            delete selectedServices.names['combo_' + id];
        }

        saveSelectedServices();
        updateSummaryBar();
        updateSelectedServicesList();
        updateServiceButtonsDisplay(); // Update visual state của các nút
        updateVariantBoxesDisplay();
        updateOffersSection();
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
                e.stopPropagation(); // Ngăn event bubble lên để không đóng form
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

        // Keep promotion_id if exists
        if (urlParams.get('promotion_id')) {
            params.append('promotion_id', urlParams.get('promotion_id'));
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
        // Chỉ đóng form nếu click bên ngoài selectedServicesList và selectedServicesLink
        // Không đóng khi click vào bất kỳ phần tử nào bên trong selectedServicesList (bao gồm nút xóa)
        if (selectedServicesList && selectedServicesLink) {
            const clickedInsideList = selectedServicesList.contains(e.target);
            const clickedOnLink = selectedServicesLink.contains(e.target);

            if (!clickedInsideList && !clickedOnLink) {
                selectedServicesList.style.display = 'none';
                if (toggleServicesListBtn) {
                    toggleServicesListBtn.style.transform = 'rotate(0deg)';
                }
            }
        }
    });

    // Initialize
    // Update variant boxes display on load
    function updateVariantBoxesDisplay() {
        document.querySelectorAll('.variant-item-box').forEach(box => {
            const variantId = box.getAttribute('data-variant-id');
            const isSelected = selectedServices.variantIds.includes(variantId);

            if (isSelected) {
                box.style.background = '#e3f2fd';
                box.style.borderColor = '#0066cc';
            } else {
                box.style.background = '#f8f9fa';
                box.style.borderColor = '#e0e0e0';
            }
        });
    }

    // Update service buttons display to show selected state
    function updateServiceButtonsDisplay() {
        // Update service buttons
        document.querySelectorAll('.select-service-btn').forEach(button => {
            const serviceId = button.getAttribute('data-service-id');
            const variantId = button.getAttribute('data-variant-id');
            const comboId = button.getAttribute('data-combo-id');

            let isSelected = false;
            if (serviceId && selectedServices.serviceIds.includes(serviceId)) {
                isSelected = true;
            } else if (variantId && selectedServices.variantIds.includes(variantId)) {
                isSelected = true;
            } else if (comboId && selectedServices.comboIds.includes(comboId)) {
                isSelected = true;
            }

            if (isSelected) {
                // Dịch vụ đã được chọn: disable nút, đổi màu, hiển thị icon check
                button.disabled = true;
                button.style.background = '#28a745';
                button.style.borderColor = '#28a745';
                button.style.color = '#fff';
                button.style.cursor = 'not-allowed';
                button.style.opacity = '0.8';

                // Thay đổi text và icon
                const icon = button.querySelector('i');
                if (icon) {
                    icon.className = 'fa fa-check-circle';
                }
                const textSpan = button.querySelector('span');
                if (textSpan) {
                    textSpan.textContent = 'Đã chọn';
                } else {
                    // Nếu không có span, thay đổi toàn bộ text
                    const originalText = button.innerHTML;
                    if (!originalText.includes('Đã chọn')) {
                        button.innerHTML = '<i class="fa fa-check-circle"></i> Đã chọn';
                    }
                }
            } else {
                // Dịch vụ chưa được chọn: enable nút, màu mặc định
                button.disabled = false;
                button.style.background = '#000';
                button.style.borderColor = '#000';
                button.style.color = '#fff';
                button.style.cursor = 'pointer';
                button.style.opacity = '1';

                // Khôi phục text và icon mặc định
                const icon = button.querySelector('i');
                if (icon && icon.classList.contains('fa-check-circle')) {
                    icon.className = 'fa fa-check';
                }
                const textSpan = button.querySelector('span');
                if (textSpan && textSpan.textContent === 'Đã chọn') {
                    textSpan.textContent = 'Chọn';
                } else if (button.innerHTML.includes('Đã chọn')) {
                    button.innerHTML = '<i class="fa fa-check"></i> Chọn';
                }
            }
        });
    }


    loadSelectedServices();

    // Update visual state sau khi load - đảm bảo DOM đã sẵn sàng
    function updateAfterLoad() {
        updateSummaryBar();
        updateSelectedServicesList();
        updateVariantBoxesDisplay();
        updateServiceButtonsDisplay(); // Update visual state của các nút sau khi load
        updateOffersSection();
    }

    // Update ngay lập tức
    updateAfterLoad();

    // Update lại sau khi DOM sẵn sàng (nếu chưa sẵn sàng)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(updateAfterLoad, 100);
        });
    }

    // Update lại sau delay để đảm bảo tất cả đã render
    setTimeout(updateAfterLoad, 500);
    setTimeout(updateAfterLoad, 1500);

    // Handle "Chọn ưu đãi" link click - save before navigating
    const selectOffersLink = document.getElementById('selectOffersLink');
    if (selectOffersLink) {
        selectOffersLink.addEventListener('click', function(e) {
            // Save selected services before navigating
            saveSelectedServices();
            // Let the link navigate normally (URL params are already set in href)
        });
    }

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
                // Try to get parent service ID from button or closest variant-item-box
                let parentServiceId = this.getAttribute('data-service-id');
                if (!parentServiceId) {
                    const variantBox = this.closest('.variant-item-box');
                    if (variantBox) {
                        parentServiceId = variantBox.getAttribute('data-service-id');
                    }
                }
                const variantName = this.getAttribute('data-variant-name') || '';
                handleServiceSelection(variantId, variantPrice, 'variant', variantName, parentServiceId);
            } else if (comboId) {
                handleServiceSelection(comboId, comboPrice, 'combo', comboName);
            } else if (serviceId) {
                handleServiceSelection(serviceId, servicePrice, 'service', serviceName);
            }
        });
    });

    // Handle toggle variant list button clicks - hiển thị như popup bên ngoài
    document.querySelectorAll('.select-variant-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const serviceId = this.getAttribute('data-service-id');
            const variantsList = document.querySelector(`.variants-list[data-service-id="${serviceId}"]`);
            const icon = this.querySelector('.fa-chevron-down');

            if (variantsList) {
                if (variantsList.classList.contains('variants-list-hidden')) {
                    // Đóng tất cả popup khác
                    document.querySelectorAll('.variants-list').forEach(list => {
                        if (list !== variantsList) {
                            list.classList.add('variants-list-hidden');
                            list.classList.remove('variants-list-visible');
                            list.style.display = 'none';
                        }
                    });
                    document.querySelectorAll('.select-variant-btn').forEach(b => {
                        if (b !== btn) {
                            b.classList.remove('active');
                            const i = b.querySelector('.fa-chevron-down');
                            if (i) i.style.transform = 'rotate(0deg)';
                        }
                    });

                    // Tính toán vị trí từ button
                    const btnRect = this.getBoundingClientRect();

                    // Di chuyển popup ra body để không bị ảnh hưởng bởi container
                    if (variantsList.parentElement !== document.body) {
                        document.body.appendChild(variantsList);
                    }

                    // Show variants như popup bên ngoài - full ra ngoài
                    variantsList.classList.remove('variants-list-hidden');
                    variantsList.classList.add('variants-list-visible');
                    variantsList.style.display = 'flex';
                    variantsList.style.position = 'fixed';
                    variantsList.style.top = (btnRect.bottom + 4) + 'px';
                    variantsList.style.left = btnRect.left + 'px';
                    variantsList.style.zIndex = '999999';
                    variantsList.style.width = 'max-content';
                    variantsList.style.minWidth = 'auto';
                    variantsList.style.maxWidth = 'calc(100vw - 40px)';
                    variantsList.style.overflowX = 'hidden';
                    this.classList.add('active');
                    if (icon) icon.style.transform = 'rotate(180deg)';
                } else {
                    // Hide variants
                    variantsList.classList.remove('variants-list-visible');
                    variantsList.classList.add('variants-list-hidden');
                    variantsList.style.display = 'none';
                    this.classList.remove('active');
                    if (icon) icon.style.transform = 'rotate(0deg)';
                }
            }
        });
    });

    // Đóng popup khi click ra ngoài
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.select-variant-btn') && !e.target.closest('.variants-list')) {
            document.querySelectorAll('.variants-list').forEach(list => {
                list.classList.add('variants-list-hidden');
                list.classList.remove('variants-list-visible');
                list.style.display = 'none';
            });
            document.querySelectorAll('.select-variant-btn').forEach(btn => {
                btn.classList.remove('active');
                const icon = btn.querySelector('.fa-chevron-down');
                if (icon) icon.style.transform = 'rotate(0deg)';
            });
        }
    });

    // Cập nhật vị trí popup khi scroll
    window.addEventListener('scroll', function() {
        document.querySelectorAll('.variants-list.variants-list-visible').forEach(list => {
            const serviceId = list.getAttribute('data-service-id');
            const btn = document.querySelector(`.select-variant-btn[data-service-id="${serviceId}"]`);
            if (btn) {
                const btnRect = btn.getBoundingClientRect();
                list.style.top = (btnRect.bottom + 4) + 'px';
                list.style.left = btnRect.left + 'px';
            }
        });
    }, { passive: true });

    // Handle variant item clicks
    document.querySelectorAll('.variant-item-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const variantId = this.getAttribute('data-variant-id');
            const serviceId = this.getAttribute('data-service-id');
            const variantPrice = parseFloat(this.getAttribute('data-variant-price') || 0);
            if (variantId) {
                const variantName = this.getAttribute('data-variant-name') || 'Biến thể #' + variantId;
                const isCurrentlySelected = selectedServices.variantIds.includes(variantId);

                handleServiceSelection(variantId, variantPrice, 'variant', variantName, serviceId);

                // Visual feedback - highlight selected variant
                const variantsList = this.closest('.variants-list');
                if (variantsList) {
                    const variantBoxes = variantsList.querySelectorAll('.variant-item-box');
                    variantBoxes.forEach(box => {
                        const boxVariantId = box.getAttribute('data-variant-id');
                        const isSelected = selectedServices.variantIds.includes(boxVariantId);

                        if (isSelected) {
                            box.style.background = '#e3f2fd';
                            box.style.borderColor = '#0066cc';
                        } else {
                            box.style.background = '#f8f9fa';
                            box.style.borderColor = '#e0e0e0';
                        }
                    });
                }
            }
        });
    });

    // Handle done button
    const doneButton = document.getElementById('doneButton');
    if (doneButton) {
        doneButton.addEventListener('click', handleDoneClick);
    }

    // Offers section visibility is now controlled by updateOffersSection() based on selected services
    // No longer using scroll-based show/hide

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
