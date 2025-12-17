<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeAppointmentRequest;
use App\Http\Requests\CancelAppointmentRequest;
use App\Services\AppointmentService;
use App\Services\EmployeeService;
use App\Services\ServiceService;
use App\Services\ServiceCategoryService;
use App\Services\WordTimeService;
use App\Models\Promotion;
use App\Models\PromotionUsage;
use App\Models\Service;
use App\Models\User;
use App\Models\ServiceVariant;
use App\Models\WorkingSchedule;
use App\Models\WorkingShift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmployeeAppointmentController extends Controller
{
    protected $appointmentService;
    protected $employeeService;

    protected $serviceService;
    protected $serviceCategoryService;
    protected $wordTimeService;

    public function __construct(
        AppointmentService $appointmentService,
        EmployeeService $employeeService,
        ServiceService $serviceService,
        ServiceCategoryService $serviceCategoryService,
        WordTimeService $wordTimeService
    ) {
        $this->appointmentService = $appointmentService;
        $this->employeeService = $employeeService;
        $this->serviceService = $serviceService;
        $this->serviceCategoryService = $serviceCategoryService;
        $this->wordTimeService = $wordTimeService;
    }

    /**
     * Show the form for creating a new appointment.
     */
    public function create()
    {
        $user = auth()->user();
        $employee = $this->employeeService->getByUserId($user->id);

        if (!$employee) {
            return redirect()->route('employee.appointments.index')
                ->with('error', 'Bạn không phải là nhân viên.');
        }

        // Get all customers (users with customer role)
        $customers = User::where('role_id', 3) // Khách hàng role
            ->orderBy('name')
            ->get();

        // Get all service categories and services with variants
        $categories = $this->serviceCategoryService->getAll();
        $services = $this->serviceService->getAll()->load('serviceVariants');

        // Active promotions that can be applied by employee (chỉ lấy status = 'active')
        // Command sẽ tự động cập nhật trạng thái dựa trên ngày
        $promotions = Promotion::where('status', 'active')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        // Get Stylists and Barbers for selection
        $staffMembers = \App\Models\Employee::whereHas('user')
            ->whereIn('position', ['Stylist', 'Barber'])
            ->with('user')
            ->get()
            ->sortBy('user.name');

        return view('admin.employee-appointments.create', compact('customers', 'categories', 'services', 'employee', 'promotions', 'staffMembers'));
    }

    /**
     * Store a newly created appointment.
     */
    public function store(StoreEmployeeAppointmentRequest $request)
    {
        $user = auth()->user();
        $employee = $this->employeeService->getByUserId($user->id);

        if (!$employee) {
            return redirect()->route('employee.appointments.index')
                ->with('error', 'Bạn không phải là nhân viên.');
        }

        // Get validated data (validation is handled by Form Request)
        $validated = $request->validated();
        $customerType = $request->input('customer_type', 'existing');

        if ($customerType === 'existing') {
            $userId = $validated['user_id'];
        } else {
            // Create new customer (phone and email can be duplicated)
            $newUser = User::create([
                'name' => $validated['new_customer_name'],
                'phone' => $validated['new_customer_phone'],
                'email' => $validated['new_customer_email'] ?? null,
                'password' => bcrypt(Str::random(32)), // Random password
                'status' => 'Hoạt động',
                'role_id' => 3, // Khách hàng role
            ]);
            $userId = $newUser->id;
        }

        $request->validate([
            'staff_id' => 'required|exists:employees,id',
        ]);

        try {
            // Validate time format manually
            $timeString = $validated['appointment_time'];
            if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $timeString)) {
                return back()->withInput()
                    ->withErrors(['appointment_time' => 'Định dạng giờ không hợp lệ. Vui lòng nhập theo định dạng HH:mm (ví dụ: 09:00, 14:30).']);
            }

            // Parse appointment date and time
            $appointmentDate = Carbon::parse($validated['appointment_date']);
            $startAt = Carbon::parse($appointmentDate->format('Y-m-d') . ' ' . $timeString);

            // Calculate total duration from selected service variants / base services
            $totalDuration = 0;
            $serviceVariantData = [];

            // Biến thể dịch vụ (nếu có)
            $variantIds = $validated['service_variants'] ?? [];
            foreach ($variantIds as $variantId) {
                $variant = ServiceVariant::findOrFail($variantId);
                $totalDuration += $variant->duration ?? 60; // Default 60 minutes if not set

                $serviceVariantData[] = [
                    'service_variant_id' => $variantId,
                    'employee_id' => $validated['staff_id'] ?? $employee->id,
                    'price_snapshot' => $variant->price,
                    'duration' => $variant->duration ?? 60,
                    'status' => 'Chờ',
                ];
            }

            // Dịch vụ không có biến thể: dùng base_price / base_duration
            $simpleServiceIds = $validated['simple_services'] ?? [];
            foreach ($simpleServiceIds as $serviceId) {
                $service = Service::findOrFail($serviceId);
                $duration = $service->base_duration ?? 60;
                $price = $service->base_price ?? 0;

                $totalDuration += $duration;

                $serviceVariantData[] = [
                    'service_variant_id' => null,
                    'employee_id' => $validated['staff_id'] ?? $employee->id,
                    'price_snapshot' => $price,
                    'duration' => $duration,
                    'status' => 'Chờ',
                    'notes' => $service->name,
                ];
            }

            $endAt = $startAt->copy()->addMinutes($totalDuration);

            // Optional: check time conflicts with existing appointments
            // Hiện tại tạm tắt check để tránh chặn đặt lịch khi không mong muốn.
            // Nếu sau này cần bật lại, đặt $enableConflictCheck = true.
            $enableConflictCheck = false;

            if ($enableConflictCheck) {
                // Rule: Appointments must be at least 1 hour apart
                $bufferMinutes = 60; // 1 hour buffer between appointments

                $existingAppointments = \App\Models\Appointment::where('employee_id', $employee->id)
                    ->whereDate('start_at', $appointmentDate->format('Y-m-d'))
                    ->whereNotIn('status', ['Đã hủy', 'Hoàn thành'])
                    ->get();

                foreach ($existingAppointments as $existing) {
                    $existingStart = Carbon::parse($existing->start_at);
                    $existingEnd = Carbon::parse($existing->end_at);

                    // Calculate minimum start time (existing end + 1 hour)
                    $minStartTime = $existingEnd->copy()->addMinutes($bufferMinutes);

                    // Calculate maximum end time (existing start - 1 hour)
                    $maxEndTime = $existingStart->copy()->subMinutes($bufferMinutes);

                    // Conflict occurs if:
                    // New appointment starts before minStartTime AND ends after maxEndTime
                    if ($startAt < $minStartTime && $endAt > $maxEndTime) {
                        $existingStartFormatted = $existingStart->format('H:i');
                        $existingEndFormatted = $existingEnd->format('H:i');
                        $newStartFormatted = $startAt->format('H:i');
                        $newEndFormatted = $endAt->format('H:i');
                        return back()->withInput()
                            ->withErrors(['appointment_time' => "Đã có người đặt trong khoảng thời gian {$existingStartFormatted} - {$existingEndFormatted}. Lịch hẹn của bạn ({$newStartFormatted} - {$newEndFormatted}) quá gần. Các lịch hẹn phải cách nhau ít nhất 1 giờ. Vui lòng chọn thời gian khác."]);
                    }
                }
            }

            // Create appointment
            $appointment = $this->appointmentService->create([
                'user_id' => $userId,
                'employee_id' => $validated['staff_id'] ?? $employee->id,
                'status' => 'Chờ xác nhận',
                'start_at' => $startAt,
                'end_at' => $endAt,
                'note' => $validated['note'] ?? null,
            ], $serviceVariantData);

            // If employee selected a promotion code, link it to this appointment & customer
            if (!empty($validated['promotion_code'] ?? null)) {
                // Chỉ chấp nhận promotion có status = 'active'
                // Command sẽ tự động cập nhật trạng thái dựa trên ngày
                $promotion = Promotion::where('code', $validated['promotion_code'])
                    ->where('status', 'active')
                    ->whereNull('deleted_at')
                    ->first();

                if ($promotion) {
                    PromotionUsage::create([
                        'promotion_id'   => $promotion->id,
                        'user_id'        => $userId,
                        'appointment_id' => $appointment->id,
                        'used_at' => now(),
                    ]);
                }
            }

            return redirect()->route('employee.appointments.show', $appointment->id)
                ->with('success', 'Đã tạo lịch hẹn thành công!');
        } catch (\Exception $e) {
            // Ghi log rồi ném lại exception để hiển thị lỗi cụ thể (trong môi trường dev)
            \Log::error('Error creating appointment by employee', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Display a listing of appointments for all employees.
     * All employees can see all appointments.
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

        // Get all appointments with filters and pagination (not filtered by employee)
        $appointments = $this->appointmentService->getAllWithFiltersPaginated(
            $filters,
            10
        );

        return view('admin.employee-appointments.index', compact('appointments', 'filters', 'employee'));
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

        // All employees can view all appointments
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

        // All employees can change appointment status
        if ($appointment->status !== 'Chờ xác nhận' && $appointment->status !== 'Chờ xử lý') {
            return redirect()->route('employee.appointments.show', $id)
                ->with('error', 'Chỉ có thể xác nhận đơn ở trạng thái "Chờ xác nhận" hoặc "Chờ xử lý".');
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

        // All employees can change appointment status
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

        // All employees can change appointment status
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
    public function cancel(CancelAppointmentRequest $request, string $id)
    {
        $user = auth()->user();
        $employee = $this->employeeService->getByUserId($user->id);

        if (!$employee) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Bạn không phải là nhân viên.');
        }

        $appointment = $this->appointmentService->getOne($id);

        // All employees can change appointment status
        // Check if appointment can be cancelled (only Chờ xác nhận or Chờ xử lý)
        if ($appointment->status !== 'Chờ xác nhận' && $appointment->status !== 'Chờ xử lý') {
            return redirect()->route('employee.appointments.show', $id)
                ->with('error', 'Chỉ có thể hủy đơn đặt ở trạng thái "Chờ xác nhận" hoặc "Chờ xử lý".');
        }

        $validated = $request->validated();

        $result = $this->appointmentService->cancelAppointment($id, $validated['cancellation_reason']);
        // Employee hủy không cần kiểm tra ban

        return redirect()->route('employee.appointments.show', $id)
            ->with('success', 'Đơn đặt đã được hủy thành công!');
    }

    /**
     * Delete an appointment (only for unconfirmed appointments).
     */
    public function destroy(string $id)
    {
        $user = auth()->user();
        $employee = $this->employeeService->getByUserId($user->id);

        if (!$employee) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Bạn không phải là nhân viên.');
        }

        $appointment = $this->appointmentService->getOne($id);

        // All employees can delete appointments
        // Check if appointment can be deleted (only Chờ xác nhận or Chờ xử lý)
        if ($appointment->status !== 'Chờ xác nhận' && $appointment->status !== 'Chờ xử lý') {
            return redirect()->route('employee.appointments.show', $id)
                ->with('error', 'Chỉ có thể xóa đơn đặt ở trạng thái "Chờ xác nhận" hoặc "Chờ xử lý".');
        }

        try {
            $this->appointmentService->delete($id);

            return redirect()->route('employee.appointments.index')
                ->with('success', 'Đơn đặt đã được xóa thành công!');
        } catch (\Exception $e) {
            \Log::error('Error deleting appointment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('employee.appointments.show', $id)
                ->with('error', 'Có lỗi xảy ra khi xóa đơn đặt. Vui lòng thử lại.');
        }
    }

    /**
     * Show the form for editing the specified appointment.
     * Only Receptionist can edit appointments.
     */
    public function edit(string $id)
    {
        $user = auth()->user();
        $employee = $this->employeeService->getByUserId($user->id);

        if (!$employee) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Bạn không phải là nhân viên.');
        }

        // Check if employee is Receptionist
        if ($employee->position !== 'Receptionist') {
            return redirect()->route('employee.appointments.index')
                ->with('error', 'Chỉ nhân viên lễ tân mới được phép sửa lịch hẹn.');
        }

        $appointment = $this->appointmentService->getOne($id);

        // Check if appointment belongs to this employee (directly or via appointment details)
        $hasAccess = $appointment->employee_id == $employee->id ||
            $appointment->appointmentDetails->where('employee_id', $employee->id)->count() > 0;

        if (!$hasAccess) {
            return redirect()->route('employee.appointments.index')
                ->with('error', 'Bạn không có quyền sửa đơn đặt này.');
        }

        // Get all employees (for assignment)
        $employees = \App\Models\Employee::whereIn('position', ['Stylist', 'Barber'])
            ->with('user')
            ->get();

        // Get single services (no variants)
        $singleServices = \App\Models\Service::whereNull('deleted_at')
            ->whereDoesntHave('serviceVariants')
            ->get();

        // Get services with variants
        $variantServices = \App\Models\Service::whereNull('deleted_at')
            ->whereHas('serviceVariants')
            ->with('serviceVariants')
            ->get();

        // Get combos
        $combos = \App\Models\Combo::whereNull('deleted_at')
            ->with('comboItems')
            ->get();

        // Determine current service type
        $currentServiceType = 'variant';
        $currentServiceId = null;
        $currentServiceVariantId = null;
        $currentComboId = null;

        if ($appointment->appointmentDetails->count() > 0) {
            $detail = $appointment->appointmentDetails->first();
            if ($detail->combo_id) {
                $currentServiceType = 'combo';
                $currentComboId = $detail->combo_id;
            } elseif ($detail->service_variant_id) {
                $currentServiceType = 'variant';
                $currentServiceVariantId = $detail->service_variant_id;
            } else {
                $currentServiceType = 'single';
                if ($detail->notes) {
                    $service = \App\Models\Service::where('name', $detail->notes)
                        ->whereNull('deleted_at')
                        ->whereDoesntHave('serviceVariants')
                        ->first();
                    if ($service) {
                        $currentServiceId = $service->id;
                    }
                }
            }
        }

        return view('admin.employee-appointments.edit', compact(
            'appointment',
            'employees',
            'singleServices',
            'variantServices',
            'combos',
            'currentServiceType',
            'currentServiceId',
            'currentServiceVariantId',
            'currentComboId'
        ));
    }

    /**
     * Update the specified appointment in storage.
     * Only Receptionist can update appointments.
     */
    public function update(Request $request, string $id)
    {
        $user = auth()->user();
        $employee = $this->employeeService->getByUserId($user->id);

        if (!$employee) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Bạn không phải là nhân viên.');
        }

        // Check if employee is Receptionist
        if ($employee->position !== 'Receptionist') {
            return redirect()->route('employee.appointments.index')
                ->with('error', 'Chỉ nhân viên lễ tân mới được phép sửa lịch hẹn.');
        }

        $appointment = $this->appointmentService->getOne($id);

        // Check if appointment belongs to this employee (directly or via appointment details)
        $hasAccess = $appointment->employee_id == $employee->id ||
            $appointment->appointmentDetails->where('employee_id', $employee->id)->count() > 0;

        if (!$hasAccess) {
            return redirect()->route('employee.appointments.index')
                ->with('error', 'Bạn không có quyền sửa đơn đặt này.');
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'email' => 'nullable|email|max:255',
                'employee_id' => [
                    'nullable',
                    'exists:employees,id',
                ],
                'new_services' => 'nullable|array',
                'new_services.*' => 'nullable|string',
                'status' => 'required|in:Chờ xử lý,Chờ xác nhận,Đã xác nhận,Đang thực hiện,Hoàn thành,Đã hủy',
                'note' => 'nullable|string',
                'appointment_date' => 'nullable|date',
                'appointment_time' => 'nullable|string',
            ]);

            // Update user info
            $customer = $appointment->user;
            if ($customer) {
                $customer->update([
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'] ?? $customer->email,
                ]);
            }

            // Prepare appointment data
            $appointmentData = [
                'user_id' => $customer->id,
                'employee_id' => $validated['employee_id'] ?? $appointment->employee_id,
                'status' => $validated['status'],
                'note' => $validated['note'] ?? null,
            ];

            // Calculate total duration and prepare service variant data
            $totalDuration = 0;
            $serviceVariantData = [];

            if (!empty($validated['new_services'])) {
                foreach ($validated['new_services'] as $serviceStr) {
                    if (empty($serviceStr)) continue;

                    $parts = explode(':', $serviceStr);
                    $serviceType = $parts[0] ?? '';
                    $serviceId = $parts[1] ?? null;

                    if ($serviceType === 'variant' && $serviceId) {
                        $variant = \App\Models\ServiceVariant::find($serviceId);
                        if ($variant) {
                            $totalDuration += $variant->duration ?? 60;
                            $serviceVariantData[] = [
                                'service_variant_id' => $variant->id,
                                'employee_id' => $validated['employee_id'] ?? $appointment->employee_id,
                                'price_snapshot' => $variant->price,
                                'duration' => $variant->duration ?? 60,
                                'status' => 'Chờ',
                            ];
                        }
                    } elseif ($serviceType === 'combo' && $serviceId) {
                        $combo = \App\Models\Combo::find($serviceId);
                        if ($combo) {
                            $totalDuration += $combo->duration ?? 60;
                            $serviceVariantData[] = [
                                'combo_id' => $combo->id,
                                'employee_id' => $validated['employee_id'] ?? $appointment->employee_id,
                                'price_snapshot' => $combo->price,
                                'duration' => $combo->duration ?? 60,
                                'status' => 'Chờ',
                            ];
                        }
                    } elseif ($serviceType === 'single' && $serviceId) {
                        $service = \App\Models\Service::find($serviceId);
                        if ($service) {
                            $duration = $service->base_duration ?? 60;
                            $price = $service->base_price ?? 0;
                            $totalDuration += $duration;
                            $serviceVariantData[] = [
                                'service_variant_id' => null,
                                'employee_id' => $validated['employee_id'] ?? $appointment->employee_id,
                                'price_snapshot' => $price,
                                'duration' => $duration,
                                'status' => 'Chờ',
                                'notes' => $service->name,
                            ];
                        }
                    }
                }
            }

            // Set start_at and end_at if date and time provided
            if (!empty($validated['appointment_date']) && !empty($validated['appointment_time'])) {
                $startAt = Carbon::parse($validated['appointment_date'] . ' ' . $validated['appointment_time']);
                $appointmentData['start_at'] = $startAt;

                if ($totalDuration > 0) {
                    $appointmentData['end_at'] = $startAt->copy()->addMinutes($totalDuration);
                } else {
                    // Use existing duration if no new services
                    if ($appointment->end_at && $appointment->start_at) {
                        $existingDuration = $appointment->start_at->diffInMinutes($appointment->end_at);
                        $appointmentData['end_at'] = $startAt->copy()->addMinutes($existingDuration);
                    }
                }
            }

            $this->appointmentService->update($id, $appointmentData, $serviceVariantData);

            return redirect()->route('employee.appointments.index')
                ->with('success', 'Lịch hẹn đã được cập nhật thành công!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error updating appointment by employee', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi cập nhật lịch hẹn: ' . $e->getMessage());
        }
    }
}
