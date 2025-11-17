<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentDetail extends Model
{
    protected $table = 'appointment_details';

    protected $fillable = [
        'appointment_id',
        'service_variant_id',
        'combo_id',
        'combo_item_id',
        'employee_id',
        'price_snapshot',
        'duration',
        'status',
        'notes',
    ];

    protected $casts = [
        'price_snapshot' => 'decimal:2',
        'duration' => 'integer',
    ];

    /**
     * Get the appointment that owns the detail.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Get the service variant that owns the detail.
     */
    public function serviceVariant(): BelongsTo
    {
        return $this->belongsTo(ServiceVariant::class);
    }

    /**
     * Get the employee that owns the detail.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Combo of the appointment detail (if any).
     */
    public function combo(): BelongsTo
    {
        return $this->belongsTo(Combo::class);
    }

    /**
     * Combo item reference.
     */
    public function comboItem(): BelongsTo
    {
        return $this->belongsTo(ComboItem::class);
    }
}

