<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price',
        'sale',
        'images',
        'id_category',
        'status',
        'description',
        'views',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale' => 'decimal:2',
        'status' => 'integer',
        'views' => 'integer',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'id_category');
    }

    /**
     * Get all comments for the product.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'id_product');
    }

    /**
     * Get all order details for the product.
     */
    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'id_product');
    }
}
