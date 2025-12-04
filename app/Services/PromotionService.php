<?php

namespace App\Services;

use App\Models\Promotion;
use Illuminate\Support\Facades\DB;

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
            $promotion = Promotion::findOrFail($id);
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
}
