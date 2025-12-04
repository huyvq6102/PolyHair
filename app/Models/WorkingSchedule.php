<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkingSchedule extends Model
{
    use SoftDeletes;

    protected $table = 'working_schedule';

    protected $fillable = [
        'employee_id',
        'work_date',
        'shift_id',
        'is_handover',
    ];

    protected $casts = [
        'work_date' => 'date',
        'is_handover' => 'boolean',
    ];

    /**
     * Get the employee that owns the schedule.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the shift that owns the schedule.
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(WorkingShift::class, 'shift_id');
    }
}

