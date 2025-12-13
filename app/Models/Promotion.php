<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Promotion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_percent',
        'discount_amount',
        'apply_scope',
        'min_order_amount',
        'max_discount_amount',
        'per_user_limit',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'discount_percent' => 'integer',
        'discount_amount' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'per_user_limit' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get all promotion usages for the promotion.
     */
    public function promotionUsages(): HasMany
    {
        return $this->hasMany(PromotionUsage::class);
    }

    /**
     * Services that this promotion applies to.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'promotion_service', 'promotion_id', 'service_id')
            ->withTimestamps();
    }

    /**
     * Combos that this promotion applies to.
     */
    public function combos(): BelongsToMany
    {
        return $this->belongsToMany(Combo::class, 'promotion_service', 'promotion_id', 'combo_id')
            ->wherePivotNotNull('combo_id')
            ->withTimestamps();
    }

    /**
     * Service variants that this promotion applies to.
     */
    public function serviceVariants(): BelongsToMany
    {
        return $this->belongsToMany(ServiceVariant::class, 'promotion_service', 'promotion_id', 'service_variant_id')
            ->wherePivotNotNull('service_variant_id')
            ->withTimestamps();
    }
}

