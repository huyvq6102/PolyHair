<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\AppointmentService;
use App\Services\ServiceService;
use App\Services\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    protected $appointmentService;
    protected $serviceService;
    protected $employeeService;

    public function __construct(
        AppointmentService $appointmentService,
        ServiceService $serviceService,
        EmployeeService $employeeService
    ) {
        $this->appointmentService = $appointmentService;
        $this->serviceService = $serviceService;
        $this->employeeService = $employeeService;
    }

    /**
     * Store a newly created appointment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'employee_id' => 'nullable|exists:employees,id',
            'service_variant_id' => 'required|array',
            'service_variant_id.*' => 'exists:service_variants,id',
            'date' => 'required|date',
            'time' => 'required|string',
            'note' => 'nullable|string|max:1000',
        ]);

        // Check if user is logged in, if not, create or find user by phone
        $user = Auth::user();
        
        if (!$user) {
            // Try to find user by phone or email
            $query = \App\Models\User::where('phone', $validated['phone']);
            if (!empty($validated['email'])) {
                $query->orWhere('email', $validated['email']);
            }
            $user = $query->first();
            
            // If user doesn't exist, create a new one
            if (!$user) {
                // Get default customer role (role_id = 2 or find by name)
                $customerRole = \App\Models\Role::where('name', 'like', '%customer%')
                    ->orWhere('name', 'like', '%khách hàng%')
                    ->orWhere('id', 2)
                    ->first();
                
                $user = \App\Models\User::create([
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'] ?? null,
                    'password' => bcrypt('password'), // Default password, user can change later
                    'role_id' => $customerRole->id ?? 2, // Default role for customers
                ]);
            } else {
                // Update user info if needed
                $user->update([
                    'name' => $validated['name'],
                    'email' => $validated['email'] ?? $user->email,
                ]);
            }
        }

        // Combine date and time
        $startAt = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $validated['date'] . ' ' . $validated['time']);

        // Calculate end time based on service duration
        $totalDuration = 0;
        $serviceVariants = \App\Models\ServiceVariant::whereIn('id', $validated['service_variant_id'])->get();
        foreach ($serviceVariants as $variant) {
            $totalDuration += $variant->duration ?? 60; // Default 60 minutes if no duration
        }
        
        $endAt = $startAt->copy()->addMinutes($totalDuration);

        // Prepare appointment data
        $appointmentData = [
            'user_id' => $user->id,
            'employee_id' => $validated['employee_id'] ?? null,
            'status' => 'Chờ xử lý',
            'start_at' => $startAt,
            'end_at' => $endAt,
            'note' => $validated['note'] ?? null,
        ];

        // Prepare service variant data
        $serviceVariantData = [];
        foreach ($validated['service_variant_id'] as $variantId) {
            $variant = $serviceVariants->firstWhere('id', $variantId);
            if ($variant) {
                $serviceVariantData[] = [
                    'service_variant_id' => $variantId,
                    'employee_id' => $validated['employee_id'] ?? null,
                    'price_snapshot' => $variant->price,
                    'duration' => $variant->duration ?? 60,
                    'status' => 'Chờ',
                ];
            }
        }

        // Create appointment
        $appointment = $this->appointmentService->create($appointmentData, $serviceVariantData);

        return response()->json([
            'success' => true,
            'message' => 'Đặt lịch hẹn thành công! Chúng tôi sẽ liên hệ với bạn sớm nhất.',
            'appointment_id' => $appointment->id,
        ]);
    }
}

