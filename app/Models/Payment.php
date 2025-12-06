<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    
    protected $fillable = [
        'user_id',
        'invoice_code',
        'appointment_id',
        'order_id',
        'price',
        'VAT',
        'total',
        'created_by',
        'payment_type',
        'status',
    ];

    protected $casts = [
        'price' => 'double',
        'VAT' => 'double',
        'total' => 'double',
    ];

    /**
     * Get the user that owns the payment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the appointment that owns the payment.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Get the order that owns the payment.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

