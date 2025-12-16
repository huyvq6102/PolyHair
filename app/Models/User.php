<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'gender',
        'dob',
        'status',
        'role_id',
        'banned_until',
        'ban_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'dob' => 'date',
            'banned_until' => 'datetime',
        ];
    }

    /**
     * Get the role that owns the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the employee profile for the user.
     */
    public function employee(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Get all appointments for the user.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get all appointment logs modified by the user.
     */
    public function appointmentLogs(): HasMany
    {
        return $this->hasMany(AppointmentLog::class, 'modified_by');
    }

    /**
     * Get all promotion usages for the user.
     */
    public function promotionUsages(): HasMany
    {
        return $this->hasMany(PromotionUsage::class);
    }

    /**
     * Get all reviews by the user.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get all notifications for the user.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get all payments for the user.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get total completed spending of the user (in VND).
     */
    public function getTotalSpentAttribute(): float
    {
        // Chỉ tính những thanh toán đã hoàn tất (status = completed)
        // và có tổng tiền hợp lệ
        return (float) $this->payments()
            ->where('status', 'completed')
            ->whereNotNull('total')
            ->sum('total');
    }

    /**
     * Get customer tier name based on total spending.
     *
     * Tiers:
     * - Khách thường: < 2,000,000
     * - Silver:      >= 2,000,000
     * - Gold:        >= 5,000,000
     * - VIP:         >= 10,000,000
     */
    public function getTierAttribute(): string
    {
        $total = $this->total_spent;

        if ($total >= 10_000_000) {
            return 'VIP';
        }

        if ($total >= 5_000_000) {
            return 'Gold';
        }

        if ($total >= 2_000_000) {
            return 'Silver';
        }

        return 'Khách thường';
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        if ($this->relationLoaded('role') || $this->role) {
            $roleName = strtolower($this->role->name ?? '');
            if ($roleName === 'admin' || $roleName === 'administrator') {
                return true;
            }
        }

        return $this->role_id === 1;
    }

    /**
     * Check if user is employee.
     */
    public function isEmployee(): bool
    {
        return $this->employee()->exists();
    }

    /**
     * Check if user is currently banned.
     */
    public function isBanned(): bool
    {
        // Nếu status = "Cấm" → bị cấm vĩnh viễn
        if ($this->status === 'Cấm') {
            return true;
        }

        // Nếu status = "Hoạt động" → không bị cấm (ưu tiên status)
        // Tự động xóa banned_until nếu có để đồng bộ dữ liệu
        if ($this->status === 'Hoạt động') {
            if ($this->banned_until || $this->ban_reason) {
                $this->update([
                    'banned_until' => null,
                    'ban_reason' => null,
                ]);
            }
            return false;
        }

        // Nếu status = "Vô hiệu hóa" và có banned_until
        if ($this->status === 'Vô hiệu hóa' && $this->banned_until) {
            // Nếu thời gian khóa đã hết, tự động mở khóa
            if (now()->greaterThan($this->banned_until)) {
                $this->update([
                    'banned_until' => null,
                    'ban_reason' => null,
                    'status' => 'Hoạt động', // Khôi phục trạng thái
                ]);
                return false;
            }
            return true;
        }

        // Nếu có banned_until nhưng status không phải "Vô hiệu hóa" hoặc "Hoạt động"
        // (trường hợp dữ liệu không đồng bộ)
        if ($this->banned_until && $this->status !== 'Vô hiệu hóa' && $this->status !== 'Hoạt động') {
            // Tự động cập nhật status
            $this->update([
                'status' => 'Vô hiệu hóa',
            ]);
            // Kiểm tra lại thời gian
            if (now()->greaterThan($this->banned_until)) {
                $this->update([
                    'banned_until' => null,
                    'ban_reason' => null,
                    'status' => 'Hoạt động',
                ]);
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * Ban user for specified hours (Vô hiệu hóa).
     */
    public function ban(int $hours = 1, string $reason = null): void
    {
        $this->update([
            'banned_until' => now()->addHours($hours),
            'ban_reason' => $reason ?? 'Hủy lịch hẹn quá nhiều lần',
            'status' => 'Vô hiệu hóa', // Cập nhật status
        ]);
    }

    /**
     * Unban user.
     */
    public function unban(): void
    {
        $this->update([
            'banned_until' => null,
            'ban_reason' => null,
            'status' => 'Hoạt động', // Khôi phục trạng thái
        ]);
    }

    /**
     * Permanently ban user (Cấm).
     */
    public function permanentlyBan(string $reason = null): void
    {
        $this->update([
            'banned_until' => null, // Clear temporary ban if exists
            'ban_reason' => $reason ?? 'Bị cấm vĩnh viễn',
            'status' => 'Cấm', // Cập nhật status
        ]);
    }
}
