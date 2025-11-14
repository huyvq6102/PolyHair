<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AppointmentService;
use App\Services\EmployeeService;
use App\Services\ServiceService;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    protected $appointmentService;
    protected $employeeService;
    protected $serviceService;

    public function __construct(
        AppointmentService $appointmentService,
        EmployeeService $employeeService,
        ServiceService $serviceService
    ) {
        $this->appointmentService = $appointmentService;
        $this->employeeService = $employeeService;
        $this->serviceService = $serviceService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $status = $request->get('status');
        $cancel = $request->get('cancel'); // For backward compatibility
        
        if ($status) {
            $appointments = $this->appointmentService->getByStatus($status);
        } elseif ($cancel !== null) {
            // Map old cancel values to new status
            $appointments = $this->appointmentService->getByCancelStatus($cancel);
        } else {
            $appointments = $this->appointmentService->getAll();
        }

        return view('admin.appointments.index', compact('appointments', 'status', 'cancel'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $appointment = $this->appointmentService->getOne($id);
        return view('admin.appointments.show', compact('appointment'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'status' => 'nullable|string|in:Chờ xử lý,Đã xác nhận,Đang thực hiện,Hoàn thành,Đã hủy,Chưa thanh toán,Đã thanh toán',
            'cancel' => 'nullable|integer|in:0,1,2,3', // For backward compatibility
        ]);

        if (isset($validated['status'])) {
            $this->appointmentService->updateStatus($id, $validated['status']);
        } elseif (isset($validated['cancel'])) {
            $this->appointmentService->updateCancelStatus($id, $validated['cancel']);
        }

        return redirect()->route('admin.appointments.index')
            ->with('success', 'Trạng thái lịch hẹn đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->appointmentService->delete($id);

        return redirect()->route('admin.appointments.index')
            ->with('success', 'Lịch hẹn đã được xóa thành công!');
    }
}
