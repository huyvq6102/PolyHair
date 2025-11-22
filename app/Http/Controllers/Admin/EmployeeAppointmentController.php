<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AppointmentService;
use App\Services\EmployeeService;
use Illuminate\Http\Request;

class EmployeeAppointmentController extends Controller
{
    protected $appointmentService;
    protected $employeeService;

    public function __construct(
        AppointmentService $appointmentService,
        EmployeeService $employeeService
    ) {
        $this->appointmentService = $appointmentService;
        $this->employeeService = $employeeService;
    }

    /**
     * Display a listing of appointments for the current employee.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $employee = $this->employeeService->getByUserId($user->id);

        if (!$employee) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Bạn không phải là nhân viên.');
        }

        // Get filter parameters
        $filters = [
            'status' => $request->get('status'),
            'customer_name' => $request->get('customer_name'),
            'phone' => $request->get('phone'),
            'date' => $request->get('date'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
        ];

        // Get appointments with filters and pagination
        $appointments = $this->appointmentService->getForEmployeeWithFilters(
            $employee->id,
            $filters,
            10
        );

        return view('admin.employee-appointments.index', compact('appointments', 'filters'));
    }

    /**
     * Display the specified appointment.
     */
    public function show(string $id)
    {
        $user = auth()->user();
        $employee = $this->employeeService->getByUserId($user->id);

        if (!$employee) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Bạn không phải là nhân viên.');
        }

        $appointment = $this->appointmentService->getOne($id);

        // Check if appointment belongs to this employee (directly or via appointment details)
        $hasAccess = $appointment->employee_id == $employee->id || 
                     $appointment->appointmentDetails->where('employee_id', $employee->id)->count() > 0;
        
        if (!$hasAccess) {
            return redirect()->route('employee.appointments.index')
                ->with('error', 'Bạn không có quyền xem đơn đặt này.');
        }

        return view('admin.employee-appointments.show', compact('appointment'));
    }

    /**
     * Confirm the appointment.
     */
    public function confirm(string $id)
    {
        $user = auth()->user();
        $employee = $this->employeeService->getByUserId($user->id);

        if (!$employee) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Bạn không phải là nhân viên.');
        }

        $appointment = $this->appointmentService->getOne($id);

        // Check if appointment belongs to this employee (directly or via appointment details)
        $hasAccess = $appointment->employee_id == $employee->id || 
                     $appointment->appointmentDetails->where('employee_id', $employee->id)->count() > 0;
        
        if (!$hasAccess) {
            return redirect()->route('employee.appointments.index')
                ->with('error', 'Bạn không có quyền thay đổi đơn đặt này.');
        }

        if ($appointment->status !== 'Chờ xử lý' && $appointment->status !== 'Chờ xác nhận') {
            return redirect()->route('employee.appointments.show', $id)
                ->with('error', 'Chỉ có thể xác nhận đơn ở trạng thái "Chờ xử lý" hoặc "Chờ xác nhận".');
        }

        $this->appointmentService->updateStatus($id, 'Đã xác nhận');

        return redirect()->route('employee.appointments.show', $id)
            ->with('success', 'Đơn đặt đã được xác nhận thành công!');
    }

    /**
     * Start the appointment.
     */
    public function start(string $id)
    {
        $user = auth()->user();
        $employee = $this->employeeService->getByUserId($user->id);

        if (!$employee) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Bạn không phải là nhân viên.');
        }

        $appointment = $this->appointmentService->getOne($id);

        // Check if appointment belongs to this employee (directly or via appointment details)
        $hasAccess = $appointment->employee_id == $employee->id || 
                     $appointment->appointmentDetails->where('employee_id', $employee->id)->count() > 0;
        
        if (!$hasAccess) {
            return redirect()->route('employee.appointments.index')
                ->with('error', 'Bạn không có quyền thay đổi đơn đặt này.');
        }

        if ($appointment->status !== 'Đã xác nhận') {
            return redirect()->route('employee.appointments.show', $id)
                ->with('error', 'Chỉ có thể bắt đầu đơn ở trạng thái "Đã xác nhận".');
        }

        $this->appointmentService->updateStatus($id, 'Đang thực hiện');

        return redirect()->route('employee.appointments.show', $id)
            ->with('success', 'Đơn đặt đã được bắt đầu thực hiện!');
    }

    /**
     * Complete the appointment.
     */
    public function complete(string $id)
    {
        $user = auth()->user();
        $employee = $this->employeeService->getByUserId($user->id);

        if (!$employee) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Bạn không phải là nhân viên.');
        }

        $appointment = $this->appointmentService->getOne($id);

        // Check if appointment belongs to this employee (directly or via appointment details)
        $hasAccess = $appointment->employee_id == $employee->id || 
                     $appointment->appointmentDetails->where('employee_id', $employee->id)->count() > 0;
        
        if (!$hasAccess) {
            return redirect()->route('employee.appointments.index')
                ->with('error', 'Bạn không có quyền thay đổi đơn đặt này.');
        }

        if ($appointment->status !== 'Đang thực hiện') {
            return redirect()->route('employee.appointments.show', $id)
                ->with('error', 'Chỉ có thể hoàn thành đơn ở trạng thái "Đang thực hiện".');
        }

        $this->appointmentService->updateStatus($id, 'Hoàn thành');

        return redirect()->route('employee.appointments.show', $id)
            ->with('success', 'Đơn đặt đã được hoàn thành!');
    }

    /**
     * Cancel the appointment.
     */
    public function cancel(Request $request, string $id)
    {
        $user = auth()->user();
        $employee = $this->employeeService->getByUserId($user->id);

        if (!$employee) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Bạn không phải là nhân viên.');
        }

        $appointment = $this->appointmentService->getOne($id);

        // Check if appointment belongs to this employee (directly or via appointment details)
        $hasAccess = $appointment->employee_id == $employee->id || 
                     $appointment->appointmentDetails->where('employee_id', $employee->id)->count() > 0;
        
        if (!$hasAccess) {
            return redirect()->route('employee.appointments.index')
                ->with('error', 'Bạn không có quyền thay đổi đơn đặt này.');
        }

        // Check if appointment can be cancelled (only Chờ xử lý or Chờ xác nhận)
        if ($appointment->status !== 'Chờ xử lý' && $appointment->status !== 'Chờ xác nhận') {
            return redirect()->route('employee.appointments.show', $id)
                ->with('error', 'Không thể hủy đơn đặt đã được xác nhận hoặc đang thực hiện.');
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $this->appointmentService->cancelAppointment($id, $validated['cancellation_reason']);

        return redirect()->route('employee.appointments.show', $id)
            ->with('success', 'Đơn đặt đã được hủy thành công!');
    }
}

