<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PasswordResetOtp extends Model
{
    protected $fillable = [
        'email',
        'phone',
        'otp',
        'expires_at',
        'used',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    /**
     * Generate a 6-digit OTP
     */
    public static function generateOtp(): string
    {
        return str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new OTP record
     */
    public static function createOtp(?string $email = null, ?string $phone = null): self
    {
        // Invalidate any existing unused OTPs
        self::where(function ($query) use ($email, $phone) {
            if ($email) {
                $query->orWhere('email', $email);
            }
            if ($phone) {
                $query->orWhere('phone', $phone);
            }
        })
        ->where('used', false)
        ->where('expires_at', '>', now())
        ->update(['used' => true]);

        return self::create([
            'email' => $email,
            'phone' => $phone,
            'otp' => self::generateOtp(),
            'expires_at' => now()->addMinutes(10), // OTP expires in 10 minutes
            'used' => false,
        ]);
    }

    /**
     * Verify OTP
     */
    public static function verifyOtp(string $otp, ?string $email = null, ?string $phone = null): bool
    {
        $query = self::where('otp', $otp)
            ->where('used', false)
            ->where('expires_at', '>', now());

        if ($email) {
            $query->where('email', $email);
        }
        if ($phone) {
            $query->where('phone', $phone);
        }

        $otpRecord = $query->first();

        if ($otpRecord) {
            $otpRecord->update(['used' => true]);
            return true;
        }

        return false;
    }

    /**
     * Check if OTP is valid
     */
    public function isValid(): bool
    {
        return !$this->used && $this->expires_at->isFuture();
    }
}
