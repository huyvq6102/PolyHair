<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppDetail extends Model
{
    protected $table = 'app_detail';

    protected $fillable = [
        'id_appointment',
        'id_service',
    ];

    /**
     * Get the appointment that owns the app detail.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'id_appointment');
    }

    /**
     * Get the service that owns the app detail.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'id_service');
    }
}
