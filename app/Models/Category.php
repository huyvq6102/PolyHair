<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'images',
    ];

    /**
     * Get all products for the category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'id_category');
    }
}
