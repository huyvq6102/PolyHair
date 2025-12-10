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

        // Kiểm tra trạng thái khuyến mãi
        if ($promotion->status === 'inactive') {
            return [
                'valid' => false,
                'promotion' => $promotion,
                'discount_amount' => 0,
                'message' => 'Mã khuyến mại này đã bị ngừng áp dụng.'
            ];
        }

        if ($promotion->status === 'expired') {
            return [
                'valid' => false,
                'promotion' => $promotion,
                'discount_amount' => 0,
                'message' => 'Mã khuyến mại này đã hết hạn.'
            ];
        }

        if ($promotion->status === 'scheduled') {
            return [
                'valid' => false,
                'promotion' => $promotion,
                'discount_amount' => 0,
                'message' => 'Mã khuyến mại này chưa đến thời gian áp dụng.'
            ];
        }

        // Chỉ chấp nhận status = 'active'
        if ($promotion->status !== 'active') {
            return [
                'valid' => false,
                'promotion' => $promotion,
                'discount_amount' => 0,
                'message' => 'Mã khuyến mại này không khả dụng.'
            ];
        }

        // Kiểm tra ngày bắt đầu và kết thúc (double check)
        if ($promotion->start_date && Carbon::parse($promotion->start_date)->gt($now)) {
            return [
                'valid' => false,
                'promotion' => $promotion,
                'discount_amount' => 0,
                'message' => 'Mã khuyến mại này chưa đến thời gian áp dụng.'
            ];
        }

        if ($promotion->end_date && Carbon::parse($promotion->end_date)->lt($now)) {
            return [
                'valid' => false,
                'promotion' => $promotion,
                'discount_amount' => 0,
                'message' => 'Mã khuyến mại này đã hết hạn.'
            ];
        }

        // Kiểm tra thời gian áp dụng sau khi đã tìm thấy promotion
        $messages = [];
        $now = now();
        
        if ($promotion->start_date) {
            // start_date đã được cast thành Carbon trong model
            // So sánh với start of day để cho phép sử dụng trong cả ngày
            $startDate = $promotion->start_date->startOfDay();
            $todayStart = $now->copy()->startOfDay();
            
            if ($startDate->gt($todayStart)) {
                $messages[] = 'Mã khuyến mại này chưa đến thời gian áp dụng. (Bắt đầu: ' . $startDate->format('d/m/Y') . ')';
            }
        }
        
        if ($promotion->end_date) {
            // end_date đã được cast thành Carbon trong model
            // So sánh với end of day để cho phép sử dụng trong cả ngày
            $endDate = $promotion->end_date->endOfDay();
            $todayEnd = $now->copy()->endOfDay();
            
            if ($endDate->lt($todayEnd)) {
                $messages[] = 'Mã khuyến mại này đã hết hạn. (Kết thúc: ' . $endDate->format('d/m/Y') . ')';
            }
        }
        
        if (!empty($messages)) {
            return [
                'valid' => false,
                'promotion' => $promotion,
                'discount_amount' => 0,
                'message' => implode(' ', $messages)
            ];
        }

        // Kiểm tra per_user_limit
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

        $discountAmount = 0;

        // Xử lý theo apply_scope
        if ($promotion->apply_scope === 'service') {
            // Áp dụng theo dịch vụ: chỉ giảm cho các dịch vụ/combo/variant có trong promotion
            // Nếu promotion không có dịch vụ/combo/variant nào được chọn, áp dụng cho tất cả
            $hasSpecificServices = $promotion->services->count() > 0 
                || $promotion->combos->count() > 0 
                || $promotion->serviceVariants->count() > 0;
            
            // Nếu promotion có dịch vụ/combo/variant được chọn, nhưng số lượng rất nhiều
            // (ví dụ: chọn tất cả), thì coi như áp dụng cho tất cả
            // Giả sử nếu có hơn 50 dịch vụ/combo/variant được chọn, coi như "tất cả"
            $totalSelected = $promotion->services->count() 
                + $promotion->combos->count() 
                + $promotion->serviceVariants->count();
            
            // Nếu số lượng được chọn quá nhiều (>= 20), coi như áp dụng cho tất cả
            // Hoặc nếu không có gì được chọn, cũng áp dụng cho tất cả
            $applyToAll = !$hasSpecificServices || $totalSelected >= 20;
            
            $applicableAmount = 0;

            foreach ($cartItems as $item) {
                $itemPrice = 0;
                $isApplicable = false;

                // Service Variant
                if (isset($item['type']) && $item['type'] === 'service_variant') {
                    $variant = \App\Models\ServiceVariant::with('service')->find($item['id']);
                    if ($variant) {
                        $itemPrice = $variant->price * ($item['quantity'] ?? 1);
                        
                        // Nếu áp dụng cho tất cả (không có dịch vụ cụ thể hoặc chọn quá nhiều)
                        if ($applyToAll) {
                            $isApplicable = true;
                        } else {
                            // Kiểm tra variant có trong promotion không
                            if ($promotion->serviceVariants->contains('id', $variant->id)) {
                                $isApplicable = true;
                            }
                            // Hoặc kiểm tra service cha có trong promotion không
                            elseif ($variant->service && $promotion->services->contains('id', $variant->service->id)) {
                                $isApplicable = true;
                            }
                        }
                    }
                }
                // Combo
                elseif (isset($item['type']) && $item['type'] === 'combo') {
                    $combo = \App\Models\Combo::find($item['id']);
                    if ($combo) {
                        $itemPrice = $combo->price * ($item['quantity'] ?? 1);
                        // Nếu áp dụng cho tất cả (không có dịch vụ cụ thể hoặc chọn quá nhiều)
                        if ($applyToAll) {
                            $isApplicable = true;
                        } elseif ($promotion->combos->contains('id', $combo->id)) {
                            $isApplicable = true;
                        }
                    }
                }
                // Appointment
                elseif (isset($item['type']) && $item['type'] === 'appointment') {
                    $appointment = \App\Models\Appointment::with([
                        'appointmentDetails.serviceVariant.service',
                        'appointmentDetails.combo'
                    ])->find($item['id']);

                    if ($appointment && $appointment->appointmentDetails->count() > 0) {
                        foreach ($appointment->appointmentDetails as $detail) {
                            // Tính giá từ price_snapshot hoặc từ variant/combo (giống như trong CheckoutController)
                            $detailPrice = $detail->price_snapshot;
                            
                            if ($detailPrice === null) {
                                if ($detail->serviceVariant) {
                                    $detailPrice = $detail->serviceVariant->price ?? 0;
                                } elseif ($detail->combo) {
                                    $detailPrice = $detail->combo->price ?? 0;
                                } else {
                                    // Nếu không có variant/combo, vẫn có thể có price_snapshot
                                    // Nếu không có gì cả, thì giá = 0
                                    $detailPrice = 0;
                                }
                            }
                            
                            // Bỏ qua nếu giá = 0 (không hợp lệ)
                            if ($detailPrice <= 0) {
                                continue;
                            }
                            
                        // Nếu áp dụng cho tất cả (không có dịch vụ cụ thể hoặc chọn quá nhiều)
                        if ($applyToAll) {
                            $isApplicable = true;
                            $itemPrice += $detailPrice;
                        } else {
                                $detailApplicable = false;
                                
                                // Kiểm tra service variant
                                if ($detail->service_variant_id) {
                                    if ($promotion->serviceVariants->contains('id', $detail->service_variant_id)) {
                                        $detailApplicable = true;
                                    }
                                    // Hoặc kiểm tra service cha
                                    elseif ($detail->serviceVariant && $detail->serviceVariant->service) {
                                        if ($promotion->services->contains('id', $detail->serviceVariant->service->id)) {
                                            $detailApplicable = true;
                                        }
                                    }
                                }
                                // Kiểm tra combo
                                elseif ($detail->combo_id) {
                                    if ($promotion->combos->contains('id', $detail->combo_id)) {
                                        $detailApplicable = true;
                                    }
                                }
                                // Nếu không có variant/combo nhưng có price_snapshot (service không có variant)
                                // Trong trường hợp này, nếu promotion có services được chọn, không thể biết service nào
                                // Nên chỉ áp dụng nếu promotion áp dụng cho tất cả (đã xử lý ở trên)
                                
                                if ($detailApplicable) {
                                    $isApplicable = true;
                                    $itemPrice += $detailPrice;
                                }
                            }
                        }
                    }
                }

                if ($isApplicable) {
                    $applicableAmount += $itemPrice;
                }
            }

            if ($applicableAmount <= 0) {
                return [
                    'valid' => false,
                    'promotion' => $promotion,
                    'discount_amount' => 0,
                    'message' => 'Mã khuyến mại này không áp dụng cho các dịch vụ trong giỏ hàng của bạn.'
                ];
            }

            // Tính giảm giá trên phần áp dụng được
            if ($promotion->discount_type === 'percent') {
                $discountAmount = $applicableAmount * ($promotion->discount_percent / 100);
            } else {
                $discountAmount = $promotion->discount_amount ?? 0;
            }

        } else {
            // Áp dụng theo hóa đơn: kiểm tra min_order_amount và giảm trên toàn bộ đơn
            if ($promotion->min_order_amount && $subtotal < $promotion->min_order_amount) {
                return [
                    'valid' => false,
                    'promotion' => $promotion,
                    'discount_amount' => 0,
                    'message' => 'Đơn hàng của bạn chưa đạt mức tối thiểu ' . number_format($promotion->min_order_amount, 0, ',', '.') . '₫ để áp dụng mã khuyến mại này.'
                ];
            }

            // Tính giảm giá trên toàn bộ đơn
            if ($promotion->discount_type === 'percent') {
                $discountAmount = $subtotal * ($promotion->discount_percent / 100);
            } else {
                $discountAmount = $promotion->discount_amount ?? 0;
            }
        }

        // Áp dụng max_discount_amount nếu có (cho discount_type = percent)
        if ($promotion->discount_type === 'percent' && $promotion->max_discount_amount) {
            $discountAmount = min($discountAmount, $promotion->max_discount_amount);
        }

        // Đảm bảo discount không vượt quá subtotal
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
        $now = Carbon::now()->startOfDay();
        $startDate = Carbon::parse($data['start_date'])->startOfDay();
        $endDate = isset($data['end_date']) ? Carbon::parse($data['end_date'])->endOfDay() : null;

        // Nếu admin đã chọn 'inactive', giữ nguyên
        if (isset($data['status']) && $data['status'] === 'inactive') {
            return;
        }

        if ($startDate->gt($now)) {
            $data['status'] = 'scheduled'; // Ngày bắt đầu trong tương lai
        } elseif ($endDate && $endDate->lt($now)) {
            $data['status'] = 'expired'; // Ngày kết thúc đã qua
        } else {
            $data['status'] = 'active'; // Đang trong thời gian áp dụng
        }
    }
}
