@extends('layouts.site')

@section('title', 'Chọn ưu đãi')

@section('content')
<div class="offers-page" style="padding: 140px 0 40px; background: #f8f9fa; min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-7">
                <div class="offers-container" style="background: #fff; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden;">
                    <!-- Header -->
                    <div class="offers-header" style="background: #fff; padding: 15px 20px; border-bottom: 1px solid #e0e0e0; display: flex; align-items: center; gap: 15px;">
                        @php
                            // Build URL with selected services to preserve them when going back
                            $backUrlParams = [];
                            
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
                                    $backUrlParams[] = 'service_id[]=' . urlencode($serviceId);
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
                                    $backUrlParams[] = 'service_variants[]=' . urlencode($variantId);
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
                                    $backUrlParams[] = 'combo_id[]=' . urlencode($comboId);
                                }
                            }
                            
                            // Build final URL
                            $backUrl = route('site.appointment.select-services');
                            if (!empty($backUrlParams)) {
                                $backUrl .= '?' . implode('&', $backUrlParams);
                            }
                        @endphp
                        <a href="{{ $backUrl }}" style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; color: #0066cc; text-decoration: none;">
                            <i class="fa fa-arrow-left" style="font-size: 18px;"></i>
                        </a>
                        <h1 style="font-size: 16px; font-weight: 600; color: #0066cc; margin: 0; flex: 1;">Ưu đãi từ Foly Hair</h1>
                    </div>

                    <!-- Tab Navigation -->
                    <div class="offers-tabs" style="background: #fff; border-bottom: 1px solid #e0e0e0; display: flex;">
                        <button type="button" 
                                class="offer-tab active" 
                                data-tab="public"
                                style="flex: 1; padding: 15px; background: none; border: none; border-bottom: 3px solid #000; color: #000; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                            Ưu đãi từ Foly Hair
                        </button>
                        <button type="button" 
                                class="offer-tab" 
                                data-tab="personal"
                                style="flex: 1; padding: 15px; background: none; border: none; border-bottom: 3px solid transparent; color: #999; font-size: 14px; font-weight: 400; cursor: pointer; transition: all 0.3s;">
                            Ưu đãi của riêng anh
                        </button>
                    </div>

                    <!-- Main Content -->
                    <div class="offers-content" style="background: #f5f5f5; min-height: calc(100vh - 400px); padding: 20px;">
        <!-- Public Offers Tab -->
        <div id="publicOffersTab" class="offer-tab-content active" style="display: block;">
            @if($publicOffers && $publicOffers->count() > 0)
                <div class="offers-list" style="display: flex; flex-direction: column; gap: 15px;">
                    @foreach($publicOffers as $offer)
                        <div class="offer-item" style="background: #fff; border-radius: 12px; padding: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); cursor: pointer;">
                            <div style="display: flex; align-items: flex-start; gap: 12px;">
                                <div style="width: 50px; height: 50px; background: #ffc107; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <span style="color: #fff; font-size: 24px; font-weight: 700;">%</span>
                                </div>
                                <div style="flex: 1;">
                                    <h3 style="font-size: 16px; font-weight: 600; color: #000; margin: 0 0 8px 0;">{{ $offer->name ?? 'Ưu đãi' }}</h3>
                                    <p style="font-size: 14px; color: #666; margin: 0 0 8px 0;">{{ $offer->description ?? '' }}</p>
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                        @if($offer->discount_type === 'percent')
                                            <span style="font-size: 14px; font-weight: 600; color: #28a745;">Giảm {{ $offer->discount_percent ?? 0 }}%</span>
                                        @else
                                            <span style="font-size: 14px; font-weight: 600; color: #28a745;">Giảm {{ number_format($offer->discount_amount ?? 0, 0, ',', '.') }} VNĐ</span>
                                        @endif
                                        @if($offer->code)
                                            <span style="font-size: 12px; color: #999;">({{ $offer->code }})</span>
                                        @endif
                                    </div>
                                    <div style="font-size: 12px; color: #999;">
                                        @if($offer->end_date)
                                            Có hiệu lực đến: {{ \Carbon\Carbon::parse($offer->end_date)->format('d/m/Y') }}
                                        @endif
                                    </div>
                                </div>
                                <div class="offer-select-box" 
                                     data-promotion-id="{{ $offer->id }}"
                                     style="width: 24px; height: 24px; border: 2px solid #ddd; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; margin-top: 4px; transition: all 0.2s;">
                                    <div class="offer-check-indicator" style="width: 14px; height: 14px; background: #0066cc; border-radius: 50%; display: none;"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align: center; padding: 60px 20px;">
                    <p style="font-size: 14px; color: #999; font-style: italic; margin: 0;">Không có ưu đãi</p>
                </div>
            @endif
        </div>

        <!-- Personal Offers Tab -->
        <div id="personalOffersTab" class="offer-tab-content" style="display: none;">
            @if($personalOffers && $personalOffers->count() > 0)
                <div class="offers-list" style="display: flex; flex-direction: column; gap: 15px;">
                    @foreach($personalOffers as $offer)
                        <div class="offer-item" style="background: #fff; border-radius: 12px; padding: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); cursor: pointer;">
                            <div style="display: flex; align-items: flex-start; gap: 12px;">
                                <div style="width: 50px; height: 50px; background: #ffc107; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <span style="color: #fff; font-size: 24px; font-weight: 700;">%</span>
                                </div>
                                <div style="flex: 1;">
                                    <h3 style="font-size: 16px; font-weight: 600; color: #000; margin: 0 0 8px 0;">{{ $offer->name ?? 'Ưu đãi' }}</h3>
                                    <p style="font-size: 14px; color: #666; margin: 0 0 8px 0;">{{ $offer->description ?? '' }}</p>
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                        @if($offer->discount_type === 'percent')
                                            <span style="font-size: 14px; font-weight: 600; color: #28a745;">Giảm {{ $offer->discount_percent ?? 0 }}%</span>
                                        @else
                                            <span style="font-size: 14px; font-weight: 600; color: #28a745;">Giảm {{ number_format($offer->discount_amount ?? 0, 0, ',', '.') }} VNĐ</span>
                                        @endif
                                        @if($offer->code)
                                            <span style="font-size: 12px; color: #999;">({{ $offer->code }})</span>
                                        @endif
                                    </div>
                                    <div style="font-size: 12px; color: #999;">
                                        @if($offer->end_date)
                                            Có hiệu lực đến: {{ \Carbon\Carbon::parse($offer->end_date)->format('d/m/Y') }}
                                        @endif
                                    </div>
                                </div>
                                <div class="offer-select-box" 
                                     data-promotion-id="{{ $offer->id }}"
                                     style="width: 24px; height: 24px; border: 2px solid #ddd; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; margin-top: 4px; transition: all 0.2s;">
                                    <div class="offer-check-indicator" style="width: 14px; height: 14px; background: #0066cc; border-radius: 50%; display: none;"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align: center; padding: 60px 20px; background: #f5f5f5;">
                    <p style="font-size: 14px; color: #999; font-style: italic; margin: 0;">Không có ưu đãi</p>
                </div>
            @endif
        </div>
                    </div>
                    
                    <!-- Action Button (Inside Container) -->
                    <div class="offers-action-bar" style="background: #fff; border-top: 1px solid #e0e0e0; padding: 15px 20px;">
                        <button type="button" 
                                id="applyOfferBtn"
                                style="width: 100%; padding: 14px; background: #e0e0e0; border: none; border-radius: 8px; color: #999; font-size: 14px; font-weight: 600; text-transform: uppercase; cursor: pointer; transition: all 0.3s;">
                            ÁP DỤNG ƯU ĐÃI
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .offer-tab.active {
        color: #000 !important;
        font-weight: 600 !important;
        border-bottom-color: #000 !important;
    }
    
    .offer-tab:not(.active) {
        color: #999 !important;
        font-weight: 400 !important;
        border-bottom-color: transparent !important;
    }
    
    .offer-item:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
    }
    
    .offer-item.selected {
        border: 2px solid #0066cc !important;
    }
    
    #applyOfferBtn.has-selection {
        background: #0066cc !important;
        color: #fff !important;
        cursor: pointer !important;
    }
    
    #applyOfferBtn:active {
        transform: scale(0.98);
    }
    
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabs = document.querySelectorAll('.offer-tab');
    const tabContents = document.querySelectorAll('.offer-tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Update tab styles
            tabs.forEach(t => {
                t.classList.remove('active');
                t.style.borderBottomColor = 'transparent';
                t.style.color = '#999';
                t.style.fontWeight = '400';
            });
            
            this.classList.add('active');
            this.style.borderBottomColor = '#000';
            this.style.color = '#000';
            this.style.fontWeight = '600';
            
            // Update tab content
            tabContents.forEach(content => {
                content.style.display = 'none';
            });
            
            if (targetTab === 'public') {
                document.getElementById('publicOffersTab').style.display = 'block';
            } else {
                document.getElementById('personalOffersTab').style.display = 'block';
            }
        });
    });
    
    // Handle offer selection
    const offerSelectBoxes = document.querySelectorAll('.offer-select-box');
    const offerItems = document.querySelectorAll('.offer-item');
    const applyBtn = document.getElementById('applyOfferBtn');
    let selectedPromotionId = null;
    
    function updateApplyButton() {
        if (selectedPromotionId) {
            applyBtn.classList.add('has-selection');
            applyBtn.style.background = '#0066cc';
            applyBtn.style.color = '#fff';
        } else {
            applyBtn.classList.remove('has-selection');
            applyBtn.style.background = '#e0e0e0';
            applyBtn.style.color = '#999';
        }
    }
    
    function toggleOfferSelection(selectBox) {
        const promotionId = selectBox.getAttribute('data-promotion-id');
        const indicator = selectBox.querySelector('.offer-check-indicator');
        const offerItem = selectBox.closest('.offer-item');
        const isCurrentlySelected = selectedPromotionId === promotionId;
        
        // If clicking on already selected offer, deselect it
        if (isCurrentlySelected) {
            selectedPromotionId = null;
            indicator.style.display = 'none';
            selectBox.style.borderColor = '#ddd';
            if (offerItem) {
                offerItem.style.border = 'none';
                offerItem.style.boxShadow = '0 2px 8px rgba(0,0,0,0.05)';
            }
        } else {
            // Deselect all other offers first
            offerSelectBoxes.forEach(box => {
                box.querySelector('.offer-check-indicator').style.display = 'none';
                box.style.borderColor = '#ddd';
            });
            offerItems.forEach(item => {
                item.style.border = 'none';
                item.style.boxShadow = '0 2px 8px rgba(0,0,0,0.05)';
            });
            
            // Select this offer
            selectedPromotionId = promotionId;
            indicator.style.display = 'block';
            selectBox.style.borderColor = '#0066cc';
            if (offerItem) {
                offerItem.style.border = '2px solid #0066cc';
                offerItem.style.boxShadow = '0 4px 12px rgba(0,102,204,0.2)';
            }
        }
        
        updateApplyButton();
    }
    
    // Handle click on select box
    offerSelectBoxes.forEach(selectBox => {
        selectBox.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleOfferSelection(this);
        });
    });
    
    // Handle click on offer item (click anywhere on the card)
    offerItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Don't trigger if clicking directly on the select box (already handled)
            if (e.target.closest('.offer-select-box')) {
                return;
            }
            
            const selectBox = this.querySelector('.offer-select-box');
            if (selectBox) {
                toggleOfferSelection(selectBox);
            }
        });
    });
    
    // Handle apply offer button
    applyBtn.addEventListener('click', function() {
        // Build URL with selected services and offer - preserve all existing params
        const currentUrl = window.location.href;
        const urlObj = new URL(currentUrl);
        const params = new URLSearchParams(urlObj.search);
        
        if (selectedPromotionId) {
            // Add or update promotion_id
            params.set('promotion_id', selectedPromotionId);
        } else {
            // Remove promotion_id if no offer selected
            params.delete('promotion_id');
        }
        
        // Redirect back to select-services page with all params
        const selectServicesUrl = '{{ route("site.appointment.select-services") }}' + '?' + params.toString();
        window.location.href = selectServicesUrl;
    });
    
    // Load selected promotion from URL params
    const urlParams = new URLSearchParams(window.location.search);
    const promotionIdFromUrl = urlParams.get('promotion_id');
    if (promotionIdFromUrl) {
        const selectBox = document.querySelector(`.offer-select-box[data-promotion-id="${promotionIdFromUrl}"]`);
        if (selectBox) {
            selectedPromotionId = promotionIdFromUrl;
            const indicator = selectBox.querySelector('.offer-check-indicator');
            const offerItem = selectBox.closest('.offer-item');
            indicator.style.display = 'block';
            selectBox.style.borderColor = '#0066cc';
            if (offerItem) {
                offerItem.style.border = '2px solid #0066cc';
                offerItem.style.boxShadow = '0 4px 12px rgba(0,102,204,0.2)';
            }
        }
    }
    
    // Initialize button state
    updateApplyButton();
});
</script>
@endsection

