<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    // TẮT timestamps vì bảng không có created_at, updated_at
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'appointment_id',
        'price',
        'VAT',
        'total',
        'created_by',
        'payment_type',
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
}
