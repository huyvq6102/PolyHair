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

        // Cho phép sửa khi status là "Hoàn thành" - có thể giữ nguyên hoặc chuyển sang "Đã thanh toán"
        // Nếu đã "Hoàn thành", chỉ cho phép giữ nguyên hoặc chuyển sang "Đã thanh toán"
        if ($oldStatus === 'Hoàn thành') {
            // Cho phép giữ nguyên hoặc chuyển sang "Đã thanh toán"
            return $newStatus === 'Hoàn thành' || $newStatus === 'Đã thanh toán';
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

        // Chỉ lấy nhân viên có position là Stylist (giống logic admin)
        $staffMembers = \App\Models\Employee::whereHas('user')
            ->where('position', 'Stylist')
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
            'staff_id' => [
                'required',
                'exists:employees,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $employee = \App\Models\Employee::find($value);
                        if (!$employee || $employee->position !== 'Stylist') {
                            $fail('Chỉ có thể chọn nhân viên có vị trí là Stylist.');
                        }
                    }
                },
            ],
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

            // If employee selected a promotion code, lưu vào session để sử dụng khi thanh toán
            // KHÔNG tạo PromotionUsage ở đây vì appointment chưa thanh toán
            // PromotionUsage sẽ được tạo sau khi thanh toán thành công
            if (!empty($validated['promotion_code'] ?? null)) {
                \Illuminate\Support\Facades\Session::put('coupon_code', $validated['promotion_code']);
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

        // Không cho phép sửa khi trạng thái là "Đã thanh toán"
        if ($appointment->status === 'Đã thanh toán') {
            return redirect()->route('employee.appointments.index')
                ->with('error', 'Không thể sửa lịch hẹn đã thanh toán.');
        }

        // Receptionist can edit all appointments, no need to check ownership

        // Chỉ lấy nhân viên có position là Stylist (giống logic admin)
        $employees = \App\Models\Employee::where('position', 'Stylist')
            ->with('user')
            ->get();

        // Lấy danh sách các dịch vụ đã có trong appointment để loại bỏ khỏi danh sách thêm mới
        $existingServiceIds = [];
        $existingVariantIds = [];
        $existingComboIds = [];
        
        foreach ($appointment->appointmentDetails as $detail) {
            if ($detail->combo_id) {
                $existingComboIds[] = $detail->combo_id;
            } elseif ($detail->service_variant_id) {
                $existingVariantIds[] = $detail->service_variant_id;
                // Lấy service_id từ variant
                if ($detail->serviceVariant && $detail->serviceVariant->service_id) {
                    $existingServiceIds[] = $detail->serviceVariant->service_id;
                }
            } elseif ($detail->notes) {
                // Tìm service theo notes (tên dịch vụ)
                $service = \App\Models\Service::where('name', $detail->notes)
                    ->whereNull('deleted_at')
                    ->whereDoesntHave('serviceVariants')
                    ->first();
                if ($service) {
                    $existingServiceIds[] = $service->id;
                }
            }
        }

        // Get single services (no variants) - loại bỏ các dịch vụ đã có
        $singleServices = \App\Models\Service::whereNull('deleted_at')
            ->whereDoesntHave('serviceVariants')
            ->whereNotIn('id', $existingServiceIds)
            ->get();

        // Get services with variants - loại bỏ các dịch vụ đã có
        $variantServices = \App\Models\Service::whereNull('deleted_at')
            ->whereHas('serviceVariants')
            ->whereNotIn('id', $existingServiceIds)
            ->with(['serviceVariants' => function($query) use ($existingVariantIds) {
                // Loại bỏ các variant đã có
                if (!empty($existingVariantIds)) {
                    $query->whereNotIn('id', $existingVariantIds);
                }
            }])
            ->get()
            ->filter(function($service) {
                // Chỉ giữ lại service có ít nhất 1 variant chưa được thêm
                return $service->serviceVariants->count() > 0;
            });

        // Get combos - loại bỏ các combo đã có
        $combos = \App\Models\Combo::whereNull('deleted_at')
            ->whereNotIn('id', $existingComboIds)
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

        // Không cho phép sửa khi trạng thái là "Đã thanh toán"
        if ($appointment->status === 'Đã thanh toán') {
            return redirect()->route('employee.appointments.index')
                ->with('error', 'Không thể sửa lịch hẹn đã thanh toán.');
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'email' => 'nullable|email|max:255',
                'employee_id' => [
                    'nullable',
                    'exists:employees,id',
                    function ($attribute, $value, $fail) {
                        if ($value) {
                            $employee = \App\Models\Employee::find($value);
                            if (!$employee || $employee->position !== 'Stylist') {
                                $fail('Chỉ có thể chọn nhân viên có vị trí là Stylist.');
                            }
                        }
                    },
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
                'user_id' => $customer ? $customer->id : $appointment->user_id,
                'employee_id' => $validated['employee_id'] ?? $appointment->employee_id,
                'status' => $validated['status'],
                'note' => $validated['note'] ?? null,
            ];
            // Nếu là guest appointment (không có user), cập nhật guest info
            if (!$customer) {
                $appointmentData['guest_name'] = $validated['name'];
                $appointmentData['guest_phone'] = $validated['phone'];
                $appointmentData['guest_email'] = $validated['email'] ?? null;
            }

            // Load active promotions for automatic discount calculation (only service-level promotions)
            $now = Carbon::now();
            $activePromotions = \App\Models\Promotion::with(['services', 'combos', 'serviceVariants'])
                ->whereNull('deleted_at')
                ->where('status', 'active')
                ->where('apply_scope', 'service') // Chỉ lấy promotion có apply_scope = 'service'
                ->where(function($query) use ($now) {
                    $query->where(function($q) use ($now) {
                        $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
                    })->where(function($q) use ($now) {
                        $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
                    });
                })
                ->get();

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
                        $variant = \App\Models\ServiceVariant::with('service')->find($serviceId);
                        if ($variant) {
                            // Calculate discount for this variant
                            // Calculate discount for this variant - truyền user_id của appointment
                            $userId = $appointment->user_id;
                            $discountResult = $this->calculateDiscountForItem($variant, 'variant', $activePromotions, $userId);
                            $finalPrice = $discountResult['finalPrice'];
                            
                            $additionalDuration += $variant->duration ?? 60;
                            $newServiceVariantData[] = [
                                'service_variant_id' => $variant->id,
                                'employee_id' => $validated['employee_id'] ?? $appointment->employee_id,
                                'price_snapshot' => $finalPrice, // Save price after discount
                                'duration' => $variant->duration ?? 60,
                                'status' => 'Chờ',
                            ];
                        }
                    } elseif ($serviceType === 'combo' && $serviceId) {
                        $combo = \App\Models\Combo::with('comboItems.serviceVariant')->find($serviceId);
                        if ($combo) {
                            // Calculate discount for this combo
                            // Calculate discount for this combo - truyền user_id của appointment
                            $userId = $appointment->user_id;
                            $discountResult = $this->calculateDiscountForItem($combo, 'combo', $activePromotions, $userId);
                            $finalPrice = $discountResult['finalPrice'];
                            
                            $additionalDuration += $combo->duration ?? 60;
                            $newServiceVariantData[] = [
                                'combo_id' => $combo->id,
                                'employee_id' => $validated['employee_id'] ?? $appointment->employee_id,
                                'price_snapshot' => $finalPrice, // Save price after discount
                                'duration' => $combo->duration ?? 60,
                                'status' => 'Chờ',
                            ];
                        }
                    } elseif ($serviceType === 'single' && $serviceId) {
                        $service = \App\Models\Service::find($serviceId);
                        if ($service) {
                            // Calculate discount for this service
                            // Calculate discount for this service - truyền user_id của appointment
                            $userId = $appointment->user_id;
                            $discountResult = $this->calculateDiscountForItem($service, 'service', $activePromotions, $userId);
                            $finalPrice = $discountResult['finalPrice'];
                            
                            $duration = $service->base_duration ?? 60;
                            $additionalDuration += $duration;
                            $newServiceVariantData[] = [
                                'service_variant_id' => null,
                                'employee_id' => $validated['employee_id'] ?? $appointment->employee_id,
                                'price_snapshot' => $finalPrice, // Save price after discount
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

        // Get applied promotion ID from request or session
        $appliedPromotionId = $request->input('applied_promotion_id')
            ?? \Illuminate\Support\Facades\Session::get('applied_promotion_id');

        // If promotion ID is provided, use it
        if ($appliedPromotionId) {
            $promotion = Promotion::find($appliedPromotionId);
            if ($promotion) {
                $couponCode = $promotion->code;
                \Illuminate\Support\Facades\Session::put('coupon_code', $couponCode);
                \Illuminate\Support\Facades\Session::put('applied_promotion_id', $appliedPromotionId);
            }
        }

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

        // Get available promotions for dropdown
        $now = Carbon::now();
        $userId = $appointment->user_id;
        
        $availableOrderPromotions = Promotion::where('apply_scope', 'order')
            ->whereNull('deleted_at')
            ->where(function($query) use ($now) {
                $query->whereNull('start_date')
                      ->orWhere('start_date', '<=', $now);
            })
            ->where(function($query) use ($now) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', $now);
            })
            ->where(function($query) {
                $query->whereNull('status')
                      ->orWhere('status', 'active');
            })
            ->orderBy('id', 'desc')
            ->get()
            ->filter(function($promotion) use ($userId) {
                // Lọc các mã đã hết lượt per_user_limit
                if ($promotion->per_user_limit && $userId) {
                    $usageCount = \App\Models\PromotionUsage::where('promotion_id', $promotion->id)
                        ->where('user_id', $userId)
                        ->whereHas('appointment', function($query) {
                            $query->where('status', 'Đã thanh toán');
                        })
                        ->count();
                    
                    return $usageCount < $promotion->per_user_limit;
                }
                return true;
            })
            ->values();

        $availableCustomerTierPromotions = Promotion::where('apply_scope', 'customer_tier')
            ->whereNull('deleted_at')
            ->where(function($query) use ($now) {
                $query->whereNull('start_date')
                      ->orWhere('start_date', '<=', $now);
            })
            ->where(function($query) use ($now) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', $now);
            })
            ->where(function($query) {
                $query->whereNull('status')
                      ->orWhere('status', 'active');
            })
            ->orderBy('id', 'desc')
            ->get()
            ->filter(function($promotion) use ($userId) {
                // Lọc các mã đã hết lượt per_user_limit
                if ($promotion->per_user_limit && $userId) {
                    $usageCount = \App\Models\PromotionUsage::where('promotion_id', $promotion->id)
                        ->where('user_id', $userId)
                        ->whereHas('appointment', function($query) {
                            $query->where('status', 'Đã thanh toán');
                        })
                        ->count();
                    
                    return $usageCount < $promotion->per_user_limit;
                }
                return true;
            })
            ->values();

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
            'appointment' => $appointment,
            // Pass available promotions
            'availableOrderPromotions' => $availableOrderPromotions,
            'availableCustomerTierPromotions' => $availableCustomerTierPromotions
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

            // If payer is null, it's a guest or product-only order

            // Get promotion information from request or session
            $appliedPromotionId = $request->input('applied_promotion_id');
            $promotionDiscountAmount = $request->input('promotion_discount_amount', 0);
            $couponCode = \Illuminate\Support\Facades\Session::get('coupon_code');

            // If applied_promotion_id is provided, ensure we have the correct coupon code
            if ($appliedPromotionId) {
                $promotion = \App\Models\Promotion::find($appliedPromotionId);
                if ($promotion) {
                    // Always update session with the correct coupon code from promotion
                    $couponCode = $promotion->code;
                    \Illuminate\Support\Facades\Session::put('coupon_code', $couponCode);
                    \Illuminate\Support\Facades\Session::put('applied_promotion_id', $appliedPromotionId);

                    \Illuminate\Support\Facades\Log::info('Employee checkout: Applied promotion', [
                        'promotion_id' => $appliedPromotionId,
                        'coupon_code' => $couponCode,
                        'appointment_id' => $appointmentId,
                        'employee_id' => $employee->id
                    ]);
                }
            } elseif ($couponCode) {
                // If only coupon_code is provided, try to find promotion and save ID
                $promotion = \App\Models\Promotion::where('code', $couponCode)->first();
                if ($promotion) {
                    \Illuminate\Support\Facades\Session::put('applied_promotion_id', $promotion->id);
                }
            }

            $paymentMethod = $request->input('payment_method', 'cash');

            // Process Payment
            // Note: processPayment uses the passed user ($payer) as the owner of the payment record
            $payment = $paymentService->processPayment($payer, $cart, $paymentMethod, $couponCode);

            // Auto-complete Cash Payment
            if ($paymentMethod === 'cash') {
                 $payment->status = 'completed';
                 $payment->save();

                 if ($payment->appointment_id) {
                     $appt = \App\Models\Appointment::find($payment->appointment_id);
                     if ($appt) {
                         $oldStatus = $appt->status;
                         $appt->status = 'Đã thanh toán';
                         $appt->save();
                         foreach ($appt->appointmentDetails as $detail) {
                            $detail->status = 'Hoàn thành';
                            $detail->save();
                         }
                         
                         // Ghi nhận việc sử dụng khuyến mãi order-level/customer_tier (nếu có)
                         // PHẢI gọi TRƯỚC KHI xóa session để có thể lấy promotion từ session
                         $appt->recordPromotionUsage();
                         
                         // Ghi nhận tất cả các khuyến mãi service-level đã được áp dụng
                         $appt->recordServiceLevelPromotionUsages();
                         
                         // Broadcast status update event
                         $appt->refresh();
                         $appt->load([
                             'user',
                             'employee.user',
                             'appointmentDetails.serviceVariant.service',
                             'appointmentDetails.combo'
                         ]);
                         event(new \App\Events\AppointmentStatusUpdated($appt));
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

            // Xóa session SAU KHI đã ghi nhận promotion usage
            \Illuminate\Support\Facades\Session::forget('cart');
            \Illuminate\Support\Facades\Session::forget('coupon_code');
            \Illuminate\Support\Facades\Session::forget('applied_promotion_id');

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
        try {
            $request->validate([
                'coupon_code' => 'required|string|max:50'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        $code = $request->input('coupon_code');
        $appointmentId = $request->input('appointment_id');

        if (!$appointmentId) {
            $errorMsg = 'Thiếu thông tin lịch hẹn để áp dụng mã khuyến mãi.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'error' => $errorMsg
                ], 400);
            }
            return back()->with('error', $errorMsg);
        }

        $appointment = \App\Models\Appointment::with([
            'user',
            'appointmentDetails.serviceVariant',
            'appointmentDetails.combo'
        ])->find($appointmentId);
        if (!$appointment) {
            $errorMsg = 'Không tìm thấy lịch hẹn.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'error' => $errorMsg
                ], 404);
            }
            return back()->with('error', $errorMsg);
        }

        $user = auth()->user();
        $employee = $this->employeeService->getByUserId($user->id);

        if (!$employee) {
            $errorMsg = 'Bạn không phải là nhân viên.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'error' => $errorMsg
                ], 403);
            }
            return redirect()->route('admin.dashboard')->with('error', $errorMsg);
        }

        // Check Access
        $hasAccess = $employee->position === 'Receptionist' ||
            $appointment->employee_id == $employee->id ||
            $appointment->appointmentDetails->where('employee_id', $employee->id)->count() > 0;

        if (!$hasAccess) {
            $errorMsg = 'Bạn không có quyền áp dụng khuyến mãi cho đơn đặt này.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'error' => $errorMsg
                ], 403);
            }
            return back()->with('error', $errorMsg);
        }

        // Calculate subtotal for promotion validation
        $subtotal = 0;
        foreach ($appointment->appointmentDetails as $detail) {
            if ($detail->price_snapshot) {
                $subtotal += $detail->price_snapshot;
            } elseif ($detail->serviceVariant && $detail->serviceVariant->price) {
                $subtotal += $detail->serviceVariant->price;
            } elseif ($detail->combo && $detail->combo->price) {
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

        // Check if request is AJAX
        if ($request->ajax() || $request->wantsJson()) {
            if (!$result['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'error' => $result['message']
                ], 400);
            }

            \Illuminate\Support\Facades\Session::put('coupon_code', $code);

            // Save applied promotion ID if provided
            $appliedPromotionId = $request->input('applied_promotion_id');
            if ($appliedPromotionId && isset($result['promotion'])) {
                \Illuminate\Support\Facades\Session::put('applied_promotion_id', $result['promotion']->id);
            } elseif ($appliedPromotionId) {
                \Illuminate\Support\Facades\Session::put('applied_promotion_id', $appliedPromotionId);
            }

            $promotion = $result['promotion'];
            return response()->json([
                'success' => true,
                'message' => 'Áp dụng mã khuyến mại thành công!',
                'promotion' => [
                    'id' => $promotion->id ?? null,
                    'code' => $promotion->code ?? $code,
                    'name' => $promotion->name ?? '',
                    'discount_amount' => $result['discount_amount'] ?? 0,
                    'discount_type' => $promotion->discount_type ?? 'amount',
                    'discount_percent' => $promotion->discount_percent ?? 0,
                    'max_discount_amount' => $promotion->max_discount_amount !== null ? $promotion->max_discount_amount : null,
                    'apply_scope' => $promotion->apply_scope ?? 'order'
                ]
            ]);
        }

        // Non-AJAX request handling
        if (!$result['valid']) {
            return back()->with('error', $result['message']);
        }

        \Illuminate\Support\Facades\Session::put('coupon_code', $code);

        // Save applied promotion ID if provided
        $appliedPromotionId = $request->input('applied_promotion_id');
        if ($appliedPromotionId && isset($result['promotion'])) {
            \Illuminate\Support\Facades\Session::put('applied_promotion_id', $result['promotion']->id);
        } elseif ($appliedPromotionId) {
            \Illuminate\Support\Facades\Session::put('applied_promotion_id', $appliedPromotionId);
        }

        return back()->with('success', 'Áp dụng mã khuyến mại thành công!');
    }

    /**
     * Remove applied coupon code from the employee checkout page.
     */
    public function removeCoupon(Request $request)
    {
        \Illuminate\Support\Facades\Session::forget('coupon_code');
        \Illuminate\Support\Facades\Session::forget('applied_promotion_id');

        // Check if request is AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã gỡ bỏ mã khuyến mại.'
            ]);
        }

        // Redirect back to the checkout page, potentially with the appointment_id if present
        $appointmentId = $request->input('appointment_id');
        if ($appointmentId) {
            return redirect()->route('employee.appointments.checkout', ['appointment_id' => $appointmentId])
                             ->with('success', 'Đã gỡ bỏ mã khuyến mại.');
        }
        return back()->with('success', 'Đã gỡ bỏ mã khuyến mại.');
    }

    /**
     * Helper function to calculate discount for an item (service/variant/combo)
     * Logic must match with Site AppointmentController and service-list-items.blade.php
     */
    protected function calculateDiscountForItem($item, $itemType, $activePromotions, $userId = null)
    {
        $originalPrice = 0;
        if ($itemType === 'service') {
            $originalPrice = $item->base_price ?? 0;
        } elseif ($itemType === 'variant') {
            $originalPrice = $item->price ?? 0;
        } elseif ($itemType === 'combo') {
            $originalPrice = $item->price ?? 0;
        }

        $discount = 0;        // Highest discount found
        $finalPrice = $originalPrice;
        $promotion = null;    // Promotion with highest discount

        if ($originalPrice <= 0) {
            return [
                'originalPrice' => 0,
                'discount' => 0,
                'finalPrice' => 0,
                'promotion' => null
            ];
        }

        $now = Carbon::now();

        foreach ($activePromotions ?? [] as $promo) {
            // Only apply discount directly to service when promotion is configured "By service"
            if ($promo->apply_scope !== 'service') {
                continue;
            }
            if ($promo->status !== 'active') continue;
            if ($promo->start_date && $promo->start_date > $now) continue;
            if ($promo->end_date && $promo->end_date < $now) continue;
            
            // Check usage_limit - CHỈ đếm các PromotionUsage có appointment đã thanh toán
            if ($promo->usage_limit) {
                $totalUsage = \App\Models\PromotionUsage::where('promotion_id', $promo->id)
                    ->whereHas('appointment', function($query) {
                        $query->where('status', 'Đã thanh toán');
                    })
                    ->count();
                if ($totalUsage >= $promo->usage_limit) {
                    continue; // Skip this promotion, use original price
                }
            }
            
            // Check per_user_limit - if user has reached their limit, skip it
            // CHỈ đếm các PromotionUsage có appointment đã thanh toán
            if ($promo->per_user_limit) {
                // Sử dụng userId được truyền vào (từ appointment) hoặc lấy từ item hoặc auth
                $checkUserId = $userId ?? ($item->user_id ?? auth()->id());
                if ($checkUserId) {
                    $userUsage = \App\Models\PromotionUsage::where('promotion_id', $promo->id)
                        ->where('user_id', $checkUserId)
                        ->whereHas('appointment', function($query) {
                            $query->where('status', 'Đã thanh toán');
                        })
                        ->count();
                    if ($userUsage >= $promo->per_user_limit) {
                        continue; // Skip this promotion, use original price
                    }
                }
            }

            $applies = false;

            if ($itemType === 'service') {
                $hasSpecificServices = ($promo->services && $promo->services->count() > 0)
                    || ($promo->combos && $promo->combos->count() > 0)
                    || ($promo->serviceVariants && $promo->serviceVariants->count() > 0);
                $applyToAll = !$hasSpecificServices ||
                    (($promo->services ? $promo->services->count() : 0) +
                     ($promo->combos ? $promo->combos->count() : 0) +
                     ($promo->serviceVariants ? $promo->serviceVariants->count() : 0)) >= 20;
                // Vì đã filter apply_scope === 'service' ở trên, chỉ cần kiểm tra applyToAll hoặc dịch vụ có trong danh sách
                if ($applyToAll) {
                    $applies = true;
                } elseif ($promo->services && $promo->services->contains('id', $item->id)) {
                    $applies = true;
                }
            } elseif ($itemType === 'variant') {
                $hasSpecificServices = ($promo->services && $promo->services->count() > 0)
                    || ($promo->combos && $promo->combos->count() > 0)
                    || ($promo->serviceVariants && $promo->serviceVariants->count() > 0);
                $applyToAll = !$hasSpecificServices ||
                    (($promo->services ? $promo->services->count() : 0) +
                     ($promo->combos ? $promo->combos->count() : 0) +
                     ($promo->serviceVariants ? $promo->serviceVariants->count() : 0)) >= 20;
                // Vì đã filter apply_scope === 'service' ở trên, chỉ cần kiểm tra applyToAll hoặc variant có trong danh sách
                if ($applyToAll) {
                    $applies = true;
                } elseif ($promo->serviceVariants && $promo->serviceVariants->contains('id', $item->id)) {
                    $applies = true;
                } elseif ($item->service_id && $promo->services && $promo->services->contains('id', $item->service_id)) {
                    $applies = true;
                }
            } elseif ($itemType === 'combo') {
                $hasSpecificServices = ($promo->services && $promo->services->count() > 0)
                    || ($promo->combos && $promo->combos->count() > 0)
                    || ($promo->serviceVariants && $promo->serviceVariants->count() > 0);
                $applyToAll = !$hasSpecificServices ||
                    (($promo->services ? $promo->services->count() : 0) +
                     ($promo->combos ? $promo->combos->count() : 0) +
                     ($promo->serviceVariants ? $promo->serviceVariants->count() : 0)) >= 20;
                // Vì đã filter apply_scope === 'service' ở trên, chỉ cần kiểm tra applyToAll hoặc combo có trong danh sách
                if ($applyToAll) {
                    $applies = true;
                } elseif ($promo->combos && $promo->combos->contains('id', $item->id)) {
                    $applies = true;
                }
            }

            if ($applies) {
                // Calculate discount for current promo
                $currentDiscount = 0;

                if ($promo->discount_type === 'percent') {
                    $currentDiscount = ($originalPrice * ($promo->discount_percent ?? 0)) / 100;
                    if ($promo->max_discount_amount) {
                        $currentDiscount = min($currentDiscount, $promo->max_discount_amount);
                    }
                } else {
                    $currentDiscount = min($promo->discount_amount ?? 0, $originalPrice);
                }

                // Prioritize promotion with highest discount amount
                if ($currentDiscount > $discount) {
                    $discount = $currentDiscount;
                    $promotion = $promo;
                }
            }
        }
        
        $finalPrice = max(0, $originalPrice - $discount);

        return [
            'originalPrice' => $originalPrice,
            'discount' => $discount,
            'finalPrice' => $finalPrice > 0 ? $finalPrice : $originalPrice,
            'promotion' => $promotion
        ];
    }
}
