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
     * Store a new appointment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'service_variants' => 'required|array|min:1',
            'service_variants.*' => 'exists:service_variants,id',
            'employee_id' => 'nullable|exists:employees,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'word_time_id' => 'required|exists:word_time,id',
            'note' => 'nullable|string|max:1000',
        ]);

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
            
            // Calculate total duration from selected service variants
            $totalDuration = 0;
            $serviceVariantData = [];
            
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
            
            $endAt = $startAt->copy()->addMinutes($totalDuration);

            // Create appointment
            $appointment = $this->appointmentService->create([
                'user_id' => $user->id,
                'employee_id' => $validated['employee_id'] ?? null,
                'status' => 'Chờ xử lý',
                'start_at' => $startAt,
                'end_at' => $endAt,
                'note' => $validated['note'] ?? null,
            ], $serviceVariantData);

            // Add appointment to cart
            $cart = Session::get('cart', []);
            $cartKey = 'appointment_' . $appointment->id;
            $cart[$cartKey] = [
                'type' => 'appointment',
                'id' => $appointment->id,
                'quantity' => 1,
            ];
            Session::put('cart', $cart);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đặt lịch thành công! Đã thêm vào lịch đặt.',
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
        $appointmentDate = Carbon::parse($request->input('appointment_date'));

        // Generate time slots from 7:00 to 22:00, every 30 minutes
        $timeSlots = [];
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
            
            $timeSlots[] = [
                'time' => $timeString,
                'display' => $timeString,
                'word_time_id' => $wordTime->id,
            ];
            $currentTime->addMinutes(30);
        }

        // Initialize all slots as available
        foreach ($timeSlots as &$slot) {
            $slot['available'] = true;
        }

        // Check all booked appointments for the selected date
        // This includes appointments of all employees to prevent double booking
        $bookedAppointments = \App\Models\Appointment::whereDate('start_at', $appointmentDate->format('Y-m-d'))
            ->whereIn('status', ['Chờ xử lý', 'Đã xác nhận', 'Đang thực hiện'])
            ->get();

        // Mark booked time slots as unavailable
        // Check if any appointment's start time matches the slot time
        foreach ($bookedAppointments as $appointment) {
            if (!$appointment->start_at) {
                continue;
            }
            
            $appointmentStart = Carbon::parse($appointment->start_at);
            $appointmentStartTime = $appointmentStart->format('H:i');
            
            // Mark the exact time slot as unavailable
            foreach ($timeSlots as &$slot) {
                if ($slot['time'] === $appointmentStartTime) {
                    $slot['available'] = false;
                }
            }
        }
        
        // If employee is selected, also check specifically for that employee's appointments
        // This ensures we don't show slots that are already booked for the selected employee
        if ($employeeId) {
            $employeeAppointments = \App\Models\Appointment::where('employee_id', $employeeId)
                ->whereDate('start_at', $appointmentDate->format('Y-m-d'))
                ->whereIn('status', ['Chờ xử lý', 'Đã xác nhận', 'Đang thực hiện'])
                ->get();
            
            foreach ($employeeAppointments as $appointment) {
                if (!$appointment->start_at) {
                    continue;
                }
                
                $appointmentStart = Carbon::parse($appointment->start_at);
                $appointmentStartTime = $appointmentStart->format('H:i');
                
                // Mark the exact time slot as unavailable for this employee
                foreach ($timeSlots as &$slot) {
                    if ($slot['time'] === $appointmentStartTime) {
                        $slot['available'] = false;
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'time_slots' => $timeSlots,
        ]);
    }
}
