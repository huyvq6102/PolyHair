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
        // Lấy tất cả danh mục có dịch vụ hoặc combo, sắp xếp theo bảng chữ cái
        $categories = \App\Models\ServiceCategory::with([
                'services' => function($query) {
                    $query->whereNull('deleted_at')
                        ->where('status', 'Hoạt động')
                        ->with('serviceVariants')
                        ->orderBy('name', 'asc'); // Sắp xếp dịch vụ theo bảng chữ cái
                },
                'combos' => function($query) {
                    $query->whereNull('deleted_at')
                        ->where('status', 'Hoạt động')
                        ->with('comboItems.serviceVariant')
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
        $combosWithoutCategory = \App\Models\Combo::with('comboItems.serviceVariant')
            ->whereNull('deleted_at')
            ->where('status', 'Hoạt động')
            ->whereNull('category_id')
            ->orderBy('name', 'asc')
            ->get();


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

        // Load promotion nếu có promotion_id trong URL
        $selectedPromotion = null;
        $promotionForJs = null;

        if ($request->has('promotion_id') && $request->input('promotion_id')) {
            $promotionId = $request->input('promotion_id');
            $selectedPromotion = \App\Models\Promotion::with(['services', 'combos', 'serviceVariants'])
                ->whereNull('deleted_at')
                ->where('status', 'active')
                ->find($promotionId);

            if ($selectedPromotion) {
                // Prepare promotion data for JavaScript
                $promotionForJs = [
                    'id' => $selectedPromotion->id,
                    'name' => $selectedPromotion->name,
                    'discount_type' => $selectedPromotion->discount_type,
                    'discount_percent' => $selectedPromotion->discount_percent ?? 0,
                    'discount_amount' => $selectedPromotion->discount_amount ?? 0,
                    'max_discount_amount' => $selectedPromotion->max_discount_amount ?? null,
                    'apply_scope' => $selectedPromotion->apply_scope,
                    'service_ids' => $selectedPromotion->services->pluck('id')->toArray(),
                    'variant_ids' => $selectedPromotion->serviceVariants->pluck('id')->toArray(),
                    'combo_ids' => $selectedPromotion->combos->pluck('id')->toArray()
                ];
            }
        }

        return view('site.appointment.select-services', compact('categories', 'combosWithoutCategory', 'selectedPromotion', 'promotionForJs', 'activePromotions'));
    }

    /**
     * Show the offers selection page.
     */
    public function selectOffers(Request $request)
    {
        // Get selected services, variants, and combos from query parameters
        $serviceIds = $request->query('service_id', []);
        $variantIds = $request->query('service_variants', []);
        $comboIds = $request->query('combo_id', []);

        // Convert to arrays if single values
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
        $serviceIds = array_filter($serviceIds, function($id) {
            return !empty($id) && $id !== '0' && $id !== 0 && is_numeric($id);
        });
        $variantIds = array_filter($variantIds, function($id) {
            return !empty($id) && $id !== '0' && $id !== 0 && is_numeric($id);
        });
        $comboIds = array_filter($comboIds, function($id) {
            return !empty($id) && $id !== '0' && $id !== 0 && is_numeric($id);
        });

        // Get all active promotions
        $now = Carbon::now();
        $allPromotions = \App\Models\Promotion::with(['services', 'combos', 'serviceVariants'])
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->where(function($query) use ($now) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $now);
            })
            ->where(function($query) use ($now) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $now);
            })
            ->get();

        // Filter applicable promotions
        $applicablePromotions = collect();

        foreach ($allPromotions as $promotion) {
            $isApplicable = false;

            // If promotion applies to all (no specific services/variants/combos selected)
            $hasSpecificItems = $promotion->services->count() > 0
                || $promotion->combos->count() > 0
                || $promotion->serviceVariants->count() > 0;

            // If promotion has many items selected (>= 20), treat as "apply to all"
            $totalSelected = $promotion->services->count()
                + $promotion->combos->count()
                + $promotion->serviceVariants->count();

            $applyToAll = !$hasSpecificItems || $totalSelected >= 20;

            if ($applyToAll) {
                // Promotion applies to all services
                $isApplicable = true;
            } else {
                // Check if any selected service matches
                foreach ($serviceIds as $serviceId) {
                    if ($promotion->services->contains('id', $serviceId)) {
                        $isApplicable = true;
                        break;
                    }
                }

                // Check if any selected variant matches
                if (!$isApplicable) {
                    foreach ($variantIds as $variantId) {
                        if ($promotion->serviceVariants->contains('id', $variantId)) {
                            $isApplicable = true;
                            break;
                        }
                        // Also check if variant's parent service is in promotion
                        $variant = \App\Models\ServiceVariant::find($variantId);
                        if ($variant && $variant->service_id && $promotion->services->contains('id', $variant->service_id)) {
                            $isApplicable = true;
                            break;
                        }
                    }
                }

                // Check if any selected combo matches
                if (!$isApplicable) {
                    foreach ($comboIds as $comboId) {
                        if ($promotion->combos->contains('id', $comboId)) {
                            $isApplicable = true;
                            break;
                        }
                    }
                }
            }

            if ($isApplicable) {
                $applicablePromotions->push($promotion);
            }
        }

        // For now, all applicable promotions are public offers
        // Personal offers could be filtered by user_id if that field exists in the future
        $publicOffers = $applicablePromotions;
        $personalOffers = collect(); // Empty for now, can be extended later

        return view('site.appointment.select-offers', compact('publicOffers', 'personalOffers'));

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

            // Giữ lại appointment_date và word_time_id từ Session hoặc request
            if (Session::has('appointment_date')) {
                $queryParams['appointment_date'] = Session::get('appointment_date');
            }
            if (Session::has('word_time_id')) {
                $queryParams['word_time_id'] = Session::get('word_time_id');
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

            // Giữ lại appointment_date và word_time_id từ Session hoặc request
            if (Session::has('appointment_date')) {
                $queryParams['appointment_date'] = Session::get('appointment_date');
            }
            if (Session::has('word_time_id')) {
                $queryParams['word_time_id'] = Session::get('word_time_id');
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

            // Giữ lại appointment_date và word_time_id từ Session hoặc request
            if (Session::has('appointment_date')) {
                $queryParams['appointment_date'] = Session::get('appointment_date');
            }
            if (Session::has('word_time_id')) {
                $queryParams['word_time_id'] = Session::get('word_time_id');
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

        // Lấy TẤT CẢ nhân viên từ trang quản lý (không filter theo chuyên môn)
        // Chỉ loại trừ admin và nhân viên bị vô hiệu hóa
        // CHỈ LẤY CÁC STYLIST (không lấy barber, shampooer, receptionist, etc.)
        $employees = \App\Models\Employee::with(['user.role', 'services'])
                        ->whereNotNull('user_id')
                        ->where('status', '!=', 'Vô hiệu hóa')
                        ->where('position', 'Stylist') // Chỉ lấy stylist
                        ->whereHas('user', function($query) {
                            // Loại trừ admin
                            $query->where('role_id', '!=', 1);
                        })
            ->orderBy('id', 'desc')
            ->get();

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

        // Restore appointment_date và word_time_id từ Session nếu có
        // Ưu tiên lấy từ request (query params), nếu không có thì lấy từ Session
        $restoredAppointmentDate = $request->input('appointment_date') ?? Session::get('appointment_date');
        $restoredWordTimeId = $request->input('word_time_id') ?? Session::get('word_time_id');

        // Load promotion nếu có promotion_id trong URL
        $selectedPromotion = null;
        if ($request->has('promotion_id') && $request->input('promotion_id')) {
            $promotionId = $request->input('promotion_id');
            $selectedPromotion = \App\Models\Promotion::with(['services', 'combos', 'serviceVariants'])
                ->whereNull('deleted_at')
                ->where('status', 'active')
                ->find($promotionId);
        }

        // Get all active promotions for automatic discount calculation (only service-level promotions)
        $now = Carbon::now();
        $activePromotions = \App\Models\Promotion::with(['services', 'combos', 'serviceVariants'])
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->where('apply_scope', 'service') // Chỉ lấy promotion có apply_scope = 'service'
            ->where(function($query) use ($now) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $now);
            })
            ->where(function($query) use ($now) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $now);
            })
            ->get();

        return view('site.appointment.create', compact(
            'employees',
            'wordTimes',
            'serviceCategories',
            'combos',
            'restoredAppointmentDate',
            'restoredWordTimeId',
            'selectedPromotion',
            'activePromotions'
        ));
    }

    /**
     * Helper function to calculate discount for an item (service/variant/combo)
     * Logic must match with service-list-items.blade.php and create.blade.php
     */
    protected function calculateDiscountForItem($item, $itemType, $activePromotions)
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

            // Check usage_limit - if promotion has reached its limit, skip it
            if ($promo->usage_limit) {
                $totalUsage = \App\Models\PromotionUsage::where('promotion_id', $promo->id)->count();
                if ($totalUsage >= $promo->usage_limit) {
                    continue; // Skip this promotion, use original price
                }
            }

            // Check per_user_limit - if user has reached their limit, skip it
            // CHỈ đếm các PromotionUsage có appointment đã thanh toán
            if ($promo->per_user_limit) {
                $userId = auth()->id();
                if ($userId) {
                    $userUsage = \App\Models\PromotionUsage::where('promotion_id', $promo->id)
                        ->where('user_id', $userId)
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
        \Log::info('Final price :' . json_encode([
            'originalPrice' => $originalPrice,
            'discount' => $discount,
            'finalPrice' => $finalPrice > 0 ? $finalPrice : $originalPrice,
            'promotion' => $promotion
        ]));

        return [
            'originalPrice' => $originalPrice,
            'discount' => $discount,
            'finalPrice' => $finalPrice > 0 ? $finalPrice : $originalPrice,
            'promotion' => $promotion
        ];
    }

    /**
     * Store a new appointment.
     */
    public function store(Request $request)
    {
        try {
            // Xử lý employee_id trước để validate đúng
            $employeeIdInput = $request->input('employee_id');
            // Validation rules
            $validationRules = [
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
            ];

            $validated = $request->validate($validationRules, [
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

            // Get user if logged in, otherwise store guest info in appointment
            $user = null;
            $guestInfo = [];
            if (Auth::check()) {
                $user = Auth::user();
            } else {
                // Store guest info to save directly in appointment (not create User)
                $guestInfo = [
                    'guest_name' => $validated['name'],
                    'guest_phone' => $validated['phone'],
                    'guest_email' => $validated['email'] ?? null,
                ];
            }

            // Get word time
            $wordTime = $this->wordTimeService->getOne($validated['word_time_id']);

            // Calculate start and end time
            $appointmentDate = Carbon::parse($validated['appointment_date']);
            $timeString = $wordTime->formatted_time; // Use formatted_time to ensure H:i format

            // Load active promotions for automatic discount calculation (only service-level promotions)
            $now = Carbon::now();
            $activePromotions = \App\Models\Promotion::with(['services', 'combos', 'serviceVariants'])
                ->whereNull('deleted_at')
                ->where('status', 'active')
                ->where('apply_scope', 'service') // Only get promotions with apply_scope = 'service'
                ->where(function($query) use ($now) {
                    $query->where(function($q) use ($now) {
                        $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
                    })->where(function($q) use ($now) {
                        $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
                    });
                })
                ->get();

            // Calculate total duration from selected service variants, service, or combo FIRST
            // (Cần tính trước để dùng cho việc tìm nhân viên còn trống)
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
                        $variant = \App\Models\ServiceVariant::with('service')->findOrFail($variantId);
                        $totalDuration += $variant->duration ?? 60; // Default 60 minutes if not set

                        // Calculate discount for this variant
                        $discountResult = $this->calculateDiscountForItem($variant, 'variant', $activePromotions);
                        $finalPrice = $discountResult['finalPrice'];

                        // Log để debug
                        \Log::info('Appointment store - Variant discount calculation', [
                            'variant_id' => $variantId,
                            'variant_name' => $variant->name,
                            'original_price' => $variant->price,
                            'discount' => $discountResult['discount'],
                            'final_price' => $finalPrice,
                            'promotion_id' => $discountResult['promotion']->id ?? null,
                            'active_promotions_count' => $activePromotions->count(),
                        ]);

                        $serviceVariantData[] = [
                            'service_variant_id' => $variantId,
                            'employee_id' => $validated['employee_id'] ?? null,
                            'price_snapshot' => $finalPrice, // Save price after discount
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

                    // Use duration from combo if available, otherwise calculate from combo items
                    if (!is_null($combo->duration)) {
                        $comboDuration = $combo->duration;
                    } else {
                        // Calculate from combo items if duration not set
                        $comboDuration = 60; // Default
                        if ($combo->comboItems && $combo->comboItems->count() > 0) {
                            $comboDuration = $combo->comboItems->sum(function($item) {
                                return $item->serviceVariant->duration ?? 60;
                            });
                        }
                    }
                    $totalDuration += $comboDuration;

                    // Calculate discount for this combo
                    $discountResult = $this->calculateDiscountForItem($combo, 'combo', $activePromotions);
                    $finalPrice = $discountResult['finalPrice'];

                    $serviceVariantData[] = [
                        'service_variant_id' => null,
                        'combo_id' => $combo->id,
                        'employee_id' => $validated['employee_id'] ?? null,
                        'price_snapshot' => $finalPrice, // Save price after discount
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

                    // Calculate discount for this service
                    $discountResult = $this->calculateDiscountForItem($service, 'service', $activePromotions);

                    $finalPrice = $discountResult['finalPrice'];

                    $serviceVariantData[] = [
                        'service_variant_id' => null, // No variant selected
                        'employee_id' => $validated['employee_id'] ?? null,
                        'price_snapshot' => $finalPrice, // Save price after discount
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

            // Xử lý tự động chọn nhân viên nếu employee_id là "auto" hoặc null
            // (Phải tính totalDuration trước)
            $selectedEmployeeId = $validated['employee_id'] ?? null;

            // ✅ QUAN TRỌNG: Xử lý selectedEmployeeId TRƯỚC khi tạo appointment
            // để đảm bảo serviceVariantData có employee_id đúng

            // Log để debug
            // Validate employee exists
            $employee = \App\Models\Employee::find($selectedEmployeeId);
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kỹ thuật viên không tồn tại.'
                ], 422);
            }
            foreach ($serviceVariantData as &$variantData) {
                $variantData['employee_id'] = $selectedEmployeeId;
            }
            unset($variantData); // Unset reference để tránh side effects

            $startAt = Carbon::parse($appointmentDate->format('Y-m-d') . ' ' . $timeString);
            $endAt = $startAt->copy()->addMinutes($totalDuration);

            // Log final serviceVariantData before creating appointment
            \Log::info('Appointment store - Final serviceVariantData', [
                'count' => count($serviceVariantData),
                'selected_employee_id' => $selectedEmployeeId,
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
            $appointmentData = [
                'user_id' => $user ? $user->id : null,
                'employee_id' => $selectedEmployeeId, // Sử dụng nhân viên đã được chọn (tự động hoặc thủ công)
                'status' => 'Chờ xử lý',
                'start_at' => $startAt,
                'end_at' => $endAt,
                'note' => $validated['note'] ?? null,
            ];

            // Add guest info if not logged in
            if (!empty($guestInfo)) {
                $appointmentData = array_merge($appointmentData, $guestInfo);
            }

            $appointment = $this->appointmentService->create($appointmentData, $serviceVariantData);

            \Log::info('Appointment store - Created appointment', [
                'appointment_id' => $appointment->id,
                'details_count' => $appointment->appointmentDetails->count(),
                'expected_count' => count($serviceVariantData),
            ]);

            // KHÔNG tạo PromotionUsage ở đây vì appointment chưa thanh toán
            // PromotionUsage sẽ được tạo sau khi thanh toán thành công thông qua:
            // - recordPromotionUsage() cho order-level/customer_tier promotions
            // - recordServiceLevelPromotionUsages() cho service-level promotions

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

            // Xác định redirect URL dựa trên trạng thái đăng nhập
            // Nếu là guest (không đăng nhập), redirect đến trang chi tiết lịch đặt
            // Nếu đã đăng nhập, redirect đến trang thanh toán
            $isGuest = !Auth::check();
            $redirectUrl = route('site.appointment.success', $appointment->id);

            $successMessage = $isGuest
                ? '<i class="fa fa-check-circle"></i> Đặt lịch thành công! Vui lòng kiểm tra thông tin lịch đặt của bạn.'
                : '<i class="fa fa-check-circle"></i> Đặt lịch thành công! Lịch hẹn của bạn đã được thêm vào giỏ hàng. Vui lòng thanh toán để hoàn tất đặt lịch.';

            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'appointment_id' => $appointment->id,
                'redirect_url' => $redirectUrl,
                'is_guest' => $isGuest,
                'cart_count' => count($cart),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            // Log lỗi chi tiết để debug
            \Log::error('Appointment store - Exception occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi đặt lịch: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'error_details' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null
            ], 500);
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
            'reviews',
            'promotionUsages.promotion'
        ])->findOrFail($id);

        // Calculate total price (price_snapshot already includes service-level discount)
        $totalAfterServiceLevel = 0;
        $totalOriginalPrice = 0;
        $serviceLevelDiscount = 0;

        foreach ($appointment->appointmentDetails as $detail) {
            $priceAfterDiscount = $detail->price_snapshot ?? 0;
            $totalAfterServiceLevel += $priceAfterDiscount;

            // Calculate original price and discount for display
            $originalPrice = 0;
            if ($detail->serviceVariant && $detail->serviceVariant->price) {
                $originalPrice = $detail->serviceVariant->price;
            } elseif ($detail->combo && $detail->combo->price) {
                $originalPrice = $detail->combo->price;
            } elseif ($detail->serviceVariant && $detail->serviceVariant->service && $detail->serviceVariant->service->base_price) {
                // Fallback: get from parent service
                $originalPrice = $detail->serviceVariant->service->base_price;
            } elseif ($detail->notes) {
                // For service without variant
                $service = \App\Models\Service::where('name', $detail->notes)->first();
                if ($service) {
                    $originalPrice = $service->base_price ?? 0;
                } else {
                    $originalPrice = $priceAfterDiscount;
                }
            } else {
                // If we can't determine original price, use price_snapshot (no discount was applied)
                $originalPrice = $priceAfterDiscount;
            }

            $totalOriginalPrice += $originalPrice;
            $serviceLevelDiscount += max(0, $originalPrice - $priceAfterDiscount);
        }

        // Tính order-level discount từ Payment (giống admin)
        $orderLevelDiscount = 0;
        $orderLevelPromotionCode = null;
        $payment = \App\Models\Payment::where('appointment_id', $appointment->id)->orderBy('created_at', 'desc')->first();
        if ($payment && $payment->total > 0 && $totalAfterServiceLevel > 0) {
            // Order-level discount = tổng sau service-level discount - tổng trong payment
            $orderLevelDiscount = max(0, $totalAfterServiceLevel - $payment->total);
            
            // Tìm mã khuyến mại order-level từ promotionUsages
            if ($orderLevelDiscount > 0 && $appointment->promotionUsages) {
                foreach ($appointment->promotionUsages as $usage) {
                    if ($usage->promotion && in_array($usage->promotion->apply_scope, ['order', 'customer_tier'])) {
                        $orderLevelPromotionCode = $usage->promotion->code;
                        break;
                    }
                }
            }
        }

        // Tổng thanh toán cuối cùng
        $totalPrice = max(0, $totalAfterServiceLevel - $orderLevelDiscount);
        $totalDiscount = $serviceLevelDiscount + $orderLevelDiscount;

        // Check if user can review (appointment completed and not reviewed yet)
        $canReview = false;
        $existingReview = null;

        if (auth()->check() && $appointment->status === 'Hoàn thành' && $appointment->user_id == auth()->id()) {
            $existingReview = \App\Models\Review::where('appointment_id', $appointment->id)
                ->where('user_id', auth()->id())
                ->first();
            $canReview = !$existingReview;
        }

        return view('site.appointment.show', compact(
            'appointment', 
            'totalPrice', 
            'totalOriginalPrice', 
            'serviceLevelDiscount',
            'orderLevelDiscount',
            'orderLevelPromotionCode',
            'totalDiscount', 
            'canReview', 
            'existingReview'
        ));
    }

    /**
     * Show success page after booking.
     */
    public function success($id)
    {
        $appointment = \App\Models\Appointment::with([
            'user',
            'employee.user',
            'appointmentDetails.serviceVariant.service',
            'appointmentDetails.serviceVariant.variantAttributes',
            'appointmentDetails.combo',
            'promotionUsages.promotion'
        ])->findOrFail($id);

        // Tính tổng tiền từ appointment details (price_snapshot đã bao gồm service-level discount)
        $subtotal = 0;
        $totalOriginalPrice = 0;
        $serviceLevelDiscount = 0; // Discount từ service-level promotions (đã áp dụng vào price_snapshot)

        foreach ($appointment->appointmentDetails as $detail) {
            $priceAfterDiscount = $detail->price_snapshot ?? 0;
            $subtotal += $priceAfterDiscount;

            // Calculate original price for display
            $originalPrice = 0;
            if ($detail->serviceVariant) {
                $originalPrice = $detail->serviceVariant->price ?? 0;
            } elseif ($detail->combo) {
                $originalPrice = $detail->combo->price ?? 0;
            } elseif ($detail->notes) {
                // For service without variant
                $service = \App\Models\Service::where('name', $detail->notes)->first();
                if ($service) {
                    $originalPrice = $service->base_price ?? 0;
                } else {
                    $originalPrice = $priceAfterDiscount;
                }
            } else {
                // Fallback
                $originalPrice = $priceAfterDiscount;
            }

            $totalOriginalPrice += $originalPrice;
            $serviceLevelDiscount += max(0, $originalPrice - $priceAfterDiscount);
        }

        // Tính order-level discount từ Payment (giống admin)
        $orderLevelPromotionAmount = 0;
        $orderLevelPromotionCode = null;
        $payment = \App\Models\Payment::where('appointment_id', $appointment->id)->orderBy('created_at', 'desc')->first();
        if ($payment && $payment->total > 0 && $subtotal > 0) {
            // Order-level discount = tổng sau service-level discount - tổng trong payment
            $orderLevelPromotionAmount = max(0, $subtotal - $payment->total);
            
            // Tìm mã khuyến mại order-level từ promotionUsages
            if ($orderLevelPromotionAmount > 0 && $appointment->promotionUsages) {
                foreach ($appointment->promotionUsages as $usage) {
                    if ($usage->promotion && in_array($usage->promotion->apply_scope, ['order', 'customer_tier'])) {
                        $orderLevelPromotionCode = $usage->promotion->code;
                        break;
                    }
                }
            }
        }

        // Tính tổng sau giảm giá (đã bao gồm service-level discount trong price_snapshot)
        // Trừ thêm order-level promotion nếu có
        $totalAfterDiscount = max(0, $subtotal - $orderLevelPromotionAmount);
        $totalDiscount = $serviceLevelDiscount + $orderLevelPromotionAmount;

        return view('site.appointment.success', [
            'appointment' => $appointment,
            'subtotal' => $subtotal,
            'totalOriginalPrice' => $totalOriginalPrice,
            'serviceLevelDiscount' => $serviceLevelDiscount,
            'orderLevelPromotionAmount' => $orderLevelPromotionAmount,
            'orderLevelPromotionCode' => $orderLevelPromotionCode,
            'totalDiscount' => $totalDiscount,
            'totalAfterDiscount' => $totalAfterDiscount,
        ]);
    }

    /**
     * Save appointment date and word_time_id to session.
     * Called when user selects a time slot.
     */
    public function saveTimeSelection(Request $request)
    {
        $request->validate([
            'appointment_date' => 'required|date',
            'word_time_id' => 'required|exists:word_time,id',
        ]);

        Session::put('appointment_date', $request->input('appointment_date'));
        Session::put('word_time_id', $request->input('word_time_id'));

        return response()->json([
            'success' => true,
            'message' => 'Time selection saved'
        ]);
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
            // Xử lý employee_id trước để validate đúng
            $employeeId = $request->input('employee_id');
            // Convert empty string, '0', or 'auto' to null
            if ($employeeId === '' || $employeeId === '0' || $employeeId === 'auto') {
                $employeeId = null;
            }

            // Validate các trường khác
            $request->validate([
                'appointment_date' => 'required|date|after_or_equal:today',
                'total_duration' => 'nullable|integer|min:1', // Tổng thời gian dịch vụ đã chọn (phút)
            ]);

            // Validate employee_id exists nếu không phải null
            if ($employeeId !== null) {
                $employee = \App\Models\Employee::find($employeeId);
                if (!$employee) {
                    return response()->json([
                        'success' => false,
                        'time_slots' => [],
                        'message' => 'Kỹ thuật viên không tồn tại.'
                    ], 422);
                }
            }
            $appointmentDate = Carbon::parse($request->input('appointment_date'));
            $totalDuration = (int)($request->input('total_duration') ?? 0); // Tổng thời gian dịch vụ (phút)

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

            // Validate employee_id is required
            if (!$employeeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn kỹ thuật viên.',
                    'time_slots' => []
                ], 422);
            }

            $timeSlots = [];
            $workingTimeRanges = [];

            // Get working schedules for the employee on the selected date
            $workingSchedules = \App\Models\WorkingSchedule::with('shift')
                ->where('employee_id', $employeeId)
                ->whereDate('work_date', $appointmentDate->format('Y-m-d'))
                ->whereNull('deleted_at')
                ->get();

            // Get employee
            $employee = \App\Models\Employee::find($employeeId);
            if ($employee) {
                \Log::info('Loading time slots for employee', [
                    'employee_id' => $employeeId,
                    'employee_name' => $employee->user->name ?? 'N/A',
                    'employee_position' => $employee->position ?? 'N/A',
                    'date' => $appointmentDate->format('Y-m-d'),
                    'working_schedules_count' => $workingSchedules->count()
                ]);
            }

            // Lưu các khoảng thời gian làm việc
            $schedulesToProcess = $workingSchedules;
            foreach ($schedulesToProcess as $schedule) {
                if (!$schedule->shift) {
                    \Log::warning('Working schedule has no shift', [
                        'schedule_id' => $schedule->id,
                        'employee_id' => $employeeId
                    ]);
                    continue;
                }

                $startTimeString = $schedule->shift->formatted_start_time;
                $endTimeString = $schedule->shift->formatted_end_time;

                if (!$startTimeString || !$endTimeString) {
                    \Log::warning('Working shift has no start/end time', [
                        'schedule_id' => $schedule->id,
                        'shift_id' => $schedule->shift_id,
                        'shift_name' => $schedule->shift->name ?? 'N/A',
                        'start_time_string' => $startTimeString,
                        'end_time_string' => $endTimeString
                    ]);
                    continue;
                }

                try {
                    $shiftStart = Carbon::createFromFormat('H:i', $startTimeString);
                    $shiftEnd = Carbon::createFromFormat('H:i', $endTimeString);
                } catch (\Exception $e) {
                    \Log::warning('Error parsing shift time, using Carbon::parse', [
                        'schedule_id' => $schedule->id,
                        'start_time_string' => $startTimeString,
                        'end_time_string' => $endTimeString,
                        'error' => $e->getMessage()
                    ]);
                    $shiftStart = Carbon::parse($startTimeString);
                    $shiftEnd = Carbon::parse($endTimeString);
                }

                $workingTimeRanges[] = [
                    'start' => $shiftStart,
                    'end' => $shiftEnd
                ];

                // Debug log cho Quang Lực
                if ($employee && $employee->user && strpos($employee->user->name ?? '', 'Quang') !== false) {
                    \Log::info('Added working time range for Quang Lực', [
                        'schedule_id' => $schedule->id,
                        'shift_name' => $schedule->shift->name ?? 'N/A',
                        'start_time_string' => $startTimeString,
                        'end_time_string' => $endTimeString,
                        'shift_start' => $shiftStart->format('H:i'),
                        'shift_end' => $shiftEnd->format('H:i'),
                        'total_ranges' => count($workingTimeRanges)
                    ]);
                }
            }

            // Debug log working time ranges
            if ($employee && $employee->user && strpos($employee->user->name ?? '', 'Quang') !== false) {
                \Log::info('Quang Lực working time ranges', [
                    'employee_id' => $employeeId,
                    'employee_name' => $employee->user->name,
                    'working_time_ranges' => array_map(function($range) {
                        return $range['start']->format('H:i') . ' - ' . $range['end']->format('H:i');
                    }, $workingTimeRanges),
                    'working_schedules' => $workingSchedules->map(function($s) {
                        return [
                            'id' => $s->id,
                            'shift_id' => $s->shift_id,
                            'shift_name' => $s->shift->name ?? 'N/A',
                            'start_time' => $s->shift->formatted_start_time ?? 'N/A',
                            'end_time' => $s->shift->formatted_end_time ?? 'N/A'
                        ];
                    })
                ]);
            }

            // Nếu không có lịch làm việc, vẫn hiển thị tất cả slots nhưng tất cả đều unavailable
            // (workingTimeRanges sẽ rỗng, nên isInWorkingTime sẽ luôn false)

            // Get booked appointments
            $appointmentsToProcess = \App\Models\Appointment::with('appointmentDetails')
                ->where('employee_id', $employeeId)
                ->whereDate('start_at', $appointmentDate->format('Y-m-d'))
                ->whereIn('status', ['Chờ xử lý', 'Đã xác nhận', 'Đang thực hiện'])
                ->get();

            // Lưu các khoảng thời gian đã bị đặt (start_at đến end_at)
            $bookedTimeRanges = [];

            foreach ($appointmentsToProcess as $appointment) {
                if ($appointment->start_at) {
                    $appointmentStart = Carbon::parse($appointment->start_at);

                    // Nếu có end_at, dùng end_at; nếu không, tính từ appointment details
                    if ($appointment->end_at) {
                        $appointmentEnd = Carbon::parse($appointment->end_at);
                    } else {
                        // Tính tổng thời gian từ appointment details
                        $totalDuration = 0;
                        $appointmentDetails = $appointment->appointmentDetails;
                        foreach ($appointmentDetails as $detail) {
                            $totalDuration += $detail->duration ?? 60; // Default 60 minutes
                        }
                        $appointmentEnd = $appointmentStart->copy()->addMinutes($totalDuration);
                    }

                    // Chỉ lưu nếu end > start và cùng ngày
                    if ($appointmentEnd->gt($appointmentStart) &&
                        $appointmentStart->format('Y-m-d') === $appointmentDate->format('Y-m-d')) {
                        $range = [
                            'start' => $appointmentStart->format('H:i'),
                            'end' => $appointmentEnd->format('H:i'),
                            'start_carbon' => $appointmentStart,
                            'end_carbon' => $appointmentEnd
                        ];

                        $bookedTimeRanges[] = $range;
                    }
                }
            }

            // Debug log
            \Log::info('Available time slots calculation', [
                'employee_id' => $employeeId,
                'date' => $appointmentDate->format('Y-m-d'),
                'total_duration' => $totalDuration,
                'total_duration_from_request' => $request->input('total_duration'),
                'booked_ranges' => array_map(function($range) {
                    return $range['start'] . ' - ' . $range['end'];
                }, $bookedTimeRanges),
                'working_ranges' => array_map(function($range) {
                    return $range['start']->format('H:i') . ' - ' . $range['end']->format('H:i');
                }, $workingTimeRanges),
                'appointments_count' => $appointmentsToProcess->count(),
                'request_total_duration' => $request->input('total_duration'),
                'note' => 'Nếu total_duration = 0, logic kiểm tra vượt quá ca sẽ không chạy'
            ]);

            // Tìm đơn đã hoàn thành trong ngày và lấy thời gian kết thúc
            // Nếu có đơn đã hoàn thành, đóng các slot SAU thời gian kết thúc đơn (vì thời gian thực đã qua)
            $completedAppointmentEndTime = null;

            $completedAppointment = \App\Models\Appointment::where('employee_id', $employeeId)
                ->whereDate('start_at', $appointmentDate->format('Y-m-d'))
                ->where('status', 'Hoàn thành')
                ->orderBy('end_at', 'desc')
                ->first();

            if ($completedAppointment && $completedAppointment->end_at) {
                $completedAppointmentEndTime = $completedAppointment->end_at->format('H:i');
                \Log::info('Found completed appointment', [
                    'appointment_id' => $completedAppointment->id,
                    'end_time' => $completedAppointmentEndTime,
                    'employee_id' => $employeeId,
                    'date' => $appointmentDate->format('Y-m-d')
                ]);
            }

            // Tạo TẤT CẢ time slots từ 7:00 đến 22:00 (mỗi 30 phút)
            // HIỂN THỊ TẤT CẢ slots, nhưng chỉ available nếu nằm trong ca làm việc
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
                // HIỂN THỊ TẤT CẢ slots từ 7h-22h, nhưng chỉ available nếu nằm trong ca làm việc
                $isInWorkingTime = false;
                $shiftEndTime = null; // Lưu thời gian kết thúc ca làm việc
                $slotTime = Carbon::createFromFormat('H:i', $timeString);


                // Debug log cho Quang Lực
                $shouldDebug = false;
                if ($employee && $employee->user && strpos($employee->user->name ?? '', 'Quang') !== false && $timeString >= '07:00' && $timeString <= '12:00') {
                    $shouldDebug = true;
                }

                if ($shouldDebug) {
                    \Log::info('Checking if slot is in working time', [
                        'slot' => $timeString,
                        'slot_time' => $slotTime->format('H:i'),
                        'working_time_ranges' => array_map(function($r) {
                            return $r['start']->format('H:i') . ' - ' . $r['end']->format('H:i');
                        }, $workingTimeRanges),
                        'working_time_ranges_count' => count($workingTimeRanges)
                    ]);
                }

                foreach ($workingTimeRanges as $range) {
                    // Kiểm tra slot có nằm trong khoảng [start, end) (không bao gồm end time)
                    // Ví dụ: ca sáng 7h-12h thì slots 7h00, 7h30, ..., 11h30 nằm trong ca
                    // Slot 12h00 KHÔNG nằm trong ca vì ca kết thúc lúc 12h00
                    // Điều này đảm bảo không thể bắt đầu dịch vụ vào lúc kết thúc ca
                    $isGte = $slotTime->gte($range['start']);
                    $isLt = $slotTime->lt($range['end']); // Sử dụng < thay vì <= để không bao gồm end time

                    if ($shouldDebug) {
                        \Log::info('Comparing slot with range', [
                            'slot' => $timeString,
                            'range_start' => $range['start']->format('H:i'),
                            'range_end' => $range['end']->format('H:i'),
                            'is_gte' => $isGte,
                            'is_lt' => $isLt,
                            'result' => ($isGte && $isLt ? 'true' : 'false')
                        ]);
                    }

                    if ($isGte && $isLt) {
                        $isInWorkingTime = true;
                        $shiftEndTime = $range['end']; // Lưu thời gian kết thúc ca
                        if ($shouldDebug) {
                            \Log::info('Slot is in working time', [
                                'slot' => $timeString,
                                'shift_end_time' => $shiftEndTime->format('H:i')
                            ]);
                        }
                        break;
                    }
                }

                if ($shouldDebug) {
                    \Log::info('Final result for slot', [
                        'slot' => $timeString,
                        'is_in_working_time' => $isInWorkingTime,
                        'shift_end_time' => $shiftEndTime ? $shiftEndTime->format('H:i') : 'null'
                    ]);
                }

                // Kiểm tra xem slot có bị đặt chưa
                // YÊU CẦU MỚI:
                // 1. Ẩn toàn bộ các mốc giờ nằm trong khoảng thời gian đã có đơn
                // 2. Nếu có nhiều đơn liên tiếp trong ngày, thì phải khóa toàn bộ khoảng từ thời gian bắt đầu của đơn đầu tiên đến thời gian kết thúc của đơn cuối cùng, kể cả khoảng trống giữa các đơn
                $isBooked = false;
                $conflictReason = null; // Lưu lý do trùng lịch để hiển thị thông báo

                if (count($bookedTimeRanges) > 0) {
                    // Tìm khoảng thời gian tổng thể: từ appointment đầu tiên đến appointment cuối cùng
                    $sortedRanges = $bookedTimeRanges;
                    usort($sortedRanges, function($a, $b) {
                        return strcmp($a['start'], $b['start']);
                    });

                    $firstAppointmentStart = $sortedRanges[0]['start'];
                    $lastAppointmentEnd = $sortedRanges[count($sortedRanges) - 1]['end'];

                    // Debug log
                    if ($timeString === '14:00') {
                        \Log::info('DEBUG 14:00 slot check', [
                            'slot' => $timeString,
                            'first_appointment_start' => $firstAppointmentStart,
                            'last_appointment_end' => $lastAppointmentEnd,
                            'booked_ranges' => $bookedTimeRanges,
                            'sorted_ranges' => array_map(function($r) {
                                return $r['start'] . '-' . $r['end'];
                            }, $sortedRanges),
                            'condition1' => ($timeString >= $firstAppointmentStart ? 'true' : 'false'),
                            'condition2' => ($timeString < $lastAppointmentEnd ? 'true' : 'false'),
                            'both_conditions' => (($timeString >= $firstAppointmentStart && $timeString < $lastAppointmentEnd) ? 'true' : 'false')
                        ]);
                    }

                    // Kiểm tra xem slot có nằm trong khoảng tổng thể không (từ đơn đầu tiên đến đơn cuối cùng)
                    // Điều này sẽ khóa cả khoảng trống giữa các đơn
                    if ($timeString >= $firstAppointmentStart && $timeString < $lastAppointmentEnd) {
                        $isBooked = true;
                        $conflictReason = "Nằm trong khoảng thời gian đã có đơn ({$firstAppointmentStart} - {$lastAppointmentEnd})";
                        \Log::info('Slot blocked - in overall range', [
                            'slot' => $timeString,
                            'first_start' => $firstAppointmentStart,
                            'last_end' => $lastAppointmentEnd
                        ]);
                    } else {
                        // Kiểm tra từng appointment riêng lẻ (cho trường hợp slot nằm trong một appointment cụ thể)
                        foreach ($bookedTimeRanges as $range) {
                            // Kiểm tra xem slot có nằm trong khoảng [start, end) của appointment không
                            // Slot bị coi là booked nếu nó >= start và < end
                            // So sánh string H:i (ví dụ: "07:00" < "08:30")
                            if ($timeString >= $range['start'] && $timeString < $range['end']) {
                                $isBooked = true;
                                $conflictReason = "Trùng với lịch đã đặt ({$range['start']} - {$range['end']})";
                                break;
                            }
                        }
                    }
                }

                // Kiểm tra xem nếu đặt lịch từ slot này có trùng với appointment đã có không
                // (Chỉ kiểm tra nếu slot chưa bị booked bởi logic trên và có duration)
                // Kiểm tra overlap: nếu appointment mới (từ slot đến slot + duration) có overlap với bất kỳ appointment nào
                if (!$isBooked && $totalDuration > 0 && $isInWorkingTime && count($bookedTimeRanges) > 0) {
                    $slotTime = Carbon::createFromFormat('H:i', $timeString);
                    $proposedEndTime = $slotTime->copy()->addMinutes($totalDuration);
                    $proposedEndTimeString = $proposedEndTime->format('H:i');

                    // Kiểm tra overlap với từng appointment
                    foreach ($bookedTimeRanges as $range) {
                        $rangeStart = $range['start'];
                        $rangeEnd = $range['end'];

                        // Trùng lịch nếu có overlap
                        // Overlap nếu: slot < rangeEnd && proposedEndTime > rangeStart
                        $hasOverlap = $timeString < $rangeEnd && $proposedEndTimeString > $rangeStart;

                        if ($hasOverlap) {
                            $isBooked = true;
                            $conflictReason = "Trùng với lịch đã đặt ({$rangeStart} - {$rangeEnd})";
                            break;
                        }
                    }
                }

                // Kiểm tra xem slot có vượt quá ca làm việc không
                // Nếu đặt lịch ở slot này, thời gian kết thúc phải <= thời gian kết thúc ca
                // Ví dụ: Ca kết thúc 12h00, slot 11h30 + 30p = 12h00 → cho phép (chưa quá 12h00)
                // Ví dụ: Ca kết thúc 12h00, slot 11h30 + 60p = 12h30 → không cho phép (vượt quá 12h00)

                // Debug log cho slot 11h30 với dịch vụ 60p
                if ($timeString === '11:30') {
                    \Log::info('DEBUG 11:30 slot check', [
                        'slot' => $timeString,
                        'total_duration' => $totalDuration,
                        'is_booked' => $isBooked,
                        'is_in_working_time' => $isInWorkingTime,
                        'shift_end_time' => $shiftEndTime ? $shiftEndTime->format('H:i') : 'null',
                        'condition_check' => [
                            '!isBooked' => !$isBooked,
                            'totalDuration > 0' => $totalDuration > 0,
                            'isInWorkingTime' => $isInWorkingTime,
                            'shiftEndTime exists' => $shiftEndTime !== null,
                            'all_conditions' => (!$isBooked && $totalDuration > 0 && $isInWorkingTime && $shiftEndTime)
                        ]
                    ]);
                }

                if (!$isBooked && $totalDuration > 0 && $isInWorkingTime && $shiftEndTime) {
                    // Tính thời gian kết thúc nếu đặt lịch ở slot này
                    $slotTime = Carbon::createFromFormat('H:i', $timeString);
                    $endTime = $slotTime->copy()->addMinutes($totalDuration);

                    // So sánh với thời gian kết thúc ca làm việc bằng Carbon objects để chính xác
                    // Nếu thời gian kết thúc > thời gian kết thúc ca, thì không cho phép đặt
                    // Nếu thời gian kết thúc = thời gian kết thúc ca, thì vẫn cho phép (chưa quá)
                    // Sử dụng Carbon::gt() (greater than) để so sánh chính xác
                    if ($endTime->gt($shiftEndTime)) {
                        $isBooked = true;
                        $endTimeString = $endTime->format('H:i');
                        $shiftEndTimeString = $shiftEndTime->format('H:i');

                        // Thông báo đơn giản cho dịch vụ trên 60 phút
                        if ($totalDuration > 60) {
                            $conflictReason = "Đã quá ca làm việc của nhân viên cho dịch vụ trên 60p";
                        } else {
                            $conflictReason = "Đã quá ca làm việc của nhân viên";
                        }

                        \Log::info('Time slot blocked - exceeds shift end', [
                            'slot' => $timeString,
                            'total_duration' => $totalDuration,
                            'end_time' => $endTimeString,
                            'shift_end' => $shiftEndTimeString,
                            'end_time_carbon' => $endTime->toTimeString(),
                            'shift_end_carbon' => $shiftEndTime->toTimeString(),
                            'comparison_result' => $endTime->gt($shiftEndTime) ? 'true' : 'false',
                            'employee_id' => $employeeId,
                            'date' => $appointmentDate->format('Y-m-d'),
                            'conflict_reason' => $conflictReason
                        ]);
                    } else {
                        // Debug log để kiểm tra các trường hợp được cho phép
                        if ($timeString === '11:30' && $totalDuration >= 30) {
                            $endTimeString = $endTime->format('H:i');
                            $shiftEndTimeString = $shiftEndTime->format('H:i');
                            \Log::info('Time slot allowed - within shift end', [
                                'slot' => $timeString,
                                'total_duration' => $totalDuration,
                                'end_time' => $endTimeString,
                                'shift_end' => $shiftEndTimeString,
                                'comparison_result' => $endTime->gt($shiftEndTime) ? 'exceeds' : 'within',
                                'employee_id' => $employeeId
                            ]);
                        }
                    }
                } else {
                    // Debug log khi điều kiện không thỏa mãn
                    if ($timeString === '11:30') {
                        \Log::warning('Time slot check skipped - conditions not met', [
                            'slot' => $timeString,
                            'total_duration' => $totalDuration,
                            'is_booked' => $isBooked,
                            'is_in_working_time' => $isInWorkingTime,
                            'shift_end_time' => $shiftEndTime ? $shiftEndTime->format('H:i') : 'null',
                            'reason' => [
                                'isBooked' => $isBooked ? 'true (blocked)' : 'false',
                                'totalDuration' => $totalDuration > 0 ? "{$totalDuration} (OK)" : '0 (missing)',
                                'isInWorkingTime' => $isInWorkingTime ? 'true (OK)' : 'false (not in shift)',
                                'shiftEndTime' => $shiftEndTime ? 'exists (OK)' : 'null (missing)'
                            ]
                        ]);
                    }
                }

                // Kiểm tra xem slot có trước giờ hiện tại không (nếu là ngày hôm nay)
                // Nếu slot đã qua (slot <= current time), thì không cho phép đặt
                // Ví dụ: Hiện tại 10h00, thì slot 7h00, 7h30, ..., 9h30, 10h00 đều đã qua → không cho phép
                $isPastTime = false;
                if ($isToday) {
                    // So sánh bằng Carbon objects để chính xác, sử dụng cùng timezone
                    $slotTime = Carbon::createFromFormat('H:i', $timeString);
                    $now = Carbon::now('Asia/Ho_Chi_Minh'); // Đảm bảo cùng timezone với $now ở đầu function

                    // Chuyển slot time sang cùng ngày với now để so sánh
                    $slotDateTime = Carbon::create(
                        $now->year,
                        $now->month,
                        $now->day,
                        $slotTime->hour,
                        $slotTime->minute,
                        0,
                        'Asia/Ho_Chi_Minh' // Đảm bảo cùng timezone
                    );

                    // Slot đã qua nếu slot <= current time (bao gồm cả slot hiện tại)
                    // Ví dụ: Hiện tại 10h00, slot 7h00-10h00 đều đã qua
                    // Ví dụ: Hiện tại 10h15, slot 7h00-10h30 đều đã qua
                    if ($slotDateTime->lte($now)) {
                        $isPastTime = true;
                    }
                }

                // Kiểm tra xem slot có sau thời gian hoàn thành đơn không (nếu là ngày hôm nay)
                // Nếu có đơn hoàn thành lúc 9h, thì các slot sau 9h phải đóng vì thời gian thực đã qua
                $isAfterCompletedAppointment = false;
                if ($isToday && $completedAppointmentEndTime) {
                    // So sánh slot với thời gian kết thúc đơn hoàn thành
                    // Slot > completedAppointmentEndTime → đóng (vì thời gian thực đã qua)
                    if ($timeString > $completedAppointmentEndTime) {
                        $isAfterCompletedAppointment = true;
                        if (!$conflictReason) {
                            $conflictReason = "Đã qua thời gian hoàn thành đơn ({$completedAppointmentEndTime})";
                        }
                    }
                }

                // Xác định trạng thái available của slot:
                // - available = true: Nằm trong ca làm việc VÀ chưa bị đặt VÀ không phải quá khứ VÀ không sau thời gian hoàn thành đơn
                // - available = false: Không nằm trong ca làm việc HOẶC đã bị đặt HOẶC là quá khứ HOẶC sau thời gian hoàn thành đơn
                // Các slot unavailable sẽ được hiển thị với màu tối (gray out) ở frontend
                // Xác định trạng thái available của slot
                $isAvailable = $isInWorkingTime && !$isBooked && !$isPastTime && !$isAfterCompletedAppointment;

                // QUAN TRỌNG: Kiểm tra lại nếu slot + duration vượt quá ca làm việc
                // Logic này PHẢI chạy bất kể $isAvailable là gì, để đảm bảo slot bị chặn đúng
                // CHỈ kiểm tra nếu có total_duration > 0 (nếu = 0 thì chưa chọn dịch vụ, không cần kiểm tra)
                if ($totalDuration > 0 && $isInWorkingTime && $shiftEndTime) {
                    $slotTime = Carbon::createFromFormat('H:i', $timeString);
                    $endTime = $slotTime->copy()->addMinutes($totalDuration);

                    if ($endTime->gt($shiftEndTime)) {
                        $isAvailable = false;
                        $isBooked = true;
                        $endTimeString = $endTime->format('H:i');
                        $shiftEndTimeString = $shiftEndTime->format('H:i');

                        // Thông báo đơn giản cho dịch vụ trên 60 phút
                        if ($totalDuration > 60) {
                            $conflictReason = "Đã quá ca làm việc của nhân viên cho dịch vụ trên 60p";
                        } else {
                            $conflictReason = "Đã quá ca làm việc của nhân viên";
                        }

                        \Log::info('Time slot blocked - final check (exceeds shift end)', [
                            'slot' => $timeString,
                            'total_duration' => $totalDuration,
                            'end_time' => $endTimeString,
                            'shift_end' => $shiftEndTimeString,
                            'is_available_before' => $isAvailable,
                            'is_available_after' => false,
                            'employee_id' => $employeeId
                        ]);
                    }
                } else {
                    // Debug log khi không kiểm tra (có thể do total_duration = 0)
                    if ($timeString === '11:30') {
                        \Log::info('DEBUG: Slot 11:30 - Final check NOT run', [
                            'total_duration' => $totalDuration,
                            'is_in_working_time' => $isInWorkingTime,
                            'shift_end_time' => $shiftEndTime ? $shiftEndTime->format('H:i') : 'null',
                            'reason' => $totalDuration <= 0 ? 'total_duration = 0' : ($isInWorkingTime ? 'shiftEndTime missing' : 'not in working time')
                        ]);
                    }
                }

                // Debug log để kiểm tra slot 11h30 với dịch vụ 60p
                if ($timeString === '11:30' && $totalDuration >= 60) {
                    $endTimeString = '';
                    $shiftEndTimeString = '';
                    if ($shiftEndTime) {
                        $slotTime = Carbon::createFromFormat('H:i', $timeString);
                        $endTime = $slotTime->copy()->addMinutes($totalDuration);
                        $endTimeString = $endTime->format('H:i');
                        $shiftEndTimeString = $shiftEndTime->format('H:i');
                    }
                    \Log::info('DEBUG 11:30 slot with 60+ min service', [
                        'slot' => $timeString,
                        'total_duration' => $totalDuration,
                        'is_booked' => $isBooked,
                        'is_in_working_time' => $isInWorkingTime,
                        'is_past_time' => $isPastTime,
                        'is_available' => $isAvailable,
                        'conflict_reason' => $conflictReason,
                        'shift_end_time' => $shiftEndTimeString,
                        'calculated_end_time' => $endTimeString,
                        'employee_id' => $employeeId
                    ]);
                }

                // Debug log để kiểm tra
                if ($timeString === '14:00' && $totalDuration > 0) {
                    \Log::info('DEBUG 14:00 slot', [
                        'slot' => $timeString,
                        'total_duration' => $totalDuration,
                        'is_booked' => $isBooked,
                        'is_in_working_time' => $isInWorkingTime,
                        'is_past_time' => $isPastTime,
                        'is_available' => $isAvailable,
                        'conflict_reason' => $conflictReason,
                        'booked_ranges' => $bookedTimeRanges
                    ]);
                }

                // Debug log cho slot 11:30 trước khi thêm vào array
                if ($timeString === '11:30') {
                    \Log::info('DEBUG: Adding slot 11:30 to timeSlots array', [
                        'time' => $timeString,
                        'is_available' => $isAvailable,
                        'conflict_reason' => $conflictReason,
                        'is_booked' => $isBooked,
                        'is_in_working_time' => $isInWorkingTime,
                        'is_past_time' => $isPastTime,
                        'total_duration' => $totalDuration,
                        'shift_end_time' => $shiftEndTime ? $shiftEndTime->format('H:i') : 'null'
                    ]);
                }

                $timeSlots[] = [
                    'time' => $timeString,
                    'display' => $timeString,
                    'word_time_id' => $wordTime->id,
                    'available' => $isAvailable, // Đảm bảo giá trị này đúng
                    'conflict_reason' => $conflictReason, // Thêm lý do trùng lịch để hiển thị tooltip
                ];

                $currentTime->addMinutes(30);
            }

            // Đảm bảo luôn trả về đủ 30 slots từ 7h-22h
            // Nếu thiếu slots (do lỗi logic), log để debug
            $expectedSlots = 30; // 7h-22h, mỗi 30 phút = 30 slots
            if (count($timeSlots) !== $expectedSlots) {
                \Log::error('Time slots count mismatch - CRITICAL ERROR', [
                    'expected' => $expectedSlots,
                    'actual' => count($timeSlots),
                    'employee_id' => $employeeId,
                    'date' => $appointmentDate->format('Y-m-d'),
                    'slots_returned' => array_map(function($slot) {
                        return $slot['time'];
                    }, $timeSlots),
                    'working_ranges_count' => count($workingTimeRanges)
                ]);

                // Nếu thiếu slots, tạo lại từ đầu để đảm bảo đủ 30 slots
                if (count($timeSlots) < $expectedSlots) {
                    $timeSlots = [];
                    $currentTime = Carbon::parse('07:00');
                    $endTime = Carbon::parse('22:00');

                    while ($currentTime->lte($endTime)) {
                        $timeString = $currentTime->format('H:i');
                        $wordTime = \App\Models\WordTime::firstOrCreate(
                            ['time' => $timeString],
                            ['time' => $timeString]
                        );

                        // Kiểm tra available (giống logic trên)
                        // Slot phải nằm trong khoảng [start, end) - không bao gồm end time
                        // Điều này đảm bảo không thể bắt đầu dịch vụ vào lúc kết thúc ca
                        $slotTime = Carbon::createFromFormat('H:i', $timeString);
                        $isInWorkingTime = false;
                        $shiftEndTime = null;
                        foreach ($workingTimeRanges as $range) {
                            if ($slotTime->gte($range['start']) && $slotTime->lt($range['end'])) {
                                $isInWorkingTime = true;
                                $shiftEndTime = $range['end'];
                                break;
                            }
                        }

                        // Kiểm tra booked (giống logic trên - khóa toàn bộ khoảng từ đơn đầu tiên đến đơn cuối cùng)
                        $isBooked = false;
                        if (count($bookedTimeRanges) > 0) {
                            $sortedRanges = $bookedTimeRanges;
                            usort($sortedRanges, function($a, $b) {
                                return strcmp($a['start'], $b['start']);
                            });

                            $firstAppointmentStart = $sortedRanges[0]['start'];
                            $lastAppointmentEnd = $sortedRanges[count($sortedRanges) - 1]['end'];

                            // Khóa toàn bộ khoảng từ đơn đầu tiên đến đơn cuối cùng
                            if ($timeString >= $firstAppointmentStart && $timeString < $lastAppointmentEnd) {
                                $isBooked = true;
                            } else {
                                // Kiểm tra từng appointment riêng lẻ
                                foreach ($bookedTimeRanges as $range) {
                                    if ($timeString >= $range['start'] && $timeString < $range['end']) {
                                        $isBooked = true;
                                        break;
                                    }
                                }
                            }
                        }

                        // Kiểm tra xem slot có trước giờ hiện tại không (nếu là ngày hôm nay)
                        // Sử dụng logic tương tự như trên để đảm bảo tính nhất quán
                        $isPastTime = false;
                        if ($isToday) {
                            // So sánh bằng Carbon objects để chính xác, sử dụng cùng timezone
                            $slotTime = Carbon::createFromFormat('H:i', $timeString);
                            $now = Carbon::now('Asia/Ho_Chi_Minh'); // Đảm bảo cùng timezone

                            // Chuyển slot time sang cùng ngày với now để so sánh
                            $slotDateTime = Carbon::create(
                                $now->year,
                                $now->month,
                                $now->day,
                                $slotTime->hour,
                                $slotTime->minute,
                                0,
                                'Asia/Ho_Chi_Minh' // Đảm bảo cùng timezone
                            );

                            // Slot đã qua nếu slot <= current time (bao gồm cả slot hiện tại)
                            // Ví dụ: Hiện tại 10h00, slot 7h00-10h00 đều đã qua
                            if ($slotDateTime->lte($now)) {
                                $isPastTime = true;
                            }
                        }

                        $isAvailable = $isInWorkingTime && !$isBooked && !$isPastTime;

                        // QUAN TRỌNG: Kiểm tra lại nếu slot + duration vượt quá ca làm việc
                        // Logic này PHẢI có trong fallback để đảm bảo slot bị chặn đúng
                        $conflictReason = null;
                        if ($totalDuration > 0 && $isInWorkingTime && $shiftEndTime) {
                            $slotTime = Carbon::createFromFormat('H:i', $timeString);
                            $endTime = $slotTime->copy()->addMinutes($totalDuration);

                            if ($endTime->gt($shiftEndTime)) {
                                $isAvailable = false;
                                $isBooked = true;

                                // Thông báo đơn giản cho dịch vụ trên 60 phút
                                if ($totalDuration > 60) {
                                    $conflictReason = "Đã quá ca làm việc của nhân viên cho dịch vụ trên 60p";
                                } else {
                                    $conflictReason = "Đã quá ca làm việc của nhân viên";
                                }

                                // Debug log cho slot 11:30 trong fallback
                                if ($timeString === '11:30') {
                                    \Log::info('DEBUG: Fallback - Slot 11:30 blocked (exceeds shift end)', [
                                        'slot' => $timeString,
                                        'total_duration' => $totalDuration,
                                        'end_time' => $endTime->format('H:i'),
                                        'shift_end' => $shiftEndTime->format('H:i'),
                                        'is_available' => false
                                    ]);
                                }
                            }
                        }

                        $timeSlots[] = [
                            'time' => $timeString,
                            'display' => $timeString,
                            'word_time_id' => $wordTime->id,
                            'available' => $isAvailable,
                            'conflict_reason' => $conflictReason, // Thêm conflict_reason vào fallback
                        ];

                        $currentTime->addMinutes(30);
                    }
                }
            }

            \Log::info('Time slots returned', [
                'count' => count($timeSlots),
                'employee_id' => $employeeId,
                'date' => $appointmentDate->format('Y-m-d'),
                'first_slot' => $timeSlots[0]['time'] ?? 'N/A',
                'last_slot' => $timeSlots[count($timeSlots) - 1]['time'] ?? 'N/A'
            ]);

            // Debug log: Kiểm tra slot 11:30 trong response cuối cùng
            $slot1130 = collect($timeSlots)->firstWhere('time', '11:30');
            if ($slot1130) {
                \Log::info('DEBUG: Final response - Slot 11:30', [
                    'available' => $slot1130['available'],
                    'conflict_reason' => $slot1130['conflict_reason'] ?? 'null',
                    'total_duration' => $totalDuration,
                    'employee_id' => $employeeId,
                    'date' => $appointmentDate->format('Y-m-d')
                ]);
            }

            return response()->json([
                'success' => true,
                'time_slots' => $timeSlots,
                'completed_appointment_end_time' => $completedAppointmentEndTime, // Format: "10:00" hoặc null
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
     * Get appointment status (API endpoint for polling)
     */
    public function getStatus($id)
    {
        try {
            $appointment = \App\Models\Appointment::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'appointment_id' => $appointment->id,
                'status' => $appointment->status,
                'booking_code' => $appointment->booking_code,
                'updated_at' => $appointment->updated_at->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy lịch hẹn'
            ], 404);
        }
    }

    /**
     * Cancel an appointment.
     */
    public function cancel(Request $request, $id)
    {
        try {
            $appointment = \App\Models\Appointment::findOrFail($id);

            // Kiểm tra quyền: cho phép cả guest và logged in user hủy
            $currentUser = auth()->user();

            // Nếu đã đăng nhập, kiểm tra quyền
            if ($currentUser) {
                if (auth()->id() != $appointment->user_id && !$currentUser->isAdmin()) {
                    return back()->with('error', 'Bạn không có quyền hủy lịch hẹn này.');
                }
            }
            // Guest: cho phép hủy nếu thỏa điều kiện (status và thời gian sẽ được kiểm tra ở dưới)

            // Kiểm tra tài khoản có bị khóa không (chỉ cho logged in user)
            $user = auth()->user();
            if ($user && $user->isBanned()) {
                $bannedUntil = $user->banned_until;
                $timeRemaining = $this->formatBanTimeRemaining($bannedUntil);
                $banMessage = 'Tài khoản của bạn đã bị khóa. ' .
                             ($timeRemaining
                                 ? "Tài khoản sẽ được mở khóa sau {$timeRemaining}. "
                                 : 'Tài khoản sẽ được mở khóa sớm. ') .
                             ($user->ban_reason ? "Lý do: {$user->ban_reason}" : '');
                return back()->with('error', $banMessage);
            }

            // Kiểm tra xem có thể hủy không
            // Chỉ có thể hủy khi status = 'Chờ xử lý' và chưa quá 30 phút
            if ($appointment->status !== 'Chờ xử lý') {
                if ($appointment->status === 'Đã xác nhận') {
                    return back()->with('error', 'Không thể hủy lịch hẹn đã được xác nhận. Lịch hẹn đã được tự động xác nhận sau 30 phút kể từ khi đặt.');
                }
                return back()->with('error', 'Chỉ có thể hủy lịch hẹn đang ở trạng thái "Chờ xử lý".');
            }

            // Kiểm tra thời gian: chỉ có thể hủy trong vòng 30 phút kể từ khi đặt
            $createdAt = \Carbon\Carbon::parse($appointment->created_at);
            $now = now();
            $minutesSinceCreated = $createdAt->diffInMinutes($now);

            if ($minutesSinceCreated > 30) {
                // Không tự động xác nhận trong hàm hủy - chỉ trả về lỗi
                return back()->with('error', 'Không thể hủy lịch hẹn sau 30 phút kể từ khi đặt. Lịch hẹn đã được tự động xác nhận.');
            }

            // Lấy lý do hủy từ form hoặc dùng mặc định
            $reason = $request->input('cancellation_reason', 'Khách hàng tự hủy');
            if (empty(trim($reason))) {
                $reason = 'Khách hàng tự hủy';
            }

            // Hủy lịch hẹn
            $modifiedBy = auth()->id(); // null nếu là guest
            $result = $this->appointmentService->cancelAppointment($id, $reason, $modifiedBy);

            // Kiểm tra nếu user bị ban sau khi hủy
            if (isset($result['was_banned']) && $result['was_banned']) {
                $user = $result['user'];
                $bannedUntil = $user->banned_until;
                $timeRemaining = $this->formatBanTimeRemaining($bannedUntil);

                // Logout user
                auth()->logout();
                request()->session()->invalidate();
                request()->session()->regenerateToken();

                // Tạo thông báo
                $banMessage = 'Tài khoản của bạn đã bị khóa vì hủy quá 3 đơn/ngày. ' .
                             ($timeRemaining
                                 ? "Tài khoản sẽ được mở khóa sau {$timeRemaining}. "
                                 : 'Tài khoản sẽ được mở khóa sớm. ') .
                             ($user->ban_reason ? "Lý do: {$user->ban_reason}" : '');

                return redirect()->route('login')
                    ->with('error', $banMessage);
            }

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

            // Lấy TẤT CẢ nhân viên từ trang quản lý (không filter theo chuyên môn)
            // Chỉ loại trừ admin và nhân viên bị vô hiệu hóa
            // CHỈ LẤY CÁC STYLIST (không lấy barber, shampooer, receptionist, etc.)
            $employees = \App\Models\Employee::with(['user.role', 'services'])
                        ->whereNotNull('user_id')
                        ->where('status', '!=', 'Vô hiệu hóa')
                        ->where('position', 'Stylist') // Chỉ lấy stylist
                        ->whereHas('user', function($query) {
                            // Loại trừ admin
                            $query->where('role_id', '!=', 1);
                        })
                ->orderBy('id', 'desc')
                        ->get()
                        ->values();

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
     * Format thời gian còn lại của ban thành chuỗi dễ đọc.
     */
    protected function formatBanTimeRemaining($bannedUntil)
    {
        if (!$bannedUntil) {
            return null;
        }

        $now = now();
        if ($now->greaterThanOrEqualTo($bannedUntil)) {
            return null;
        }

        // Sử dụng diffInRealMinutes để có số chính xác hơn, sau đó làm tròn lên
        $diffInMinutes = (int) ceil($now->diffInRealMinutes($bannedUntil, false));

        if ($diffInMinutes < 60) {
            return $diffInMinutes . ' phút';
        }

        $hours = floor($diffInMinutes / 60);
        $minutes = $diffInMinutes % 60;

        if ($minutes == 0) {
            return $hours . ' giờ';
        }

        return $hours . ' giờ ' . $minutes . ' phút';
    }

    /**
     * Apply coupon code for appointment booking.
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

        // Get service IDs from request
        $serviceIds = $request->input('service_id', []);
        $variantIds = $request->input('service_variants', []);
        $comboIds = $request->input('combo_id', []);

        // Convert to arrays if single values
        if (!is_array($serviceIds)) {
            $serviceIds = $serviceIds ? [$serviceIds] : [];
        }
        if (!is_array($variantIds)) {
            $variantIds = $variantIds ? [$variantIds] : [];
        }
        if (!is_array($comboIds)) {
            $comboIds = $comboIds ? [$comboIds] : [];
        }

        // Build cart from selected services
        $cart = [];
        $subtotal = 0;

        // Add services
        if (!empty($serviceIds)) {
            $services = \App\Models\Service::whereIn('id', $serviceIds)->get();
            foreach ($services as $service) {
                $price = $service->base_price ?? 0;
                $cart[] = [
                    'type' => 'service',
                    'id' => $service->id,
                    'price' => $price,
                    'quantity' => 1
                ];
                $subtotal += $price;
            }
        }

        // Add variants
        if (!empty($variantIds)) {
            $variants = \App\Models\ServiceVariant::whereIn('id', $variantIds)->get();
            foreach ($variants as $variant) {
                $price = $variant->price ?? 0;
                $cart[] = [
                    'type' => 'service_variant',
                    'id' => $variant->id,
                    'price' => $price,
                    'quantity' => 1
                ];
                $subtotal += $price;
            }
        }

        // Add combos
        if (!empty($comboIds)) {
            $combos = \App\Models\Combo::whereIn('id', $comboIds)->get();
            foreach ($combos as $combo) {
                $price = $combo->price ?? 0;
                $cart[] = [
                    'type' => 'combo',
                    'id' => $combo->id,
                    'price' => $price,
                    'quantity' => 1
                ];
                $subtotal += $price;
            }
        }

        $user = Auth::user();
        $result = $promotionService->validateAndCalculateDiscount(
            $code,
            $cart,
            $subtotal,
            $user ? $user->id : null
        );

        if ($request->ajax() || $request->wantsJson()) {
            if (!$result['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'error' => $result['message']
                ], 400);
            }

            Session::put('coupon_code', $code);

            $appliedPromotionId = $request->input('applied_promotion_id');
            if (isset($result['promotion'])) {
                Session::put('applied_promotion_id', $result['promotion']->id);
            } elseif ($appliedPromotionId) {
                Session::put('applied_promotion_id', $appliedPromotionId);
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
                    'max_discount_amount' => $promotion->max_discount_amount ?? null,
                    'apply_scope' => $promotion->apply_scope ?? 'order'
                ]
            ]);
        }

        Session::put('coupon_code', $code);
        if (isset($result['promotion'])) {
            Session::put('applied_promotion_id', $result['promotion']->id);
        }

        return back()->with('success', 'Áp dụng mã khuyến mại thành công!');
    }

    /**
     * Remove applied coupon code.
     */
    public function removeCoupon(Request $request)
    {
        Session::forget('coupon_code');
        Session::forget('applied_promotion_id');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã gỡ bỏ mã khuyến mại.'
            ]);
        }

        return back()->with('success', 'Đã gỡ bỏ mã khuyến mại.');
    }
}
