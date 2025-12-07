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
            
            // Merge service_id - đảm bảo convert về string để so sánh đúng
            if (isset($newParams['service_id'])) {
                $existingIds = isset($mergedParams['service_id']) ? (is_array($mergedParams['service_id']) ? $mergedParams['service_id'] : [$mergedParams['service_id']]) : [];
                // Convert existing IDs to strings for comparison
                $existingIds = array_map('strval', $existingIds);
                $newIds = is_array($newParams['service_id']) ? $newParams['service_id'] : [$newParams['service_id']];
                foreach ($newIds as $newId) {
                    $newIdStr = (string)$newId;
                    if (!in_array($newIdStr, $existingIds, true)) {
                        $existingIds[] = $newIdStr;
                    }
                }
                $mergedParams['service_id'] = $existingIds;
            }
            
            // Merge service_variants - đảm bảo convert về string để so sánh đúng
            if (isset($newParams['service_variants'])) {
                $existingVariants = isset($mergedParams['service_variants']) ? (is_array($mergedParams['service_variants']) ? $mergedParams['service_variants'] : [$mergedParams['service_variants']]) : [];
                // Convert existing variants to strings for comparison
                $existingVariants = array_map('strval', $existingVariants);
                $newVariants = is_array($newParams['service_variants']) ? $newParams['service_variants'] : [$newParams['service_variants']];
                foreach ($newVariants as $variant) {
                    $variantStr = (string)$variant;
                    if (!in_array($variantStr, $existingVariants, true)) {
                        $existingVariants[] = $variantStr;
                    }
                }
                $mergedParams['service_variants'] = $existingVariants;
            }
            
            // Merge combo_id - đảm bảo convert về string để so sánh đúng
            if (isset($newParams['combo_id'])) {
                $existingCombos = isset($mergedParams['combo_id']) ? (is_array($mergedParams['combo_id']) ? $mergedParams['combo_id'] : [$mergedParams['combo_id']]) : [];
                // Convert existing combos to strings for comparison
                $existingCombos = array_map('strval', $existingCombos);
                $newCombos = is_array($newParams['combo_id']) ? $newParams['combo_id'] : [$newParams['combo_id']];
                foreach ($newCombos as $newCombo) {
                    $newComboStr = (string)$newCombo;
                    if (!in_array($newComboStr, $existingCombos, true)) {
                        $existingCombos[] = $newComboStr;
                    }
                }
                $mergedParams['combo_id'] = $existingCombos;
            }
            
            // Đảm bảo giữ lại tất cả các dịch vụ hiện có (không chỉ merge khi có newParams)
            // Nếu không có newParams nhưng có existing params, vẫn giữ lại
            if (!isset($newParams['service_id']) && isset($mergedParams['service_id'])) {
                $mergedParams['service_id'] = is_array($mergedParams['service_id']) ? $mergedParams['service_id'] : [$mergedParams['service_id']];
            }
            if (!isset($newParams['service_variants']) && isset($mergedParams['service_variants'])) {
                $mergedParams['service_variants'] = is_array($mergedParams['service_variants']) ? $mergedParams['service_variants'] : [$mergedParams['service_variants']];
            }
            if (!isset($newParams['combo_id']) && isset($mergedParams['combo_id'])) {
                $mergedParams['combo_id'] = is_array($mergedParams['combo_id']) ? $mergedParams['combo_id'] : [$mergedParams['combo_id']];
            }
            
            // Xóa add_more khỏi params
            unset($mergedParams['add_more']);
            
            return route('site.appointment.create', $mergedParams);
        } else {
            // Không có add_more, chỉ dùng params mới
            return route('site.appointment.create', $newParams);
        }
    }
@endphp

