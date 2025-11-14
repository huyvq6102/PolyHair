<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComboItem extends Model
{
    protected $table = 'combo_items';

    protected $fillable = [
        'combo_id',
        'service_id',
    ];

    /**
     * Get the combo that owns the item.
     */
    public function combo(): BelongsTo
    {
        return $this->belongsTo(Combo::class);
    }

    /**
     * Get the service that owns the item.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}

