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
        // Chỉ lấy nhân viên có position là Stylist
        $employees = \App\Models\Employee::where('position', 'Stylist')
            ->with('user')
            ->get();
        
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
            'services' => 'required|array|min:1',
            'services.*' => 'required|string',
            'status' => 'required|in:Chờ xử lý,Đã xác nhận,Đang thực hiện,Hoàn thành',
            'note' => 'nullable|string',
            'appointment_date' => 'nullable|date',
            'appointment_time' => 'nullable|string',
        ]);

        // Validate that at least one service is selected
        if (empty($validated['services']) || count($validated['services']) === 0) {
            return redirect()->back()->withErrors(['services' => 'Vui lòng chọn ít nhất một dịch vụ'])->withInput();
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

        // Prepare service data from selected services
        $serviceVariantData = [];
        $totalDuration = 0;
        
        foreach ($validated['services'] as $serviceValue) {
            // Format: "type_id" (e.g., "single_1", "variant_5", "combo_2")
            $parts = explode('_', $serviceValue);
            if (count($parts) !== 2) {
                continue;
            }
            
            $serviceType = $parts[0];
            $serviceId = $parts[1];
            
            if ($serviceType === 'single') {
                $service = \App\Models\Service::find($serviceId);
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
                    $totalDuration += $service->base_duration ?? 0;
                }
            } elseif ($serviceType === 'variant') {
                $serviceVariant = ServiceVariant::find($serviceId);
                if ($serviceVariant) {
                    $serviceVariantData[] = [
                        'service_variant_id' => $serviceVariant->id,
                        'combo_id' => null,
                        'employee_id' => $validated['employee_id'] ?? null,
                        'price_snapshot' => $serviceVariant->price,
                        'duration' => $serviceVariant->duration,
                        'status' => 'Chờ',
                    ];
                    $totalDuration += $serviceVariant->duration ?? 0;
                }
            } elseif ($serviceType === 'combo') {
                $combo = \App\Models\Combo::find($serviceId);
                if ($combo) {
                    // Use duration from combo if available, otherwise calculate from combo items
                    if (!is_null($combo->duration)) {
                        $comboDuration = $combo->duration;
                    } else {
                        // Calculate from combo items if duration not set
                        $comboDuration = 0;
                        foreach ($combo->comboItems as $item) {
                            if ($item->serviceVariant) {
                                $comboDuration += $item->serviceVariant->duration ?? 0;
                            } elseif ($item->service) {
                                $comboDuration += $item->service->base_duration ?? 0;
                            }
                        }
                    }
                    
                    $serviceVariantData[] = [
                        'service_variant_id' => null,
                        'combo_id' => $combo->id,
                        'employee_id' => $validated['employee_id'] ?? null,
                        'price_snapshot' => $combo->price,
                        'duration' => $comboDuration,
                        'status' => 'Chờ',
                        'notes' => $combo->name,
                    ];
                    $totalDuration += $comboDuration;
                }
            }
        }

        // Set start_at and end_at if date and time provided
        if (!empty($validated['appointment_date']) && !empty($validated['appointment_time'])) {
            $startAt = Carbon::parse($validated['appointment_date'] . ' ' . $validated['appointment_time']);
            $appointmentData['start_at'] = $startAt;
            
            // Calculate end_at based on total service duration
            if ($totalDuration > 0) {
                $appointmentData['end_at'] = $startAt->copy()->addMinutes($totalDuration);
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
        // Chỉ lấy nhân viên có position là Stylist
        $employees = \App\Models\Employee::where('position', 'Stylist')
            ->with('user')
            ->get();
        
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
        \Illuminate\Support\Facades\Log::info('Appointment update started', [
            'appointment_id' => $id,
            'method' => $request->method(),
            'route' => $request->route()->getName(),
            'url' => $request->fullUrl()
        ]);
        
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
                'status' => 'required|in:Chờ xử lý,Đã xác nhận,Đang thực hiện,Hoàn thành,Đã hủy',
                'note' => 'nullable|string',
                'appointment_date' => 'nullable|date',
                'appointment_time' => 'nullable|string',
            ]);

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
                    
                    if ($serviceType === 'single') {
                        $service = \App\Models\Service::find($serviceId);
                        if ($service) {
                            $newServiceVariantData[] = [
                                'service_variant_id' => null,
                                'combo_id' => null,
                                'employee_id' => $validated['employee_id'] ?? null,
                                'price_snapshot' => $service->base_price ?? 0,
                                'duration' => $service->base_duration ?? 0,
                                'status' => 'Chờ',
                                'notes' => $service->name,
                            ];
                            $additionalDuration += $service->base_duration ?? 0;
                        }
                    } elseif ($serviceType === 'variant') {
                        $serviceVariant = ServiceVariant::find($serviceId);
                        if ($serviceVariant) {
                            $newServiceVariantData[] = [
                                'service_variant_id' => $serviceVariant->id,
                                'combo_id' => null,
                                'employee_id' => $validated['employee_id'] ?? null,
                                'price_snapshot' => $serviceVariant->price,
                                'duration' => $serviceVariant->duration,
                                'status' => 'Chờ',
                            ];
                            $additionalDuration += $serviceVariant->duration ?? 0;
                        }
                    } elseif ($serviceType === 'combo') {
                        $combo = \App\Models\Combo::find($serviceId);
                        if ($combo) {
                            // Use duration from combo if available, otherwise calculate from combo items
                            $comboDuration = $combo->duration ?? 0;
                            if (!$comboDuration) {
                                foreach ($combo->comboItems as $item) {
                                    if ($item->serviceVariant) {
                                        $comboDuration += $item->serviceVariant->duration ?? 0;
                                    } elseif ($item->service) {
                                        $comboDuration += $item->service->base_duration ?? 0;
                                    }
                                }
                            }
                            
                            $newServiceVariantData[] = [
                                'service_variant_id' => null,
                                'combo_id' => $combo->id,
                                'employee_id' => $validated['employee_id'] ?? null,
                                'price_snapshot' => $combo->price,
                                'duration' => $comboDuration,
                                'status' => 'Chờ',
                                'notes' => $combo->name,
                            ];
                            $additionalDuration += $comboDuration;
                        }
                    }
                }
            }

            // Set start_at and end_at if date and time provided
            if (!empty($validated['appointment_date']) && !empty($validated['appointment_time'])) {
                $startAt = Carbon::parse($validated['appointment_date'] . ' ' . $validated['appointment_time']);
                $appointmentData['start_at'] = $startAt;
                
                // Calculate total duration from existing services + new services
                // Reload appointment to get fresh appointment details count
                $appointment->refresh();
                $existingDuration = $appointment->appointmentDetails->sum('duration');
                $totalDuration = $existingDuration + $additionalDuration;
                
                // Always set end_at if we have start_at, even if duration is 0
                $appointmentData['end_at'] = $startAt->copy()->addMinutes(max($totalDuration, 60)); // Minimum 60 minutes if no duration
            }
            // If date/time not provided, don't include start_at and end_at in updateData
            // They will remain unchanged

            // Update appointment (keep existing services, add new ones)
            \Illuminate\Support\Facades\Log::info('Calling appointmentService->update', [
                'appointment_id' => $id,
                'appointment_data' => $appointmentData,
                'service_variant_data_count' => count($newServiceVariantData)
            ]);
            
            $updatedAppointment = $this->appointmentService->update($id, $appointmentData, $newServiceVariantData);
            
            \Illuminate\Support\Facades\Log::info('Appointment updated successfully', [
                'appointment_id' => $id,
                'appointment_deleted_at' => $updatedAppointment->deleted_at,
                'appointment_trashed' => $updatedAppointment->trashed()
            ]);

            return redirect()->route('admin.appointments.index')
                ->with('success', 'Lịch hẹn đã được cập nhật thành công!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exceptions to show validation errors
            throw $e;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error updating appointment: ' . $e->getMessage(), [
                'appointment_id' => $id,
                'error' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi cập nhật lịch hẹn: ' . $e->getMessage());
        }
    }

    /**
     * Remove a service from appointment.
     */
    public function removeService(string $appointmentId, string $detailId)
    {
        try {
            $appointment = $this->appointmentService->getOne($appointmentId);
            $detail = \App\Models\AppointmentDetail::findOrFail($detailId);
            
            if ($detail->appointment_id != $appointment->id) {
                return redirect()->back()->with('error', 'Dịch vụ không thuộc lịch hẹn này!');
            }
            
            $detail->delete();
            
            return redirect()->route('admin.appointments.edit', $appointmentId)
                ->with('success', 'Dịch vụ đã được xóa thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
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
        \Illuminate\Support\Facades\Log::warning('AppointmentController->destroy called', [
            'appointment_id' => $id,
            'request_method' => request()->method(),
            'request_url' => request()->fullUrl(),
            'request_route' => request()->route()->getName() ?? 'unknown',
            'request_all' => request()->all()
        ]);
        
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