@section('content')
<div class="select-services-page" style="padding: 120px 0 20px; background: #f8f9fa; min-height: 100vh;">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="text-center mb-4" style="margin-top: 20px;">
                    <h2 class="fw-bold mb-2" style="color: #000; font-size: 24px;">
                        <i class="fa fa-scissors"></i> CHỌN DỊCH VỤ
                    </h2>
                    <p class="text-muted mb-0" style="font-size: 14px; color: #000;">Vui lòng chọn dịch vụ bạn muốn đặt lịch</p>
                </div>

                <!-- Services by Category -->
                @forelse($categories ?? [] as $category)
                    @php
                        $hasServices = $category->services && $category->services->count() > 0;
                        $hasCombos = isset($category->combos) && $category->combos->count() > 0;
                    @endphp
                    @if($hasServices || $hasCombos)
                        <!-- Category Header -->
                        <div class="category-section" style="margin-top: 40px; margin-bottom: 20px;">
                            <div class="d-flex align-items-center mb-3">
                                <span class="bar mr-2" style="width: 10px; height: 28px; background: linear-gradient(135deg, #f6d17a 0%, #d8b26a 50%, #8b5a2b 100%);"></span>
                                <h3 class="category-title mb-0" style="font-size: 20px; font-weight: 800; text-transform: uppercase; color: #000;">
                                    {{ $category->name }}
                                </h3>
                            </div>
                            
                            <!-- Service Grid for this Category -->
                            <div class="service-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
                                <!-- Services in this Category -->
                                @foreach($category->services as $service)
                                    @php
                                        $imagePath = $service->image ? 'legacy/images/products/' . $service->image : null;
                                        $formattedPrice = number_format($service->base_price ?? 0, 0, ',', '.');
                                        $hasVariants = $service->serviceVariants && $service->serviceVariants->count() > 0;
                                    @endphp
                                    <div class="svc-card service-card-wrapper" 
                                         data-service-id="{{ $service->id }}"
                                         style="border: 1px solid #eee; box-shadow: 0 6px 14px rgba(0,0,0,0.06); background: #fff; display: flex; flex-direction: column; border-radius: 8px; overflow: visible; position: relative;">
                                        <div class="svc-img" style="overflow: hidden; display: block; height: 250px; background: #f5f5f5; cursor: default;">
                                            @if($imagePath && file_exists(public_path($imagePath)))
                                                <img src="{{ asset($imagePath) }}" alt="{{ $service->name }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                                            @elseif($service->image)
                                                <img src="{{ asset('legacy/images/products/' . $service->image) }}" alt="{{ $service->name }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onerror="this.src='{{ asset('legacy/images/products/default.jpg') }}'; this.onerror=null;">
                                            @else
                                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);">
                                                    <i class="fa fa-image" style="font-size: 48px; color: #ccc;"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="svc-body" style="padding: 15px; display: flex; flex-direction: column; flex-grow: 1;">
                                            <div class="svc-info" style="flex-grow: 1;">
                                                <h4 class="svc-name" style="margin: 0 0 10px 0; font-weight: 800; font-size: 16px;">
                                                    <a href="{{ route('site.services.show', $service->id) }}" style="color: inherit; text-decoration: none;">{{ $service->name }}</a>
                                                </h4>
                                                @if(!$hasVariants)
                                                    <div class="svc-price" style="margin-bottom: 10px; font-size: 14px;">
                                                        Giá từ: <span style="color: #c08a3f; font-weight: 700;">{{ $formattedPrice }}vnđ</span>
                                                    </div>
                                                @endif
                                                @if($hasVariants)
                                                    <div class="svc-variants" style="font-size: 12px; color: #666; margin-bottom: 10px; cursor: pointer;">
                                                        <i class="fa fa-info-circle"></i> Có {{ $service->serviceVariants->count() }} dịch vụ 
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="svc-actions" style="margin-top: auto; position: relative;">
                                                @if($hasVariants)
                                                    <!-- Variants Tooltip -->
                                                    <div class="variants-tooltip" 
                                                         data-service-id="{{ $service->id }}"
                                                         style="display: none; position: absolute; left: calc(100% + 8px); top: 50%; transform: translateY(-50%); background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border: 2px solid #000; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.2); z-index: 1000; min-width: 380px; max-width: 480px; padding: 0; overflow: hidden;">
                                                        <!-- Tooltip Arrow -->
                                                        <div style="position: absolute; left: -10px; top: 50%; transform: translateY(-50%); width: 0; height: 0; border-top: 10px solid transparent; border-bottom: 10px solid transparent; border-right: 10px solid #000;"></div>
                                                        <div style="position: absolute; left: -8px; top: 50%; transform: translateY(-50%); width: 0; height: 0; border-top: 10px solid transparent; border-bottom: 10px solid transparent; border-right: 10px solid #ffffff;"></div>
                                                        
                                                        <!-- Hover Bridge - Tạo cầu nối để dễ di chuyển chuột -->
                                                        <div class="hover-bridge" style="position: absolute; left: -8px; top: 0; bottom: 0; width: 8px; z-index: 999; pointer-events: auto;"></div>
                                                        
                                                        <!-- Header -->
                                                        <div class="tooltip-header" style="background: linear-gradient(135deg, #000 0%, #333 100%); padding: 16px 20px; border-bottom: 2px solid #000;">
                                                            <h5 style="margin: 0; font-size: 15px; font-weight: 800; color: #fff; text-transform: uppercase; letter-spacing: 0.5px;">
                                                                <i class="fa fa-list-ul" style="margin-right: 8px; color: #FFC107;"></i> Các dịch vụ
                                                            </h5>
                                                            <p style="margin: 4px 0 0 0; font-size: 11px; color: #ccc; font-weight: 500;">{{ $service->name }}</p>
                                                        </div>
                                                        
                                                        <!-- Body -->
                                                        <div class="tooltip-body" style="padding: 12px; max-height: 320px; overflow-y: auto;">
                                                            @foreach($service->serviceVariants as $variant)
                                                                @php
                                                                    $variantPrice = number_format($variant->price ?? 0, 0, ',', '.');
                                                                @endphp
                                                                <div class="variant-item-wrapper" 
                                                                     data-variant-id="{{ $variant->id }}"
                                                                     data-variant-name="{{ $variant->name }}"
                                                                     data-variant-price="{{ $variant->price ?? 0 }}"
                                                                     data-variant-duration="{{ $variant->duration ?? 60 }}"
                                                                     data-service-name="{{ $service->name }}"
                                                                     data-variant-attributes="{{ $variant->variantAttributes->map(function($attr) { return $attr->attribute_name . ':' . $attr->attribute_value; })->implode(',') }}"
                                                                   style="text-decoration: none; display: block;">
                                                                    <div class="variant-item" style="padding: 12px; margin-bottom: 8px; background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; transition: all 0.3s ease; cursor: pointer;">
                                                                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                                                            <div style="flex: 1;">
                                                                                <div style="display: flex; align-items: center; margin-bottom: 6px;">
                                                                                    <div style="width: 4px; height: 4px; background: #000; border-radius: 50%; margin-right: 8px;"></div>
                                                                                    <div style="font-weight: 700; color: #000; font-size: 14px; line-height: 1.3;">{{ $variant->name }}</div>
                                                                                </div>
                                                                                <div style="display: flex; flex-direction: column; gap: 6px; margin-top: 6px;">
                                                                                    <div style="display: flex; align-items: center; font-size: 12px; color: #666; background: #f5f5f5; padding: 4px 8px; border-radius: 4px; width: fit-content;">
                                                                                        <i class="fa fa-clock-o" style="margin-right: 4px; color: #888;"></i>
                                                                                        <span style="font-weight: 600;">{{ $variant->duration ?? 60 }} phút</span>
                                                                                    </div>
                                                                                    @if($variant->variantAttributes && $variant->variantAttributes->count() > 0)
                                                                                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                                                            @foreach($variant->variantAttributes as $attr)
                                                                                                <div style="display: flex; align-items: center; font-size: 12px; color: #666; background: #e3f2fd; padding: 4px 8px; border-radius: 4px;">
                                                                                                    <i class="fa fa-tag" style="margin-right: 4px; color: #1976d2;"></i>
                                                                                                    <span style="font-weight: 600;">{{ $attr->attribute_name }}: {{ $attr->attribute_value }}</span>
                                                                                                </div>
                                                                                            @endforeach
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                            <div style="text-align: right; margin-left: 12px;">
                                                                                <div style="font-weight: 800; color: #c08a3f; font-size: 16px; line-height: 1.2;">
                                                                                    {{ $variantPrice }}<span style="font-size: 12px; font-weight: 600;">vnđ</span>
                                                                                </div>
                                                                                <div class="variant-select-status" style="margin-top: 4px; font-size: 11px; color: #666; display: none;">
                                                                                    <i class="fa fa-check-circle" style="color: #28a745;"></i> Đã chọn
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
                                                            style="background: #000; border: 1px solid #000; color: #fff; padding: 10px 20px; font-size: 14px; font-weight: 600; border-radius: 8px; transition: all 0.3s ease; text-decoration: none; display: inline-block; text-align: center; position: relative; z-index: 1; cursor: pointer;">
                                                        <i class="fa fa-check"></i> <span class="btn-text">Chọn</span>
                                                    </button>
                                                @else
                                                    <button type="button"
                                                       class="btn btn-primary w-100 select-service-btn" 
                                                       data-has-variants="false"
                                                            data-service-id="{{ $service->id }}"
                                                            data-service-name="{{ $service->name }}"
                                                            data-service-price="{{ $service->base_price ?? 0 }}"
                                                            data-service-duration="{{ $service->base_duration ?? 60 }}"
                                                            style="background: #000; border: 1px solid #000; color: #fff; padding: 10px 20px; font-size: 14px; font-weight: 600; border-radius: 8px; transition: all 0.3s ease; text-decoration: none; display: inline-block; text-align: center; position: relative; z-index: 1; cursor: pointer;">
                                                        <i class="fa fa-check"></i> <span class="btn-text">Chọn</span>
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
                                             style="border: 1px solid #eee; box-shadow: 0 6px 14px rgba(0,0,0,0.06); background: #fff; display: flex; flex-direction: column; border-radius: 8px; overflow: visible; position: relative;">
                                            <div class="svc-img" style="overflow: hidden; display: block; height: 250px; background: #f5f5f5; cursor: default;">
                                                @if($imagePath && file_exists(public_path($imagePath)))
                                                    <img src="{{ asset($imagePath) }}" alt="{{ $combo->name }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                                                @elseif($combo->image)
                                                    <img src="{{ asset('legacy/images/products/' . $combo->image) }}" alt="{{ $combo->name }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onerror="this.src='{{ asset('legacy/images/products/default.jpg') }}'; this.onerror=null;">
                                                @else
                                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);">
                                                        <i class="fa fa-image" style="font-size: 48px; color: #ccc;"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="svc-body" style="padding: 15px; display: flex; flex-direction: column; flex-grow: 1;">
                                                <div class="svc-info" style="flex-grow: 1;">
                                                    <h4 class="svc-name" style="margin: 0 0 10px 0; font-weight: 800; font-size: 16px;">
                                                        <a href="#" style="color: inherit; text-decoration: none;">{{ $combo->name }}</a>
                                                        <span style="color: #c08a3f; font-size: 12px; font-weight: 600; margin-left: 5px;">(COMBO)</span>
                                                    </h4>
                                                    <div class="svc-price" style="margin-bottom: 10px; font-size: 14px;">
                                                        Giá: <span style="color: #c08a3f; font-weight: 700;">{{ $formattedPrice }}vnđ</span>
                                                    </div>
                                                    <div style="font-size: 12px; color: #666; margin-bottom: 10px;">
                                                        <i class="fa fa-clock-o"></i> Thời gian: <strong>{{ $comboDuration }} phút</strong>
                                                    </div>
                                                </div>
                                                <div class="svc-actions" style="margin-top: auto; position: relative;">
                                                    <button type="button"
                                                       class="btn btn-primary w-100 select-service-btn" 
                                                       data-has-variants="false"
                                                            data-combo-id="{{ $combo->id }}"
                                                            data-combo-name="{{ $combo->name }}"
                                                            data-combo-price="{{ $combo->price ?? 0 }}"
                                                            data-combo-duration="{{ $comboDuration }}"
                                                            style="background: #000; border: 1px solid #000; color: #fff; padding: 10px 20px; font-size: 14px; font-weight: 600; border-radius: 8px; transition: all 0.3s ease; text-decoration: none; display: inline-block; text-align: center; position: relative; z-index: 1; cursor: pointer;">
                                                        <i class="fa fa-check"></i> <span class="btn-text">Chọn</span>
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
                    <div class="col-12 text-center" style="margin-top: 40px;">
                        <p class="text-muted">Chưa có dịch vụ nào</p>
                    </div>
                @endforelse

                <!-- Combos Without Category Section (if any) -->
                @if(isset($combosWithoutCategory) && $combosWithoutCategory->count() > 0)
                    <div class="category-section" style="margin-top: 40px; margin-bottom: 20px;">
                        <div class="d-flex align-items-center mb-3">
                            <span class="bar mr-2" style="width: 10px; height: 28px; background: linear-gradient(135deg, #f6d17a 0%, #d8b26a 50%, #8b5a2b 100%);"></span>
                            <h3 class="category-title mb-0" style="font-size: 20px; font-weight: 800; text-transform: uppercase; color: #000;">
                                COMBO
                            </h3>
                        </div>
                        
                        <!-- Combo Grid -->
                        <div class="service-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
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
                                     style="border: 1px solid #eee; box-shadow: 0 6px 14px rgba(0,0,0,0.06); background: #fff; display: flex; flex-direction: column; border-radius: 8px; overflow: visible; position: relative;">
                                    <div class="svc-img" style="overflow: hidden; display: block; height: 250px; background: #f5f5f5; cursor: default;">
                                        @if($imagePath && file_exists(public_path($imagePath)))
                                            <img src="{{ asset($imagePath) }}" alt="{{ $combo->name }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                                        @elseif($combo->image)
                                            <img src="{{ asset('legacy/images/products/' . $combo->image) }}" alt="{{ $combo->name }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onerror="this.src='{{ asset('legacy/images/products/default.jpg') }}'; this.onerror=null;">
                                        @else
                                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);">
                                                <i class="fa fa-image" style="font-size: 48px; color: #ccc;"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="svc-body" style="padding: 15px; display: flex; flex-direction: column; flex-grow: 1;">
                                        <div class="svc-info" style="flex-grow: 1;">
                                            <h4 class="svc-name" style="margin: 0 0 10px 0; font-weight: 800; font-size: 16px;">
                                                <a href="#" style="color: inherit; text-decoration: none;">{{ $combo->name }}</a>
                                                <span style="color: #c08a3f; font-size: 12px; font-weight: 600; margin-left: 5px;">(COMBO)</span>
                                            </h4>
                                            <div class="svc-price" style="margin-bottom: 10px; font-size: 14px;">
                                                Giá: <span style="color: #c08a3f; font-weight: 700;">{{ $formattedPrice }}vnđ</span>
                                            </div>
                                            <div style="font-size: 12px; color: #666; margin-bottom: 10px;">
                                                <i class="fa fa-clock-o"></i> Thời gian: <strong>{{ $comboDuration }} phút</strong>
                                            </div>
                                        </div>
                                        <div class="svc-actions" style="margin-top: auto; position: relative;">
                                            <a href="{{ buildServiceUrl(['combo_id' => $combo->id]) }}" 
                                               class="btn btn-primary w-100 select-service-btn" 
                                               data-has-variants="false"
                                               style="background: #000; border: 1px solid #000; color: #fff; padding: 10px 20px; font-size: 14px; font-weight: 600; border-radius: 8px; transition: all 0.3s ease; text-decoration: none; display: inline-block; text-align: center; position: relative; z-index: 1;">
                                                <i class="fa fa-check"></i> Chọn
                                            </a>
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
    
    <!-- Selected Services Summary - Fixed Bottom Bar -->
    <div id="selected-services-summary-wrapper" style="position: fixed; bottom: 0; left: 0; right: 0; z-index: 1000; display: none; pointer-events: none;">
        <div id="selected-services-summary" style="background: #fff; border-top: 2px solid #000; box-shadow: 0 -4px 12px rgba(0,0,0,0.1); padding: 20px 0; pointer-events: auto; margin: 0 auto; border-top-left-radius: 20px; border-top-right-radius: 20px; border-bottom-left-radius: 20px; border-bottom-right-radius: 20px;">
            <div class="container" style="max-width: 1140px; margin: 0 auto; padding-left: 15px; padding-right: 15px;">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                        <div style="font-weight: 600; color: #000; font-size: 14px;">
                            <span id="selected-count">0</span> dịch vụ đã chọn
                        </div>
                        <div id="selected-services-list" style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <!-- Selected services will be displayed here -->
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <div style="display: flex; align-items: center; justify-content: flex-end; gap: 15px;">
                        <div style="text-align: right;">
                            <div style="font-size: 12px; color: #666; margin-bottom: 2px;">Tổng thanh toán</div>
                            <div style="font-size: 18px; font-weight: 800; color: #c08a3f;">
                                <span id="total-price">0</span><span style="font-size: 14px;">vnđ</span>
                            </div>
                        </div>
                        <button id="done-button" 
                                type="button"
                                style="background: #000; border: 1px solid #000; color: #fff; padding: 12px 30px; font-size: 16px; font-weight: 600; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; min-width: 120px;">
                            Xong
                        </button>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>

