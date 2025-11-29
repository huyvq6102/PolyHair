<?php

namespace App\Services;

use App\Models\Promotion;
use Illuminate\Support\Facades\DB;

class PromotionService
{
    /**
     * Get all promotions ordered by start date desc.
     */
    public function getAll()
    {
        return Promotion::with([
            'services.category',
            'combos.category',
            'serviceVariants.service.category'
        ])
            ->orderByDesc('start_date')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get promotion by id.
     */
    public function getOne($id): Promotion
    {
        return Promotion::with([
            'services.category',
            'combos.category',
            'serviceVariants.service.category'
        ])->findOrFail($id);
    }

    /**
     * Create promotion.
     */
    public function create(array $data, ?array $serviceIds = null, ?array $comboIds = null, ?array $variantIds = null): Promotion
    {
        $promotion = Promotion::create($data);
        
        // Xóa tất cả dữ liệu cũ
        DB::table('promotion_service')->where('promotion_id', $promotion->id)->delete();
        
        // Thêm services
        if (!empty($serviceIds)) {
            foreach ($serviceIds as $serviceId) {
                DB::table('promotion_service')->insert([
                    'promotion_id' => $promotion->id,
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
                    'promotion_id' => $promotion->id,
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
                    'promotion_id' => $promotion->id,
                    'service_id' => null,
                    'combo_id' => null,
                    'service_variant_id' => $variantId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        return $promotion;
    }

    /**
     * Update promotion.
     */
    public function update($id, array $data, ?array $serviceIds = null, ?array $comboIds = null, ?array $variantIds = null): Promotion
    {
        $promotion = $this->getOne($id);
        $promotion->update($data);
        
        // Xóa tất cả dữ liệu cũ
        DB::table('promotion_service')->where('promotion_id', $promotion->id)->delete();
        
        // Thêm services
        if (!empty($serviceIds)) {
            foreach ($serviceIds as $serviceId) {
                DB::table('promotion_service')->insert([
                    'promotion_id' => $promotion->id,
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
                    'promotion_id' => $promotion->id,
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
                    'promotion_id' => $promotion->id,
                    'service_id' => null,
                    'combo_id' => null,
                    'service_variant_id' => $variantId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        return $promotion;
    }

    /**
     * Soft delete promotion.
     */
    public function delete($id): bool
    {
        $promotion = $this->getOne($id);

        return (bool) $promotion->delete();
    }
}

