<?php

namespace App\Services;

use App\Models\Promotion;

class PromotionService
{
    /**
     * Get all promotions ordered by start date desc.
     */
    public function getAll()
    {
        return Promotion::with('services')
            ->orderByDesc('start_date')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get promotion by id.
     */
    public function getOne($id): Promotion
    {
        return Promotion::with(['services.category'])
            ->findOrFail($id);
    }

    /**
     * Create promotion.
     */
    public function create(array $data, ?array $serviceIds = null): Promotion
    {
        $promotion = Promotion::create($data);
        
        if ($serviceIds !== null) {
            $promotion->services()->sync($serviceIds);
        }
        
        return $promotion;
    }

    /**
     * Update promotion.
     */
    public function update($id, array $data, ?array $serviceIds = null): Promotion
    {
        $promotion = $this->getOne($id);
        $promotion->update($data);
        
        if ($serviceIds !== null) {
            $promotion->services()->sync($serviceIds);
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

