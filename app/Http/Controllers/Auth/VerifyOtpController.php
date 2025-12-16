<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetOtp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerifyOtpController extends Controller
{
    /**
     * Display the OTP verification view.
     */
    public function create(): View
    {
        if (!session('password_reset_login')) {
            return redirect()->route('password.request')
                ->withErrors(['login' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.']);
        }

        return view('auth.verify-otp');
    }

    /**
     * Handle OTP verification.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ], [
            'otp.required' => 'Vui lòng nhập mã xác nhận.',
            'otp.string' => 'Mã xác nhận phải là chuỗi ký tự.',
            'otp.size' => 'Mã xác nhận phải có đúng 6 ký tự.',
        ]);

        $login = session('password_reset_login');
        $email = session('password_reset_email');
        $phone = session('password_reset_phone');

        if (!$login) {
            return redirect()->route('password.request')
                ->withErrors(['login' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.']);
        }

        // Verify OTP
        $isValid = PasswordResetOtp::verifyOtp($request->otp, $email, $phone);

        if (!$isValid) {
            return back()->withErrors(['otp' => 'Mã xác nhận không hợp lệ hoặc đã hết hạn.']);
        }

        // Store verified status in session
        session(['password_reset_verified' => true]);

        return redirect()->route('password.reset');
    }
}
