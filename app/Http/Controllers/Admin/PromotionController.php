<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PromotionService;
use App\Services\ServiceService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PromotionController extends Controller
{
    protected PromotionService $promotionService;
    protected ServiceService $serviceService;

    /**
     * Available statuses for promotions.
     */
    protected array $statuses = [
        'inactive' => 'Ngừng áp dụng',
        'active' => 'Đang chạy',
        'scheduled' => 'Chờ áp dụng',
        'expired' => 'Đã kết thúc',
    ];

    public function __construct(PromotionService $promotionService, ServiceService $serviceService)
    {
        $this->promotionService = $promotionService;
        $this->serviceService = $serviceService;
    }

    /**
     * Display a listing of promotions.
     */
    public function index()
    {
        $promotions = $this->promotionService->getAll();
        $statuses = $this->statuses;

        return view('admin.promotions.index', compact('promotions', 'statuses'));
    }

    /**
     * Show the form for creating a new promotion.
     */
    public function create()
    {
        $statuses = $this->statuses;
        $services = $this->serviceService->getAll();
        $combos = \App\Models\Combo::whereNull('deleted_at')->with('category')->get();
        $serviceVariants = \App\Models\ServiceVariant::whereNull('deleted_at')
            ->with('service.category')
            ->get();

        return view('admin.promotions.create', compact('statuses', 'services', 'combos', 'serviceVariants'));
    }

    /**
     * Store a newly created promotion.
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $serviceIds = $request->input('services', []);
        $comboIds = $request->input('combos', []);
        $variantIds = $request->input('service_variants', []);
        
        $this->promotionService->create($data, $serviceIds, $comboIds, $variantIds);

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Khuyến mãi đã được tạo thành công!');
    }

    /**
     * Display the specified promotion.
     */
    public function show(string $id)
    {
        $promotion = $this->promotionService->getOne($id);
        $statuses = $this->statuses;

        return view('admin.promotions.show', compact('promotion', 'statuses'));
    }

    /**
     * Show the form for editing the specified promotion.
     */
    public function edit(string $id)
    {
        $promotion = $this->promotionService->getOne($id);
        $statuses = $this->statuses;
        $services = $this->serviceService->getAll();
        $combos = \App\Models\Combo::whereNull('deleted_at')->with('category')->get();
        $serviceVariants = \App\Models\ServiceVariant::whereNull('deleted_at')
            ->with('service.category')
            ->get();
        $selectedServiceIds = $promotion->services->pluck('id')->toArray();
        $selectedComboIds = $promotion->combos->pluck('id')->toArray();
        $selectedVariantIds = $promotion->serviceVariants->pluck('id')->toArray();

        return view('admin.promotions.edit', compact(
            'promotion', 
            'statuses', 
            'services', 
            'combos',
            'serviceVariants',
            'selectedServiceIds',
            'selectedComboIds',
            'selectedVariantIds'
        ));
    }

    /**
     * Update the specified promotion in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $this->validateData($request, $id);
        $serviceIds = $request->input('services', []);
        $comboIds = $request->input('combos', []);
        $variantIds = $request->input('service_variants', []);
        
        $this->promotionService->update($id, $data, $serviceIds, $comboIds, $variantIds);

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Khuyến mãi đã được cập nhật!');
    }

    /**
     * Remove the specified promotion from storage.
     */
    public function destroy(string $id)
    {
        $this->promotionService->delete($id);

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Khuyến mãi đã được xóa!');
    }

    /**
     * Validate request data.
     */
    protected function validateData(Request $request, ?string $id = null): array
    {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('promotions', 'code')->ignore($id),
            ],
            'name' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string'],
            'discount_type' => ['required', Rule::in(['percent', 'amount'])],
            'discount_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'apply_scope' => ['required', Rule::in(['service', 'order'])],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'per_user_limit' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(array_keys($this->statuses))],
        ]);

        // Ràng buộc logic theo loại giảm giá
        if ($data['discount_type'] === 'percent') {
            $data['discount_amount'] = null;
            if ($data['discount_percent'] === null) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'discount_percent' => 'Vui lòng nhập % giảm giá khi chọn loại giảm giá theo phần trăm.',
                ]);
            }
        } else {
            $data['discount_percent'] = null;
            if ($data['discount_amount'] === null) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'discount_amount' => 'Vui lòng nhập số tiền giảm giá khi chọn loại giảm giá theo số tiền.',
                ]);
            }
        }

        // Nếu áp dụng theo hóa đơn thì yêu cầu tổng tiền tối thiểu
        if ($data['apply_scope'] === 'order' && $data['min_order_amount'] === null) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'min_order_amount' => 'Vui lòng nhập số tiền hóa đơn tối thiểu khi áp dụng theo hóa đơn.',
            ]);
        }

        return $data;
    }
}

