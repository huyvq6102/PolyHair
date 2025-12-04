<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CustomerController extends Controller
{
    /**
     * Hiển thị thông tin chi tiết của khách hàng đang đăng nhập.
     */
    public function show($id)
    {
        // Nếu muốn chỉ cho người dùng xem thông tin của chính họ:
        if (Auth::id() != $id && !Auth::user()->isAdmin()) {
            abort(403, 'Bạn không có quyền xem thông tin người dùng này.');
        }

        $user = User::with([
            'role',
            'employee',
            'appointments.appointmentDetails.serviceVariant.service',
            'appointments.appointmentDetails.combo',
            'appointments.employee.user',
            'appointments.reviews',
            'payments',
            'promotionUsages',
            'reviews',
        ])->findOrFail($id);

        return view('site.customers.show', compact('user'));
    }
}
