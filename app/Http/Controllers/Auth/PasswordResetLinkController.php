<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset OTP request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => ['required', 'string'],
        ]);

        $login = $request->input('login');
        $user = null;
        $email = null;
        $phone = null;

        // Check if login is email or phone
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $email = $login;
            $user = User::where('email', $email)->first();
        } else {
            $phone = $login;
            $user = User::where('phone', $phone)->first();
        }

        if (!$user) {
            return back()->withInput($request->only('login'))
                ->withErrors(['login' => 'Không tìm thấy tài khoản với email/số điện thoại này.']);
        }

        // Generate OTP
        $otpRecord = PasswordResetOtp::createOtp($user->email, $user->phone);
        
        // Send OTP via email
        if ($user->email) {
            try {
                Mail::send('emails.password-reset-otp', ['otp' => $otpRecord->otp], function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Mã xác nhận đặt lại mật khẩu - PolyHair');
                });
                \Log::info('OTP email sent successfully', ['email' => $user->email]);
            } catch (\Exception $e) {
                \Log::error('Failed to send OTP email', [
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return back()->withInput($request->only('login'))
                    ->withErrors(['login' => 'Không thể gửi email. Vui lòng thử lại sau.']);
            }
        }

        // Store login info in session for verification step
        session([
            'password_reset_login' => $login,
            'password_reset_email' => $user->email,
            'password_reset_phone' => $user->phone,
        ]);

        return redirect()->route('password.verify-otp')
            ->with('status', 'Mã xác nhận đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư.');
    }
}
