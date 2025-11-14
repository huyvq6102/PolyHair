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
}

