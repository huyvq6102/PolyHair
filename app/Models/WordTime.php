<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WordTime extends Model
{
    protected $table = 'word_time';

    protected $fillable = [
        'time',
    ];

    /**
     * Get formatted time attribute.
     */
    public function getFormattedTimeAttribute(): string
    {
        if (!$this->time) {
            return '00:00';
        }
        
        // If time is already a string in H:i format
        if (is_string($this->time)) {
            // Extract time if it contains date part
            if (preg_match('/(\d{2}):(\d{2})/', $this->time, $matches)) {
                return $matches[1] . ':' . $matches[2];
            }
            return $this->time;
        }
        
        // If time is a DateTime object
        if ($this->time instanceof \DateTimeInterface) {
            return $this->time->format('H:i');
        }
        
        // Try to parse as Carbon
        try {
            $time = \Carbon\Carbon::parse($this->time);
            return $time->format('H:i');
        } catch (\Exception $e) {
            return (string) $this->time;
        }
    }

    /**
     * Get all appointments for the time slot.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'id_time');
    }
}
