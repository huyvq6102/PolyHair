<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCategory extends Model
{
    use SoftDeletes;

    protected $table = 'service_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get all services for the category.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'category_id');
    }

    /**
     * Get all combos for the category.
     */
    public function combos(): HasMany
    {
        return $this->hasMany(Combo::class, 'category_id');
    }
}

