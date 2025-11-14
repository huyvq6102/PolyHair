<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'employee_id',
        'status',
        'start_at',
        'end_at',
        'note',
        'cancellation_reason',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    /**
     * Get the user that owns the appointment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the employee that owns the appointment.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get all appointment details for the appointment.
     */
    public function appointmentDetails(): HasMany
    {
        return $this->hasMany(AppointmentDetail::class);
    }

    /**
     * Get all appointment logs for the appointment.
     */
    public function appointmentLogs(): HasMany
    {
        return $this->hasMany(AppointmentLog::class);
    }

    /**
     * Get all promotion usages for the appointment.
     */
    public function promotionUsages(): HasMany
    {
        return $this->hasMany(PromotionUsage::class);
    }

    /**
     * Get all reviews for the appointment.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get all payments for the appointment.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
