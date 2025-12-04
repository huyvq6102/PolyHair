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

        // Active promotions that can be applied by employee (same rule as checkout)
        $promotions = Promotion::where('status', 'active')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->orderBy('name')
            ->get();

        return view('admin.employee-appointments.create', compact('customers', 'categories', 'services', 'employee', 'promotions'));
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
                    'employee_id' => $employee->id,
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
                    'employee_id' => $employee->id,
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
                'employee_id' => $employee->id,
                'status' => 'Chờ xử lý',
                'start_at' => $startAt,
                'end_at' => $endAt,
                'note' => $validated['note'] ?? null,
            ], $serviceVariantData);

            // If employee selected a promotion code, link it to this appointment & customer
            if (!empty($validated['promotion_code'] ?? null)) {
                $promotion = Promotion::where('code', $validated['promotion_code'])
                    ->where('status', 'active')
                    ->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now())
                    ->first();

                if ($promotion) {
                    PromotionUsage::create([
                        'promotion_id'   => $promotion->id,
                        'user_id'        => $userId,
                        'appointment_id' => $appointment->id,
                        'used_at'        => null, // sẽ được ghi nhận khi thực tế sử dụng/ thanh toán
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
    public function cancel(CancelAppointmentRequest $request, string $id)
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

        $validated = $request->validated();

        $this->appointmentService->cancelAppointment($id, $validated['cancellation_reason']);

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

        // Check if appointment belongs to this employee
        $hasAccess = $appointment->employee_id == $employee->id || 
                     $appointment->appointmentDetails->where('employee_id', $employee->id)->count() > 0;
        
        if (!$hasAccess) {
            return redirect()->route('employee.appointments.index')
                ->with('error', 'Bạn không có quyền xóa đơn đặt này.');
        }

        // Check if appointment can be deleted (only Chờ xử lý or Chờ xác nhận)
        if ($appointment->status !== 'Chờ xử lý' && $appointment->status !== 'Chờ xác nhận') {
            return redirect()->route('employee.appointments.show', $id)
                ->with('error', 'Chỉ có thể xóa đơn đặt chưa được xác nhận.');
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
}

