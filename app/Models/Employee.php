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
     * Get all services (chuyên môn) for the employee.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'employee_skills', 'employee_id', 'service_id');
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

    /**
     * Get position name in Vietnamese.
     */
    public function getPositionVietnameseAttribute(): string
    {
        $positions = [
            'Stylist' => 'Thợ tạo kiểu',
            'Barber' => 'Thợ cắt tóc nam',
            'Shampooer' => 'Nhân viên gội đầu',
            'Receptionist' => 'Lễ tân',
        ];

        return $positions[$this->position] ?? $this->position;
    }

    /**
     * Static method to get position name in Vietnamese.
     */
    public static function getPositionVietnamese(string $position): string
    {
        $positions = [
            'Stylist' => 'Thợ tạo kiểu',
            'Barber' => 'Thợ cắt tóc nam',
            'Shampooer' => 'Nhân viên gội đầu',
            'Receptionist' => 'Lễ tân',
        ];

        return $positions[$position] ?? $position;
    }
}

