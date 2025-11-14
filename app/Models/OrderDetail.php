<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetail extends Model
{
    protected $table = 'order_details';

    protected $fillable = [
        'id_order',
        'id_product',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Get the order that owns the order detail.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'id_order');
    }

    /**
     * Get the product that owns the order detail.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'id_product');
    }
}
