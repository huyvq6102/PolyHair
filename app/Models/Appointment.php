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
        
        // 4. Nếu vẫn không có, thử tìm promotion từ payment
        // Tính discount amount từ appointment details và payment để tìm promotion phù hợp
        if (!$promotion && $this->user_id) {
            $payment = $this->payments()->where('status', 'completed')->orderBy('id', 'desc')->first();
            if ($payment) {
                // Tính tổng giá gốc từ appointment details
                $totalOriginalPrice = 0;
                foreach ($this->appointmentDetails as $detail) {
                    $originalPrice = 0;
                    if ($detail->combo_id && $detail->combo) {
                        $originalPrice = $detail->combo->price ?? 0;
                    } elseif ($detail->service_variant_id && $detail->serviceVariant) {
                        $originalPrice = $detail->serviceVariant->price ?? 0;
                    } elseif ($detail->notes) {
                        $service = \App\Models\Service::where('name', $detail->notes)->first();
                        if ($service) {
                            $originalPrice = $service->base_price ?? 0;
                        }
                    }
                    $totalOriginalPrice += $originalPrice;
                }
                
                // Tính discount amount = tổng giá gốc - giá thanh toán (price trong payment)
                // Note: payment->price là giá sau discount, payment->total có thể bao gồm VAT
                $discountAmount = $totalOriginalPrice - ($payment->price ?? 0);
                
                // Tìm promotion có discount_amount hoặc discount_percent phù hợp
                if ($discountAmount > 0) {
                    // Tìm promotion order-level hoặc customer_tier có discount phù hợp
                    $promotion = \App\Models\Promotion::where(function($query) {
                        $query->where('apply_scope', 'order')
                              ->orWhere('apply_scope', 'customer_tier');
                    })
                    ->where(function($query) use ($discountAmount, $totalOriginalPrice) {
                        // Tìm promotion có discount_amount = discountAmount
                        $query->where(function($q) use ($discountAmount) {
                            $q->where('discount_type', 'fixed')
                              ->where('discount_amount', $discountAmount);
                        })
                        // Hoặc promotion có discount_percent tạo ra discountAmount
                        ->orWhere(function($q) use ($discountAmount, $totalOriginalPrice) {
                            if ($totalOriginalPrice > 0) {
                                $percent = ($discountAmount / $totalOriginalPrice) * 100;
                                $q->where('discount_type', 'percent')
                                  ->where('discount_percent', '>=', floor($percent - 0.1))
                                  ->where('discount_percent', '<=', ceil($percent + 0.1));
                            }
                        });
                    })
                    ->whereNull('deleted_at')
                    ->orderBy('id', 'desc')
                    ->first();
                }
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

    /**
     * Ghi nhận tất cả các khuyến mãi service-level đã được áp dụng cho các dịch vụ trong appointment.
     * Chỉ ghi nhận khi appointment status là "Đã thanh toán".
     */
    public function recordServiceLevelPromotionUsages()
    {
        // Chỉ ghi nhận khi status là "Đã thanh toán"
        if ($this->status !== 'Đã thanh toán') {
            return false;
        }

        if (!$this->user_id) {
            return false; // Không có user thì không ghi nhận
        }

        // Load appointment details với relations
        $this->load([
            'appointmentDetails.serviceVariant.service',
            'appointmentDetails.combo'
        ]);

        // Load tất cả active promotions với apply_scope = 'service'
        $now = \Carbon\Carbon::now();
        $activeServicePromotions = \App\Models\Promotion::with(['services', 'combos', 'serviceVariants'])
            ->where('apply_scope', 'service')
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->where(function($query) use ($now) {
                $query->whereNull('start_date')
                      ->orWhere('start_date', '<=', $now);
            })
            ->where(function($query) use ($now) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', $now);
            })
            ->get();

        $recordedPromotions = [];

        // Duyệt qua từng appointment detail
        foreach ($this->appointmentDetails as $detail) {
            $originalPrice = 0;
            $finalPrice = $detail->price_snapshot ?? 0;
            
            // Xác định giá gốc
            if ($detail->serviceVariant) {
                $originalPrice = $detail->serviceVariant->price ?? 0;
            } elseif ($detail->combo) {
                $originalPrice = $detail->combo->price ?? 0;
            } elseif ($detail->notes) {
                // Tìm service theo notes
                $service = \App\Models\Service::where('name', $detail->notes)->first();
                if ($service) {
                    $originalPrice = $service->base_price ?? 0;
                }
            }

            // Nếu có discount (finalPrice < originalPrice), tìm khuyến mãi đã áp dụng
            if ($finalPrice < $originalPrice && $originalPrice > 0) {
                $discountAmount = $originalPrice - $finalPrice;
                
                // Tìm khuyến mãi phù hợp với discount này
                $appliedPromotion = null;
                $maxDiscount = 0;

                foreach ($activeServicePromotions as $promotion) {
                    // Kiểm tra xem promotion có áp dụng cho dịch vụ này không
                    $applies = false;
                    
                    if ($detail->serviceVariant) {
                        $variantId = $detail->service_variant_id;
                        $serviceId = $detail->serviceVariant->service_id ?? null;
                        
                        // Check if promotion applies to this variant or service
                        $hasSpecificItems = ($promotion->services && $promotion->services->count() > 0)
                            || ($promotion->combos && $promotion->combos->count() > 0)
                            || ($promotion->serviceVariants && $promotion->serviceVariants->count() > 0);
                        $applyToAll = !$hasSpecificItems ||
                            (($promotion->services ? $promotion->services->count() : 0) +
                             ($promotion->combos ? $promotion->combos->count() : 0) +
                             ($promotion->serviceVariants ? $promotion->serviceVariants->count() : 0)) >= 20;
                        
                        if ($applyToAll) {
                            $applies = true;
                        } elseif ($variantId && $promotion->serviceVariants && $promotion->serviceVariants->contains('id', $variantId)) {
                            $applies = true;
                        } elseif ($serviceId && $promotion->services && $promotion->services->contains('id', $serviceId)) {
                            $applies = true;
                        }
                    } elseif ($detail->combo) {
                        $comboId = $detail->combo_id;
                        
                        $hasSpecificItems = ($promotion->services && $promotion->services->count() > 0)
                            || ($promotion->combos && $promotion->combos->count() > 0)
                            || ($promotion->serviceVariants && $promotion->serviceVariants->count() > 0);
                        $applyToAll = !$hasSpecificItems ||
                            (($promotion->services ? $promotion->services->count() : 0) +
                             ($promotion->combos ? $promotion->combos->count() : 0) +
                             ($promotion->serviceVariants ? $promotion->serviceVariants->count() : 0)) >= 20;
                        
                        if ($applyToAll) {
                            $applies = true;
                        } elseif ($comboId && $promotion->combos && $promotion->combos->contains('id', $comboId)) {
                            $applies = true;
                        }
                    } elseif ($detail->notes) {
                        $service = \App\Models\Service::where('name', $detail->notes)->first();
                        if ($service) {
                            $serviceId = $service->id;
                            
                            $hasSpecificItems = ($promotion->services && $promotion->services->count() > 0)
                                || ($promotion->combos && $promotion->combos->count() > 0)
                                || ($promotion->serviceVariants && $promotion->serviceVariants->count() > 0);
                            $applyToAll = !$hasSpecificItems ||
                                (($promotion->services ? $promotion->services->count() : 0) +
                                 ($promotion->combos ? $promotion->combos->count() : 0) +
                                 ($promotion->serviceVariants ? $promotion->serviceVariants->count() : 0)) >= 20;
                            
                            if ($applyToAll) {
                                $applies = true;
                            } elseif ($serviceId && $promotion->services && $promotion->services->contains('id', $serviceId)) {
                                $applies = true;
                            }
                        }
                    }

                    if ($applies) {
                        // Tính discount của promotion này
                        $promoDiscount = 0;
                        if ($promotion->discount_type === 'percent') {
                            $promoDiscount = $originalPrice * ($promotion->discount_percent / 100);
                            if ($promotion->max_discount_amount && $promoDiscount > $promotion->max_discount_amount) {
                                $promoDiscount = $promotion->max_discount_amount;
                            }
                        } else {
                            $promoDiscount = min($promotion->discount_amount, $originalPrice);
                        }

                        // So sánh với discount thực tế (có thể có sai số nhỏ do làm tròn)
                        $tolerance = 0.01; // Cho phép sai số 1 cent
                        if (abs($promoDiscount - $discountAmount) <= $tolerance || $promoDiscount >= $discountAmount) {
                            if ($promoDiscount > $maxDiscount) {
                                $maxDiscount = $promoDiscount;
                                $appliedPromotion = $promotion;
                            }
                        }
                    }
                }

                // Nếu tìm thấy promotion phù hợp, tạo PromotionUsage
                if ($appliedPromotion && !in_array($appliedPromotion->id, $recordedPromotions)) {
                    // Kiểm tra xem đã có PromotionUsage cho promotion này chưa
                    $existingUsage = \App\Models\PromotionUsage::where('appointment_id', $this->id)
                        ->where('promotion_id', $appliedPromotion->id)
                        ->where('user_id', $this->user_id)
                        ->first();

                    if (!$existingUsage) {
                        \App\Models\PromotionUsage::create([
                            'promotion_id'   => $appliedPromotion->id,
                            'user_id'        => $this->user_id,
                            'appointment_id' => $this->id,
                            'used_at'        => now(),
                        ]);

                        // Giảm số lượt dùng còn lại (usage_limit) nếu đang được giới hạn
                        if (!is_null($appliedPromotion->usage_limit) && $appliedPromotion->usage_limit > 0) {
                            $appliedPromotion->decrement('usage_limit', 1);
                            $appliedPromotion->refresh();
                        }

                        $recordedPromotions[] = $appliedPromotion->id;
                    }
                }
            }
        }

        return count($recordedPromotions) > 0;
    }
}
