<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Employee;
use App\Models\Appointment;

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

        // Tìm barber yêu thích (employee được đặt lịch nhiều nhất, không tính lịch đã hủy)
        $favoriteBarber = null;
        $appointmentsByEmployee = Appointment::where('user_id', $user->id)
            ->where('status', '!=', 'Đã hủy')
            ->whereNotNull('employee_id')
            ->selectRaw('employee_id, COUNT(*) as appointment_count')
            ->groupBy('employee_id')
            ->orderByDesc('appointment_count')
            ->first();

        if ($appointmentsByEmployee) {
            $favoriteBarber = Employee::with('user')
                ->find($appointmentsByEmployee->employee_id);
        }

        return view('site.customers.show', compact('user', 'favoriteBarber'));
    }

    /**
     * API: Lấy trạng thái các lịch hẹn của user
     */
    public function getAppointmentsStatus($id)
    {
        // Kiểm tra quyền
        if (Auth::id() != $id && !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = User::with([
            'appointments' => function($query) {
                // Lấy tất cả lịch hẹn sắp tới (không chỉ "Chờ xử lý")
                // Loại bỏ các lịch đã hoàn thành, đã thanh toán, và đã hủy
                // Nhưng vẫn lấy các trạng thái khác như "Đã xác nhận", "Đang thực hiện", "Chưa thanh toán", "Đã thanh toán"
                $query->where('status', '!=', 'Đã hủy')
                    ->whereNotIn('status', ['Hoàn thành'])
                    ->orderBy('start_at', 'asc');
            }
        ])->findOrFail($id);

        $appointments = $user->appointments->map(function($appointment) {
            $canCancel = false;
            if ($appointment->status === 'Chờ xử lý' && $appointment->created_at) {
                $createdAt = \Carbon\Carbon::parse($appointment->created_at);
                $minutesSinceCreated = $createdAt->diffInMinutes(now());
                $canCancel = $minutesSinceCreated <= 5;
            }

            return [
                'id' => (string) $appointment->id, // Đảm bảo là string để so sánh với data-appointment-id
                'status' => $appointment->status ?? 'Chờ xử lý',
                'can_cancel' => $canCancel,
                'created_at' => $appointment->created_at ? $appointment->created_at->toDateTimeString() : null,
            ];
        });

        return response()->json([
            'success' => true,
            'appointments' => $appointments,
            'count' => $appointments->count()
        ])->header('Cache-Control', 'no-cache, no-store, must-revalidate')
          ->header('Pragma', 'no-cache')
          ->header('Expires', '0');
    }
}
