<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Employee;
use App\Models\Appointment;
use App\Models\AppointmentLog;
use App\Events\AppointmentStatusUpdated;

class CustomerController extends Controller
{
    /**
     * Hiển thị thông tin chi tiết của khách hàng đang đăng nhập !.
     */
    public function show($id)
    {
        // Nếu muốn chỉ cho người dùng xem thông tin của chính họ:
        $currentUser = Auth::user();
        if (!$currentUser) {
            return redirect()->route('login');
        }
        
        if (Auth::id() != $id && !$currentUser->isAdmin()) {
            abort(403, 'Bạn không có quyền xem thông tin người dùng này.');
        }

        // Tự động cập nhật trạng thái lịch hẹn từ "Chờ xử lý" sang "Đã xác nhận" nếu đã quá 10 giây
        $this->autoConfirmPendingAppointments($id);

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
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        if (Auth::id() != $id && !$currentUser->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Tự động cập nhật trạng thái TRƯỚC KHI load appointments
        $this->autoConfirmPendingAppointments($id);

        $user = User::with([
            'appointments' => function($query) {
                // Lấy tất cả lịch hẹn (bao gồm cả "Hoàn thành" và "Đã hủy" để polling có thể cập nhật)
                // Chỉ loại bỏ các lịch đã quá cũ (hơn 30 ngày)
                $query->where('created_at', '>=', now()->subDays(30))
                    ->orderBy('start_at', 'asc');
            }
        ])->findOrFail($id);
$appointments = $user->appointments->map(function($appointment) {
            $canCancel = false;
            if ($appointment->status === 'Chờ xử lý' && $appointment->created_at) {
                $createdAt = \Carbon\Carbon::parse($appointment->created_at);
                $minutesSinceCreated = $createdAt->diffInMinutes(now());
                $canCancel = $minutesSinceCreated <= 30;
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

    /**
     * Tự động chuyển lịch hẹn từ "Chờ xử lý" sang "Đã xác nhận" sau 10 giây
     */
    private function autoConfirmPendingAppointments($userId)
    {
        try {
            $cutoffTime = \Carbon\Carbon::now()->subSeconds(10);

            $appointments = Appointment::where('user_id', $userId)
                ->where('status', 'Chờ xử lý')
                ->where('created_at', '<=', $cutoffTime)
                ->whereRaw('TIMESTAMPDIFF(SECOND, created_at, NOW()) >= 10') // Đảm bảo đã qua ít nhất 10 giây
                ->get();

            foreach ($appointments as $appointment) {
                try {
                    DB::beginTransaction();

                    $oldStatus = $appointment->status;

                    // Chuyển status sang "Đã xác nhậnn"
                    $appointment->update([
                        'status' => 'Đã xác nhận'
                    ]);

                    // Log status change
                    AppointmentLog::create([
                        'appointment_id' => $appointment->id,
                        'status_from' => $oldStatus,
                        'status_to' => 'Đã xác nhận',
                        'modified_by' => null, // Tự động xác nhận
                    ]);

                    // Refresh và load relationships trước khi broadcast
                    $appointment->refresh();
                    $appointment->load([
                        'user',
                        'employee.user',
                        'appointmentDetails.serviceVariant.service',
                        'appointmentDetails.combo'
                    ]);

                    // Broadcast status update event
                    event(new AppointmentStatusUpdated($appointment));

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('Error auto-confirming appointment: ' . $e->getMessage(), [
                        'appointment_id' => $appointment->id,
                        'user_id' => $userId
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error in autoConfirmPendingAppointments: ' . $e->getMessage(), [
                'user_id' => $userId
            ]);
        }
    }
}
