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
use App\Services\PaymentService;
use App\Services\PromotionService;
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
     * Kiểm tra xem việc chuyển đổi trạng thái có được phép không.
     * Không cho phép rollback về trạng thái trước đó.
     * 
     * @param string $oldStatus Trạng thái hiện tại
     * @param string $newStatus Trạng thái mới
     * @return bool
     */
    private function isStatusTransitionAllowed(string $oldStatus, string $newStatus): bool
    {
        // Nếu trạng thái không đổi, cho phép
        if ($oldStatus === $newStatus) {
            return true;
        }

        // Định nghĩa thứ tự trạng thái (từ thấp đến cao)
        $statusOrder = [
            'Chờ xử lý' => 0,
            'Chờ xác nhận' => 1,
            'Đã xác nhận' => 2,
            'Đang thực hiện' => 3,
            'Hoàn thành' => 4,
            'Đã hủy' => -1, // Đặc biệt: có thể ở bất kỳ thời điểm nào
        ];

        // Nếu trạng thái cũ hoặc mới không có trong danh sách, cho phép (để tránh lỗi)
        if (!isset($statusOrder[$oldStatus]) || !isset($statusOrder[$newStatus])) {
            return true;
        }

        $oldOrder = $statusOrder[$oldStatus];
        $newOrder = $statusOrder[$newStatus];

        // Nếu chuyển sang "Đã hủy", luôn cho phép (trừ khi đã "Hoàn thành")
        if ($newStatus === 'Đã hủy') {
            return $oldStatus !== 'Hoàn thành';
        }

        // Nếu đang ở "Đã hủy", không cho phép chuyển sang trạng thái khác
        if ($oldStatus === 'Đã hủy') {
            return false;
        }

        // Nếu đã "Hoàn thành", không cho phép chuyển sang trạng thái khác
        if ($oldStatus === 'Hoàn thành') {
            return false;
        }

        // Chỉ cho phép chuyển sang trạng thái có thứ tự cao hơn (tiến về phía trước)
        return $newOrder > $oldOrder;
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

        // Receptionist can edit all appointments, no need to check ownership

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

        // Check if employee is Receptionist - Receptionist can edit all appointments
        if ($employee->position !== 'Receptionist') {
            return redirect()->route('employee.appointments.index')
                ->with('error', 'Chỉ nhân viên lễ tân mới được phép sửa lịch hẹn.');
        }

        $appointment = $this->appointmentService->getOne($id);

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

            // Kiểm tra không cho rollback trạng thái
            $oldStatus = $appointment->status;
            $newStatus = $validated['status'];
            if (!$this->isStatusTransitionAllowed($oldStatus, $newStatus)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Không thể chuyển trạng thái từ '{$oldStatus}' về '{$newStatus}'. Trạng thái chỉ có thể tiến về phía trước theo trình tự.");
            }

            // Prepare appointment data
            $appointmentData = [
                'user_id' => $customer->id,
                'employee_id' => $validated['employee_id'] ?? $appointment->employee_id,
                'status' => $validated['status'],
                'note' => $validated['note'] ?? null,
            ];

            // Prepare new services data if any
            $newServiceVariantData = [];
            $additionalDuration = 0;

            if (!empty($validated['new_services'])) {
                foreach ($validated['new_services'] as $serviceValue) {
                    if (empty($serviceValue)) continue;

                    // Format: "type_id" (e.g., "single_1", "variant_5", "combo_2")
                    $parts = explode('_', $serviceValue);
                    if (count($parts) !== 2) {
                        continue;
                    }
                    
                    $serviceType = $parts[0];
                    $serviceId = $parts[1];

                    if ($serviceType === 'variant' && $serviceId) {
                        $variant = \App\Models\ServiceVariant::find($serviceId);
                        if ($variant) {
                            $additionalDuration += $variant->duration ?? 60;
                            $newServiceVariantData[] = [
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
                            $additionalDuration += $combo->duration ?? 60;
                            $newServiceVariantData[] = [
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
                            $additionalDuration += $duration;
                            $newServiceVariantData[] = [
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
                
                // Kiểm tra không được chọn ngày giờ trong quá khứ
                $now = Carbon::now('Asia/Ho_Chi_Minh');
                if ($startAt->lt($now)) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Không được chọn ngày giờ trong quá khứ! Vui lòng chọn ngày giờ từ bây giờ trở đi.');
                }
                
                $appointmentData['start_at'] = $startAt;
                
                // Calculate total duration from existing services + new services
                // Reload appointment to get fresh appointment details count
                $appointment->refresh();
                $existingDuration = $appointment->appointmentDetails->sum('duration');
                $totalDuration = $existingDuration + $additionalDuration;
                
                // Always set end_at if we have start_at, even if duration is 0
                $appointmentData['end_at'] = $startAt->copy()->addMinutes(max($totalDuration, 60)); // Minimum 60 minutes if no duration
            }

            $this->appointmentService->update($id, $appointmentData, $newServiceVariantData);

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

    /**
     * Remove a service from appointment.
     * Only Receptionist can remove services.
     */
    public function removeService(string $appointmentId, string $detailId)
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
                ->with('error', 'Chỉ nhân viên lễ tân mới được phép xóa dịch vụ.');
        }

        try {
            $appointment = $this->appointmentService->getOne($appointmentId);
            $detail = \App\Models\AppointmentDetail::findOrFail($detailId);
            
            if ($detail->appointment_id != $appointment->id) {
                return redirect()->back()->with('error', 'Dịch vụ không thuộc lịch hẹn này!');
            }
            
            // Kiểm tra số lượng dịch vụ - không cho xóa nếu chỉ còn 1 dịch vụ
            $serviceCount = $appointment->appointmentDetails->count();
            if ($serviceCount <= 1) {
                return redirect()->back()->with('error', 'Không thể xóa dịch vụ cuối cùng! Đơn đặt phải có ít nhất 1 dịch vụ.');
            }
            
            // Receptionist can remove services from all appointments, no need to check ownership
            
            $detail->delete();
            
            // Update appointment end_at if needed (recalculate based on remaining services)
            $appointment->refresh();
            $remainingDuration = $appointment->appointmentDetails->sum('duration');
            if ($appointment->start_at && $remainingDuration > 0) {
                $appointment->end_at = Carbon::parse($appointment->start_at)->addMinutes($remainingDuration);
                $appointment->save();
            }
            
            return redirect()->route('employee.appointments.edit', $appointmentId)
                ->with('success', 'Dịch vụ đã được xóa thành công!');
        } catch (\Exception $e) {
            \Log::error('Error removing service from appointment by employee', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Show Checkout Page for Employee.
     */
    public function checkout(Request $request)
    {
        $user = auth()->user();
        $employee = $this->employeeService->getByUserId($user->id);

        if (!$employee) {
            return redirect()->route('admin.dashboard')->with('error', 'Bạn không phải là nhân viên.');
        }

        $appointmentId = $request->input('appointment_id');
        if (!$appointmentId) {
            return redirect()->route('employee.appointments.index')->with('error', 'Thiếu thông tin lịch hẹn.');
        }

        $appointment = $this->appointmentService->getOne($appointmentId);

        // Check Access
        $hasAccess = $employee->position === 'Receptionist' ||
            $appointment->employee_id == $employee->id ||
            $appointment->appointmentDetails->where('employee_id', $employee->id)->count() > 0;

        if (!$hasAccess) {
            return redirect()->route('employee.appointments.index')->with('error', 'Bạn không có quyền thanh toán đơn đặt này.');
        }
        
        if ($appointment->status === 'Đã thanh toán') {
            return redirect()->route('employee.appointments.show', $appointment->id)
                             ->with('info', 'Lịch hẹn này đã được thanh toán.');
        }

        // Construct Cart
        $services = [];
        $subtotal = 0;
        
        $cart = [
            'appointment_' . $appointment->id => [
                'type' => 'appointment',
                'id' => $appointment->id,
                'quantity' => 1
            ]
        ];
        \Illuminate\Support\Facades\Session::put('cart', $cart);

        foreach ($appointment->appointmentDetails as $detail) {
            if ($detail->serviceVariant && $detail->serviceVariant->service) {
                $price = $detail->price_snapshot ?? ($detail->serviceVariant->price ?? 0);
                $subtotal += $price;
                $services[] = [
                    'name' => '[Lịch hẹn] ' . $detail->serviceVariant->service->name . ' - ' . $detail->serviceVariant->name,
                    'price' => $price,
                ];
            } elseif (!$detail->serviceVariant && !$detail->combo_id && $detail->notes) {
                $price = $detail->price_snapshot ?? 0;
                $subtotal += $price;
                $services[] = [
                    'name' => '[Lịch hẹn] ' . $detail->notes,
                    'price' => $price,
                ];
            } elseif ($detail->combo_id && $detail->combo) {
                $price = $detail->price_snapshot ?? ($detail->combo->price ?? 0);
                $subtotal += $price;
                $services[] = [
                    'name' => '[Lịch hẹn] Combo: ' . $detail->combo->name,
                    'price' => $price,
                ];
            }
        }

        // Customer Info
        $customerData = [
            'name' => $appointment->user->name ?? 'Khách vãng lai',
            'phone' => $appointment->user->phone ?? '',
            'email' => $appointment->user->email ?? '',
        ];

        // Promotion Logic
        $promotionAmount = 0; 
        $couponCode = \Illuminate\Support\Facades\Session::get('coupon_code');
        $appliedCoupon = null;
        $promotionMessage = null;

        if ($couponCode) {
            $promotionService = app(PromotionService::class);
            $userIdForPromo = $appointment->user_id ?? $user->id;
            
            $result = $promotionService->validateAndCalculateDiscount(
                $couponCode,
                $cart,
                $subtotal,
                $userIdForPromo
            );

            if ($result['valid']) {
                $promotionAmount = $result['discount_amount'];
                $appliedCoupon = $result['promotion'];
                $promotionMessage = $result['message'];
            } else {
                \Illuminate\Support\Facades\Session::forget('coupon_code');
                $promotionMessage = $result['message'];
            }
        }

        $taxablePrice = max(0, $subtotal - $promotionAmount);
        $VAT = 0;
        $total = $taxablePrice;
        
        return view('admin.employee-appointments.checkout', [
            'customer' => $customerData,
            'services' => $services,
            'promotion' => $promotionAmount,
            'appliedCoupon' => $appliedCoupon,
            'promotionMessage' => $promotionMessage,
            'subtotal' => $subtotal,
            'taxablePrice' => $taxablePrice,
            'vat' => $VAT,
            'total' => $total,
            'payment_methods' => [
                ['id' => 'cash', 'name' => 'Thanh toán tại quầy'],
                ['id' => 'card', 'name' => 'Thẻ tín dụng'],
                ['id' => 'vnpay', 'name' => 'VNPAY'],
                ['id' => 'zalopay', 'name' => 'ZaloPay'],
            ],
            'appointment' => $appointment
        ]);
    }

    /**
     * Process Checkout for Employee.
     */
    public function processCheckout(Request $request, PaymentService $paymentService)
    {
        $user = auth()->user();
        $employee = $this->employeeService->getByUserId($user->id);

        if (!$employee) {
            return redirect()->route('admin.dashboard')->with('error', 'Bạn không phải là nhân viên.');
        }

        try {
            $cart = \Illuminate\Support\Facades\Session::get('cart', []);
            
            // Determine Payer
            $payer = null;
            $appointmentId = null;

            foreach ($cart as $item) {
                if (isset($item['type']) && $item['type'] === 'appointment') {
                    $appointmentId = $item['id'];
                    $appt = \App\Models\Appointment::find($item['id']);
                    if ($appt) {
                        $payer = $appt->user;
                        
                        // Check Access again for safety
                        $hasAccess = $employee->position === 'Receptionist' ||
                            $appt->employee_id == $employee->id ||
                            $appt->appointmentDetails->where('employee_id', $employee->id)->count() > 0;
                        if (!$hasAccess) {
                             return redirect()->route('employee.appointments.index')->with('error', 'Bạn không có quyền thanh toán đơn đặt này.');
                        }
                    }
                    break; 
                }
            }

            if (!$payer) {
                 return redirect()->back()->with('error', 'Không tìm thấy thông tin khách hàng trong đơn hàng.');
            }

            $couponCode = \Illuminate\Support\Facades\Session::get('coupon_code');
            $paymentMethod = $request->input('payment_method', 'cash');

            // Process Payment
            $payment = $paymentService->processPayment($payer, $cart, $paymentMethod, $couponCode);

            // Auto-complete Cash Payment
            if ($paymentMethod === 'cash') {
                 $payment->status = 'completed';
                 $payment->save();
                 
                 if ($payment->appointment_id) {
                     $appt = \App\Models\Appointment::find($payment->appointment_id);
                     if ($appt) {
                         $appt->status = 'Đã thanh toán';
                         $appt->save();
                         foreach ($appt->appointmentDetails as $detail) {
                            $detail->status = 'Hoàn thành';
                            $detail->save();
                         }
                     }
                 }
                 
                 if ($payment->order_id) {
                     $order = \App\Models\Order::find($payment->order_id);
                     if ($order) {
                         $order->status = 'Đã thanh toán';
                         $order->save();
                     }
                 }
            }
            
            \Illuminate\Support\Facades\Session::forget('cart');
            \Illuminate\Support\Facades\Session::forget('coupon_code');

            if ($paymentMethod === 'vnpay') {
                // Save context for return URL handling
                \Illuminate\Support\Facades\Session::put('payment_source', 'employee');
                \Illuminate\Support\Facades\Session::put('payment_appointment_id', $payment->appointment_id);

                $vnpayService = app(\App\Services\VnpayService::class);
                $vnpUrl = $vnpayService->createPayment($payment->invoice_code, $payment->total);
                return redirect($vnpUrl);
            }

            return redirect()->route('employee.appointments.index')
                ->with('success', 'Thanh toán thành công cho lịch hẹn #' . $payment->appointment_id);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error($e);
            return back()->with('error', 'Thanh toán thất bại: ' . $e->getMessage());
        }
    }

    /**
     * Apply a coupon code from the employee checkout page.
     */
    public function applyCoupon(Request $request, PromotionService $promotionService)
    {
        $request->validate([
            'coupon_code' => 'required|string|max:50'
        ]);

        $code = $request->input('coupon_code');
        $appointmentId = $request->input('appointment_id'); // Hidden field in form

        if (!$appointmentId) {
            return back()->with('error', 'Thiếu thông tin lịch hẹn để áp dụng mã khuyến mãi.');
        }

        $appointment = \App\Models\Appointment::with('user')->find($appointmentId);
        if (!$appointment) {
            return back()->with('error', 'Không tìm thấy lịch hẹn.');
        }

        $user = auth()->user();
        $employee = $this->employeeService->getByUserId($user->id);

        if (!$employee) {
            return redirect()->route('admin.dashboard')->with('error', 'Bạn không phải là nhân viên.');
        }
        // Check Access
        $hasAccess = $employee->position === 'Receptionist' ||
            $appointment->employee_id == $employee->id ||
            $appointment->appointmentDetails->where('employee_id', $employee->id)->count() > 0;

        if (!$hasAccess) {
            return back()->with('error', 'Bạn không có quyền áp dụng khuyến mãi cho đơn đặt này.');
        }

        // Calculate subtotal for promotion validation
        $subtotal = 0;
        foreach ($appointment->appointmentDetails as $detail) {
            if ($detail->price_snapshot) {
                $subtotal += $detail->price_snapshot;
            } elseif ($detail->serviceVariant) {
                $subtotal += $detail->serviceVariant->price;
            } elseif ($detail->combo) {
                $subtotal += $detail->combo->price;
            }
        }

        // Construct cart data for validation, just this appointment
        $cart = [
            'appointment_' . $appointment->id => [
                'type' => 'appointment',
                'id' => $appointment->id,
                'quantity' => 1
            ]
        ];
        
        $result = $promotionService->validateAndCalculateDiscount(
            $code,
            $cart,
            $subtotal,
            $appointment->user_id
        );
        
        if (!$result['valid']) {
            return back()->with('error', $result['message']);
        }
        
        \Illuminate\Support\Facades\Session::put('coupon_code', $code);
        
        return back()->with('success', 'Áp dụng mã khuyến mại thành công!');
    }

    /**
     * Remove applied coupon code from the employee checkout page.
     */
    public function removeCoupon(Request $request)
    {
        \Illuminate\Support\Facades\Session::forget('coupon_code');
        // Redirect back to the checkout page, potentially with the appointment_id if present
        $appointmentId = $request->input('appointment_id');
        if ($appointmentId) {
            return redirect()->route('employee.appointments.checkout', ['appointment_id' => $appointmentId])
                             ->with('success', 'Đã gỡ bỏ mã khuyến mại.');
        }
        return back()->with('success', 'Đã gỡ bỏ mã khuyến mại.');
    }
}
