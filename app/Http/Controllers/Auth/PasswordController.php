<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = $request->user();
        $userEmail = $user->email;

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Logout user
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to login with email pre-filled
        return redirect()->route('login')
            ->with('success', 'Đổi mật khẩu thành công! Vui lòng đăng nhập lại bằng mật khẩu mới.')
            ->with('email', $userEmail);
    }
}