<style>
    .svc-img:hover img {
        transform: scale(1.05);
    }
    
    .btn-primary:hover {
        background: #FFC107 !important;
        color: #000 !important;
        border: 1px solid #FFC107 !important;
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
    
    /* Hover bridge để dễ di chuyển chuột */
    .hover-bridge {
        pointer-events: auto;
        background: transparent;
    }
    
    /* Đảm bảo tooltip và bridge luôn hiển thị khi hover */
    .svc-actions:hover .hover-bridge,
    .svc-actions:hover .variants-tooltip {
        display: block !important;
    }
    
    /* Delay before hiding tooltip */
    .variants-tooltip {
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    
    .variant-item {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .variant-item:hover {
        background: linear-gradient(135deg, #fff8e1 0%, #ffe082 100%) !important;
        border-color: #FFC107 !important;
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3) !important;
    }
    
    .variant-item-wrapper {
        cursor: pointer;
    }
    
    .variant-item-wrapper:hover .variant-item {
        background: linear-gradient(135deg, #fff8e1 0%, #ffe082 100%) !important;
        border-color: #FFC107 !important;
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3) !important;
    }
    
    .variant-item-wrapper.selected .variant-item {
        border-color: #28a745 !important;
        background: #f0fff4 !important;
    }
    
    .variant-item:last-child {
        margin-bottom: 0 !important;
    }
    
    /* Scrollbar styling for tooltip */
    .variants-tooltip .tooltip-body::-webkit-scrollbar {
        width: 8px;
    }
    
    .variants-tooltip .tooltip-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
        margin: 4px 0;
    }
    
    .variants-tooltip .tooltip-body::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #888 0%, #666 100%);
        border-radius: 10px;
    }
    
    .variants-tooltip .tooltip-body::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #666 0%, #444 100%);
    }
    
    /* Tooltip responsive adjustments */
    @media (max-width: 768px) {
        .variants-tooltip {
            min-width: 320px !important;
            max-width: 90vw !important;
        }
    }
    
    /* Selected services summary styles */
    #selected-services-summary {
        animation: slideUp 0.3s ease-out;
    }
    
    @keyframes slideUp {
        from {
            transform: translateY(100%);
        }
        to {
            transform: translateY(0);
        }
    }
    
    #done-button:hover {
        background: #FFC107 !important;
        color: #000 !important;
        border-color: #FFC107 !important;
    }
    
    #selected-services-summary {
        max-width: 1140px;
        margin: 0 auto;
    }
    
    @media (max-width: 1200px) {
        #selected-services-summary {
            max-width: 960px;
        }
    }
    
    @media (max-width: 992px) {
        #selected-services-summary {
            max-width: 720px;
        }
    }
    
    @media (max-width: 768px) {
        #selected-services-summary {
            max-width: 540px;
            padding: 10px 0 !important;
        }
        
        #selected-services-summary .row {
            flex-direction: column;
            gap: 10px;
        }
        
        #selected-services-summary .col-md-8,
        #selected-services-summary .col-md-4 {
            width: 100%;
        }
        
        #selected-services-summary .col-md-4 {
            text-align: left !important;
        }
    }
    
    @media (max-width: 576px) {
        #selected-services-summary {
            max-width: 100%;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Service selection state management
    const selectedServices = {
        serviceIds: [],
        variantIds: [],
        comboIds: [],
        items: [] // Store full item details
    };
    
    // Store services from URL (these should NOT be removed when toggling)
    const lockedServices = {
        serviceIds: [],
        variantIds: [],
        comboIds: []
    };
    
    // Initialize selected services from URL query parameters (when add_more is true)
    function initializeFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        const isAddMore = urlParams.get('add_more') === 'true' || urlParams.get('add_more') === '1';
        
        if (isAddMore) {
            // Get service_ids from URL and lock them
            // Handle both formats: service_id[] and service_id[0], service_id[1], etc.
            let serviceIds = [];
            urlParams.forEach((value, key) => {
                if (key === 'service_id[]' || /^service_id\[\d+\]$/.test(key)) {
                    const id = parseInt(value);
                    if (!isNaN(id) && !serviceIds.includes(id)) {
                        serviceIds.push(id);
                    }
                }
            });
            if (serviceIds.length > 0) {
                selectedServices.serviceIds = [...serviceIds];
                lockedServices.serviceIds = [...serviceIds]; // Lock these services
            }
            
            // Get service_variants from URL and lock them
            let variantIds = [];
            urlParams.forEach((value, key) => {
                if (key === 'service_variants[]' || /^service_variants\[\d+\]$/.test(key)) {
                    const id = parseInt(value);
                    if (!isNaN(id) && !variantIds.includes(id)) {
                        variantIds.push(id);
                    }
                }
            });
            if (variantIds.length > 0) {
                selectedServices.variantIds = [...variantIds];
                lockedServices.variantIds = [...variantIds]; // Lock these variants
            }
            
            // Get combo_ids from URL and lock them
            let comboIds = [];
            urlParams.forEach((value, key) => {
                if (key === 'combo_id[]' || /^combo_id\[\d+\]$/.test(key)) {
                    const id = parseInt(value);
                    if (!isNaN(id) && !comboIds.includes(id)) {
                        comboIds.push(id);
                    }
                }
            });
            if (comboIds.length > 0) {
                selectedServices.comboIds = [...comboIds];
                lockedServices.comboIds = [...comboIds]; // Lock these combos
            }
            
            // Load full item details for selected services (use setTimeout to ensure DOM is ready)
            // First, add items with basic info (so they show up immediately)
            selectedServices.serviceIds.forEach(serviceId => {
                if (!selectedServices.items.find(item => item.type === 'service' && item.id === serviceId)) {
                    selectedServices.items.push({
                        type: 'service',
                        id: serviceId,
                        name: 'Dịch vụ #' + serviceId,
                        price: 0,
                        duration: 60
                    });
                }
            });
            
            selectedServices.variantIds.forEach(variantId => {
                if (!selectedServices.items.find(item => item.type === 'variant' && item.id === variantId)) {
                    selectedServices.items.push({
                        type: 'variant',
                        id: variantId,
                        name: 'Biến thể #' + variantId,
                        price: 0,
                        duration: 60,
                        attributes: []
                    });
                }
            });
            
            selectedServices.comboIds.forEach(comboId => {
                if (!selectedServices.items.find(item => item.type === 'combo' && item.id === comboId)) {
                    selectedServices.items.push({
                        type: 'combo',
                        id: comboId,
                        name: 'Combo #' + comboId,
                        price: 0,
                        duration: 60
                    });
                }
            });
            
            // Update UI immediately
            updateSummary();
            updateButtonStates();
            
            // Then, try to load full details from DOM
            setTimeout(() => {
                selectedServices.serviceIds.forEach(serviceId => {
                    const btn = document.querySelector(`.select-service-btn[data-service-id="${serviceId}"]`) || 
                                document.querySelector(`[data-service-id="${serviceId}"]`);
                    if (btn) {
                        const item = selectedServices.items.find(item => item.type === 'service' && item.id === serviceId);
                        if (item) {
                            item.name = btn.getAttribute('data-service-name') || 
                                       btn.closest('.service-card-wrapper')?.querySelector('.svc-name')?.textContent?.trim() || 
                                       item.name;
                            item.price = parseFloat(btn.getAttribute('data-service-price') || item.price);
                            item.duration = parseInt(btn.getAttribute('data-service-duration') || item.duration);
                        }
                    }
                });
                
                selectedServices.variantIds.forEach(variantId => {
                    const variantEl = document.querySelector(`[data-variant-id="${variantId}"]`);
                    if (variantEl) {
                        const item = selectedServices.items.find(item => item.type === 'variant' && item.id === variantId);
                        if (item) {
                            const serviceName = variantEl.getAttribute('data-service-name') || '';
                            const variantName = variantEl.getAttribute('data-variant-name') || 'Biến thể';
                            item.name = serviceName ? serviceName + ' - ' + variantName : variantName;
                            item.price = parseFloat(variantEl.getAttribute('data-variant-price') || item.price);
                            item.duration = parseInt(variantEl.getAttribute('data-variant-duration') || item.duration);
                        }
                    }
                });
                
                selectedServices.comboIds.forEach(comboId => {
                    const btn = document.querySelector(`.select-service-btn[data-combo-id="${comboId}"]`);
                    if (btn) {
                        const item = selectedServices.items.find(item => item.type === 'combo' && item.id === comboId);
                        if (item) {
                            item.name = btn.getAttribute('data-combo-name') || item.name;
                            item.price = parseFloat(btn.getAttribute('data-combo-price') || item.price);
                            item.duration = parseInt(btn.getAttribute('data-combo-duration') || item.duration);
                        }
                    }
                });
                
                // Update UI again with full details
                updateSummary();
            }, 200);
        }
    }
    
    // Helper function to format price
    function formatPrice(price) {
        return new Intl.NumberFormat('vi-VN').format(price);
    }
    
    // Update summary bar width to match container
    function updateSummaryBarWidth() {
        const mainContainer = document.querySelector('.select-services-page .container');
        const summary = document.getElementById('selected-services-summary');
        
        if (mainContainer && summary) {
            const containerRect = mainContainer.getBoundingClientRect();
            const containerWidth = containerRect.width;
            
            // Set width to match container exactly
            summary.style.width = containerWidth + 'px';
            summary.style.maxWidth = containerWidth + 'px';
        }
    }
    
    // Update summary bar
    function updateSummary() {
        const summaryWrapper = document.getElementById('selected-services-summary-wrapper');
        const summary = document.getElementById('selected-services-summary');
        const countEl = document.getElementById('selected-count');
        const totalEl = document.getElementById('total-price');
        const listEl = document.getElementById('selected-services-list');
        
        if (!summaryWrapper || !summary || !countEl || !totalEl || !listEl) return;
        
        const count = selectedServices.items.length;
        const total = selectedServices.items.reduce((sum, item) => sum + (item.price || 0), 0);
        
        if (count > 0) {
            summaryWrapper.style.display = 'block';
            document.body.style.paddingBottom = '90px';
            // Update width after display
            setTimeout(updateSummaryBarWidth, 0);
        } else {
            summaryWrapper.style.display = 'none';
            document.body.style.paddingBottom = '';
        }
        
        countEl.textContent = count;
        totalEl.textContent = formatPrice(total);
        
        // Update selected services list
        listEl.innerHTML = '';
        selectedServices.items.forEach((item, index) => {
            const badge = document.createElement('div');
            badge.style.cssText = 'background: #f0f0f0; padding: 8px 12px; border-radius: 6px; font-size: 12px; color: #000; display: flex; flex-direction: column; gap: 4px;';
            
            let content = `<div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                <span style="font-weight: 600;">${item.name}</span>
                <button onclick="removeService(${index})" style="background: none; border: none; color: #999; cursor: pointer; padding: 0; font-size: 16px; line-height: 1;">&times;</button>
            </div>`;
            
            // Add duration and attributes
            if (item.duration) {
                content += `<div style="display: flex; align-items: center; margin-top: 4px;">
                    <span style="display: inline-flex; align-items: center; gap: 4px; color: #666; font-size: 11px; background: #f5f5f5; padding: 2px 6px; border-radius: 4px;"><i class="fa fa-clock-o"></i> ${item.duration} phút</span>
                </div>`;
            }
            
            if (item.attributes && item.attributes.length > 0) {
                const attributesHtml = item.attributes.map(attr => 
                    `<span style="display: inline-flex; align-items: center; gap: 4px; color: #1976d2; font-size: 11px; background: #e3f2fd; padding: 2px 6px; border-radius: 4px;"><i class="fa fa-tag"></i> ${attr.name}: ${attr.value}</span>`
                ).join('');
                content += `<div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 4px;">${attributesHtml}</div>`;
            }
            
            badge.innerHTML = content;
            listEl.appendChild(badge);
        });
        
        // Update button states
        updateButtonStates();
    }
    
    // Update button states based on selection
    function updateButtonStates() {
        // Update service buttons
        document.querySelectorAll('.select-service-btn').forEach(btn => {
            const serviceId = btn.getAttribute('data-service-id');
            const comboId = btn.getAttribute('data-combo-id');
            const hasVariants = btn.getAttribute('data-has-variants') === 'true';
            
            if (hasVariants) {
                // For services with variants, check if any variant is selected
                const serviceCard = btn.closest('.service-card-wrapper');
                if (serviceCard) {
                    const variants = serviceCard.querySelectorAll('.variant-item-wrapper');
                    let hasSelectedVariant = false;
                    variants.forEach(variant => {
                        const variantId = variant.getAttribute('data-variant-id');
                        if (selectedServices.variantIds.includes(parseInt(variantId))) {
                            hasSelectedVariant = true;
                        }
                    });
                    
                    if (hasSelectedVariant) {
                        btn.style.background = '#28a745';
                        btn.querySelector('.btn-text').textContent = 'Đã chọn';
                    } else {
                        btn.style.background = '#000';
                        btn.querySelector('.btn-text').textContent = 'Chọn';
                    }
                }
            } else if (serviceId) {
                // For services without variants
                if (selectedServices.serviceIds.includes(parseInt(serviceId))) {
                    btn.style.background = '#28a745';
                    btn.querySelector('.btn-text').textContent = 'Đã chọn';
                } else {
                    btn.style.background = '#000';
                    btn.querySelector('.btn-text').textContent = 'Chọn';
                }
            } else if (comboId) {
                // For combos
                if (selectedServices.comboIds.includes(parseInt(comboId))) {
                    btn.style.background = '#28a745';
                    btn.querySelector('.btn-text').textContent = 'Đã chọn';
                } else {
                    btn.style.background = '#000';
                    btn.querySelector('.btn-text').textContent = 'Chọn';
                }
            }
        });
        
        // Update variant items
        document.querySelectorAll('.variant-item-wrapper').forEach(variant => {
            const variantId = variant.getAttribute('data-variant-id');
            const statusEl = variant.querySelector('.variant-select-status');
            
            if (selectedServices.variantIds.includes(parseInt(variantId))) {
                variant.querySelector('.variant-item').style.borderColor = '#28a745';
                variant.querySelector('.variant-item').style.background = '#f0fff4';
                if (statusEl) statusEl.style.display = 'block';
            } else {
                variant.querySelector('.variant-item').style.borderColor = '#e0e0e0';
                variant.querySelector('.variant-item').style.background = '#fff';
                if (statusEl) statusEl.style.display = 'none';
            }
        });
    }
    
    // Remove service by index
    window.removeService = function(index) {
        const item = selectedServices.items[index];
        if (!item) return;
        
        if (item.type === 'variant') {
            selectedServices.variantIds = selectedServices.variantIds.filter(id => id !== item.id);
        } else if (item.type === 'service') {
            selectedServices.serviceIds = selectedServices.serviceIds.filter(id => id !== item.id);
        } else if (item.type === 'combo') {
            selectedServices.comboIds = selectedServices.comboIds.filter(id => id !== item.id);
        }
        
        selectedServices.items.splice(index, 1);
        updateSummary();
    };
    
    // Handle variant item click
    document.querySelectorAll('.variant-item-wrapper').forEach(variant => {
        variant.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const variantId = parseInt(this.getAttribute('data-variant-id'));
            const variantName = this.getAttribute('data-variant-name');
            const variantPrice = parseFloat(this.getAttribute('data-variant-price')) || 0;
            const variantDuration = parseInt(this.getAttribute('data-variant-duration')) || 60;
            const serviceName = this.getAttribute('data-service-name');
            const attributesStr = this.getAttribute('data-variant-attributes') || '';
            
            // Parse attributes
            const attributes = [];
            if (attributesStr) {
                attributesStr.split(',').forEach(attr => {
                    if (attr && attr.includes(':')) {
                        const [name, value] = attr.split(':');
                        if (name && value) {
                            attributes.push({ name: name.trim(), value: value.trim() });
                        }
                    }
                });
            }
            
            // Check if this variant is locked (from URL) - cannot remove locked variants
            const isLocked = lockedServices.variantIds.includes(variantId);
            
            const index = selectedServices.variantIds.indexOf(variantId);
            if (index > -1) {
                // Only allow remove if NOT locked (variants from URL cannot be removed)
                if (!isLocked) {
                    selectedServices.variantIds.splice(index, 1);
                    selectedServices.items = selectedServices.items.filter(item => !(item.type === 'variant' && item.id === variantId));
                }
                // If locked, do nothing - prevent removal of variants from URL
            } else {
                // Add
                selectedServices.variantIds.push(variantId);
                selectedServices.items.push({
                    type: 'variant',
                    id: variantId,
                    name: serviceName + ' - ' + variantName,
                    price: variantPrice,
                    duration: variantDuration,
                    attributes: attributes
                });
            }
            updateSummary();
        });
    });
    
    // Handle service/combo button click
    document.querySelectorAll('.select-service-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const hasVariants = this.getAttribute('data-has-variants') === 'true';
            
            if (hasVariants) {
                // Do nothing - variants are handled separately
                return;
            }
            
            const serviceId = this.getAttribute('data-service-id');
            const comboId = this.getAttribute('data-combo-id');
            
            if (serviceId) {
                const id = parseInt(serviceId);
                const index = selectedServices.serviceIds.indexOf(id);
                
                // Check if this service is locked (from URL) - cannot remove locked services
                const isLocked = lockedServices.serviceIds.includes(id);
                
                if (index > -1) {
                    // Only allow remove if NOT locked (services from URL cannot be removed)
                    if (!isLocked) {
                        selectedServices.serviceIds.splice(index, 1);
                        selectedServices.items = selectedServices.items.filter(item => !(item.type === 'service' && item.id === id));
                    }
                    // If locked, do nothing - prevent removal of services from URL
                } else {
                    // Add
                    selectedServices.serviceIds.push(id);
                    selectedServices.items.push({
                        type: 'service',
                        id: id,
                        name: this.getAttribute('data-service-name') || 'Dịch vụ',
                        price: parseFloat(this.getAttribute('data-service-price')) || 0,
                        duration: parseInt(this.getAttribute('data-service-duration')) || 60
                    });
                }
            } else if (comboId) {
                const id = parseInt(comboId);
                const index = selectedServices.comboIds.indexOf(id);
                
                // Check if this combo is locked (from URL) - cannot remove locked combos
                const isLocked = lockedServices.comboIds.includes(id);
                
                if (index > -1) {
                    // Only allow remove if NOT locked (combos from URL cannot be removed)
                    if (!isLocked) {
                        selectedServices.comboIds.splice(index, 1);
                        selectedServices.items = selectedServices.items.filter(item => !(item.type === 'combo' && item.id === id));
                    }
                    // If locked, do nothing - prevent removal of combos from URL
                } else {
                    // Add
                    selectedServices.comboIds.push(id);
                    selectedServices.items.push({
                        type: 'combo',
                        id: id,
                        name: this.getAttribute('data-combo-name') || 'Combo',
                        price: parseFloat(this.getAttribute('data-combo-price')) || 0,
                        duration: parseInt(this.getAttribute('data-combo-duration')) || 60
                    });
                }
            }
            
            updateSummary();
        });
    });
    
    // Handle done button
    document.getElementById('done-button')?.addEventListener('click', function() {
        // Check if we're in add_more mode - if so, merge with existing services from URL
        const urlParams = new URLSearchParams(window.location.search);
        const isAddMore = urlParams.get('add_more') === 'true' || urlParams.get('add_more') === '1';
        
        // Build URL with selected services
        const params = {};
        let totalCount = 0;
        
        if (isAddMore) {
            // Get existing services from URL (these MUST be kept - always include them)
            // Handle both formats: service_id[] and service_id[0], service_id[1], etc.
            let existingServiceIds = [];
            urlParams.forEach((value, key) => {
                if (key === 'service_id[]' || /^service_id\[\d+\]$/.test(key)) {
                    const id = parseInt(value);
                    if (!isNaN(id) && !existingServiceIds.includes(id)) {
                        existingServiceIds.push(id);
                    }
                }
            });
            
            let existingVariantIds = [];
            urlParams.forEach((value, key) => {
                if (key === 'service_variants[]' || /^service_variants\[\d+\]$/.test(key)) {
                    const id = parseInt(value);
                    if (!isNaN(id) && !existingVariantIds.includes(id)) {
                        existingVariantIds.push(id);
                    }
                }
            });
            
            let existingComboIds = [];
            urlParams.forEach((value, key) => {
                if (key === 'combo_id[]' || /^combo_id\[\d+\]$/.test(key)) {
                    const id = parseInt(value);
                    if (!isNaN(id) && !existingComboIds.includes(id)) {
                        existingComboIds.push(id);
                    }
                }
            });
            
            // Get ALL currently selected services (includes both existing from URL + newly selected)
            // Use Set to automatically remove duplicates when merging
            const allServiceIdsSet = new Set([...existingServiceIds, ...selectedServices.serviceIds]);
            const allVariantIdsSet = new Set([...existingVariantIds, ...selectedServices.variantIds]);
            const allComboIdsSet = new Set([...existingComboIds, ...selectedServices.comboIds]);
            
            // Convert back to arrays
            const allServiceIds = Array.from(allServiceIdsSet);
            const allVariantIds = Array.from(allVariantIdsSet);
            const allComboIds = Array.from(allComboIdsSet);
            
            totalCount = allServiceIds.length + allVariantIds.length + allComboIds.length;
            
            // ALWAYS include all merged services/variants/combos
            if (allServiceIds.length > 0) {
                params['service_id'] = allServiceIds;
            }
            if (allVariantIds.length > 0) {
                params['service_variants'] = allVariantIds;
            }
            if (allComboIds.length > 0) {
                params['combo_id'] = allComboIds;
            }
            
            console.log('=== MERGE IN ADD_MORE MODE ===');
            console.log('Existing from URL - services:', existingServiceIds);
            console.log('Existing from URL - variants:', existingVariantIds);
            console.log('Existing from URL - combos:', existingComboIds);
            console.log('Currently selected - services:', selectedServices.serviceIds);
            console.log('Currently selected - variants:', selectedServices.variantIds);
            console.log('Currently selected - combos:', selectedServices.comboIds);
            console.log('All services (merged with Set):', allServiceIds);
            console.log('All variants (merged with Set):', allVariantIds);
            console.log('All combos (merged with Set):', allComboIds);
            console.log('Total count:', totalCount);
            console.log('Params object:', params);
        } else {
            // Normal mode - just use selected services
            if (selectedServices.serviceIds.length > 0) {
                params['service_id'] = selectedServices.serviceIds;
                totalCount += selectedServices.serviceIds.length;
            }
            if (selectedServices.variantIds.length > 0) {
                params['service_variants'] = selectedServices.variantIds;
                totalCount += selectedServices.variantIds.length;
            }
            if (selectedServices.comboIds.length > 0) {
                params['combo_id'] = selectedServices.comboIds;
                totalCount += selectedServices.comboIds.length;
            }
        }
        
        // Validate that at least one service is selected
        if (totalCount === 0) {
            alert('Vui lòng chọn ít nhất một dịch vụ');
            return;
        }
        
        // Build query string with array notation
        const queryParts = [];
        Object.keys(params).forEach(key => {
            if (Array.isArray(params[key])) {
                params[key].forEach(val => {
                    queryParts.push(encodeURIComponent(key + '[]') + '=' + encodeURIComponent(val));
                });
            } else {
                queryParts.push(encodeURIComponent(key) + '=' + encodeURIComponent(params[key]));
            }
        });
        
        const queryString = queryParts.join('&');
        const baseUrl = '{{ route("site.appointment.create") }}';
        const finalUrl = baseUrl + (queryString ? '?' + queryString : '');
        
        console.log('=== FINAL URL ===');
        console.log('Base URL:', baseUrl);
        console.log('Query string:', queryString);
        console.log('Final URL:', finalUrl);
        
        // Redirect to create appointment page
        window.location.href = finalUrl;
    });
    
    // Initialize when DOM is ready
    initializeFromUrl();
    
    // Tooltip handling for services with variants
    const selectButtons = document.querySelectorAll('.select-service-btn');
    selectButtons.forEach(function(button) {
        const hasVariants = button.getAttribute('data-has-variants') === 'true';
        if (!hasVariants) return;
        
        const svcActions = button.closest('.svc-actions');
        const tooltip = svcActions.querySelector('.variants-tooltip');
        if (!tooltip) return;
        
        let tooltipTimeout;
        
        button.addEventListener('mouseenter', function() {
            clearTimeout(tooltipTimeout);
            tooltip.style.display = 'block';
            void tooltip.offsetWidth;
            
            const buttonRect = button.getBoundingClientRect();
            const tooltipRect = tooltip.getBoundingClientRect();
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            
            tooltip.style.left = 'calc(100% + 8px)';
            tooltip.style.right = 'auto';
            tooltip.style.top = '50%';
            tooltip.style.bottom = 'auto';
            tooltip.style.transform = 'translateY(-50%)';
            
            const tooltipWidth = tooltipRect.width || 380;
            const tooltipHeight = tooltipRect.height || 200;
            const buttonCenterY = buttonRect.top + buttonRect.height / 2;
            
            if (buttonRect.right + tooltipWidth + 8 > viewportWidth - 10) {
                tooltip.style.left = 'auto';
                tooltip.style.right = 'calc(100% + 8px)';
            }
            
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
        
        button.addEventListener('mouseleave', function() {
            tooltipTimeout = setTimeout(function() {
                tooltip.style.display = 'none';
            }, 200);
        });
        
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
        
        let hideTimeout;
        svcActions.addEventListener('mouseleave', function() {
            hideTimeout = setTimeout(function() {
                tooltip.style.display = 'none';
            }, 200);
        });
        
        svcActions.addEventListener('mouseenter', function() {
            if (hideTimeout) {
                clearTimeout(hideTimeout);
                hideTimeout = null;
            }
        });
    });
    
    // Initialize summary
    updateSummary();
    
    // Update summary bar width on window resize
    window.addEventListener('resize', function() {
        updateSummaryBarWidth();
    });
    
    // Initial width update
    setTimeout(updateSummaryBarWidth, 100);
});
</script>
@endsection

