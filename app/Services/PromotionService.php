<?php

namespace App\Services;

use App\Models\Promotion;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PromotionService
{
    /**
     * Get all active promotions.
     */
    public function getAll()
    {
        return Promotion::with(['services', 'combos', 'serviceVariants'])
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get all trashed promotions.
     */
    public function getTrashed()
    {
        return Promotion::with(['services', 'combos', 'serviceVariants'])
            ->onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get();
    }

    /**
     * Get one promotion by id.
     */
    public function getOne($id)
    {
        return Promotion::with([
            'services.category',
            'combos.category',
            'serviceVariants.service.category'
        ])->findOrFail($id);
    }

    /**
     * Create a new promotion.
     */
    public function create(array $data, array $serviceIds = [], array $comboIds = [], array $variantIds = [])
    {
        return DB::transaction(function () use ($data, $serviceIds, $comboIds, $variantIds) {
            // Đảm bảo discount_percent và discount_amount được set đúng
            if (isset($data['discount_type'])) {
                if ($data['discount_type'] === 'percent') {
                    $data['discount_amount'] = null;
                    // Đảm bảo discount_percent không null
                    if (!isset($data['discount_percent']) || $data['discount_percent'] === null) {
                        $data['discount_percent'] = 0;
                    }
                } else {
                    $data['discount_percent'] = 0; // Set 0 thay vì null để tránh lỗi database
                    // Đảm bảo discount_amount không null
                    if (!isset($data['discount_amount']) || $data['discount_amount'] === null) {
                        $data['discount_amount'] = 0;
                    }
                }
            }
            
            // Tự động cập nhật trạng thái dựa trên ngày
            $this->autoUpdateStatus($data);
            
            $promotion = Promotion::create($data);
            
            $this->syncPromotionServices($promotion->id, $serviceIds, $comboIds, $variantIds);
            
            return $promotion->load(['services', 'combos', 'serviceVariants']);
        });
    }

    /**
     * Update a promotion.
     */
    public function update($id, array $data, array $serviceIds = [], array $comboIds = [], array $variantIds = [])
    {
        return DB::transaction(function () use ($id, $data, $serviceIds, $comboIds, $variantIds) {
            // Đảm bảo discount_percent và discount_amount được set đúng
            if (isset($data['discount_type'])) {
                if ($data['discount_type'] === 'percent') {
                    $data['discount_amount'] = null;
                    // Đảm bảo discount_percent không null
                    if (!isset($data['discount_percent']) || $data['discount_percent'] === null) {
                        $data['discount_percent'] = 0;
                    }
                } else {
                    $data['discount_percent'] = 0; // Set 0 thay vì null để tránh lỗi database
                    // Đảm bảo discount_amount không null
                    if (!isset($data['discount_amount']) || $data['discount_amount'] === null) {
                        $data['discount_amount'] = 0;
                    }
                }
            }
            
            $promotion = Promotion::findOrFail($id);
            
            // Tự động cập nhật trạng thái dựa trên ngày
            $this->autoUpdateStatus($data);
            
            $promotion->update($data);
            
            $this->syncPromotionServices($promotion->id, $serviceIds, $comboIds, $variantIds);
            
            return $promotion->load(['services', 'combos', 'serviceVariants']);
        });
    }

    /**
     * Soft delete a promotion.
     */
    public function delete($id)
    {
        $promotion = Promotion::findOrFail($id);
        return $promotion->delete();
    }

    /**
     * Restore a trashed promotion.
     */
    public function restore($id)
    {
        $promotion = Promotion::onlyTrashed()->findOrFail($id);
        return $promotion->restore();
    }

    /**
     * Permanently delete a promotion.
     */
    public function forceDelete($id)
    {
        $promotion = Promotion::onlyTrashed()->findOrFail($id);
        
        return DB::transaction(function () use ($promotion) {
            // Xóa các quan hệ trong bảng pivot
            DB::table('promotion_service')->where('promotion_id', $promotion->id)->delete();
            
            // Xóa vĩnh viễn promotion
            return $promotion->forceDelete();
        });
    }

    /**
     * Sync promotion services, combos, and variants.
     */
    protected function syncPromotionServices($promotionId, array $serviceIds, array $comboIds, array $variantIds)
    {
        // Xóa tất cả dữ liệu cũ
        DB::table('promotion_service')->where('promotion_id', $promotionId)->delete();

        // Thêm services
        if (!empty($serviceIds)) {
            foreach ($serviceIds as $serviceId) {
                DB::table('promotion_service')->insert([
                    'promotion_id' => $promotionId,
                    'service_id' => $serviceId,
                    'combo_id' => null,
                    'service_variant_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Thêm combos
        if (!empty($comboIds)) {
            foreach ($comboIds as $comboId) {
                DB::table('promotion_service')->insert([
                    'promotion_id' => $promotionId,
                    'service_id' => null,
                    'combo_id' => $comboId,
                    'service_variant_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Thêm service variants
        if (!empty($variantIds)) {
            foreach ($variantIds as $variantId) {
                DB::table('promotion_service')->insert([
                    'promotion_id' => $promotionId,
                    'service_id' => null,
                    'combo_id' => null,
                    'service_variant_id' => $variantId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Validate and calculate discount for a promotion code.
     * 
     * @param string $code Promotion code
     * @param array $cartItems Cart items with structure: [['type' => 'service_variant|combo|appointment', 'id' => int, ...], ...]
     * @param float $subtotal Total amount before discount
     * @param int|null $userId User ID for checking per_user_limit
     * @return array ['valid' => bool, 'promotion' => Promotion|null, 'discount_amount' => float, 'message' => string]
     */
    public function validateAndCalculateDiscount($code, $cartItems, $subtotal, $userId = null)
    {
        $now = Carbon::now();
        
        $promotion = Promotion::with(['services', 'combos', 'serviceVariants'])
            ->where('code', $code)
            ->whereNull('deleted_at')
            ->first();

        if (!$promotion) {
            return [
                'valid' => false,
                'promotion' => null,
                'discount_amount' => 0,
                'message' => 'Mã khuyến mại không tồn tại hoặc đã bị xóa.'
            ];
        }

        // 1. Check Status explicitly set by admin
        if ($promotion->status === 'inactive') {
            return [
                'valid' => false,
                'promotion' => $promotion,
                'discount_amount' => 0,
                'message' => 'Mã khuyến mại này đã bị ngừng áp dụng.'
            ];
        }

        // 2. Check Date Range
        $startDate = $promotion->start_date ? $promotion->start_date->startOfDay() : null;
        $endDate = $promotion->end_date ? $promotion->end_date->endOfDay() : null;

        if ($startDate && $now->lt($startDate)) {
            return [
                'valid' => false,
                'promotion' => $promotion,
                'discount_amount' => 0,
                'message' => 'Mã khuyến mại này chưa đến thời gian áp dụng. (Bắt đầu: ' . $startDate->format('d/m/Y') . ')'
            ];
        }

        if ($endDate && $now->gt($endDate)) {
            return [
                'valid' => false,
                'promotion' => $promotion,
                'discount_amount' => 0,
                'message' => 'Mã khuyến mại này đã hết hạn. (Kết thúc: ' . $endDate->format('d/m/Y') . ')'
            ];
        }
        
        // 3. Check Customer Tier (if apply_scope = customer_tier)
        if ($promotion->apply_scope === 'customer_tier') {
            if (!$userId) {
                return [
                    'valid' => false,
                    'promotion' => $promotion,
                    'discount_amount' => 0,
                    'message' => 'Vui lòng đăng nhập để sử dụng mã khuyến mại áp dụng theo hạng khách hàng.'
                ];
            }

            $user = \App\Models\User::find($userId);
            if (!$user) {
                return [
                    'valid' => false,
                    'promotion' => $promotion,
                    'discount_amount' => 0,
                    'message' => 'Không tìm thấy thông tin khách hàng để áp dụng mã khuyến mại.'
                ];
            }

            // Map hạng sang mức độ (1 thấp -> 4 cao)
            $tierOrder = [
                'Khách thường' => 1,
                'Silver' => 2,
                'Gold' => 3,
                'VIP' => 4,
            ];

            $userTier = $user->tier ?? 'Khách thường';
            $requiredTier = $promotion->min_customer_tier ?: 'Khách thường';

            $userLevel = $tierOrder[$userTier] ?? 1;
            $requiredLevel = $tierOrder[$requiredTier] ?? 1;

            if ($userLevel < $requiredLevel) {
                return [
                    'valid' => false,
                    'promotion' => $promotion,
                    'discount_amount' => 0,
                    'message' => 'Mã khuyến mại này chỉ áp dụng cho khách hàng từ hạng ' . $requiredTier . ' trở lên.'
                ];
            }
        }

        // 4. Check Global Usage Limit
        if ($promotion->usage_limit) {
            $totalUsage = \App\Models\PromotionUsage::where('promotion_id', $promotion->id)->count();
            
            if ($totalUsage >= $promotion->usage_limit) {
                return [
                    'valid' => false,
                    'promotion' => $promotion,
                    'discount_amount' => 0,
                    'message' => 'Mã khuyến mại này đã được sử dụng hết số lượt cho phép.'
                ];
            }
        }

        // 5. Check User Limit
        if ($promotion->per_user_limit && $userId) {
            $usageCount = \App\Models\PromotionUsage::where('promotion_id', $promotion->id)
                ->where('user_id', $userId)
                ->count();
            
            if ($usageCount >= $promotion->per_user_limit) {
                return [
                    'valid' => false,
                    'promotion' => $promotion,
                    'discount_amount' => 0,
                    'message' => 'Bạn đã sử dụng hết số lần được phép cho mã khuyến mại này.'
                ];
            }
        }

        // 6. Check Min Order Amount
        if ($promotion->min_order_amount && $subtotal < $promotion->min_order_amount) {
            return [
                'valid' => false,
                'promotion' => $promotion,
                'discount_amount' => 0,
                'message' => 'Đơn hàng chưa đạt mức tối thiểu ' . number_format($promotion->min_order_amount, 0, ',', '.') . '₫.'
            ];
        }

        $discountAmount = 0;
        $applicableAmount = 0;

        // 7. Calculate Discount
        if ($promotion->apply_scope === 'service') {
            // Get IDs of applicable items (ensure integer)
            $validServiceIds = $promotion->services->pluck('id')->map(fn($id) => (int)$id)->toArray();
            $validComboIds = $promotion->combos->pluck('id')->map(fn($id) => (int)$id)->toArray();
            $validVariantIds = $promotion->serviceVariants->pluck('id')->map(fn($id) => (int)$id)->toArray();

            // If no specific services selected, assume it applies to ALL services
            $applyToAll = empty($validServiceIds) && empty($validComboIds) && empty($validVariantIds);

            foreach ($cartItems as $item) {
                $itemType = $item['type'] ?? '';
                $itemId = $item['id'] ?? 0;
                $quantity = $item['quantity'] ?? 1;
                
                // Helper to check and add price
                $checkAndAdd = function($price, $sId = null, $cId = null, $vId = null) use (
                    $applyToAll, $validServiceIds, $validComboIds, $validVariantIds, &$applicableAmount
                ) {
                    if ($price <= 0) return;

                    if ($applyToAll) {
                        $applicableAmount += $price;
                        return;
                    }

                    $isValid = false;
                    
                    // 1. Direct checks
                    if ($vId && in_array((int)$vId, $validVariantIds, true)) $isValid = true;
                    if ($cId && in_array((int)$cId, $validComboIds, true)) $isValid = true;
                    if ($sId && in_array((int)$sId, $validServiceIds, true)) $isValid = true;
                    
                    // 2. Combo content check (if not valid yet and is a combo)
                    if (!$isValid && $cId && !empty($validServiceIds)) {
                        $combo = \App\Models\Combo::with('services')->find($cId);
                        if ($combo) {
                            foreach ($combo->services as $cSvc) {
                                if (in_array((int)$cSvc->id, $validServiceIds, true)) {
                                    $isValid = true;
                                    break;
                                }
                            }
                        }
                    }

                    if ($isValid) {
                        $applicableAmount += $price;
                    }
                };

                // Handle Cart Item Types
                if ($itemType === 'service_variant') {
                    $variant = \App\Models\ServiceVariant::with('service')->find($itemId);
                    if ($variant) {
                        $price = $variant->price * $quantity;
                        $checkAndAdd($price, $variant->service_id, null, $variant->id);
                    }
                } elseif ($itemType === 'combo') {
                    $combo = \App\Models\Combo::find($itemId);
                    if ($combo) {
                        $price = $combo->price * $quantity;
                        $checkAndAdd($price, null, $combo->id, null);
                    }
                } elseif ($itemType === 'appointment') {
                    $appointment = \App\Models\Appointment::with([
                        'appointmentDetails.serviceVariant.service',
                        'appointmentDetails.combo'
                    ])->find($itemId);

                    if ($appointment) {
                        foreach ($appointment->appointmentDetails as $detail) {
                            $price = $detail->price_snapshot; // Prioritize snapshot
                            
                            // Fallback price if snapshot is missing
                            if ($price === null) {
                                $price = $detail->serviceVariant->price 
                                    ?? ($detail->combo->price ?? 0);
                            }

                            $sId = null;
                            $vId = $detail->service_variant_id;
                            $cId = $detail->combo_id;
                            $notes = $detail->notes;
                            
                            // Handle Soft Deleted or Missing Relations
                            if ($vId && !$detail->serviceVariant) {
                                $trashedVariant = \App\Models\ServiceVariant::withTrashed()->find($vId);
                                if ($trashedVariant) {
                                    $sId = $trashedVariant->service_id;
                                }
                            } else {
                                $sId = $detail->serviceVariant ? $detail->serviceVariant->service_id : null;
                            }
                            
                            // Handle Note-based Services (Custom/Legacy)
                            if (!$sId && !$vId && !$cId && $notes) {
                                // Try to find service by name matching the notes
                                // We use a loose match or exact match depending on data quality
                                // Try exact match first
                                $serviceByNote = \App\Models\Service::where('name', $notes)->first();
                                if (!$serviceByNote) {
                                     // Try loose match
                                     $serviceByNote = \App\Models\Service::where('name', 'like', "%{$notes}%")->first();
                                }
                                
                                if ($serviceByNote) {
                                    $sId = $serviceByNote->id;
                                }
                            }
                            
                            if ($cId && !$detail->combo) {
                                // Try finding trashed combo
                                $trashedCombo = \App\Models\Combo::withTrashed()->find($cId);
                                if ($trashedCombo) {
                                    // Logic for trashed combo content check if needed
                                }
                            }

                            // Fallback price
                            if ($price === null) {
                                $price = $detail->price_snapshot ?? 0;
                                if ($price == 0 && $vId) {
                                     $v = $detail->serviceVariant ?? \App\Models\ServiceVariant::withTrashed()->find($vId);
                                     $price = $v->price ?? 0;
                                }
                            }
                            
                            $checkAndAdd($price, $sId, $cId, $vId);
                        }
                    }
                }
            }

            if ($applicableAmount <= 0) {
                // Debug info construction
                $debugInfo = "Debug: ValidServices=[" . implode(',', $validServiceIds) . "], ValidCombos=[" . implode(',', $validComboIds) . "], ValidVariants=[" . implode(',', $validVariantIds) . "]. ";
                $debugInfo .= "CartItems checked: ";
                
                $itemsInfo = [];
                foreach ($cartItems as $item) {
                     $type = $item['type'] ?? 'unknown';
                     $iId = $item['id'] ?? 0;
                     
                     if ($type === 'service_variant') {
                         $v = \App\Models\ServiceVariant::withTrashed()->find($iId);
                         $sId = $v ? $v->service_id : 'null';
                         $itemsInfo[] = "Variant $iId (Service $sId)";
                     } elseif ($type === 'appointment') {
                         $appt = \App\Models\Appointment::with(['appointmentDetails.serviceVariant', 'appointmentDetails.combo'])->find($iId);
                         $apptDetails = [];
                         if ($appt) {
                             foreach ($appt->appointmentDetails as $d) {
                                 $dsId = $d->serviceVariant ? $d->serviceVariant->service_id : 'null';
                                 $dRawV = $d->service_variant_id ?? 'null';
                                 $dRawC = $d->combo_id ?? 'null';
                                 $dNotes = $d->notes ? "Note:{$d->notes}" : '';
                                 
                                 if ($dsId === 'null' && $dRawV !== 'null') {
                                     $trashedV = \App\Models\ServiceVariant::withTrashed()->find($dRawV);
                                     if ($trashedV) $dsId = $trashedV->service_id . "(trashed)";
                                 }
                                 
                                 // Check resolved by note
                                 if ($dsId === 'null' && $dRawV === 'null' && $dRawC === 'null' && $d->notes) {
                                     $svc = \App\Models\Service::where('name', $d->notes)->first();
                                     if (!$svc) $svc = \App\Models\Service::where('name', 'like', "%{$d->notes}%")->first();
                                     if ($svc) $dsId = $svc->id . "(byNote)";
                                 }
                                 
                                 $apptDetails[] = "Detail(S:$dsId, RawV:$dRawV, RawC:$dRawC, $dNotes)";
                             }
                         }
                         $itemsInfo[] = "Appt $iId [" . implode(',', $apptDetails) . "]";
                     } elseif ($type === 'combo') {
                         $itemsInfo[] = "Combo $iId";
                     } else {
                         $itemsInfo[] = "$type $iId";
                     }
                }
                $debugInfo .= implode('; ', $itemsInfo);

                return [
                    'valid' => false,
                    'promotion' => $promotion,
                    'discount_amount' => 0,
                    'message' => 'Mã khuyến mại không áp dụng cho các dịch vụ trong giỏ hàng. ' . $debugInfo
                ];
            }
            
            // Calculate based on applicable amount
            if ($promotion->discount_type === 'percent') {
                $discountAmount = $applicableAmount * ($promotion->discount_percent / 100);
            } else {
                // Fixed amount: usually applied once per order, or per item?
                // Standard logic: Fixed amount off the total applicable items value.
                // But ensure we don't discount more than the applicable amount.
                $discountAmount = min($promotion->discount_amount, $applicableAmount);
            }

        } else {
            // Apply Scope: ORDER / CUSTOMER TIER (All items)
            $applicableAmount = $subtotal;
            
            if ($promotion->discount_type === 'percent') {
                $discountAmount = $subtotal * ($promotion->discount_percent / 100);
            } else {
                $discountAmount = $promotion->discount_amount;
            }
        }

        // 6. Max Discount Amount (for percent)
        if ($promotion->discount_type === 'percent' && $promotion->max_discount_amount > 0) {
            $discountAmount = min($discountAmount, $promotion->max_discount_amount);
        }

        // 7. Final cap at subtotal (cannot discount more than total value)
        $discountAmount = min($discountAmount, $subtotal);

        return [
            'valid' => true,
            'promotion' => $promotion,
            'discount_amount' => round($discountAmount, 2),
            'message' => 'Áp dụng mã khuyến mại thành công!'
        ];
    }

    /**
     * Tự động cập nhật trạng thái khuyến mãi dựa trên ngày bắt đầu và kết thúc.
     */
    public function autoUpdateStatus(array &$data)
    {
        // Not modifying 'inactive' status if explicitly set
        if (isset($data['status']) && $data['status'] === 'inactive') {
            return;
        }

        $now = Carbon::now();
        $startDate = isset($data['start_date']) ? Carbon::parse($data['start_date'])->startOfDay() : null;
        $endDate = isset($data['end_date']) ? Carbon::parse($data['end_date'])->endOfDay() : null;

        if ($startDate && $now->lt($startDate)) {
            $data['status'] = 'scheduled';
        } elseif ($endDate && $now->gt($endDate)) {
            $data['status'] = 'expired';
        } else {
            $data['status'] = 'active';
        }
    }
}
