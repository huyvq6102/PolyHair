<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_percent',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'discount_percent' => 'integer',
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
}

