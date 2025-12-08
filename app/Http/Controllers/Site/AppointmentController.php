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
    public function selectServices(Request $request)
    {
        // Load selected promotion if exists
        $selectedPromotion = null;
        if ($request->has('promotion_id') && $request->promotion_id) {
            $selectedPromotion = \App\Models\Promotion::where('id', $request->promotion_id)
                ->where('status', 'active')
                ->whereNull('deleted_at')
                ->first();
        }
        
        // Lấy tất cả danh mục có dịch vụ hoặc combo, sắp xếp theo bảng chữ cái
        $categories = \App\Models\ServiceCategory::with([
                'services' => function($query) {
                    $query->whereNull('deleted_at')
                        ->where('status', 'Hoạt động')
                        ->with(['serviceVariants.variantAttributes'])
                        ->orderBy('name', 'asc'); // Sắp xếp dịch vụ theo bảng chữ cái
                },
                'combos' => function($query) {
                    $query->whereNull('deleted_at')
                        ->where('status', 'Hoạt động')
                        ->with(['comboItems.serviceVariant.service', 'comboItems.service'])
                        ->orderBy('name', 'asc'); // Sắp xếp combo theo bảng chữ cái
                }
            ])
            ->where(function($query) {
                // Danh mục có dịch vụ hoặc combo
                $query->whereHas('services', function($q) {
                    $q->whereNull('deleted_at')
                        ->where('status', 'Hoạt động');
                })->orWhereHas('combos', function($q) {
                    $q->whereNull('deleted_at')
                        ->where('status', 'Hoạt động');
                });
            })
            ->orderBy('name', 'asc') // Sắp xếp danh mục theo bảng chữ cái
            ->get();

        // Lấy các combo không có category (để hiển thị riêng nếu cần)
        $combosWithoutCategory = \App\Models\Combo::with(['comboItems.serviceVariant.service', 'comboItems.service'])
            ->whereNull('deleted_at')
            ->where('status', 'Hoạt động')
            ->whereNull('category_id')
            ->orderBy('name', 'asc')
            ->get();

        return view('site.appointment.select-services', compact('categories', 'combosWithoutCategory', 'selectedPromotion'));
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
        
        // Filter employees by service expertise - chỉ hiển thị nhân viên có chuyên môn phù hợp
        // Lấy service_id từ query string hoặc input
        // Sử dụng request()->query() để lấy từ query string, request()->input() để lấy từ POST
        $serviceIds = $request->query('service_id', $request->input('service_id', []));
        $variantIds = $request->query('service_variants', $request->input('service_variants', []));
        $comboIds = $request->query('combo_id', $request->input('combo_id', []));
        
        // Nếu service_id là từ query string (single value), chuyển thành array
        if (!is_array($serviceIds)) {
            $serviceIds = $serviceIds ? [$serviceIds] : [];
        }
        if (!is_array($variantIds)) {
            $variantIds = $variantIds ? [$variantIds] : [];
        }
        if (!is_array($comboIds)) {
            $comboIds = $comboIds ? [$comboIds] : [];
        }
        
        // Lọc bỏ các giá trị null hoặc rỗng, nhưng giữ lại giá trị 0 nếu có
        $serviceIds = array_filter($serviceIds, function($value) {
            return $value !== null && $value !== '';
        });
        $variantIds = array_filter($variantIds, function($value) {
            return $value !== null && $value !== '';
        });
        $comboIds = array_filter($comboIds, function($value) {
            return $value !== null && $value !== '';
        });
        
        // Thu thập service IDs và phân biệt dịch vụ đơn vs dịch vụ biến thể
        $singleServiceIds = []; // Dịch vụ đơn (không có variants)
        $variantServiceIds = []; // Dịch vụ biến thể (có variants)
        
        // Lấy service IDs từ service_id (dịch vụ đơn)
        if (!empty($serviceIds)) {
            $singleServiceIds = array_merge($singleServiceIds, $serviceIds);
        }
        
        // Lấy service IDs từ service_variants (dịch vụ biến thể)
        if (!empty($variantIds)) {
            $variants = \App\Models\ServiceVariant::whereIn('id', $variantIds)->get();
            $variantServiceIds = $variants->pluck('service_id')->unique()->toArray();
        }
        
        // Lấy service IDs từ combo (có thể là dịch vụ biến thể)
        if (!empty($comboIds)) {
            $combos = \App\Models\Combo::with(['comboItems.serviceVariant.service', 'comboItems.service'])
                ->whereIn('id', $comboIds)
                ->get();
            
            foreach ($combos as $combo) {
                if ($combo && $combo->comboItems) {
                    foreach ($combo->comboItems as $item) {
                        // Lấy service ID từ service_variant nếu có
                        if ($item->serviceVariant && $item->serviceVariant->service) {
                            $variantServiceIds[] = $item->serviceVariant->service->id;
                        }
                        // Lấy service ID trực tiếp nếu có (combo item có service_id nhưng không có variant)
                        elseif ($item->service_id) {
                            // Kiểm tra xem service này có variants không để phân loại
                            $service = \App\Models\Service::find($item->service_id);
                            if ($service) {
                                $hasVariants = $service->serviceVariants()->whereNull('deleted_at')->exists();
                                if ($hasVariants) {
                                    $variantServiceIds[] = $item->service_id;
                                } else {
                                    $singleServiceIds[] = $item->service_id;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        // Loại bỏ trùng lặp
        $singleServiceIds = array_unique($singleServiceIds);
        $variantServiceIds = array_unique($variantServiceIds);
        
        // Khởi tạo $employees là collection rỗng
        $employees = collect([]);
        
        // Query nhân viên dựa trên chuyên môn được set trong admin
        // Phân biệt dịch vụ đơn và dịch vụ biến thể
        if (!empty($singleServiceIds) || !empty($variantServiceIds)) {
            $employeeIds = [];
            
            // Nếu có dịch vụ đơn được chọn, chỉ lấy nhân viên có chuyên môn là dịch vụ đơn
            if (!empty($singleServiceIds)) {
                $singleServiceIds = array_map('intval', $singleServiceIds);
                $singleServiceIds = array_filter($singleServiceIds);
                $singleServiceIds = array_values($singleServiceIds);
                
                if (!empty($singleServiceIds)) {
                    // Lấy danh sách service_id là dịch vụ đơn (không có variants)
                    $validSingleServiceIds = \App\Models\Service::whereIn('id', $singleServiceIds)
                        ->whereNull('deleted_at')
                        ->whereDoesntHave('serviceVariants')
                        ->pluck('id')
                        ->toArray();
                    
                    if (!empty($validSingleServiceIds)) {
                        // Lấy employee_id từ employee_skills có service_id là dịch vụ đơn
                        $singleEmployeeIds = \DB::table('employee_skills')
                            ->whereIn('service_id', $validSingleServiceIds)
                            ->whereNotNull('employee_id')
                            ->whereNotNull('service_id')
                            ->distinct()
                            ->pluck('employee_id')
                            ->toArray();
                        
                        $employeeIds = array_merge($employeeIds, $singleEmployeeIds);
                    }
                }
            }
            
            // Nếu có dịch vụ biến thể được chọn, chỉ lấy nhân viên có chuyên môn là dịch vụ biến thể
            if (!empty($variantServiceIds)) {
                $variantServiceIds = array_map('intval', $variantServiceIds);
                $variantServiceIds = array_filter($variantServiceIds);
                $variantServiceIds = array_values($variantServiceIds);
                
                if (!empty($variantServiceIds)) {
                    // Lấy danh sách service_id là dịch vụ biến thể (có variants)
                    $validVariantServiceIds = \App\Models\Service::whereIn('id', $variantServiceIds)
                        ->whereNull('deleted_at')
                        ->whereHas('serviceVariants')
                        ->pluck('id')
                        ->toArray();
                    
                    if (!empty($validVariantServiceIds)) {
                        // Lấy employee_id từ employee_skills có service_id là dịch vụ biến thể
                        $variantEmployeeIds = \DB::table('employee_skills')
                            ->whereIn('service_id', $validVariantServiceIds)
                            ->whereNotNull('employee_id')
                            ->whereNotNull('service_id')
                            ->distinct()
                            ->pluck('employee_id')
                            ->toArray();
                        
                        $employeeIds = array_merge($employeeIds, $variantEmployeeIds);
                    }
                }
            }
            
            // Loại bỏ trùng lặp
            $employeeIds = array_unique($employeeIds);
            $employeeIds = array_map('intval', $employeeIds);
            $employeeIds = array_filter($employeeIds);
            $employeeIds = array_values($employeeIds);
            
            if (!empty($employeeIds)) {
                // Query nhân viên dựa trên employee_id từ employee_skills
                $employees = \App\Models\Employee::with(['user.role', 'services'])
                    ->whereIn('id', $employeeIds)
                    ->whereNotNull('user_id')
                    ->where('status', '!=', 'Vô hiệu hóa')
                    ->whereHas('user', function($query) {
                        // Loại trừ admin
                        $query->where('role_id', '!=', 1);
                    })
                    ->get()
                    // Lọc lại để đảm bảo nhân viên chỉ có chuyên môn phù hợp với loại dịch vụ đã chọn
                    ->filter(function($employee) use ($singleServiceIds, $variantServiceIds) {
                        $employeeServiceIds = $employee->services->pluck('id')->toArray();
                        
                        if (empty($employeeServiceIds)) {
                            return false;
                        }
                        
                        // Nếu chọn dịch vụ đơn, chỉ giữ lại nhân viên có chuyên môn là dịch vụ đơn
                        if (!empty($singleServiceIds) && empty($variantServiceIds)) {
                            // Kiểm tra xem nhân viên có service_id nào trùng với dịch vụ đơn đã chọn không
                            $hasMatchingService = !empty(array_intersect($employeeServiceIds, $singleServiceIds));
                            
                            if (!$hasMatchingService) {
                                return false;
                            }
                            
                            // Kiểm tra xem tất cả chuyên môn của nhân viên có phải là dịch vụ đơn không
                            // Lấy tất cả service_id của nhân viên và kiểm tra xem có dịch vụ biến thể nào không
                            // Sử dụng whereHas để kiểm tra xem có service nào có variants không
                            $servicesWithVariants = \App\Models\Service::whereIn('id', $employeeServiceIds)
                                ->whereNull('deleted_at')
                                ->whereHas('serviceVariants', function($query) {
                                    $query->whereNull('deleted_at');
                                })
                                ->pluck('id')
                                ->toArray();
                            
                            // Nếu nhân viên có bất kỳ chuyên môn nào là dịch vụ biến thể, loại bỏ
                            if (!empty($servicesWithVariants)) {
                                return false;
                            }
                            
                            // Tất cả chuyên môn đều là dịch vụ đơn và có service_id trùng
                            return true;
                        }
                        
                        // Nếu chọn dịch vụ biến thể, chỉ giữ lại nhân viên có chuyên môn là dịch vụ biến thể
                        if (!empty($variantServiceIds) && empty($singleServiceIds)) {
                            // Kiểm tra xem nhân viên có service_id nào trùng với dịch vụ biến thể đã chọn không
                            $hasMatchingService = !empty(array_intersect($employeeServiceIds, $variantServiceIds));
                            
                            if (!$hasMatchingService) {
                                return false;
                            }
                            
                            // Kiểm tra xem tất cả chuyên môn của nhân viên có phải là dịch vụ biến thể không
                            // Lấy tất cả service_id của nhân viên và kiểm tra xem có dịch vụ đơn nào không
                            // Sử dụng whereDoesntHave để kiểm tra xem có service nào không có variants không
                            $servicesWithoutVariants = \App\Models\Service::whereIn('id', $employeeServiceIds)
                                ->whereNull('deleted_at')
                                ->whereDoesntHave('serviceVariants', function($query) {
                                    $query->whereNull('deleted_at');
                                })
                                ->pluck('id')
                                ->toArray();
                            
                            // Nếu nhân viên có bất kỳ chuyên môn nào là dịch vụ đơn, loại bỏ
                            if (!empty($servicesWithoutVariants)) {
                                return false;
                            }
                            
                            // Tất cả chuyên môn đều là dịch vụ biến thể và có service_id trùng
                            return true;
                        }
                        
                        // Nếu chọn cả hai loại, giữ lại nhân viên có service_id trùng với bất kỳ dịch vụ nào đã chọn
                        if (!empty($singleServiceIds) && !empty($variantServiceIds)) {
                            $allSelectedServiceIds = array_merge($singleServiceIds, $variantServiceIds);
                            return !empty(array_intersect($employeeServiceIds, $allSelectedServiceIds));
                        }
                        
                        return false;
                    })
                    ->values();
            } else {
                // Không có nhân viên nào có chuyên môn phù hợp
                $employees = collect([]);
            }
        }
        // Nếu không có dịch vụ nào được chọn, $employees đã được khởi tạo là collection rỗng ở trên
        
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
            
            // Log để debug
            \Log::info('Appointment store - Received data', [
                'service_variants' => $validated['service_variants'] ?? [],
                'service_id' => $validated['service_id'] ?? [],
                'combo_id' => $validated['combo_id'] ?? [],
                'raw_request' => $request->all(),
            ]);
            
            // Process service variants if selected (priority: variants over service/combo)
            // IMPORTANT: Only process the service_variants that were actually selected
            if (!empty($validated['service_variants'])) {
                // Ensure it's an array and filter out any empty/null values
                $variantIds = is_array($validated['service_variants']) 
                    ? array_filter($validated['service_variants'], function($id) {
                        return !empty($id) && $id !== '0' && $id !== 0 && is_numeric($id);
                    })
                    : [];
                
                // Remove duplicates and re-index array
                $variantIds = array_values(array_unique($variantIds));
                
                // CRITICAL: If we have more than 10 variants, something is wrong - log warning
                if (count($variantIds) > 10) {
                    \Log::warning('Appointment store - Suspicious number of variants', [
                        'count' => count($variantIds),
                        'variant_ids' => $variantIds,
                        'request_url' => $request->fullUrl(),
                    ]);
                }
                
                \Log::info('Appointment store - Processing variants', [
                    'variant_ids' => $variantIds,
                    'count' => count($variantIds),
                ]);
                
                foreach ($variantIds as $variantId) {
                    try {
                        $variant = \App\Models\ServiceVariant::findOrFail($variantId);
                        $totalDuration += $variant->duration ?? 60; // Default 60 minutes if not set
                        
                        $serviceVariantData[] = [
                            'service_variant_id' => $variantId,
                            'employee_id' => $validated['employee_id'] ?? null,
                            'price_snapshot' => $variant->price,
                            'duration' => $variant->duration ?? 60,
                            'status' => 'Chờ',
                        ];
                    } catch (\Exception $e) {
                        \Log::error('Appointment store - Failed to process variant', [
                            'variant_id' => $variantId,
                            'error' => $e->getMessage(),
                        ]);
                        // Skip invalid variant
                    }
                }
            }
            
            // Process combos if selected
            if (!empty($validated['combo_id'])) {
                // Ensure it's an array and filter out any empty/null values
                $comboIds = is_array($validated['combo_id']) 
                    ? array_filter($validated['combo_id'], function($id) {
                        return !empty($id) && $id !== '0' && $id !== 0;
                    })
                    : (($validated['combo_id'] && $validated['combo_id'] !== '0') ? [$validated['combo_id']] : []);
                
                // Remove duplicates
                $comboIds = array_unique($comboIds);
                
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
                // Ensure it's an array and filter out any empty/null values
                $serviceIds = is_array($validated['service_id']) 
                    ? array_filter($validated['service_id'], function($id) {
                        return !empty($id) && $id !== '0' && $id !== 0;
                    })
                    : (($validated['service_id'] && $validated['service_id'] !== '0') ? [$validated['service_id']] : []);
                
                // Remove duplicates
                $serviceIds = array_unique($serviceIds);
                
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

            // Log final serviceVariantData before creating appointment
            \Log::info('Appointment store - Final serviceVariantData', [
                'count' => count($serviceVariantData),
                'data' => $serviceVariantData,
            ]);
            
            // CRITICAL: Validate that we're not creating too many appointment details
            // Reasonable limit: 20 services (allowing for multiple services, combos, etc.)
            // If more than 20, something is definitely wrong
            if (count($serviceVariantData) > 20) {
                \Log::error('Appointment store - Too many service variants detected', [
                    'count' => count($serviceVariantData),
                    'service_variant_data' => $serviceVariantData,
                ]);
                
                // Return error instead of creating appointment with wrong data
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra: Quá nhiều dịch vụ được chọn. Vui lòng thử lại.',
                    'error' => 'Too many service variants: ' . count($serviceVariantData)
                ], 422);
            }
            
            // Warning if more than 5 (unusual but not necessarily wrong)
            if (count($serviceVariantData) > 5) {
                \Log::warning('Appointment store - Unusual number of service variants', [
                    'count' => count($serviceVariantData),
                    'service_variant_data' => $serviceVariantData,
                ]);
            }

            // Always create a new appointment for each booking
            // This ensures each booking has its own appointment with only the selected services
            $appointment = $this->appointmentService->create([
                'user_id' => $user->id,
                'employee_id' => $validated['employee_id'] ?? null,
                'status' => 'Chờ xử lý',
                'start_at' => $startAt,
                'end_at' => $endAt,
                'note' => $validated['note'] ?? null,
            ], $serviceVariantData);
            
            \Log::info('Appointment store - Created appointment', [
                'appointment_id' => $appointment->id,
                'details_count' => $appointment->appointmentDetails->count(),
                'expected_count' => count($serviceVariantData),
            ]);
            
            // Verify appointment details count matches expected
            if ($appointment->appointmentDetails->count() !== count($serviceVariantData)) {
                \Log::error('Appointment store - Mismatch in appointment details count', [
                    'appointment_id' => $appointment->id,
                    'expected' => count($serviceVariantData),
                    'actual' => $appointment->appointmentDetails->count(),
                ]);
            }

            DB::commit();

            // CRITICAL: Remove any existing appointments from cart before adding new one
            // This ensures we don't have old appointments with wrong data in cart
            // Update Session AFTER commit to ensure no race condition with database transaction
            $cart = Session::get('cart', []);
            
            // Remove all existing appointments from cart
            foreach ($cart as $key => $item) {
                if (isset($item['type']) && $item['type'] === 'appointment') {
                    unset($cart[$key]);
                }
            }
            
            // Add new appointment to cart
            $cartKey = 'appointment_' . $appointment->id;
            $cart[$cartKey] = [
                'type' => 'appointment',
                'id' => $appointment->id,
                'quantity' => 1,
            ];
            Session::put('cart', $cart);
            Session::save(); // Force save session
            
            \Log::info('Appointment store - Cart updated', [
                'appointment_id' => $appointment->id,
                'cart_keys' => array_keys($cart),
            ]);

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
                'redirect_url' => route('site.payments.checkout'),
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
            'appointmentDetails.serviceVariant.variantAttributes',
            'appointmentDetails.combo',
            'payments',
            'reviews'
        ])->findOrFail($id);
        
        // Calculate total price
        $totalPrice = 0;
        foreach ($appointment->appointmentDetails as $detail) {
            $totalPrice += $detail->price_snapshot ?? 0;
        }
        
        // Check if user can review (appointment completed and not reviewed yet)
        $canReview = false;
        $existingReview = null;
        
        if (auth()->check() && $appointment->status === 'Hoàn thành' && $appointment->user_id == auth()->id()) {
            $existingReview = \App\Models\Review::where('appointment_id', $appointment->id)
                ->where('user_id', auth()->id())
                ->first();
            $canReview = !$existingReview;
        }
        
        return view('site.appointment.show', compact('appointment', 'totalPrice', 'canReview', 'existingReview'));
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
            $workingSchedules = \App\Models\WorkingSchedule::with('shift')
                ->where('employee_id', $employeeId)
                ->whereDate('work_date', $appointmentDate->format('Y-m-d'))
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
     * Cancel an appointment.
     */
    public function cancel(Request $request, $id)
    {
        try {
            $appointment = \App\Models\Appointment::findOrFail($id);
            
            // Kiểm tra quyền: chỉ chủ sở hữu mới được hủy
            if (auth()->id() != $appointment->user_id && !auth()->user()->isAdmin()) {
                return back()->with('error', 'Bạn không có quyền hủy lịch hẹn này.');
            }
            
            // Kiểm tra xem có thể hủy không
            // Chỉ có thể hủy khi status = 'Chờ xử lý' và chưa quá 5 phút
            if ($appointment->status !== 'Chờ xử lý') {
                return back()->with('error', 'Chỉ có thể hủy lịch hẹn đang ở trạng thái "Chờ xử lý".');
            }
            
            // Kiểm tra thời gian: chỉ có thể hủy trong vòng 5 phút kể từ khi đặt
            $createdAt = \Carbon\Carbon::parse($appointment->created_at);
            $now = now();
            $minutesSinceCreated = $createdAt->diffInMinutes($now);
            
            if ($minutesSinceCreated > 5) {
                return back()->with('error', 'Không thể hủy lịch hẹn sau 5 phút kể từ khi đặt. Lịch hẹn đã được tự động xác nhận.');
            }
            
            // Lấy lý do hủy từ form hoặc dùng mặc định
            $reason = $request->input('cancellation_reason', 'Khách hàng tự hủy');
            if (empty(trim($reason))) {
                $reason = 'Khách hàng tự hủy';
            }
            
            // Hủy lịch hẹn
            $this->appointmentService->cancelAppointment($id, $reason, auth()->id());
            
            return back()->with('success', 'Lịch hẹn đã được hủy thành công.');
            
        } catch (\Exception $e) {
            \Log::error('Error canceling appointment: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi hủy lịch hẹn. Vui lòng thử lại.');
        }
    }

    /**
     * Get employees by service (chuyên môn).
     */
    public function getEmployeesByService(Request $request)
    {
        try {
            // Filter by service expertise - chỉ hiển thị nhân viên có chuyên môn phù hợp
            // Sử dụng request()->query() để lấy từ query string, request()->input() để lấy từ POST
            $serviceIds = $request->query('service_id', $request->input('service_id', []));
            $variantIds = $request->query('service_variants', $request->input('service_variants', []));
            $comboIds = $request->query('combo_id', $request->input('combo_id', []));
            
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
            
            // Lọc bỏ các giá trị null hoặc rỗng, nhưng giữ lại giá trị 0 nếu có
            $serviceIds = array_filter($serviceIds, function($value) {
                return $value !== null && $value !== '';
            });
            $variantIds = array_filter($variantIds, function($value) {
                return $value !== null && $value !== '';
            });
            $comboIds = array_filter($comboIds, function($value) {
                return $value !== null && $value !== '';
            });
            
            // Thu thập service IDs và phân biệt dịch vụ đơn vs dịch vụ biến thể
            $singleServiceIds = []; // Dịch vụ đơn (không có variants)
            $variantServiceIds = []; // Dịch vụ biến thể (có variants)
            
            // Lấy service IDs từ service_id (dịch vụ đơn)
            if (!empty($serviceIds)) {
                $singleServiceIds = array_merge($singleServiceIds, $serviceIds);
            }
            
            // Lấy service IDs từ service_variants (dịch vụ biến thể)
            if (!empty($variantIds)) {
                $variants = \App\Models\ServiceVariant::whereIn('id', $variantIds)->get();
                $variantServiceIds = $variants->pluck('service_id')->unique()->toArray();
            }
            
            // Lấy service IDs từ combo (có thể là dịch vụ biến thể)
            if (!empty($comboIds)) {
                $combos = \App\Models\Combo::with(['comboItems.serviceVariant.service', 'comboItems.service'])
                    ->whereIn('id', $comboIds)
                    ->get();
                
                foreach ($combos as $combo) {
                    if ($combo && $combo->comboItems) {
                        foreach ($combo->comboItems as $item) {
                            // Lấy service ID từ service_variant nếu có
                            if ($item->serviceVariant && $item->serviceVariant->service) {
                                $variantServiceIds[] = $item->serviceVariant->service->id;
                            }
                            // Lấy service ID trực tiếp nếu có (combo item có service_id nhưng không có variant)
                            elseif ($item->service_id) {
                                // Kiểm tra xem service này có variants không để phân loại
                                $service = \App\Models\Service::find($item->service_id);
                                if ($service) {
                                    $hasVariants = $service->serviceVariants()->whereNull('deleted_at')->exists();
                                    if ($hasVariants) {
                                        $variantServiceIds[] = $item->service_id;
                                    } else {
                                        $singleServiceIds[] = $item->service_id;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            // Loại bỏ trùng lặp
            $singleServiceIds = array_unique($singleServiceIds);
            $variantServiceIds = array_unique($variantServiceIds);
            
            // Khởi tạo $employees là collection rỗng
            $employees = collect([]);
            
            // Query nhân viên dựa trên chuyên môn được set trong admin
            // Phân biệt dịch vụ đơn và dịch vụ biến thể
            if (!empty($singleServiceIds) || !empty($variantServiceIds)) {
                $employeeIds = [];
                
                // Nếu có dịch vụ đơn được chọn, chỉ lấy nhân viên có chuyên môn là dịch vụ đơn
                if (!empty($singleServiceIds)) {
                    $singleServiceIds = array_map('intval', $singleServiceIds);
                    $singleServiceIds = array_filter($singleServiceIds);
                    $singleServiceIds = array_values($singleServiceIds);
                    
                    if (!empty($singleServiceIds)) {
                        // Lấy danh sách service_id là dịch vụ đơn (không có variants)
                        $validSingleServiceIds = \App\Models\Service::whereIn('id', $singleServiceIds)
                            ->whereNull('deleted_at')
                            ->whereDoesntHave('serviceVariants')
                            ->pluck('id')
                            ->toArray();
                        
                        if (!empty($validSingleServiceIds)) {
                            // Lấy employee_id từ employee_skills có service_id là dịch vụ đơn
                            $singleEmployeeIds = \DB::table('employee_skills')
                                ->whereIn('service_id', $validSingleServiceIds)
                                ->whereNotNull('employee_id')
                                ->whereNotNull('service_id')
                                ->distinct()
                                ->pluck('employee_id')
                                ->toArray();
                            
                            $employeeIds = array_merge($employeeIds, $singleEmployeeIds);
                        }
                    }
                }
                
                // Nếu có dịch vụ biến thể được chọn, chỉ lấy nhân viên có chuyên môn là dịch vụ biến thể
                if (!empty($variantServiceIds)) {
                    $variantServiceIds = array_map('intval', $variantServiceIds);
                    $variantServiceIds = array_filter($variantServiceIds);
                    $variantServiceIds = array_values($variantServiceIds);
                    
                    if (!empty($variantServiceIds)) {
                        // Lấy danh sách service_id là dịch vụ biến thể (có variants)
                        $validVariantServiceIds = \App\Models\Service::whereIn('id', $variantServiceIds)
                            ->whereNull('deleted_at')
                            ->whereHas('serviceVariants')
                            ->pluck('id')
                            ->toArray();
                        
                        if (!empty($validVariantServiceIds)) {
                            // Lấy employee_id từ employee_skills có service_id là dịch vụ biến thể
                            $variantEmployeeIds = \DB::table('employee_skills')
                                ->whereIn('service_id', $validVariantServiceIds)
                                ->whereNotNull('employee_id')
                                ->whereNotNull('service_id')
                                ->distinct()
                                ->pluck('employee_id')
                                ->toArray();
                            
                            $employeeIds = array_merge($employeeIds, $variantEmployeeIds);
                        }
                    }
                }
                
                // Loại bỏ trùng lặp
                $employeeIds = array_unique($employeeIds);
                $employeeIds = array_map('intval', $employeeIds);
                $employeeIds = array_filter($employeeIds);
                $employeeIds = array_values($employeeIds);
                
                if (!empty($employeeIds)) {
                    // Query nhân viên dựa trên employee_id từ employee_skills
                    $employees = \App\Models\Employee::with(['user.role', 'services'])
                        ->whereIn('id', $employeeIds)
                        ->whereNotNull('user_id')
                        ->where('status', '!=', 'Vô hiệu hóa')
                        ->whereHas('user', function($query) {
                            // Loại trừ admin
                            $query->where('role_id', '!=', 1);
                        })
                        ->get()
                        // Lọc lại để đảm bảo nhân viên chỉ có chuyên môn phù hợp với loại dịch vụ đã chọn
                        ->filter(function($employee) use ($singleServiceIds, $variantServiceIds) {
                            $employeeServiceIds = $employee->services->pluck('id')->toArray();
                            
                            if (empty($employeeServiceIds)) {
                                return false;
                            }
                            
                            // Nếu chọn dịch vụ đơn, chỉ giữ lại nhân viên có chuyên môn là dịch vụ đơn
                            if (!empty($singleServiceIds) && empty($variantServiceIds)) {
                                // Kiểm tra xem nhân viên có service_id nào trùng với dịch vụ đơn đã chọn không
                                $hasMatchingService = !empty(array_intersect($employeeServiceIds, $singleServiceIds));
                                
                                if (!$hasMatchingService) {
                                    return false;
                                }
                                
                                // Kiểm tra xem tất cả chuyên môn của nhân viên có phải là dịch vụ đơn không
                                // Lấy tất cả service_id của nhân viên và kiểm tra xem có dịch vụ biến thể nào không
                                // Sử dụng whereHas để kiểm tra xem có service nào có variants không
                                $servicesWithVariants = \App\Models\Service::whereIn('id', $employeeServiceIds)
                                    ->whereNull('deleted_at')
                                    ->whereHas('serviceVariants', function($query) {
                                        $query->whereNull('deleted_at');
                                    })
                                    ->pluck('id')
                                    ->toArray();
                                
                                // Nếu nhân viên có bất kỳ chuyên môn nào là dịch vụ biến thể, loại bỏ
                                if (!empty($servicesWithVariants)) {
                                    return false;
                                }
                                
                                // Tất cả chuyên môn đều là dịch vụ đơn và có service_id trùng
                                return true;
                            }
                            
                            // Nếu chọn dịch vụ biến thể, chỉ giữ lại nhân viên có chuyên môn là dịch vụ biến thể
                            if (!empty($variantServiceIds) && empty($singleServiceIds)) {
                                // Kiểm tra xem nhân viên có service_id nào trùng với dịch vụ biến thể đã chọn không
                                $hasMatchingService = !empty(array_intersect($employeeServiceIds, $variantServiceIds));
                                
                                if (!$hasMatchingService) {
                                    return false;
                                }
                                
                                // Kiểm tra xem tất cả chuyên môn của nhân viên có phải là dịch vụ biến thể không
                                // Lấy tất cả service_id của nhân viên và kiểm tra xem có dịch vụ đơn nào không
                                // Sử dụng whereDoesntHave để kiểm tra xem có service nào không có variants không
                                $servicesWithoutVariants = \App\Models\Service::whereIn('id', $employeeServiceIds)
                                    ->whereNull('deleted_at')
                                    ->whereDoesntHave('serviceVariants', function($query) {
                                        $query->whereNull('deleted_at');
                                    })
                                    ->pluck('id')
                                    ->toArray();
                                
                                // Nếu nhân viên có bất kỳ chuyên môn nào là dịch vụ đơn, loại bỏ
                                if (!empty($servicesWithoutVariants)) {
                                    return false;
                                }
                                
                                // Tất cả chuyên môn đều là dịch vụ biến thể và có service_id trùng
                                return true;
                            }
                            
                            // Nếu chọn cả hai loại, giữ lại nhân viên có service_id trùng với bất kỳ dịch vụ nào đã chọn
                            if (!empty($singleServiceIds) && !empty($variantServiceIds)) {
                                $allSelectedServiceIds = array_merge($singleServiceIds, $variantServiceIds);
                                return !empty(array_intersect($employeeServiceIds, $allSelectedServiceIds));
                            }
                            
                            return false;
                        })
                        ->values();
                } else {
                    // Không có nhân viên nào có chuyên môn phù hợp
                    $employees = collect([]);
                }
            }

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

    /**
     * Show the offers selection page.
     */
    public function selectOffers(Request $request)
    {
        // Get selected services from query params to preserve them
        $serviceIds = $request->query('service_id', []);
        $variantIds = $request->query('service_variants', []);
        $comboIds = $request->query('combo_id', []);
        
        // Convert to arrays if needed
        if (!is_array($serviceIds)) {
            $serviceIds = $serviceIds ? [$serviceIds] : [];
        }
        if (!is_array($variantIds)) {
            $variantIds = $variantIds ? [$variantIds] : [];
        }
        if (!is_array($comboIds)) {
            $comboIds = $comboIds ? [$comboIds] : [];
        }
        
        // Filter out empty values
        $serviceIds = array_filter($serviceIds, function($value) {
            return !empty($value) && $value !== '0';
        });
        $variantIds = array_filter($variantIds, function($value) {
            return !empty($value) && $value !== '0';
        });
        $comboIds = array_filter($comboIds, function($value) {
            return !empty($value) && $value !== '0';
        });
        
        // Load promotions/offers from database
        $now = now();
        
        // Public offers (Ưu đãi từ 30Shine) - All active promotions
        $publicOffers = \App\Models\Promotion::where('status', 'active')
            ->whereNull('deleted_at')
            ->where(function($query) use ($now) {
                $query->whereNull('start_date')
                      ->orWhere('start_date', '<=', $now);
            })
            ->where(function($query) use ($now) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', $now);
            })
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Personal offers (Ưu đãi của riêng anh) - For logged in users
        $personalOffers = collect([]);
        if (Auth::check()) {
            // Load user-specific promotions if any (can be extended later)
            $personalOffers = \App\Models\Promotion::where('status', 'active')
                ->whereNull('deleted_at')
                ->where(function($query) use ($now) {
                    $query->whereNull('start_date')
                          ->orWhere('start_date', '<=', $now);
                })
                ->where(function($query) use ($now) {
                    $query->whereNull('end_date')
                          ->orWhere('end_date', '>=', $now);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        return view('site.appointment.select-offers', compact(
            'serviceIds',
            'variantIds',
            'comboIds',
            'publicOffers',
            'personalOffers'
        ));
    }
}
