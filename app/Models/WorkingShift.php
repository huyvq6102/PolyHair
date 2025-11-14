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

    /**
     * Get all working schedules for the shift.
     */
    public function workingSchedules(): HasMany
    {
        return $this->hasMany(WorkingSchedule::class, 'shift_id');
    }
}

