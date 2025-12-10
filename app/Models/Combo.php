<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Combo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'category_id',
        'owner_service_id',
        'price',
        'duration',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get the category that owns the combo.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    /**
     * Get all services in the combo.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'combo_items')
            ->withPivot(['service_variant_id', 'quantity', 'price_override', 'notes'])
            ->withTimestamps();
    }

    /**
     * Variants linked to this combo.
     */
    public function serviceVariants(): BelongsToMany
    {
        return $this->belongsToMany(ServiceVariant::class, 'combo_items')
            ->withPivot(['service_id', 'quantity', 'price_override', 'notes'])
            ->withTimestamps();
    }

    /**
     * Combo items detail.
     */
    public function comboItems(): HasMany
    {
        return $this->hasMany(ComboItem::class);
    }

    /**
     * Service owning/creating this combo entry (optional).
     */
    public function ownerService(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'owner_service_id');
    }
}
