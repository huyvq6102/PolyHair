<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentDetail;
use App\Models\AppointmentLog;

class AppointmentService
{
    /**
     * Get all appointments with relations.
     */
    public function getAll()
    {
        return Appointment::with(['employee.user', 'user', 'appointmentDetails.serviceVariant.service'])
            ->orderBy('id', 'desc')
            ->get();
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
        return Appointment::with(['employee.user', 'user', 'appointmentDetails.serviceVariant.service'])
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
                'service_variant_id' => $variantData['service_variant_id'],
                'employee_id' => $variantData['employee_id'] ?? $data['employee_id'] ?? null,
                'price_snapshot' => $variantData['price_snapshot'] ?? null,
                'duration' => $variantData['duration'] ?? null,
                'status' => $variantData['status'] ?? 'Chờ',
            ]);
        }

        // Log appointment creation
        AppointmentLog::create([
            'appointment_id' => $appointment->id,
            'status_from' => null,
            'status_to' => $appointment->status,
            'modified_by' => $data['user_id'],
        ]);

        return $appointment->load(['employee.user', 'user', 'appointmentDetails.serviceVariant.service']);
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
     * Cancel appointment with reason.
     */
    public function cancelAppointment($id, $reason, $modifiedBy = null)
    {
        $appointment = Appointment::findOrFail($id);
        $oldStatus = $appointment->status;
        
        $appointment->update([
            'status' => 'Đã hủy',
            'cancellation_reason' => $reason
        ]);

        // Log status change
        AppointmentLog::create([
            'appointment_id' => $appointment->id,
            'status_from' => $oldStatus,
            'status_to' => 'Đã hủy',
            'modified_by' => $modifiedBy ?? auth()->id(),
        ]);

        return $appointment;
    }
}

