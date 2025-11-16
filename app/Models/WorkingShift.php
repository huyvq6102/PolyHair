<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkingShift extends Model
{
    protected $table = 'working_shifts';

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'duration',
    ];

    protected $casts = [
        'duration' => 'integer',
    ];

    /**
     * Get formatted start time (HH:MM)
     */
    public function getFormattedStartTimeAttribute(): string
    {
        if (!$this->start_time) {
            return '';
        }
        
        // Handle string format - could be "08:00:00" or "2025-11-16 08:00:00"
        if (is_string($this->start_time)) {
            // If it contains space, it's datetime format, extract time part
            if (strpos($this->start_time, ' ') !== false) {
                $parts = explode(' ', $this->start_time);
                return substr($parts[1] ?? $this->start_time, 0, 5);
            }
            // If it's just time format "HH:MM:SS", take first 5 chars
            return substr($this->start_time, 0, 5);
        }
        
        // If it's a Carbon instance or DateTime, format it
        return \Carbon\Carbon::parse($this->start_time)->format('H:i');
    }

    /**
     * Get formatted end time (HH:MM)
     */
    public function getFormattedEndTimeAttribute(): string
    {
        if (!$this->end_time) {
            return '';
        }
        
        // Handle string format - could be "20:00:00" or "2025-11-16 20:00:00"
        if (is_string($this->end_time)) {
            // If it contains space, it's datetime format, extract time part
            if (strpos($this->end_time, ' ') !== false) {
                $parts = explode(' ', $this->end_time);
                return substr($parts[1] ?? $this->end_time, 0, 5);
            }
            // If it's just time format "HH:MM:SS", take first 5 chars
            return substr($this->end_time, 0, 5);
        }
        
        // If it's a Carbon instance or DateTime, format it
        return \Carbon\Carbon::parse($this->end_time)->format('H:i');
    }

    /**
     * Get all working schedules for the shift.
     */
    public function workingSchedules(): HasMany
    {
        return $this->hasMany(WorkingSchedule::class, 'shift_id');
    }
}

