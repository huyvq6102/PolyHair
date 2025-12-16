<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        if (!session('password_reset_verified')) {
            return redirect()->route('password.request')
                ->withErrors(['login' => 'Vui lòng xác nhận mã OTP trước.']);
        }

        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        if (!session('password_reset_verified')) {
            return redirect()->route('password.request')
                ->withErrors(['login' => 'Vui lòng xác nhận mã OTP trước.']);
        }

        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'password.min' => 'Mật khẩu phải có ít nhất :min ký tự.',
        ]);

        $login = session('password_reset_login');
        $email = session('password_reset_email');
        $phone = session('password_reset_phone');

        // Find user
        $user = null;
        if ($email) {
            $user = User::where('email', $email)->first();
        } elseif ($phone) {
            $user = User::where('phone', $phone)->first();
        }

        if (!$user) {
            return redirect()->route('password.request')
                ->withErrors(['login' => 'Không tìm thấy tài khoản.']);
        }

        // Reset password
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));

        // Clear session
        session()->forget(['password_reset_login', 'password_reset_email', 'password_reset_phone', 'password_reset_verified']);

        return redirect()->route('login')
            ->with('status', 'Mật khẩu đã được đặt lại thành công. Vui lòng đăng nhập.');
    }
}
