<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ServiceVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'service_id',
        'name',
        'price',
        'duration',
        'is_default',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the service that owns the variant.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get all variant attributes for the variant.
     */
    public function variantAttributes(): HasMany
    {
        return $this->hasMany(VariantAttribute::class);
    }

    /**
     * Get all appointment details for the variant.
     */
    public function appointmentDetails(): HasMany
    {
        return $this->hasMany(AppointmentDetail::class);
    }

    /**
     * Get all combos that reference this variant.
     */
    public function combos(): BelongsToMany
    {
        return $this->belongsToMany(Combo::class, 'combo_items')
            ->withPivot(['service_id', 'quantity', 'price_override', 'notes'])
            ->withTimestamps();
    }
}

