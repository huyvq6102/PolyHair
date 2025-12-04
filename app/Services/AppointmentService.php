<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentDetail;
use App\Models\AppointmentLog;
use App\Models\WorkingSchedule;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    /**
     * Get all appointments with relations (excluding cancelled).
     */
    public function getAll()
    {
        return Appointment::with(['employee.user', 'user', 'appointmentDetails.serviceVariant.service', 'appointmentDetails.combo'])
            ->where('status', '!=', 'Đã hủy')
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get all appointments with filters.
     */
    public function getAllWithFilters(array $filters = [])
    {
        $query = Appointment::with(['employee.user', 'user', 'appointmentDetails.serviceVariant.service', 'appointmentDetails.combo'])
            ->where('status', '!=', 'Đã hủy');

        // Search by customer name
        if (isset($filters['customer_name']) && !empty($filters['customer_name'])) {
            $query->whereHas('user', function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['customer_name'] . '%');
            });
        }

        // Search by phone
        if (isset($filters['phone']) && !empty($filters['phone'])) {
            $query->whereHas('user', function($q) use ($filters) {
                $q->where('phone', 'like', '%' . $filters['phone'] . '%');
            });
        }

        // Search by email
        if (isset($filters['email']) && !empty($filters['email'])) {
            $query->whereHas('user', function($q) use ($filters) {
                $q->where('email', 'like', '%' . $filters['email'] . '%');
            });
        }

        // Search by employee name
        if (isset($filters['employee_name']) && !empty($filters['employee_name'])) {
            $query->whereHas('employee.user', function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['employee_name'] . '%');
            });
        }

        // Search by service
        if (isset($filters['service']) && !empty($filters['service'])) {
            $query->whereHas('appointmentDetails.serviceVariant.service', function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['service'] . '%');
            });
        }

        return $query->orderBy('id', 'desc')->get();
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
        return Appointment::with([
                'employee.user',
                'user',
                'appointmentDetails.serviceVariant.service',
                'appointmentDetails.combo',
                'promotionUsages.promotion',
            ])
            ->findOrFail($id);
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

        // Log status change
        AppointmentLog::create([
            'appointment_id' => $appointment->id,
            'status_from' => $oldStatus,
            'status_to' => $status,
            'modified_by' => $modifiedBy ?? auth()->id(),
        ]);

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
        $query = Appointment::with(['employee.user', 'user', 'appointmentDetails.serviceVariant.service'])
            ->where(function($q) use ($employeeId) {
                // Appointments assigned to employee directly
                $q->where('employee_id', $employeeId)
                  // Or appointments where employee is assigned in appointment details
                  ->orWhereHas('appointmentDetails', function($detailQuery) use ($employeeId) {
                      $detailQuery->where('employee_id', $employeeId);
                  });
            });

        // Filter by status
        if (isset($filters['status']) && !empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Search by customer name
        if (isset($filters['customer_name']) && !empty($filters['customer_name'])) {
            $query->whereHas('user', function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['customer_name'] . '%');
            });
        }

        // Search by phone
        if (isset($filters['phone']) && !empty($filters['phone'])) {
            $query->whereHas('user', function($q) use ($filters) {
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

            return $appointment;
        });
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
            
            $appointment->update([
                'status' => $newStatus,
                'cancellation_reason' => null
            ]);

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

            return $appointment;
        });
    }

    /**
     * Update appointment with full data.
     */
    public function update($id, array $data, array $serviceVariantData = [])
    {
        return DB::transaction(function () use ($id, $data, $serviceVariantData) {
            $appointment = Appointment::findOrFail($id);
            $oldStatus = $appointment->status;
            
            $appointment->update([
                'user_id' => $data['user_id'] ?? $appointment->user_id,
                'employee_id' => $data['employee_id'] ?? $appointment->employee_id,
                'status' => $data['status'] ?? $appointment->status,
                'start_at' => $data['start_at'] ?? $appointment->start_at,
                'end_at' => $data['end_at'] ?? $appointment->end_at,
                'note' => $data['note'] ?? $appointment->note,
            ]);

            // Update service variants if provided
            if (!empty($serviceVariantData)) {
                // Delete existing appointment details
                $appointment->appointmentDetails()->delete();
                
                // Create new appointment details
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
            }

            // Log status change if status changed
            if (isset($data['status']) && $data['status'] !== $oldStatus) {
                AppointmentLog::create([
                    'appointment_id' => $appointment->id,
                    'status_from' => $oldStatus,
                    'status_to' => $data['status'],
                    'modified_by' => auth()->id(),
                ]);
            }

            return $appointment->load(['employee.user', 'user', 'appointmentDetails.serviceVariant.service']);
        });
    }
}

