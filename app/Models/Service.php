<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'service_code',
        'category_id',
        'name',
        'slug',
        'description',
        'image',
        'status',
        'base_price',
        'base_duration',
        'sort_order',
        'is_featured',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'base_duration' => 'integer',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the category that owns the service.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    /**
     * Get all service variants for the service.
     */
    public function serviceVariants(): HasMany
    {
        return $this->hasMany(ServiceVariant::class);
    }

    /**
     * Get all reviews for the service.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get all combos that include this service.
     */
    public function combos(): BelongsToMany
    {
        return $this->belongsToMany(Combo::class, 'combo_items')
            ->withPivot(['service_variant_id', 'quantity', 'price_override', 'notes'])
            ->withTimestamps();
    }

    /**
     * Get combos owned/managed directly by this service.
     */
    public function ownedCombos(): HasMany
    {
        return $this->hasMany(Combo::class, 'owner_service_id');
    }
}
