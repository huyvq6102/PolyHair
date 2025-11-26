<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'avatar',
        'gender',
        'dob',
        'position',
        'level',
        'experience_years',
        'bio',
        'status',
    ];

    protected $casts = [
        'experience_years' => 'integer',
        'dob' => 'date',
    ];

    /**
     * Get the user that owns the employee.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Get all skills for the employee.
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'employee_skills');
    }

    /**
     * Get all appointments for the employee.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get all appointment details for the employee.
     */
    public function appointmentDetails(): HasMany
    {
        return $this->hasMany(AppointmentDetail::class);
    }

    /**
     * Get all working schedules for the employee.
     */
    public function workingSchedules(): HasMany
    {
        return $this->hasMany(WorkingSchedule::class);
    }

    /**
     * Get all reviews for the employee.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}

