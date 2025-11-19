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
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration' => 'integer',
    ];

    protected function normalizeTime($value): ?string
    {
        if (!$value) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('H:i');
        }

        $value = (string) $value;
        if (strpos($value, ' ') !== false) {
            $parts = explode(' ', $value);
            $value = end($parts);
        }

        return substr($value, 0, 5);
    }

    public function getFormattedStartTimeAttribute(): ?string
    {
        return $this->normalizeTime($this->start_time);
    }

    public function getFormattedEndTimeAttribute(): ?string
    {
        return $this->normalizeTime($this->end_time);
    }

    public function getDisplayTimeAttribute(): string
    {
        $start = $this->formatted_start_time;
        $end = $this->formatted_end_time;

        $predefined = [
            '08:00' => '08h - 12h',
            '12:00' => '12h - 17h',
            '17:00' => '17h - 20h',
            '20:00' => '20h - 22h',
        ];

        if ($start && isset($predefined[$start])) {
            return $predefined[$start];
        }

        if ($start && $end) {
            return sprintf('%sh - %sh', substr($start, 0, 2), substr($end, 0, 2));
        }

        return 'Chưa xác định';
    }

    /**
     * Get all working schedules for the shift.
     */
    public function workingSchedules(): HasMany
    {
        return $this->hasMany(WorkingSchedule::class, 'shift_id');
    }
}

