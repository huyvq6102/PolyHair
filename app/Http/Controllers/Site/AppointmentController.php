<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\AppointmentService;
use App\Services\ServiceService;
use App\Services\EmployeeService;
use App\Services\WordTimeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppointmentConfirmationMail;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    protected $appointmentService;
    protected $serviceService;
    protected $employeeService;
    protected $wordTimeService;

    public function __construct(
        AppointmentService $appointmentService,
        ServiceService $serviceService,
        EmployeeService $employeeService,
        WordTimeService $wordTimeService
    ) {
        $this->appointmentService = $appointmentService;
        $this->serviceService = $serviceService;
        $this->employeeService = $employeeService;
        $this->wordTimeService = $wordTimeService;
    }

    /**
     * Show the service selection page.
     */
    public function selectServices()
    {
        // Lấy tất cả danh mục có dịch vụ, sắp xếp theo bảng chữ cái
        $categories = \App\Models\ServiceCategory::with(['services' => function($query) {
                $query->whereNull('deleted_at')
                    ->where('status', 'Hoạt động')
                    ->with('serviceVariants')
                    ->orderBy('name', 'asc'); // Sắp xếp dịch vụ theo bảng chữ cái
            }])
            ->whereHas('services', function($query) {
                $query->whereNull('deleted_at')
                    ->where('status', 'Hoạt động');
            })
            ->orderBy('name', 'asc') // Sắp xếp danh mục theo bảng chữ cái
            ->get();

        // Lấy tất cả combo, sắp xếp theo bảng chữ cái
        $combos = \App\Models\Combo::with('comboItems.serviceVariant')
            ->whereNull('deleted_at')
            ->where('status', 'Hoạt động')
            ->orderBy('name', 'asc')
            ->get();

        return view('site.appointment.select-services', compact('categories', 'combos'));
    }

    /**
     * Show the appointment booking form page.
     */
    public function create(Request $request)
    {
        // Xử lý xóa dịch vụ
        $queryParams = $request->all();
        
        if ($request->has('remove_service_id')) {
            $removeId = $request->input('remove_service_id');
            $serviceIds = is_array($request->input('service_id')) ? $request->input('service_id') : ($request->input('service_id') ? [$request->input('service_id')] : []);
            $serviceIds = array_filter($serviceIds, function($id) use ($removeId) {
                return $id != $removeId;
            });
            unset($queryParams['remove_service_id']);
            $queryParams['service_id'] = array_values($serviceIds);
            if (empty($queryParams['service_id'])) {
                unset($queryParams['service_id']);
            }
            return redirect()->route('site.appointment.create', $queryParams);
        }
        
        if ($request->has('remove_variant_id')) {
            $removeId = $request->input('remove_variant_id');
            $variantIds = is_array($request->input('service_variants')) ? $request->input('service_variants') : ($request->input('service_variants') ? [$request->input('service_variants')] : []);
            $variantIds = array_filter($variantIds, function($id) use ($removeId) {
                return $id != $removeId;
            });
            unset($queryParams['remove_variant_id']);
            $queryParams['service_variants'] = array_values($variantIds);
            if (empty($queryParams['service_variants'])) {
                unset($queryParams['service_variants']);
            }
            return redirect()->route('site.appointment.create', $queryParams);
        }
        
        if ($request->has('remove_combo_id')) {
            $removeId = $request->input('remove_combo_id');
            $comboIds = is_array($request->input('combo_id')) ? $request->input('combo_id') : ($request->input('combo_id') ? [$request->input('combo_id')] : []);
            $comboIds = array_filter($comboIds, function($id) use ($removeId) {
                return $id != $removeId;
            });
            unset($queryParams['remove_combo_id']);
            $queryParams['combo_id'] = array_values($comboIds);
            if (empty($queryParams['combo_id'])) {
                unset($queryParams['combo_id']);
            }
            return redirect()->route('site.appointment.create', $queryParams);
        }
        
        // Lấy tất cả nhân viên từ database
        $allEmployees = \App\Models\Employee::with(['user.role', 'services'])
            ->whereNotNull('user_id')
            ->orderBy('id', 'desc')
            ->get();
        
        // Lọc nhân viên: loại trừ admin và nhân viên bị vô hiệu hóa
        $employees = $allEmployees->filter(function($employee) {
            // Bỏ qua nếu không có user
            if (!$employee->user) {
                return false;
            }
            
            // Loại trừ admin - kiểm tra role_id
            if ($employee->user->role_id == 1) {
                return false;
            }
            
            // Kiểm tra role name nếu có
            if ($employee->user->role) {
                $roleName = strtolower(trim($employee->user->role->name ?? ''));
                if (in_array($roleName, ['admin', 'administrator'])) {
                    return false;
                }
            }
            
            // Loại trừ nhân viên bị vô hiệu hóa
            if ($employee->status === 'Vô hiệu hóa') {
                return false;
            }
            
            return true;
        });

        // Filter employees by service expertise - chỉ hiển thị nhân viên có chuyên môn phù hợp
        $serviceIds = $request->input('service_id', []);
        $variantIds = $request->input('service_variants', []);
        $comboIds = $request->input('combo_id', []);
        
        // Chuyển đổi thành array nếu là single value
        if (!is_array($serviceIds)) {
            $serviceIds = $serviceIds ? [$serviceIds] : [];
        }
        if (!is_array($variantIds)) {
            $variantIds = $variantIds ? [$variantIds] : [];
        }
        if (!is_array($comboIds)) {
            $comboIds = $comboIds ? [$comboIds] : [];
        }
        
        // Thu thập tất cả service IDs từ các nguồn
        $allServiceIds = [];
        
        // Lấy service IDs từ service_id
        if (!empty($serviceIds)) {
            $allServiceIds = array_merge($allServiceIds, $serviceIds);
        }
        
        // Lấy service IDs từ service_variants
        if (!empty($variantIds)) {
            $variants = \App\Models\ServiceVariant::whereIn('id', $variantIds)->get();
            $variantServiceIds = $variants->pluck('service_id')->unique()->toArray();
            $allServiceIds = array_merge($allServiceIds, $variantServiceIds);
        }
        
        // Lấy service IDs từ combo
        if (!empty($comboIds)) {
            $combos = \App\Models\Combo::with('comboItems.serviceVariant.service')
                ->whereIn('id', $comboIds)
                ->get();
            
            foreach ($combos as $combo) {
                if ($combo && $combo->comboItems) {
                    foreach ($combo->comboItems as $item) {
                        if ($item->serviceVariant && $item->serviceVariant->service) {
                            $allServiceIds[] = $item->serviceVariant->service->id;
                        }
                    }
                }
            }
        }
        
        // Loại bỏ trùng lặp
        $allServiceIds = array_unique($allServiceIds);
        
        // Chỉ lọc nhân viên nếu có dịch vụ được chọn
        if (!empty($allServiceIds)) {
            // Lọc nhân viên có chuyên môn với ít nhất một service trong danh sách
            $employees = $employees->filter(function($employee) use ($allServiceIds) {
                return $employee->services->whereIn('id', $allServiceIds)->count() > 0;
            });
        } else {
            // Nếu không có dịch vụ nào được chọn, không hiển thị nhân viên nào
            $employees = collect([]);
        }

        $employees = $employees->values();
        
        $wordTimes = $this->wordTimeService->getAll();
        $serviceCategories = \App\Models\ServiceCategory::whereNull('deleted_at')
            ->orderBy('name')
            ->get();
        
        // Lấy các combo từ bảng combos
        $combos = \App\Models\Combo::with('comboItems.serviceVariant')
            ->whereNull('deleted_at')
            ->where('status', 'Hoạt động')
            ->orderBy('name')
            ->get();
        
        return view('site.appointment.create', compact('employees', 'wordTimes', 'serviceCategories', 'combos'));
    }

    /**
     * Store a new appointment.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'email' => 'nullable|email|max:255',
                'service_variants' => 'nullable|array',
                'service_variants.*' => 'exists:service_variants,id',
                'service_id' => 'nullable|array',
                'service_id.*' => 'exists:services,id',
                'combo_id' => 'nullable|array',
                'combo_id.*' => 'exists:combos,id',
                'employee_id' => 'required|exists:employees,id',
                'appointment_date' => 'required|date|after_or_equal:today',
                'word_time_id' => 'required|exists:word_time,id',
                'note' => 'nullable|string|max:1000',
            ], [
                'name.required' => 'Vui lòng nhập họ và tên',
                'phone.required' => 'Vui lòng nhập số điện thoại',
                'employee_id.required' => 'Vui lòng chọn kỹ thuật viên',
                'appointment_date.required' => 'Vui lòng chọn ngày đặt lịch',
                'word_time_id.required' => 'Vui lòng chọn giờ đặt lịch',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Always return JSON errors (form always submits via AJAX)
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        }
        
        // Validate that at least one service is selected
        if (empty($validated['service_id']) && empty($validated['service_variants']) && empty($validated['combo_id'])) {
            // Always return JSON errors (form always submits via AJAX)
            return response()->json([
                'success' => false,
                'errors' => ['service' => ['Vui lòng chọn ít nhất một dịch vụ hoặc combo']]
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Get or create user
            $user = null;
            if (Auth::check()) {
                $user = Auth::user();
            } else {
                // Create guest user or find existing by phone/email
                $user = \App\Models\User::where('phone', $validated['phone'])
                    ->orWhere('email', $validated['email'])
                    ->first();

                if (!$user) {
                    $user = \App\Models\User::create([
                        'name' => $validated['name'],
                        'phone' => $validated['phone'],
                        'email' => $validated['email'] ?? null,
                        'password' => bcrypt('guest123'), // Temporary password
                    ]);
                } else {
                    // Update user info if needed
                    $user->update([
                        'name' => $validated['name'],
                        'email' => $validated['email'] ?? $user->email,
                    ]);
                }
            }

            // Get word time
            $wordTime = $this->wordTimeService->getOne($validated['word_time_id']);
            
            // Calculate start and end time
            $appointmentDate = Carbon::parse($validated['appointment_date']);
            $timeString = $wordTime->formatted_time; // Use formatted_time to ensure H:i format
            $startAt = Carbon::parse($appointmentDate->format('Y-m-d') . ' ' . $timeString);
            
            // Calculate total duration from selected service variants, service, or combo
            $totalDuration = 0;
            $serviceVariantData = [];
            
            // Process service variants if selected (priority: variants over service/combo)
            if (!empty($validated['service_variants'])) {
                foreach ($validated['service_variants'] as $variantId) {
                    $variant = \App\Models\ServiceVariant::findOrFail($variantId);
                    $totalDuration += $variant->duration ?? 60; // Default 60 minutes if not set
                    
                    $serviceVariantData[] = [
                        'service_variant_id' => $variantId,
                        'employee_id' => $validated['employee_id'] ?? null,
                        'price_snapshot' => $variant->price,
                        'duration' => $variant->duration ?? 60,
                        'status' => 'Chờ',
                    ];
                }
            }
            
            // Process combos if selected
            if (!empty($validated['combo_id'])) {
                $comboIds = is_array($validated['combo_id']) ? $validated['combo_id'] : [$validated['combo_id']];
                foreach ($comboIds as $comboId) {
                    $combo = \App\Models\Combo::with('comboItems.serviceVariant')->findOrFail($comboId);
                    
                    // Calculate duration from combo items
                    $comboDuration = 60; // Default
                    if ($combo->comboItems && $combo->comboItems->count() > 0) {
                        $comboDuration = $combo->comboItems->sum(function($item) {
                            return $item->serviceVariant->duration ?? 60;
                        });
                    }
                    $totalDuration += $comboDuration;
                    
                    $serviceVariantData[] = [
                        'service_variant_id' => null,
                        'combo_id' => $combo->id,
                        'employee_id' => $validated['employee_id'] ?? null,
                        'price_snapshot' => $combo->price ?? 0,
                        'duration' => $comboDuration,
                        'status' => 'Chờ',
                        'notes' => $combo->name, // Store combo name in notes for display
                    ];
                }
            }
            
            // Process services if selected
            if (!empty($validated['service_id'])) {
                $serviceIds = is_array($validated['service_id']) ? $validated['service_id'] : [$validated['service_id']];
                foreach ($serviceIds as $serviceId) {
                    $service = \App\Models\Service::findOrFail($serviceId);
                    $totalDuration += $service->base_duration ?? 60; // Default 60 minutes if not set
                    
                    $serviceVariantData[] = [
                        'service_variant_id' => null, // No variant selected
                        'employee_id' => $validated['employee_id'] ?? null,
                        'price_snapshot' => $service->base_price ?? 0,
                        'duration' => $service->base_duration ?? 60,
                        'status' => 'Chờ',
                        'notes' => $service->name, // Store service name in notes for display
                    ];
                }
            }
            
            if (empty($serviceVariantData)) {
                // No service selected - use default duration
                $totalDuration = 60; // Default 60 minutes
            }
            
            $endAt = $startAt->copy()->addMinutes($totalDuration);

            // Check for duplicate appointment (same user, same date, same time)
            $existingAppointment = \App\Models\Appointment::where('user_id', $user->id)
                ->where('start_at', $startAt)
                ->where('employee_id', $validated['employee_id'] ?? null)
                ->where('status', '!=', 'Đã hủy')
                ->first();
            
            if ($existingAppointment) {
                // Appointment already exists, use it instead of creating new one
                $appointment = $existingAppointment;
            } else {
                // Create appointment
                $appointment = $this->appointmentService->create([
                'user_id' => $user->id,
                'employee_id' => $validated['employee_id'] ?? null,
                'status' => 'Chờ xử lý',
                'start_at' => $startAt,
                'end_at' => $endAt,
                'note' => $validated['note'] ?? null,
                ], $serviceVariantData);
            }

            // Add appointment to cart (check if already exists to prevent duplicates)
            $cart = Session::get('cart', []);
            $cartKey = 'appointment_' . $appointment->id;
            
            // Only add if not already in cart
            if (!isset($cart[$cartKey])) {
                $cart[$cartKey] = [
                    'type' => 'appointment',
                    'id' => $appointment->id,
                    'quantity' => 1,
                ];
                Session::put('cart', $cart);
            }

            DB::commit();

            // Gửi email xác nhận đặt lịch - chỉ gửi một lần cho mỗi appointment
            // Sử dụng cache để đảm bảo chỉ gửi email một lần
            $emailSentKey = 'appointment_email_sent_' . $appointment->id;
            
            // Kiểm tra xem đã gửi email cho appointment này chưa
            if (!\Cache::has($emailSentKey)) {
                // Lấy email từ form (ưu tiên email trong form, nếu không có thì dùng email của user)
                $emailToSend = !empty($validated['email']) ? trim($validated['email']) : (trim($user->email ?? ''));
                
                // Đảm bảo email hợp lệ
                if (!empty($emailToSend) && filter_var($emailToSend, FILTER_VALIDATE_EMAIL)) {
                    try {
                        // Load đầy đủ relationships cho email
                        $appointment->load([
                            'user',
                            'employee.user',
                            'appointmentDetails.serviceVariant.service',
                            'appointmentDetails.combo'
                        ]);
                        
                        // Đánh dấu đã gửi email (cache trong 5 phút để tránh gửi lại)
                        \Cache::put($emailSentKey, true, 300);
                        
                        // Gửi email đến địa chỉ email trong form
                        Mail::to($emailToSend)->send(new AppointmentConfirmationMail($appointment));
                        
                        \Log::info('Appointment confirmation email sent successfully', [
                            'to' => $emailToSend,
                            'appointment_id' => $appointment->id,
                            'mailer' => config('mail.default'),
                            'mail_host' => config('mail.mailers.smtp.host'),
                            'mail_port' => config('mail.mailers.smtp.port'),
                            'from_address' => config('mail.from.address'),
                        ]);
                    } catch (\Swift_TransportException $e) {
                        // Lỗi kết nối SMTP
                        \Log::error('SMTP connection error when sending appointment confirmation email', [
                            'email_to' => $emailToSend,
                            'appointment_id' => $appointment->id,
                            'error' => $e->getMessage(),
                            'mailer' => config('mail.default'),
                            'mail_host' => config('mail.mailers.smtp.host'),
                            'mail_port' => config('mail.mailers.smtp.port'),
                        ]);
                    } catch (\Exception $e) {
                        // Log lỗi chi tiết nhưng không làm gián đoạn quá trình đặt lịch
                        \Log::error('Failed to send appointment confirmation email', [
                            'email_to' => $emailToSend,
                            'form_email' => $validated['email'] ?? 'N/A',
                            'user_email' => $user->email ?? 'N/A',
                            'appointment_id' => $appointment->id,
                            'error' => $e->getMessage(),
                            'error_class' => get_class($e),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                } else {
                    \Log::warning('Cannot send appointment confirmation email: Invalid or missing email address', [
                        'form_email' => $validated['email'] ?? 'N/A',
                        'user_email' => $user->email ?? 'N/A',
                        'email_to_send' => $emailToSend ?? 'N/A',
                        'appointment_id' => $appointment->id,
                    ]);
                }
            } else {
                // Email đã được gửi cho appointment này rồi, bỏ qua
                \Log::info('Appointment confirmation email already sent, skipping', [
                    'appointment_id' => $appointment->id,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => '<i class="fa fa-check-circle"></i> Đặt lịch thành công! Lịch hẹn của bạn đã được thêm vào giỏ hàng. Vui lòng thanh toán để hoàn tất đặt lịch.',
                'appointment_id' => $appointment->id,
                'redirect_url' => route('site.cart.index'),
                'cart_count' => count($cart),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi đặt lịch. Vui lòng thử lại.',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Show appointment detail page.
     */
    public function show($id)
    {
        $appointment = \App\Models\Appointment::with([
            'user', 
            'employee.user', 
            'appointmentDetails.serviceVariant.service',
            'payments'
        ])->findOrFail($id);
        
        // Calculate total price
        $totalPrice = 0;
        foreach ($appointment->appointmentDetails as $detail) {
            $totalPrice += $detail->price_snapshot ?? 0;
        }
        
        return view('site.appointment.show', compact('appointment', 'totalPrice'));
    }

    /**
     * Show success page after booking.
     */
    public function success($id)
    {
        $appointment = \App\Models\Appointment::with([
            'user', 
            'employee.user', 
            'appointmentDetails.serviceVariant.service'
        ])->findOrFail($id);
        
        return view('site.appointment.success', compact('appointment'));
    }

    /**
     * Get available time slots for an employee on a specific date.
     */
    public function getAvailableTimeSlots(Request $request)
    {
        try {
            // Handle POST request for getting word_time_id
            if ($request->isMethod('post') && $request->input('action') === 'get_or_create_word_time') {
                $time = $request->input('time');
                if (!$time) {
                    return response()->json(['success' => false, 'message' => 'Time is required'], 400);
                }
                
                // Find or create word_time record
                $wordTime = \App\Models\WordTime::firstOrCreate(
                    ['time' => $time],
                    ['time' => $time]
                );
                
                return response()->json([
                    'success' => true,
                    'word_time_id' => $wordTime->id,
                ]);
            }
            
            // Handle GET request for available time slots
            $request->validate([
                'employee_id' => 'nullable|exists:employees,id',
                'appointment_date' => 'required|date|after_or_equal:today',
            ]);

            $employeeId = $request->input('employee_id');
            // Convert empty string to null
            if ($employeeId === '' || $employeeId === '0') {
                $employeeId = null;
            }
            $appointmentDate = Carbon::parse($request->input('appointment_date'));
            
            // Lấy giờ hiện tại theo timezone Việt Nam
            $now = Carbon::now('Asia/Ho_Chi_Minh');
            $isToday = $appointmentDate->format('Y-m-d') === $now->format('Y-m-d');
            $currentHour = (int)$now->format('H');
            $currentMinute = (int)$now->format('i');
            // Làm tròn lên đến 30 phút tiếp theo
            $currentSlotMinute = $currentMinute < 30 ? 30 : 0;
            if ($currentSlotMinute === 0) {
                $currentHour = $currentHour + 1;
            }

            $timeSlots = [];
            $workingTimeRanges = [];
            
            // Bắt buộc phải có employee_id
            if (!$employeeId) {
                return response()->json([
                    'success' => true,
                    'time_slots' => [],
                    'message' => 'Vui lòng chọn kỹ thuật viên trước'
                ]);
            }
            
            // Get working schedules for the employee on the selected date
            // Status phải là 'approved' (đã được duyệt) để hiển thị lịch làm việc
            $workingSchedules = \App\Models\WorkingSchedule::with('shift')
                ->where('employee_id', $employeeId)
                ->whereDate('work_date', $appointmentDate->format('Y-m-d'))
                ->where('status', 'approved')
                ->whereNull('deleted_at') // Loại bỏ các bản ghi đã bị xóa mềm
                ->get();

            // Lưu các khoảng thời gian làm việc
            foreach ($workingSchedules as $schedule) {
                if (!$schedule->shift) {
                    continue;
                }

                $startTimeString = $schedule->shift->formatted_start_time;
                $endTimeString = $schedule->shift->formatted_end_time;
                
                if (!$startTimeString || !$endTimeString) {
                    continue;
                }
                
                try {
                    $shiftStart = Carbon::createFromFormat('H:i', $startTimeString);
                    $shiftEnd = Carbon::createFromFormat('H:i', $endTimeString);
                } catch (\Exception $e) {
                    $shiftStart = Carbon::parse($startTimeString);
                    $shiftEnd = Carbon::parse($endTimeString);
                }
                
                $workingTimeRanges[] = [
                    'start' => $shiftStart,
                    'end' => $shiftEnd
                ];
            }
            
            // Nếu không có lịch làm việc, vẫn hiển thị tất cả slots nhưng tất cả đều unavailable
            // (workingTimeRanges sẽ rỗng, nên isInWorkingTime sẽ luôn false)

            // Get booked appointments for this employee on this date
            $bookedAppointments = \App\Models\Appointment::where('employee_id', $employeeId)
                ->whereDate('start_at', $appointmentDate->format('Y-m-d'))
                ->whereIn('status', ['Chờ xử lý', 'Đã xác nhận', 'Đang thực hiện'])
                ->get();
            
            $bookedTimes = [];
            foreach ($bookedAppointments as $appointment) {
                if ($appointment->start_at) {
                    $appointmentStart = Carbon::parse($appointment->start_at);
                    $bookedTimes[] = $appointmentStart->format('H:i');
                }
            }
            
            // Tạo TẤT CẢ time slots từ 7:00 đến 22:00 (mỗi 30 phút)
            $startTime = Carbon::parse('07:00');
            $endTime = Carbon::parse('22:00');
            $currentTime = $startTime->copy();
            
            while ($currentTime->lte($endTime)) {
                $timeString = $currentTime->format('H:i');
                
                // Find or create word_time for this time slot
                $wordTime = \App\Models\WordTime::firstOrCreate(
                    ['time' => $timeString],
                    ['time' => $timeString]
                );
                
                // Kiểm tra xem slot có nằm trong khung giờ làm việc không
                $isInWorkingTime = false;
                foreach ($workingTimeRanges as $range) {
                    $slotTime = Carbon::createFromFormat('H:i', $timeString);
                    // Kiểm tra slot có nằm trong khoảng [start, end) không
                    if ($slotTime->gte($range['start']) && $slotTime->lt($range['end'])) {
                        $isInWorkingTime = true;
                        break;
                    }
                }
                
                // Kiểm tra xem slot có bị đặt chưa
                $isBooked = in_array($timeString, $bookedTimes);
                
                // Kiểm tra xem slot có trước giờ hiện tại không (nếu là ngày hôm nay)
                $isPastTime = false;
                if ($isToday) {
                    $slotHour = (int)substr($timeString, 0, 2);
                    $slotMinute = (int)substr($timeString, 3, 2);
                    
                    if ($slotHour < $currentHour || ($slotHour === $currentHour && $slotMinute < $currentSlotMinute)) {
                        $isPastTime = true;
                    }
                }
                
                // Slot chỉ available nếu: nằm trong khung giờ làm việc VÀ chưa bị đặt VÀ không phải quá khứ
                $isAvailable = $isInWorkingTime && !$isBooked && !$isPastTime;
                
                $timeSlots[] = [
                    'time' => $timeString,
                    'display' => $timeString,
                    'word_time_id' => $wordTime->id,
                    'available' => $isAvailable,
                ];
                
                $currentTime->addMinutes(30);
            }

            return response()->json([
                'success' => true,
                'time_slots' => $timeSlots,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error in getAvailableTimeSlots: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải khung giờ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get services by category ID (AJAX).
     */
    public function getServicesByCategory(Request $request)
    {
        try {
            $request->validate([
                'category_id' => 'required|exists:service_categories,id',
            ]);

            $categoryId = $request->input('category_id');
            $services = \App\Models\Service::with('serviceVariants')
                ->where('category_id', $categoryId)
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'services' => $services->map(function($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                        'base_price' => $service->base_price ?? 0,
                        'base_duration' => $service->base_duration ?? 60,
                        'variants' => $service->serviceVariants->map(function($variant) {
                            return [
                                'id' => $variant->id,
                                'name' => $variant->name,
                                'price' => $variant->price,
                                'duration' => $variant->duration,
                            ];
                        }),
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get employees by service (chuyên môn).
     */
    public function getEmployeesByService(Request $request)
    {
        try {
            // Lấy tất cả nhân viên từ database
            $allEmployees = \App\Models\Employee::with(['user.role', 'services'])
                ->whereNotNull('user_id')
                ->orderBy('id', 'desc')
                ->get();
            
            // Lọc nhân viên: loại trừ admin và nhân viên bị vô hiệu hóa
            $employees = $allEmployees->filter(function($employee) {
                // Bỏ qua nếu không có user
                if (!$employee->user) {
                    return false;
                }
                
                // Loại trừ admin - kiểm tra role_id
                if ($employee->user->role_id == 1) {
                    return false;
                }
                
                // Kiểm tra role name nếu có
                if ($employee->user->role) {
                    $roleName = strtolower(trim($employee->user->role->name ?? ''));
                    if (in_array($roleName, ['admin', 'administrator'])) {
                        return false;
                    }
                }
                
                // Loại trừ nhân viên bị vô hiệu hóa
                if ($employee->status === 'Vô hiệu hóa') {
                    return false;
                }
                
                return true;
            });

            // Filter by service expertise - chỉ hiển thị nhân viên có chuyên môn phù hợp
            $serviceIds = $request->input('service_id', []);
            $variantIds = $request->input('service_variants', []);
            $comboIds = $request->input('combo_id', []);
            
            // Chuyển đổi thành array nếu là single value
            if (!is_array($serviceIds)) {
                $serviceIds = $serviceIds ? [$serviceIds] : [];
            }
            if (!is_array($variantIds)) {
                $variantIds = $variantIds ? [$variantIds] : [];
            }
            if (!is_array($comboIds)) {
                $comboIds = $comboIds ? [$comboIds] : [];
            }
            
            // Thu thập tất cả service IDs từ các nguồn
            $allServiceIds = [];
            
            // Lấy service IDs từ service_id
            if (!empty($serviceIds)) {
                $allServiceIds = array_merge($allServiceIds, $serviceIds);
            }
            
            // Lấy service IDs từ service_variants
            if (!empty($variantIds)) {
                $variants = \App\Models\ServiceVariant::whereIn('id', $variantIds)->get();
                $variantServiceIds = $variants->pluck('service_id')->unique()->toArray();
                $allServiceIds = array_merge($allServiceIds, $variantServiceIds);
            }
            
            // Lấy service IDs từ combo
            if (!empty($comboIds)) {
                $combos = \App\Models\Combo::with('comboItems.serviceVariant.service')
                    ->whereIn('id', $comboIds)
                    ->get();
                
                foreach ($combos as $combo) {
                    if ($combo && $combo->comboItems) {
                        foreach ($combo->comboItems as $item) {
                            if ($item->serviceVariant && $item->serviceVariant->service) {
                                $allServiceIds[] = $item->serviceVariant->service->id;
                            }
                        }
                    }
                }
            }
            
            // Loại bỏ trùng lặp
            $allServiceIds = array_unique($allServiceIds);
            
            // Chỉ lọc nhân viên nếu có dịch vụ được chọn
            if (!empty($allServiceIds)) {
                // Lọc nhân viên có chuyên môn với ít nhất một service trong danh sách
                $employees = $employees->filter(function($employee) use ($allServiceIds) {
                    return $employee->services->whereIn('id', $allServiceIds)->count() > 0;
                });
            } else {
                // Nếu không có dịch vụ nào được chọn, không hiển thị nhân viên nào
                $employees = collect([]);
            }

            $employees = $employees->values();

            return response()->json([
                'success' => true,
                'employees' => $employees->map(function($employee) {
                    return [
                        'id' => $employee->id,
                        'name' => $employee->user->name,
                        'position' => $employee->position,
                        'level' => $employee->level,
                        'avatar' => $employee->avatar,
                        'display_name' => $employee->user->name . 
                            ($employee->position ? ' - ' . $employee->position : '') . 
                            ($employee->level ? ' (' . $employee->level . ')' : ''),
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }
}
