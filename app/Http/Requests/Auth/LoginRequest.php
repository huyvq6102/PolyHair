<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'login' => 'email hoặc số điện thoại',
            'password' => 'mật khẩu',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'login.required' => 'Vui lòng nhập email hoặc số điện thoại.',
            'login.string' => 'Email hoặc số điện thoại phải là chuỗi ký tự.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.string' => 'Mật khẩu phải là chuỗi ký tự.',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = $this->input('login');
        $password = $this->input('password');
        $remember = $this->boolean('remember');

        // Determine if login is email or phone
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        // Attempt authentication with email or phone
        if (! Auth::attempt([$field => $login, 'password' => $password], $remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => 'Thông tin đăng nhập không chính xác.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        // Kiểm tra tài khoản có bị khóa không
        $user = Auth::user();
        if ($user && $user->isBanned()) {
            Auth::logout();
            $bannedUntil = $user->banned_until;
            $timeRemaining = $this->formatBanTimeRemaining($bannedUntil);
            
            throw ValidationException::withMessages([
                'login' => 'Tài khoản của bạn đã bị khóa. ' . 
                          ($timeRemaining 
                              ? "Tài khoản sẽ được mở khóa sau {$timeRemaining}. " 
                              : 'Tài khoản sẽ được mở khóa sớm. ') .
                          ($user->ban_reason ? "Lý do: {$user->ban_reason}" : ''),
            ]);
        }
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => 'Bạn đã thử quá nhiều lần. Vui lòng thử lại sau ' . ceil($seconds / 60) . ' phút.',
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login')).'|'.$this->ip());
    }

    /**
     * Format thời gian còn lại của ban thành chuỗi dễ đọc.
     */
    protected function formatBanTimeRemaining($bannedUntil)
    {
        if (!$bannedUntil) {
            return null;
        }

        $now = now();
        if ($now->greaterThanOrEqualTo($bannedUntil)) {
            return null;
        }

        // Sử dụng diffInRealMinutes để có số chính xác hơn, sau đó làm tròn lên
        $diffInMinutes = (int) ceil($now->diffInRealMinutes($bannedUntil, false));
        
        if ($diffInMinutes < 60) {
            return $diffInMinutes . ' phút';
        }

        $hours = floor($diffInMinutes / 60);
        $minutes = $diffInMinutes % 60;

        if ($minutes == 0) {
            return $hours . ' giờ';
        }

        return $hours . ' giờ ' . $minutes . ' phút';
    }
}
