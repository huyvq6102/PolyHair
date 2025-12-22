<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentDetail;
use App\Models\AppointmentLog;
use App\Models\User;
use App\Models\WorkingSchedule;
use App\Mail\AppointmentCancellationMail;
use App\Events\AppointmentStatusUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AppointmentService
{
    /**
     * Get all appointments with relations (excluding cancelled).
     */
    public function getAll()
    {
        return Appointment::with(['employee.user', 'user', 'appointmentDetails.serviceVariant.service', 'appointmentDetails.combo'])
            ->whereNull('deleted_at')
            ->where('status', '!=', 'Đã hủy')
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get all appointments with filters (excluding cancelled).
     */
    public function getAllWithFilters(array $filters = [])
    {
        $query = Appointment::with(['employee.user', 'user', 'appointmentDetails.serviceVariant.service', 'appointmentDetails.combo'])
            ->whereNull('deleted_at')
            ->where('status', '!=', 'Đã hủy');

        // Search by customer name
        if (isset($filters['customer_name']) && !empty($filters['customer_name'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['customer_name'] . '%');
            });
        }

        // Search by phone
        if (isset($filters['phone']) && !empty($filters['phone'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('phone', 'like', '%' . $filters['phone'] . '%');
            });
        }

        // Search by email
        if (isset($filters['email']) && !empty($filters['email'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('email', 'like', '%' . $filters['email'] . '%');
            });
        }

        // Search by employee name
        if (isset($filters['employee_name']) && !empty($filters['employee_name'])) {
            $query->whereHas('employee.user', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['employee_name'] . '%');
            });
        }

        // Search by service
        if (isset($filters['service']) && !empty($filters['service'])) {
            $query->whereHas('appointmentDetails.serviceVariant.service', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['service'] . '%');
            });
        }

        // Filter by appointment date
        if (isset($filters['appointment_date']) && !empty($filters['appointment_date'])) {
            $query->whereDate('start_at', $filters['appointment_date']);
        }

        // Search by booking code
        if (isset($filters['booking_code']) && !empty($filters['booking_code'])) {
            $query->where('booking_code', 'like', '%' . $filters['booking_code'] . '%');
        }

        return $query->orderBy('id', 'desc')->get();
    }

    /**
     * Get all appointments with filters and pagination (excluding cancelled).
     */
    public function getAllWithFiltersPaginated(array $filters = [], $perPage = 10)
    {
        $query = Appointment::with(['employee.user', 'user', 'appointmentDetails.serviceVariant.service', 'appointmentDetails.combo'])
            ->whereNull('deleted_at');

        // Filter by status
        if (isset($filters['status']) && !empty($filters['status'])) {
            $query->where('status', $filters['status']);
        } else {
            // Default: exclude cancelled appointments
            $query->where('status', '!=', 'Đã hủy');
        }

        // Search by customer name
        if (isset($filters['customer_name']) && !empty($filters['customer_name'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['customer_name'] . '%');
            });
        }

        // Search by phone
        if (isset($filters['phone']) && !empty($filters['phone'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('phone', 'like', '%' . $filters['phone'] . '%');
            });
        }

        // Filter by date
        if (isset($filters['date']) && !empty($filters['date'])) {
            $query->whereDate('start_at', $filters['date']);
        }

        // Filter by date range
        if (isset($filters['date_from']) && !empty($filters['date_from'])) {
            $query->whereDate('start_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && !empty($filters['date_to'])) {
            $query->whereDate('start_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    /**
     * Get appointments by status.
     */
    public function getByStatus($status)
    {
        return Appointment::with(['employee.user', 'user', 'appointmentDetails.serviceVariant.service'])
            ->where('status', $status)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get one appointment by id.
     */
    public function getOne($id)
    {
        // Use withTrashed to find appointment even if soft deleted
        // This allows us to restore it if needed
        $appointment = Appointment::withTrashed()
            ->with([
                'employee.user',
                'user',
                'appointmentDetails.serviceVariant.service',
                'appointmentDetails.combo',
                'promotionUsages.promotion',
            ])
            ->findOrFail($id);

        // If appointment is soft deleted, restore it automatically
        if ($appointment->trashed()) {
            $appointment->restore();
        }

        return $appointment;
    }

    /**
     * Get latest appointment for user.
     */
    public function getLatestForUser($userId)
    {
        return Appointment::where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Create a new appointment with service variants.
     */
    public function create(array $data, array $serviceVariantData = [])
    {
        $appointment = Appointment::create([
            'user_id' => $data['user_id'],
            'guest_name' => $data['guest_name'] ?? null,
            'guest_phone' => $data['guest_phone'] ?? null,
            'guest_email' => $data['guest_email'] ?? null,
            'employee_id' => $data['employee_id'] ?? null,
            'status' => $data['status'] ?? 'Chờ xử lý',
            'start_at' => $data['start_at'] ?? null,
            'end_at' => $data['end_at'] ?? null,
            'note' => $data['note'] ?? null,
        ]);

        // Add service variants to appointment details
        foreach ($serviceVariantData as $variantData) {
            AppointmentDetail::create([
                'appointment_id' => $appointment->id,
                'service_variant_id' => $variantData['service_variant_id'] ?? null,
                'combo_id' => $variantData['combo_id'] ?? null, // Store combo_id if present
                'employee_id' => $variantData['employee_id'] ?? $data['employee_id'] ?? null,
                'price_snapshot' => $variantData['price_snapshot'] ?? null,
                'duration' => $variantData['duration'] ?? null,
                'status' => $variantData['status'] ?? 'Chờ',
                'notes' => $variantData['notes'] ?? null, // Store service/combo name when no variant
            ]);
        }

        // Log appointment creation
        AppointmentLog::create([
            'appointment_id' => $appointment->id,
            'status_from' => null,
            'status_to' => $appointment->status,
            'modified_by' => $data['user_id'],
        ]);

        return $appointment->load([
            'employee.user',
            'user',
            'appointmentDetails.serviceVariant.service',
            'promotionUsages.promotion',
        ]);
    }

    /**
     * Update appointment status.
     */
    public function updateStatus($id, $status, $modifiedBy = null)
    {
        $appointment = Appointment::findOrFail($id);
        $oldStatus = $appointment->status;

        $appointment->update(['status' => $status]);

        // Sync appointment details status with main appointment status
        // Map appointment status to appointment_details status enum values
        // appointment_details status: ['Chờ', 'Xác nhận', 'Hoàn thành', 'Hủy']
        $detailStatus = match ($status) {
            'Hoàn thành' => 'Hoàn thành',
            'Đang thực hiện' => 'Xác nhận', // Map 'Đang thực hiện' to 'Xác nhận' in details
            'Đã hủy' => 'Hủy',
            'Đã xác nhận' => 'Xác nhận',
            'Chờ xử lý' => 'Chờ',
            'Chờ xác nhận' => 'Chờ',
            default => null, // Don't update details for other statuses
        };

        if ($detailStatus !== null) {
            $appointment->appointmentDetails()->update(['status' => $detailStatus]);
        }

        // Log status change
        AppointmentLog::create([
            'appointment_id' => $appointment->id,
            'status_from' => $oldStatus,
            'status_to' => $status,
            'modified_by' => $modifiedBy ?? auth()->id(),
        ]);

        // Refresh appointment và load relationships trước khi broadcast
        $appointment->refresh();
        $appointment->load([
            'user',
            'employee.user',
            'appointmentDetails.serviceVariant.service',
            'appointmentDetails.combo'
        ]);

        // Broadcast status update event
        \Illuminate\Support\Facades\Log::info('Broadcasting appointment status update', [
            'appointment_id' => $appointment->id,
            'old_status' => $oldStatus,
            'new_status' => $appointment->status,
        ]);

        event(new AppointmentStatusUpdated($appointment));

        return $appointment;
    }

    /**
     * Update appointment cancel status (for backward compatibility).
     */
    public function updateCancelStatus($id, $cancel)
    {
        $statusMap = [
            0 => 'Chờ xử lý',
            1 => 'Đã xác nhận',
            2 => 'Đã hủy',
            3 => 'Hoàn thành',
        ];

        $status = $statusMap[$cancel] ?? 'Chờ xử lý';
        return $this->updateStatus($id, $status);
    }

    /**
     * Delete an appointment.
     */
    public function delete($id)
    {
        $appointment = Appointment::findOrFail($id);
        return $appointment->delete();
    }

    /**
     * Get appointments for user.
     */
    public function getForUser($userId)
    {
        return Appointment::with(['employee.user', 'user', 'appointmentDetails.serviceVariant.service'])
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get appointments for employee.
     */
    public function getForEmployee($employeeId)
    {
        return Appointment::with(['employee.user', 'user', 'appointmentDetails.serviceVariant.service'])
            ->where('employee_id', $employeeId)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get appointments for user by status.
     */
    public function getForUserByStatus($userId, $status)
    {
        return Appointment::with(['employee.user', 'user', 'appointmentDetails.serviceVariant.service'])
            ->where('user_id', $userId)
            ->where('status', $status)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get appointments for employee by status.
     */
    public function getForEmployeeByStatus($employeeId, $status)
    {
        return Appointment::with(['employee.user', 'user', 'appointmentDetails.serviceVariant.service'])
            ->where('employee_id', $employeeId)
            ->where('status', $status)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get appointments for employee with search, filter and pagination.
     */
    public function getForEmployeeWithFilters($employeeId, array $filters = [], $perPage = 10)
    {
        $query = Appointment::with(['employee.user', 'user', 'appointmentDetails.serviceVariant.service', 'appointmentDetails.combo'])
            ->where(function ($q) use ($employeeId) {
                // Appointments assigned to employee directly
                $q->where('employee_id', $employeeId)
                    // Or appointments where employee is assigned in appointment details
                    ->orWhereHas('appointmentDetails', function ($detailQuery) use ($employeeId) {
                        $detailQuery->where('employee_id', $employeeId);
                    });
            });

        // Filter by status
        if (isset($filters['status']) && !empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Search by customer name
        if (isset($filters['customer_name']) && !empty($filters['customer_name'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['customer_name'] . '%');
            });
        }

        // Search by phone
        if (isset($filters['phone']) && !empty($filters['phone'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('phone', 'like', '%' . $filters['phone'] . '%');
            });
        }

        // Filter by date
        if (isset($filters['date']) && !empty($filters['date'])) {
            $query->whereDate('start_at', $filters['date']);
        }

        // Filter by date range
        if (isset($filters['date_from']) && !empty($filters['date_from'])) {
            $query->whereDate('start_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && !empty($filters['date_to'])) {
            $query->whereDate('start_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    /**
     * Cancel appointment with reason and free up time slot.
     */
    public function cancelAppointment($id, $reason = null, $modifiedBy = null)
    {
        return DB::transaction(function () use ($id, $reason, $modifiedBy) {
            $appointment = Appointment::findOrFail($id);
            $oldStatus = $appointment->status;
            $user = $appointment->user;

            $appointment->update([
                'status' => 'Đã hủy',
                'cancellation_reason' => $reason
            ]);

            // Note: Working schedule status column has been removed.
            // The working schedule is now managed differently (if needed).
            // Free up working schedule time slot logic removed as status column no longer exists.

            // Log status change
            AppointmentLog::create([
                'appointment_id' => $appointment->id,
                'status_from' => $oldStatus,
                'status_to' => 'Đã hủy',
                'modified_by' => $modifiedBy ?? auth()->id(),
            ]);

            // Refresh appointment và load relationships trước khi broadcast
            $appointment->refresh();
            $appointment->load([
                'user',
                'employee.user',
                'appointmentDetails.serviceVariant.service',
                'appointmentDetails.combo'
            ]);

            // Broadcast status update event
            \Illuminate\Support\Facades\Log::info('Broadcasting appointment cancellation', [
                'appointment_id' => $appointment->id,
                'old_status' => $oldStatus,
                'new_status' => $appointment->status,
            ]);

            event(new AppointmentStatusUpdated($appointment));

            // Gửi email thông báo hủy lịch
            $this->sendCancellationEmail($appointment);

            // Chỉ kiểm tra và ban nếu là khách hàng tự hủy (không phải admin/employee)
            // Kiểm tra SAU KHI hủy để đếm chính xác số lần hủy bao gồm cả lịch vừa hủy
            $wasBanned = false;

            // Chỉ kiểm tra ban nếu user tồn tại (không phải guest)
            if ($user) {
                $shouldCheckBan = !$user->isAdmin() && !$user->isEmployee();

                if ($shouldCheckBan) {
                    $wasBanned = $this->checkAndBanUserIfNeeded($user);
                }

                // Refresh user để lấy thông tin mới nhất
                $user->refresh();
            }

            // Trả về thông tin về việc ban để controller có thể xử lý
            return [
                'appointment' => $appointment,
                'was_banned' => $wasBanned,
                'user' => $user,
            ];
        });
    }

    /**
     * Kiểm tra và ban tài khoản nếu hủy quá giới hạn.
     *
     * @return bool True nếu user bị ban, False nếu không
     */
    protected function checkAndBanUserIfNeeded($user)
    {
        $now = now();

        // Đếm số lần hủy trong ngày (từ 00:00 hôm nay)
        $todayStart = $now->copy()->startOfDay();
        $cancellationsToday = Appointment::where('user_id', $user->id)
            ->where('status', 'Đã hủy')
            ->where('created_at', '>=', $todayStart)
            ->count();

        // Đếm số lần hủy trong tuần (7 ngày gần nhất)
        $weekStart = $now->copy()->subDays(7);
        $cancellationsThisWeek = Appointment::where('user_id', $user->id)
            ->where('status', 'Đã hủy')
            ->where('created_at', '>=', $weekStart)
            ->count();

        // Đếm số lần hủy trong tháng (30 ngày gần nhất)
        $monthStart = $now->copy()->subDays(30);
        $cancellationsThisMonth = Appointment::where('user_id', $user->id)
            ->where('status', 'Đã hủy')
            ->where('created_at', '>=', $monthStart)
            ->count();

        // Log để debug
        \Log::info('Checking ban for user', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'cancellations_today' => $cancellationsToday,
            'cancellations_week' => $cancellationsThisWeek,
            'cancellations_month' => $cancellationsThisMonth,
            'is_banned' => $user->isBanned(),
            'banned_until' => $user->banned_until,
            'status' => $user->status,
        ]);

        // Kiểm tra giới hạn: 3/ngày, 7/tuần, 15/tháng
        $exceededLimit = false;
        $banReason = '';

        if ($cancellationsToday >= 3) {
            $exceededLimit = true;
            $banReason = "Hủy lịch hẹn quá 3 lần trong ngày ({$cancellationsToday} lần)";
        } elseif ($cancellationsThisWeek >= 7) {
            $exceededLimit = true;
            $banReason = "Hủy lịch hẹn quá 7 lần trong tuần ({$cancellationsThisWeek} lần)";
        } elseif ($cancellationsThisMonth >= 15) {
            $exceededLimit = true;
            $banReason = "Hủy lịch hẹn quá 15 lần trong tháng ({$cancellationsThisMonth} lần)";
        }

        if ($exceededLimit) {
            // Kiểm tra nếu user đã bị cấm vĩnh viễn (status = "Cấm") thì không ban lại
            if ($user->status === 'Cấm') {
                \Log::info('User is already permanently banned, skipping ban', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                ]);
                return;
            }

            // Nếu đã bị ban tạm thời nhưng chưa hết thời gian, không ban lại
            if ($user->isBanned()) {
                \Log::info('User is already banned, skipping ban', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'banned_until' => $user->banned_until,
                    'status' => $user->status,
                ]);
                return;
            }

            // Ban tài khoản: Tất cả các trường hợp vô hiệu hóa đều bị cấm 1 giờ
            $banHours = 1; // Vô hiệu hóa: khóa 1 giờ

            $user->ban($banHours, $banReason);

            // Refresh để lấy giá trị banned_until mới nhất
            $user->refresh();

            \Log::warning('User banned due to excessive cancellations', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'cancellations_today' => $cancellationsToday,
                'cancellations_week' => $cancellationsThisWeek,
                'cancellations_month' => $cancellationsThisMonth,
                'ban_reason' => $banReason,
                'ban_hours' => $banHours,
                'banned_until' => $user->banned_until,
                'status' => $user->status,
            ]);

            return true; // User đã bị ban
        }

        return false; // User không bị ban
    }

    /**
     * Send cancellation email to customer.
     */
    protected function sendCancellationEmail(Appointment $appointment)
    {
        // Lấy email từ user
        $emailToSend = trim($appointment->user->email ?? '');

        // Đảm bảo email hợp lệ
        if (empty($emailToSend) || !filter_var($emailToSend, FILTER_VALIDATE_EMAIL)) {
            \Log::warning('Cannot send appointment cancellation email: Invalid or missing email address', [
                'user_email' => $appointment->user->email ?? 'N/A',
                'appointment_id' => $appointment->id,
            ]);
            return;
        }

        try {
            // Gửi email thông báo hủy lịch
            Mail::to($emailToSend)->send(new AppointmentCancellationMail($appointment));

            \Log::info('Appointment cancellation email sent successfully', [
                'to' => $emailToSend,
                'appointment_id' => $appointment->id,
                'mailer' => config('mail.default'),
                'mail_host' => config('mail.mailers.smtp.host'),
                'mail_port' => config('mail.mailers.smtp.port'),
                'from_address' => config('mail.from.address'),
            ]);
        } catch (\Swift_TransportException $e) {
            // Lỗi kết nối SMTP
            \Log::error('SMTP connection error when sending appointment cancellation email', [
                'email_to' => $emailToSend,
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
                'mailer' => config('mail.default'),
                'mail_host' => config('mail.mailers.smtp.host'),
                'mail_port' => config('mail.mailers.smtp.port'),
            ]);
        } catch (\Exception $e) {
            // Log lỗi chi tiết nhưng không làm gián đoạn quá trình hủy lịch
            \Log::error('Failed to send appointment cancellation email', [
                'user_email' => $emailToSend,
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Get cancelled appointments.
     */
    public function getCancelled()
    {
        return Appointment::with(['employee.user', 'user', 'appointmentDetails.serviceVariant.service', 'appointmentDetails.combo'])
            ->where('status', 'Đã hủy')
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Permanently delete a cancelled appointment.
     */
    public function forceDelete($id)
    {
        return DB::transaction(function () use ($id) {
            $appointment = Appointment::findOrFail($id);

            if ($appointment->status !== 'Đã hủy') {
                throw new \Exception('Chỉ có thể xóa vĩnh viễn lịch đã hủy.');
            }

            // Delete related records first
            $appointment->appointmentDetails()->delete();
            $appointment->appointmentLogs()->delete();

            // Delete promotion usages if table exists
            if (\Illuminate\Support\Facades\Schema::hasTable('promotion_usages')) {
                try {
                    if (method_exists($appointment, 'promotionUsages')) {
                        $appointment->promotionUsages()->delete();
                    } else {
                        // Fallback: delete directly if relationship doesn't exist
                        \App\Models\PromotionUsage::where('appointment_id', $appointment->id)->delete();
                    }
                } catch (\Exception $e) {
                    \Log::warning('Could not delete promotion_usages for appointment ' . $id . ': ' . $e->getMessage());
                }
            }

            // Delete reviews if table exists
            if (\Illuminate\Support\Facades\Schema::hasTable('reviews')) {
                try {
                    if (method_exists($appointment, 'reviews')) {
                        $appointment->reviews()->delete();
                    } else {
                        // Fallback: delete directly if relationship doesn't exist
                        \App\Models\Review::where('appointment_id', $appointment->id)->delete();
                    }
                } catch (\Exception $e) {
                    \Log::warning('Could not delete reviews for appointment ' . $id . ': ' . $e->getMessage());
                }
            }

            // Delete payments if table exists
            if (\Illuminate\Support\Facades\Schema::hasTable('payments')) {
                try {
                    if (method_exists($appointment, 'payments')) {
                        $appointment->payments()->delete();
                    } else {
                        // Fallback: delete directly if relationship doesn't exist
                        \App\Models\Payment::where('appointment_id', $appointment->id)->delete();
                    }
                } catch (\Exception $e) {
                    \Log::warning('Could not delete payments for appointment ' . $id . ': ' . $e->getMessage());
                }
            }

            // Force delete the appointment
            return $appointment->forceDelete();
        });
    }

    /**
     * Auto delete cancelled appointments older than 7 days.
     */
    public function autoDeleteOldCancelled()
    {
        $deletedCount = 0;
        $sevenDaysAgo = now()->subDays(7);

        // Get all cancelled appointments
        $cancelledAppointments = Appointment::where('status', 'Đã hủy')->get();

        foreach ($cancelledAppointments as $appointment) {
            try {
                // Find when the appointment was cancelled (from logs)
                $cancelledLog = $appointment->appointmentLogs()
                    ->where('status_to', 'Đã hủy')
                    ->orderBy('created_at', 'desc')
                    ->first();

                // Use log date if exists, otherwise use updated_at
                $cancelledDate = $cancelledLog ? $cancelledLog->created_at : $appointment->updated_at;

                // Check if cancelled more than 7 days ago
                if ($cancelledDate->lte($sevenDaysAgo)) {
                    $this->forceDelete($appointment->id);
                    $deletedCount++;
                }
            } catch (\Exception $e) {
                \Log::error('Error auto deleting appointment ' . $appointment->id . ': ' . $e->getMessage());
            }
        }

        return $deletedCount;
    }

    /**
     * Restore cancelled appointment.
     */
    public function restore($id, $modifiedBy = null)
    {
        return DB::transaction(function () use ($id, $modifiedBy) {
            $appointment = Appointment::findOrFail($id);

            if ($appointment->status !== 'Đã hủy') {
                throw new \Exception('Chỉ có thể khôi phục lịch đã hủy.');
            }

            $oldStatus = $appointment->status;
            $newStatus = 'Chờ xử lý'; // Restore to pending status

            // Reset created_at và updated_at để tránh auto-confirm ngay lập tức
            $now = now();

            // Sử dụng DB::table để đảm bảo created_at được update (vì Eloquent có thể không update created_at)
            DB::table('appointments')
                ->where('id', $appointment->id)
                ->update([
                    'status' => $newStatus,
                    'cancellation_reason' => null,
                    'created_at' => $now, // Reset created_at để tránh auto-confirm ngay lập tức
                    'updated_at' => $now, // Cập nhật updated_at
                ]);

            // Refresh appointment để lấy dữ liệu mới
            $appointment->refresh();

            // Note: Working schedule status column has been removed.
            // The working schedule is now managed differently (if needed).
            // Mark working schedule as busy logic removed as status column no longer exists.

            // Log status change
            AppointmentLog::create([
                'appointment_id' => $appointment->id,
                'status_from' => $oldStatus,
                'status_to' => $newStatus,
                'modified_by' => $modifiedBy ?? auth()->id(),
            ]);

            // Refresh appointment và load relationships trước khi broadcast
            $appointment->refresh();
            $appointment->load([
                'user',
                'employee.user',
                'appointmentDetails.serviceVariant.service',
                'appointmentDetails.combo'
            ]);

            // Broadcast status update event
            \Illuminate\Support\Facades\Log::info('Broadcasting appointment restore', [
                'appointment_id' => $appointment->id,
                'old_status' => $oldStatus,
                'new_status' => $appointment->status,
                'booking_code' => $appointment->booking_code,
            ]);

            try {
                event(new AppointmentStatusUpdated($appointment));
                \Illuminate\Support\Facades\Log::info('Appointment restore event broadcasted successfully', [
                    'appointment_id' => $appointment->id,
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to broadcast appointment restore event', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return $appointment;
        });
    }

    /**
     * Update appointment with full data.
     */
    public function update($id, array $data, array $serviceVariantData = [])
    {
        \Illuminate\Support\Facades\Log::info('AppointmentService->update started', [
            'appointment_id' => $id,
            'data_keys' => array_keys($data),
            'service_variant_data_count' => count($serviceVariantData)
        ]);

        return DB::transaction(function () use ($id, $data, $serviceVariantData) {
            // Use withTrashed to find appointment even if soft deleted (shouldn't happen, but just in case)
            $appointment = Appointment::withTrashed()->findOrFail($id);

            \Illuminate\Support\Facades\Log::info('Appointment found', [
                'appointment_id' => $appointment->id,
                'deleted_at' => $appointment->deleted_at,
                'trashed' => $appointment->trashed()
            ]);

            // If appointment is soft deleted, restore it first
            if ($appointment->trashed()) {
                \Illuminate\Support\Facades\Log::warning('Appointment was trashed, restoring it', [
                    'appointment_id' => $appointment->id
                ]);
                $appointment->restore();
            }

            $oldStatus = $appointment->status;

            $appointment->update([
                'user_id' => $data['user_id'] ?? $appointment->user_id,
                'guest_name' => $data['guest_name'] ?? $appointment->guest_name,
                'guest_phone' => $data['guest_phone'] ?? $appointment->guest_phone,
                'guest_email' => $data['guest_email'] ?? $appointment->guest_email,
                'employee_id' => $data['employee_id'] ?? $appointment->employee_id,
                'status' => $data['status'] ?? $appointment->status,
                'start_at' => $data['start_at'] ?? $appointment->start_at,
                'end_at' => $data['end_at'] ?? $appointment->end_at,
                'note' => $data['note'] ?? $appointment->note,
            ]);

            // Ensure appointment is not soft deleted after update
            if ($appointment->trashed()) {
                \Illuminate\Support\Facades\Log::warning('Appointment was trashed after update, restoring it', [
                    'appointment_id' => $appointment->id
                ]);
                $appointment->restore();
            }

            // Add new service variants if provided (keep existing ones - don't delete)
            if (!empty($serviceVariantData)) {
                // Create new appointment details (keep existing ones)
                foreach ($serviceVariantData as $variantData) {
                    AppointmentDetail::create([
                        'appointment_id' => $appointment->id,
                        'service_variant_id' => $variantData['service_variant_id'] ?? null,
                        'combo_id' => $variantData['combo_id'] ?? null,
                        'employee_id' => $variantData['employee_id'] ?? $data['employee_id'] ?? null,
                        'price_snapshot' => $variantData['price_snapshot'] ?? null,
                        'duration' => $variantData['duration'] ?? null,
                        'status' => $variantData['status'] ?? 'Chờ',
                        'notes' => $variantData['notes'] ?? null,
                    ]);
                }

                \Illuminate\Support\Facades\Log::info('New services added to appointment', [
                    'appointment_id' => $appointment->id,
                    'new_services_count' => count($serviceVariantData)
                ]);
            }
            // Nếu serviceVariantData rỗng, không làm gì - giữ lại appointment details hiện có

            // Log status change if status changed
            if (isset($data['status']) && $data['status'] !== $oldStatus) {
                AppointmentLog::create([
                    'appointment_id' => $appointment->id,
                    'status_from' => $oldStatus,
                    'status_to' => $data['status'],
                    'modified_by' => auth()->id(),
                ]);

                // Refresh appointment và load relationships trước khi broadcast
                $appointment->refresh();
                $appointment->load([
                    'user',
                    'employee.user',
                    'appointmentDetails.serviceVariant.service',
                    'appointmentDetails.combo'
                ]);

                // Broadcast status update event
                \Illuminate\Support\Facades\Log::info('Broadcasting appointment status update', [
                    'appointment_id' => $appointment->id,
                    'old_status' => $oldStatus,
                    'new_status' => $appointment->status,
                ]);

                event(new AppointmentStatusUpdated($appointment));
            }

            $result = $appointment->load(['employee.user', 'user', 'appointmentDetails.serviceVariant.service']);

            \Illuminate\Support\Facades\Log::info('AppointmentService->update completed', [
                'appointment_id' => $result->id,
                'deleted_at' => $result->deleted_at,
                'trashed' => $result->trashed()
            ]);

            return $result;
        });
    }
}
