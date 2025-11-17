<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['required', 'string', 'max:20'],
            'gender' => ['nullable', 'in:Nam,Nữ,Khác'],
            'dob' => ['nullable', 'date'],
        ]);

        // Tìm role "Khách hàng"
        $customerRole = Role::where('name', 'Khách hàng')
            ->orWhere('name', 'khách hàng')
            ->orWhere('name', 'Khach hang')
            ->first();

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'gender' => $request->gender ?? null,
            'dob' => $request->dob ?? null,
            'status' => 'Hoạt động',
            'role_id' => $customerRole ? $customerRole->id : null,
        ];

        $user = User::create($userData);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('site.home')->with('success', 'Đăng ký thành công!');
    }
}
