<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Combo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'category_id',
        'price',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
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
        return $this->belongsToMany(Service::class, 'combo_items');
    }
}

