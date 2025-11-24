@extends('layouts.site')

@section('title', 'Chọn dịch vụ')

@section('content')
<div class="select-services-page" style="padding: 40px 0 20px; background: #f8f9fa; min-height: 100vh;">
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
                    @if($category->services && $category->services->count() > 0)
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
                                @foreach($category->services as $service)
                                    @php
                                        $imagePath = $service->image ? 'legacy/images/products/' . $service->image : null;
                                        $formattedPrice = number_format($service->base_price ?? 0, 0, ',', '.');
                                        $hasVariants = $service->serviceVariants && $service->serviceVariants->count() > 0;
                                    @endphp
                                    <div class="svc-card service-card-wrapper" 
                                         data-service-id="{{ $service->id }}"
                                         style="border: 1px solid #eee; box-shadow: 0 6px 14px rgba(0,0,0,0.06); background: #fff; display: flex; flex-direction: column; border-radius: 8px; overflow: visible; position: relative;">
                                        <a class="svc-img" href="{{ route('site.services.show', $service->id) }}" style="overflow: hidden; display: block; height: 250px; background: #f5f5f5;">
                                            @if($imagePath && file_exists(public_path($imagePath)))
                                                <img src="{{ asset($imagePath) }}" alt="{{ $service->name }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                                            @elseif($service->image)
                                                <img src="{{ asset('legacy/images/products/' . $service->image) }}" alt="{{ $service->name }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onerror="this.src='{{ asset('legacy/images/products/default.jpg') }}'; this.onerror=null;">
                                            @else
                                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);">
                                                    <i class="fa fa-image" style="font-size: 48px; color: #ccc;"></i>
                                                </div>
                                            @endif
                                        </a>
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
                                                                <i class="fa fa-list-ul" style="margin-right: 8px; color: #FFC107;"></i> Biến thể dịch vụ
                                                            </h5>
                                                            <p style="margin: 4px 0 0 0; font-size: 11px; color: #ccc; font-weight: 500;">{{ $service->name }}</p>
                                                        </div>
                                                        
                                                        <!-- Body -->
                                                        <div class="tooltip-body" style="padding: 12px; max-height: 320px; overflow-y: auto;">
                                                            @foreach($service->serviceVariants as $variant)
                                                                @php
                                                                    $variantPrice = number_format($variant->price ?? 0, 0, ',', '.');
                                                                @endphp
                                                                <a href="{{ route('site.appointment.create') }}?service_variants[]={{ $variant->id }}" 
                                                                   class="variant-item-link" 
                                                                   style="text-decoration: none; display: block;">
                                                                    <div class="variant-item" style="padding: 12px; margin-bottom: 8px; background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; transition: all 0.3s ease; cursor: pointer;">
                                                                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                                                            <div style="flex: 1;">
                                                                                <div style="display: flex; align-items: center; margin-bottom: 6px;">
                                                                                    <div style="width: 4px; height: 4px; background: #000; border-radius: 50%; margin-right: 8px;"></div>
                                                                                    <div style="font-weight: 700; color: #000; font-size: 14px; line-height: 1.3;">{{ $variant->name }}</div>
                                                                                </div>
                                                                                <div style="display: flex; align-items: center; gap: 12px; margin-top: 6px;">
                                                                                    <div style="display: flex; align-items: center; font-size: 12px; color: #666; background: #f5f5f5; padding: 4px 8px; border-radius: 4px;">
                                                                                        <i class="fa fa-clock-o" style="margin-right: 4px; color: #888;"></i>
                                                                                        <span style="font-weight: 600;">{{ $variant->duration ?? 60 }} phút</span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div style="text-align: right; margin-left: 12px;">
                                                                                <div style="font-weight: 800; color: #c08a3f; font-size: 16px; line-height: 1.2;">
                                                                                    {{ $variantPrice }}<span style="font-size: 12px; font-weight: 600;">vnđ</span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                                @if($hasVariants)
                                                    <button type="button"
                                                            class="btn btn-primary w-100 select-service-btn" 
                                                            data-has-variants="true"
                                                            style="background: #000; border: 1px solid #000; color: #fff; padding: 10px 20px; font-size: 14px; font-weight: 600; border-radius: 8px; transition: all 0.3s ease; text-decoration: none; display: inline-block; text-align: center; position: relative; z-index: 1; cursor: pointer;">
                                                        <i class="fa fa-check"></i> Chọn
                                                    </button>
                                                @else
                                                    <a href="{{ route('site.appointment.create') }}?service_id={{ $service->id }}" 
                                                       class="btn btn-primary w-100 select-service-btn" 
                                                       data-has-variants="false"
                                                       style="background: #000; border: 1px solid #000; color: #fff; padding: 10px 20px; font-size: 14px; font-weight: 600; border-radius: 8px; transition: all 0.3s ease; text-decoration: none; display: inline-block; text-align: center; position: relative; z-index: 1;">
                                                        <i class="fa fa-check"></i> Chọn
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="col-12 text-center" style="margin-top: 40px;">
                        <p class="text-muted">Chưa có dịch vụ nào</p>
                    </div>
                @endforelse

                <!-- Combos Section -->
                @if(isset($combos) && $combos->count() > 0)
                    <div class="category-section" style="margin-top: 40px; margin-bottom: 20px;">
                        <div class="d-flex align-items-center mb-3">
                            <span class="bar mr-2" style="width: 10px; height: 28px; background: linear-gradient(135deg, #f6d17a 0%, #d8b26a 50%, #8b5a2b 100%);"></span>
                            <h3 class="category-title mb-0" style="font-size: 20px; font-weight: 800; text-transform: uppercase; color: #000;">
                                COMBO
                            </h3>
                        </div>
                        
                        <!-- Combo Grid -->
                        <div class="service-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
                            @foreach($combos as $combo)
                                @php
                                    $imagePath = $combo->image ? 'legacy/images/products/' . $combo->image : null;
                                    $formattedPrice = number_format($combo->price ?? 0, 0, ',', '.');
                                    // Tính duration từ combo items
                                    $comboDuration = 60;
                                    if ($combo->comboItems && $combo->comboItems->count() > 0) {
                                        $comboDuration = $combo->comboItems->sum(function($item) {
                                            return $item->serviceVariant->duration ?? 60;
                                        });
                                    }
                                @endphp
                                <div class="svc-card service-card-wrapper" 
                                     data-combo-id="{{ $combo->id }}"
                                     style="border: 1px solid #eee; box-shadow: 0 6px 14px rgba(0,0,0,0.06); background: #fff; display: flex; flex-direction: column; border-radius: 8px; overflow: visible; position: relative;">
                                    <a class="svc-img" href="#" style="overflow: hidden; display: block; height: 250px; background: #f5f5f5;">
                                        @if($imagePath && file_exists(public_path($imagePath)))
                                            <img src="{{ asset($imagePath) }}" alt="{{ $combo->name }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                                        @elseif($combo->image)
                                            <img src="{{ asset('legacy/images/products/' . $combo->image) }}" alt="{{ $combo->name }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onerror="this.src='{{ asset('legacy/images/products/default.jpg') }}'; this.onerror=null;">
                                        @else
                                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);">
                                                <i class="fa fa-image" style="font-size: 48px; color: #ccc;"></i>
                                            </div>
                                        @endif
                                    </a>
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
                                            <a href="{{ route('site.appointment.create') }}?combo_id={{ $combo->id }}" 
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
    
    .variant-item-link {
        text-decoration: none !important;
        display: block;
    }
    
    .variant-item-link:hover .variant-item {
        background: linear-gradient(135deg, #fff8e1 0%, #ffe082 100%) !important;
        border-color: #FFC107 !important;
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3) !important;
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
            const tooltipWidth = tooltipRect.width || 380;
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
                    arrows[1].style.borderLeft = '10px solid #ffffff';
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
                    arrows[1].style.borderRight = '10px solid #ffffff';
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
            }, 200); // 200ms delay before hiding
        });
        
        // Cancel hide if mouse enters again
        svcActions.addEventListener('mouseenter', function() {
            if (hideTimeout) {
                clearTimeout(hideTimeout);
                hideTimeout = null;
            }
        });
    });
});
</script>
@endsection

