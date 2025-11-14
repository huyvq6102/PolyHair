<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evaluate extends Model
{
    protected $fillable = [
        'content',
        'rating',
        'id_user',
        'id_appointment',
        'id_service',
        'parent_id',
    ];

    protected $casts = [
        'rating' => 'integer',
        'parent_id' => 'integer',
    ];

    /**
     * Get the user that authored the evaluate.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /**
     * Get the appointment related to the evaluate.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'id_appointment');
    }

    /**
     * Get the service that owns the evaluate.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'id_service');
    }

    /**
     * Get the parent evaluate.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get the child evaluates.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
