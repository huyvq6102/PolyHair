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
                        <h2 class="fw-bold mb-1" style="color: #000; font-size: 18px; margin-bottom: 4px;">
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

                    <form action="{{ route('site.appointment.store') }}" method="POST" id="appointmentForm" novalidate>
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

                        @if(request('promotion_id'))
                            <input type="hidden" name="promotion_id" value="{{ request('promotion_id') }}">
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
                                <h5 class="fw-semibold mb-0" style="color: #000; font-size: 14px;">
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
                                       class="form-control"
                                       style="font-size: 12px; padding: 8px 12px; height: 38px; border: 1px solid #ddd; border-radius: 6px;"
                                       placeholder="Email (tùy chọn)"
                                       value="{{ old('email', auth()->user()->email ?? '') }}">
                            </div>
                        </div>

                        <!-- Chọn dịch vụ -->
                        <div class="mb-2" style="margin-top: 15px;">
                            <h5 class="fw-semibold mb-2" style="color: #000; font-size: 14px; margin-bottom: 8px;">
                                2. DỊCH VỤ <span class="text-danger">*</span>
                            </h5>

                            @php
                                $hasAnyService = request('service_id') || request('service_variants') || request('combo_id');
                                
                                // Load selected promotion if exists
                                $selectedPromotion = null;
                                if (request('promotion_id') && request('promotion_id')) {
                                    $selectedPromotion = \App\Models\Promotion::where('id', request('promotion_id'))
                                        ->where('status', 'active')
                                        ->whereNull('deleted_at')
                                        ->first();
                                }
                                
                                // Collect all selected items
                                $allSelectedItems = [];
                                $totalPrice = 0;
                                $totalCount = 0;
                                
                                // Debug arrays to track what's being parsed
                                $debugServiceIds = [];
                                $debugVariantIds = [];
                                $debugComboIds = [];
                                
                                // Parse URL params manually to handle all formats (same logic as select-services page)
                                $url = request()->fullUrl();
                                $parsedUrl = parse_url($url);
                                $queryParams = [];
                                if (isset($parsedUrl['query'])) {
                                    parse_str($parsedUrl['query'], $queryParams);
                                }
                                
                                // Get services - parse all formats
                                $serviceIds = [];
                                $queryServices = [];
                                
                                // Try request()->query() first
                                if (request('service_id')) {
                                    $queryServices = request()->query('service_id', []);
                                    if (!is_array($queryServices)) {
                                        $queryServices = $queryServices ? [$queryServices] : [];
                                    }
                                }
                                
                                // Also check parsed URL params for service_id[] format
                                if (isset($queryParams['service_id'])) {
                                    if (is_array($queryParams['service_id'])) {
                                        $queryServices = array_merge($queryServices, $queryParams['service_id']);
                                    } else {
                                        $queryServices[] = $queryParams['service_id'];
                                    }
                                }
                                
                                // Check for indexed format service_id[0], etc.
                                foreach ($queryParams as $key => $value) {
                                    if (preg_match('/^service_id\[(\d+)\]$/', $key, $matches)) {
                                        $queryServices[] = $value;
                                    }
                                }
                                
                                $serviceIds = array_filter($queryServices, function($id) {
                                    return !empty($id) && $id !== '0' && $id !== 0 && is_numeric($id);
                                });
                                $serviceIds = array_values(array_unique($serviceIds));
                                
                                if (!empty($serviceIds)) {
                                    $selectedServices = \App\Models\Service::whereIn('id', $serviceIds)->get();
                                    foreach ($selectedServices as $service) {
                                        $allSelectedItems[] = [
                                            'name' => $service->name,
                                            'price' => $service->base_price ?? 0,
                                            'type' => 'service',
                                            'id' => $service->id
                                        ];
                                        $totalPrice += $service->base_price ?? 0;
                                        $totalCount++;
                                    }
                                }
                                
                                // Get variants - parse from URL to handle service_variants[] format
                                $variantIds = [];
                                if (request()->has('service_variants')) {
                                    $queryVariants = [];
                                    
                                    // Check for service_variants[] format
                                    if (isset($queryParams['service_variants']) && is_array($queryParams['service_variants'])) {
                                        $queryVariants = $queryParams['service_variants'];
                                    } elseif (isset($queryParams['service_variants'])) {
                                        $queryVariants = [$queryParams['service_variants']];
                                    }
                                    
                                    // Check for service_variants[0], service_variants[1], etc. format
                                    foreach ($queryParams as $key => $value) {
                                        if (preg_match('/^service_variants\[(\d+)\]$/', $key, $matches)) {
                                            $queryVariants[] = $value;
                                        }
                                    }
                                    
                                    $variantIds = array_filter($queryVariants, function($id) {
                                        return !empty($id) && $id !== '0' && $id !== 0 && is_numeric($id);
                                    });
                                    $variantIds = array_values(array_unique($variantIds));
                                    $debugVariantIds = $variantIds; // For debugging
                                }
                                
                                if (!empty($variantIds)) {
                                    $selectedVariants = \App\Models\ServiceVariant::whereIn('id', $variantIds)->with('service')->get();
                                    foreach ($selectedVariants as $variant) {
                                        $name = $variant->service ? $variant->service->name . ' - ' . $variant->name : $variant->name;
                                        $allSelectedItems[] = [
                                            'name' => $name,
                                            'price' => $variant->price ?? 0,
                                            'type' => 'variant',
                                            'id' => $variant->id
                                        ];
                                        $totalPrice += $variant->price ?? 0;
                                        $totalCount++;
                                    }
                                }
                                
                                // Get combos - parse all formats
                                $comboIds = [];
                                $queryCombos = [];
                                
                                // Try request()->query() first
                                if (request('combo_id')) {
                                    $queryCombos = request()->query('combo_id', []);
                                    if (!is_array($queryCombos)) {
                                        $queryCombos = $queryCombos ? [$queryCombos] : [];
                                    }
                                }
                                
                                // Also check parsed URL params for combo_id[] format
                                if (isset($queryParams['combo_id'])) {
                                    if (is_array($queryParams['combo_id'])) {
                                        $queryCombos = array_merge($queryCombos, $queryParams['combo_id']);
                                    } else {
                                        $queryCombos[] = $queryParams['combo_id'];
                                    }
                                }
                                
                                // Check for indexed format combo_id[0], etc.
                                foreach ($queryParams as $key => $value) {
                                    if (preg_match('/^combo_id\[(\d+)\]$/', $key, $matches)) {
                                        $queryCombos[] = $value;
                                    }
                                }
                                
                                $comboIds = array_filter($queryCombos, function($id) {
                                    return !empty($id) && $id !== '0' && $id !== 0 && is_numeric($id);
                                });
                                $comboIds = array_values(array_unique($comboIds));
                                
                                if (!empty($comboIds)) {
                                    $selectedCombos = \App\Models\Combo::whereIn('id', $comboIds)->get();
                                    foreach ($selectedCombos as $combo) {
                                        $allSelectedItems[] = [
                                            'name' => $combo->name,
                                            'price' => $combo->price ?? 0,
                                            'type' => 'combo',
                                            'id' => $combo->id
                                        ];
                                        $totalPrice += $combo->price ?? 0;
                                        $totalCount++;
                                    }
                                }
                                
                                // Load selected promotion if exists and validate it applies to selected services
                                $selectedPromotion = null;
                                if (request('promotion_id') && request('promotion_id')) {
                                    $promotion = \App\Models\Promotion::where('id', request('promotion_id'))
                                        ->where('status', 'active')
                                        ->whereNull('deleted_at')
                                        ->with(['services', 'combos', 'serviceVariants'])
                                        ->first();
                                    
                                    if ($promotion) {
                                        // Get selected IDs for validation
                                        $selectedServiceIds = array_map(function($item) {
                                            return $item['id'];
                                        }, array_filter($allSelectedItems, function($item) {
                                            return $item['type'] === 'service';
                                        }));
                                        $selectedVariantIds = array_map(function($item) {
                                            return $item['id'];
                                        }, array_filter($allSelectedItems, function($item) {
                                            return $item['type'] === 'variant';
                                        }));
                                        $selectedComboIds = array_map(function($item) {
                                            return $item['id'];
                                        }, array_filter($allSelectedItems, function($item) {
                                            return $item['type'] === 'combo';
                                        }));
                                        
                                        // Validate promotion applies to selected services
                                        // If promotion has apply_scope = 'order', it applies to all
                                        if ($promotion->apply_scope === 'order') {
                                            $selectedPromotion = $promotion;
                                        } elseif ($promotion->apply_scope === 'service') {
                                            $hasSpecificServices = $promotion->services->count() > 0 
                                                || $promotion->combos->count() > 0 
                                                || $promotion->serviceVariants->count() > 0;
                                            
                                            // If no specific services, applies to all
                                            if (!$hasSpecificServices) {
                                                $selectedPromotion = $promotion;
                                            } else {
                                                // Check if any selected service/variant/combo matches
                                                $hasMatch = false;
                                                
                                                // Check services
                                                foreach ($selectedServiceIds as $serviceId) {
                                                    if ($promotion->services->contains('id', $serviceId)) {
                                                        $hasMatch = true;
                                                        break;
                                                    }
                                                }
                                                
                                                // Check variants
                                                if (!$hasMatch) {
                                                    foreach ($selectedVariantIds as $variantId) {
                                                        if ($promotion->serviceVariants->contains('id', $variantId)) {
                                                            $hasMatch = true;
                                                            break;
                                                        }
                                                        // Also check parent service
                                                        $variant = \App\Models\ServiceVariant::with('service')->find($variantId);
                                                        if ($variant && $variant->service && $promotion->services->contains('id', $variant->service->id)) {
                                                            $hasMatch = true;
                                                            break;
                                                        }
                                                    }
                                                }
                                                
                                                // Check combos
                                                if (!$hasMatch) {
                                                    foreach ($selectedComboIds as $comboId) {
                                                        if ($promotion->combos->contains('id', $comboId)) {
                                                            $hasMatch = true;
                                                            break;
                                                        }
                                                    }
                                                }
                                                
                                                if ($hasMatch) {
                                                    $selectedPromotion = $promotion;
                                                }
                                            }
                                        }
                                    }
                                }
                                
                                // Calculate discount from promotion - only on applicable services
                                $discountAmount = 0;
                                $applicablePrice = 0; // Price of services that match promotion
                                $finalPrice = $totalPrice;
                                
                                if ($selectedPromotion) {
                                    // Check if promotion applies to all services
                                    $hasSpecificServices = $selectedPromotion->services->count() > 0 
                                        || $selectedPromotion->combos->count() > 0 
                                        || $selectedPromotion->serviceVariants->count() > 0;
                                    
                                    $totalSelected = $selectedPromotion->services->count() 
                                        + $selectedPromotion->combos->count() 
                                        + $selectedPromotion->serviceVariants->count();
                                    
                                    $applyToAll = !$hasSpecificServices || $totalSelected >= 20;
                                    
                                    // Calculate applicable price (only for services that match promotion)
                                    if ($selectedPromotion->apply_scope === 'order' || $applyToAll) {
                                        // Apply to all services
                                        $applicablePrice = $totalPrice;
                                    } else {
                                        // Only apply to matching services
                                        foreach ($allSelectedItems as $item) {
                                            $isApplicable = false;
                                            
                                            if ($item['type'] === 'service') {
                                                if ($selectedPromotion->services->contains('id', $item['id'])) {
                                                    $isApplicable = true;
                                                }
                                            } elseif ($item['type'] === 'variant') {
                                                if ($selectedPromotion->serviceVariants->contains('id', $item['id'])) {
                                                    $isApplicable = true;
                                                } else {
                                                    // Check if variant's parent service is in promotion
                                                    $variant = \App\Models\ServiceVariant::with('service')->find($item['id']);
                                                    if ($variant && $variant->service && $selectedPromotion->services->contains('id', $variant->service->id)) {
                                                        $isApplicable = true;
                                                    }
                                                }
                                            } elseif ($item['type'] === 'combo') {
                                                if ($selectedPromotion->combos->contains('id', $item['id'])) {
                                                    $isApplicable = true;
                                                }
                                            }
                                            
                                            if ($isApplicable) {
                                                $applicablePrice += $item['price'];
                                            }
                                        }
                                    }
                                    
                                    // Calculate discount on applicable price only
                                    if ($applicablePrice > 0) {
                                        if ($selectedPromotion->discount_type === 'percent') {
                                            $discountPercent = $selectedPromotion->discount_percent ?? 0;
                                            $discountAmount = ($applicablePrice * $discountPercent) / 100;
                                            // Apply max discount if exists
                                            if ($selectedPromotion->max_discount_amount) {
                                                $discountAmount = min($discountAmount, $selectedPromotion->max_discount_amount);
                                            }
                                        } else {
                                            $discountAmount = min($selectedPromotion->discount_amount ?? 0, $applicablePrice);
                                        }
                                        $finalPrice = max(0, $totalPrice - $discountAmount);
                                    }
                                }
                                
                                // Debug: Log để kiểm tra
                                if (config('app.debug')) {
                                    \Log::info('Appointment create - Total price calculation', [
                                        'url' => request()->fullUrl(),
                                        'service_ids' => $debugServiceIds,
                                        'variant_ids' => $debugVariantIds,
                                        'combo_ids' => $debugComboIds,
                                        'service_count' => count($debugServiceIds),
                                        'variant_count' => count($debugVariantIds),
                                        'combo_count' => count($debugComboIds),
                                        'total_count' => $totalCount,
                                        'total_price' => $totalPrice,
                                        'discount_amount' => $discountAmount,
                                        'final_price' => $finalPrice,
                                        'promotion_id' => request('promotion_id'),
                                        'all_items' => array_map(function($item) {
                                            return [
                                                'id' => $item['id'],
                                                'name' => $item['name'],
                                                'price' => $item['price'],
                                                'type' => $item['type']
                                            ];
                                        }, $allSelectedItems)
                                    ]);
                                }
                            @endphp
                            
                            @if($hasAnyService && count($allSelectedItems) > 0)
                                <!-- Container với white background, rounded corners và shadow -->
                                <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden;">
                                    <!-- Header với icon và số lượng -->
                                    <div style="background: #fff; padding: 16px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f0f0f0;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <i class="fa fa-scissors" style="color: #000; font-size: 18px;"></i>
                                            <span style="color: #000; font-size: 14px; font-weight: 600;">Đã chọn {{ $totalCount }} dịch vụ</span>
                                        </div>
                                        <i class="fa fa-chevron-right" style="color: #000; font-size: 14px;"></i>
                                    </div>
                                    
                                    <!-- Danh sách dịch vụ dạng tags -->
                                    @php
                                        // Determine which services are eligible for discount
                                        $applicableServiceIds = [];
                                        $nonApplicableServiceIds = [];
                                        
                                        if ($selectedPromotion) {
                                            $hasSpecificServices = $selectedPromotion->services->count() > 0 
                                                || $selectedPromotion->combos->count() > 0 
                                                || $selectedPromotion->serviceVariants->count() > 0;
                                            
                                            $totalSelected = $selectedPromotion->services->count() 
                                                + $selectedPromotion->combos->count() 
                                                + $selectedPromotion->serviceVariants->count();
                                            
                                            $applyToAll = !$hasSpecificServices || $totalSelected >= 20;
                                            
                                            if ($selectedPromotion->apply_scope === 'order' || $applyToAll) {
                                                // All services are applicable
                                                foreach ($allSelectedItems as $item) {
                                                    $applicableServiceIds[] = $item['id'] . '_' . $item['type'];
                                                }
                                            } else {
                                                // Check each service
                                                foreach ($allSelectedItems as $item) {
                                                    $isApplicable = false;
                                                    
                                                    if ($item['type'] === 'service') {
                                                        if ($selectedPromotion->services->contains('id', $item['id'])) {
                                                            $isApplicable = true;
                                                        }
                                                    } elseif ($item['type'] === 'variant') {
                                                        if ($selectedPromotion->serviceVariants->contains('id', $item['id'])) {
                                                            $isApplicable = true;
                                                        } else {
                                                            $variant = \App\Models\ServiceVariant::with('service')->find($item['id']);
                                                            if ($variant && $variant->service && $selectedPromotion->services->contains('id', $variant->service->id)) {
                                                                $isApplicable = true;
                                                            }
                                                        }
                                                    } elseif ($item['type'] === 'combo') {
                                                        if ($selectedPromotion->combos->contains('id', $item['id'])) {
                                                            $isApplicable = true;
                                                        }
                                                    }
                                                    
                                                    if ($isApplicable) {
                                                        $applicableServiceIds[] = $item['id'] . '_' . $item['type'];
                                                    } else {
                                                        $nonApplicableServiceIds[] = $item['id'] . '_' . $item['type'];
                                                    }
                                                }
                                            }
                                        } else {
                                            // No promotion, all services are non-applicable
                                            foreach ($allSelectedItems as $item) {
                                                $nonApplicableServiceIds[] = $item['id'] . '_' . $item['type'];
                                            }
                                        }
                                    @endphp
                                    <div style="background: #fff; padding: 16px; display: flex; flex-wrap: wrap; gap: 8px;">
                                        @foreach($allSelectedItems as $item)
                                            @php
                                                $serviceKey = $item['id'] . '_' . $item['type'];
                                                $isApplicable = in_array($serviceKey, $applicableServiceIds);
                                            @endphp
                                            <div style="background: {{ $isApplicable ? '#e8f5e9' : '#f5f5f5' }}; border: 1px solid {{ $isApplicable ? '#4caf50' : '#e0e0e0' }}; border-radius: 16px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: #333;">
                                                @if($isApplicable && $selectedPromotion)
                                                    <i class="fa fa-check-circle" style="color: #4caf50; font-size: 12px;"></i>
                                                @endif
                                                <span>{{ $item['name'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if($selectedPromotion && count($applicableServiceIds) > 0 && count($nonApplicableServiceIds) > 0)
                                        <div style="background: #f8f9fa; border-left: 3px solid #28a745; padding: 12px 16px; border-top: 1px solid #f0f0f0; font-size: 13px;">
                                            <div style="margin-bottom: 8px;">
                                                <i class="fa fa-check-circle" style="color: #28a745; margin-right: 6px; font-size: 14px;"></i>
                                                <span style="color: #28a745; font-weight: 600; font-size: 13px;">Được giảm giá:</span>
                                                <span style="color: #333; font-weight: 500; margin-left: 4px;">
                                                    @foreach($allSelectedItems as $item)
                                                        @php
                                                            $serviceKey = $item['id'] . '_' . $item['type'];
                                                            $isApplicable = in_array($serviceKey, $applicableServiceIds);
                                                        @endphp
                                                        @if($isApplicable)
                                                            {{ $item['name'] }}@if(!$loop->last), @endif
                                                        @endif
                                                    @endforeach
                                                </span>
                                            </div>
                                            <div>
                                                <i class="fa fa-circle-o" style="color: #999; margin-right: 6px; font-size: 14px;"></i>
                                                <span style="color: #999; font-weight: 600; font-size: 13px;">Không được giảm:</span>
                                                <span style="color: #666; margin-left: 4px;">
                                                    @foreach($allSelectedItems as $item)
                                                        @php
                                                            $serviceKey = $item['id'] . '_' . $item['type'];
                                                            $isApplicable = in_array($serviceKey, $applicableServiceIds);
                                                        @endphp
                                                        @if(!$isApplicable)
                                                            {{ $item['name'] }}@if(!$loop->last), @endif
                                                        @endif
                                                    @endforeach
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- Tổng số tiền -->
                                    <div style="background: #fff; padding: 16px; border-top: 1px solid #f0f0f0;">
                                        @if($selectedPromotion && $discountAmount > 0)
                                            <div style="margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px dashed #e0e0e0;">
                                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                                    <span style="color: #666; font-size: 13px;">Tổng giá gốc:</span>
                                                    <span style="color: #999; font-size: 14px; text-decoration: line-through;">{{ number_format($totalPrice, 0, ',', '.') }} VNĐ</span>
                                                </div>
                                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                                    <span style="color: #28a745; font-size: 13px; font-weight: 600;">✓ Giảm giá:</span>
                                                    <span style="color: #28a745; font-size: 14px; font-weight: 700;">-{{ number_format($discountAmount, 0, ',', '.') }} VNĐ</span>
                                                </div>
                                            </div>
                                        @endif
                                        <div style="display: flex; align-items: center; justify-content: space-between;">
                                            <span style="color: #000; font-size: 14px; font-weight: 600;">Tổng số tiền anh cần thanh toán:</span>
                                            <span id="totalPriceDisplay" style="color: #28a745; font-size: 18px; font-weight: 700;">{{ number_format($finalPrice, 0, ',', '.') }} VNĐ</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <script>
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
                                
                                // Selected services data for discount calculation
                                @php
                                    $selectedServicesDataForJs = array_map(function($item) {
                                        $data = [
                                            'id' => $item['id'],
                                            'type' => $item['type'],
                                            'price' => $item['price']
                                        ];
                                        // For variants, add parent service ID if available
                                        if ($item['type'] === 'variant') {
                                            $variant = \App\Models\ServiceVariant::with('service')->find($item['id']);
                                            if ($variant && $variant->service) {
                                                $data['parent_service_id'] = $variant->service->id;
                                            }
                                        }
                                        return $data;
                                    }, $allSelectedItems);
                                @endphp
                                const selectedServicesData = @json($selectedServicesDataForJs);
                                
                                // Update total price from sessionStorage and apply discount
                                document.addEventListener('DOMContentLoaded', function() {
                                    try {
                                        const stored = sessionStorage.getItem('selectedServices');
                                        const totalPriceEl = document.getElementById('totalPriceDisplay');
                                        if (!totalPriceEl) return;
                                        
                                        let totalPrice = {{ $totalPrice }};
                                        
                                        if (stored) {
                                            const parsed = JSON.parse(stored);
                                            const prices = parsed.prices || {};
                                            
                                            // Calculate total from sessionStorage prices
                                            let totalFromStorage = 0;
                                            Object.values(prices).forEach(price => {
                                                totalFromStorage += parseFloat(price) || 0;
                                            });
                                            
                                            // Use sessionStorage price if available and different
                                            if (totalFromStorage > 0) {
                                                totalPrice = totalFromStorage;
                                            }
                                        }
                                        
                                        // Calculate discount if promotion exists - only on applicable services
                                        let discountAmount = 0;
                                        let applicablePrice = 0; // Price of services that match promotion
                                        let finalPrice = totalPrice;
                                        
                                        if (promotionData && selectedServicesData) {
                                            // Check if promotion applies to all services
                                            const hasSpecificServices = (promotionData.service_ids && promotionData.service_ids.length > 0) 
                                                || (promotionData.combo_ids && promotionData.combo_ids.length > 0) 
                                                || (promotionData.variant_ids && promotionData.variant_ids.length > 0);
                                            
                                            const totalSelected = (promotionData.service_ids?.length || 0) 
                                                + (promotionData.combo_ids?.length || 0) 
                                                + (promotionData.variant_ids?.length || 0);
                                            
                                            const applyToAll = !hasSpecificServices || totalSelected >= 20;
                                            
                                            // Calculate applicable price (only for services that match promotion)
                                            if (promotionData.apply_scope === 'order' || applyToAll) {
                                                // Apply to all services
                                                applicablePrice = totalPrice;
                                            } else {
                                                // Only apply to matching services
                                                selectedServicesData.forEach(item => {
                                                    let isApplicable = false;
                                                    
                                                    if (item.type === 'service') {
                                                        if (promotionData.service_ids && promotionData.service_ids.includes(item.id)) {
                                                            isApplicable = true;
                                                        }
                                                    } else if (item.type === 'variant') {
                                                        if (promotionData.variant_ids && promotionData.variant_ids.includes(item.id)) {
                                                            isApplicable = true;
                                                        } else if (item.parent_service_id && promotionData.service_ids && promotionData.service_ids.includes(item.parent_service_id)) {
                                                            // Check if variant's parent service is in promotion
                                                            isApplicable = true;
                                                        }
                                                    } else if (item.type === 'combo') {
                                                        if (promotionData.combo_ids && promotionData.combo_ids.includes(item.id)) {
                                                            isApplicable = true;
                                                        }
                                                    }
                                                    
                                                    if (isApplicable) {
                                                        applicablePrice += parseFloat(item.price || 0);
                                                    }
                                                });
                                            }
                                            
                                            // Calculate discount on applicable price only
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
                                        
                                        // Format and update price
                                        const formattedPrice = Math.round(finalPrice).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                                        totalPriceEl.textContent = formattedPrice + ' VNĐ';
                                    } catch (e) {
                                        console.error('Error updating total price:', e);
                                    }
                                });
                                </script>
                                
                                <!-- Nút chọn thêm dịch vụ -->
                                @php
                                    // Build URL query string manually to ensure correct format
                                    $urlParams = [];
                                    
                                    // Get service IDs
                                    if (request('service_id')) {
                                        $queryServices = request()->query('service_id', []);
                                        if (!is_array($queryServices)) {
                                            $queryServices = $queryServices ? [$queryServices] : [];
                                        }
                                        $serviceIds = array_filter($queryServices, function($id) {
                                            return !empty($id) && $id !== '0' && $id !== 0 && is_numeric($id);
                                        });
                                        foreach ($serviceIds as $serviceId) {
                                            $urlParams[] = 'service_id[]=' . urlencode($serviceId);
                                        }
                                    }
                                    
                                    // Get variant IDs
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
                                        
                                        foreach ($queryParams as $key => $value) {
                                            if (preg_match('/^service_variants\[(\d+)\]$/', $key, $matches)) {
                                                $queryVariants[] = $value;
                                            }
                                        }
                                        
                                        $variantIds = array_filter($queryVariants, function($id) {
                                            return !empty($id) && $id !== '0' && $id !== 0 && is_numeric($id);
                                        });
                                        
                                        foreach (array_unique($variantIds) as $variantId) {
                                            $urlParams[] = 'service_variants[]=' . urlencode($variantId);
                                        }
                                    }
                                    
                                    // Get combo IDs
                                    if (request('combo_id')) {
                                        $queryCombos = request()->query('combo_id', []);
                                        if (!is_array($queryCombos)) {
                                            $queryCombos = $queryCombos ? [$queryCombos] : [];
                                        }
                                        $comboIds = array_filter($queryCombos, function($id) {
                                            return !empty($id) && $id !== '0' && $id !== 0 && is_numeric($id);
                                        });
                                        foreach ($comboIds as $comboId) {
                                            $urlParams[] = 'combo_id[]=' . urlencode($comboId);
                                        }
                                    }
                                    
                                    // Add promotion_id if exists
                                    if (request('promotion_id')) {
                                        $urlParams[] = 'promotion_id=' . urlencode(request('promotion_id'));
                                    }
                                    
                                    // Build final URL
                                    $selectServicesUrl = route('site.appointment.select-services');
                                    if (!empty($urlParams)) {
                                        $selectServicesUrl .= '?' . implode('&', $urlParams);
                                    }
                                @endphp
                                <a href="{{ $selectServicesUrl }}" 
                                   class="btn w-100" 
                                   style="background: #fff; border: 1px solid #0066cc; color: #0066cc; padding: 12px; font-size: 14px; font-weight: 600; border-radius: 8px; text-decoration: none; display: inline-block; text-align: center; margin-top: 12px;">
                                    <i class="fa fa-plus-circle" style="margin-right: 8px;"></i> Chọn thêm dịch vụ ({{ $totalCount }})
                                </a>
                            @else
                                <a href="{{ route('site.appointment.select-services') }}" class="btn btn-primary w-100" style="background: #000; border: 1px solid #000; color: #fff; padding: 12px; font-size: 14px; font-weight: 600; border-radius: 8px; text-decoration: none; display: inline-block; text-align: center;">
                                    <i class="fa fa-plus-circle" style="margin-right: 8px;"></i> Chọn dịch vụ
                                </a>
                            @endif
                            
                            <div class="field-error" id="service-error" style="display: none; color: #dc3545; font-size: 11px; margin-top: 4px;">
                                <i class="fa fa-exclamation-circle"></i> <span></span>
                            </div>
                            <style>
                                .btn-primary:hover {
                                    background: #FFC107 !important;
                                    color: #000 !important;
                                    border: 1px solid #FFC107 !important;
                                }
                            </style>
                        </div>

                        <!-- Thời gian -->
                        <div class="mb-2" style="margin-top: 15px;">
                            <h5 class="fw-semibold mb-2" style="color: #000; font-size: 14px; margin-bottom: 8px;">
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
                                <input type="hidden" name="employee_id" id="employee_id" value="{{ old('employee_id') }}">

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
                                                    @if($employee->position)
                                                        <div class="employee-position" style="font-size: 11px; color: #666;">{{ $employee->position }}</div>
                                                    @endif
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
                                <label class="form-label" style="font-size: 12px; margin-bottom: 5px; font-weight: 500;">
                                    <i class="fa fa-clock-o"></i> Chọn giờ <span class="text-danger">*</span>
                                </label>
                                <div class="time-slot-container" style="position: relative; display: none;">
                                    <button type="button" class="time-slot-nav-btn time-slot-prev" style="position: absolute; left: -35px; top: 50%; transform: translateY(-50%); background: #000; color: #fff; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa fa-chevron-left"></i>
                                    </button>
                                    <div id="time_slot_grid" class="time-slot-grid" style="overflow: hidden;">
                                        <div class="time-slot-slider" style="transition: transform 0.3s ease;">
                                            <!-- Time slots will be rendered here -->
                                        </div>
                                    </div>
                                    <button type="button" class="time-slot-nav-btn time-slot-next" style="position: absolute; right: -35px; top: 50%; transform: translateY(-50%); background: #000; color: #fff; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa fa-chevron-right"></i>
                                    </button>
                                </div>
                                <div id="time_slot_message" class="text-muted" style="padding: 8px; color: #000; font-size: 13px;">
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
        background: #FFC107 !important;
        color: #000 !important;
        border: 1px solid #FFC107 !important;
    }

    .submit-appointment-btn:hover i {
        color: #000 !important;
    }

    /* Fill User Info Button - giống nút Chọn dịch vụ */
    .fill-user-info-btn {
        cursor: pointer;
    }

    .fill-user-info-btn:hover {
        background: #FFC107 !important;
        border-color: #FFC107 !important;
        color: #000 !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
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
        margin-top: 8px;
        padding: 0;
    }

    /* Time Slot Grid */
    .time-slot-grid {
        width: 100%;
        position: relative;
        overflow: hidden;
    }

    .time-slot-slider {
        display: flex;
        gap: 0;
        width: max-content;
        transition: transform 0.3s ease;
    }

    .time-slot-page {
        display: grid;
        grid-template-columns: repeat(11, 1fr);
        grid-template-rows: repeat(3, 1fr);
        grid-auto-flow: column;
        gap: 8px;
        width: 100%;
        min-width: 100%;
        flex-shrink: 0;
        box-sizing: border-box;
        align-items: stretch;
        justify-items: stretch;
        overflow: visible;
        margin-top: 10px;
    }
    
    .time-slot-page > * {
        min-width: 0;
        min-height: 0;
    }

    .time-slot-nav-btn {
        transition: all 0.3s ease;
    }

    .time-slot-nav-btn:hover {
        background: #FFC107 !important;
        color: #000 !important;
    }

    .time-slot-nav-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .time-slot-btn {
        padding: 14px 10px;
        border: 1px solid #000;
        border-radius: 8px;
        background: #fff;
        color: #000;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
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
        transform: scale(1);
        z-index: 1;
        position: relative;
    }

    .time-slot-btn:hover:not(.unavailable) {
        background: #f8f8f8;
        border-color: #333;
        transform: scale(1.1);
        z-index: 10;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .time-slot-btn.selected {
        background: #000;
        color: #fff;
        border-color: #000;
        font-weight: 600;
    }

    .time-slot-btn.unavailable {
        background: #e8e8e8;
        color: #b0b0b0;
        border: 1px solid #e0e0e0;
        cursor: not-allowed;
        opacity: 0.6;
    }

    .time-slot-btn.unavailable:hover {
        background: #e8e8e8;
        transform: none;
        box-shadow: none;
        border-color: #e0e0e0;
    }

    .time-slot-btn.empty-slot {
        visibility: hidden;
        pointer-events: none;
        border: none;
        background: transparent;
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 10px 6px;
        min-width: 0;
        min-height: 0;
        box-sizing: border-box;
        overflow: hidden;
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

    .employee-item-btn.selected .employee-name,
    .employee-item-btn.selected .employee-position {
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
        background: #FFC107 !important;
        color: #000 !important;
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

    .employee-name,
    .employee-position {
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
        background: #FFC107 !important;
        color: #000 !important;
    }

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
            
            if (removedCount > 0) {
                console.log(`Removed ${removedCount} duplicate/invalid ${inputName} inputs`);
            }
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
            
            const removedCount = existingValues.length - validVariants.length;
            if (removedCount > 0) {
                console.log(`Removed ${removedCount} invalid service_variants[] inputs and recreated ${validVariants.length} valid ones from URL`);
            } else if (existingValues.length !== validVariants.length) {
                console.log(`Recreated ${validVariants.length} service_variants[] inputs from URL (was ${existingValues.length})`);
            }
            
            return validVariants;
        }
        
        // Log BEFORE cleanup
        console.log('Hidden inputs BEFORE cleanup:', {
            service_variants: $('input[name="service_variants[]"]').length,
            service_id: $('input[name="service_id[]"]').length,
            combo_id: $('input[name="combo_id[]"]').length,
            url: window.location.href,
        });
        
        // Log all service_variants values before cleanup
        const allVariantsBefore = [];
        $('input[name="service_variants[]"]').each(function() {
            allVariantsBefore.push($(this).val());
        });
        console.log('All service_variants values BEFORE cleanup:', allVariantsBefore);
        
        // CRITICAL: First validate inputs against URL, then remove duplicates
        const validUrlVariants = validateHiddenInputsFromUrl();
        
        // Remove duplicates for all three input types
        removeDuplicateHiddenInputs('service_variants[]');
        removeDuplicateHiddenInputs('service_id[]');
        removeDuplicateHiddenInputs('combo_id[]');
        
        // Log AFTER cleanup
        const allVariantsAfter = [];
        $('input[name="service_variants[]"]').each(function() {
            allVariantsAfter.push($(this).val());
        });
        console.log('Hidden inputs AFTER cleanup:', {
            service_variants: $('input[name="service_variants[]"]').length,
            service_id: $('input[name="service_id[]"]').length,
            combo_id: $('input[name="combo_id[]"]').length,
        });
        console.log('All service_variants values AFTER cleanup:', allVariantsAfter);
        
        // Khôi phục thông tin từ localStorage khi quay lại từ trang chọn dịch vụ
        const savedFormData = localStorage.getItem('appointmentFormData');
        let restoredEmployeeId = null;
        let restoredAppointmentDate = null;
        
        if (savedFormData) {
            try {
                const formData = JSON.parse(savedFormData);
                // Khôi phục thông tin khách hàng (luôn điền lại khi quay lại)
                if (formData.name) {
                    $('#name').val(formData.name);
                }
                if (formData.phone) {
                    $('#phone').val(formData.phone);
                }
                if (formData.email) {
                    $('input[name="email"]').val(formData.email);
                }
                if (formData.note) {
                    $('textarea[name="note"]').val(formData.note);
                }
                // Khôi phục thông tin đặt lịch nếu có
                if (formData.employee_id) {
                    $('#employee_id').val(formData.employee_id);
                    restoredEmployeeId = formData.employee_id;
                    // Enable input date nếu đã có employee
                    $('#appointment_date').prop('disabled', false);
                }
                if (formData.appointment_date) {
                    $('#appointment_date').val(formData.appointment_date);
                    restoredAppointmentDate = formData.appointment_date;
                }
                if (formData.word_time_id) {
                    $('#word_time_id').val(formData.word_time_id);
                }
                if (formData.time_slot) {
                    $('#time_slot').val(formData.time_slot);
                }
            } catch (e) {
                console.error('Error restoring form data:', e);
            }
        }
        
        // Lưu thông tin form vào localStorage trước khi chuyển trang chọn dịch vụ
        $('.select-services-link').on('click', function(e) {
            const formData = {
                name: $('#name').val() || '',
                phone: $('#phone').val() || '',
                email: $('input[name="email"]').val() || '',
                note: $('textarea[name="note"]').val() || '',
                employee_id: $('#employee_id').val() || '',
                appointment_date: $('#appointment_date').val() || '',
                word_time_id: $('#word_time_id').val() || '',
                time_slot: $('#time_slot').val() || ''
            };
            localStorage.setItem('appointmentFormData', JSON.stringify(formData));
        });
        
        // Lưu thông tin form khi người dùng nhập (auto-save)
        $('#name, #phone, input[name="email"], textarea[name="note"]').on('input change', function() {
            const formData = {
                name: $('#name').val() || '',
                phone: $('#phone').val() || '',
                email: $('input[name="email"]').val() || '',
                note: $('textarea[name="note"]').val() || '',
                employee_id: $('#employee_id').val() || '',
                appointment_date: $('#appointment_date').val() || '',
                word_time_id: $('#word_time_id').val() || '',
                time_slot: $('#time_slot').val() || ''
            };
            localStorage.setItem('appointmentFormData', JSON.stringify(formData));
        });
        
        // Lưu khi chọn employee, date, time slot
        $('#employee_id, #appointment_date, #word_time_id, #time_slot').on('change', function() {
            const formData = {
                name: $('#name').val() || '',
                phone: $('#phone').val() || '',
                email: $('input[name="email"]').val() || '',
                note: $('textarea[name="note"]').val() || '',
                employee_id: $('#employee_id').val() || '',
                appointment_date: $('#appointment_date').val() || '',
                word_time_id: $('#word_time_id').val() || '',
                time_slot: $('#time_slot').val() || ''
            };
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
            
            // Clear phone error nếu đã có giá trị
            if ($('#phone').val() && $('#phone').val().trim() !== '') {
                $('#phone-error').hide();
                $('#phone').removeClass('is-invalid');
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
        if (!$('#employee_id').val()) {
            $('#appointment_date').prop('disabled', true);
        } else {
            // Nếu đã có employee_id (từ localStorage), enable input date
            $('#appointment_date').prop('disabled', false);
        }
        
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
            
            // Kiểm tra xem có dịch vụ nào được chọn không
            if (serviceIds.length === 0 && serviceVariants.length === 0 && comboIds.length === 0) {
                const $slider = $('.employee-slider');
                $slider.empty();
                $slider.append('<div style="text-align: center; padding: 20px; color: #999; width: 100%;">Vui lòng chọn dịch vụ trước để hiển thị kỹ thuật viên phù hợp</div>');
                $('#employee_id').val('');
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
                                
                                if (employee.position) {
                                    itemHtml += '<div class="employee-position" style="font-size: 11px; color: #666;">' + employee.position + '</div>';
                                }
                                
                                itemHtml += '</div>';
                                $slider.append(itemHtml);
                            });
                            
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
                    console.error('Error loading employees:', xhr);
                    const $slider = $('.employee-slider');
                    $slider.empty();
                    $slider.append('<div style="text-align: center; padding: 20px; color: #dc3545; width: 100%;">Có lỗi xảy ra khi tải danh sách kỹ thuật viên</div>');
                }
            });
        }
        
        // Employee Selector - Toggle container
        $('#employeeToggleBtn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            const container = $('#employeeContainer');
            const chevron = $('.employee-chevron');
            
            // Chỉ toggle khi click vào toggle button, không toggle khi click vào container
            if (container.is(':visible')) {
                container.slideUp(300, function() {
                    chevron.css('transform', 'rotate(0deg)');
                });
            } else {
                container.slideDown(300, function() {
                    chevron.css('transform', 'rotate(180deg)');
                    // Load employees nếu chưa có
                    if ($('.employee-item-btn').length === 0) {
                        loadEmployeesForCarousel();
                    }
                });
            }
            
            return false;
        });
        
        
        // Xử lý old value nếu có
        const oldEmployeeId = $('#employee_id').val();
        if (oldEmployeeId) {
            const selectedEmployee = $('.employee-item-btn[data-employee-id="' + oldEmployeeId + '"]');
            if (selectedEmployee.length) {
                selectedEmployee.addClass('selected');
                selectedEmployee.find('.employee-avatar-wrapper').css('border-color', '#007bff');
            }
        }
        
        // Xử lý chọn employee - đặt priority cao để chạy trước document click
        $('#employeeContainer').on('click', '.employee-item-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            const employeeId = $(this).data('employee-id');
            const employeeName = $(this).data('employee-name');
            const employeePosition = $(this).data('employee-position');
            
            if (!employeeId) {
                return false;
            }
            
            // Cập nhật hidden input
            $('#employee_id').val(employeeId);
            
            // Debug: Log để kiểm tra
            console.log('Employee selected:', {
                employeeId: employeeId,
                employeeIdValue: $('#employee_id').val()
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
        
        $('input[name="phone"]').on('input keyup change paste', function() {
            const value = $(this).val();
            if (value && value.trim().length > 0) {
                $('#phone-error').hide();
                $(this).removeClass('is-invalid');
            }
        });
        
        // Clear error khi focus vào input (nếu đã có giá trị)
        $('input[name="name"], input[name="phone"]').on('focus', function() {
            const value = $(this).val();
            if (value && value.trim().length > 0) {
                const fieldName = $(this).attr('name');
                $('#' + fieldName + '-error').hide();
                $(this).removeClass('is-invalid');
            }
        });
        
        $('#employee_id').on('change', function() {
            const employeeId = $(this).val();
            const $appointmentDate = $('#appointment_date');
            
            if (employeeId) {
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
                if ($('#employee_id').val()) {
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
        
        // Clear time slot error when a time slot is selected
        $(document).on('click', '.time-slot-btn:not(.unavailable)', function() {
            $('#time_slot-error').hide();
        });
        
        // Format time from HH:MM to HHhMM
        function formatTimeSlot(time) {
            return time.replace(':', 'h');
        }

        // Load available time slots when employee or date changes
        function loadAvailableTimeSlots() {
            const employeeId = $('#employee_id').val();
            const appointmentDate = $('#appointment_date').val();
            const timeSlotGrid = $('#time_slot_grid');
            const timeSlotMessage = $('#time_slot_message');
            const timeSlotHidden = $('#time_slot');
            const wordTimeIdInput = $('#word_time_id');
            
            // Reset
            $('.time-slot-container').hide();
            $('.time-slot-slider').empty();
            timeSlotMessage.show();
            timeSlotHidden.val('');
            wordTimeIdInput.val('');
            
            // Check if employee is selected
            if (!employeeId) {
                timeSlotMessage.text('Vui lòng chọn kỹ thuật viên trước');
                return;
            }
            
            // Check if date is selected
            if (!appointmentDate) {
                timeSlotMessage.text('Vui lòng chọn ngày trước');
                return;
            }
            
            // Show loading
            timeSlotMessage.text('Đang tải khung giờ...');
            
            // Load time slots via AJAX
            $.ajax({
                url: '{{ route("site.appointment.available-time-slots") }}',
                method: 'GET',
                data: {
                    employee_id: employeeId || '',
                    appointment_date: appointmentDate
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // Kiểm tra xem có message từ server không (khi không có lịch làm việc)
                    if (response.success && response.message && (!response.time_slots || response.time_slots.length === 0)) {
                        $('.time-slot-container').hide();
                        timeSlotMessage.text(response.message || 'Nhân viên này không có lịch làm việc vào ngày đã chọn');
                        timeSlotHidden.val('');
                        wordTimeIdInput.val('');
                        return;
                    }
                    
                    if (response.success && response.time_slots && response.time_slots.length > 0) {
                        const currentlySelectedTime = timeSlotHidden.val();
                        let availableCount = 0;
                        
                        // Sort time slots by time
                        const sortedSlots = response.time_slots.sort(function(a, b) {
                            return a.time.localeCompare(b.time);
                        });
                        
                        const $slider = $('.time-slot-slider');
                        $slider.empty();
                        
                        // Tính toán số cột và hàng dựa trên số lượng slots
                        // Từ 7:00 đến 22:00 mỗi 30 phút = 30 slots
                        // Sử dụng 10 cột x 3 hàng = 30 slots (hoặc 11 cột x 3 hàng = 33 slots để có thêm không gian)
                        const fixedColumns = 11;
                        const totalSlots = sortedSlots.length;
                        // Tính số hàng cần thiết (làm tròn lên)
                        const fixedRowsPerPage = Math.ceil(totalSlots / fixedColumns);
                        const slotsPerPage = fixedColumns * fixedRowsPerPage;
                        
                        // Xóa style cũ nếu có
                        $('#dynamic-time-slot-style').remove();
                        
                        // Cập nhật CSS cho grid - cố định 11 cột x 3 hàng để đồng bộ cho tất cả nhân viên
                        $('<style>').prop('id', 'dynamic-time-slot-style').html(
                            '.time-slot-page { grid-template-columns: repeat(' + fixedColumns + ', 1fr) !important; grid-template-rows: repeat(' + fixedRowsPerPage + ', 1fr) !important; }'
                        ).appendTo('head');
                        
                        let currentPage = null;
                        let slotIndex = 0;
                        
                        sortedSlots.forEach(function(slot) {
                            // Create new page if needed
                            if (slotIndex % slotsPerPage === 0) {
                                currentPage = $('<div></div>').addClass('time-slot-page');
                                $slider.append(currentPage);
                            }
                            
                            const isAvailable = slot.available !== false;
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
                            } else {
                                availableCount++;
                                if (isSelected) {
                                    btn.addClass('selected');
                                    timeSlotHidden.val(slot.time);
                                    wordTimeIdInput.val(slot.word_time_id);
                                }
                            }
                            
                            currentPage.append(btn);
                            slotIndex++;
                        });
                        
                        // Đảm bảo page cuối cùng luôn có đủ slots để layout đồng bộ
                        if (currentPage && currentPage.children().length < slotsPerPage) {
                            const remainingSlots = slotsPerPage - currentPage.children().length;
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
                        }
                        
                        if (availableCount === 0) {
                            $('.time-slot-container').hide();
                            timeSlotMessage.text('Không còn khung giờ trống trong ca làm việc của nhân viên này');
                        } else {
                            $('.time-slot-container').show();
                            timeSlotMessage.hide();
                            updateNavigationButtons();
                        }
                    } else {
                        // No time slots available
                        $('.time-slot-container').hide();
                        if (employeeId) {
                            timeSlotMessage.text(response.message || 'Nhân viên này không có ca làm việc vào ngày đã chọn');
                        } else {
                            timeSlotMessage.text('Vui lòng chọn kỹ thuật viên và ngày trước');
                        }
                        timeSlotHidden.val('');
                        wordTimeIdInput.val('');
                    }
                },
                error: function(xhr) {
                    $('.time-slot-container').hide();
                    let errorMessage = 'Có lỗi xảy ra khi tải khung giờ';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    timeSlotMessage.text(errorMessage);
                }
            });
        }

        // Update navigation buttons state
        function updateNavigationButtons() {
            const $slider = $('.time-slot-slider');
            const $container = $('.time-slot-container');
            const containerWidth = $container.width();
            const sliderWidth = $slider[0].scrollWidth;
            const currentTransform = $slider.css('transform');
            
            // Parse current transform
            let currentX = 0;
            if (currentTransform && currentTransform !== 'none') {
                const matrix = currentTransform.match(/matrix\(([^)]+)\)/);
                if (matrix) {
                    currentX = parseFloat(matrix[1].split(',')[4]) || 0;
                }
            }
            
            // Show/hide buttons based on scroll position
            $('.time-slot-prev').prop('disabled', currentX >= 0);
            $('.time-slot-next').prop('disabled', Math.abs(currentX) >= sliderWidth - containerWidth - 10);
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
            
            // Debug: Log để kiểm tra
            console.log('Time slot selected:', {
                time: time,
                wordTimeId: wordTimeId,
                timeSlotValue: $('#time_slot').val(),
                wordTimeIdValue: $('#word_time_id').val()
            });
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
        
        // Show error for a specific field - chỉ hiển thị nếu thực sự thiếu giá trị
        function showFieldError(fieldId, message) {
            // Kiểm tra xem field có giá trị không trước khi hiển thị lỗi
            let hasValue = false;
            
            if (fieldId === 'name') {
                const value = $('#name').val();
                hasValue = value && value.trim() !== '';
            } else if (fieldId === 'phone') {
                const value = $('#phone').val();
                hasValue = value && value.trim() !== '';
            } else if (fieldId === 'employee') {
                const value = $('#employee_id').val();
                hasValue = value && value !== '' && value !== '0';
            } else if (fieldId === 'appointment_date') {
                const value = $('#appointment_date').val();
                hasValue = value && value.trim() !== '';
            } else if (fieldId === 'time_slot') {
                const value = $('#word_time_id').val();
                hasValue = value && value !== '' && value !== '0';
            }
            
            // Chỉ hiển thị lỗi nếu thực sự không có giá trị
            if (!hasValue) {
                const $errorDiv = $('#' + fieldId + '-error');
                if ($errorDiv.length) {
                    $errorDiv.find('span').text(message);
                    $errorDiv.show();
                    const $field = $('#' + fieldId);
                    if ($field.length) {
                        $field.addClass('is-invalid');
                    }
                    // Xử lý đặc biệt cho employee
                    if (fieldId === 'employee') {
                        $('#employeeToggleBtn').css('color', '#dc3545');
                    }
                }
            } else {
                // Nếu đã có giá trị, clear error
                const $errorDiv = $('#' + fieldId + '-error');
                if ($errorDiv.length) {
                    $errorDiv.hide();
                }
                const $field = $('#' + fieldId);
                if ($field.length) {
                    $field.removeClass('is-invalid');
                }
                if (fieldId === 'employee') {
                    $('#employeeToggleBtn').css('color', '');
                }
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
            
            // Check name - chỉ hiển thị lỗi nếu thực sự trống
            const name = $('input[name="name"]').val();
            const nameTrimmed = name ? String(name).trim() : '';
            if (!nameTrimmed || nameTrimmed === '') {
                showFieldError('name', 'Mời anh nhập họ và tên');
                isValid = false;
            } else {
                // Clear error nếu đã có giá trị
                $('#name-error').hide();
                $('#name').removeClass('is-invalid');
            }
            
            // Check phone - chỉ hiển thị lỗi nếu thực sự trống
            const phone = $('input[name="phone"]').val();
            const phoneTrimmed = phone ? String(phone).trim() : '';
            if (!phoneTrimmed || phoneTrimmed === '') {
                showFieldError('phone', 'Mời anh nhập số điện thoại');
                isValid = false;
            } else {
                // Clear error nếu đã có giá trị
                $('#phone-error').hide();
                $('#phone').removeClass('is-invalid');
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
            const hasEmployeeId = employeeIdTrimmed && employeeIdTrimmed !== '' && employeeIdTrimmed !== '0' && employeeIdTrimmed !== 'null' && employeeIdTrimmed !== 'undefined';
            
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
            
            // Debug log để kiểm tra
            console.log('Validation details:', {
                hasName: nameTrimmed !== '',
                hasPhone: phoneTrimmed !== '',
                hasService: serviceIds.length > 0 || serviceVariants.length > 0 || comboIds.length > 0,
                hasEmployeeId: hasEmployeeId,
                hasAppointmentDate: hasAppointmentDate,
                hasWordTimeId: hasWordTimeId,
                isValid: isValid
            });
            
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
            
            // Debug: Log tất cả giá trị để kiểm tra
            console.log('Form values:', {
                name: $('#name').val(),
                phone: $('#phone').val(),
                email: $('input[name="email"]').val(),
                serviceIds: $('input[name="service_id[]"]').length,
                serviceVariants: $('input[name="service_variants[]"]').length,
                comboIds: $('input[name="combo_id[]"]').length,
                employeeId: $('#employee_id').val(),
                appointmentDate: $('#appointment_date').val(),
                wordTimeId: $('#word_time_id').val(),
                timeSlot: $('#time_slot').val()
            });
            
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
            const name = $('#name').val();
            if (name && name.trim() !== '') {
                formDataObj.name = name.trim();
            }
            
            const phone = $('#phone').val();
            if (phone && phone.trim() !== '') {
                formDataObj.phone = phone.trim();
            }
            
            const email = $('input[name="email"]').val();
            if (email && email.trim() !== '') {
                formDataObj.email = email.trim();
            }
            
            const employeeId = $('#employee_id').val();
            if (employeeId && employeeId !== '' && employeeId !== '0') {
                formDataObj.employee_id = employeeId;
            }
            
            const appointmentDate = $('#appointment_date').val();
            if (appointmentDate && appointmentDate.trim() !== '') {
                formDataObj.appointment_date = appointmentDate.trim();
            }
            
            const wordTimeId = $('#word_time_id').val();
            if (wordTimeId && wordTimeId !== '' && wordTimeId !== '0') {
                formDataObj.word_time_id = wordTimeId;
            }
            
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
            
            // Log để debug
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
            
            console.log('Form data object:', formDataObj);
            console.log('Form data keys:', Object.keys(formDataObj));
            console.log('Form data values:', {
                name: formDataObj.name,
                phone: formDataObj.phone,
                email: formDataObj.email,
                employee_id: formDataObj.employee_id,
                appointment_date: formDataObj.appointment_date,
                word_time_id: formDataObj.word_time_id,
                service_id: formDataObj.service_id,
                service_variants: formDataObj.service_variants,
                combo_id: formDataObj.combo_id
            });
            
            // Disable form và submit button để ngăn chặn submit lại
            const $submitBtn = $('.submit-appointment-btn');
            const originalBtnText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Đang xử lý...');
            $form.find('input, button, select, textarea').prop('disabled', true);
            
            // Submit form via AJAX
            $.ajax({
                url: $form.attr('action'),
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
                        
                        // Redirect to checkout page immediately
                        if (response.redirect_url) {
                            window.location.href = response.redirect_url;
                        } else {
                            window.location.href = '{{ route("site.payments.checkout") }}';
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
                    console.log('Response:', xhr.responseJSON);
                    console.log('Status:', xhr.status);
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
                        // Nếu không có errors từ server, có thể là lỗi khác
                        console.error('Unexpected error:', xhr);
                        let errorMessage = 'Có lỗi xảy ra khi đặt lịch. Vui lòng thử lại.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        alert(errorMessage);
                    }
                }
            });
        });
    });
</script>
@endpush

