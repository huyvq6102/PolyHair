<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Appointment extends Model
{
    use SoftDeletes;

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($appointment) {
            if (empty($appointment->booking_code)) {
                $appointment->booking_code = static::generateBookingCode();
            }
        });
    }

    /**
     * Generate a unique booking code.
     */
    protected static function generateBookingCode(): string
    {
        $prefix = 'POLY-HB';
        
        // Get the last appointment with booking code matching our format
        $lastAppointment = static::whereNotNull('booking_code')
            ->where('booking_code', 'like', $prefix . '-%')
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastAppointment && $lastAppointment->booking_code) {
            // Extract the sequence number from the last booking code
            // Format: POLY-HB-001
            $parts = explode('-', $lastAppointment->booking_code);
            if (count($parts) === 3 && $parts[0] === 'POLY' && $parts[1] === 'HB') {
                $sequence = intval($parts[2]) + 1;
            } else {
                // If format doesn't match, get the highest sequence number
                $allCodes = static::whereNotNull('booking_code')
                    ->where('booking_code', 'like', $prefix . '-%')
                    ->pluck('booking_code')
                    ->map(function($code) {
                        $parts = explode('-', $code);
                        return count($parts) === 3 && $parts[0] === 'POLY' && $parts[1] === 'HB' 
                            ? intval($parts[2]) 
                            : 0;
                    })
                    ->filter()
                    ->max();
                
                $sequence = ($allCodes ?? 0) + 1;
            }
        } else {
            $sequence = 1;
        }
        
        // Format sequence with leading zeros (3 digits)
        $sequenceFormatted = str_pad($sequence, 3, '0', STR_PAD_LEFT);
        
        $bookingCode = "{$prefix}-{$sequenceFormatted}";
        
        // Ensure uniqueness (in case of race condition)
        $counter = 0;
        while (static::where('booking_code', $bookingCode)->exists() && $counter < 100) {
            $sequence++;
            $sequenceFormatted = str_pad($sequence, 3, '0', STR_PAD_LEFT);
            $bookingCode = "{$prefix}-{$sequenceFormatted}";
            $counter++;
        }
        
        return $bookingCode;
    }

    protected $fillable = [
        'user_id',
        'employee_id',
        'status',
        'start_at',
        'end_at',
        'note',
        'cancellation_reason',
        'booking_code',
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
