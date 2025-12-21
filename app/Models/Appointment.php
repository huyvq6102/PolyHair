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
        'guest_name',
        'guest_phone',
        'guest_email',
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
     * Ghi nhận việc sử dụng khuyến mãi khi appointment được thanh toán.
     * Chỉ ghi nhận nếu appointment status là "Đã thanh toán" và có promotion được áp dụng.
     */
    public function recordPromotionUsage()
    {
        // Chỉ ghi nhận khi status là "Đã thanh toán"
        if ($this->status !== 'Đã thanh toán') {
            return false;
        }

        // Kiểm tra xem đã có PromotionUsage chưa
        $existingUsage = \App\Models\PromotionUsage::where('appointment_id', $this->id)->first();
        if ($existingUsage) {
            return true; // Đã có rồi, không cần tạo lại
        }

        // Lấy promotion từ nhiều nguồn
        $promotion = null;
        
        // 1. Kiểm tra từ PromotionUsage đã có (có thể đã được tạo trong PaymentService cho online payment)
        $existingUsage = \App\Models\PromotionUsage::where('appointment_id', $this->id)->first();
        if ($existingUsage) {
            return true; // Đã có rồi, không cần tạo lại
        }
        
        // 2. Kiểm tra từ session (nếu còn)
        $couponCode = \Illuminate\Support\Facades\Session::get('coupon_code');
        if ($couponCode) {
            $promotion = \App\Models\Promotion::where('code', $couponCode)->first();
        }
        
        // 3. Nếu không có từ session, lấy từ applied_promotion_id
        if (!$promotion) {
            $appliedPromotionId = \Illuminate\Support\Facades\Session::get('applied_promotion_id');
            if ($appliedPromotionId) {
                $promotion = \App\Models\Promotion::find($appliedPromotionId);
            }
        }
        
        // 4. Nếu vẫn không có, thử tìm promotion từ payment bằng cách so sánh discount amount
        // (Cách này không chính xác 100% nhưng là giải pháp cuối cùng)
        if (!$promotion && $this->user_id) {
            $payment = $this->payments()->where('status', 'completed')->first();
            if ($payment) {
                // Tính discount amount từ payment
                // Tạm thời không làm vì không có thông tin đầy đủ
                // Có thể lưu promotion_id vào payment trong tương lai
            }
        }

        // Nếu có promotion và có user_id, ghi nhận việc sử dụng
        if ($promotion && $this->user_id) {
            // Kiểm tra lại xem đã có PromotionUsage chưa (double check)
            $existingUsage = \App\Models\PromotionUsage::where('appointment_id', $this->id)
                ->where('promotion_id', $promotion->id)
                ->where('user_id', $this->user_id)
                ->first();

            if (!$existingUsage) {
                \App\Models\PromotionUsage::create([
                    'promotion_id'   => $promotion->id,
                    'user_id'        => $this->user_id,
                    'appointment_id' => $this->id,
                    'used_at'        => now(),
                ]);

                // Giảm số lượt dùng còn lại (usage_limit) nếu đang được giới hạn
                if (!is_null($promotion->usage_limit) && $promotion->usage_limit > 0) {
                    $promotion->decrement('usage_limit', 1);
                    $promotion->refresh();
                }

                return true;
            }
        }

        return false;
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
