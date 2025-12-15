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
            // Họ tên chỉ chữ cái, số và khoảng trắng
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\p{L}\p{N}\s]+$/u',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:'.User::class,
            ],
            // Điện thoại: phải có đúng 10 số và bắt đầu bằng số 0
            'phone' => [
                'required',
                'regex:/^0[0-9]{9}$/',
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'gender' => ['nullable', 'in:Nam,Nữ,Khác'],
            'dob' => ['nullable', 'date'],
        ], [
            // Name validation messages
            'name.required' => 'Vui lòng nhập họ và tên.',
            'name.string' => 'Họ và tên phải là chuỗi ký tự.',
            'name.max' => 'Họ và tên không được vượt quá 255 ký tự.',
            'name.regex' => 'Họ và tên chỉ được chứa chữ cái, số và khoảng trắng.',
            
            // Email validation messages
            'email.required' => 'Vui lòng nhập email.',
            'email.string' => 'Email phải là chuỗi ký tự.',
            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá 255 ký tự.',
            'email.unique' => 'Email này đã được sử dụng. Vui lòng chọn email khác.',
            
            // Phone validation messages
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại phải có đúng 10 số và bắt đầu bằng số 0.',
            
            // Password validation messages
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'password.min' => 'Mật khẩu phải có ít nhất :min ký tự.',
            
            // Gender validation messages
            'gender.in' => 'Giới tính không hợp lệ.',
            
            // Date of birth validation messages
            'dob.date' => 'Ngày sinh không đúng định dạng.',
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
