<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantAttribute extends Model
{
    protected $table = 'variant_attributes';

    protected $fillable = [
        'service_variant_id',
        'attribute_name',
        'attribute_value',
    ];

    /**
     * Get the service variant that owns the attribute.
     */
    public function serviceVariant(): BelongsTo
    {
        return $this->belongsTo(ServiceVariant::class);
    }
}

