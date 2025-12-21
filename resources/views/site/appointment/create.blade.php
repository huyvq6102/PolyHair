@extends('layouts.site')

@section('title', 'Đặt lịch ngay')

@section('content')
<div class="appointment-page" style="padding: 140px 0 40px; background: #f8f9fa; min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-7">
                <div class="appointment-form-container" style="background: #fff; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 20px 25px; margin-bottom: 20px; margin-top: 0;">

                    <!-- Header -->
                    <div class="text-center mb-2" style="margin-bottom: 15px;">
                        <h2 class="fw-bold mb-1" style="background: linear-gradient(135deg, #d8b26a 0%, #8b5a2b 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 18px; margin-bottom: 4px;">
                            <i class="fa fa-calendar-check-o"></i> ĐẶT LỊCH NGAY
                        </h2>
                        <p class="text-muted mb-0" style="font-size: 12px; color: #666; margin: 0;">
                            Hãy liên hệ ngay với chúng tôi để được tư vấn sớm nhất về các mẫu tóc hot nhất hiện nay!
                        </p>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ url(route('site.appointment.store')) }}" method="POST" id="appointmentForm" novalidate>
                        @csrf

                        @if(request('service_id'))
                            @php
                                // CRITICAL: Get services ONLY from query string
                                $queryServices = request()->query('service_id', []);

                                // Convert to array if single value
                                if (!is_array($queryServices)) {
                                    $queryServices = $queryServices ? [$queryServices] : [];
                                }

                                // Filter out any empty/null values
                                $serviceIds = array_filter($queryServices, function($id) {
                                    return !empty($id) && $id !== '0' && $id !== 0 && is_numeric($id);
                                });

                                // Remove duplicates - ensure each service ID appears only once
                                $serviceIds = array_values(array_unique($serviceIds));
                            @endphp
                            @foreach($serviceIds as $serviceId)
                                @if(!request('remove_service_id') || request('remove_service_id') != $serviceId)
                                    <input type="hidden" name="service_id[]" value="{{ $serviceId }}">
                                @endif
                            @endforeach
                        @endif

                        @if(request()->has('service_variants'))
                            @php
                                // CRITICAL: Get variants ONLY from query string, not from any other source
                                // Parse URL manually to avoid any Laravel request merging issues
                                $url = request()->fullUrl();
                                $parsedUrl = parse_url($url);
                                $queryParams = [];
                                if (isset($parsedUrl['query'])) {
                                    parse_str($parsedUrl['query'], $queryParams);
                                }

                                // Get service_variants from parsed query string only
                                // Handle both formats: service_variants[] and service_variants[0], service_variants[1], etc.
                                $queryVariants = [];

                                // Check for service_variants[] format
                                if (isset($queryParams['service_variants']) && is_array($queryParams['service_variants'])) {
                                    $queryVariants = $queryParams['service_variants'];
                                } elseif (isset($queryParams['service_variants'])) {
                                    $queryVariants = [$queryParams['service_variants']];
                                }

                                // Check for service_variants[0], service_variants[1], etc. format
                                $indexedVariants = [];
                                foreach ($queryParams as $key => $value) {
                                    if (preg_match('/^service_variants\[(\d+)\]$/', $key, $matches)) {
                                        $indexedVariants[] = $value;
                                    }
                                }

                                // Merge both formats
                                $queryVariants = array_merge($queryVariants, $indexedVariants);

                                // Filter out any empty/null values
                                $variantIds = array_filter($queryVariants, function($id) {
                                    return !empty($id) && $id !== '0' && $id !== 0 && is_numeric($id);
                                });

                                // Remove duplicates - ensure each variant ID appears only once
                                $variantIds = array_values(array_unique($variantIds));

                                // Debug log (only in development)
                                if (config('app.debug')) {
                                    \Log::info('Appointment form - Creating hidden inputs', [
                                        'url' => $url,
                                        'parsed_query' => $queryParams,
                                        'query_variants' => $queryVariants,
                                        'indexed_variants' => $indexedVariants,
                                        'filtered_variants' => $variantIds,
                                        'count' => count($variantIds),
                                        'request_all' => request()->all(),
                                        'request_query' => request()->query(),
                                    ]);
                                }
                            @endphp
                            @foreach($variantIds as $variantId)
                                @if(!request('remove_variant_id') || request('remove_variant_id') != $variantId)
                                    <input type="hidden" name="service_variants[]" value="{{ $variantId }}">
                                @endif
                            @endforeach
                        @endif

                        @if(request('combo_id'))
                            @php
                                // CRITICAL: Get combos ONLY from query string
                                $queryCombos = request()->query('combo_id', []);

                                // Convert to array if single value
                                if (!is_array($queryCombos)) {
                                    $queryCombos = $queryCombos ? [$queryCombos] : [];
                                }

                                // Filter out any empty/null values
                                $comboIds = array_filter($queryCombos, function($id) {
                                    return !empty($id) && $id !== '0' && $id !== 0 && is_numeric($id);
                                });

                                // Remove duplicates - ensure each combo ID appears only once
                                $comboIds = array_values(array_unique($comboIds));
                            @endphp
                            @foreach($comboIds as $comboId)
                                @if(!request('remove_combo_id') || request('remove_combo_id') != $comboId)
                                    <input type="hidden" name="combo_id[]" value="{{ $comboId }}">
                                @endif
                            @endforeach
                        @endif

                        <!-- Thông tin khách hàng -->
                        <div class="mb-2">
                            <div style="margin-bottom: 10px;">
                                <h5 class="fw-semibold mb-0" style="background: linear-gradient(135deg, #d8b26a 0%, #8b5a2b 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 14px;">
                                    1. Thông tin khách hàng
                                </h5>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label" style="font-size: 12px; margin-bottom: 5px; font-weight: 500;">
                                        <i class="fa fa-user-circle"></i> Họ và tên <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           name="name"
                                           id="name"
                                           class="form-control"
                                           style="font-size: 12px; padding: 8px 12px; height: 38px; border: 1px solid #ddd; border-radius: 6px;"
                                           placeholder="Họ tên"
                                           value="{{ old('name', auth()->user()->name ?? '') }}">
                                    <div class="field-error" id="name-error" style="display: none; color: #dc3545; font-size: 10px; margin-top: 3px;">
                                        <i class="fa fa-exclamation-circle"></i> <span></span>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-2">
                                    <label class="form-label" style="font-size: 12px; margin-bottom: 5px; font-weight: 500;">
                                        <i class="fa fa-phone"></i> Số điện thoại <span class="text-danger">*</span>
                                    </label>
                                    <input type="tel"
                                           name="phone"
                                           id="phone"
                                           class="form-control"
                                           style="font-size: 12px; padding: 8px 12px; height: 38px; border: 1px solid #ddd; border-radius: 6px;"
                                           placeholder="Số điện thoại"
                                           value="{{ old('phone', auth()->user()->phone ?? '') }}">
                                    <div class="field-error" id="phone-error" style="display: none; color: #dc3545; font-size: 10px; margin-top: 3px;">
                                        <i class="fa fa-exclamation-circle"></i> <span></span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label" style="font-size: 12px; margin-bottom: 5px; font-weight: 500;">
                                    <i class="fa fa-envelope"></i> Email
                                </label>
                                <input type="email"
                                       name="email"
                                       id="email"
                                       class="form-control"
                                       style="font-size: 12px; padding: 8px 12px; height: 38px; border: 1px solid #ddd; border-radius: 6px;"
                                       placeholder="Email (tùy chọn)"
                                       value="{{ old('email', auth()->user()->email ?? '') }}">
                                <div class="field-error" id="email-error" style="display: none; color: #dc3545; font-size: 10px; margin-top: 3px;">
                                    <i class="fa fa-exclamation-circle"></i> <span></span>
                                </div>
                            </div>
                        </div>

                        <!-- Chọn dịch vụ -->
                        <div class="mb-2" style="margin-top: 15px;">
                            <h5 class="fw-semibold mb-2" style="background: linear-gradient(135deg, #d8b26a 0%, #8b5a2b 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 14px; margin-bottom: 8px;">
                                2. DỊCH VỤ <span class="text-danger">*</span>
                            </h5>

                            @php
                                // Helper function để tính discount cho từng item
                                // Logic này phải giống với service-list-items.blade.php để đảm bảo discount hiển thị đúng
                                function calculateDiscountForItemInCreate($item, $itemType, $activePromotions) {
                                    $originalPrice = 0;
                                    if ($itemType === 'service') {
                                        $originalPrice = $item->base_price ?? 0;
                                    } elseif ($itemType === 'variant') {
                                        $originalPrice = $item->price ?? 0;
                                    } elseif ($itemType === 'combo') {
                                        $originalPrice = $item->price ?? 0;
                                    }

                                    $discount = 0;        // Mức giảm cao nhất tìm được
                                    $finalPrice = $originalPrice;
                                    $promotion = null;    // Khuyến mãi mang lại giảm giá cao nhất

                                    if ($originalPrice <= 0) {
                                        return ['originalPrice' => 0, 'discount' => 0, 'finalPrice' => 0, 'promotion' => null];
                                    }

                                    $now = \Carbon\Carbon::now();

                                    foreach ($activePromotions ?? [] as $promo) {
                                        // Chỉ áp dụng giảm trực tiếp vào dịch vụ khi khuyến mãi được cấu hình "Theo dịch vụ"
                                        // Logic này phải giống với service-list-items.blade.php
                                        if ($promo->apply_scope !== 'service') {
                                            continue;
                                        }
                                        if ($promo->status !== 'active') continue;
                                        if ($promo->start_date && $promo->start_date > $now) continue;
                                        if ($promo->end_date && $promo->end_date < $now) continue;
                                        
                                        // Check usage_limit - if promotion has reached its limit, skip it
                                        if ($promo->usage_limit) {
                                            $totalUsage = \App\Models\PromotionUsage::where('promotion_id', $promo->id)->count();
                                            if ($totalUsage >= $promo->usage_limit) {
                                                continue; // Skip this promotion, use original price
                                            }
                                        }
                                        
                                        // Check per_user_limit - if user has reached their limit, skip it
                                        if ($promo->per_user_limit) {
                                            $userId = auth()->id();
                                            if ($userId) {
                                                $userUsage = \App\Models\PromotionUsage::where('promotion_id', $promo->id)
                                                    ->where('user_id', $userId)
                                                    ->count();
                                                if ($userUsage >= $promo->per_user_limit) {
                                                    continue; // Skip this promotion, use original price
                                                }
                                            }
                                        }

                                        $applies = false;

                                        if ($itemType === 'service') {
                                            $hasSpecificServices = ($promo->services && $promo->services->count() > 0)
                                                || ($promo->combos && $promo->combos->count() > 0)
                                                || ($promo->serviceVariants && $promo->serviceVariants->count() > 0);
                                            $applyToAll = !$hasSpecificServices ||
                                                (($promo->services ? $promo->services->count() : 0) +
                                                 ($promo->combos ? $promo->combos->count() : 0) +
                                                 ($promo->serviceVariants ? $promo->serviceVariants->count() : 0)) >= 20;
                                            if ($promo->apply_scope === 'order' || $applyToAll) {
                                                $applies = true;
                                            } elseif ($promo->services && $promo->services->contains('id', $item->id)) {
                                                $applies = true;
                                            }
                                        } elseif ($itemType === 'variant') {
                                            $hasSpecificServices = ($promo->services && $promo->services->count() > 0)
                                                || ($promo->combos && $promo->combos->count() > 0)
                                                || ($promo->serviceVariants && $promo->serviceVariants->count() > 0);
                                            $applyToAll = !$hasSpecificServices ||
                                                (($promo->services ? $promo->services->count() : 0) +
                                                 ($promo->combos ? $promo->combos->count() : 0) +
                                                 ($promo->serviceVariants ? $promo->serviceVariants->count() : 0)) >= 20;
                                            if ($promo->apply_scope === 'order' || $applyToAll) {
                                                $applies = true;
                                            } elseif ($promo->serviceVariants && $promo->serviceVariants->contains('id', $item->id)) {
                                                $applies = true;
                                            } elseif ($item->service_id && $promo->services && $promo->services->contains('id', $item->service_id)) {
                                                $applies = true;
                                            }
                                        } elseif ($itemType === 'combo') {
                                            $hasSpecificServices = ($promo->services && $promo->services->count() > 0)
                                                || ($promo->combos && $promo->combos->count() > 0)
                                                || ($promo->serviceVariants && $promo->serviceVariants->count() > 0);
                                            $applyToAll = !$hasSpecificServices ||
                                                (($promo->services ? $promo->services->count() : 0) +
                                                 ($promo->combos ? $promo->combos->count() : 0) +
                                                 ($promo->serviceVariants ? $promo->serviceVariants->count() : 0)) >= 20;
                                            if ($promo->apply_scope === 'order' || $applyToAll) {
                                                $applies = true;
                                            } elseif ($promo->combos && $promo->combos->contains('id', $item->id)) {
                                                $applies = true;
                                            }
                                        }

                                        if ($applies) {
                                            // Tính mức giảm cho promo hiện tại
                                            $currentDiscount = 0;

                                            if ($promo->discount_type === 'percent') {
                                                $currentDiscount = ($originalPrice * ($promo->discount_percent ?? 0)) / 100;
                                                if ($promo->max_discount_amount) {
                                                    $currentDiscount = min($currentDiscount, $promo->max_discount_amount);
                                                }
                                            } else {
                                                $currentDiscount = min($promo->discount_amount ?? 0, $originalPrice);
                                            }

                                            // Ưu tiên khuyến mãi cho mức giảm tiền nhiều nhất (giống service-list)
                                            if ($currentDiscount > $discount) {
                                                $discount = $currentDiscount;
                                                $promotion = $promo;
                                            }
                                        }
                                    }

                                    $finalPrice = max(0, $originalPrice - $discount);

                                    return [
                                        'originalPrice' => $originalPrice,
                                        'discount' => $discount,
                                        'finalPrice' => $finalPrice > 0 ? $finalPrice : $originalPrice,
                                        'promotion' => $promotion
                                    ];
                                }

                                $hasAnyService = request('service_id') || request('service_variants') || request('combo_id');

                                // Collect all selected items với giá đã giảm
                                $allSelectedItems = [];
                                $totalOriginalPrice = 0;
                                $totalPrice = 0; // Tổng giá sau khi giảm
                                $totalDiscount = 0;
                                $totalDuration = 0;
                                $totalCount = 0;

                                // Get active promotions for automatic discount calculation
                                $activePromotions = $activePromotions ?? collect();
                                
                                // Get services
                                if (request('service_id')) {
                                    $serviceIds = is_array(request('service_id')) ? request('service_id') : [request('service_id')];
                                    $selectedServices = \App\Models\Service::whereIn('id', $serviceIds)->get();
                                    foreach ($selectedServices as $service) {
                                        $serviceDiscount = calculateDiscountForItemInCreate($service, 'service', $activePromotions);
                                        $allSelectedItems[] = [
                                            'name' => $service->name,
                                            'price' => $serviceDiscount['finalPrice'],
                                            'originalPrice' => $serviceDiscount['originalPrice'],
                                            'discount' => $serviceDiscount['discount'],
                                            'duration' => $service->base_duration ?? 60,
                                            'type' => 'service',
                                            'id' => $service->id
                                        ];
                                        $totalOriginalPrice += $serviceDiscount['originalPrice'];
                                        $totalPrice += $serviceDiscount['finalPrice'];
                                        $totalDiscount += $serviceDiscount['discount'];
                                        $totalDuration += $service->base_duration ?? 60;
                                        $totalCount++;
                                    }
                                }

                                // Get variants
                                if (request('service_variants')) {
                                    $variantIds = is_array(request('service_variants')) ? request('service_variants') : [request('service_variants')];
                                    $selectedVariants = \App\Models\ServiceVariant::whereIn('id', $variantIds)->with('service')->get();
                                    foreach ($selectedVariants as $variant) {
                                        $name = $variant->service ? $variant->service->name . ' - ' . $variant->name : $variant->name;
                                        $variantDiscount = calculateDiscountForItemInCreate($variant, 'variant', $activePromotions);
                                        $allSelectedItems[] = [
                                            'name' => $name,
                                            'price' => $variantDiscount['finalPrice'],
                                            'originalPrice' => $variantDiscount['originalPrice'],
                                            'discount' => $variantDiscount['discount'],
                                            'duration' => $variant->duration ?? 60,
                                            'type' => 'variant',
                                            'id' => $variant->id,
                                            'parent_service_id' => $variant->service_id ?? null
                                        ];
                                        $totalOriginalPrice += $variantDiscount['originalPrice'];
                                        $totalPrice += $variantDiscount['finalPrice'];
                                        $totalDiscount += $variantDiscount['discount'];
                                        $totalDuration += $variant->duration ?? 60;
                                        $totalCount++;
                                    }
                                }

                                // Get combos
                                if (request('combo_id')) {
                                    $comboIds = is_array(request('combo_id')) ? request('combo_id') : [request('combo_id')];
                                    $selectedCombos = \App\Models\Combo::whereIn('id', $comboIds)->get();
                                    foreach ($selectedCombos as $combo) {
                                        // Use duration from combo if available, otherwise calculate from combo items
                                        $comboDuration = $combo->duration ?? 60;
                                        if (!$comboDuration && $combo->comboItems && $combo->comboItems->count() > 0) {
                                            $comboDuration = $combo->comboItems->sum(function($item) {
                                                return $item->serviceVariant->duration ?? 60;
                                            });
                                        }

                                        $comboDiscount = calculateDiscountForItemInCreate($combo, 'combo', $activePromotions);
                                        $allSelectedItems[] = [
                                            'name' => $combo->name,
                                            'price' => $comboDiscount['finalPrice'],
                                            'originalPrice' => $comboDiscount['originalPrice'],
                                            'discount' => $comboDiscount['discount'],
                                            'duration' => $comboDuration,
                                            'type' => 'combo',
                                            'id' => $combo->id
                                        ];
                                        $totalOriginalPrice += $comboDiscount['originalPrice'];
                                        $totalPrice += $comboDiscount['finalPrice'];
                                        $totalDiscount += $comboDiscount['discount'];
                                        $totalDuration += $comboDuration;
                                        $totalCount++;
                                    }
                                }

                                $formattedTotalPrice = number_format($totalOriginalPrice, 0, ',', '.');
                                $formattedDiscountAmount = number_format($totalDiscount, 0, ',', '.');
                                $formattedFinalPrice = number_format($totalPrice, 0, ',', '.');
                                @endphp

                            @if($hasAnyService && count($allSelectedItems) > 0)
                                <!-- Header với icon và số lượng -->
                                <div style="background: #f8f9fa; padding: 12px 16px; border-radius: 8px 8px 0 0; display: flex; align-items: center; justify-content: space-between; border: 1px solid #e0e0e0; border-bottom: none;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fa fa-scissors" style="color: #000; font-size: 16px;"></i>
                                        <span style="color: #000; font-size: 14px; font-weight: 600;">Đã chọn {{ $totalCount }} dịch vụ</span>
                                                    </div>
                                    <i class="fa fa-chevron-right" style="color: #000; font-size: 12px;"></i>
                                                    </div>

                                <!-- Danh sách dịch vụ dạng tags -->
                                <div style="background: #fff; padding: 12px 16px; border-left: 1px solid #e0e0e0; border-right: 1px solid #e0e0e0; display: flex; flex-wrap: wrap; gap: 8px;">
                                    @foreach($allSelectedItems as $item)
                                        <div style="background: #f0f0f0; border: 1px solid #e0e0e0; border-radius: 20px; padding: 8px 14px; display: inline-block; font-size: 13px; color: #333;">
                                            {{ $item['name'] }}
                                                    </div>
                                                            @endforeach
                                                    </div>

                                <!-- Tổng số tiền -->
                                <div style="background: #fff; padding: 12px 16px; border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 8px 8px;">
                                    <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                        @if($totalDiscount > 0)
                                            <div style="font-size: 11px; color: #666; line-height: 1.3; margin-bottom: 2px;">Tổng tiền: {{ $formattedTotalPrice }} VNĐ</div>
                                            <div style="font-size: 11px; color: #28a745; line-height: 1.3; margin-bottom: 2px;">Giảm giá: -{{ $formattedDiscountAmount }} VNĐ</div>
                                        @endif
                                        <div style="font-size: 11px; color: #666; line-height: 1.3; margin-bottom: 1px;">Tổng thanh toán</div>
                                        <div style="font-size: 20px; font-weight: 700; color: #000; line-height: 1.2;" id="total_price_display">{{ $formattedFinalPrice }} VNĐ</div>
                                    </div>
                                </div>

                                <!-- Hidden input để lưu total duration cho JavaScript -->
                                <input type="hidden" id="total_duration_minutes" value="{{ $totalDuration }}">

                                <!-- Nút chọn thêm dịch vụ -->
                                <a href="{{ route('site.appointment.select-services', request()->except(['remove_service_id', 'remove_variant_id', 'remove_combo_id'])) }}"
                                   class="btn w-100 select-services-link"
                                   style="background: #fff; border: 1px solid #0066cc; color: #0066cc; padding: 12px; font-size: 14px; font-weight: 600; border-radius: 8px; text-decoration: none; display: inline-block; text-align: center; margin-top: 12px;">
                                    <i class="fa fa-plus-circle" style="margin-right: 8px;"></i> Chọn thêm dịch vụ ({{ $totalCount }})
                                </a>
                            @else
                                @php
                                    // Truyền lại employee_id, appointment_date, word_time_id nếu có
                                    $selectServicesParams = [];
                                    if (request('employee_id')) {
                                        $selectServicesParams['employee_id'] = request('employee_id');
                                    }
                                    if (request('appointment_date')) {
                                        $selectServicesParams['appointment_date'] = request('appointment_date');
                                    }
                                    if (request('word_time_id')) {
                                        $selectServicesParams['word_time_id'] = request('word_time_id');
                                    }
                                @endphp
                                <a href="{{ route('site.appointment.select-services', $selectServicesParams) }}" class="btn btn-primary w-100 select-services-link" style="background: #000; border: 1px solid #000; color: #fff; padding: 12px; font-size: 14px; font-weight: 600; border-radius: 8px; text-decoration: none; display: inline-block; text-align: center;">
                                    <i class="fa fa-plus-circle" style="margin-right: 8px;"></i> Chọn dịch vụ
                                </a>
                            @endif

                            <div class="field-error" id="service-error" style="display: none; color: #dc3545; font-size: 11px; margin-top: 4px;">
                                <i class="fa fa-exclamation-circle"></i> <span></span>
                            </div>
                            <style>
                                .btn-primary:hover {
                                    background: linear-gradient(135deg, #d8b26a 0%, #8b5a2b 100%) !important;
                                    color: #fff !important;
                                    border: 1px solid #8b5a2b !important;
                                }
                            </style>
                        </div>

                        <!-- Thời gian -->
                        <div class="mb-2" style="margin-top: 15px;">
                            <h5 class="fw-semibold mb-2" style="background: linear-gradient(135deg, #d8b26a 0%, #8b5a2b 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 14px; margin-bottom: 8px;">
                                3. Chọn ngày, giờ và stylist
                            </h5>

                            <!-- Kỹ thuật viên -->
                            <div class="mb-2" style="margin-top: 10px;">
                                <label class="form-label" style="font-size: 12px; margin-bottom: 5px; font-weight: 500; display: flex; align-items: center; gap: 6px; cursor: pointer;" id="employeeToggleBtn">
                                    <i class="fa fa-scissors"></i>
                                    <span>KỸ THUẬT VIÊN <span class="text-danger">*</span></span>
                                    <i class="fa fa-chevron-down employee-chevron" style="font-size: 12px; color: #999; transition: transform 0.3s ease; margin-left: auto;"></i>
                                </label>

                                <!-- Hidden input để lưu employee_id -->
                                <input type="hidden" name="employee_id" id="employee_id" value="{{ old('employee_id', request('employee_id')) }}">

                                <!-- Container hiển thị nhân viên giống time slot -->
                                <div class="employee-container" id="employeeContainer" style="position: relative; display: none; margin-top: 10px;">
                                    <button type="button" class="employee-nav-btn employee-nav-prev" style="position: absolute; left: -35px; top: 50%; transform: translateY(-50%); background: #000; color: #fff; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa fa-chevron-left"></i>
                                    </button>
                                    <div id="employee_grid" class="employee-grid" style="overflow: hidden; padding: 5px 0;">
                                        <div class="employee-slider" style="transition: transform 0.3s ease; display: flex; gap: 15px;">
                                            @if(count($employees) > 0)
                                                @foreach($employees as $employee)
                                                    <div class="employee-item-btn{{ old('employee_id') == $employee->id ? ' selected' : '' }}" data-employee-id="{{ $employee->id }}" data-employee-name="{{ $employee->user->name }}" data-employee-position="{{ $employee->position ?? '' }}" style="text-align: center; cursor: pointer; padding: 10px; min-width: 120px; flex-shrink: 0;">
                                                        <div class="employee-avatar-wrapper" style="width: 100px; height: 100px; margin: 0 auto 8px; border-radius: 50%; overflow: hidden; border: 2px solid {{ old('employee_id') == $employee->id ? '#007bff' : '#ddd' }};">
                                                            @if($employee->avatar)
                                                                <img src="{{ asset('legacy/images/avatars/' . $employee->avatar) }}" alt="{{ $employee->user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                            @else
                                                                <div style="width: 100%; height: 100%; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                                                    <i class="fa fa-user" style="font-size: 40px; color: #999;"></i>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="employee-name" style="font-size: 13px; font-weight: 600; color: #000; margin-bottom: 3px;">{{ $employee->user->name }}</div>
                                                    {{-- Đã bỏ phần hiển thị chức vụ --}}
                                                    </div>
                                                @endforeach
                                            @else
                                                <div style="text-align: center; padding: 20px; color: #999; width: 100%;">Vui lòng chọn dịch vụ trước để hiển thị kỹ thuật viên phù hợp</div>
                                            @endif
                                        </div>
                                    </div>
                                    <button type="button" class="employee-nav-btn employee-nav-next" style="position: absolute; right: -35px; top: 50%; transform: translateY(-50%); background: #000; color: #fff; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa fa-chevron-right"></i>
                                    </button>
                                </div>
                                <div class="field-error" id="employee-error" style="display: none; color: #dc3545; font-size: 11px; margin-top: 4px;">
                                    <i class="fa fa-exclamation-circle"></i> <span></span>
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label" style="font-size: 12px; margin-bottom: 5px; font-weight: 500;">
                                    <i class="fa fa-calendar"></i> Ngày đặt lịch <span class="text-danger">*</span>
                                </label>
                                <input type="date"
                                       name="appointment_date"
                                       id="appointment_date"
                                       class="form-control"
                                       style="font-size: 12px; padding: 8px 12px; height: 38px; border: 1px solid #ddd; border-radius: 6px;"
                                       value="{{ old('appointment_date') }}"
                                       min="{{ \Carbon\Carbon::now('Asia/Ho_Chi_Minh')->format('Y-m-d') }}"
                                       disabled>
                                <div class="field-error" id="appointment_date-error" style="display: none; color: #dc3545; font-size: 10px; margin-top: 3px;">
                                    <i class="fa fa-exclamation-circle"></i> <span></span>
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label" style="font-size: 13px; margin-bottom: 8px; font-weight: 500;">
                                    <i class="fa fa-clock-o"></i> Chọn giờ <span class="text-danger">*</span>
                                </label>
                                <!-- Hiển thị ước tính thời gian hoàn thành -->
                                <div id="estimated_completion_time" style="display: none; background: #e7f3ff; border: 1px solid #0066cc; border-radius: 6px; padding: 10px 12px; margin-bottom: 10px;">
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                        <i class="fa fa-clock-o" style="color: #0066cc; font-size: 14px;"></i>
                                        <span style="color: #000; font-size: 13px; font-weight: 500;">
                                            Bắt đầu: <strong id="start_time_display" style="color: #0066cc;">-</strong>
                                        </span>
                                        <span style="color: #666; font-size: 13px;">→</span>
                                        <span style="color: #000; font-size: 13px; font-weight: 500;">
                                            Ước tính hoàn thành: <strong id="end_time_display" style="color: #28a745; font-size: 14px;">-</strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="time-slot-container" style="position: relative; display: none;">
                                    <button type="button" class="time-slot-nav-btn time-slot-prev" style="position: absolute; left: -35px; top: 50%; transform: translateY(-50%); background: #000; color: #fff; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa fa-chevron-left"></i>
                                    </button>
                                    <div id="time_slot_grid" class="time-slot-grid">
                                        <div class="time-slot-slider">
                                            <!-- Time slots will be rendered here -->
                                        </div>
                                    </div>
                                    <button type="button" class="time-slot-nav-btn time-slot-next" style="position: absolute; right: -35px; top: 50%; transform: translateY(-50%); background: #000; color: #fff; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa fa-chevron-right"></i>
                                    </button>
                                </div>
                                <div id="time_slot_message" class="text-muted" style="padding: 8px; color: #666; font-size: 13px;">
                                    Vui lòng chọn kỹ thuật viên trước
                                </div>
                                <input type="hidden" name="time_slot" id="time_slot" value="">
                                <input type="hidden" name="word_time_id" id="word_time_id" value="">
                                <div class="field-error" id="time_slot-error" style="display: none; color: #dc3545; font-size: 11px; margin-top: 4px;">
                                    <i class="fa fa-exclamation-circle"></i> <span></span>
                                </div>
                            </div>
                        </div>

                        <!-- Ghi chú -->
                        <div class="mb-3" style="margin-top: 15px;">
                            <label class="form-label" style="font-size: 12px; margin-bottom: 5px; font-weight: 500;">
                                <i class="fa fa-comment-o"></i> Ghi chú
                            </label>
                            <textarea name="note" class="form-control" style="font-size: 12px; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; min-height: 60px;" rows="2" placeholder="Nhập ghi chú (tùy chọn)">{{ old('note') }}</textarea>
                        </div>

                        <!-- Submit -->
                        <div class="text-center mt-3" style="margin-top: 20px;">
                            <button type="submit" class="btn btn-primary px-4 py-2 submit-appointment-btn" style="background: #000; border: none; font-size: 13px; font-weight: 600; min-width: 160px; color: #fff; transition: all 0.3s ease; border-radius: 8px; height: 40px;">
                                <i class="fa fa-calendar-check-o"></i> GỬI ĐẶT LỊCH
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Ẩn tất cả thông báo lỗi tổng hợp */
    .alert-danger:not(.field-error),
    .alert-warning:not(.field-error),
    .validation-error-alert,
    .alert.alert-danger ul,
    .alert.alert-danger li {
        display: none !important;
    }


    .appointment-form-container {
        animation: fadeIn 0.5s ease-in;
        margin-left: auto !important;
        margin-right: auto !important;
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

    .form-label {
        display: block;
        font-weight: 500;
        color: #000;
        margin-bottom: 5px;
        font-size: 12px;
    }

    .form-label i {
        margin-right: 5px;
        color: #000;
        font-size: 12px;
    }

    .form-control {
        font-size: 12px;
        padding: 8px 12px;
        height: 38px;
        border: 1px solid #ddd;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #000;
        box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.1);
        outline: none;
    }

    .form-control.is-invalid,
    .form-select.is-invalid {
        border-color: #dc3545;
    }

    .form-control.is-invalid:focus,
    .form-select.is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    .form-select,
    .variant-chooser,
    .variant-select,
    select.form-select {
        border: 1px solid #000 !important;
        transition: all 0.3s ease;
    }

    .form-select:hover,
    .variant-chooser:hover,
    .variant-select:hover,
    select.form-select:hover {
        border-color: #333 !important;
        background-color: #f8f9fa !important;
    }

    .form-select:focus,
    .variant-chooser:focus,
    .variant-select:focus,
    select.form-select:focus {
        border-color: #000 !important;
        background-color: #fff !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.1);
    }

    .card {
        border: none;
        border-radius: 8px;
    }

    .card-header {
        border-radius: 8px 8px 0 0 !important;
    }

    label.bg-light:hover {
        background: #fff8e1 !important;
        border-color: #4A3600 !important;
    }

    .form-check-input:checked {
        background-color: #4A3600;
        border-color: #4A3600;
    }

    /* Service Header */
    .service-header:hover {
        background: #333 !important;
    }

    .service-header.active .service-arrow {
        transform: rotate(180deg);
    }

    .service-dropdown {
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            max-height: 0;
        }
        to {
            opacity: 1;
            max-height: 500px;
        }
    }

    .service-variant-select {
        min-height: 120px;
    }

    /* Submit button hover effect - giống nút Đặt lịch ngay trên menu */
    .submit-appointment-btn {
        transition: all 0.3s ease;
    }

    .submit-appointment-btn:hover {
        background: linear-gradient(135deg, #d8b26a 0%, #8b5a2b 100%) !important;
        color: #fff !important;
        border: 1px solid #8b5a2b !important;
    }

    .submit-appointment-btn:hover i {
        color: #fff !important;
    }

    /* Fill User Info Button - giống nút Chọn dịch vụ */
    .fill-user-info-btn {
        cursor: pointer;
    }

    .fill-user-info-btn:hover {
        background: linear-gradient(135deg, #d8b26a 0%, #8b5a2b 100%) !important;
        border-color: #8b5a2b !important;
        color: #fff !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(139, 90, 43, 0.3);
    }

    /* Custom Select Box */
    .custom-select-wrapper {
        position: relative;
    }

    .custom-select-input {
        border: 1px solid #000;
        padding: 10px 15px;
        background: #000;
        cursor: pointer;
        position: relative;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .custom-select-input:hover {
        background-color: #333;
    }

    .custom-select-input.active {
        border-color: #000;
        background: #000;
    }

    .custom-select-text {
        color: #fff;
        font-size: 14px;
    }

    .custom-select-input i {
        color: #fff !important;
    }

    .custom-select-dropdown {
        position: absolute;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        z-index: 1000;
        max-height: 300px;
        overflow-y: auto;
        width: 100%;
        margin-top: 2px;
        top: 100%;
        left: 0;
    }

    .custom-select-option {
        padding: 10px 15px;
        cursor: pointer;
        color: #000;
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.2s ease;
        font-size: 14px;
    }

    .custom-select-option:last-child {
        border-bottom: none;
    }

    .custom-select-option:hover {
        background-color: #f5f5f5;
    }

    .custom-select-option.selected {
        background-color: #007bff;
        color: #fff;
    }

    .custom-select-option.selected:hover {
        background-color: #0056b3;
    }

    /* Time Slot Container */
    .time-slot-container {
        margin-top: 12px;
        padding: 0;
    }

    /* Time Slot Grid */
    .time-slot-grid {
        width: 100%;
        position: relative;
        overflow: visible;
        background: transparent;
        padding: 0;
    }

    .time-slot-slider {
        display: flex;
        gap: 0;
        width: 100%;
        transition: none;
        transform: none !important;
    }

    .time-slot-page {
        display: grid;
        grid-template-columns: repeat(11, 1fr);
        grid-template-rows: repeat(3, 1fr);
        grid-auto-flow: row;
        gap: 8px;
        width: 100%;
        min-width: 100%;
        flex-shrink: 0;
        box-sizing: border-box;
        align-items: stretch;
        justify-items: stretch;
        overflow: visible;
        margin-top: 0;
        padding: 0;
    }

    .time-slot-page > * {
        min-width: 0;
        min-height: 0;
    }

    .time-slot-nav-btn {
        transition: all 0.3s ease;
    }

    .time-slot-nav-btn:hover {
        background: linear-gradient(135deg, #d8b26a 0%, #8b5a2b 100%) !important;
        color: #fff !important;
    }

    .time-slot-nav-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .time-slot-btn {
        padding: 12px 8px;
        border: 1px solid #000;
        border-radius: 6px;
        background: #fff;
        color: #000;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
        min-width: 0;
        min-height: 0;
        width: 100%;
        height: 100%;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
        overflow: visible;
        white-space: nowrap;
        position: relative;
    }

    .time-slot-btn:hover:not(.unavailable):not(.selected) {
        background: #f8f8f8;
        border-color: #333;
    }

    .time-slot-btn.selected {
        background: linear-gradient(135deg, #d8b26a 0%, #8b5a2b 100%);
        color: #fff;
        border-color: #8b5a2b;
        font-weight: 600;
    }

    .time-slot-btn.unavailable {
        background: #e8e8e8;
        color: #b0b0b0;
        border: 1px solid #e0e0e0;
        cursor: not-allowed;
        position: relative;
    }

    .time-slot-btn.unavailable:hover {
        background: #e8e8e8;
        border-color: #e0e0e0;
        cursor: not-allowed;
        color: #b0b0b0;
    }

    /* Tooltip cho slot bị trùng lịch - SỬA: Chỉ hiển thị khi hover, tự động ẩn khi mouse leave */
    .time-slot-btn.unavailable {
        position: relative;
    }
    
    .time-slot-btn.unavailable[title]:hover::after {
        content: attr(title);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        margin-bottom: 5px;
        padding: 6px 10px;
        background: #333;
        color: #fff;
        font-size: 12px;
        white-space: nowrap;
        border-radius: 4px;
        z-index: 1000;
        pointer-events: none; /* ✅ MỚI: Ngăn tooltip chặn mouse events */
        z-index: 1000;
        pointer-events: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    .time-slot-btn.unavailable[title]:hover::before {
        content: '';
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        margin-bottom: -1px;
        border: 5px solid transparent;
        border-top-color: #333;
        z-index: 1000;
        pointer-events: none;
    }

    .time-slot-btn.empty-slot {
        visibility: hidden;
        pointer-events: none;
        border: none;
        background: transparent;
    }

    /* Employee Selector Styles */
    .employee-selector-wrapper {
        position: relative;
    }

    .employee-select-btn {
        transition: all 0.3s ease;
    }

    .employee-select-btn:hover {
        background: #f8f9fa !important;
        border-color: #333 !important;
    }

    /* Employee Container - giống time slot */
    .employee-container {
        margin-top: 10px;
        padding: 5px 0;
        overflow: visible;
    }

    .employee-grid {
        width: 100%;
        position: relative;
        overflow: hidden;
        padding: 5px 0;
    }

    .employee-slider {
        display: flex;
        gap: 15px;
        transition: transform 0.3s ease;
    }

    .employee-item-btn {
        transition: all 0.3s ease;
        border: 1px solid #ddd;
        border-radius: 8px;
        background: #fff;
    }

    .employee-item-btn:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border-color: #333;
        position: relative;
        z-index: 10;
    }

    .employee-item-btn {
        position: relative;
    }

    .employee-item-btn.selected {
        background: #fff;
        border-color: #007bff;
        border-width: 2px;
    }

    .employee-item-btn.selected .employee-name {
        color: #000;
    }

    .employee-item-btn.selected .employee-avatar-wrapper {
        border-color: #007bff;
        border-width: 2px;
    }

    .employee-nav-btn {
        transition: all 0.3s ease;
    }

    .employee-nav-btn:hover {
        background: linear-gradient(135deg, #d8b26a 0%, #8b5a2b 100%) !important;
        color: #fff !important;
    }

    .employee-nav-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .employee-carousel .owl-item {
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .employee-carousel .owl-stage-outer {
        padding: 0;
    }

    .employee-carousel .owl-stage {
        display: flex;
        align-items: center;
    }

    .employee-item {
        transition: all 0.3s ease;
        padding: 10px;
        margin: 0 auto;
        width: 100%;
        max-width: 140px;
        min-width: 120px;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .employee-avatar-wrapper {
        flex-shrink: 0;
    }

    .employee-name {
        width: 100%;
        text-align: center;
        word-wrap: break-word;
    }

    .employee-item:hover {
        transform: translateY(-5px);
    }

    .employee-item.selected {
        opacity: 0.7;
    }

    .employee-item.selected .employee-avatar-wrapper {
        border-color: #4A3600;
        border-width: 3px;
    }

    .employee-carousel .owl-nav {
        position: absolute;
        width: 100%;
        top: 50%;
        transform: translateY(-50%);
        margin-top: 0;
        pointer-events: none;
        display: flex;
        justify-content: space-between;
        padding: 0;
    }

    .employee-carousel .owl-nav button {
        pointer-events: all;
        position: relative;
        background: #000 !important;
        color: #fff !important;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        margin: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        font-size: 18px;
        border: none;
    }

    .employee-carousel .owl-nav button.owl-prev {
        left: -40px;
    }

    .employee-carousel .owl-nav button.owl-next {
        right: -40px;
    }

    .employee-carousel .owl-nav button:hover {
        background: linear-gradient(135deg, #d8b26a 0%, #8b5a2b 100%) !important;
        color: #fff !important;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .appointment-form-container {
            padding: 25px 20px !important;
        }

        .appointment-page {
            padding: 100px 0 30px !important;
        }

        .form-control {
            font-size: 12px;
            padding: 8px 12px;
            height: 38px;
        }

        .form-label {
            font-size: 13px;
            margin-bottom: 6px;
        }

        .time-slot-page {
            grid-template-columns: repeat(8, 1fr) !important;
            grid-template-rows: repeat(4, 1fr) !important;
            gap: 6px !important;
        }

        .time-slot-btn {
            padding: 10px 4px !important;
            font-size: 12px !important;
        }
    }

    @media (max-width: 480px) {
        .time-slot-page {
            grid-template-columns: repeat(6, 1fr) !important;
            grid-template-rows: repeat(5, 1fr) !important;
            gap: 5px !important;
        }

        .time-slot-btn {
            padding: 8px 3px !important;
            font-size: 11px !important;
        }
    }

</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // CRITICAL: Remove duplicate hidden inputs for service_variants, service_id, and combo_id
        // This ensures we only have one input per unique ID
        function removeDuplicateHiddenInputs(inputName) {
            const seen = new Set();
            const inputs = $(`input[name="${inputName}"]`);
            let removedCount = 0;

            inputs.each(function() {
                const val = $(this).val();
                if (val && val.trim() !== '' && val !== '0') {
                    const id = val.trim();
                    if (seen.has(id)) {
                        // Duplicate found - remove it
                        $(this).remove();
                        removedCount++;
                    } else {
                        seen.add(id);
                    }
                } else {
                    // Empty or invalid value - remove it
                    $(this).remove();
                    removedCount++;
                }
            });

            console.log('Removed duplicate inputs:', removedCount, 'for', inputName);
        }

        // CRITICAL: Get valid variants from URL and remove any inputs that don't match
        function validateHiddenInputsFromUrl() {
            // Parse URL to get service_variants
            const url = new URL(window.location.href);
            const urlVariants = url.searchParams.getAll('service_variants[]');

            // Also check for service_variants[0], service_variants[1], etc.
            const urlVariantsAlt = [];
            for (let i = 0; i < 100; i++) {
                const param = url.searchParams.get(`service_variants[${i}]`);
                if (param) {
                    urlVariantsAlt.push(param);
                } else {
                    // Continue checking, don't break on first gap (might have service_variants[0] and service_variants[2])
                    if (i > 10) break; // But stop after reasonable limit
                }
            }

            // Combine both formats and remove duplicates
            const validVariants = [...new Set([...urlVariants, ...urlVariantsAlt])].filter(v => v && v !== '0' && v !== '');

            console.log('Valid variants from URL:', validVariants);

            // CRITICAL: Remove ALL existing hidden inputs first, then recreate only valid ones
            // This ensures we don't have any stray inputs from previous renders or JavaScript
            const allInputs = $('input[name="service_variants[]"]');
            const existingValues = [];
            allInputs.each(function() {
                existingValues.push($(this).val());
            });

            // Remove all existing inputs
            allInputs.remove();

            // Recreate only valid inputs from URL
            const $form = $('#appointmentForm');
            if ($form.length) {
            validVariants.forEach(function(variantId) {
                    const $newInput = $('<input>', {
                        type: 'hidden',
                        name: 'service_variants[]',
                        value: variantId
                    });
                    // Insert after CSRF token for proper form structure
                    $form.find('input[name="_token"]').after($newInput);
                });
            }

            console.log('Recreated', validVariants.length, 'valid variant inputs');

            return validVariants;
        }

        console.log('=== Initializing appointment form ===');

        // CRITICAL: First validate inputs against URL, then remove duplicates
        const validUrlVariants = validateHiddenInputsFromUrl();

        // Remove duplicates for all three input types
        removeDuplicateHiddenInputs('service_variants[]');
        removeDuplicateHiddenInputs('service_id[]');
        removeDuplicateHiddenInputs('combo_id[]');

        console.log('Form initialization complete. Valid URL variants:', validUrlVariants);

        // Khôi phục thông tin từ localStorage khi quay lại từ trang chọn dịch vụ
        const savedFormData = localStorage.getItem('appointmentFormData');
        let restoredEmployeeId = null;
        let restoredAppointmentDate = null;

        // Kiểm tra xem user đã đăng nhập chưa
        const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};
        const currentUserName = '{{ auth()->user()->name ?? '' }}';
        const currentUserPhone = '{{ auth()->user()->phone ?? '' }}';
        const currentUserEmail = '{{ auth()->user()->email ?? '' }}';
        // Nếu user đã đăng nhập, ưu tiên thông tin từ server (không khôi phục từ localStorage)
        // Chỉ khôi phục thông tin đặt lịch (employee, date, time) từ localStorage
        if (savedFormData) {
            try {
                const formData = JSON.parse(savedFormData);

                // Chỉ khôi phục thông tin khách hàng nếu user CHƯA đăng nhập
                // Nếu user đã đăng nhập, giữ nguyên thông tin từ server (đã được render sẵn)
                if (!isLoggedIn) {
                    // User chưa đăng nhập: khôi phục từ localStorage
                    if (formData.name) {
                        $('#name').val(formData.name);
                    }
                    if (formData.phone) {
                        $('#phone').val(formData.phone);
                    }
                    if (formData.email) {
                        $('#email').val(formData.email);
                    }
                } else {
                    // User đã đăng nhập: xóa thông tin khách hàng cũ trong localStorage để tránh nhầm lẫn
                    // Chỉ giữ lại thông tin đặt lịch (employee, date, time)
                    const appointmentData = {
                        employee_id: formData.employee_id || '',
                        appointment_date: formData.appointment_date || '',
                        word_time_id: formData.word_time_id || '',
                        time_slot: formData.time_slot || ''
                    };
                    localStorage.setItem('appointmentFormData', JSON.stringify(appointmentData));
                }
                // Khôi phục note (luôn khôi phục vì không liên quan đến user)
                if (formData.note) {
                    $('textarea[name="note"]').val(formData.note);
                }
                // Khôi phục thông tin đặt lịch nếu có
                if (formData.employee_id) {
                    $('#employee_id').val(formData.employee_id);
                    restoredEmployeeId = formData.employee_id;
                    // Enable input date nếu đã có employee
                }
                
                // ✅ Tự động chọn employee từ query parameter nếu có
                const urlParams = new URLSearchParams(window.location.search);
                const employeeIdFromUrl = urlParams.get('employee_id');
                if (employeeIdFromUrl && employeeIdFromUrl !== '' && employeeIdFromUrl !== '0') {
                    $('#employee_id').val(employeeIdFromUrl);
                    restoredEmployeeId = employeeIdFromUrl;
                    // Enable input date nếu đã có employee
                }

                // ✅ Ưu tiên restore từ Session (nếu có)
                // Session được ưu tiên vì nó persist qua redirect, còn localStorage có thể bị mất
                @if(isset($savedDate) && isset($savedWordTimeId))
                    const savedDate = '{{ $savedDate }}';
                    const savedWordTimeId = '{{ $savedWordTimeId }}';
                    
                    if (savedDate) {
                        $('#appointment_date').val(savedDate);
                        restoredAppointmentDate = savedDate;
                    }
                    
                    if (savedWordTimeId) {
                        $('#word_time_id').val(savedWordTimeId);
                    }
                @endif

                // Fallback: restore từ localStorage nếu không có Session
                if (formData.appointment_date) {
                    // Chỉ set nếu chưa có từ Session
                    if (!$('#appointment_date').val()) {
                        $('#appointment_date').val(formData.appointment_date);
                        restoredAppointmentDate = formData.appointment_date;
                    }
                }
                if (formData.word_time_id) {
                    // Chỉ set nếu chưa có từ Session
                    if (!$('#word_time_id').val()) {
                        $('#word_time_id').val(formData.word_time_id);
                    }
                }
                if (formData.time_slot) {
                    $('#time_slot').val(formData.time_slot);
                }

                // QUAN TRỌNG: Sau khi khôi phục, reload time slots nếu đã có employee, date và total_duration
                // Điều này đảm bảo khi quay lại từ trang select-services, time slots được load lại với total_duration mới
                if (restoredEmployeeId && restoredAppointmentDate) {
                    const totalDuration = parseInt($('#total_duration_minutes').val()) || 0;
                    if (totalDuration > 0) {
                        // Delay một chút để đảm bảo DOM đã sẵn sàng
                        setTimeout(function() {
                            loadAvailableTimeSlots();
                        }, 300);
                    }
                }
            } catch (e) {
                console.error('Error restoring form data from localStorage:', e);
            }
        }

        // Lưu thông tin form vào localStorage trước khi chuyển trang chọn dịch vụ
        $('.select-services-link').on('click', function(e) {
            const formData = {
                employee_id: $('#employee_id').val() || '',
                appointment_date: $('#appointment_date').val() || '',
                word_time_id: $('#word_time_id').val() || '',
                time_slot: $('#time_slot').val() || '',
                note: $('textarea[name="note"]').val() || ''
            };

            // Chỉ lưu thông tin khách hàng nếu user CHƯA đăng nhập
            if (!isLoggedIn) {
                formData.name = $('#name').val() || '';
                formData.phone = $('#phone').val() || '';
                formData.email = $('#email').val() || '';
            }

            localStorage.setItem('appointmentFormData', JSON.stringify(formData));
        });

        // Lưu thông tin form khi người dùng nhập (auto-save)
        // Chỉ lưu thông tin đặt lịch và note, không lưu thông tin khách hàng nếu user đã đăng nhập
        $('#name, #phone, #email, textarea[name="note"]').on('input change', function() {
            const formData = {
                employee_id: $('#employee_id').val() || '',
                appointment_date: $('#appointment_date').val() || '',
                word_time_id: $('#word_time_id').val() || '',
                time_slot: $('#time_slot').val() || '',
                note: $('textarea[name="note"]').val() || ''
            };

            // Chỉ lưu thông tin khách hàng nếu user CHƯA đăng nhập
            if (!isLoggedIn) {
                formData.name = $('#name').val() || '';
                formData.phone = $('#phone').val() || '';
                formData.email = $('#email').val() || '';
            }

            localStorage.setItem('appointmentFormData', JSON.stringify(formData));
        });

        // Lưu khi chọn employee, date, time slot
        $('#employee_id, #appointment_date, #word_time_id, #time_slot').on('change', function() {
            const formData = {
                employee_id: $('#employee_id').val() || '',
                appointment_date: $('#appointment_date').val() || '',
                word_time_id: $('#word_time_id').val() || '',
                time_slot: $('#time_slot').val() || '',
                note: $('textarea[name="note"]').val() || ''
            };

            // Chỉ lưu thông tin khách hàng nếu user CHƯA đăng nhập
            if (!isLoggedIn) {
                formData.name = $('#name').val() || '';
                formData.phone = $('#phone').val() || '';
                formData.email = $('#email').val() || '';
            }

            localStorage.setItem('appointmentFormData', JSON.stringify(formData));
        });

        // Xóa tất cả thông báo lỗi tổng hợp khi trang load
        $('.alert-danger:not(.field-error), .alert-warning:not(.field-error), .validation-error-alert').remove();

        // Clear errors khi trang load nếu đã có giá trị
        function clearErrorsIfHasValue() {
            // Clear name error nếu đã có giá trị
            if ($('#name').val() && $('#name').val().trim() !== '') {
                $('#name-error').hide();
                $('#name').removeClass('is-invalid');
            }

            // Clear phone error nếu đã có giá trị và đúng format
            const phoneValue = $('#phone').val();
            if (phoneValue && phoneValue.trim() !== '') {
                const phoneRegex = /^0\d{9}$/;
                if (phoneRegex.test(phoneValue.trim())) {
                    $('#phone-error').hide();
                    $('#phone').removeClass('is-invalid');
                }
            }

            // Clear email error nếu đã có giá trị và đúng format
            const emailValue = $('#email').val();
            if (emailValue && emailValue.trim() !== '') {
                if (emailValue.trim().includes('@')) {
                    $('#email-error').hide();
                    $('#email').removeClass('is-invalid');
                }
            } else {
                // Email là tùy chọn, nếu trống thì clear error
                $('#email-error').hide();
                $('#email').removeClass('is-invalid');
            }

            // Clear employee error nếu đã có giá trị
            const employeeId = $('#employee_id').val();
            if (employeeId && employeeId !== '' && employeeId !== '0') {
                $('#employee-error').hide();
                $('#employeeToggleBtn').css('color', '');
            }

            // Clear appointment date error nếu đã có giá trị
            const appointmentDate = $('#appointment_date').val();
            if (appointmentDate && appointmentDate.trim() !== '') {
                $('#appointment_date-error').hide();
                $('#appointment_date').removeClass('is-invalid');
            }

            // Clear time slot error nếu đã có giá trị
            const wordTimeId = $('#word_time_id').val();
            if (wordTimeId && wordTimeId !== '' && wordTimeId !== '0') {
                $('#time_slot-error').hide();
            }
        }

        // Chạy ngay khi trang load
        clearErrorsIfHasValue();

        // Chạy lại sau một chút để đảm bảo tất cả giá trị đã được set
        setTimeout(clearErrorsIfHasValue, 500);
        setTimeout(clearErrorsIfHasValue, 1000);

        // Theo dõi và tự động clear errors nếu đã có giá trị (chạy định kỳ)
        setInterval(function() {
            // Clear errors nếu đã có giá trị
            clearErrorsIfHasValue();

            // Xóa thông báo lỗi tổng hợp mới được thêm vào
            $('.alert-danger:not(.field-error), .alert-warning:not(.field-error), .validation-error-alert').each(function() {
                if (!$(this).hasClass('field-error') && !$(this).closest('.field-error').length) {
                    $(this).remove();
                }
            });
        }, 500);
        // Set min date to today (theo timezone Việt Nam)
        const now = new Date();
        // Lấy timezone offset của Việt Nam (UTC+7)
        const vietnamOffset = 7 * 60; // 7 giờ * 60 phút
        const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
        const vietnamTime = new Date(utc + (vietnamOffset * 60000));
        const today = vietnamTime.toISOString().split('T')[0];
        $('#appointment_date').attr('min', today);

        // Kiểm tra và disable input ngày nếu chưa chọn kỹ thuật viên khi trang load
        const initialEmployeeId = $('#employee_id').val();
        if (!initialEmployeeId || initialEmployeeId === '') {
            $('#appointment_date').prop('disabled', true);
        } else {
            // Nếu đã có employee_id, enable input date
            $('#appointment_date').prop('disabled', false);
        }

        // QUAN TRỌNG: Sau khi trang load xong, reload time slots nếu đã có đủ thông tin
        // Điều này đảm bảo khi quay lại từ trang select-services, time slots được load lại với total_duration mới
        setTimeout(function() {
            const employeeId = $('#employee_id').val();
            const appointmentDate = $('#appointment_date').val();
            const totalDuration = parseInt($('#total_duration_minutes').val()) || 0;

            if (employeeId && appointmentDate && totalDuration > 0) {
                loadAvailableTimeSlots();
            }
        }, 500);

        // ✅ Đã sửa: Không tự động mở dropdown, chỉ mở khi user click
        // Container sẽ được mở khi user click vào toggle button
        
        // Load employees by service on page load
            loadEmployeesByService();
            loadEmployeesForCarousel();

        // Nếu đã khôi phục employee_id và appointment_date từ localStorage, load time slots
        if (restoredEmployeeId && restoredAppointmentDate) {
            // Đợi một chút để đảm bảo employees đã load xong
        setTimeout(function() {
                loadAvailableTimeSlots();
        }, 500);
        }

        // Function to load employees by service (for select dropdown - not used anymore but kept for compatibility)
        function loadEmployeesByService() {
            const serviceIds = [];
            $('input[name="service_id[]"]').each(function() {
                if ($(this).val()) {
                    serviceIds.push($(this).val());
                }
            });

            const serviceVariants = [];
            $('input[name="service_variants[]"]').each(function() {
                if ($(this).val()) {
                    serviceVariants.push($(this).val());
                }
            });

            const comboIds = [];
            $('input[name="combo_id[]"]').each(function() {
                if ($(this).val()) {
                    comboIds.push($(this).val());
                }
            });

            // Only load if there's a service selected
            if (serviceIds.length === 0 && serviceVariants.length === 0 && comboIds.length === 0) {
                return;
            }

            $.ajax({
                url: '{{ route("site.appointment.employees-by-service") }}',
                method: 'GET',
                data: {
                    service_id: serviceIds,
                    service_variants: serviceVariants,
                    combo_id: comboIds
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success && response.employees) {
                        const $select = $('#employee_id');
                        const currentValue = $select.val();

                        // Clear existing options except the first one
                        $select.find('option:not(:first)').remove();

                        // Add new options
                        if (response.employees.length > 0) {
                            response.employees.forEach(function(employee) {
                                const $option = $('<option></option>')
                                    .attr('value', employee.id)
                                    .text(employee.display_name);

                                if (currentValue == employee.id) {
                                    $option.attr('selected', 'selected');
                                }

                                $select.append($option);
                            });
                        } else {
                            // No employees found
                            $select.append($('<option></option>').text('Không có kỹ thuật viên nào có chuyên môn phù hợp'));
                        }
                    }
                },
                error: function(xhr) {
                    console.error('Error loading employees:', xhr);
                }
            });
        }

        // Function to load employees for slider
        function loadEmployeesForCarousel() {
            const serviceIds = [];
            $('input[name="service_id[]"]').each(function() {
                if ($(this).val()) {
                    serviceIds.push($(this).val());
                }
            });

            const serviceVariants = [];
            $('input[name="service_variants[]"]').each(function() {
                if ($(this).val()) {
                    serviceVariants.push($(this).val());
                }
            });

            const comboIds = [];
            $('input[name="combo_id[]"]').each(function() {
                if ($(this).val()) {
                    comboIds.push($(this).val());
                }
            });

            // ✅ Kiểm tra xem có employee_id trong URL không
            const urlParams = new URLSearchParams(window.location.search);
            const employeeIdFromUrl = urlParams.get('employee_id');
            const hasEmployeeIdInUrl = employeeIdFromUrl && employeeIdFromUrl !== '' && employeeIdFromUrl !== '0';

            // Kiểm tra xem có dịch vụ nào được chọn không
            // Nếu không có service nhưng có employee_id trong URL, vẫn load employees
            if (serviceIds.length === 0 && serviceVariants.length === 0 && comboIds.length === 0 && !hasEmployeeIdInUrl) {
                const $slider = $('.employee-slider');
                $slider.empty();
                $slider.append('<div style="text-align: center; padding: 20px; color: #999; width: 100%;">Vui lòng chọn dịch vụ trước để hiển thị kỹ thuật viên phù hợp</div>');
                
                // Không reset employee_id nếu có trong URL
                if (!hasEmployeeIdInUrl) {
                    $('#employee_id').val('');
                }
                return;
            }

            const currentEmployeeId = $('#employee_id').val();

            $.ajax({
                url: '{{ route("site.appointment.employees-by-service") }}',
                method: 'GET',
                data: {
                    service_id: serviceIds,
                    service_variants: serviceVariants,
                    combo_id: comboIds
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success && response.employees) {
                        const $slider = $('.employee-slider');
                        $slider.empty();

                        // Thêm employees vào slider
                        if (response.employees.length > 0) {
                            response.employees.forEach(function(employee) {
                                const avatarUrl = employee.avatar ? '{{ asset("legacy/images/avatars") }}/' + employee.avatar : '';
                                const isSelected = currentEmployeeId == employee.id;

                                let itemHtml = '<div class="employee-item-btn' + (isSelected ? ' selected' : '') + '" data-employee-id="' + employee.id + '" data-employee-name="' + employee.name + '" data-employee-position="' + (employee.position || '') + '" style="text-align: center; cursor: pointer; padding: 10px; min-width: 120px; flex-shrink: 0;">';
                                itemHtml += '<div class="employee-avatar-wrapper" style="width: 100px; height: 100px; margin: 0 auto 8px; border-radius: 50%; overflow: hidden; border: 2px solid ' + (isSelected ? '#007bff' : '#ddd') + ';">';

                                if (avatarUrl) {
                                    itemHtml += '<img src="' + avatarUrl + '" alt="' + employee.name + '" style="width: 100%; height: 100%; object-fit: cover;">';
                                } else {
                                    itemHtml += '<div style="width: 100%; height: 100%; background: #f0f0f0; display: flex; align-items: center; justify-content: center;"><i class="fa fa-user" style="font-size: 40px; color: #999;"></i></div>';
                                }

                                itemHtml += '</div>';
                                itemHtml += '<div class="employee-name" style="font-size: 13px; font-weight: 600; color: #000; margin-bottom: 3px;">' + employee.name + '</div>';

                                // Đã bỏ phần hiển thị chức vụ

                                itemHtml += '</div>';
                                $slider.append(itemHtml);
                            });

                            // ✅ Đã sửa: Không tự động mở dropdown sau khi load employees
                            // Container chỉ mở khi user click vào toggle button

                            console.log('Employees loaded for carousel:', response.employees.length);

                            // ✅ Tự động chọn employee từ URL (chỉ khi có trong URL, không tự chọn ngẫu nhiên)
                            const urlParams = new URLSearchParams(window.location.search);
                            const employeeIdFromUrl = urlParams.get('employee_id');
                            
                            // Chỉ tự động chọn nếu có employee_id trong URL (không tự chọn ngẫu nhiên)
                            if (employeeIdFromUrl && employeeIdFromUrl !== '' && employeeIdFromUrl !== '0') {
                                // Đợi một chút để DOM được render xong
                                setTimeout(function() {
                                    const selectedEmployee = $('.employee-item-btn[data-employee-id="' + employeeIdFromUrl + '"]');
                                    if (selectedEmployee.length) {
                                        // Set hidden input
                                        $('#employee_id').val(employeeIdFromUrl);
                                        
                                        // Highlight employee
                                        $('.employee-item-btn').removeClass('selected');
                                        $('.employee-item-btn .employee-avatar-wrapper').css('border-color', '#ddd');
                                        selectedEmployee.addClass('selected');
                                        selectedEmployee.find('.employee-avatar-wrapper').css('border-color', '#007bff');
                                        
                                        // Enable date input
                                        $('#appointment_date').prop('disabled', false);
                                        
                                        // Trigger change để load time slots nếu đã có date
                                        $('#employee_id').trigger('change');
                                    }
                                }, 100);
                            } else if (currentEmployeeId && currentEmployeeId !== '') {
                                // Chỉ tự động chọn nếu đã có giá trị từ trước (từ localStorage hoặc old input)
                                // Không tự động chọn giá trị rỗng
                                setTimeout(function() {
                                    const selectedEmployee = $('.employee-item-btn[data-employee-id="' + currentEmployeeId + '"]');
                                    if (selectedEmployee.length) {
                                        // Highlight employee
                                        $('.employee-item-btn').removeClass('selected');
                                        $('.employee-item-btn .employee-avatar-wrapper').css('border-color', '#ddd');
                                        selectedEmployee.addClass('selected');
                                        selectedEmployee.find('.employee-avatar-wrapper').css('border-color', '#007bff');
                                    }
                                }, 100);
                            }

                            // Nếu employee đã chọn không còn trong danh sách, reset
                            if (currentEmployeeId && !response.employees.find(e => e.id == currentEmployeeId)) {
                                $('#employee_id').val('');
                            }
                        } else {
                            // Không có nhân viên phù hợp
                            $slider.append('<div style="text-align: center; padding: 20px; color: #999; width: 100%;">Không có kỹ thuật viên nào có chuyên môn phù hợp với dịch vụ đã chọn</div>');
                        }
                    }
                },
                error: function(xhr) {
                    const $slider = $('.employee-slider');
                    $slider.empty();
                    $slider.append('<div style="text-align: center; padding: 20px; color: #dc3545; width: 100%;">Có lỗi xảy ra khi tải danh sách kỹ thuật viên</div>');
                }
            });
        }

        // Employee Selector - Toggle container
        // Đảm bảo event handler được gắn đúng cách và hoạt động
        function toggleEmployeeContainer() {
            const container = $('#employeeContainer');
            const chevron = $('.employee-chevron');
            
            // Kiểm tra trạng thái hiện tại của container
            const isVisible = container.is(':visible') && container.css('display') !== 'none';
            
            console.log('Toggling employee container. Current state:', isVisible);
            
            if (isVisible) {
                // Đóng container
                container.slideUp(300, function() {
                    chevron.css('transform', 'rotate(0deg)');
                });
            } else {
                // Mở container
                // Luôn load employees khi mở container (để đảm bảo có dữ liệu mới nhất)
                console.log('Opening employee container, loading employees...');
                loadEmployeesForCarousel();
                
                // Mở container ngay lập tức
                container.slideDown(300, function() {
                    chevron.css('transform', 'rotate(180deg)');
                    console.log('Employee container opened');
                });
            }
        }
        
        // Gắn event handler với nhiều cách để đảm bảo hoạt động
        $(document).ready(function() {
            // Cách 1: Gắn trực tiếp vào element
            $('#employeeToggleBtn').off('click.employeeToggle').on('click.employeeToggle', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                toggleEmployeeContainer();
                return false;
            });
        });
        
        // Cách 2: Gắn với document.on (backup)
        $(document).off('click', '#employeeToggleBtn').on('click', '#employeeToggleBtn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleEmployeeContainer();
            return false;
        });


        // ✅ Tự động load employees nếu có employee_id trong URL (ngay cả khi chưa có service)
        // Điều này cho phép chọn employee trước khi chọn service
        const urlParams = new URLSearchParams(window.location.search);
        const employeeIdFromUrl = urlParams.get('employee_id');
        if (employeeIdFromUrl && employeeIdFromUrl !== '' && employeeIdFromUrl !== '0') {
            // Set employee_id vào hidden input
            $('#employee_id').val(employeeIdFromUrl);
            // Enable date input
            $('#appointment_date').prop('disabled', false);
            // Load employees để hiển thị và tự động chọn
            // Note: loadEmployeesForCarousel sẽ tự động chọn employee này sau khi load xong
        }

        // Xử lý chọn employee - đặt priority cao để chạy trước document click
        // Sử dụng $(document) để đảm bảo event handler hoạt động ngay cả khi container chưa tồn tại
        $(document).on('click', '.employee-item-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            const employeeId = $(this).data('employee-id');
            const employeeName = $(this).data('employee-name');


            if (!employeeId) {
                return false;
            }

            // Cập nhật hidden input
            $('#employee_id').val(employeeId);

            console.log('Employee selected:', {
                id: employeeId,
                name: employeeName
            });

            // Xóa selected của tất cả items
            $('.employee-item-btn').removeClass('selected');
            $('.employee-item-btn .employee-avatar-wrapper').css('border-color', '#ddd');

            // Thêm selected cho item được chọn
            $(this).addClass('selected');
            $(this).find('.employee-avatar-wrapper').css('border-color', '#007bff');

            // Clear error và remove invalid class
            $('#employee-error').hide();
            $('#employeeToggleBtn').css('color', '');

            // Trigger change event để load time slots nếu đã chọn ngày
            $('#employee_id').trigger('change');

            // Đảm bảo container vẫn mở
            $('#employeeContainer').show();

            return false;
        });

        // Navigation buttons cho employee slider
        $('#employeeContainer').on('click', '.employee-nav-prev', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            const $slider = $('.employee-slider');
            const containerWidth = $('.employee-grid').width();
            const currentTransform = $slider.css('transform');

            let currentX = 0;
            if (currentTransform && currentTransform !== 'none') {
                const matrix = currentTransform.match(/matrix\(([^)]+)\)/);
                if (matrix) {
                    currentX = parseFloat(matrix[1].split(',')[4]) || 0;
                }
            }

            const newX = Math.min(0, currentX + containerWidth);
            $slider.css('transform', 'translateX(' + newX + 'px)');

            return false;
        });

        $('#employeeContainer').on('click', '.employee-nav-next', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            const $slider = $('.employee-slider');
            const $container = $('.employee-grid');
            const containerWidth = $container.width();
            const sliderWidth = $slider[0].scrollWidth;
            const currentTransform = $slider.css('transform');

            let currentX = 0;
            if (currentTransform && currentTransform !== 'none') {
                const matrix = currentTransform.match(/matrix\(([^)]+)\)/);
                if (matrix) {
                    currentX = parseFloat(matrix[1].split(',')[4]) || 0;
                }
            }

            const maxX = -(sliderWidth - containerWidth);
            const newX = Math.max(maxX, currentX - containerWidth);
            $slider.css('transform', 'translateX(' + newX + 'px)');

            return false;
        });

        // Clear error when user starts typing/selecting (clear ngay khi có giá trị)
        $('input[name="name"]').on('input keyup change paste', function() {
            const value = $(this).val();
            if (value && value.trim().length > 0) {
                $('#name-error').hide();
                $(this).removeClass('is-invalid');
            }
        });

        // Clear error khi đang nhập (không validate real-time)
        $('input[name="phone"]').on('input keyup change paste', function() {
            // Chỉ clear error khi đang nhập, không validate
            const value = $(this).val();
            if (value && value.trim().length > 0) {
                $('#phone-error').hide();
                $(this).removeClass('is-invalid');
            }
        });

        // Validate phone khi blur (rời khỏi ô input)
        $('input[name="phone"]').on('blur', function() {
            const value = $(this).val();
            const phoneTrimmed = value ? String(value).trim() : '';
            
            if (phoneTrimmed && phoneTrimmed.length > 0) {
                // Validate format: phải đủ 10 số và bắt đầu bằng số 0
                const phoneRegex = /^0\d{9}$/;
                if (!phoneRegex.test(phoneTrimmed)) {
                    // Hiển thị lỗi nếu format không đúng
                    showFieldError('phone', 'số điện thoại không đúng');
                } else {
                    // Clear error nếu đúng format
                    $('#phone-error').hide();
                    $(this).removeClass('is-invalid');
                }
            }
        });

        // Clear error khi đang nhập (không validate real-time)
        $('#email').on('input keyup change paste', function() {
            // Chỉ clear error khi đang nhập, không validate
            const value = $(this).val();
            if (value && value.trim().length > 0) {
                $('#email-error').hide();
                $(this).removeClass('is-invalid');
            } else {
                // Nếu trống, chỉ ẩn lỗi (vì email là tùy chọn)
                $('#email-error').hide();
                $(this).removeClass('is-invalid');
            }
        });

        // Validate email khi blur (rời khỏi ô input)
        $('#email').on('blur', function() {
            const value = $(this).val();
            const emailTrimmed = value ? String(value).trim() : '';
            
            if (emailTrimmed && emailTrimmed.length > 0) {
                // Kiểm tra email có chứa ký tự @
                if (!emailTrimmed.includes('@')) {
                    showFieldError('email', 'email không đúng định dạng');
                } else {
                    // Clear error nếu đúng format
                    $('#email-error').hide();
                    $(this).removeClass('is-invalid');
                }
            }
        });

        // Clear error khi focus vào input (nếu đã có giá trị)
        $('input[name="name"], input[name="phone"], #email').on('focus', function() {
            const value = $(this).val();
            if (value && value.trim().length > 0) {
                const fieldName = $(this).attr('name') || $(this).attr('id');
                if (fieldName) {
                    $('#' + fieldName + '-error').hide();
                    $(this).removeClass('is-invalid');
                }
            }
        });

        $('#employee_id').on('change', function() {
            const employeeId = $(this).val();
            const $appointmentDate = $('#appointment_date');

            if (employeeId && employeeId !== '') {
                // Enable input ngày khi đã chọn kỹ thuật viên
                $('#employee-error').hide();
                $(this).removeClass('is-invalid');
                $appointmentDate.prop('disabled', false);

                // Load time slots nếu đã chọn ngày
                if ($appointmentDate.val()) {
                    loadAvailableTimeSlots();
                } else {
                    // Reset và hiển thị thông báo
                    $('.time-slot-container').hide();
                    $('#time_slot_message').text('Vui lòng chọn ngày trước').show();
                    $('#time_slot').val('');
                    $('#word_time_id').val('');
                }
            } else {
                // Disable input ngày và reset khi bỏ chọn kỹ thuật viên
                $appointmentDate.prop('disabled', true).val('').removeClass('is-invalid');
                $('#appointment_date-error').hide();

                // Reset time slots
                $('.time-slot-container').hide();
                $('#time_slot_message').text('Vui lòng chọn kỹ thuật viên trước').show();
                $('#time_slot').val('');
                $('#word_time_id').val('');
            }
        });

        $('#appointment_date').on('change input', function() {
            const dateValue = $(this).val();
            if (dateValue && dateValue.trim() !== '') {
                $('#appointment_date-error').hide();
                $(this).removeClass('is-invalid');
                // Chỉ load time slots nếu đã chọn kỹ thuật viên
                const currentEmployeeId = $('#employee_id').val();
                if (currentEmployeeId && currentEmployeeId !== '') {
                    loadAvailableTimeSlots();
                }
            } else {
                // Nếu xóa date, hiển thị error
                $('#appointment_date-error').show();
                $(this).addClass('is-invalid');
            }
        });

        // Clear service error when service is selected (check on page load only)
        function checkAndClearServiceError() {
            // Kiểm tra service_id[] (array)
            const serviceIds = [];
            $('input[name="service_id[]"]').each(function() {
                if ($(this).val()) {
                    serviceIds.push($(this).val());
                }
            });

            // Kiểm tra service_variants[] (array)
            const serviceVariants = [];
            $('input[name="service_variants[]"]').each(function() {
                if ($(this).val()) {
                    serviceVariants.push($(this).val());
                }
            });

            // Kiểm tra combo_id[] (array)
            const comboIds = [];
            $('input[name="combo_id[]"]').each(function() {
                if ($(this).val()) {
                    comboIds.push($(this).val());
                }
            });

            if (serviceIds.length > 0 || serviceVariants.length > 0 || comboIds.length > 0) {
                $('#service-error').hide();
            }
        }

        // Check on page load only (not continuously)
        checkAndClearServiceError();

        // Reload time slots khi total_duration thay đổi (khi chọn/xóa dịch vụ)
        // Sử dụng MutationObserver để theo dõi thay đổi của input total_duration_minutes
        const totalDurationInput = document.getElementById('total_duration_minutes');
        if (totalDurationInput) {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                        // Khi value thay đổi, reload time slots nếu đã chọn employee và date
                        const employeeId = $('#employee_id').val();
                        const appointmentDate = $('#appointment_date').val();
                        if (employeeId && appointmentDate) {
                            loadAvailableTimeSlots();
                        }
                    }
                });
            });

            observer.observe(totalDurationInput, {
                attributes: true,
                attributeFilter: ['value']
            });

            // Cũng theo dõi thay đổi giá trị trực tiếp (khi set bằng JavaScript)
            let lastValue = totalDurationInput.value;
            setInterval(function() {
                const currentValue = totalDurationInput.value;
                if (currentValue !== lastValue) {
                    lastValue = currentValue;
                    const employeeId = $('#employee_id').val();
                    const appointmentDate = $('#appointment_date').val();
                    if (employeeId && appointmentDate) {
                        loadAvailableTimeSlots();
                    }
                }
            }, 500);
        }

        // Clear time slot error when a time slot is selected
        $(document).on('click', '.time-slot-btn:not(.unavailable)', function() {
            $('#time_slot-error').hide();
        });

        // Format time from HH:MM to HHhMM
        function formatTimeSlot(time) {
            return time.replace(':', 'h');
        }

        // Load available time slots when employee or date changes
        // Debounce để tránh nhiều request đồng thời gây flicker
        let loadTimeSlotsTimeout = null;
        let isLoadingTimeSlots = false;

        function loadAvailableTimeSlots() {
            // Clear timeout nếu có
            if (loadTimeSlotsTimeout) {
                clearTimeout(loadTimeSlotsTimeout);
            }

            // BỎ QUA kiểm tra isLoadingTimeSlots để không chặn các request hợp lệ
            // Chỉ dùng debounce với timeout thay vì chặn hoàn toàn

            const employeeId = $('#employee_id').val();
            const appointmentDate = $('#appointment_date').val();
            const timeSlotGrid = $('#time_slot_grid');
            const timeSlotMessage = $('#time_slot_message');
            const timeSlotHidden = $('#time_slot');
            const wordTimeIdInput = $('#word_time_id');

            // ✅ LƯU GIỜ ĐÃ CHỌN TRƯỚC KHI RESET
            // Điều này đảm bảo khi thêm dịch vụ, giờ đã chọn sẽ được giữ lại
            const savedSelectedTime = timeSlotHidden.val();
            const savedWordTimeId = wordTimeIdInput.val();

            // Reset
            $('.time-slot-container').hide();
            $('.time-slot-slider').empty();
            timeSlotMessage.show();
            // KHÔNG reset các giá trị ở đây - sẽ restore sau khi load xong

            // Kiểm tra employee_id trước khi load time slots

            // Check if date is selected
            if (!appointmentDate) {
                timeSlotMessage.text('Vui lòng chọn ngày trước');
                return;
            }

            // Show loading
            timeSlotMessage.text('Đang tải khung giờ...');

            // Lấy tổng thời gian dịch vụ đã chọn
            let totalDuration = parseInt($('#total_duration_minutes').val()) || 0;

            // Nếu không có total_duration, tính từ các dịch vụ đã chọn
            if (totalDuration === 0) {
                // Tính từ service variants
                let duration = 0;
                $('input[name="service_variants[]"]').each(function() {
                    const variantId = $(this).val();
                    if (variantId) {
                        // Lấy duration từ data attribute hoặc default 60
                        const variantDuration = $(this).data('duration') || 60;
                        duration += variantDuration;
                    }
                });

                // Tính từ services
                $('input[name="service_id[]"]').each(function() {
                    const serviceId = $(this).val();
                    if (serviceId) {
                        const serviceDuration = $(this).data('duration') || 60;
                        duration += serviceDuration;
                    }
                });

                // Tính từ combos
                $('input[name="combo_id[]"]').each(function() {
                    const comboId = $(this).val();
                    if (comboId) {
                        const comboDuration = $(this).data('duration') || 60;
                        duration += comboDuration;
                    }
                });

                if (duration > 0) {
                    totalDuration = duration;
                }
            }

            // Debug log để kiểm tra total_duration
            console.log('Loading time slots:', {
                employeeId: employeeId,
                appointmentDate: appointmentDate,
                totalDuration: totalDuration
            });

            // Đánh dấu đang load và set timeout để reset nếu request bị treo
            isLoadingTimeSlots = true;
            const loadingTimeout = setTimeout(function() {
                if (isLoadingTimeSlots) {
                    isLoadingTimeSlots = false;
                }
            }, 10000); // Reset sau 10 giây nếu request không hoàn thành

            // Load time slots via AJAX
            $.ajax({
                url: '{{ route("site.appointment.available-time-slots") }}',
                method: 'GET',
                data: {
                    employee_id: employeeId || '',
                    appointment_date: appointmentDate,
                    total_duration: totalDuration
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout: 8000, // Timeout sau 8 giây
                success: function(response) {
                    // Clear timeout và reset flag sau khi nhận response
                    clearTimeout(loadingTimeout);
                    isLoadingTimeSlots = false;

                    console.log('Time slots loaded:', {
                        success: response.success,
                        slotsCount: response.time_slots ? response.time_slots.length : 0,
                        completedAppointmentEndTime: response.completed_appointment_end_time
                    });

                    // LUÔN hiển thị tất cả slots từ 7h-22h (30 slots), kể cả khi không có lịch làm việc
                    if (response.success && response.time_slots && response.time_slots.length > 0) {
                        const currentlySelectedTime = timeSlotHidden.val();
                        let availableCount = 0;

                        // Sort time slots by time
                        const sortedSlots = response.time_slots.sort(function(a, b) {
                            return a.time.localeCompare(b.time);
                        });

                        const $slider = $('.time-slot-slider');
                        $slider.empty();

                        // Layout responsive: Desktop 11 cột x 3 hàng, Mobile tự động điều chỉnh
                        // Hiển thị tất cả slots từ 7h-22h (30 slots), còn 3 slots trống ở cuối
                        const isMobile = window.innerWidth <= 768;
                        const isSmallMobile = window.innerWidth <= 480;

                        let fixedColumns, fixedRows;
                        if (isSmallMobile) {
                            fixedColumns = 6;
                            fixedRows = 5;
                        } else if (isMobile) {
                            fixedColumns = 8;
                            fixedRows = 4;
                        } else {
                            fixedColumns = 11;
                            fixedRows = 3;
                        }

                        const totalSlots = fixedColumns * fixedRows;
                        const slotsPerPage = totalSlots;

                        // Cập nhật CSS grid động dựa trên kích thước màn hình
                        $('#dynamic-time-slot-style').remove();
                        $('<style>').prop('id', 'dynamic-time-slot-style').html(
                            '.time-slot-page { grid-template-columns: repeat(' + fixedColumns + ', 1fr) !important; grid-template-rows: repeat(' + fixedRows + ', 1fr) !important; }'
                        ).appendTo('head');

                        // Tạo một page duy nhất với layout 11 cột x 3 hàng
                        const currentPage = $('<div></div>').addClass('time-slot-page');
                        $slider.append(currentPage);

                        // Hàm so sánh thời gian (HH:MM format)
                        function compareTime(time1, time2) {
                            const [h1, m1] = time1.split(':').map(Number);
                            const [h2, m2] = time2.split(':').map(Number);
                            const total1 = h1 * 60 + m1;
                            const total2 = h2 * 60 + m2;
                            return total1 - total2;
                        }

                        // Kiểm tra xem có đơn đã hoàn thành không và lấy thời gian kết thúc
                        // Nếu có, đánh dấu các slot <= thời gian kết thúc đơn là unavailable (không ẩn, chỉ gray out)
                        let completedAppointmentEndTime = null;
                        if (response.completed_appointment_end_time) {
                            completedAppointmentEndTime = response.completed_appointment_end_time; // Format: "10:00"
                        }

                        // Tạo danh sách đầy đủ 30 slots từ 7h-22h (30 phút một slot)
                        const allTimeSlots = [];
                        for (let hour = 7; hour <= 22; hour++) {
                            for (let minute = 0; minute < 60; minute += 30) {
                                if (hour === 22 && minute > 0) break; // Dừng ở 22h00
                                const timeString = String(hour).padStart(2, '0') + ':' + String(minute).padStart(2, '0');
                                allTimeSlots.push(timeString);
                            }
                        }

                        // Merge slots từ backend với danh sách đầy đủ
                        const completeSlots = allTimeSlots.map(function(timeString) {
                            const existingSlot = sortedSlots.find(s => s.time === timeString);
                            if (existingSlot) {
                                // Sử dụng slot từ backend (giữ nguyên available từ backend)
                                return existingSlot;
                            } else {
                                // Tạo slot mới nếu chưa có (backend không trả về slot này)
                                // KHÔNG set conflict_reason mặc định vì có thể slot này nằm trong ca làm việc
                                // nhưng bị unavailable vì lý do khác (quá khứ, đã đặt, v.v.)
                                return {
                                    time: timeString,
                                    display: timeString,
                                    word_time_id: null,
                                    available: false,
                                    conflict_reason: null // Không set mặc định, để backend xử lý
                                };
                            }
                        });

                        // Đánh dấu các slot < completed appointment end time là unavailable
                        // QUAN TRỌNG: KHÔNG override available nếu slot = completedAppointmentEndTime
                        // (vì backend đã xử lý và set available = true cho slot này)
                        // Ví dụ: Dịch vụ hoàn thành lúc 8h00 → slot 8h00 phải available (backend đã set)
                        // Chỉ đánh dấu unavailable các slot < 8h00 (7h00, 7h30)
                        completeSlots.forEach(function(slot) {
                            // Chỉ xử lý nếu slot < completedAppointmentEndTime
                            // KHÔNG xử lý nếu slot = completedAppointmentEndTime (để giữ nguyên available từ backend)
                            if (completedAppointmentEndTime && compareTime(slot.time, completedAppointmentEndTime) < 0) {
                                slot.available = false;
                                if (!slot.conflict_reason) {
                                    slot.conflict_reason = 'Đã qua thời gian';
                                }
                            }

                        });

                        // Đếm số slot đã render để debug
                        let renderedSlotCount = 0;

                        // Render tất cả 30 slots - KHÔNG ẨN, CHỈ GRAY OUT
                        completeSlots.forEach(function(slot) {
                            // QUAN TRỌNG: KHÔNG đánh dấu slot = completedAppointmentEndTime là unavailable
                            // Chỉ đánh dấu unavailable các slot < completedAppointmentEndTime
                            // Ví dụ: Đơn hoàn thành lúc 8h00 → chỉ đánh dấu 7h, 7h30 là unavailable
                            // Slot 8h00 PHẢI available (có thể bắt đầu ngay sau khi kết thúc)
                            // Logic này đã được xử lý ở trên (dòng 2144), không cần xử lý lại ở đây

                            // Kiểm tra available chính xác: phải là true (không phải false, null, undefined)
                            const isAvailable = slot.available === true;
                            const formattedTime = formatTimeSlot(slot.time);
                            const isSelected = currentlySelectedTime === slot.time;


                            const btn = $('<button></button>')
                                .attr('type', 'button')
                                .addClass('time-slot-btn')
                                .attr('data-time', slot.time)
                                .attr('data-word-time-id', slot.word_time_id)
                                .text(formattedTime);

                            if (!isAvailable) {
                                btn.addClass('unavailable');
                                // Thêm tooltip nếu có lý do trùng lịch
                                // CHỈ hiển thị tooltip nếu có conflict_reason rõ ràng từ backend (không phải null, empty, hoặc undefined)
                                if (slot.conflict_reason && typeof slot.conflict_reason === 'string' && slot.conflict_reason.trim() !== '') {
                                    btn.attr('title', slot.conflict_reason);
                                    // ✅ SỬA: Không dùng Bootstrap tooltip, chỉ dùng CSS tooltip (::after)
                                    // btn.attr('data-toggle', 'tooltip');
                                    // btn.attr('data-placement', 'top');
                                }


                                // Ngăn chặn click vào slot unavailable
                                btn.on('click', function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();

                                    if (slot.conflict_reason) {
                                        alert(slot.conflict_reason);
                                    }
                                    return false;
                                });
                                
                                // ✅ SỬA: Đảm bảo tooltip tự động ẩn khi mouse leave (CSS tooltip tự động ẩn)
                            } else {
                                availableCount++;
                                if (isSelected) {
                                    btn.addClass('selected');
                                    timeSlotHidden.val(slot.time);
                                    wordTimeIdInput.val(slot.word_time_id);
                                }
                            }

                            currentPage.append(btn);
                            renderedSlotCount++;
                        });


                        // ✅ SỬA: Không cần khởi tạo Bootstrap tooltip nữa vì đã dùng CSS tooltip (::after)
                        // CSS tooltip tự động hiển thị khi hover và ẩn khi mouse leave

                        // Thêm các empty slots ở cuối để đủ 33 slots (11 cột x 3 hàng)
                        // Lưu ý: remainingSlots phải tính dựa trên số slot đã render
                        const remainingSlots = totalSlots - renderedSlotCount;
                        for (let i = 0; i < remainingSlots; i++) {
                            const emptyBtn = $('<button></button>')
                                .attr('type', 'button')
                                .addClass('time-slot-btn empty-slot')
                                .css({
                                    'visibility': 'hidden',
                                    'pointer-events': 'none'
                                });
                            currentPage.append(emptyBtn);
                        }

                        // LUÔN hiển thị time slot container để người dùng thấy tất cả slots từ 7h-22h
                        // (kể cả các slots unavailable - sẽ được đánh dấu xám)
                        $('.time-slot-container').show();

                        // Đảm bảo slider hiển thị tất cả, không scroll
                        $('.time-slot-slider').css({
                            'width': '100%',
                            'transform': 'none'
                        });

                        // Ẩn các nút navigation vì hiển thị tất cả slots cùng lúc (11 cột x 3 hàng)
                        $('.time-slot-nav-btn').hide();

                        if (availableCount === 0) {
                            // Nếu không có slot nào available, hiển thị thông báo nhưng vẫn show container với tất cả slots
                            timeSlotMessage.text('Nhân viên này không có ca làm việc vào ngày đã chọn. Tất cả khung giờ đều không khả dụng.').show();
                        } else {
                            timeSlotMessage.hide();
                        }

                        // ✅ Restore giờ đã chọn trước đó (khi thêm dịch vụ)
                        if (currentlySelectedTime && currentlySelectedWordTimeId) {
                            setTimeout(function() {
                                const $savedBtn = $('.time-slot-btn[data-time="' + currentlySelectedTime + '"]');
                                
                                if ($savedBtn.length) {
                                    // Kiểm tra xem slot có còn available không
                                    if (!$savedBtn.hasClass('unavailable')) {
                                        // Slot vẫn available, restore lại
                                        $savedBtn.addClass('selected');
                                        timeSlotHidden.val(currentlySelectedTime);
                                        wordTimeIdInput.val(currentlySelectedWordTimeId);
                                    } else {
                                        // Slot không còn available, nhưng vẫn giữ giá trị trong hidden input
                                        timeSlotHidden.val(currentlySelectedTime);
                                        wordTimeIdInput.val(currentlySelectedWordTimeId);
                                    }
                                } else {
                                    // Không tìm thấy button, nhưng vẫn giữ giá trị
                                    timeSlotHidden.val(currentlySelectedTime);
                                    wordTimeIdInput.val(currentlySelectedWordTimeId);
                                }
                            }, 100); // Delay nhỏ để đảm bảo DOM đã render
                        }

                        // ✅ Auto-select time slot nếu đã có trong Session
                        // Điều này đảm bảo khi trang reload (do redirect), giờ đã chọn sẽ tự động được highlight lại
                        @if(isset($savedWordTimeId))
                            const savedWordTimeId = '{{ $savedWordTimeId }}';
                            const savedTimeSlot = response.time_slots.find(function(slot) {
                                return slot.word_time_id == savedWordTimeId;
                            });
                            
                            if (savedTimeSlot) {
                                // Delay một chút để đảm bảo DOM đã render xong tất cả time slot buttons
                                setTimeout(function() {
                                    const $savedBtn = $('.time-slot-btn[data-word-time-id="' + savedWordTimeId + '"]');
                                    
                                    if ($savedBtn.length) {
                                        if (!$savedBtn.hasClass('unavailable')) {
                                            // Nếu slot available, click để highlight và set đầy đủ giá trị
                                            $savedBtn.trigger('click');
                                        } else {
                                            // Nếu slot không available, vẫn set hidden input để giữ giá trị
                                            $('#word_time_id').val(savedWordTimeId);
                                            $('#time_slot').val(savedTimeSlot.time);
                                        }
                                    } else {
                                        // Nếu không tìm thấy button (có thể do slot không có trong response)
                                        // Vẫn set hidden input để giữ giá trị
                                        $('#word_time_id').val(savedWordTimeId);
                                    }
                                }, 200); // Delay 200ms để đảm bảo DOM đã render xong
                            } else {
                            }
                        @endif

                        // Không cần update navigation buttons vì đã ẩn
                        // updateNavigationButtons();
                    } else {
                        // Nếu không có time_slots từ server (lỗi), vẫn hiển thị thông báo
                        clearTimeout(loadingTimeout);
                        isLoadingTimeSlots = false; // Reset flag
                        $('.time-slot-container').hide();
                        if (employeeId) {
                            timeSlotMessage.text(response.message || 'Không thể tải khung giờ. Vui lòng thử lại.');
                        } else {
                            timeSlotMessage.text('Vui lòng chọn kỹ thuật viên và ngày trước');
                        }
                        timeSlotHidden.val('');
                        wordTimeIdInput.val('');
                    }
                },
                error: function(xhr, status, error) {
                    // Reset flag khi có lỗi
                    clearTimeout(loadingTimeout);
                    isLoadingTimeSlots = false;
                    $('.time-slot-container').hide();

                    let errorMessage = 'Không thể tải khung giờ. Vui lòng thử lại.';
                    
                    // Xử lý các loại lỗi khác nhau
                    if (xhr.status === 422) {
                        // Validation error
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            const errorMessages = [];
                            if (errors.employee_id) {
                                errorMessages.push(errors.employee_id[0]);
                            }
                            if (errors.appointment_date) {
                                errorMessages.push(errors.appointment_date[0]);
                            }
                            errorMessage = errorMessages.length > 0 ? errorMessages.join('. ') : 'Dữ liệu không hợp lệ.';
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else {
                            errorMessage = 'Dữ liệu không hợp lệ. Vui lòng kiểm tra lại thông tin đã nhập.';
                        }
                    } else if (xhr.status === 500) {
                        // Server error
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else {
                            errorMessage = 'Có lỗi xảy ra từ server. Vui lòng thử lại sau.';
                        }
                    } else if (status === 'timeout') {
                        errorMessage = 'Yêu cầu quá thời gian chờ. Vui lòng thử lại.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    timeSlotMessage.text(errorMessage).show();
                    timeSlotHidden.val('');
                    wordTimeIdInput.val('');
                }
            });
        }

        // Update navigation buttons state
        // Ẩn các nút navigation vì hiển thị tất cả slots cùng lúc (11 cột x 3 hàng)
        function updateNavigationButtons() {
            $('.time-slot-nav-btn').hide();
            // Đảm bảo slider không scroll
            $('.time-slot-slider').css('transform', 'none');
        }

        // Navigation button handlers
        $(document).on('click', '.time-slot-prev', function() {
            const $slider = $('.time-slot-slider');
            const containerWidth = $('.time-slot-container').width();
            const currentTransform = $slider.css('transform');

            let currentX = 0;
            if (currentTransform && currentTransform !== 'none') {
                const matrix = currentTransform.match(/matrix\(([^)]+)\)/);
                if (matrix) {
                    currentX = parseFloat(matrix[1].split(',')[4]) || 0;
                }
            }

            const newX = Math.min(0, currentX + containerWidth);
            $slider.css('transform', 'translateX(' + newX + 'px)');

            setTimeout(updateNavigationButtons, 300);
        });

        $(document).on('click', '.time-slot-next', function() {
            const $slider = $('.time-slot-slider');
            const $container = $('.time-slot-container');
            const containerWidth = $container.width();
            const sliderWidth = $slider[0].scrollWidth;
            const currentTransform = $slider.css('transform');

            let currentX = 0;
            if (currentTransform && currentTransform !== 'none') {
                const matrix = currentTransform.match(/matrix\(([^)]+)\)/);
                if (matrix) {
                    currentX = parseFloat(matrix[1].split(',')[4]) || 0;
                }
            }

            const maxX = -(sliderWidth - containerWidth);
            const newX = Math.max(maxX, currentX - containerWidth);
            $slider.css('transform', 'translateX(' + newX + 'px)');

            setTimeout(updateNavigationButtons, 300);
        });

        // Function to calculate and display estimated completion time
        function updateEstimatedCompletionTime() {
            const selectedTime = $('#time_slot').val();
            const totalDurationMinutes = parseInt($('#total_duration_minutes').val()) || 0;

            if (selectedTime && totalDurationMinutes > 0) {
                // Parse selected time (format: HH:MM)
                const [startHours, startMinutes] = selectedTime.split(':').map(Number);

                // Calculate end time: đơn giản cộng phút
                let totalMinutes = (startHours * 60) + startMinutes + totalDurationMinutes;

                // Tính giờ và phút kết thúc
                let endHours = Math.floor(totalMinutes / 60);
                let endMinutes = totalMinutes % 60;

                // Xử lý trường hợp vượt quá 24h (nếu cần)
                if (endHours >= 24) {
                    endHours = endHours % 24;
                }

                // Format times: 8h00, 8h30, etc. (không có số 0 phía trước giờ)
                const startTimeStr = startHours + 'h' + String(startMinutes).padStart(2, '0');
                const endTimeStr = endHours + 'h' + String(endMinutes).padStart(2, '0');

                // Update display
                $('#start_time_display').text(startTimeStr);
                $('#end_time_display').text(endTimeStr);
                $('#estimated_completion_time').show();
                            } else {
                $('#estimated_completion_time').hide();
            }
        }

        // Handle time slot button click
        $(document).on('click', '.time-slot-btn:not(.unavailable)', function() {
            // Kiểm tra xem đã chọn kỹ thuật viên chưa
            const employeeId = $('#employee_id').val();
            if (!employeeId) {
                $('#time_slot_message').text('Vui lòng chọn kỹ thuật viên trước').show();
                $('.time-slot-container').hide();
                            return false;
                        }

            // Remove previous selection
            $('.time-slot-btn').removeClass('selected');

            // Add selection to clicked button
            $(this).addClass('selected');

            // Clear time slot error
            $('#time_slot-error').hide();

            // Set hidden inputs
            const time = $(this).data('time');
            const wordTimeId = $(this).data('word-time-id');

            // Đảm bảo set giá trị đúng
                if (time) {
                    $('#time_slot').val(time);
                }
                if (wordTimeId) {
                    $('#word_time_id').val(wordTimeId);
            }

            // ✅ Lưu vào Session khi user chọn giờ
            // Điều này đảm bảo khi redirect (do thêm/xóa dịch vụ), giờ đã chọn vẫn được giữ lại
            const appointmentDate = $('#appointment_date').val();
            if (appointmentDate && wordTimeId) {
                $.ajax({
                    url: '{{ route("site.appointment.save-selected-time") }}',
                    method: 'POST',
                    data: {
                        appointment_date: appointmentDate,
                        word_time_id: wordTimeId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            console.log('✅ [Session] Đã lưu thời gian vào Session:', {
                                appointment_date: appointmentDate,
                                word_time_id: wordTimeId,
                                time: time
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('❌ [Session] Lỗi khi lưu thời gian vào Session:', xhr);
                        // Không block user nếu lưu Session thất bại, vì localStorage vẫn hoạt động
                    }
                });
            }

            // Update estimated completion time
            updateEstimatedCompletionTime();

        });


        // Flag to prevent multiple submissions
        let isSubmitting = false;

        // Clear all field errors
        function clearFieldErrors() {
            $('.field-error').hide().find('span').text('');
            $('.form-control, .form-select').removeClass('is-invalid');
            $('.selected-service-display, .selected-variants-display, .selected-combo-display').removeClass('is-invalid');
            // Clear employee error và reset màu
            $('#employee-error').hide();
            $('#employeeToggleBtn').css('color', '');
            // Clear appointment date error
            $('#appointment_date-error').hide();
            $('#appointment_date').removeClass('is-invalid');
            // Clear time slot error
            $('#time_slot-error').hide();
        }

        // Show error for a specific field
        function showFieldError(fieldId, message) {
            const $errorDiv = $('#' + fieldId + '-error');
            if ($errorDiv.length) {
                $errorDiv.find('span').text(message);
                $errorDiv.show();
            }
            
            const $field = $('#' + fieldId);
            if ($field.length) {
                $field.addClass('is-invalid');
            }
            
            // Xử lý đặc biệt cho employee
            if (fieldId === 'employee') {
                $('#employeeToggleBtn').css('color', '#dc3545');
            }
        }

        // Show error for service section
        function showServiceError(message) {
            const $errorDiv = $('#service-error');
            $errorDiv.find('span').text(message);
            $errorDiv.show();
            // Highlight service section
            $('.selected-service-display, .selected-variants-display, .selected-combo-display, .btn-primary').closest('.mb-2').find('.btn-primary').addClass('is-invalid');
        }

        // Validate form before submission
        function validateForm() {
            let isValid = true;

            // Clear previous errors TRƯỚC KHI validate
            clearFieldErrors();

            // Clear errors ngay nếu đã có giá trị (đảm bảo không hiển thị lỗi sai)
            clearErrorsIfHasValue();

            // Check name - BẮT BUỘC (chỉ hiển thị lỗi nếu thực sự trống)
            const name = $('#name').val();
            const nameTrimmed = name ? String(name).trim() : '';
            if (!nameTrimmed || nameTrimmed === '') {
                showFieldError('name', 'Vui lòng nhập họ và tên');
                isValid = false;
            } else {
                // Clear error nếu đã có giá trị
                $('#name-error').hide();
                $('#name').removeClass('is-invalid');
            }

            // Check phone - BẮT BUỘC
            const phone = $('#phone').val();
            const phoneTrimmed = phone ? String(phone).trim() : '';
            if (!phoneTrimmed || phoneTrimmed === '') {
                showFieldError('phone', 'Vui lòng nhập số điện thoại');
                isValid = false;
            } else {
                // Validate format: phải đủ 10 số và bắt đầu bằng số 0
                const phoneRegex = /^0\d{9}$/;
                if (!phoneRegex.test(phoneTrimmed)) {
                    showFieldError('phone', 'số điện thoại không đúng');
                    isValid = false;
                } else {
                    // Clear error nếu đã đúng format
                    $('#phone-error').hide();
                    $('#phone').removeClass('is-invalid');
                }
            }

            // Check email - TÙY CHỌN nhưng nếu có thì phải đúng format
            const email = $('#email').val();
            const emailTrimmed = email ? String(email).trim() : '';
            if (emailTrimmed && emailTrimmed !== '') {
                // Kiểm tra email có chứa ký tự @
                if (!emailTrimmed.includes('@')) {
                    showFieldError('email', 'email không đúng định dạng');
                    isValid = false;
                } else {
                    // Clear error nếu đã đúng format
                    $('#email-error').hide();
                    $('#email').removeClass('is-invalid');
                }
            } else {
                // Clear error nếu để trống (vì email là tùy chọn)
                $('#email-error').hide();
                $('#email').removeClass('is-invalid');
            }

            // Check service (at least one must be selected)
            // Kiểm tra service_id[] (array)
            const serviceIds = [];
            $('input[name="service_id[]"]').each(function() {
                if ($(this).val()) {
                    serviceIds.push($(this).val());
                }
            });

            // Kiểm tra service_variants[] (array)
            const serviceVariants = [];
            $('input[name="service_variants[]"]').each(function() {
                if ($(this).val()) {
                    serviceVariants.push($(this).val());
                }
            });

            // Kiểm tra combo_id[] (array)
            const comboIds = [];
            $('input[name="combo_id[]"]').each(function() {
                if ($(this).val()) {
                    comboIds.push($(this).val());
                }
            });

            // Kiểm tra xem có ít nhất một dịch vụ được chọn không
            if (serviceIds.length === 0 && serviceVariants.length === 0 && comboIds.length === 0) {
                const $errorDiv = $('#service-error');
                if ($errorDiv.length) {
                    $errorDiv.find('span').text('Mời anh chọn dịch vụ để chọn giờ cắt');
                    $errorDiv.show();
                }
                isValid = false;
            }

            // Check employee - kiểm tra kỹ hơn
            const employeeId = $('#employee_id').val();
            const employeeIdTrimmed = employeeId ? String(employeeId).trim() : '';
            const hasEmployeeId = employeeIdTrimmed && 
                                 employeeIdTrimmed !== '' && 
                                 employeeIdTrimmed !== '0' && 
                                 employeeIdTrimmed !== 'null' && 
                                 employeeIdTrimmed !== 'undefined' &&
                                 !isNaN(employeeIdTrimmed); // Phải là số

            if (!hasEmployeeId) {
                showFieldError('employee', 'Mời anh chọn kỹ thuật viên');
                isValid = false;
            } else {
                // Clear error nếu đã chọn
                $('#employee-error').hide();
                $('#employeeToggleBtn').css('color', '');
            }

            // Check appointment date - kiểm tra kỹ hơn
            const appointmentDate = $('#appointment_date').val();
            const appointmentDateTrimmed = appointmentDate ? String(appointmentDate).trim() : '';
            const hasAppointmentDate = appointmentDateTrimmed && appointmentDateTrimmed !== '';

            if (!hasAppointmentDate) {
                showFieldError('appointment_date', 'Mời anh chọn ngày đặt lịch');
                isValid = false;
            } else {
                // Clear error nếu đã chọn
                $('#appointment_date-error').hide();
                $('#appointment_date').removeClass('is-invalid');
            }

            // Check time slot - kiểm tra kỹ hơn
            const wordTimeId = $('#word_time_id').val();
            const wordTimeIdTrimmed = wordTimeId ? String(wordTimeId).trim() : '';
            const hasWordTimeId = wordTimeIdTrimmed && wordTimeIdTrimmed !== '' && wordTimeIdTrimmed !== '0' && wordTimeIdTrimmed !== 'null' && wordTimeIdTrimmed !== 'undefined';

            if (!hasWordTimeId) {
                showFieldError('time_slot', 'Mời anh chọn giờ đặt lịch');
                isValid = false;
            } else {
                // Clear error nếu đã chọn
                $('#time_slot-error').hide();
            }


            return isValid;
        }

        // Handle form submission via AJAX (remove previous listeners to prevent duplicates)
        $('#appointmentForm').off('submit').on('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            // Prevent multiple submissions - kiểm tra ngay từ đầu
            if (isSubmitting) {
                console.log('Form is already submitting, ignoring duplicate submission');
                return false;
            }

            // Kiểm tra xem đã có thông báo thành công chưa (tránh submit lại)
            if ($('.alert-success').length > 0) {
                console.log('Success message already shown, ignoring submission');
                return false;
            }

            // Remove ALL previous error messages and alerts
            $('.validation-error-alert, .alert-danger, .alert-warning').remove();

            // Get form reference
            const $form = $(this);

            // Validate form - validation will get values directly from DOM
            console.log('Starting validation...');
            const isValid = validateForm();
            console.log('Validation result:', isValid);

            if (!isValid) {
                console.log('Validation failed, showing errors');
                // Scroll to first error
                const firstError = $('.field-error:visible').first();
                if (firstError.length) {
                    $('html, body').animate({
                        scrollTop: firstError.offset().top - 100
                    }, 300);
                }

                // Prevent form submission
                isSubmitting = false;
                return false;
            }

            console.log('Validation passed, submitting form...');

            // If validation passes, continue with submission

            // Remove previous messages
            $('.success-message, .error-message, .alert-success').remove();

            // Set submitting flag NGAY LẬP TỨC để ngăn chặn submit lại
            isSubmitting = true;

            // Chuẩn bị dữ liệu TRƯỚC KHI disable form
            const formDataObj = {};

            // Lấy dữ liệu trực tiếp từ các input (trước khi disable)
            // Name - BẮT BUỘC
            const name = $('#name').val();
            const nameTrimmed = name ? String(name).trim() : '';
            if (nameTrimmed) {
                formDataObj.name = nameTrimmed;
            } else {
                // Nếu name trống, validation đã bắt lỗi ở trên, nhưng vẫn gửi để server validate lại
                formDataObj.name = '';
            }

            // Phone - BẮT BUỘC
            const phone = $('#phone').val();
            const phoneTrimmed = phone ? String(phone).trim() : '';
            if (phoneTrimmed) {
                formDataObj.phone = phoneTrimmed;
            } else {
                // Nếu phone trống, validation đã bắt lỗi ở trên, nhưng vẫn gửi để server validate lại
                formDataObj.phone = '';
            }

            // Email - TÙY CHỌN (có thể để trống)
            const email = $('#email').val();
            if (email && email.trim() !== '') {
                formDataObj.email = email.trim();
            }

            // Luôn gửi các field required để server có thể validate
            const employeeId = $('#employee_id').val();
            formDataObj.employee_id = employeeId || '';

            const appointmentDate = $('#appointment_date').val();
            formDataObj.appointment_date = appointmentDate ? appointmentDate.trim() : '';

            const wordTimeId = $('#word_time_id').val();
            formDataObj.word_time_id = wordTimeId || '';

            const note = $('textarea[name="note"]').val();
            if (note && note.trim() !== '') {
                formDataObj.note = note.trim();
            }

            // Xử lý service arrays
            // Collect service IDs and remove duplicates
            const serviceIds = [];
            const seenServiceIds = new Set(); // Use Set to track duplicates
            $('input[name="service_id[]"]').each(function() {
                const val = $(this).val();
                if (val && val.trim() !== '' && val !== '0') {
                    const serviceId = val.trim();
                    // Only add if not already seen (remove duplicates)
                    if (!seenServiceIds.has(serviceId)) {
                        seenServiceIds.add(serviceId);
                        serviceIds.push(serviceId);
                    }
                }
            });
            if (serviceIds.length > 0) {
                formDataObj.service_id = serviceIds;
            }

            // Collect service variants and remove duplicates
            const serviceVariants = [];
            const seenVariants = new Set(); // Use Set to track duplicates
            $('input[name="service_variants[]"]').each(function() {
                const val = $(this).val();
                if (val && val.trim() !== '' && val !== '0') {
                    const variantId = val.trim();
                    // Only add if not already seen (remove duplicates)
                    if (!seenVariants.has(variantId)) {
                        seenVariants.add(variantId);
                        serviceVariants.push(variantId);
                    }
                }
            });
            if (serviceVariants.length > 0) {
                formDataObj.service_variants = serviceVariants;
            }

            console.log('Service variants collected:', {
                total_inputs: $('input[name="service_variants[]"]').length,
                unique_variants: serviceVariants.length,
                variants: serviceVariants
            });

            // Collect combo IDs and remove duplicates
            const comboIds = [];
            const seenComboIds = new Set(); // Use Set to track duplicates
            $('input[name="combo_id[]"]').each(function() {
                const val = $(this).val();
                if (val && val.trim() !== '' && val !== '0') {
                    const comboId = val.trim();
                    // Only add if not already seen (remove duplicates)
                    if (!seenComboIds.has(comboId)) {
                        seenComboIds.add(comboId);
                        comboIds.push(comboId);
                    }
                }
            });
            if (comboIds.length > 0) {
                formDataObj.combo_id = comboIds;
            }


            // Disable form và submit button để ngăn chặn submit lại
            const $submitBtn = $('.submit-appointment-btn');
            const originalBtnText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Đang xử lý...');
            $form.find('input, button, select, textarea').prop('disabled', true);

            // Submit form via AJAX
            // Lấy URL từ route để đảm bảo đúng - sử dụng absolute URL
            const submitUrl = '{{ url(route("site.appointment.store")) }}';

            console.log('=== APPOINTMENT SUBMIT DEBUG ===');
            console.log('Submitting to URL:', submitUrl);
            console.log('Form data object:', formDataObj);
            console.log('Form data keys:', Object.keys(formDataObj));
            console.log('Employee ID:', formDataObj.employee_id);
            console.log('Appointment Date:', formDataObj.appointment_date);
            console.log('Word Time ID:', formDataObj.word_time_id);
            console.log('Service IDs:', formDataObj.service_id);
            console.log('Service Variants:', formDataObj.service_variants);
            console.log('Combo IDs:', formDataObj.combo_id);
            console.log('Name:', formDataObj.name);
            console.log('Phone:', formDataObj.phone);
            console.log('===============================');

            $.ajax({
                url: submitUrl,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                data: formDataObj,
                success: function(response) {
                    // Kiểm tra lại để tránh xử lý nhiều lần
                    if (isSubmitting && response.success) {
                        // Xóa dữ liệu đã lưu trong localStorage sau khi submit thành công
                        localStorage.removeItem('appointmentFormData');

                        // Remove any previous messages (kiểm tra lại)
                        $('.alert-success, .alert-danger, .alert-warning, .validation-error-alert').remove();

                        // Chỉ hiển thị thông báo nếu chưa có
                        if ($('.alert-success').length === 0) {
                            // Extract only the text message without icon
                            let messageText = response.message.replace(/<i[^>]*>.*?<\/i>/gi, '').trim();

                            // Show success message with better styling (chỉ một lần)
                            $('#appointmentForm').prepend(
                                '<div class="alert alert-success alert-dismissible fade show appointment-success-message" role="alert" style="margin-bottom: 20px; border-left: 4px solid #28a745; background-color: #d4edda; color: #155724; padding: 15px 20px; border-radius: 5px;">' +
                                messageText +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="float: right; border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>' +
                                '</div>'
                            );

                            // Scroll to top to show message
                            $('html, body').animate({ scrollTop: 0 }, 300);
                        }

                        // Prevent any further submissions - disable form hoàn toàn
                        isSubmitting = true;
                        $('#appointmentForm').off('submit');
                        $form.find('input, button, select, textarea').prop('disabled', true);

                        // Redirect to appointment success page or home
                        if (response.redirect_url) {
                            window.location.href = response.redirect_url;
                        } else if (response.appointment_id) {
                            window.location.href = '{{ route("site.appointment.success", ":id") }}'.replace(':id', response.appointment_id);
                        } else {
                            window.location.href = '{{ route("site.home") }}';
                        }
                    } else {
                        // Re-enable button if not successful
                        isSubmitting = false;
                        $submitBtn.prop('disabled', false).html(originalBtnText);
                        $form.find('input, button, select, textarea').prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    // Re-enable button on error
                    isSubmitting = false;
                    $submitBtn.prop('disabled', false).html(originalBtnText);
                    $form.find('input, button, select, textarea').prop('disabled', false);

                    console.log('AJAX Error:', xhr);
                    console.log('Request URL:', submitUrl);
                    console.log('Request Data:', formDataObj);
                    console.log('Response:', xhr.responseJSON);
                    console.log('Status:', xhr.status);
                    console.log('Status Text:', xhr.statusText);
                    console.log('Response Text:', xhr.responseText);

                    // Handle validation errors - display inline errors
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        // Clear all errors trước
                        clearFieldErrors();

                        console.log('Server validation errors:', xhr.responseJSON.errors);

                        $.each(xhr.responseJSON.errors, function(key, value) {
                            if (value && value.length > 0) {
                                console.log('Error for field:', key, 'Message:', value[0]);

                                    // Map backend field names to frontend field IDs
                                    let fieldId = key;
                                    if (key === 'employee_id') fieldId = 'employee';
                                    if (key === 'appointment_date') fieldId = 'appointment_date';
                                    if (key === 'time_slot' || key === 'word_time_id') fieldId = 'time_slot';
                                    if (key === 'service_id' || key === 'service_id.*') fieldId = 'service';

                                    // Hiển thị lỗi
                                    const $errorDiv = $('#' + fieldId + '-error');
                                    if ($errorDiv.length) {
                                        $errorDiv.find('span').text(value[0]);
                                        $errorDiv.show();
                                    } else {
                                        // Nếu không tìm thấy error div, thử showFieldError
                                        showFieldError(fieldId, value[0]);
                                    }

                                    // Thêm invalid class cho field
                                    const $field = $('#' + fieldId);
                                    if ($field.length) {
                                        $field.addClass('is-invalid');
                                }
                            }
                        });
                        
                        // ✅ SỬA: Nếu có message từ server và không có errors array, hiển thị ở time_slot-error
                        if (xhr.responseJSON.message && (!xhr.responseJSON.errors || Object.keys(xhr.responseJSON.errors).length === 0)) {
                            const errorMessage = xhr.responseJSON.message;
                            if (errorMessage.includes('khung giờ') || errorMessage.includes('nhân viên rảnh')) {
                                const $timeSlotError = $('#time_slot-error');
                                if ($timeSlotError.length) {
                                    $timeSlotError.find('span').text(errorMessage);
                                    $timeSlotError.show();
                                }
                            }
                        }

                        // Scroll to first error
                        const firstError = $('.field-error:visible').first();
                        if (firstError.length) {
                            $('html, body').animate({
                                scrollTop: firstError.offset().top - 100
                            }, 300);
                        } else {
                            // Nếu không có error nào hiển thị, hiển thị alert với tất cả lỗi
                            let errorMessages = [];
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                if (value && value.length > 0) {
                                    errorMessages.push(value[0]);
                                }
                            });
                            if (errorMessages.length > 0) {
                                alert('Có lỗi xảy ra:\n' + errorMessages.join('\n'));
                            }
                        }
                    } else {
                        // ✅ SỬA: Kiểm tra nếu có message từ server (không phải errors array)
                        // Hiển thị thông báo đỏ ở dưới phần chọn khung giờ thay vì alert
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            const errorMessage = xhr.responseJSON.message;
                            
                            // Kiểm tra nếu message liên quan đến time slot
                            if (errorMessage.includes('khung giờ') || errorMessage.includes('nhân viên rảnh')) {
                                // Hiển thị thông báo đỏ ở dưới phần chọn khung giờ
                                const $timeSlotError = $('#time_slot-error');
                                if ($timeSlotError.length) {
                                    $timeSlotError.find('span').text(errorMessage);
                                    $timeSlotError.show();
                                    
                                    // Scroll đến phần chọn khung giờ
                                    $('html, body').animate({
                                        scrollTop: $timeSlotError.offset().top - 100
                                    }, 300);
                                } else {
                                    // Fallback: hiển thị alert nếu không tìm thấy error div
                                    alert(errorMessage);
                                    $('html, body').animate({ scrollTop: 0 }, 300);
                                }
                            } else {
                                // Nếu không phải lỗi time slot, hiển thị alert như cũ
                                alert(errorMessage);
                                $('html, body').animate({ scrollTop: 0 }, 300);
                            }
                        } else {
                            // Nếu không có errors từ server, có thể là lỗi khác
                            console.error('Unexpected error:', xhr);
                            let errorMessage = 'Có lỗi xảy ra khi đặt lịch. Vui lòng thử lại.';

                            // Kiểm tra các loại lỗi khác nhau
                            if (xhr.responseJSON) {
                                if (xhr.responseJSON.error) {
                                    errorMessage = xhr.responseJSON.error;
                                }
                            } else if (xhr.status === 0) {
                                errorMessage = 'Không thể kết nối đến server. Vui lòng kiểm tra kết nối internet.';
                            } else if (xhr.status === 500) {
                                errorMessage = 'Lỗi server. Vui lòng thử lại sau.';
                            } else if (xhr.status === 404) {
                                errorMessage = 'Không tìm thấy trang. Vui lòng thử lại.';
                            }

                            // Hiển thị alert và scroll to top
                            alert(errorMessage);
                            $('html, body').animate({ scrollTop: 0 }, 300);
                        }
                    }
                }
            });
        });

    });
</script>
@endpush

