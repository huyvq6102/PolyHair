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
            'appointment_date' => $request->get('appointment_date'),
            'booking_code' => $request->get('booking_code'),
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
                    // Calculate total duration from combo items
                    $comboDuration = 0;
                    foreach ($combo->comboItems as $item) {
                        if ($item->serviceVariant) {
                            $comboDuration += $item->serviceVariant->duration ?? 0;
                        } elseif ($item->service) {
                            $comboDuration += $item->service->base_duration ?? 0;
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
                            // Calculate total duration from combo items
                            $comboDuration = 0;
                            foreach ($combo->comboItems as $item) {
                                if ($item->serviceVariant) {
                                    $comboDuration += $item->serviceVariant->duration ?? 0;
                                } elseif ($item->service) {
                                    $comboDuration += $item->service->base_duration ?? 0;
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
            
            // Kiểm tra số lượng dịch vụ - không cho xóa nếu chỉ còn 1 dịch vụ
            $serviceCount = $appointment->appointmentDetails->count();
            if ($serviceCount <= 1) {
                return redirect()->back()->with('error', 'Không thể xóa dịch vụ cuối cùng! Đơn đặt phải có ít nhất 1 dịch vụ.');
            }
            
            $detail->delete();
            
            // Update appointment end_at if needed (recalculate based on remaining services)
            $appointment->refresh();
            $remainingDuration = $appointment->appointmentDetails->sum('duration');
            if ($appointment->start_at && $remainingDuration > 0) {
                $appointment->end_at = \Carbon\Carbon::parse($appointment->start_at)->addMinutes($remainingDuration);
                $appointment->save();
            }
            
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
            $result = $this->appointmentService->cancelAppointment($id, $validated['cancellation_reason'] ?? null);
            // Admin hủy không cần kiểm tra ban
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

    /**
     * Show Checkout Page for Admin.
     */
    public function checkout(Request $request)
    {
        $appointmentId = $request->input('appointment_id');
        if (!$appointmentId) {
            return redirect()->route('admin.appointments.index')->with('error', 'Thiếu thông tin lịch hẹn.');
        }

        $appointment = \App\Models\Appointment::with([
            'appointmentDetails.serviceVariant.service',
            'appointmentDetails.combo',
            'user'
        ])->find($appointmentId);

        if (!$appointment) {
            return redirect()->route('admin.appointments.index')->with('error', 'Không tìm thấy lịch hẹn.');
        }
        
        if ($appointment->status === 'Đã thanh toán') {
            return redirect()->route('admin.appointments.show', $appointment->id)
                             ->with('info', 'Lịch hẹn này đã được thanh toán.');
        }

        // Construct "Cart" data from Appointment
        $services = [];
        $subtotal = 0;
        
        // Also build a cart array compatible with PaymentService/PromotionService
        $cart = [
            'appointment_' . $appointment->id => [
                'type' => 'appointment',
                'id' => $appointment->id,
                'quantity' => 1
            ]
        ];
        // We might want to put this in session so PromotionService can use it via Session if it relies on that,
        // OR we just pass it explicitly. The checkout view might rely on Session for coupon.
        // Let's set the session cart to be safe and consistent with Site checkout.
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
            $promotion = \App\Models\Promotion::find($appliedPromotionId);
            if ($promotion) {
                $couponCode = $promotion->code;
                \Illuminate\Support\Facades\Session::put('coupon_code', $couponCode);
                \Illuminate\Support\Facades\Session::put('applied_promotion_id', $appliedPromotionId);
            }
        }

        if ($couponCode) {
            $promotionService = app(\App\Services\PromotionService::class);
            $userIdForPromo = $appointment->user_id ?? (auth()->check() ? auth()->id() : null);
            
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
        $availableOrderPromotions = \App\Models\Promotion::where('apply_scope', 'order')
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
            ->get();

        $availableCustomerTierPromotions = \App\Models\Promotion::where('apply_scope', 'customer_tier')
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
            ->get();

        // Use the same view as Site Checkout but we might need to adjust form action
        // OR we can pass a route override variable to the view?
        // Actually the view hardcodes route('site.payments.process').
        // We should duplicate the view or make it dynamic.
        // For now, let's duplicate the view to 'admin.appointments.checkout' to be safe and customizable.
        
        return view('admin.appointments.checkout', [
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
            // Pass appointment ID for context
            'appointment' => $appointment,
            // Pass available promotions
            'availableOrderPromotions' => $availableOrderPromotions,
            'availableCustomerTierPromotions' => $availableCustomerTierPromotions
        ]);
    }

    /**
     * Process Checkout for Admin.
     */
    public function processCheckout(Request $request, \App\Services\PaymentService $paymentService)
    {
        // Similar to Site\CheckoutController@processPayment but simplified for Admin
        try {
            $cart = \Illuminate\Support\Facades\Session::get('cart', []);
            $user = auth()->user(); // The Admin executing the payment
            
            // Determine the "Payer" user (Customer) from appointment in cart
            $payer = null;
            $appointmentId = null;

            foreach ($cart as $item) {
                if (isset($item['type']) && $item['type'] === 'appointment') {
                    $appointmentId = $item['id'];
                    $appt = \App\Models\Appointment::find($item['id']);
                    if ($appt) {
                        $payer = $appt->user; // The customer
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
                    
                    \Illuminate\Support\Facades\Log::info('Admin checkout: Applied promotion', [
                        'promotion_id' => $appliedPromotionId,
                        'coupon_code' => $couponCode,
                        'appointment_id' => $appointmentId
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

            // If Admin/Staff, mark as completed immediately for Cash
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

            // Handle VNPAY redirect if needed (though usually Admin takes Cash)
            if ($paymentMethod === 'vnpay') {
                // Save context for return URL handling
                \Illuminate\Support\Facades\Session::put('payment_source', 'admin');
                \Illuminate\Support\Facades\Session::put('payment_appointment_id', $payment->appointment_id);

                $vnpayService = app(\App\Services\VnpayService::class);
                $vnpUrl = $vnpayService->createPayment($payment->invoice_code, $payment->total);
                return redirect($vnpUrl);
            }

            return redirect()->route('admin.appointments.index')
                ->with('success', 'Thanh toán thành công cho lịch hẹn #' . $payment->appointment_id);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error($e);
            return back()->with('error', 'Thanh toán thất bại: ' . $e->getMessage());
        }
    }

    /**
     * Apply a coupon code from the admin checkout page.
     */
    public function applyCoupon(Request $request, \App\Services\PromotionService $promotionService)
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
        $appointmentId = $request->input('appointment_id'); // Hidden field in form

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
        // Assuming the appointment has a method or a way to get its current price
        // For simplicity, let's recalculate subtotal from appointment details for promotion validation
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
        // Use the cart variable directly in promotionService, no need to put in Session temporarily here
        // as the controller's checkout method handles setting the session cart for the view.

        $result = $promotionService->validateAndCalculateDiscount(
            $code,
            $cart,
            $subtotal, // Use the dynamically calculated subtotal
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
     * Remove applied coupon code from the admin checkout page.
     */
    public function removeCoupon(Request $request)
    {
        \Illuminate\Support\Facades\Session::forget('coupon_code');
        \Illuminate\Support\Facades\Session::forget('applied_promotion_id');
        // Redirect back to the checkout page, potentially with the appointment_id if present
        $appointmentId = $request->input('appointment_id');
        if ($appointmentId) {
            return redirect()->route('admin.appointments.checkout', ['appointment_id' => $appointmentId])
                             ->with('success', 'Đã gỡ bỏ mã khuyến mại.');
        }
        return back()->with('success', 'Đã gỡ bỏ mã khuyến mại.');
    }
}
