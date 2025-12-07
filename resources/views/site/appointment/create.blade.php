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
                                // CRITICAL: Use same logic as hidden inputs creation - parse URL directly
                                $url = request()->fullUrl();
                                $parsedUrl = parse_url($url);
                                $queryParams = [];
                                if (isset($parsedUrl['query'])) {
                                    parse_str($parsedUrl['query'], $queryParams);
                                }
                                
                                // Get combo_id from parsed query string only
                                $queryCombos = [];
                                
                                // Check for combo_id[] format
                                if (isset($queryParams['combo_id']) && is_array($queryParams['combo_id'])) {
                                    $queryCombos = $queryParams['combo_id'];
                                } elseif (isset($queryParams['combo_id'])) {
                                    $queryCombos = [$queryParams['combo_id']];
                                }
                                
                                // Check for combo_id[0], combo_id[1], etc. format
                                $indexedCombos = [];
                                foreach ($queryParams as $key => $value) {
                                    if (preg_match('/^combo_id\[(\d+)\]$/', $key, $matches)) {
                                        $indexedCombos[] = $value;
                                    }
                                }
                                
                                // Merge both formats
                                $queryCombos = array_merge($queryCombos, $indexedCombos);
                                
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
                                // Đếm tổng số dịch vụ được chọn
                                $totalServicesCount = 0;
                                if (request('service_id')) {
                                    $serviceIds = is_array(request('service_id')) ? request('service_id') : [request('service_id')];
                                    $totalServicesCount += count(array_filter($serviceIds));
                                }
                                if (request('service_variants')) {
                                    $variantIds = is_array(request('service_variants')) ? request('service_variants') : [request('service_variants')];
                                    $totalServicesCount += count(array_filter($variantIds));
                                }
                                if (request('combo_id')) {
                                    $comboIds = is_array(request('combo_id')) ? request('combo_id') : [request('combo_id')];
                                    $totalServicesCount += count(array_filter($comboIds));
                                }
                                $hasMultipleServices = $totalServicesCount >= 2;
                            @endphp
                            
                            @if(request()->has('service_id'))
                                @php
                                    // CRITICAL: Use same logic as hidden inputs creation - parse URL directly
                                    $url = request()->fullUrl();
                                    $parsedUrl = parse_url($url);
                                    $queryParams = [];
                                    if (isset($parsedUrl['query'])) {
                                        parse_str($parsedUrl['query'], $queryParams);
                                    }
                                    
                                    // Get service_id from parsed query string only
                                    $queryServices = [];
                                    
                                    // Check for service_id[] format
                                    if (isset($queryParams['service_id']) && is_array($queryParams['service_id'])) {
                                        $queryServices = $queryParams['service_id'];
                                    } elseif (isset($queryParams['service_id'])) {
                                        $queryServices = [$queryParams['service_id']];
                                    }
                                    
                                    // Check for service_id[0], service_id[1], etc. format
                                    $indexedServices = [];
                                    foreach ($queryParams as $key => $value) {
                                        if (preg_match('/^service_id\[(\d+)\]$/', $key, $matches)) {
                                            $indexedServices[] = $value;
                                        }
                                    }
                                    
                                    // Merge both formats
                                    $queryServices = array_merge($queryServices, $indexedServices);
                                    
                                    // Filter out any empty/null values
                                    $serviceIds = array_filter($queryServices, function($id) {
                                        return !empty($id) && $id !== '0' && $id !== 0 && is_numeric($id);
                                    });
                                    
                                    // Remove duplicates
                                    $serviceIds = array_values(array_unique($serviceIds));
                                    
                                    $selectedServices = \App\Models\Service::whereIn('id', $serviceIds)->get();
                                @endphp
                                @if($selectedServices->count() > 0)
                                    @foreach($selectedServices as $selectedService)
                                        <div class="selected-service-display service-item-selectable" data-service-id="{{ $selectedService->id }}" data-service-type="service" data-service-selector="service_{{ $selectedService->id }}" data-service-duration="{{ $selectedService->base_duration ?? 60 }}" style="background: #f8f9fa; border: 2px solid #000; border-radius: 10px; padding: 15px; margin-bottom: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.3s ease; cursor: pointer;">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div style="flex: 1;">
                                                    <div style="color: #000; font-size: 15px; font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; flex-wrap: wrap; gap: 8px;">
                                                        <i class="fa fa-check-circle" style="color: #28a745; font-size: 16px; margin-right: 8px;"></i>
                                                        <span>{{ $selectedService->name }}</span>
                                                        <span id="service_employee_display_service_{{ $selectedService->id }}" class="selected-employee-info" style="display: none; font-size: 13px; color: #1976d2; font-weight: 600; margin-left: 5px;">
                                                            <i class="fa fa-user" style="color: #1976d2; margin-right: 6px;"></i>
                                                            <span class="employee-name"></span>
                                                            <span class="employee-position" style="display: none;"></span>
                                                            <span id="service_time_display_service_{{ $selectedService->id }}" class="selected-time-info" style="display: none; color: #28a745; font-weight: 600; margin-left: 8px;">
                                                                <i class="fa fa-clock-o" style="color: #28a745; margin-right: 4px;"></i>
                                                                <span class="time-slot-text"></span>
                                                            </span>
                                                        </span>
                                                    </div>
                                                    <div style="color: #666; font-size: 13px;">
                                                        <span style="margin-right: 20px;">
                                                            <i class="fa fa-money" style="color: #c08a3f;"></i> <strong style="color: #c08a3f;">{{ number_format($selectedService->base_price ?? 0, 0, ',', '.') }}vnđ</strong>
                                                        </span>
                                                        <span>
                                                            <i class="fa fa-clock-o"></i> <strong>{{ $selectedService->base_duration ?? 60 }} phút</strong>
                                                        </span>
                                                    </div>
                                                    @if($hasMultipleServices)
                                                        <input type="hidden" name="service_employee[service_{{ $selectedService->id }}]" 
                                                               class="service-employee-input" 
                                                               data-service-id="{{ $selectedService->id }}"
                                                               data-service-type="service"
                                                               data-display-container="service_employee_display_service_{{ $selectedService->id }}"
                                                               data-duration="{{ $selectedService->base_duration ?? 60 }}"
                                                               value="">
                                                        <input type="hidden" name="service_time[service_{{ $selectedService->id }}]" 
                                                               class="service-time-input" 
                                                               data-service-id="{{ $selectedService->id }}"
                                                               data-service-type="service"
                                                               data-display-container="service_time_display_service_{{ $selectedService->id }}"
                                                               data-duration="{{ $selectedService->base_duration ?? 60 }}"
                                                               value="">
                                                        <input type="hidden" name="service_date[service_{{ $selectedService->id }}]" 
                                                               class="service-date-input" 
                                                               data-service-id="{{ $selectedService->id }}"
                                                               data-service-type="service"
                                                               data-duration="{{ $selectedService->base_duration ?? 60 }}"
                                                               value="">
                                                    @endif
                                                </div>
                                                <a href="{{ route('site.appointment.create', array_filter(array_merge(request()->all(), ['remove_service_id' => $selectedService->id]))) }}" class="btn btn-sm" style="background: #fff; border: 1px solid #dc3545; color: #dc3545; padding: 6px 12px; font-size: 12px; border-radius: 6px; margin-left: 15px;">
                                                    <i class="fa fa-times"></i> Xóa
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            @endif
                            
                            @if(request()->has('service_variants'))
                                @php
                                    // CRITICAL: Use same logic as hidden inputs creation - parse URL directly
                                    $url = request()->fullUrl();
                                    $parsedUrl = parse_url($url);
                                    $queryParams = [];
                                    if (isset($parsedUrl['query'])) {
                                        parse_str($parsedUrl['query'], $queryParams);
                                    }
                                    
                                    // Get service_variants from parsed query string only
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
                                    
                                    $selectedVariants = \App\Models\ServiceVariant::whereIn('id', $variantIds)->with(['service', 'variantAttributes'])->get();
                                @endphp
                                @if($selectedVariants->count() > 0)
                                    @foreach($selectedVariants as $variant)
                                        <div class="selected-variant-display service-item-selectable" data-variant-id="{{ $variant->id }}" data-service-id="{{ $variant->service_id }}" data-service-type="variant" data-service-selector="variant_{{ $variant->id }}" data-variant-duration="{{ $variant->duration ?? 60 }}" style="background: #f8f9fa; border: 2px solid #000; border-radius: 10px; padding: 15px; margin-bottom: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.3s ease; cursor: pointer;">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div style="flex: 1;">
                                                    <div style="color: #000; font-size: 15px; font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; flex-wrap: wrap; gap: 8px;">
                                                        <i class="fa fa-check-circle" style="color: #28a745; font-size: 16px; margin-right: 8px;"></i>
                                                        <span>{{ $variant->name }}</span>
                                                        @if($variant->service)
                                                            <span style="color: #666; font-size: 12px; font-weight: 400;">({{ $variant->service->name }})</span>
                                                        @endif
                                                        <span id="service_employee_display_variant_{{ $variant->id }}" class="selected-employee-info" style="display: none; font-size: 13px; color: #1976d2; font-weight: 600; margin-left: 5px;">
                                                            <i class="fa fa-user" style="color: #1976d2; margin-right: 6px;"></i>
                                                            <span class="employee-name"></span>
                                                            <span class="employee-position" style="display: none;"></span>
                                                            <span id="service_time_display_variant_{{ $variant->id }}" class="selected-time-info" style="display: none; color: #28a745; font-weight: 600; margin-left: 8px;">
                                                                <i class="fa fa-clock-o" style="color: #28a745; margin-right: 4px;"></i>
                                                                <span class="time-slot-text"></span>
                                                            </span>
                                                        </span>
                                                    </div>
                                                    <div style="color: #666; font-size: 13px;">
                                                        <span style="margin-right: 20px;">
                                                            <i class="fa fa-money" style="color: #c08a3f;"></i> <strong style="color: #c08a3f;">{{ number_format($variant->price ?? 0, 0, ',', '.') }}vnđ</strong>
                                                        </span>
                                                        <span style="margin-right: 20px;">
                                                            <i class="fa fa-clock-o"></i> <strong>{{ $variant->duration ?? 60 }} phút</strong>
                                                        </span>
                                                        @if($variant->variantAttributes && $variant->variantAttributes->count() > 0)
                                                            @foreach($variant->variantAttributes as $attr)
                                                                <span style="margin-right: 12px; display: inline-flex; align-items: center; gap: 4px; color: #1976d2; font-size: 12px; background: #e3f2fd; padding: 2px 8px; border-radius: 4px;">
                                                                    <i class="fa fa-tag"></i>
                                                                    <strong>{{ $attr->attribute_name }}: {{ $attr->attribute_value }}</strong>
                                                                </span>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                    @if($hasMultipleServices)
                                                        <input type="hidden" name="service_employee[variant_{{ $variant->id }}]" 
                                                                class="service-employee-input" 
                                                                data-variant-id="{{ $variant->id }}"
                                                                data-service-id="{{ $variant->service_id }}"
                                                                data-service-type="variant"
                                                                data-display-container="service_employee_display_variant_{{ $variant->id }}"
                                                                data-duration="{{ $variant->duration ?? 60 }}"
                                                                value="">
                                                        <input type="hidden" name="service_time[variant_{{ $variant->id }}]" 
                                                               class="service-time-input" 
                                                               data-variant-id="{{ $variant->id }}"
                                                               data-service-type="variant"
                                                               data-display-container="service_time_display_variant_{{ $variant->id }}"
                                                               data-duration="{{ $variant->duration ?? 60 }}"
                                                               value="">
                                                        <input type="hidden" name="service_date[variant_{{ $variant->id }}]" 
                                                               class="service-date-input" 
                                                               data-variant-id="{{ $variant->id }}"
                                                               data-service-type="variant"
                                                               data-duration="{{ $variant->duration ?? 60 }}"
                                                               value="">
                                                    @endif
                                                </div>
                                                <a href="{{ route('site.appointment.create', array_filter(array_merge(request()->all(), ['remove_variant_id' => $variant->id]))) }}" class="btn btn-sm" style="background: #fff; border: 1px solid #dc3545; color: #dc3545; padding: 6px 12px; font-size: 12px; border-radius: 6px; margin-left: 15px;">
                                                    <i class="fa fa-times"></i> Xóa
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            @endif
                            
                            @if(request('combo_id'))
                                @php
                                    // CRITICAL: Use same logic as hidden inputs creation - parse URL directly
                                    $url = request()->fullUrl();
                                    $parsedUrl = parse_url($url);
                                    $queryParams = [];
                                    if (isset($parsedUrl['query'])) {
                                        parse_str($parsedUrl['query'], $queryParams);
                                    }
                                    
                                    // Get combo_id from parsed query string only
                                    $queryCombos = [];
                                    
                                    // Check for combo_id[] format
                                    if (isset($queryParams['combo_id']) && is_array($queryParams['combo_id'])) {
                                        $queryCombos = $queryParams['combo_id'];
                                    } elseif (isset($queryParams['combo_id'])) {
                                        $queryCombos = [$queryParams['combo_id']];
                                    }
                                    
                                    // Check for combo_id[0], combo_id[1], etc. format
                                    $indexedCombos = [];
                                    foreach ($queryParams as $key => $value) {
                                        if (preg_match('/^combo_id\[(\d+)\]$/', $key, $matches)) {
                                            $indexedCombos[] = $value;
                                        }
                                    }
                                    
                                    // Merge both formats
                                    $queryCombos = array_merge($queryCombos, $indexedCombos);
                                    
                                    // Filter out any empty/null values
                                    $comboIds = array_filter($queryCombos, function($id) {
                                        return !empty($id) && $id !== '0' && $id !== 0 && is_numeric($id);
                                    });
                                    
                                    // Remove duplicates
                                    $comboIds = array_values(array_unique($comboIds));
                                    
                                    $selectedCombos = \App\Models\Combo::whereIn('id', $comboIds)->with('comboItems.serviceVariant')->get();
                                @endphp
                                @if($selectedCombos->count() > 0)
                                    @foreach($selectedCombos as $selectedCombo)
                                        @php
                                            $comboDuration = 60;
                                            $comboServiceIds = [];
                                            if ($selectedCombo->comboItems && $selectedCombo->comboItems->count() > 0) {
                                                $comboDuration = $selectedCombo->comboItems->sum(function($item) {
                                                    return $item->serviceVariant->duration ?? 60;
                                                });
                                                // Lấy service IDs từ combo items
                                                foreach ($selectedCombo->comboItems as $item) {
                                                    if ($item->serviceVariant && $item->serviceVariant->service_id) {
                                                        $comboServiceIds[] = $item->serviceVariant->service_id;
                                                    } elseif ($item->service_id) {
                                                        $comboServiceIds[] = $item->service_id;
                                                    }
                                                }
                                                $comboServiceIds = array_unique($comboServiceIds);
                                            }
                                        @endphp
                                        <div class="selected-combo-display service-item-selectable" data-combo-id="{{ $selectedCombo->id }}" data-service-type="combo" data-service-selector="combo_{{ $selectedCombo->id }}" data-combo-duration="{{ $comboDuration }}" style="background: #f8f9fa; border: 2px solid #000; border-radius: 10px; padding: 15px; margin-bottom: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.3s ease; cursor: pointer;">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div style="flex: 1;">
                                                    <div style="color: #000; font-size: 15px; font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; flex-wrap: wrap; gap: 8px;">
                                                        <i class="fa fa-check-circle" style="color: #28a745; font-size: 16px; margin-right: 8px;"></i>
                                                        <span>{{ $selectedCombo->name }}</span>
                                                        <span style="color: #666; font-size: 12px; font-weight: 400; margin-left: 5px;">(COMBO)</span>
                                                        <span id="service_employee_display_combo_{{ $selectedCombo->id }}" class="selected-employee-info" style="display: none; font-size: 13px; color: #1976d2; font-weight: 600; margin-left: 5px;">
                                                            <i class="fa fa-user" style="color: #1976d2; margin-right: 6px;"></i>
                                                            <span class="employee-name"></span>
                                                            <span class="employee-position" style="display: none;"></span>
                                                            <span id="service_time_display_combo_{{ $selectedCombo->id }}" class="selected-time-info" style="display: none; color: #28a745; font-weight: 600; margin-left: 8px;">
                                                                <i class="fa fa-clock-o" style="color: #28a745; margin-right: 4px;"></i>
                                                                <span class="time-slot-text"></span>
                                                            </span>
                                                        </span>
                                                    </div>
                                                    <div style="color: #666; font-size: 13px;">
                                                        <span style="margin-right: 20px;">
                                                            <i class="fa fa-money" style="color: #c08a3f;"></i> <strong style="color: #c08a3f;">{{ number_format($selectedCombo->price ?? 0, 0, ',', '.') }}vnđ</strong>
                                                        </span>
                                                        <span>
                                                            <i class="fa fa-clock-o"></i> <strong>{{ $comboDuration }} phút</strong>
                                                        </span>
                                                    </div>
                                                    @if($hasMultipleServices)
                                                        <input type="hidden" name="service_employee[combo_{{ $selectedCombo->id }}]" 
                                                               class="service-employee-input" 
                                                               data-combo-id="{{ $selectedCombo->id }}"
                                                               data-service-ids="{{ implode(',', $comboServiceIds) }}"
                                                               data-service-type="combo"
                                                               data-display-container="service_employee_display_combo_{{ $selectedCombo->id }}"
                                                               data-duration="{{ $comboDuration }}"
                                                               value="">
                                                        <input type="hidden" name="service_time[combo_{{ $selectedCombo->id }}]" 
                                                               class="service-time-input" 
                                                               data-combo-id="{{ $selectedCombo->id }}"
                                                               data-service-type="combo"
                                                               data-display-container="service_time_display_combo_{{ $selectedCombo->id }}"
                                                               data-duration="{{ $comboDuration }}"
                                                               value="">
                                                        <input type="hidden" name="service_date[combo_{{ $selectedCombo->id }}]" 
                                                               class="service-date-input" 
                                                               data-combo-id="{{ $selectedCombo->id }}"
                                                               data-service-type="combo"
                                                               data-duration="{{ $comboDuration }}"
                                                               value="">
                                                    @endif
                                                </div>
                                                <a href="{{ route('site.appointment.create', array_filter(array_merge(request()->all(), ['remove_combo_id' => $selectedCombo->id]))) }}" class="btn btn-sm" style="background: #fff; border: 1px solid #dc3545; color: #dc3545; padding: 6px 12px; font-size: 12px; border-radius: 6px; margin-left: 15px;">
                                                    <i class="fa fa-times"></i> Xóa
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            @endif
                            
                            @if($hasAnyService)
                                <div style="margin-top: 6px;">
                                    <a href="{{ route('site.appointment.select-services', array_merge(request()->all(), ['add_more' => true])) }}" 
                                       class="btn btn-sm w-100 select-services-link" 
                                       style="background: #000; border: 1px solid #000; color: #fff; padding: 8px 16px; font-size: 12px; font-weight: 600; border-radius: 6px; text-decoration: none; display: inline-block; text-align: center; height: 38px;">
                                        <i class="fa fa-plus"></i> Chọn thêm dịch vụ
                                    </a>
                                </div>
                            @else
                                <a href="{{ route('site.appointment.select-services') }}" 
                                   class="btn btn-primary w-100 select-services-link" 
                                   style="background: #000; border: 1px solid #000; color: #fff; padding: 8px 16px; font-size: 12px; font-weight: 600; border-radius: 6px; transition: all 0.3s ease; text-decoration: none; display: inline-block; text-align: center; height: 38px;">
                                    <i class="fa fa-book"></i> Chọn dịch vụ
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
                            <div class="mb-2 employee-selection-container" style="margin-top: 10px;">
                                <label class="form-label" style="font-size: 12px; margin-bottom: 5px; font-weight: 500; display: flex; align-items: center; gap: 6px; cursor: pointer;" id="employeeToggleBtn">
                                    <i class="fa fa-scissors"></i>
                                    <span>KỸ THUẬT VIÊN <span class="text-danger">*</span></span>
                                    <i class="fa fa-chevron-down employee-chevron" style="font-size: 12px; color: #999; transition: transform 0.3s ease; margin-left: auto;"></i>
                                </label>

                                <!-- Hidden input để lưu employee_id (dùng khi chỉ có 1 dịch vụ) -->
                                <input type="hidden" name="employee_id" id="employee_id" value="">

                                <!-- Container hiển thị nhân viên giống time slot -->
                                <div class="employee-container" id="employeeContainer" style="position: relative; display: none; margin-top: 10px;">
                                    <button type="button" class="employee-nav-btn employee-nav-prev" style="position: absolute; left: -35px; top: 50%; transform: translateY(-50%); background: #000; color: #fff; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa fa-chevron-left"></i>
                                    </button>
                                    <div id="employee_grid" class="employee-grid" style="overflow: hidden; padding: 5px 0;">
                                        <div class="employee-slider" style="transition: transform 0.3s ease; display: flex; gap: 15px;">
                                            @if(count($employees) > 0)
                                                @foreach($employees as $employee)
                                                    @php
                                                        // Chỉ thêm class selected nếu đã có dịch vụ được chọn
                                                        $isEmployeeSelected = $hasAnyService && old('employee_id') == $employee->id;
                                                    @endphp
                                                    <div class="employee-item-btn{{ $isEmployeeSelected ? ' selected' : '' }}" data-employee-id="{{ $employee->id }}" data-employee-name="{{ $employee->user->name }}" data-employee-position="{{ $employee->position ?? '' }}" style="text-align: center; cursor: pointer; padding: 10px; min-width: 120px; flex-shrink: 0;">
                                                        <div class="employee-avatar-wrapper" style="width: 100px; height: 100px; margin: 0 auto 8px; border-radius: 50%; overflow: hidden; border: 2px solid {{ $isEmployeeSelected ? '#007bff' : '#ddd' }};">
                                                            @if($employee->avatar)
                                                                <img src="{{ asset('legacy/images/avatars/' . $employee->avatar) }}" alt="{{ $employee->user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                            @else
                                                                <div style="width: 100%; height: 100%; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                                                    <i class="fa fa-user" style="font-size: 40px; color: #999;"></i>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="employee-name" style="font-size: 13px; font-weight: 600; color: #000; margin-bottom: 3px;">{{ $employee->user->name }}</div>
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
        grid-auto-flow: row;
        gap: 10px;
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
        padding: 18px 14px;
        border: 1px solid #000;
        border-radius: 8px;
        background: #fff;
        color: #000;
        font-size: 16px;
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
    }
    
    /* Service Time Slot Styles */
    .service-time-slot-btn {
        padding: 18px 14px;
        border: 1px solid #000;
        border-radius: 8px;
        background: #fff;
        color: #000;
        font-size: 16px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        min-width: 80px;
        flex-shrink: 0;
    }
    
    .service-time-slot-btn:hover:not(.unavailable) {
        background: #f8f8f8;
        border-color: #333;
        transform: scale(1.05);
    }
    
    .service-time-slot-btn.selected {
        background: #000;
        color: #fff;
        border-color: #000;
        font-weight: 600;
    }
    
    .service-time-slot-btn.unavailable {
        background: #e8e8e8;
        color: #b0b0b0;
        border: 1px solid #e0e0e0;
        cursor: not-allowed;
        opacity: 0.6;
    }
    
    .service-time-slot-btn.unavailable:hover {
        background: #e8e8e8;
        transform: none;
        box-shadow: none;
        border-color: #e0e0e0;
    }
    
    .service-time-slot-grid {
        width: 100%;
        position: relative;
        overflow: hidden;
    }
    
    .service-time-slot-slider {
        display: flex;
        gap: 0;
        width: max-content;
        transition: transform 0.3s ease;
    }
    
    .service-time-slot-slider .time-slot-page {
        display: grid;
        grid-template-columns: repeat(11, 1fr);
        grid-template-rows: repeat(3, 1fr);
        grid-auto-flow: row;
        gap: 10px;
        width: 100%;
        min-width: 100%;
        flex-shrink: 0;
        box-sizing: border-box;
        align-items: stretch;
        justify-items: stretch;
        overflow: visible;
        margin-top: 10px;
    }
    
    .service-time-slot-nav-btn {
        transition: all 0.3s ease;
    }
    
    .service-time-slot-nav-btn:hover {
        background: #FFC107 !important;
        color: #000 !important;
    }
    
    .service-time-slot-nav-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
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

    /* Service Employee Carousel Styles */
    .service-employee-container {
        position: relative;
    }

    .service-employee-grid {
        width: 100%;
        position: relative;
        overflow: hidden;
        padding: 5px 0;
    }

    .service-employee-slider {
        display: flex;
        gap: 15px;
        transition: transform 0.3s ease;
    }

    .service-employee-item-btn {
        transition: all 0.3s ease;
        border: 1px solid #ddd;
        border-radius: 8px;
        background: #fff;
        position: relative;
    }

    .service-employee-item-btn:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border-color: #333;
        position: relative;
        z-index: 10;
    }

    .service-employee-item-btn.selected {
        background: #fff;
        border-color: #007bff;
        border-width: 2px;
    }

    .service-employee-item-btn.selected .employee-name,
    .service-employee-item-btn.selected .employee-position {
        color: #000;
    }

    .service-employee-item-btn.selected .employee-avatar-wrapper {
        border-color: #007bff !important;
        border-width: 2px !important;
    }

    .service-employee-nav-btn {
        transition: all 0.3s ease;
    }

    .service-employee-nav-btn:hover:not(:disabled) {
        background: #FFC107 !important;
        color: #000 !important;
    }

    .service-employee-nav-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Service Selection Styles */
    .service-item-selectable {
        cursor: pointer;
        position: relative;
        user-select: none;
    }
    
    .service-item-selectable:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
    }

    .service-item-selectable.active-service {
        border-color: #007bff !important;
        border-width: 3px !important;
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3) !important;
        background: #f0f8ff !important;
        transform: translateY(-2px);
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
        // ONLY remove duplicates and empty/invalid inputs - DO NOT remove valid inputs
        function removeDuplicateHiddenInputs(inputName) {
            const seen = new Set();
            const inputs = $(`input[name="${inputName}"]`);
            let removedCount = 0;
            
            inputs.each(function() {
                const val = $(this).val();
                if (val && val.trim() !== '' && val !== '0') {
                    const id = String(val.trim());
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
        
        // CRITICAL: Get valid services from URL and ensure they exist as hidden inputs
        // DO NOT remove inputs that are already rendered from server - only add missing ones from URL
        function validateHiddenInputsFromUrl() {
            // Parse URL to get service_variants, service_id, combo_id
            const url = new URL(window.location.href);
            const urlVariants = url.searchParams.getAll('service_variants[]');
            const urlServiceIds = url.searchParams.getAll('service_id[]');
            const urlComboIds = url.searchParams.getAll('combo_id[]');
            
            // Also check for indexed format service_variants[0], service_variants[1], etc.
            const urlVariantsAlt = [];
            for (let i = 0; i < 100; i++) {
                const param = url.searchParams.get(`service_variants[${i}]`);
                if (param) {
                    urlVariantsAlt.push(param);
                } else {
                    if (i > 10) break;
                }
            }
            
            // Combine both formats and remove duplicates
            const validVariants = [...new Set([...urlVariants, ...urlVariantsAlt])].map(v => String(v)).filter(v => v && v !== '0' && v !== '');
            const validServiceIds = [...new Set(urlServiceIds)].map(v => String(v)).filter(v => v && v !== '0' && v !== '');
            const validComboIds = [...new Set(urlComboIds)].map(v => String(v)).filter(v => v && v !== '0' && v !== '');
            
            console.log('Valid variants from URL:', validVariants);
            console.log('Valid service_ids from URL:', validServiceIds);
            console.log('Valid combo_ids from URL:', validComboIds);
            
            const $form = $('#appointmentForm');
            if (!$form.length) {
                return { variants: validVariants, serviceIds: validServiceIds, comboIds: validComboIds };
            }
            
            // Ensure variants from URL exist (don't remove existing ones)
            validVariants.forEach(function(variantId) {
                const exists = $form.find(`input[name="service_variants[]"][value="${variantId}"]`).length > 0;
                if (!exists) {
                    const $newInput = $('<input>', {
                        type: 'hidden',
                        name: 'service_variants[]',
                        value: variantId
                    });
                    $form.find('input[name="_token"]').after($newInput);
                }
            });
            
            // Ensure service_ids from URL exist
            validServiceIds.forEach(function(serviceId) {
                const exists = $form.find(`input[name="service_id[]"][value="${serviceId}"]`).length > 0;
                if (!exists) {
                    const $newInput = $('<input>', {
                        type: 'hidden',
                        name: 'service_id[]',
                        value: serviceId
                    });
                    $form.find('input[name="_token"]').after($newInput);
                }
            });
            
            // Ensure combo_ids from URL exist
            validComboIds.forEach(function(comboId) {
                const exists = $form.find(`input[name="combo_id[]"][value="${comboId}"]`).length > 0;
                if (!exists) {
                    const $newInput = $('<input>', {
                        type: 'hidden',
                        name: 'combo_id[]',
                        value: comboId
                    });
                    $form.find('input[name="_token"]').after($newInput);
                }
            });
            
            return {
                variants: validVariants,
                serviceIds: validServiceIds,
                comboIds: validComboIds
            };
        }
        
        // CRITICAL: Ensure inputs from URL exist as hidden inputs
        // This function only ADDS missing inputs from URL, does NOT remove existing ones
        // Server-side rendering already created the correct inputs based on URL, we just need to ensure they exist
        const validUrlData = validateHiddenInputsFromUrl();
        
        // Only remove duplicates and empty/invalid inputs - DO NOT remove valid inputs
        // This ensures we don't have duplicate inputs, but keeps all valid ones from server-side rendering
        removeDuplicateHiddenInputs('service_variants[]');
        removeDuplicateHiddenInputs('service_id[]');
        removeDuplicateHiddenInputs('combo_id[]');
        
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
                // KHÔNG khôi phục thông tin đặt lịch (employee, date, time) - để người dùng tự chọn lại mỗi lần
                // Chỉ khôi phục thông tin khách hàng (name, phone, email, note)
                $('#employee_id').val('');
                $('#appointment_date').val('').prop('disabled', true);
                $('#word_time_id').val('');
                $('#time_slot').val('');
            } catch (e) {
                console.error('Error restoring form data:', e);
            }
        }
        
        // Reset employee selection nếu chưa có dịch vụ (chạy sau khi khôi phục từ localStorage)
        function resetEmployeeSelectionIfNoService() {
            const serviceCount = countSelectedServices();
            if (serviceCount === 0) {
                // Xóa selected class từ tất cả nhân viên
                $('.employee-item-btn').removeClass('selected');
                $('.employee-item-btn .employee-avatar-wrapper').css('border-color', '#ddd');
                // Xóa giá trị employee_id
                $('#employee_id').val('');
                // Disable input ngày
                $('#appointment_date').prop('disabled', true).val('');
                // Reset time slots
                $('.time-slot-container').hide();
                $('#time_slot_message').text('Vui lòng chọn dịch vụ trước').show();
                $('#time_slot').val('');
                $('#word_time_id').val('');
            }
        }
        
        // Chạy ngay sau khi khôi phục từ localStorage
        resetEmployeeSelectionIfNoService();
        
        // Chạy lại sau một chút để đảm bảo tất cả code đã chạy xong
        setTimeout(function() {
            resetEmployeeSelectionIfNoService();
        }, 100);
        
        // Chạy lại sau khi DOM đã load xong hoàn toàn
        $(window).on('load', function() {
            resetEmployeeSelectionIfNoService();
        });
        
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
        
        // Biến để lưu dịch vụ đang được chọn để gán nhân viên
        let activeServiceSelector = null;
        
        // Đếm số dịch vụ được chọn
        function countSelectedServices() {
            let count = 0;
            $('input[name="service_id[]"]').each(function() {
                if ($(this).val()) count++;
            });
            $('input[name="service_variants[]"]').each(function() {
                if ($(this).val()) count++;
            });
            $('input[name="combo_id[]"]').each(function() {
                if ($(this).val()) count++;
            });
            return count;
        }
        
        // Xử lý khi click vào card dịch vụ để chọn dịch vụ đang active (chỉ khi có >= 2 dịch vụ)
        $(document).on('click', '.service-item-selectable', function(e) {
            // Nếu click vào button xóa hoặc link khác, không xử lý
            if ($(e.target).closest('a, button').length) {
                return;
            }
            
            const serviceCount = countSelectedServices();
            if (serviceCount < 2) {
                return; // Chỉ xử lý khi có >= 2 dịch vụ
            }
            
            e.preventDefault();
            e.stopPropagation();
            
            const $serviceItem = $(this);
            const selector = $serviceItem.attr('data-service-selector');
            
            if (!selector) {
                return;
            }
            
            // Kiểm tra xem dịch vụ này đã được chọn (active) chưa
            const isCurrentlyActive = $serviceItem.hasClass('active-service');
            
            // Chuyển active từ dịch vụ cũ sang dịch vụ mới (nếu chưa active)
            if (!isCurrentlyActive) {
                $('.service-item-selectable').removeClass('active-service');
                // Đánh dấu dịch vụ này là active
                $serviceItem.addClass('active-service');
                // Lưu selector của dịch vụ đang active
                activeServiceSelector = selector;
                
                // Luôn xóa employees cũ từ server-side render để đảm bảo đồng bộ (giống như arrow dropdown)
                $('.employee-slider').empty();
                
                // Load nhân viên cho dịch vụ này (luôn load lại từ AJAX để đảm bảo đồng bộ)
                loadEmployeesForSelectedService($serviceItem);
                
                // KHÔNG tự động mở dropdown khi click vào service item
                // Người dùng phải click vào arrow dropdown để mở
            }
        });
        
        // Load nhân viên cho dịch vụ được chọn
        function loadEmployeesForSelectedService($serviceItem) {
            const serviceType = $serviceItem.attr('data-service-type');
            let serviceId = null;
            let variantId = null;
            let comboId = null;
            
            if (serviceType === 'service') {
                serviceId = $serviceItem.attr('data-service-id');
            } else if (serviceType === 'variant') {
                variantId = $serviceItem.attr('data-variant-id');
            } else if (serviceType === 'combo') {
                comboId = $serviceItem.attr('data-combo-id');
            }
            
            if (!serviceId && !variantId && !comboId) {
                return;
            }
            
            // Hiển thị loading
            const $slider = $('.employee-slider');
            $slider.html('<div style="text-align: center; padding: 20px; color: #999; width: 100%;">Đang tải danh sách kỹ thuật viên...</div>');
            
            // Gọi API để lấy TẤT CẢ nhân viên (không filter theo dịch vụ)
            $.ajax({
                url: '{{ route("site.appointment.employees-for-service") }}',
                method: 'GET',
                data: {
                    // Không gửi service_id, variant_id, combo_id để lấy tất cả nhân viên
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success && response.employees) {
                        const currentEmployeeId = $('#employee_id').val();
                        const $slider = $('.employee-slider');
                        
                        // Clear existing content
                        $slider.empty();
                        
                        if (response.employees.length > 0) {
                            response.employees.forEach(function(employee) {
                                const avatarUrl = employee.avatar ? '{{ asset("legacy/images/avatars") }}/' + employee.avatar : '';
                                const isSelected = currentEmployeeId == employee.id;
                                
                                const serviceIds = employee.service_ids || [];
                                let itemHtml = '<div class="employee-item-btn' + (isSelected ? ' selected' : '') + '" data-employee-id="' + employee.id + '" data-employee-name="' + employee.name + '" data-employee-position="' + (employee.position || '') + '" data-service-ids="' + serviceIds.join(',') + '" style="text-align: center; cursor: pointer; padding: 10px; min-width: 120px; flex-shrink: 0;">';
                                itemHtml += '<div class="employee-avatar-wrapper" style="width: 100px; height: 100px; margin: 0 auto 8px; border-radius: 50%; overflow: hidden; border: 2px solid ' + (isSelected ? '#007bff' : '#ddd') + ';">';
                                
                                if (avatarUrl) {
                                    itemHtml += '<img src="' + avatarUrl + '" alt="' + employee.name + '" style="width: 100%; height: 100%; object-fit: cover;">';
                                } else {
                                    itemHtml += '<div style="width: 100%; height: 100%; background: #f0f0f0; display: flex; align-items: center; justify-content: center;"><i class="fa fa-user" style="font-size: 40px; color: #999;"></i></div>';
                                }
                                
                                itemHtml += '</div>';
                                itemHtml += '<div class="employee-name" style="font-size: 13px; font-weight: 600; color: #000; margin-bottom: 3px;">' + employee.name + '</div>';
                                
                                itemHtml += '</div>';
                                $slider.append(itemHtml);
                            });
                            
                            // Reset slider position về đầu
                            $slider.css('transform', 'translateX(0px)');
                        } else {
                            // Không có nhân viên phù hợp
                            $slider.html('<div style="text-align: center; padding: 20px; color: #999; width: 100%;">Không có kỹ thuật viên nào có chuyên môn phù hợp với dịch vụ này</div>');
                        }
                    }
                },
                error: function(xhr) {
                    console.error('Error loading employees for service:', xhr);
                    const $slider = $('.employee-slider');
                    $slider.empty();
                    $slider.html('<div style="text-align: center; padding: 20px; color: #dc3545; width: 100%;">Có lỗi xảy ra khi tải danh sách kỹ thuật viên</div>');
                }
            });
        }
        
        // Load employees for each service when >= 2 services (vào carousel)
        function loadEmployeesForEachService() {
            const serviceCount = countSelectedServices();
            
            if (serviceCount < 2) {
                // Chỉ có 1 dịch vụ, giữ nguyên selector chung
                $('.service-employee-selector').hide();
                $('.employee-selection-container').show();
                loadEmployeesByService();
                return;
            }
            
            // Có >= 2 dịch vụ, giữ nguyên carousel chung và hiển thị selector riêng cho từng dịch vụ
            $('.employee-selection-container').show();
            $('.service-employee-selector').show();
            
            // Load nhân viên cho từng dịch vụ vào carousel (chỉ nhân viên có chuyên môn phù hợp)
            $('.service-employee-input').each(function() {
                const $input = $(this);
                const serviceType = $input.attr('data-service-type');
                let serviceId = null;
                let variantId = null;
                let comboId = null;
                let sliderSelector = null;
                
                if (serviceType === 'service') {
                    serviceId = $input.attr('data-service-id');
                    sliderSelector = '.service_employee_slider_service_' + serviceId;
                } else if (serviceType === 'variant') {
                    variantId = $input.attr('data-variant-id');
                    sliderSelector = '.service_employee_slider_variant_' + variantId;
                } else if (serviceType === 'combo') {
                    comboId = $input.attr('data-combo-id');
                    sliderSelector = '.service_employee_slider_combo_' + comboId;
                }
                
                if (!serviceId && !variantId && !comboId) {
                    return;
                }
                
                const $slider = $(sliderSelector);
                if ($slider.length === 0) {
                    return;
                }
                
                // Load TẤT CẢ nhân viên (không filter theo dịch vụ)
                $.ajax({
                    url: '{{ route("site.appointment.employees-for-service") }}',
                    method: 'GET',
                    data: {
                        // Không gửi service_id, variant_id, combo_id để lấy tất cả nhân viên
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success && response.employees) {
                            // Clear existing content
                            $slider.empty();
                            
                            // Add employees to carousel
                            if (response.employees.length > 0) {
                                response.employees.forEach(function(employee) {
                                    const avatarUrl = employee.avatar ? '{{ asset("legacy/images/avatars") }}/' + employee.avatar : '';
                                    
                                    let itemHtml = '<div class="service-employee-item-btn" data-employee-id="' + employee.id + '" data-employee-name="' + (employee.name || employee.display_name) + '" data-employee-position="' + (employee.position || '') + '" data-service-input="' + $input.attr('name') + '" data-display-container="' + $input.attr('data-display-container') + '" style="text-align: center; cursor: pointer; padding: 10px; min-width: 120px; flex-shrink: 0; border: 1px solid #ddd; border-radius: 8px; background: #fff;">';
                                    itemHtml += '<div class="employee-avatar-wrapper" style="width: 100px; height: 100px; margin: 0 auto 8px; border-radius: 50%; overflow: hidden; border: 2px solid #ddd;">';
                                    
                                    if (avatarUrl) {
                                        itemHtml += '<img src="' + avatarUrl + '" alt="' + employee.name + '" style="width: 100%; height: 100%; object-fit: cover;">';
                                    } else {
                                        itemHtml += '<div style="width: 100%; height: 100%; background: #f0f0f0; display: flex; align-items: center; justify-content: center;"><i class="fa fa-user" style="font-size: 40px; color: #999;"></i></div>';
                                    }
                                    
                                    itemHtml += '</div>';
                                    itemHtml += '<div class="employee-name" style="font-size: 13px; font-weight: 600; color: #000; margin-bottom: 3px;">' + employee.name + '</div>';
                                    
                                    itemHtml += '</div>';
                                    $slider.append(itemHtml);
                                });
                                
                                // Update navigation buttons after a short delay to ensure DOM is updated
                                setTimeout(function() {
                                    const sliderClasses = $slider.attr('class').split(' ');
                                    const sliderClass = sliderClasses.find(c => c.startsWith('service_employee_slider_'));
                                    if (sliderClass) {
                                        updateServiceEmployeeNavigation(sliderClass);
                                    }
                                }, 100);
                            } else {
                                $slider.html('<div style="text-align: center; padding: 20px; color: #999; width: 100%;">Không có kỹ thuật viên phù hợp</div>');
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading employees for service:', xhr);
                        $slider.html('<div style="text-align: center; padding: 20px; color: #dc3545; width: 100%;">Có lỗi xảy ra khi tải danh sách kỹ thuật viên</div>');
                    }
                });
            });
        }
        
        // Update navigation buttons for service employee carousel
        function updateServiceEmployeeNavigation(sliderClass) {
            if (!sliderClass) return;
            
            const $slider = $('.' + sliderClass);
            if ($slider.length === 0) return;
            
            const $container = $slider.closest('.service-employee-grid');
            const $prevBtn = $container.siblings('.service-employee-nav-prev');
            const $nextBtn = $container.siblings('.service-employee-nav-next');
            
            if ($container.length === 0) {
                return;
            }
            
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
            
            // Show/hide navigation buttons
            if (sliderWidth <= containerWidth) {
                $prevBtn.hide();
                $nextBtn.hide();
            } else {
                $prevBtn.show();
                $nextBtn.show();
                
                // Disable/enable buttons based on position
                if (currentX >= 0) {
                    $prevBtn.prop('disabled', true);
                } else {
                    $prevBtn.prop('disabled', false);
                }
                
                const maxX = -(sliderWidth - containerWidth);
                if (currentX <= maxX) {
                    $nextBtn.prop('disabled', true);
                } else {
                    $nextBtn.prop('disabled', false);
                }
            }
        }
        
        // Handle service employee carousel navigation
        $(document).on('click', '.service-employee-nav-prev', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $container = $(this).siblings('.service-employee-grid');
            const $slider = $container.find('.service-employee-slider');
            const containerWidth = $container.width();
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
            
            setTimeout(function() {
                const sliderClasses = $slider.attr('class').split(' ');
                const sliderClass = sliderClasses.find(c => c.startsWith('service_employee_slider_'));
                if (sliderClass) {
                    updateServiceEmployeeNavigation(sliderClass);
                }
            }, 300);
        });
        
        $(document).on('click', '.service-employee-nav-next', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $container = $(this).siblings('.service-employee-grid');
            const $slider = $container.find('.service-employee-slider');
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
            
            setTimeout(function() {
                const sliderClasses = $slider.attr('class').split(' ');
                const sliderClass = sliderClasses.find(c => c.startsWith('service_employee_slider_'));
                if (sliderClass) {
                    updateServiceEmployeeNavigation(sliderClass);
                }
            }, 300);
        });
        
        // Handle service employee selection from carousel
        $(document).on('click', '.service-employee-item-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const employeeId = $(this).attr('data-employee-id');
            const employeeName = $(this).attr('data-employee-name') || '';
            const employeePosition = $(this).attr('data-employee-position') || '';
            const inputName = $(this).attr('data-service-input');
            const displayContainerId = $(this).attr('data-display-container');
            
            if (!employeeId || !inputName) {
                return false;
            }
            
            // Update hidden input
            const $employeeInput = $('input[name="' + inputName + '"]');
            $employeeInput.val(employeeId);
            
            // Tìm date picker tương ứng với dịch vụ này
            const serviceType = $employeeInput.attr('data-service-type');
            let $datePicker = null;
            let timeInputSelector = null;
            
            if (serviceType === 'service') {
                const serviceId = $employeeInput.attr('data-service-id');
                $datePicker = $('.service-date-picker[data-service-type="service"][data-service-id="' + serviceId + '"]');
                timeInputSelector = '.service-time-input[data-service-type="service"][data-service-id="' + serviceId + '"]';
            } else if (serviceType === 'variant') {
                const variantId = $employeeInput.attr('data-variant-id');
                $datePicker = $('.service-date-picker[data-service-type="variant"][data-variant-id="' + variantId + '"]');
                timeInputSelector = '.service-time-input[data-service-type="variant"][data-variant-id="' + variantId + '"]';
            } else if (serviceType === 'combo') {
                const comboId = $employeeInput.attr('data-combo-id');
                $datePicker = $('.service-date-picker[data-service-type="combo"][data-combo-id="' + comboId + '"]');
                timeInputSelector = '.service-time-input[data-service-type="combo"][data-combo-id="' + comboId + '"]';
            }
            
            // Cập nhật data-employee-id của date picker
            if ($datePicker && $datePicker.length) {
                $datePicker.attr('data-employee-id', employeeId);
                
                // Tìm time input và date input
                const $timeInput = $(timeInputSelector);
                let $dateInput = null;
                
                if (serviceType === 'service') {
                    const serviceId = $employeeInput.attr('data-service-id');
                    $dateInput = $('.service-date-input[data-service-type="service"][data-service-id="' + serviceId + '"]');
                } else if (serviceType === 'variant') {
                    const variantId = $employeeInput.attr('data-variant-id');
                    $dateInput = $('.service-date-input[data-service-type="variant"][data-variant-id="' + variantId + '"]');
                } else if (serviceType === 'combo') {
                    const comboId = $employeeInput.attr('data-combo-id');
                    $dateInput = $('.service-date-input[data-service-type="combo"][data-combo-id="' + comboId + '"]');
                }
                
                // Nếu đã chọn ngày
                const appointmentDate = $datePicker.val();
                if (appointmentDate && appointmentDate.trim() !== '' && timeInputSelector) {
                    // Tìm time container
                    const $timeContainer = $datePicker.closest('.service-time-container');
                    
                    // Load time slots cho dịch vụ này
                    loadTimeSlotsForService($datePicker, appointmentDate, employeeId, timeInputSelector);
                }
            }
            
            // Remove selected class from all items in this carousel
            $(this).closest('.service-employee-slider').find('.service-employee-item-btn').removeClass('selected');
            $(this).closest('.service-employee-slider').find('.employee-avatar-wrapper').css('border-color', '#ddd');
            
            // Add selected class to clicked item
            $(this).addClass('selected');
            $(this).find('.employee-avatar-wrapper').css('border-color', '#007bff');
            
            // Display employee info under service name
            if (displayContainerId) {
                const $displayContainer = $('#' + displayContainerId);
                $displayContainer.find('.employee-name').text(employeeName);
                
                // Ẩn position - không hiển thị vị trí nữa
                $displayContainer.find('.employee-position').hide();
                
                $displayContainer.fadeIn(300);
            }
            
            return false;
        });
        
        // Load employees by service on page load - chỉ load khi có dịch vụ
        const serviceCountOnLoad = countSelectedServices();
        if (serviceCountOnLoad > 0) {
            loadEmployeesByService();
            loadEmployeesForCarousel();
        } else {
            // Nếu chưa có dịch vụ, hiển thị thông báo
            const $slider = $('.employee-slider');
            if ($slider.length) {
                $slider.empty();
                $slider.append('<div style="text-align: center; padding: 20px; color: #999; width: 100%;">Vui lòng chọn dịch vụ trước để hiển thị kỹ thuật viên phù hợp</div>');
            }
            $('#employee_id').val('');
            // Reset employee selection
            resetEmployeeSelectionIfNoService();
        }
        
        // Đảm bảo reset sau khi tất cả code đã chạy xong
        setTimeout(function() {
            resetEmployeeSelectionIfNoService();
        }, 500);
        
        // Nếu có >= 2 dịch vụ, tự động chọn dịch vụ đầu tiên làm active
        const serviceCount = countSelectedServices();
        if (serviceCount >= 2) {
            const $firstServiceItem = $('.service-item-selectable').first();
            if ($firstServiceItem.length) {
                $firstServiceItem.addClass('active-service');
                activeServiceSelector = $firstServiceItem.attr('data-service-selector');
            }
            
            // Ẩn time slot picker cho tất cả dịch vụ sau dịch vụ đầu tiên
            $('.service-item-selectable').each(function(index) {
                if (index > 0) {
                    // Tìm time container của dịch vụ này
                    const serviceType = $(this).attr('data-service-type');
                    let $timeContainer = null;
                    
                    if (serviceType === 'service') {
                        const serviceId = $(this).attr('data-service-id');
                        $timeContainer = $('.service-time-container').has('.service-date-picker[data-service-type="service"][data-service-id="' + serviceId + '"]');
                    } else if (serviceType === 'variant') {
                        const variantId = $(this).attr('data-variant-id');
                        $timeContainer = $('.service-time-container').has('.service-date-picker[data-service-type="variant"][data-variant-id="' + variantId + '"]');
                    } else if (serviceType === 'combo') {
                        const comboId = $(this).attr('data-combo-id');
                        $timeContainer = $('.service-time-container').has('.service-date-picker[data-service-type="combo"][data-combo-id="' + comboId + '"]');
                    }
                    
                    if ($timeContainer && $timeContainer.length) {
                        $timeContainer.find('.service-time-slot-container').hide();
                        console.log('Hidden time slot picker for service index:', index);
                    }
                }
            });
        }
        
        
        // Format time slot
        function formatTimeSlot(time) {
            if (!time) return '';
            const parts = time.split(':');
            if (parts.length >= 2) {
                return parts[0] + ':' + parts[1];
            }
            return time;
        }
        
        // Lấy index của dịch vụ trong danh sách (0 = đầu tiên, 1 = thứ 2, ...)
        function getServiceIndex($serviceInput) {
            let index = -1;
            let currentIndex = 0;
            
            // Tìm theo thứ tự hiển thị trong DOM (service-item-selectable)
            $('.service-item-selectable').each(function(i) {
                const $serviceItem = $(this);
                const serviceType = $serviceItem.attr('data-service-type');
                const inputServiceType = $serviceInput.attr('data-service-type');
                
                if (serviceType === inputServiceType) {
                    if (serviceType === 'service') {
                        const serviceId = $serviceItem.attr('data-service-id');
                        const inputServiceId = $serviceInput.attr('data-service-id');
                        if (serviceId === inputServiceId) {
                            index = i;
                            return false; // break
                        }
                    } else if (serviceType === 'variant') {
                        const variantId = $serviceItem.attr('data-variant-id');
                        const inputVariantId = $serviceInput.attr('data-variant-id');
                        if (variantId === inputVariantId) {
                            index = i;
                            return false; // break
                        }
                    } else if (serviceType === 'combo') {
                        const comboId = $serviceItem.attr('data-combo-id');
                        const inputComboId = $serviceInput.attr('data-combo-id');
                        if (comboId === inputComboId) {
                            index = i;
                            return false; // break
                        }
                    }
                }
            });
            
            return index;
        }
        
        // Tính thời gian kết thúc của dịch vụ (thời gian bắt đầu + duration)
        function calculateEndTime(startTime, durationMinutes) {
            if (!startTime) return null;
            const [hours, minutes] = startTime.split(':').map(Number);
            const startTotalMinutes = hours * 60 + minutes;
            const endTotalMinutes = startTotalMinutes + durationMinutes;
            const endHours = Math.floor(endTotalMinutes / 60);
            const endMins = endTotalMinutes % 60;
            return String(endHours).padStart(2, '0') + ':' + String(endMins).padStart(2, '0');
        }
        
        // Tìm thời gian kết thúc lớn nhất từ tất cả các dịch vụ đã chọn giờ
        function getMaxEndTime() {
            let maxEndMinutes = 0;
            $('.service-time-input').each(function() {
                const $timeInput = $(this);
                const time = $timeInput.val();
                if (!time) return;
                
                const serviceType = $timeInput.attr('data-service-type');
                let duration = 60; // default
                
                if (serviceType === 'service') {
                    duration = parseInt($timeInput.attr('data-duration')) || 60;
                } else if (serviceType === 'variant') {
                    duration = parseInt($timeInput.attr('data-duration')) || 60;
                } else if (serviceType === 'combo') {
                    duration = parseInt($timeInput.attr('data-duration')) || 60;
                }
                
                const [hours, minutes] = time.split(':').map(Number);
                const startTotalMinutes = hours * 60 + minutes;
                const endTotalMinutes = startTotalMinutes + duration;
                
                if (endTotalMinutes > maxEndMinutes) {
                    maxEndMinutes = endTotalMinutes;
                }
            });
            
            if (maxEndMinutes === 0) return null;
            
            const endHours = Math.floor(maxEndMinutes / 60);
            const endMins = maxEndMinutes % 60;
            return String(endHours).padStart(2, '0') + ':' + String(endMins).padStart(2, '0');
        }
        
        // Tự động chọn giờ cho dịch vụ dựa trên thời gian kết thúc của dịch vụ trước đó
        function autoSelectTimeForService($timeInput, $dateInput, appointmentDate, employeeId) {
            console.log('=== AUTO-SELECT TIME FOR SERVICE ===');
            console.log('$timeInput:', $timeInput.attr('data-service-type'), $timeInput.attr('data-service-id') || $timeInput.attr('data-variant-id') || $timeInput.attr('data-combo-id'));
            
            const maxEndTime = getMaxEndTime();
            if (!maxEndTime) {
                console.log('No previous service time found, will select first available slot');
            }
            
            // Tìm time container của dịch vụ này (từ date input)
            const serviceType = $timeInput.attr('data-service-type');
            let $datePicker = null;
            
            if (serviceType === 'service') {
                const serviceId = $timeInput.attr('data-service-id');
                $datePicker = $('.service-date-picker[data-service-type="service"][data-service-id="' + serviceId + '"]');
            } else if (serviceType === 'variant') {
                const variantId = $timeInput.attr('data-variant-id');
                $datePicker = $('.service-date-picker[data-service-type="variant"][data-variant-id="' + variantId + '"]');
            } else if (serviceType === 'combo') {
                const comboId = $timeInput.attr('data-combo-id');
                $datePicker = $('.service-date-picker[data-service-type="combo"][data-combo-id="' + comboId + '"]');
            }
            
            if (!$datePicker || !$datePicker.length) {
                console.error('Date picker not found for service');
                return false;
            }
            
            // Tìm time slot gần nhất >= maxEndTime
            const $timeContainer = $datePicker.closest('.service-time-container');
            const $timeSlotContainer = $timeContainer.find('.service-time-slot-container');
            const $timeSlotSlider = $timeContainer.find('.service-time-slot-slider');
            
            // Kiểm tra xem slider có time slots chưa
            if (!$timeSlotSlider.length || $timeSlotSlider.find('.service-time-slot-btn').length === 0) {
                console.log('Time slots not loaded yet, waiting...');
                // Nếu chưa có time slots, đợi thêm một chút rồi thử lại
                setTimeout(function() {
                    autoSelectTimeForService($timeInput, $dateInput, appointmentDate, employeeId);
                }, 500);
                return false;
            }
            
            // Parse maxEndTime thành phút
            const [maxEndHours, maxEndMins] = maxEndTime.split(':').map(Number);
            const maxEndTotalMinutes = maxEndHours * 60 + maxEndMins;
            
            console.log('Auto-selecting time for service. maxEndTime:', maxEndTime, 'maxEndTotalMinutes:', maxEndTotalMinutes);
            console.log('Available time slots:', $timeSlotSlider.find('.service-time-slot-btn:not(.unavailable)').length);
            
            // Tìm time slot đầu tiên >= maxEndTime (hoặc time slot đầu tiên nếu không có maxEndTime)
            let selectedTime = null;
            const availableSlots = $timeSlotSlider.find('.service-time-slot-btn:not(.unavailable)');
            
            if (maxEndTime) {
                // Có dịch vụ trước đó, chọn time slot >= thời gian kết thúc
                availableSlots.each(function() {
                    const slotTime = $(this).attr('data-time');
                    if (!slotTime) return;
                    
                    const [slotHours, slotMins] = slotTime.split(':').map(Number);
                    const slotTotalMinutes = slotHours * 60 + slotMins;
                    
                    console.log('Checking slot:', slotTime, 'totalMinutes:', slotTotalMinutes, '>=', maxEndTotalMinutes, '?', slotTotalMinutes >= maxEndTotalMinutes);
                    
                    if (slotTotalMinutes >= maxEndTotalMinutes) {
                        selectedTime = slotTime;
                        return false; // break
                    }
                });
            } else {
                // Không có dịch vụ trước đó, chọn time slot đầu tiên có sẵn
                const firstSlot = availableSlots.first();
                if (firstSlot.length) {
                    selectedTime = firstSlot.attr('data-time');
                    console.log('No previous service, selecting first available slot:', selectedTime);
                }
            }
            
            if (selectedTime) {
                // Tự động chọn time slot này
                $timeInput.val(selectedTime);
                
                // Cập nhật display container
                const serviceType = $timeInput.attr('data-service-type');
                let displayContainerId = null;
                
                if (serviceType === 'service') {
                    const serviceId = $timeInput.attr('data-service-id');
                    displayContainerId = 'service_time_display_service_' + serviceId;
                } else if (serviceType === 'variant') {
                    const variantId = $timeInput.attr('data-variant-id');
                    displayContainerId = 'service_time_display_variant_' + variantId;
                } else if (serviceType === 'combo') {
                    const comboId = $timeInput.attr('data-combo-id');
                    displayContainerId = 'service_time_display_combo_' + comboId;
                }
                
                if (displayContainerId) {
                    const $displayContainer = $('#' + displayContainerId);
                    if ($displayContainer.length === 1) {
                        // QUAN TRỌNG: Kiểm tra lại container ID để đảm bảo không cập nhật nhầm
                        const containerIdCheck = $displayContainer.attr('id');
                        if (containerIdCheck === displayContainerId) {
                            const formattedTime = formatTimeSlot(selectedTime);
                            $displayContainer.find('.time-slot-text').text(formattedTime);
                            $displayContainer.fadeIn(300);
                            console.log('✅ Updated display container from localStorage:', displayContainerId, 'with time:', formattedTime);
                        } else {
                            console.error('❌ Container ID mismatch in localStorage restore:', displayContainerId, 'Actual:', containerIdCheck);
                        }
                    } else {
                        console.error('❌ Multiple or no display containers found:', displayContainerId, 'Count:', $displayContainer.length);
                    }
                }
                
                // Đánh dấu time slot được chọn
                $timeSlotSlider.find('.service-time-slot-btn').removeClass('selected');
                $timeSlotSlider.find('.service-time-slot-btn[data-time="' + selectedTime + '"]').addClass('selected');
                
                console.log('Auto-selected time:', selectedTime, 'for service (>=', maxEndTime, ')');
                return true;
            }
            
            console.log('No available time slot found >=', maxEndTime);
            return false;
        }
        
        // Load time slots cho từng dịch vụ
        function loadTimeSlotsForService($datePicker, appointmentDate, employeeId, timeInputSelector, callback) {
            const $timeContainer = $datePicker.closest('.service-time-container');
            const $timeSlotContainer = $timeContainer.find('.service-time-slot-container');
            const $timeSlotSlider = $timeContainer.find('.service-time-slot-slider');
            const $timeSlotMessage = $timeContainer.find('.service-time-slot-message');
            const $timeInput = $(timeInputSelector);
            const $dateInput = $timeContainer.siblings('.service-date-input');
            
            // Reset - CHỈ reset nếu chưa có giờ được chọn (giữ lại giờ đã chọn)
            $timeSlotContainer.hide();
            $timeSlotSlider.empty();
            $timeSlotMessage.hide();
            // KHÔNG xóa giờ đã chọn khi reload - chỉ reset nếu chưa có giờ
            // $timeInput.val(''); // Comment out để giữ lại giờ đã chọn
            
            if (!employeeId || !appointmentDate) {
                return;
            }
            
            // Show loading
            $timeSlotMessage.text('Đang tải khung giờ...').show();
            
            // Load time slots via AJAX
            $.ajax({
                url: '{{ route("site.appointment.available-time-slots") }}',
                method: 'GET',
                data: {
                    employee_id: employeeId,
                    appointment_date: appointmentDate
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success && response.message && (!response.time_slots || response.time_slots.length === 0)) {
                        $timeSlotContainer.hide();
                        $timeSlotMessage.text(response.message || 'Nhân viên này không có lịch làm việc vào ngày đã chọn').show();
                        $timeInput.val('');
                        return;
                    }
                    
                    if (response.success && response.time_slots && response.time_slots.length > 0) {
                        const currentlySelectedTime = $timeInput.val();
                        
                        // Tìm tất cả các dịch vụ đã chọn giờ để tính thời gian kết thúc
                        // Lưu danh sách các khoảng thời gian đã được sử dụng
                        const usedTimeRanges = [];
                        $('.service-time-input').each(function() {
                            const $otherTimeInput = $(this);
                            
                            // Bỏ qua chính dịch vụ đang chọn giờ
                            if ($otherTimeInput[0] === $timeInput[0]) {
                                return true; // continue
                            }
                            
                            const otherTime = $otherTimeInput.val();
                            if (!otherTime) {
                                return true; // continue - dịch vụ này chưa chọn giờ
                            }
                            
                            // Tìm date input tương ứng với time input này
                            const otherServiceType = $otherTimeInput.attr('data-service-type');
                            let $otherDateInput = null;
                            
                            if (otherServiceType === 'service') {
                                const otherServiceId = $otherTimeInput.attr('data-service-id');
                                $otherDateInput = $('.service-date-input[data-service-type="service"][data-service-id="' + otherServiceId + '"]');
                            } else if (otherServiceType === 'variant') {
                                const otherVariantId = $otherTimeInput.attr('data-variant-id');
                                $otherDateInput = $('.service-date-input[data-service-type="variant"][data-variant-id="' + otherVariantId + '"]');
                            } else if (otherServiceType === 'combo') {
                                const otherComboId = $otherTimeInput.attr('data-combo-id');
                                $otherDateInput = $('.service-date-input[data-service-type="combo"][data-combo-id="' + otherComboId + '"]');
                            }
                            
                            if (!$otherDateInput || !$otherDateInput.length) {
                                return true; // continue - không tìm thấy date input
                            }
                            
                            const otherDate = $otherDateInput.val();
                            
                            // Nếu dịch vụ này đã chọn giờ và cùng ngày
                            if (otherTime && otherDate && otherDate === appointmentDate) {
                                // Lấy duration từ data attribute hoặc từ service item
                                let duration = parseInt($otherTimeInput.attr('data-duration'));
                                
                                // Nếu không có trong input hoặc = 0, tìm từ service item
                                if (!duration || duration === 0 || isNaN(duration)) {
                                    const otherServiceType = $otherTimeInput.attr('data-service-type');
                                    if (otherServiceType === 'service') {
                                        const otherServiceId = $otherTimeInput.attr('data-service-id');
                                        const $serviceItem = $('.selected-service-display[data-service-id="' + otherServiceId + '"]');
                                        duration = parseInt($serviceItem.attr('data-service-duration')) || 60;
                                    } else if (otherServiceType === 'variant') {
                                        const otherVariantId = $otherTimeInput.attr('data-variant-id');
                                        const $variantItem = $('.selected-variant-display[data-variant-id="' + otherVariantId + '"]');
                                        duration = parseInt($variantItem.attr('data-variant-duration')) || 60;
                                    } else if (otherServiceType === 'combo') {
                                        const otherComboId = $otherTimeInput.attr('data-combo-id');
                                        const $comboItem = $('.selected-combo-display[data-combo-id="' + otherComboId + '"]');
                                        duration = parseInt($comboItem.attr('data-combo-duration')) || 60;
                                    } else {
                                        duration = 60; // Default
                                    }
                                }
                                
                                // Parse thời gian bắt đầu (HH:mm)
                                const [hours, minutes] = otherTime.split(':').map(Number);
                                const startMinutes = hours * 60 + minutes;
                                
                                // Tính thời gian kết thúc (phút)
                                const endMinutes = startMinutes + duration;
                                
                                // Lưu khoảng thời gian đã sử dụng
                                usedTimeRanges.push({
                                    start: startMinutes,
                                    end: endMinutes,
                                    startTime: otherTime,
                                    endTime: String(Math.floor(endMinutes / 60)).padStart(2, '0') + ':' + String(endMinutes % 60).padStart(2, '0')
                                });
                            }
                        });
                        
                        // Sort time slots by time
                        let sortedSlots = response.time_slots.sort(function(a, b) {
                            return a.time.localeCompare(b.time);
                        });
                        
                        // Filter time slots: chỉ hiển thị những slot >= thời gian kết thúc lớn nhất
                        if (usedTimeRanges.length > 0) {
                            // Tìm thời gian kết thúc lớn nhất (dịch vụ cuối cùng kết thúc) - tính bằng phút
                            let maxEndMinutes = 0;
                            usedTimeRanges.forEach(function(range) {
                                if (range.end > maxEndMinutes) {
                                    maxEndMinutes = range.end;
                                }
                            });
                            
                            // Debug log
                            console.log('Filter time slots - usedTimeRanges:', usedTimeRanges);
                            console.log('Filter time slots - maxEndMinutes:', maxEndMinutes, '(', String(Math.floor(maxEndMinutes / 60)).padStart(2, '0') + ':' + String(maxEndMinutes % 60).padStart(2, '0'), ')');
                            
                            // Filter chỉ hiển thị slot >= thời gian kết thúc lớn nhất (so sánh bằng phút)
                            const beforeFilter = sortedSlots.length;
                            sortedSlots = sortedSlots.filter(function(slot) {
                                // Parse thời gian slot thành phút
                                const [slotHours, slotMinutes] = slot.time.split(':').map(Number);
                                const slotStartMinutes = slotHours * 60 + slotMinutes;
                                
                                // Slot hợp lệ nếu bắt đầu >= thời gian kết thúc lớn nhất
                                const isValid = slotStartMinutes >= maxEndMinutes;
                                if (!isValid) {
                                    console.log('Filtered out slot:', slot.time, '(', slotStartMinutes, 'minutes) because it is <', maxEndMinutes, 'minutes');
                                }
                                return isValid;
                            });
                            console.log('Filter time slots - before:', beforeFilter, 'after:', sortedSlots.length);
                        }
                        
                        $timeSlotSlider.empty();
                        
                        // Tính toán số cột và hàng
                        const fixedColumns = 11;
                        const totalSlots = sortedSlots.length;
                        const fixedRowsPerPage = Math.ceil(totalSlots / fixedColumns);
                        const slotsPerPage = fixedColumns * fixedRowsPerPage;
                        
                        let currentPage = null;
                        let slotIndex = 0;
                        
                        sortedSlots.forEach(function(slot) {
                            // Create new page if needed
                            if (slotIndex % slotsPerPage === 0) {
                                currentPage = $('<div></div>').addClass('time-slot-page');
                                $timeSlotSlider.append(currentPage);
                            }
                            
                            const isAvailable = slot.available !== false;
                            const formattedTime = formatTimeSlot(slot.time);
                            const isSelected = currentlySelectedTime === slot.time;
                            
                            const btn = $('<button></button>')
                                .attr('type', 'button')
                                .addClass('time-slot-btn service-time-slot-btn')
                                .attr('data-time', slot.time)
                                .attr('data-word-time-id', slot.word_time_id)
                                .text(formattedTime);
                            
                            if (!isAvailable) {
                                btn.addClass('unavailable');
                            }
                            
                            if (isSelected) {
                                btn.addClass('selected');
                            }
                            
                            currentPage.append(btn);
                            slotIndex++;
                        });
                        
                        // Fill empty slots in last page
                        const remainingSlots = slotsPerPage - (slotIndex % slotsPerPage);
                        if (remainingSlots < slotsPerPage && currentPage) {
                            for (let i = 0; i < remainingSlots; i++) {
                                const emptyBtn = $('<button></button>')
                                    .addClass('time-slot-btn empty-slot')
                                    .css({
                                        'visibility': 'hidden',
                                        'pointer-events': 'none',
                                        'border': 'none'
                                    });
                                currentPage.append(emptyBtn);
                            }
                        }
                        
                        $timeSlotContainer.show();
                        $timeSlotMessage.hide();
                        
                        // QUAN TRỌNG: Không cập nhật display container khi reload time slots
                        // Display container chỉ được cập nhật khi người dùng click chọn giờ
                        // Không cập nhật ở đây để tránh cập nhật nhầm container của dịch vụ khác
                        
                        // Gọi callback nếu có (để tự động chọn giờ sau khi load xong)
                        if (callback && typeof callback === 'function') {
                            // Delay để đảm bảo DOM đã render đầy đủ các time slot buttons
                            setTimeout(callback, 500);
                        }
                    } else {
                        $timeSlotContainer.hide();
                        $timeSlotMessage.text('Không có khung giờ khả dụng').show();
                        
                        // Gọi callback ngay cả khi không có time slots
                        if (callback && typeof callback === 'function') {
                            setTimeout(callback, 100);
                        }
                    }
                },
                error: function(xhr) {
                    console.error('Error loading time slots:', xhr);
                    $timeSlotContainer.hide();
                    $timeSlotMessage.text('Có lỗi xảy ra khi tải khung giờ').show();
                }
            });
        }
        
        // Event handler khi chọn ngày cho từng dịch vụ
        $(document).on('change', '.service-date-picker', function() {
            const $datePicker = $(this);
            const appointmentDate = $datePicker.val();
            const employeeId = $datePicker.attr('data-employee-id');
            const timeInputSelector = $datePicker.attr('data-time-input-selector');
            const serviceType = $datePicker.attr('data-service-type');
            
            // Lưu ngày vào hidden input
            let $dateInput = null;
            if (serviceType === 'service') {
                const serviceId = $datePicker.attr('data-service-id');
                $dateInput = $('.service-date-input[data-service-type="service"][data-service-id="' + serviceId + '"]');
            } else if (serviceType === 'variant') {
                const variantId = $datePicker.attr('data-variant-id');
                $dateInput = $('.service-date-input[data-service-type="variant"][data-variant-id="' + variantId + '"]');
            } else if (serviceType === 'combo') {
                const comboId = $datePicker.attr('data-combo-id');
                $dateInput = $('.service-date-input[data-service-type="combo"][data-combo-id="' + comboId + '"]');
            }
            
            if ($dateInput && $dateInput.length) {
                $dateInput.val(appointmentDate);
            }
            
            if (appointmentDate && employeeId && timeInputSelector) {
                // Tìm time input tương ứng
                let $timeInput = null;
                if (serviceType === 'service') {
                    const serviceId = $datePicker.attr('data-service-id');
                    $timeInput = $('.service-time-input[data-service-type="service"][data-service-id="' + serviceId + '"]');
                } else if (serviceType === 'variant') {
                    const variantId = $datePicker.attr('data-variant-id');
                    $timeInput = $('.service-time-input[data-service-type="variant"][data-variant-id="' + variantId + '"]');
                } else if (serviceType === 'combo') {
                    const comboId = $datePicker.attr('data-combo-id');
                    $timeInput = $('.service-time-input[data-service-type="combo"][data-combo-id="' + comboId + '"]');
                }
                
                // Load time slots cho dịch vụ này
                loadTimeSlotsForService($datePicker, appointmentDate, employeeId, timeInputSelector);
            }
        });
        
        // Event handler khi chọn time slot cho từng dịch vụ
        $(document).on('click', '.service-time-slot-btn', function(e) {
            console.log('🔵 CLICKED ON TIME SLOT BUTTON');
            e.preventDefault();
            e.stopPropagation();
            
            if ($(this).hasClass('unavailable')) {
                console.log('Button is unavailable, returning');
                return false;
            }
            
            const time = $(this).attr('data-time');
            const wordTimeId = $(this).attr('data-word-time-id');
            const formattedTime = formatTimeSlot(time);
            
            console.log('Time slot clicked:', time, 'formatted:', formattedTime);
            
            // Tìm time input và date input tương ứng
            const $timeContainer = $(this).closest('.service-time-container');
            const $datePicker = $timeContainer.find('.service-date-picker');
            const serviceType = $datePicker.attr('data-service-type');
            let $timeInput = null;
            let $dateInput = null;
            let displayContainerId = null;
            
            // QUAN TRỌNG: Tìm đúng input và container dựa trên date picker trong cùng time container
            // KHÔNG dùng .first() khi tìm toàn cục để tránh lấy nhầm input của dịch vụ khác
            if (!serviceType) {
                console.error('❌ Cannot find service type');
                return false;
            }
            
            if (serviceType === 'service') {
                const serviceId = $datePicker.attr('data-service-id');
                console.log('🔍 Finding inputs for service ID:', serviceId);
                
                // Tìm input trong cùng container trước
                $timeInput = $timeContainer.find('.service-time-input[data-service-type="service"][data-service-id="' + serviceId + '"]');
                console.log('Time input found in container:', $timeInput.length);
                
                // Nếu không tìm thấy trong container, tìm toàn cục nhưng PHẢI đúng service ID
                if (!$timeInput.length) {
                    // Tìm bằng name attribute để đảm bảo chính xác
                    $timeInput = $('input[name="service_time[service_' + serviceId + ']"].service-time-input[data-service-type="service"][data-service-id="' + serviceId + '"]');
                    console.log('Time input found by name:', $timeInput.length, 'name="service_time[service_' + serviceId + ']"');
                }
                
                $dateInput = $timeContainer.find('.service-date-input[data-service-type="service"][data-service-id="' + serviceId + '"]');
                if (!$dateInput.length) {
                    $dateInput = $('input[name="service_date[service_' + serviceId + ']"].service-date-input[data-service-type="service"][data-service-id="' + serviceId + '"]');
                }
                
                displayContainerId = 'service_time_display_service_' + serviceId;
                console.log('Display container ID:', displayContainerId);
            } else if (serviceType === 'variant') {
                const variantId = $datePicker.attr('data-variant-id');
                console.log('🔍 Finding inputs for variant ID:', variantId);
                $timeInput = $timeContainer.find('.service-time-input[data-service-type="variant"][data-variant-id="' + variantId + '"]');
                if (!$timeInput.length) {
                    $timeInput = $('input[name="service_time[variant_' + variantId + ']"].service-time-input[data-service-type="variant"][data-variant-id="' + variantId + '"]');
                }
                $dateInput = $timeContainer.find('.service-date-input[data-service-type="variant"][data-variant-id="' + variantId + '"]');
                if (!$dateInput.length) {
                    $dateInput = $('input[name="service_date[variant_' + variantId + ']"].service-date-input[data-service-type="variant"][data-variant-id="' + variantId + '"]');
                }
                displayContainerId = 'service_time_display_variant_' + variantId;
                console.log('Display container ID:', displayContainerId);
            } else if (serviceType === 'combo') {
                const comboId = $datePicker.attr('data-combo-id');
                console.log('🔍 Finding inputs for combo ID:', comboId);
                $timeInput = $timeContainer.find('.service-time-input[data-service-type="combo"][data-combo-id="' + comboId + '"]');
                if (!$timeInput.length) {
                    $timeInput = $('input[name="service_time[combo_' + comboId + ']"].service-time-input[data-service-type="combo"][data-combo-id="' + comboId + '"]');
                }
                $dateInput = $timeContainer.find('.service-date-input[data-service-type="combo"][data-combo-id="' + comboId + '"]');
                if (!$dateInput.length) {
                    $dateInput = $('input[name="service_date[combo_' + comboId + ']"].service-date-input[data-service-type="combo"][data-combo-id="' + comboId + '"]');
                }
                displayContainerId = 'service_time_display_combo_' + comboId;
                console.log('Display container ID:', displayContainerId);
            }
            
            console.log('=== TIME SLOT CLICK DEBUG ===');
            console.log('serviceType:', serviceType);
            console.log('serviceId/variantId/comboId:', serviceType === 'service' ? $datePicker.attr('data-service-id') : (serviceType === 'variant' ? $datePicker.attr('data-variant-id') : $datePicker.attr('data-combo-id')));
            console.log('$timeInput found:', $timeInput && $timeInput.length ? 'YES (' + $timeInput.length + ')' : 'NO');
            console.log('$dateInput found:', $dateInput && $dateInput.length ? 'YES (' + $dateInput.length + ')' : 'NO');
            console.log('displayContainerId:', displayContainerId);
            if ($timeInput && $timeInput.length) {
                console.log('Current $timeInput value BEFORE update:', $timeInput.val());
                console.log('$timeInput data-service-id:', $timeInput.attr('data-service-id'));
                console.log('$timeInput data-variant-id:', $timeInput.attr('data-variant-id'));
                console.log('$timeInput data-combo-id:', $timeInput.attr('data-combo-id'));
            }
            
            if ($timeInput && $timeInput.length) {
                // QUAN TRỌNG: Kiểm tra xem $timeInput có đúng với service/variant/combo đang chọn không
                // Đặc biệt quan trọng với combo để tránh cập nhật nhầm index 0
                let isValidInput = false;
                if (serviceType === 'service') {
                    const serviceId = $datePicker.attr('data-service-id');
                    const inputServiceId = $timeInput.attr('data-service-id');
                    if (String(serviceId) === String(inputServiceId)) {
                        isValidInput = true;
                    } else {
                        console.error('❌ Service ID mismatch. Date picker service ID:', serviceId, 'Time input service ID:', inputServiceId);
                    }
                } else if (serviceType === 'variant') {
                    const variantId = $datePicker.attr('data-variant-id');
                    const inputVariantId = $timeInput.attr('data-variant-id');
                    if (String(variantId) === String(inputVariantId)) {
                        isValidInput = true;
                    } else {
                        console.error('❌ Variant ID mismatch. Date picker variant ID:', variantId, 'Time input variant ID:', inputVariantId);
                    }
                } else if (serviceType === 'combo') {
                    const comboId = $datePicker.attr('data-combo-id');
                    const inputComboId = $timeInput.attr('data-combo-id');
                    if (String(comboId) === String(inputComboId)) {
                        isValidInput = true;
                    } else {
                        console.error('❌ Combo ID mismatch. Date picker combo ID:', comboId, 'Time input combo ID:', inputComboId);
                    }
                }
                
                if (!isValidInput) {
                    console.error('❌ Input validation failed. Not updating time slot to prevent wrong service update.');
                    return false;
                }
                
                const appointmentDate = $dateInput.val();
                console.log('appointmentDate:', appointmentDate);
                
                // Kiểm tra appointmentDate
                if (!appointmentDate) {
                    console.error('ERROR: appointmentDate is empty!', {
                        dateInput: $dateInput,
                        dateInputVal: $dateInput.val(),
                        serviceType: serviceType
                    });
                    alert('Vui lòng chọn ngày đặt lịch trước khi chọn giờ!');
                    return false;
                }
                
                // Bọc toàn bộ validation trong try-catch để bắt lỗi
                try {
                
                // Lấy duration của dịch vụ đang chọn giờ
                let duration = parseInt($timeInput.attr('data-duration'));
                
                // Nếu không có trong input hoặc = 0, tìm từ service item
                if (!duration || duration === 0 || isNaN(duration)) {
                    if (serviceType === 'service') {
                        const serviceId = $datePicker.attr('data-service-id');
                        const $serviceItem = $('.selected-service-display[data-service-id="' + serviceId + '"]');
                        duration = parseInt($serviceItem.attr('data-service-duration')) || 60;
                    } else if (serviceType === 'variant') {
                        const variantId = $datePicker.attr('data-variant-id');
                        const $variantItem = $('.selected-variant-display[data-variant-id="' + variantId + '"]');
                        duration = parseInt($variantItem.attr('data-variant-duration')) || 60;
                    } else if (serviceType === 'combo') {
                        const comboId = $datePicker.attr('data-combo-id');
                        const $comboItem = $('.selected-combo-display[data-combo-id="' + comboId + '"]');
                        duration = parseInt($comboItem.attr('data-combo-duration')) || 60;
                    } else {
                        duration = 60; // Default
                    }
                }
                
                // Parse thời gian bắt đầu (HH:mm)
                const [hours, minutes] = time.split(':').map(Number);
                const startMinutes = hours * 60 + minutes;
                
                // Tính thời gian kết thúc (phút)
                const endMinutes = startMinutes + duration;
                
                // Chuyển về giờ:phút
                const endHours = Math.floor(endMinutes / 60);
                const endMins = endMinutes % 60;
                const endTimeStr = String(endHours).padStart(2, '0') + ':' + String(endMins).padStart(2, '0');
                
                // Kiểm tra xem có trùng với dịch vụ khác không
                let hasConflict = false;
                let conflictServiceName = '';
                let conflictDetails = '';
                
                // Debug log
                console.log('=== CHECKING TIME CONFLICT ===');
                console.log('Selected time:', time, 'on date:', appointmentDate);
                console.log('Current service duration:', duration, 'minutes');
                console.log('Time range:', time, '-', endTimeStr, '(', startMinutes, 'to', endMinutes, 'minutes)');
                console.log('Total service-time-input elements:', $('.service-time-input').length);
                
                // Debug: In ra tất cả time inputs và giá trị của chúng
                console.log('--- ALL TIME INPUTS ---');
                $('.service-time-input').each(function(index) {
                    const $input = $(this);
                    const timeVal = $input.val();
                    const serviceType = $input.attr('data-service-type');
                    const serviceId = $input.attr('data-service-id');
                    const variantId = $input.attr('data-variant-id');
                    const comboId = $input.attr('data-combo-id');
                    console.log(`Time input ${index + 1}:`, {
                        time: timeVal,
                        serviceType: serviceType,
                        serviceId: serviceId,
                        variantId: variantId,
                        comboId: comboId,
                        isCurrent: $input[0] === $timeInput[0]
                    });
                });
                
                // Kiểm tra từ time inputs
                let checkedCount = 0;
                $('.service-time-input').each(function() {
                    checkedCount++;
                    const $otherTimeInput = $(this);
                    
                    // Bỏ qua chính dịch vụ đang chọn giờ
                    if ($otherTimeInput[0] === $timeInput[0]) {
                        console.log('Skipping current service input');
                        return true; // continue
                    }
                    
                    const otherTime = $otherTimeInput.val();
                    console.log('Checking other service:', {
                        otherTime: otherTime,
                        serviceType: $otherTimeInput.attr('data-service-type'),
                        serviceId: $otherTimeInput.attr('data-service-id'),
                        variantId: $otherTimeInput.attr('data-variant-id'),
                        comboId: $otherTimeInput.attr('data-combo-id')
                    });
                    
                    if (!otherTime || otherTime.trim() === '') {
                        console.log('Other service has no time selected, skipping');
                        return true; // continue - dịch vụ này chưa chọn giờ
                    }
                    
                    // Tìm date input tương ứng với time input này
                    const otherServiceType = $otherTimeInput.attr('data-service-type');
                    let $otherDateInput = null;
                    
                    if (otherServiceType === 'service') {
                        const otherServiceId = $otherTimeInput.attr('data-service-id');
                        $otherDateInput = $('.service-date-input[data-service-type="service"][data-service-id="' + otherServiceId + '"]');
                        console.log('Looking for date input for service:', otherServiceId, 'Found:', $otherDateInput.length);
                    } else if (otherServiceType === 'variant') {
                        const otherVariantId = $otherTimeInput.attr('data-variant-id');
                        $otherDateInput = $('.service-date-input[data-service-type="variant"][data-variant-id="' + otherVariantId + '"]');
                        console.log('Looking for date input for variant:', otherVariantId, 'Found:', $otherDateInput.length);
                    } else if (otherServiceType === 'combo') {
                        const otherComboId = $otherTimeInput.attr('data-combo-id');
                        $otherDateInput = $('.service-date-input[data-service-type="combo"][data-combo-id="' + otherComboId + '"]');
                        console.log('Looking for date input for combo:', otherComboId, 'Found:', $otherDateInput.length);
                    }
                    
                    if (!$otherDateInput || !$otherDateInput.length) {
                        console.log('Could not find date input for other service, skipping');
                        return true; // continue - không tìm thấy date input
                    }
                    
                    const otherDate = $otherDateInput.val();
                    
                    // Normalize dates để so sánh (chuyển về format YYYY-MM-DD)
                    const normalizeDate = function(dateStr) {
                        if (!dateStr) return '';
                        // Nếu là format DD/MM/YYYY, chuyển về YYYY-MM-DD
                        if (dateStr.includes('/')) {
                            const parts = dateStr.split('/');
                            if (parts.length === 3) {
                                // parts[0] = DD, parts[1] = MM, parts[2] = YYYY
                                const day = parts[0].padStart(2, '0');
                                const month = parts[1].padStart(2, '0');
                                const year = parts[2];
                                return year + '-' + month + '-' + day;
                            }
                        }
                        // Nếu đã là format YYYY-MM-DD, giữ nguyên
                        return dateStr;
                    };
                    
                    const normalizedAppointmentDate = normalizeDate(appointmentDate);
                    const normalizedOtherDate = normalizeDate(otherDate);
                    
                    console.log('Other service date:', otherDate, '-> normalized:', normalizedOtherDate);
                    console.log('Current appointment date:', appointmentDate, '-> normalized:', normalizedAppointmentDate);
                    console.log('Date match:', normalizedOtherDate === normalizedAppointmentDate);
                    
                    // Nếu dịch vụ khác đã chọn giờ và cùng ngày
                    if (otherTime && otherDate && normalizedOtherDate === normalizedAppointmentDate) {
                        console.log('✓ Found other service with time:', otherTime, 'on date:', otherDate, '- Checking for conflict...');
                        // Lấy duration của dịch vụ khác
                        let otherDuration = parseInt($otherTimeInput.attr('data-duration'));
                        
                        // Nếu không có trong input hoặc = 0, tìm từ service item
                        if (!otherDuration || otherDuration === 0 || isNaN(otherDuration)) {
                            const otherServiceType = $otherTimeInput.attr('data-service-type');
                            if (otherServiceType === 'service') {
                                const otherServiceId = $otherTimeInput.attr('data-service-id');
                                const $serviceItem = $('.selected-service-display[data-service-id="' + otherServiceId + '"]');
                                otherDuration = parseInt($serviceItem.attr('data-service-duration')) || 60;
                            } else if (otherServiceType === 'variant') {
                                const otherVariantId = $otherTimeInput.attr('data-variant-id');
                                const $variantItem = $('.selected-variant-display[data-variant-id="' + otherVariantId + '"]');
                                otherDuration = parseInt($variantItem.attr('data-variant-duration')) || 60;
                            } else if (otherServiceType === 'combo') {
                                const otherComboId = $otherTimeInput.attr('data-combo-id');
                                const $comboItem = $('.selected-combo-display[data-combo-id="' + otherComboId + '"]');
                                otherDuration = parseInt($comboItem.attr('data-combo-duration')) || 60;
                            } else {
                                otherDuration = 60; // Default
                            }
                        }
                        
                        // Parse thời gian bắt đầu của dịch vụ khác
                        const [otherHours, otherMinutes] = otherTime.split(':').map(Number);
                        const otherStartMinutes = otherHours * 60 + otherMinutes;
                        
                        // Tính thời gian kết thúc của dịch vụ khác
                        const otherEndMinutes = otherStartMinutes + otherDuration;
                        
                        // Kiểm tra xem có trùng không
                        // Trùng nếu có overlap giữa 2 khoảng thời gian:
                        // - Dịch vụ mới bắt đầu trước khi dịch vụ khác kết thúc: startMinutes < otherEndMinutes
                        // - Dịch vụ mới kết thúc sau khi dịch vụ khác bắt đầu: endMinutes > otherStartMinutes
                        // Dịch vụ sau phải bắt đầu >= thời gian kết thúc của dịch vụ trước
                        console.log('--- COMPARING TIME RANGES ---');
                        console.log('Current service:', time, '(', startMinutes, 'to', endMinutes, 'minutes)');
                        console.log('Other service:', otherTime, '(', otherStartMinutes, 'to', otherEndMinutes, 'minutes)');
                        console.log('Check 1: startMinutes < otherEndMinutes?', startMinutes, '<', otherEndMinutes, '=', startMinutes < otherEndMinutes);
                        console.log('Check 2: endMinutes > otherStartMinutes?', endMinutes, '>', otherStartMinutes, '=', endMinutes > otherStartMinutes);
                        
                        // Kiểm tra overlap: có trùng nếu 2 khoảng thời gian giao nhau
                        const hasOverlap = (startMinutes < otherEndMinutes && endMinutes > otherStartMinutes);
                        console.log('HAS OVERLAP?', hasOverlap);
                        
                        if (hasOverlap) {
                            console.log('🚨 CONFLICT DETECTED! 🚨');
                            hasConflict = true;
                            
                            // Lấy tên dịch vụ để hiển thị trong thông báo
                            const otherServiceType = $otherTimeInput.attr('data-service-type');
                            if (otherServiceType === 'service') {
                                const otherServiceId = $otherTimeInput.attr('data-service-id');
                                const $serviceDisplay = $('.selected-service-display[data-service-id="' + otherServiceId + '"]');
                                conflictServiceName = $serviceDisplay.find('span:first').text().trim() || 'dịch vụ khác';
                            } else if (otherServiceType === 'variant') {
                                const otherVariantId = $otherTimeInput.attr('data-variant-id');
                                const $variantDisplay = $('.selected-variant-display[data-variant-id="' + otherVariantId + '"]');
                                conflictServiceName = $variantDisplay.find('span:first').text().trim() || 'dịch vụ khác';
                            } else if (otherServiceType === 'combo') {
                                const otherComboId = $otherTimeInput.attr('data-combo-id');
                                const $comboDisplay = $('.selected-combo-display[data-combo-id="' + otherComboId + '"]');
                                conflictServiceName = $comboDisplay.find('span:first').text().trim() || 'dịch vụ khác';
                            }
                            
                            // Tính thời gian kết thúc để hiển thị trong thông báo
                            const otherEndHours = Math.floor(otherEndMinutes / 60);
                            const otherEndMins = otherEndMinutes % 60;
                            const otherEndTimeStr = String(otherEndHours).padStart(2, '0') + ':' + String(otherEndMins).padStart(2, '0');
                            
                            // Tính thời gian kết thúc của dịch vụ đang chọn
                            const currentEndHours = Math.floor(endMinutes / 60);
                            const currentEndMins = endMinutes % 60;
                            const currentEndTimeStr = String(currentEndHours).padStart(2, '0') + ':' + String(currentEndMins).padStart(2, '0');
                            
                            // Tạo thông báo chi tiết
                            conflictDetails = '⚠️ KHÔNG THỂ CHỌN GIỜ NÀY!\n\n' +
                                'Giờ bạn chọn (' + formattedTime + ' - ' + currentEndTimeStr + ') trùng với thời gian thực hiện dịch vụ:\n' +
                                '📋 "' + conflictServiceName + '"\n' +
                                '⏰ Thời gian: ' + otherTime + ' - ' + otherEndTimeStr + '\n\n' +
                                '✅ Vui lòng chọn giờ từ ' + otherEndTimeStr + ' trở đi.';
                            
                            console.log('CONFLICT DETECTED:', conflictServiceName, 'from', otherTime, 'to', otherEndTimeStr, 'conflicts with', formattedTime, 'to', currentEndTimeStr);
                            return false; // break
                        }
                    }
                });
                
                console.log('Total time inputs checked:', checkedCount);
                console.log('=== VALIDATION RESULT ===');
                console.log('hasConflict:', hasConflict);
                console.log('conflictServiceName:', conflictServiceName);
                console.log('conflictDetails:', conflictDetails);
                
                // Nếu có trùng, hiển thị thông báo và không cho lưu
                if (hasConflict) {
                    const message = conflictDetails || ('Không thể chọn giờ này! Giờ bạn chọn trùng với thời gian thực hiện dịch vụ "' + conflictServiceName + '". Vui lòng chọn giờ sau khi dịch vụ đó kết thúc.');
                    console.log('🚫 BLOCKING: Showing alert and preventing save');
                    alert(message);
                    console.log('BLOCKED: Time conflict detected');
                    return false;
                }
                
                console.log('✅ VALID: No time conflict, saving time slot');
                
                // Lưu time slot vào ĐÚNG input của dịch vụ này
                // QUAN TRỌNG: Đảm bảo chỉ lưu vào đúng input của dịch vụ được chọn
                if ($timeInput && $timeInput.length) {
                    // Xác nhận lại rằng đây là input đúng bằng cách so sánh service ID
                    let isValidInput = false;
                    if (serviceType === 'service') {
                        const serviceId = $datePicker.attr('data-service-id');
                        const inputServiceId = $timeInput.attr('data-service-id');
                        isValidInput = serviceId === inputServiceId;
                    } else if (serviceType === 'variant') {
                        const variantId = $datePicker.attr('data-variant-id');
                        const inputVariantId = $timeInput.attr('data-variant-id');
                        isValidInput = variantId === inputVariantId;
                    } else if (serviceType === 'combo') {
                        const comboId = $datePicker.attr('data-combo-id');
                        const inputComboId = $timeInput.attr('data-combo-id');
                        isValidInput = comboId === inputComboId;
                    }
                    
                    if (isValidInput) {
                        // QUAN TRỌNG: Kiểm tra lại một lần nữa trước khi lưu
                        // Đảm bảo rằng đây là input đúng bằng cách kiểm tra name attribute
                        let finalInputCheck = false;
                        if (serviceType === 'service') {
                            const serviceId = $datePicker.attr('data-service-id');
                            const inputName = $timeInput.attr('name');
                            const expectedName = 'service_time[service_' + serviceId + ']';
                            finalInputCheck = inputName === expectedName && $timeInput.attr('data-service-id') === serviceId;
                            console.log('Final input check:', {
                                inputName: inputName,
                                expectedName: expectedName,
                                serviceId: serviceId,
                                inputServiceId: $timeInput.attr('data-service-id'),
                                finalInputCheck: finalInputCheck
                            });
                        } else if (serviceType === 'variant') {
                            const variantId = $datePicker.attr('data-variant-id');
                            const inputName = $timeInput.attr('name');
                            const expectedName = 'service_time[variant_' + variantId + ']';
                            finalInputCheck = inputName === expectedName && $timeInput.attr('data-variant-id') === variantId;
                        } else if (serviceType === 'combo') {
                            const comboId = $datePicker.attr('data-combo-id');
                            const inputName = $timeInput.attr('name');
                            const expectedName = 'service_time[combo_' + comboId + ']';
                            finalInputCheck = inputName === expectedName && $timeInput.attr('data-combo-id') === comboId;
                        }
                        
                        if (finalInputCheck) {
                            // Lưu time vào input
                            $timeInput.val(time);
                            console.log('✅ Saved time to input:', {
                                selector: timeInputSelector || 'N/A',
                                serviceType: serviceType,
                                serviceId: serviceType === 'service' ? $datePicker.attr('data-service-id') : (serviceType === 'variant' ? $datePicker.attr('data-variant-id') : $datePicker.attr('data-combo-id')),
                                time: time,
                                inputValue: $timeInput.val(),
                                inputName: $timeInput.attr('name')
                            });
                        } else {
                            console.error('❌ Final input check failed! Not saving to prevent wrong service update.');
                            console.error('Input name:', $timeInput.attr('name'), 'Service type:', serviceType);
                            // KHÔNG cập nhật display container nếu input check fail
                            displayContainerId = null;
                        }
                    } else {
                        console.error('❌ Invalid time input! Service ID mismatch:', {
                            serviceType: serviceType,
                            datePickerId: serviceType === 'service' ? $datePicker.attr('data-service-id') : (serviceType === 'variant' ? $datePicker.attr('data-variant-id') : $datePicker.attr('data-combo-id')),
                            inputId: serviceType === 'service' ? $timeInput.attr('data-service-id') : (serviceType === 'variant' ? $timeInput.attr('data-variant-id') : $timeInput.attr('data-combo-id'))
                        });
                        // KHÔNG cập nhật display container nếu input check fail
                        displayContainerId = null;
                    }
                } else {
                    console.error('❌ Time input not found!', {
                        serviceType: serviceType,
                        selector: timeInputSelector || 'N/A'
                    });
                    // KHÔNG cập nhật display container nếu không tìm thấy input
                    displayContainerId = null;
                }
                
                // CHỈ cập nhật selected class và display container nếu input đã được lưu thành công
                if (displayContainerId) {
                    // Xóa selected của tất cả time slots trong container này
                    $timeContainer.find('.service-time-slot-btn').removeClass('selected');
                    
                    // Thêm selected cho time slot được chọn
                    $(this).addClass('selected');
                } else {
                    // Nếu input check fail, không cập nhật selected class
                    console.error('❌ Skipping display update because input validation failed');
                    return false;
                }
                
                // Hiển thị time slot cạnh tên nhân viên - CHỈ cập nhật container của dịch vụ này
                // QUAN TRỌNG: Đảm bảo chỉ cập nhật đúng container của dịch vụ được chọn
                // ĐẶC BIỆT: KHÔNG BAO GIỜ cập nhật container của dịch vụ khác (như index 0)
                if (displayContainerId) {
                    console.log('🔍 Looking for display container:', displayContainerId);
                    console.log('🔍 Current service being updated:', {
                        serviceType: serviceType,
                        serviceId: serviceType === 'service' ? $datePicker.attr('data-service-id') : (serviceType === 'variant' ? $datePicker.attr('data-variant-id') : $datePicker.attr('data-combo-id')),
                        timeContainerServiceId: serviceType === 'service' ? $timeContainer.find('.service-date-picker').attr('data-service-id') : null
                    });
                    
                    // Tìm display container bằng ID chính xác - CHỈ tìm 1 container duy nhất
                    const $displayContainer = $('#' + displayContainerId);
                    console.log('Found display containers:', $displayContainer.length);
                    
                    // QUAN TRỌNG: Kiểm tra ngay từ đầu - nếu container này không thuộc về service đang chọn, KHÔNG cập nhật
                    if ($displayContainer.length > 0) {
                        const $containerParent = $displayContainer.closest('.service-item-selectable');
                        const containerParentServiceId = $containerParent.attr('data-service-id');
                        const currentServiceIdForCheck = serviceType === 'service' ? $datePicker.attr('data-service-id') : null;
                        
                        if (currentServiceIdForCheck && containerParentServiceId && String(currentServiceIdForCheck) !== String(containerParentServiceId)) {
                            console.error('🚫 BLOCKED: Attempted to update wrong service container!');
                            console.error('Current service ID:', currentServiceIdForCheck, 'Container parent service ID:', containerParentServiceId);
                            console.error('This would update service:', containerParentServiceId, 'but we are selecting time for service:', currentServiceIdForCheck);
                            return false; // DỪNG NGAY - không cập nhật container sai
                        }
                    }
                    
                    if ($displayContainer.length === 1) {
                        // QUAN TRỌNG: Kiểm tra lại một lần nữa - container này phải thuộc về đúng service
                        const $containerParentCheck = $displayContainer.closest('.service-item-selectable');
                        const containerParentServiceIdCheck = $containerParentCheck.attr('data-service-id');
                        const currentServiceIdForValidation = serviceType === 'service' ? $datePicker.attr('data-service-id') : null;
                        
                        if (currentServiceIdForValidation && containerParentServiceIdCheck && String(currentServiceIdForValidation) !== String(containerParentServiceIdCheck)) {
                            console.error('🚫 BLOCKED AT VALIDATION: Container belongs to different service!');
                            console.error('Current service ID:', currentServiceIdForValidation, 'Container parent service ID:', containerParentServiceIdCheck);
                            return false; // DỪNG NGAY - không tiếp tục validation
                        }
                        
                        // Xác nhận lại rằng đây là container đúng bằng cách kiểm tra service ID
                        let isValidContainer = false;
                        let expectedId = '';
                        let currentServiceId = null;
                        
                        if (serviceType === 'service') {
                            currentServiceId = $datePicker.attr('data-service-id');
                            expectedId = 'service_time_display_service_' + currentServiceId;
                            isValidContainer = displayContainerId === expectedId;
                        } else if (serviceType === 'variant') {
                            currentServiceId = $datePicker.attr('data-variant-id');
                            expectedId = 'service_time_display_variant_' + currentServiceId;
                            isValidContainer = displayContainerId === expectedId;
                        } else if (serviceType === 'combo') {
                            currentServiceId = $datePicker.attr('data-combo-id');
                            expectedId = 'service_time_display_combo_' + currentServiceId;
                            isValidContainer = displayContainerId === expectedId;
                        }
                        
                        console.log('Container validation:', {
                            displayContainerId: displayContainerId,
                            expectedId: expectedId,
                            isValidContainer: isValidContainer,
                            currentServiceId: currentServiceId
                        });
                        
                        if (isValidContainer) {
                            // QUAN TRỌNG: Kiểm tra lại một lần nữa rằng đây là container đúng
                            // Bằng cách kiểm tra xem container này có thuộc về đúng service ID không
                            let isCorrectContainer = false;
                            let parentServiceId = null;
                            
                            if (serviceType === 'service') {
                                // Kiểm tra xem container này có nằm trong service item đúng không
                                const $parentServiceItem = $displayContainer.closest('.service-item-selectable[data-service-id="' + currentServiceId + '"]');
                                isCorrectContainer = $parentServiceItem.length > 0;
                                parentServiceId = $parentServiceItem.length > 0 ? $parentServiceItem.attr('data-service-id') : null;
                                
                                // QUAN TRỌNG: Kiểm tra thêm bằng cách so sánh ID từ container với service ID
                                const containerIdFromElement = $displayContainer.attr('id');
                                const expectedContainerId = 'service_time_display_service_' + currentServiceId;
                                // QUAN TRỌNG: Chỉ cập nhật nếu container ID khớp CHÍNH XÁC và parent service ID khớp
                                // Điều này đảm bảo không cập nhật nhầm vào dịch vụ khác (như index 0)
                                if (containerIdFromElement === expectedContainerId && currentServiceId === parentServiceId && String(currentServiceId) === String(parentServiceId)) {
                                    isCorrectContainer = true;
                                } else {
                                    isCorrectContainer = false;
                                    console.error('❌ Container validation failed for service:', currentServiceId, {
                                        containerIdFromElement: containerIdFromElement,
                                        expectedContainerId: expectedContainerId,
                                        currentServiceId: currentServiceId,
                                        parentServiceId: parentServiceId,
                                        idsMatch: String(currentServiceId) === String(parentServiceId)
                                    });
                                }
                                
                                console.log('Parent service item check:', {
                                    found: $parentServiceItem.length > 0,
                                    currentServiceId: currentServiceId,
                                    parentServiceId: parentServiceId,
                                    containerIdFromElement: containerIdFromElement,
                                    expectedContainerId: expectedContainerId,
                                    isCorrectContainer: isCorrectContainer
                                });
                            } else if (serviceType === 'variant') {
                                const $parentServiceItem = $displayContainer.closest('.service-item-selectable[data-variant-id="' + currentServiceId + '"]');
                                const containerIdFromElement = $displayContainer.attr('id');
                                const expectedContainerId = 'service_time_display_variant_' + currentServiceId;
                                isCorrectContainer = $parentServiceItem.length > 0 && containerIdFromElement === expectedContainerId;
                            } else if (serviceType === 'combo') {
                                const $parentServiceItem = $displayContainer.closest('.service-item-selectable[data-combo-id="' + currentServiceId + '"]');
                                const containerIdFromElement = $displayContainer.attr('id');
                                const expectedContainerId = 'service_time_display_combo_' + currentServiceId;
                                isCorrectContainer = $parentServiceItem.length > 0 && containerIdFromElement === expectedContainerId;
                            }
                            
                            if (isCorrectContainer) {
                                // CHỈ cập nhật text trong container này
                                const $timeSlotText = $displayContainer.find('.time-slot-text');
                                if ($timeSlotText.length === 1) {
                                    // Lưu giá trị cũ để debug
                                    const oldValue = $timeSlotText.text();
                                    
                                    // QUAN TRỌNG: Kiểm tra lại một lần nữa trước khi cập nhật
                                    // Đảm bảo rằng đây là container đúng bằng cách kiểm tra ID và service ID
                                    const finalCheck = $displayContainer.attr('id') === displayContainerId;
                                    // QUAN TRỌNG: Kiểm tra thêm service ID từ parent để đảm bảo không cập nhật nhầm vào dịch vụ khác (như index 0)
                                    let serviceIdMatch = true;
                                    if (serviceType === 'service') {
                                        const $parentCheck = $displayContainer.closest('.service-item-selectable[data-service-id]');
                                        const parentServiceIdCheck = $parentCheck.attr('data-service-id');
                                        serviceIdMatch = String(currentServiceId) === String(parentServiceIdCheck);
                                        console.log('Final service ID check:', {
                                            currentServiceId: currentServiceId,
                                            parentServiceIdCheck: parentServiceIdCheck,
                                            match: serviceIdMatch
                                        });
                                    }
                                    
                                    if (finalCheck && serviceIdMatch) {
                                        // CHỈ cập nhật text trong container này, KHÔNG cập nhật container khác
                                        $timeSlotText.text(formattedTime);
                                        $displayContainer.fadeIn(300);
                                        console.log('✅ Updated display container:', displayContainerId, 'with time:', formattedTime, '(old:', oldValue, ')', 'for service:', serviceType, currentServiceId);
                                    } else {
                                        console.error('❌ Final check failed! Container ID or service ID mismatch. Not updating.');
                                        console.error('Expected ID:', displayContainerId, 'Actual ID:', $displayContainer.attr('id'));
                                        console.error('Service ID match:', serviceIdMatch, 'Final check:', finalCheck);
                                    }
                                } else {
                                    console.error('❌ Time slot text element not found or multiple found in container:', displayContainerId, 'Found:', $timeSlotText.length);
                                }
                            } else {
                                console.error('❌ Container validation failed! Container does not belong to correct service. Not updating to prevent wrong service update.');
                                console.error('displayContainerId:', displayContainerId, 'serviceType:', serviceType, 'currentServiceId:', currentServiceId, 'parentServiceId:', parentServiceId);
                            }
                        } else {
                            console.error('❌ Invalid display container ID! Expected:', expectedId, 'but got:', displayContainerId, 'for service:', serviceType);
                        }
                    } else if ($displayContainer.length > 1) {
                        console.error('❌ Multiple display containers found with ID:', displayContainerId, 'Count:', $displayContainer.length);
                    } else {
                        console.error('❌ Display container not found:', displayContainerId);
                    }
                } else {
                    console.error('❌ displayContainerId is null or undefined');
                }
                
                // KHÔNG tự động cập nhật giờ cho các dịch vụ khác khi chọn giờ
                // Mỗi dịch vụ phải được chọn giờ độc lập
                } catch (error) {
                    console.error('❌ ERROR in time slot validation:', error);
                    console.error('Error stack:', error.stack);
                    alert('Có lỗi xảy ra khi kiểm tra giờ. Vui lòng thử lại hoặc liên hệ hỗ trợ.');
                    return false;
                }
            } else {
                console.error('❌ ERROR: $timeInput not found!', {
                    serviceType: serviceType,
                    timeInput: $timeInput,
                    dateInput: $dateInput
                });
                alert('Không tìm thấy thông tin dịch vụ. Vui lòng thử lại.');
                return false;
            }
            
            return false;
        });
        
        // Nếu đã khôi phục employee_id và appointment_date từ localStorage, load time slots
        // KHÔNG tự động load time slots từ lần trước - để người dùng tự chọn lại
        
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
            
            // Load TẤT CẢ nhân viên (không cần kiểm tra dịch vụ)
            $.ajax({
                url: '{{ route("site.appointment.employees-by-service") }}',
                method: 'GET',
                data: {
                    // Không gửi service_id, service_variants, combo_id để lấy tất cả nhân viên
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
            
            // Load TẤT CẢ nhân viên (không filter theo dịch vụ)
            const currentEmployeeId = $('#employee_id').val();
            
            $.ajax({
                url: '{{ route("site.appointment.employees-by-service") }}',
                method: 'GET',
                data: {
                    // Không gửi service_id, service_variants, combo_id để lấy tất cả nhân viên
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
                                
                                const serviceIds = employee.service_ids || [];
                                let itemHtml = '<div class="employee-item-btn' + (isSelected ? ' selected' : '') + '" data-employee-id="' + employee.id + '" data-employee-name="' + employee.name + '" data-employee-position="' + (employee.position || '') + '" data-service-ids="' + serviceIds.join(',') + '" style="text-align: center; cursor: pointer; padding: 10px; min-width: 120px; flex-shrink: 0;">';
                                itemHtml += '<div class="employee-avatar-wrapper" style="width: 100px; height: 100px; margin: 0 auto 8px; border-radius: 50%; overflow: hidden; border: 2px solid ' + (isSelected ? '#007bff' : '#ddd') + ';">';
                                
                                if (avatarUrl) {
                                    itemHtml += '<img src="' + avatarUrl + '" alt="' + employee.name + '" style="width: 100%; height: 100%; object-fit: cover;">';
                                } else {
                                    itemHtml += '<div style="width: 100%; height: 100%; background: #f0f0f0; display: flex; align-items: center; justify-content: center;"><i class="fa fa-user" style="font-size: 40px; color: #999;"></i></div>';
                                }
                                
                                itemHtml += '</div>';
                                itemHtml += '<div class="employee-name" style="font-size: 13px; font-weight: 600; color: #000; margin-bottom: 3px;">' + employee.name + '</div>';
                                
                                itemHtml += '</div>';
                                $slider.append(itemHtml);
                            });
                            
                            // Đảm bảo không có nhân viên nào được chọn nếu chưa có dịch vụ
                            const finalServiceCount = countSelectedServices();
                            if (finalServiceCount === 0) {
                                $('.employee-item-btn').removeClass('selected');
                                $('.employee-item-btn .employee-avatar-wrapper').css('border-color', '#ddd');
                                $('#employee_id').val('');
                            }
                            
                            // Nếu employee đã chọn không còn trong danh sách, reset
                            if (currentEmployeeId && !response.employees.find(e => e.id == currentEmployeeId)) {
                                $('#employee_id').val('');
                            }
                            
                            // Reset slider position về đầu
                            $slider.css('transform', 'translateX(0px)');
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
                    
                    // Luôn xóa employees cũ từ server-side render để đảm bảo đồng bộ
                    $('.employee-slider').empty();
                    
                    // Kiểm tra xem có dịch vụ nào đang active không (khi có >= 2 dịch vụ)
                    const serviceCount = countSelectedServices();
                    if (serviceCount >= 2 && activeServiceSelector) {
                        // Nếu có service đang active, load employees cho service đó (giống như khi click vào service item)
                        const $activeServiceItem = $('.service-item-selectable.active-service');
                        if ($activeServiceItem.length > 0) {
                            // Load employees cho service đang active
                            loadEmployeesForSelectedService($activeServiceItem);
                        } else {
                            // Nếu không có service active, load employees cho tất cả dịch vụ
                            loadEmployeesForCarousel();
                        }
                    } else {
                        // Nếu chỉ có 1 dịch vụ hoặc chưa có dịch vụ, load employees cho tất cả dịch vụ
                        loadEmployeesForCarousel();
                    }
                });
            }
            
            return false;
        });
        
        
        // KHÔNG khôi phục old value của employee - xóa luôn để người dùng tự chọn lại
        $('#employee_id').val('');
        $('.employee-item-btn').removeClass('selected');
        $('.employee-item-btn .employee-avatar-wrapper').css('border-color', '#ddd');
        
        // Reset employee selection một lần nữa sau khi xử lý old value (đảm bảo không có nhân viên nào được chọn khi chưa có dịch vụ)
        resetEmployeeSelectionIfNoService();
        
        // Chạy lại sau một chút để đảm bảo tất cả code đã chạy xong
        setTimeout(function() {
            resetEmployeeSelectionIfNoService();
        }, 200);
        
        // Chạy lại sau khi tất cả AJAX requests đã hoàn thành
        setTimeout(function() {
            resetEmployeeSelectionIfNoService();
        }, 1000);
        
        // Theo dõi thay đổi của DOM và reset employee selection nếu chưa có dịch vụ
        const observer = new MutationObserver(function(mutations) {
            const serviceCount = countSelectedServices();
            if (serviceCount === 0) {
                // Kiểm tra xem có nhân viên nào đang được chọn không
                const hasSelectedEmployee = $('.employee-item-btn.selected').length > 0;
                if (hasSelectedEmployee) {
                    resetEmployeeSelectionIfNoService();
                }
            }
        });
        
        // Bắt đầu quan sát thay đổi trong body
        if (document.body) {
            observer.observe(document.body, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['class']
            });
        }
        
        // Xử lý chọn employee - đặt priority cao để chạy trước document click
        $('#employeeContainer').on('click', '.employee-item-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            const employeeId = $(this).data('employee-id');
            const employeeName = $(this).data('employee-name') || $(this).attr('data-employee-name') || '';
            const employeePosition = $(this).data('employee-position') || $(this).attr('data-employee-position') || '';
            
            if (!employeeId) {
                return false;
            }
            
            // Kiểm tra xem đã chọn dịch vụ chưa
            const serviceCount = countSelectedServices();
            if (serviceCount === 0) {
                alert('Vui lòng chọn dịch vụ trước khi chọn kỹ thuật viên.');
                return false;
            }
            
            // Cập nhật hidden input
            $('#employee_id').val(employeeId);
            
            // Debug: Log để kiểm tra
            console.log('Employee selected:', {
                employeeId: employeeId,
                employeeIdValue: $('#employee_id').val(),
                employeeName: employeeName,
                employeePosition: employeePosition
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
            
            // Nếu có >= 2 dịch vụ, gán nhân viên cho dịch vụ đang active
            if (serviceCount >= 2) {
                // Nếu chưa chọn dịch vụ nào, tự động chọn dịch vụ đầu tiên
                if (!activeServiceSelector) {
                    const $firstServiceItem = $('.service-item-selectable').first();
                    if ($firstServiceItem.length) {
                        $firstServiceItem.addClass('active-service');
                        activeServiceSelector = $firstServiceItem.attr('data-service-selector');
                    }
                }
                
                if (!activeServiceSelector) {
                    // Nếu vẫn không có dịch vụ nào, thông báo cho người dùng
                    alert('Vui lòng chọn dịch vụ để gán nhân viên trước khi chọn nhân viên.');
                    return false;
                }
                
                // Tìm input của dịch vụ đang active
                let $targetInput = null;
                
                if (activeServiceSelector.startsWith('service_')) {
                    const serviceId = activeServiceSelector.replace('service_', '');
                    $targetInput = $('.service-employee-input[data-service-type="service"][data-service-id="' + serviceId + '"]');
                } else if (activeServiceSelector.startsWith('variant_')) {
                    const variantId = activeServiceSelector.replace('variant_', '');
                    $targetInput = $('.service-employee-input[data-service-type="variant"][data-variant-id="' + variantId + '"]');
                } else if (activeServiceSelector.startsWith('combo_')) {
                    const comboId = activeServiceSelector.replace('combo_', '');
                    $targetInput = $('.service-employee-input[data-service-type="combo"][data-combo-id="' + comboId + '"]');
                }
                
                if ($targetInput && $targetInput.length) {
                    // BỎ KIỂM TRA CHUYÊN MÔN - Cho phép chọn bất kỳ nhân viên nào
                    // Gán nhân viên cho dịch vụ này
                    $targetInput.val(employeeId);
                    const displayContainerId = $targetInput.attr('data-display-container');
                    if (displayContainerId) {
                        const $displayContainer = $('#' + displayContainerId);
                        $displayContainer.find('.employee-name').text(employeeName);
                        
                        // Ẩn position - không hiển thị vị trí nữa
                        $displayContainer.find('.employee-position').hide();
                        
                        $displayContainer.fadeIn(300);
                    }
                    
                    // Giữ active để người dùng có thể chọn giờ ngay sau khi chọn nhân viên
                    // Chỉ xóa active khi chọn dịch vụ khác
                }
            }
            
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
        
        // Navigation buttons cho service time slots
        $(document).on('click', '.service-time-slot-prev', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $container = $(this).siblings('.service-time-slot-grid');
            const $slider = $container.find('.service-time-slot-slider');
            const containerWidth = $container.width();
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
        
        $(document).on('click', '.service-time-slot-next', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $container = $(this).siblings('.service-time-slot-grid');
            const $slider = $container.find('.service-time-slot-slider');
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
                
                // Load time slots
                const serviceCount = countSelectedServices();
                const hasServiceEmployeeInputs = $('.service-employee-input').length > 0;
                
                if (serviceCount >= 2 && hasServiceEmployeeInputs) {
                    // Nếu có >= 2 dịch vụ, kiểm tra xem có dịch vụ nào đã chọn nhân viên chưa
                    let hasEmployee = false;
                    $('.service-employee-input').each(function() {
                        if ($(this).val()) {
                            hasEmployee = true;
                            return false; // break
                        }
                    });
                    
                    if (hasEmployee) {
                        loadAvailableTimeSlots();
                    } else {
                        $('#time_slot_message').text('Vui lòng chọn dịch vụ và nhân viên trước').show();
                        $('.time-slot-container').hide();
                    }
                } else {
                    // Nếu chỉ có 1 dịch vụ, kiểm tra employee_id chung
                    if ($('#employee_id').val()) {
                        loadAvailableTimeSlots();
                    } else {
                        $('#time_slot_message').text('Vui lòng chọn kỹ thuật viên trước').show();
                        $('.time-slot-container').hide();
                    }
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
            // Nếu có >= 2 dịch vụ, lấy employee_id từ dịch vụ đang active
            const serviceCount = countSelectedServices();
            let employeeId = null;
            
            // Kiểm tra xem có service-employee-input không (chỉ có khi >= 2 dịch vụ)
            const hasServiceEmployeeInputs = $('.service-employee-input').length > 0;
            
            if (serviceCount >= 2 && hasServiceEmployeeInputs) {
                // Lấy employee_id từ dịch vụ đang active
                if (activeServiceSelector) {
                    let $employeeInput = null;
                    
                    if (activeServiceSelector.startsWith('service_')) {
                        const serviceId = activeServiceSelector.replace('service_', '');
                        $employeeInput = $('.service-employee-input[data-service-type="service"][data-service-id="' + serviceId + '"]');
                    } else if (activeServiceSelector.startsWith('variant_')) {
                        const variantId = activeServiceSelector.replace('variant_', '');
                        $employeeInput = $('.service-employee-input[data-service-type="variant"][data-variant-id="' + variantId + '"]');
                    } else if (activeServiceSelector.startsWith('combo_')) {
                        const comboId = activeServiceSelector.replace('combo_', '');
                        $employeeInput = $('.service-employee-input[data-service-type="combo"][data-combo-id="' + comboId + '"]');
                    }
                    
                    if ($employeeInput && $employeeInput.length) {
                        employeeId = $employeeInput.val();
                    }
                }
                
                // Nếu không có dịch vụ active, lấy dịch vụ đầu tiên có nhân viên
                if (!employeeId) {
                    $('.service-employee-input').each(function() {
                        if ($(this).val()) {
                            employeeId = $(this).val();
                            return false; // break
                        }
                    });
                }
            } else {
                // Nếu chỉ có 1 dịch vụ hoặc không có service-employee-input, dùng employee_id chung
                employeeId = $('#employee_id').val();
            }
            
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
                if (serviceCount >= 2 && hasServiceEmployeeInputs) {
                    timeSlotMessage.text('Vui lòng chọn dịch vụ và nhân viên trước');
                } else {
                timeSlotMessage.text('Vui lòng chọn kỹ thuật viên trước');
                }
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
                    employee_id: employeeId,
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
                        // Từ 7:00 đến 23:00 mỗi 30 phút = 33 slots
                        // Sử dụng 11 cột x 3 hàng = 33 slots
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
            const serviceCount = countSelectedServices();
            const time = $(this).data('time');
            const wordTimeId = $(this).data('word-time-id');
            const formattedTime = formatTimeSlot(time);
            const appointmentDate = $('#appointment_date').val();
            
            // Remove previous selection
            $('.time-slot-btn').removeClass('selected');
            
            // Add selection to clicked button
            $(this).addClass('selected');
            
            // Clear time slot error
            $('#time_slot-error').hide();
            
            if (serviceCount >= 2) {
                // QUAN TRỌNG: CHỈ cập nhật time slot cho dịch vụ đang active
                // KHÔNG tự động tìm dịch vụ đầu tiên có nhân viên để tránh cập nhật nhầm index 0
                let targetServiceSelector = activeServiceSelector;
                
                // Nếu không có dịch vụ active, KHÔNG làm gì cả - không tự động cập nhật
                if (!targetServiceSelector) {
                    console.log('⚠️ No active service selected. Skipping time slot update to prevent wrong service update.');
                    return false;
                }
                
                if (targetServiceSelector) {
                    let $timeInput = null;
                    let $dateInput = null;
                    let displayContainerId = null;
                    
                    if (targetServiceSelector.startsWith('service_')) {
                        const serviceId = targetServiceSelector.replace('service_', '');
                        $timeInput = $('.service-time-input[data-service-type="service"][data-service-id="' + serviceId + '"]');
                        $dateInput = $('.service-date-input[data-service-type="service"][data-service-id="' + serviceId + '"]');
                        displayContainerId = 'service_time_display_service_' + serviceId;
                    } else if (targetServiceSelector.startsWith('variant_')) {
                        const variantId = targetServiceSelector.replace('variant_', '');
                        $timeInput = $('.service-time-input[data-service-type="variant"][data-variant-id="' + variantId + '"]');
                        $dateInput = $('.service-date-input[data-service-type="variant"][data-variant-id="' + variantId + '"]');
                        displayContainerId = 'service_time_display_variant_' + variantId;
                    } else if (targetServiceSelector.startsWith('combo_')) {
                        const comboId = targetServiceSelector.replace('combo_', '');
                        $timeInput = $('.service-time-input[data-service-type="combo"][data-combo-id="' + comboId + '"]');
                        $dateInput = $('.service-date-input[data-service-type="combo"][data-combo-id="' + comboId + '"]');
                        displayContainerId = 'service_time_display_combo_' + comboId;
                    }
                    
                    if ($timeInput && $timeInput.length) {
                        // QUAN TRỌNG: Kiểm tra xem $timeInput có đúng với targetServiceSelector không
                        // Đặc biệt quan trọng với combo để tránh cập nhật nhầm index 0
                        let isValidInput = false;
                        if (targetServiceSelector.startsWith('service_')) {
                            const serviceId = targetServiceSelector.replace('service_', '');
                            const inputServiceId = $timeInput.attr('data-service-id');
                            if (String(serviceId) === String(inputServiceId)) {
                                isValidInput = true;
                            } else {
                                console.error('❌ Service ID mismatch. Target service ID:', serviceId, 'Time input service ID:', inputServiceId);
                            }
                        } else if (targetServiceSelector.startsWith('variant_')) {
                            const variantId = targetServiceSelector.replace('variant_', '');
                            const inputVariantId = $timeInput.attr('data-variant-id');
                            if (String(variantId) === String(inputVariantId)) {
                                isValidInput = true;
                            } else {
                                console.error('❌ Variant ID mismatch. Target variant ID:', variantId, 'Time input variant ID:', inputVariantId);
                            }
                        } else if (targetServiceSelector.startsWith('combo_')) {
                            const comboId = targetServiceSelector.replace('combo_', '');
                            const inputComboId = $timeInput.attr('data-combo-id');
                            if (String(comboId) === String(inputComboId)) {
                                isValidInput = true;
                            } else {
                                console.error('❌ Combo ID mismatch. Target combo ID:', comboId, 'Time input combo ID:', inputComboId);
                            }
                        }
                        
                        if (!isValidInput) {
                            console.error('❌ Input validation failed. Not updating time slot to prevent wrong service update (especially index 0).');
                            return false;
                        }
                        
                        // QUAN TRỌNG: Kiểm tra xem dịch vụ này đã có time slot chưa
                        // Nếu đã có và không phải là dịch vụ đang active, KHÔNG cập nhật
                        const existingTime = $timeInput.val();
                        if (existingTime && existingTime.trim() !== '') {
                            // Kiểm tra xem đây có phải là dịch vụ đang active không
                            const $serviceItem = $('.service-item-selectable.active-service');
                            if ($serviceItem.length === 0) {
                                console.log('⚠️ Service already has time slot and is not active. Skipping update to prevent overwrite.');
                                return false;
                            }
                            
                            // Kiểm tra service/variant/combo ID của active service
                            let activeServiceId = null;
                            let targetServiceId = null;
                            
                            if (targetServiceSelector.startsWith('service_')) {
                                activeServiceId = $serviceItem.attr('data-service-id');
                                targetServiceId = targetServiceSelector.replace('service_', '');
                            } else if (targetServiceSelector.startsWith('variant_')) {
                                activeServiceId = $serviceItem.attr('data-variant-id');
                                targetServiceId = targetServiceSelector.replace('variant_', '');
                            } else if (targetServiceSelector.startsWith('combo_')) {
                                activeServiceId = $serviceItem.attr('data-combo-id');
                                targetServiceId = targetServiceSelector.replace('combo_', '');
                            }
                            
                            if (activeServiceId && String(activeServiceId) !== String(targetServiceId)) {
                                console.log('⚠️ Active service ID mismatch. Skipping update to prevent wrong service update.');
                                console.log('Active service ID:', activeServiceId, 'Target service ID:', targetServiceId, 'Selector:', targetServiceSelector);
                                return false;
                            }
                        }
                        
                        // Lưu time slot và date cho dịch vụ này
                        $timeInput.val(time);
                        if ($dateInput && $dateInput.length && appointmentDate) {
                            $dateInput.val(appointmentDate);
                        }
                        
                        // Hiển thị time slot cạnh tên nhân viên
                        if (displayContainerId) {
                            const $displayContainer = $('#' + displayContainerId);
                            if ($displayContainer.length === 1) {
                                // QUAN TRỌNG: Kiểm tra lại container ID và service/variant/combo ID để đảm bảo không cập nhật nhầm
                                const containerIdCheck = $displayContainer.attr('id');
                                const $containerParent = $displayContainer.closest('.service-item-selectable');
                                
                                // Kiểm tra validation dựa trên loại service
                                let isValidUpdate = false;
                                
                                if (targetServiceSelector.startsWith('service_')) {
                                    const targetServiceId = targetServiceSelector.replace('service_', '');
                                    const containerParentServiceId = $containerParent.attr('data-service-id');
                                    
                                    if (containerIdCheck === displayContainerId && 
                                        (!containerParentServiceId || String(containerParentServiceId) === String(targetServiceId))) {
                                        isValidUpdate = true;
                                    } else {
                                        console.error('❌ Container validation failed for service:', {
                                            containerIdCheck: containerIdCheck,
                                            displayContainerId: displayContainerId,
                                            containerParentServiceId: containerParentServiceId,
                                            targetServiceId: targetServiceId
                                        });
                                    }
                                } else if (targetServiceSelector.startsWith('variant_')) {
                                    const targetVariantId = targetServiceSelector.replace('variant_', '');
                                    const containerParentVariantId = $containerParent.attr('data-variant-id');
                                    
                                    if (containerIdCheck === displayContainerId && 
                                        (!containerParentVariantId || String(containerParentVariantId) === String(targetVariantId))) {
                                        isValidUpdate = true;
                                    } else {
                                        console.error('❌ Container validation failed for variant:', {
                                            containerIdCheck: containerIdCheck,
                                            displayContainerId: displayContainerId,
                                            containerParentVariantId: containerParentVariantId,
                                            targetVariantId: targetVariantId
                                        });
                                    }
                                } else if (targetServiceSelector.startsWith('combo_')) {
                                    const targetComboId = targetServiceSelector.replace('combo_', '');
                                    const containerParentComboId = $containerParent.attr('data-combo-id');
                                    
                                    if (containerIdCheck === displayContainerId && 
                                        (!containerParentComboId || String(containerParentComboId) === String(targetComboId))) {
                                        isValidUpdate = true;
                                    } else {
                                        console.error('❌ Container validation failed for combo:', {
                                            containerIdCheck: containerIdCheck,
                                            displayContainerId: displayContainerId,
                                            containerParentComboId: containerParentComboId,
                                            targetComboId: targetComboId
                                        });
                                    }
                                }
                                
                                if (isValidUpdate) {
                                    $displayContainer.find('.time-slot-text').text(formattedTime);
                                    $displayContainer.fadeIn(300);
                                    console.log('✅ Updated display container:', displayContainerId, 'with time:', formattedTime, 'for active service:', targetServiceSelector);
                                } else {
                                    console.error('❌ Container validation failed. Not updating display container.');
                                }
                            } else {
                                console.error('❌ Multiple or no display containers found:', displayContainerId, 'Count:', $displayContainer.length);
                            }
                        }
                        
                        // KHÔNG xóa active sau khi chọn giờ - giữ lại để người dùng có thể thấy dịch vụ nào đang được chọn
                        // Active class sẽ chỉ bị xóa khi click vào dịch vụ khác hoặc click lại vào cùng dịch vụ
                    } else {
                        // Không tìm thấy dịch vụ có nhân viên
                        console.error('❌ Time input not found for active service:', targetServiceSelector);
                        return false;
                    }
                }
            } else {
                // Nếu chỉ có 1 dịch vụ, dùng time slot chung
                if (time) {
                    $('#time_slot').val(time);
                }
                if (wordTimeId) {
                    $('#word_time_id').val(wordTimeId);
                }
            }
            
            return false;
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
            const serviceCount = countSelectedServices();
            let hasTimeSlot = false;
            
            if (serviceCount >= 2) {
                // Nếu có >= 2 dịch vụ, kiểm tra service_time cho từng dịch vụ
                let allServicesHaveTime = true;
                
                // Kiểm tra service_time cho từng dịch vụ
                $('.service-time-input').each(function() {
                    const timeValue = $(this).val();
                    if (!timeValue || timeValue.trim() === '') {
                        allServicesHaveTime = false;
                        return false; // break
                    }
                });
                
                hasTimeSlot = allServicesHaveTime;
            } else {
                // Nếu chỉ có 1 dịch vụ, kiểm tra word_time_id chung
            const wordTimeId = $('#word_time_id').val();
            const wordTimeIdTrimmed = wordTimeId ? String(wordTimeId).trim() : '';
                hasTimeSlot = wordTimeIdTrimmed && wordTimeIdTrimmed !== '' && wordTimeIdTrimmed !== '0' && wordTimeIdTrimmed !== 'null' && wordTimeIdTrimmed !== 'undefined';
            }
            
            if (!hasTimeSlot) {
                showFieldError('time_slot', 'Mời anh chọn giờ đặt lịch');
                isValid = false;
            } else {
                // Clear error nếu đã chọn
                $('#time_slot-error').hide();
            }
            
            // Kiểm tra trùng giờ giữa các dịch vụ (nếu có >= 2 dịch vụ)
            if (serviceCount >= 2 && hasTimeSlot) {
                const timeConflicts = [];
                const allTimeRanges = [];
                
                // Thu thập tất cả các khoảng thời gian đã chọn
                $('.service-time-input').each(function() {
                    const $timeInput = $(this);
                    const time = $timeInput.val();
                    
                    if (!time || time.trim() === '') {
                        return true; // continue
                    }
                    
                    // Tìm date input tương ứng
                    const serviceType = $timeInput.attr('data-service-type');
                    let $dateInput = null;
                    
                    if (serviceType === 'service') {
                        const serviceId = $timeInput.attr('data-service-id');
                        $dateInput = $('.service-date-input[data-service-type="service"][data-service-id="' + serviceId + '"]');
                    } else if (serviceType === 'variant') {
                        const variantId = $timeInput.attr('data-variant-id');
                        $dateInput = $('.service-date-input[data-service-type="variant"][data-variant-id="' + variantId + '"]');
                    } else if (serviceType === 'combo') {
                        const comboId = $timeInput.attr('data-combo-id');
                        $dateInput = $('.service-date-input[data-service-type="combo"][data-combo-id="' + comboId + '"]');
                    }
                    
                    if (!$dateInput || !$dateInput.length) {
                        return true; // continue
                    }
                    
                    const date = $dateInput.val();
                    if (!date || date.trim() === '') {
                        return true; // continue
                    }
                    
                    // Lấy duration
                    let duration = parseInt($timeInput.attr('data-duration'));
                    if (!duration || duration === 0 || isNaN(duration)) {
                        if (serviceType === 'service') {
                            const serviceId = $timeInput.attr('data-service-id');
                            const $serviceItem = $('.selected-service-display[data-service-id="' + serviceId + '"]');
                            duration = parseInt($serviceItem.attr('data-service-duration')) || 60;
                        } else if (serviceType === 'variant') {
                            const variantId = $timeInput.attr('data-variant-id');
                            const $variantItem = $('.selected-variant-display[data-variant-id="' + variantId + '"]');
                            duration = parseInt($variantItem.attr('data-variant-duration')) || 60;
                        } else if (serviceType === 'combo') {
                            const comboId = $timeInput.attr('data-combo-id');
                            const $comboItem = $('.selected-combo-display[data-combo-id="' + comboId + '"]');
                            duration = parseInt($comboItem.attr('data-combo-duration')) || 60;
                        } else {
                            duration = 60;
                        }
                    }
                    
                    // Parse thời gian
                    const [hours, minutes] = time.split(':').map(Number);
                    const startMinutes = hours * 60 + minutes;
                    const endMinutes = startMinutes + duration;
                    
                    allTimeRanges.push({
                        timeInput: $timeInput,
                        serviceType: serviceType,
                        time: time,
                        date: date,
                        startMinutes: startMinutes,
                        endMinutes: endMinutes,
                        duration: duration
                    });
                });
                
                // Normalize dates để so sánh
                const normalizeDate = function(dateStr) {
                    if (!dateStr) return '';
                    if (dateStr.includes('/')) {
                        const parts = dateStr.split('/');
                        if (parts.length === 3) {
                            const day = parts[0].padStart(2, '0');
                            const month = parts[1].padStart(2, '0');
                            const year = parts[2];
                            return year + '-' + month + '-' + day;
                        }
                    }
                    return dateStr;
                };
                
                // Kiểm tra overlap giữa các khoảng thời gian trên cùng ngày
                for (let i = 0; i < allTimeRanges.length; i++) {
                    for (let j = i + 1; j < allTimeRanges.length; j++) {
                        const range1 = allTimeRanges[i];
                        const range2 = allTimeRanges[j];
                        
                        // Normalize dates
                        const normalizedDate1 = normalizeDate(range1.date);
                        const normalizedDate2 = normalizeDate(range2.date);
                        
                        // Chỉ kiểm tra nếu cùng ngày
                        if (normalizedDate1 === normalizedDate2) {
                            // Kiểm tra overlap
                            const hasOverlap = (range1.startMinutes < range2.endMinutes && range1.endMinutes > range2.startMinutes);
                            
                            if (hasOverlap) {
                                timeConflicts.push({
                                    range1: range1,
                                    range2: range2
                                });
                            }
                        }
                    }
                }
                
                // Nếu có conflict, hiển thị lỗi
                if (timeConflicts.length > 0) {
                    const conflict = timeConflicts[0];
                    const endTime1 = String(Math.floor(conflict.range1.endMinutes / 60)).padStart(2, '0') + ':' + String(conflict.range1.endMinutes % 60).padStart(2, '0');
                    const endTime2 = String(Math.floor(conflict.range2.endMinutes / 60)).padStart(2, '0') + ':' + String(conflict.range2.endMinutes % 60).padStart(2, '0');
                    
                    showFieldError('time_slot', 'Các dịch vụ không được trùng giờ! Vui lòng chọn lại giờ cho các dịch vụ.');
                    isValid = false;
                    console.log('Time conflict detected in form validation:', timeConflicts);
                }
            }
            
            // Debug log để kiểm tra
            console.log('Validation details:', {
                hasName: nameTrimmed !== '',
                hasPhone: phoneTrimmed !== '',
                hasService: serviceIds.length > 0 || serviceVariants.length > 0 || comboIds.length > 0,
                hasEmployeeId: hasEmployeeId,
                hasAppointmentDate: hasAppointmentDate,
                hasTimeSlot: hasTimeSlot,
                serviceCount: serviceCount,
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
            
            // Disable form và submit button để ngăn chặn submit lại
            const $submitBtn = $('.submit-appointment-btn');
            const originalBtnText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Đang xử lý...');
            
            // Collect form data as object
            const formData = {};
            
            // Serialize form to array first
            const formArray = $form.serializeArray();
            
            // Convert to object, properly handling arrays
            formArray.forEach(function(item) {
                const name = item.name;
                const value = item.value;
                
                if (name.endsWith('[]')) {
                    // Array field like service_id[]
                    const key = name.slice(0, -2);
                    if (!formData[key]) {
                        formData[key] = [];
                    }
                    if (value && value.trim() !== '') {
                        formData[key].push(value);
                    }
                } else if (name.includes('[') && name.includes(']')) {
                    // Nested array like service_employee[service_10]
                    formData[name] = value;
                } else {
                    // Simple field
                    formData[name] = value;
                }
            });
            
            // Add service_employee, service_time, service_date
            $('input[name^="service_employee["]').each(function() {
                const name = $(this).attr('name');
                const value = $(this).val();
                if (value && value.trim() !== '' && value !== '0') {
                    formData[name] = value;
                }
            });
            
            $('input[name^="service_time["]').each(function() {
                const name = $(this).attr('name');
                const value = $(this).val();
                if (value && value.trim() !== '') {
                    formData[name] = value;
                }
            });
            
            $('input[name^="service_date["]').each(function() {
                const name = $(this).attr('name');
                const value = $(this).val();
                if (value && value.trim() !== '') {
                    formData[name] = value;
                }
            });
            
            // Disable form inputs after collecting data
            $form.find('input, button, select, textarea').prop('disabled', true);
            
            console.log('Form data object:', formData);
            console.log('Service IDs:', formData.service_id);
            console.log('Service IDs is array:', Array.isArray(formData.service_id));
            
            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                data: formData,
                traditional: true,
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
                                
                                // Handle nested field errors like service_employee.service_10
                                if (key.includes('.')) {
                                    const parts = key.split('.');
                                    const fieldName = parts[0];
                                    const fieldKey = parts.slice(1).join('.');
                                    
                                    // For service_employee and service_time errors, show generic error
                                    if (fieldName === 'service_employee' || fieldName === 'service_time') {
                                        // Show error in appropriate place
                                        if (fieldName === 'service_employee') {
                                            showFieldError('employee', value[0]);
                                        } else if (fieldName === 'service_time') {
                                            showFieldError('time_slot', value[0]);
                                        }
                                    }
                                } else {
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

