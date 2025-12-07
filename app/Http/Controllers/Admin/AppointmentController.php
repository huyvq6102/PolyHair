<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AppointmentService;
use App\Services\EmployeeService;
use App\Services\ServiceService;
use App\Models\User;
use App\Models\ServiceVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

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
        $filters = [
            'customer_name' => $request->get('customer_name'),
            'phone' => $request->get('phone'),
            'email' => $request->get('email'),
            'employee_name' => $request->get('employee_name'),
            'service' => $request->get('service'),
        ];

        $appointments = $this->appointmentService->getAllWithFilters($filters);

        return view('admin.appointments.index', compact('appointments', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = $this->employeeService->getAll();
        
        // Lấy dịch vụ đơn (không có biến thể)
        $singleServices = \App\Models\Service::whereNull('deleted_at')
            ->whereDoesntHave('serviceVariants')
            ->get();
        
        // Lấy dịch vụ có biến thể
        $variantServices = \App\Models\Service::whereNull('deleted_at')
            ->whereHas('serviceVariants')
            ->with('serviceVariants')
            ->get();
        
        // Lấy combo
        $combos = \App\Models\Combo::whereNull('deleted_at')
            ->with('comboItems')
            ->get();
        
        return view('admin.appointments.create', compact('employees', 'singleServices', 'variantServices', 'combos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'employee_id' => 'nullable|exists:employees,id',
            'service_type' => 'required|in:single,variant,combo',
            'service_id' => 'nullable|exists:services,id',
            'service_variant_id' => 'nullable|exists:service_variants,id',
            'combo_id' => 'nullable|exists:combos,id',
            'status' => 'required|in:Chờ xử lý,Đã xác nhận,Đang thực hiện,Hoàn thành,Chưa thanh toán,Đã thanh toán',
            'note' => 'nullable|string',
            'appointment_date' => 'nullable|date',
            'appointment_time' => 'nullable|string',
        ]);

        // Validate that at least one service is selected based on service_type
        if ($validated['service_type'] === 'single' && empty($validated['service_id'])) {
            return redirect()->back()->withErrors(['service_id' => 'Vui lòng chọn dịch vụ đơn'])->withInput();
        }
        if ($validated['service_type'] === 'variant' && empty($validated['service_variant_id'])) {
            return redirect()->back()->withErrors(['service_variant_id' => 'Vui lòng chọn dịch vụ biến thể'])->withInput();
        }
        if ($validated['service_type'] === 'combo' && empty($validated['combo_id'])) {
            return redirect()->back()->withErrors(['combo_id' => 'Vui lòng chọn combo'])->withInput();
        }

        // Get or create user
        $user = User::where('phone', $validated['phone'])
            ->orWhere(function($q) use ($validated) {
                if (!empty($validated['email'])) {
                    $q->where('email', $validated['email']);
                }
            })
            ->first();

        if (!$user) {
            $user = User::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'password' => Hash::make('guest123'),
                'status' => 'Hoạt động',
            ]);
        } else {
            // Update user info if needed
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? $user->email,
            ]);
        }

        // Prepare appointment data
        $appointmentData = [
            'user_id' => $user->id,
            'employee_id' => $validated['employee_id'] ?? null,
            'status' => $validated['status'],
            'note' => $validated['note'] ?? null,
        ];

        // Prepare service data based on type
        $serviceVariantData = [];
        $duration = 0;
        
        if ($validated['service_type'] === 'single' && !empty($validated['service_id'])) {
            $service = \App\Models\Service::find($validated['service_id']);
            if ($service) {
                $serviceVariantData[] = [
                    'service_variant_id' => null,
                    'combo_id' => null,
                    'employee_id' => $validated['employee_id'] ?? null,
                    'price_snapshot' => $service->base_price ?? 0,
                    'duration' => $service->base_duration ?? 0,
                    'status' => 'Chờ',
                    'notes' => $service->name, // Store service name in notes
                ];
                $duration = $service->base_duration ?? 0;
            }
        } elseif ($validated['service_type'] === 'variant' && !empty($validated['service_variant_id'])) {
            $serviceVariant = ServiceVariant::find($validated['service_variant_id']);
            if ($serviceVariant) {
                $serviceVariantData[] = [
                    'service_variant_id' => $serviceVariant->id,
                    'combo_id' => null,
                    'employee_id' => $validated['employee_id'] ?? null,
                    'price_snapshot' => $serviceVariant->price,
                    'duration' => $serviceVariant->duration,
                    'status' => 'Chờ',
                ];
                $duration = $serviceVariant->duration ?? 0;
            }
        } elseif ($validated['service_type'] === 'combo' && !empty($validated['combo_id'])) {
            $combo = \App\Models\Combo::find($validated['combo_id']);
            if ($combo) {
                // Calculate total duration from combo items
                $totalDuration = 0;
                foreach ($combo->comboItems as $item) {
                    if ($item->serviceVariant) {
                        $totalDuration += $item->serviceVariant->duration ?? 0;
                    } elseif ($item->service) {
                        $totalDuration += $item->service->base_duration ?? 0;
                    }
                }
                
                $serviceVariantData[] = [
                    'service_variant_id' => null,
                    'combo_id' => $combo->id,
                    'employee_id' => $validated['employee_id'] ?? null,
                    'price_snapshot' => $combo->price,
                    'duration' => $totalDuration,
                    'status' => 'Chờ',
                    'notes' => $combo->name, // Store combo name in notes
                ];
                $duration = $totalDuration;
            }
        }

        // Set start_at and end_at if date and time provided
        if (!empty($validated['appointment_date']) && !empty($validated['appointment_time'])) {
            $startAt = Carbon::parse($validated['appointment_date'] . ' ' . $validated['appointment_time']);
            $appointmentData['start_at'] = $startAt;
            
            // Calculate end_at based on service duration
            if ($duration > 0) {
                $appointmentData['end_at'] = $startAt->copy()->addMinutes($duration);
            }
        }

        $this->appointmentService->create($appointmentData, $serviceVariantData);

        return redirect()->route('admin.appointments.index')
            ->with('success', 'Lịch hẹn đã được tạo thành công!');
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
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $appointment = $this->appointmentService->getOne($id);
        $employees = $this->employeeService->getAll();
        
        // Lấy dịch vụ đơn (không có biến thể)
        $singleServices = \App\Models\Service::whereNull('deleted_at')
            ->whereDoesntHave('serviceVariants')
            ->get();
        
        // Lấy dịch vụ có biến thể
        $variantServices = \App\Models\Service::whereNull('deleted_at')
            ->whereHas('serviceVariants')
            ->with('serviceVariants')
            ->get();
        
        // Lấy combo
        $combos = \App\Models\Combo::whereNull('deleted_at')
            ->with('comboItems')
            ->get();
        
        // Xác định loại dịch vụ hiện tại
        $currentServiceType = 'variant'; // default
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
                // Dịch vụ đơn - tìm service theo name trong notes
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
        
        return view('admin.appointments.edit', compact(
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'employee_id' => 'nullable|exists:employees,id',
            'service_type' => 'required|in:single,variant,combo',
            'service_id' => 'nullable|exists:services,id',
            'service_variant_id' => 'nullable|exists:service_variants,id',
            'combo_id' => 'nullable|exists:combos,id',
            'status' => 'required|in:Chờ xử lý,Đã xác nhận,Đang thực hiện,Hoàn thành,Đã hủy,Chưa thanh toán,Đã thanh toán',
            'note' => 'nullable|string',
            'appointment_date' => 'nullable|date',
            'appointment_time' => 'nullable|string',
        ]);

        // Validate that at least one service is selected based on service_type
        if ($validated['service_type'] === 'single' && empty($validated['service_id'])) {
            return redirect()->back()->withErrors(['service_id' => 'Vui lòng chọn dịch vụ đơn'])->withInput();
        }
        if ($validated['service_type'] === 'variant' && empty($validated['service_variant_id'])) {
            return redirect()->back()->withErrors(['service_variant_id' => 'Vui lòng chọn dịch vụ biến thể'])->withInput();
        }
        if ($validated['service_type'] === 'combo' && empty($validated['combo_id'])) {
            return redirect()->back()->withErrors(['combo_id' => 'Vui lòng chọn combo'])->withInput();
        }

        $appointment = $this->appointmentService->getOne($id);

        // Update user info
        $user = $appointment->user;
        if ($user) {
            $user->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? $user->email,
            ]);
        }

        // Prepare appointment data
        $appointmentData = [
            'user_id' => $user->id,
            'employee_id' => $validated['employee_id'] ?? null,
            'status' => $validated['status'],
            'note' => $validated['note'] ?? null,
        ];

        // Nếu appointment có nhiều dịch vụ (>1), giữ lại appointment details hiện có
        // Chỉ cập nhật dịch vụ nếu appointment có 1 dịch vụ hoặc có flag update_services
        $shouldUpdateServices = $request->has('update_services') && $request->input('update_services') === '1';
        $serviceVariantData = [];
        $duration = 0;
        
        // Tính duration từ appointment details hiện có
        foreach ($appointment->appointmentDetails as $detail) {
            $duration += $detail->duration ?? 60;
        }
        
        // Chỉ cập nhật dịch vụ nếu appointment có 1 dịch vụ hoặc có flag update_services
        if ($shouldUpdateServices || $appointment->appointmentDetails->count() <= 1) {
            $serviceVariantData = []; // Reset
            $duration = 0; // Reset
            
            if ($validated['service_type'] === 'single' && !empty($validated['service_id'])) {
                $service = \App\Models\Service::find($validated['service_id']);
                if ($service) {
                    $serviceVariantData[] = [
                        'service_variant_id' => null,
                        'combo_id' => null,
                        'employee_id' => $validated['employee_id'] ?? null,
                        'price_snapshot' => $service->base_price ?? 0,
                        'duration' => $service->base_duration ?? 0,
                        'status' => 'Chờ',
                        'notes' => $service->name,
                    ];
                    $duration = $service->base_duration ?? 0;
                }
            } elseif ($validated['service_type'] === 'variant' && !empty($validated['service_variant_id'])) {
                $serviceVariant = ServiceVariant::find($validated['service_variant_id']);
                if ($serviceVariant) {
                    $serviceVariantData[] = [
                        'service_variant_id' => $serviceVariant->id,
                        'combo_id' => null,
                        'employee_id' => $validated['employee_id'] ?? null,
                        'price_snapshot' => $serviceVariant->price,
                        'duration' => $serviceVariant->duration,
                        'status' => 'Chờ',
                    ];
                    $duration = $serviceVariant->duration ?? 0;
                }
            } elseif ($validated['service_type'] === 'combo' && !empty($validated['combo_id'])) {
                $combo = \App\Models\Combo::find($validated['combo_id']);
                if ($combo) {
                    // Calculate total duration from combo items
                    $totalDuration = 0;
                    foreach ($combo->comboItems as $item) {
                        if ($item->serviceVariant) {
                            $totalDuration += $item->serviceVariant->duration ?? 0;
                        } elseif ($item->service) {
                            $totalDuration += $item->service->base_duration ?? 0;
                        }
                    }
                    
                    $serviceVariantData[] = [
                        'service_variant_id' => null,
                        'combo_id' => $combo->id,
                        'employee_id' => $validated['employee_id'] ?? null,
                        'price_snapshot' => $combo->price,
                        'duration' => $totalDuration,
                        'status' => 'Chờ',
                        'notes' => $combo->name,
                    ];
                    $duration = $totalDuration;
                }
            }
        }
        // Nếu không cập nhật dịch vụ, serviceVariantData sẽ rỗng và AppointmentService sẽ giữ lại appointment details hiện có

        // Set start_at and end_at if date and time provided
        if (!empty($validated['appointment_date']) && !empty($validated['appointment_time'])) {
            $startAt = Carbon::parse($validated['appointment_date'] . ' ' . $validated['appointment_time']);
            $appointmentData['start_at'] = $startAt;
            
            // Calculate end_at based on service duration
            if ($duration > 0) {
                $appointmentData['end_at'] = $startAt->copy()->addMinutes($duration);
            }
        }

        // Chỉ truyền serviceVariantData nếu muốn cập nhật dịch vụ
        // Nếu rỗng, AppointmentService sẽ giữ lại appointment details hiện có
        $this->appointmentService->update($id, $appointmentData, $serviceVariantData);

        return redirect()->route('admin.appointments.index')
            ->with('success', 'Lịch hẹn đã được cập nhật thành công!');
    }

    /**
     * Cancel an appointment.
     */
    public function cancel(Request $request, string $id)
    {
        $validated = $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->appointmentService->cancelAppointment($id, $validated['cancellation_reason'] ?? null);
            return redirect()->route('admin.appointments.index')
                ->with('success', 'Lịch hẹn đã được hủy thành công!');
        } catch (\Exception $e) {
            return redirect()->route('admin.appointments.index')
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Restore a cancelled appointment.
     */
    public function restore(string $id)
    {
        try {
            $this->appointmentService->restore($id);
            return redirect()->route('admin.appointments.index')
                ->with('success', 'Lịch hẹn đã được khôi phục thành công!');
        } catch (\Exception $e) {
            return redirect()->route('admin.appointments.cancelled')
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Display cancelled appointments.
     */
    public function cancelled()
    {
        $appointments = $this->appointmentService->getCancelled();
        return view('admin.appointments.cancelled', compact('appointments'));
    }

    /**
     * Get services by employee ID (AJAX).
     */
    public function getServicesByEmployee($employeeId)
    {
        $employee = \App\Models\Employee::with('services')->findOrFail($employeeId);
        $employeeServiceIds = $employee->services->pluck('id')->toArray();
        
        // Lấy dịch vụ đơn
        $singleServices = \App\Models\Service::whereNull('deleted_at')
            ->whereDoesntHave('serviceVariants')
            ->whereIn('id', $employeeServiceIds)
            ->get()
            ->map(function($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'price' => $service->base_price ?? 0,
                    'duration' => $service->base_duration ?? 0,
                ];
            });
        
        // Lấy dịch vụ có biến thể
        $variantServices = \App\Models\Service::whereNull('deleted_at')
            ->whereHas('serviceVariants')
            ->whereIn('id', $employeeServiceIds)
            ->with('serviceVariants')
            ->get()
            ->map(function($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'variants' => $service->serviceVariants->map(function($variant) {
                        return [
                            'id' => $variant->id,
                            'name' => $variant->name,
                            'price' => $variant->price,
                            'duration' => $variant->duration,
                        ];
                    }),
                ];
            });
        
        // Combo - lấy tất cả vì combo có thể chứa nhiều dịch vụ
        $combos = \App\Models\Combo::whereNull('deleted_at')
            ->with('comboItems')
            ->get()
            ->map(function($combo) {
                return [
                    'id' => $combo->id,
                    'name' => $combo->name,
                    'price' => $combo->price,
                ];
            });
        
        return response()->json([
            'singleServices' => $singleServices,
            'variantServices' => $variantServices,
            'combos' => $combos,
        ]);
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

    /**
     * Permanently delete a cancelled appointment.
     */
    public function forceDelete(string $id)
    {
        try {
            $this->appointmentService->forceDelete($id);
            return redirect()->route('admin.appointments.cancelled')
                ->with('success', 'Lịch hẹn đã được xóa vĩnh viễn thành công!');
        } catch (\Exception $e) {
            return redirect()->route('admin.appointments.cancelled')
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}
