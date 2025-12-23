<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceVariant;
use App\Models\Combo;
use App\Services\ServiceCategoryService;
use App\Services\ServiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    protected $serviceService;
    protected $serviceCategoryService;

    public function __construct(ServiceService $serviceService, ServiceCategoryService $serviceCategoryService)
    {
        $this->serviceService = $serviceService;
        $this->serviceCategoryService = $serviceCategoryService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $keyword = $request->get('keyword');
        $perPage = $request->get('per_page', 15);

        // Get all services (single services and services with variants)
        $query = Service::with(['category', 'serviceVariants'])
            ->whereNull('deleted_at');

        if ($keyword) {
            $query->where('name', 'like', "%{$keyword}%");
        }

        $services = $query->orderBy('id', 'desc')->paginate($perPage);
        
        // Redirect to first page if current page is out of range
        if ($request->has('page')) {
            $currentPage = (int) $request->get('page');
            $lastPage = $services->lastPage();
            
            // Nếu trang hiện tại lớn hơn trang cuối và có ít nhất 1 trang, redirect về trang 1
            if ($currentPage > $lastPage && $lastPage > 0) {
                return redirect()->route('admin.services.index', array_merge($request->except('page'), ['page' => 1]));
            }
            // Nếu không có dữ liệu (lastPage = 0) và đang ở trang > 1, redirect về trang 1
            if ($lastPage == 0 && $currentPage > 1) {
                return redirect()->route('admin.services.index', array_merge($request->except('page'), ['page' => 1]));
            }
        }

        // Get all combos
        $combosQuery = Combo::with(['category', 'comboItems.service'])
            ->whereNull('deleted_at');

        if ($keyword) {
            $combosQuery->where('name', 'like', "%{$keyword}%");
        }

        $combos = $combosQuery->orderBy('id', 'desc')->get();

        return view('admin.services.index', compact('services', 'combos', 'keyword'));
    }

    /**
     * Show service detail for modal
     */
    public function showDetail(Request $request, $id)
    {
        $type = $request->get('type', 'service');

        if ($type === 'combo') {
            $combo = Combo::with(['category', 'comboItems.service.serviceVariants'])->findOrFail($id);
            $html = view('admin.services.partials.combo_detail', compact('combo'))->render();
        } else {
            $service = Service::with(['category', 'serviceVariants.variantAttributes'])->findOrFail($id);
            $html = view('admin.services.partials.service_detail', compact('service'))->render();
        }

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = $this->serviceCategoryService->getActive();
        $singleServices = Service::whereNull('deleted_at')
            ->whereDoesntHave('serviceVariants')
            ->get();
        
        // Lấy cả dịch vụ có biến thể để có thể chọn biến thể
        $variantServices = Service::whereNull('deleted_at')
            ->whereHas('serviceVariants')
            ->with('serviceVariants')
            ->get();
        
        return view('admin.services.create', compact('categories', 'singleServices', 'variantServices'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $serviceType = $request->input('service_type');

        switch ($serviceType) {
            case 'single':
                return $this->storeSingleService($request);
            case 'variant':
                return $this->storeVariant($request);
            case 'combo':
                return $this->storeCombo($request);
            default:
                return redirect()->back()->with('error', 'Vui lòng chọn loại dịch vụ');
        }
    }

    /**
     * Store single service
     */
    protected function storeSingleService(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:services,name',
            'category_id' => 'required|exists:service_categories,id',
            'base_price' => 'required|numeric|min:0',
            'base_duration' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|in:Hoạt động,Vô hiệu hóa',
            'description' => 'nullable|string',
        ], [
            'name.unique' => 'Dịch vụ này đã tồn tại',
        ]);

        $data = $request->only([
            'name',
            'category_id',
            'base_price',
            'base_duration',
            'status',
            'description',
        ]);

        // Tự động tạo mã dịch vụ
        $data['service_code'] = 'DV' . str_pad(Service::max('id') + 1, 6, '0', STR_PAD_LEFT);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('legacy/images/products'), $imageName);
            $data['image'] = $imageName;
        }

        if (empty($data['status'])) {
            $data['status'] = 'Hoạt động';
        }

        Service::create($data);

        return redirect()->route('admin.services.index')
            ->with('success', 'Dịch vụ đơn đã được thêm thành công!');
    }

    /**
     * Store variant service
     */
    protected function storeVariant(Request $request)
    {
        $request->validate([
            'service_name' => 'required|string|max:255|unique:services,name',
            'category_id' => 'required|exists:service_categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'variants' => 'required|array|min:1',
            'variants.*.name' => 'required|string|max:255',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.duration' => 'required|integer|min:0',
            'variants.*.is_active' => 'nullable|boolean',
            'variants.*.notes' => 'nullable|string',
            'variants.*.attributes' => 'nullable|array',
            'variants.*.attributes.*.name' => 'required_with:variants.*.attributes|string|max:100',
            'variants.*.attributes.*.value' => 'required_with:variants.*.attributes|string|max:100',
        ], [
            'service_name.unique' => 'Dịch vụ này đã tồn tại',
        ]);

        DB::beginTransaction();
        try {
            // Tạo dịch vụ chính
            $serviceData = [
                'name' => $request->input('service_name'),
                'category_id' => $request->input('category_id'),
                'description' => $request->input('description'),
                'status' => 'Hoạt động',
            ];

            // Tự động tạo mã dịch vụ
            $serviceData['service_code'] = 'DV' . str_pad(Service::max('id') + 1, 6, '0', STR_PAD_LEFT);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('legacy/images/products'), $imageName);
                $serviceData['image'] = $imageName;
            }

            $service = Service::create($serviceData);

            // Tạo các biến thể
            $variants = $request->input('variants', []);
            foreach ($variants as $variantData) {
                $variant = ServiceVariant::create([
                    'service_id' => $service->id,
                    'name' => $variantData['name'],
                    'price' => $variantData['price'],
                    'duration' => $variantData['duration'],
                    'is_default' => false,
                    'is_active' => isset($variantData['is_active']) && $variantData['is_active'] == '1',
                    'notes' => $variantData['notes'] ?? null,
                ]);

                // Tạo các thuộc tính cho biến thể
                if (isset($variantData['attributes']) && is_array($variantData['attributes'])) {
                    foreach ($variantData['attributes'] as $attrData) {
                        if (!empty($attrData['name']) && !empty($attrData['value'])) {
                            \App\Models\VariantAttribute::create([
                                'service_variant_id' => $variant->id,
                                'attribute_name' => $attrData['name'],
                                'attribute_value' => $attrData['value'],
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.services.index')
                ->with('success', 'Dịch vụ biến thể đã được thêm thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Store combo service
     */
    protected function storeCombo(Request $request)
    {
        // Lọc bỏ các combo items trống trước khi validate
        $comboItems = $request->input('combo_items', []);
        $filteredComboItems = [];
        foreach ($comboItems as $key => $item) {
            // Chỉ giữ lại item có service_id
            if (!empty($item['service_id'])) {
                $filteredComboItems[$key] = $item;
            }
        }
        
        // Merge lại vào request để validate
        $request->merge(['combo_items' => $filteredComboItems]);
        
        $request->validate([
            'combo_name' => 'required|string|max:255|unique:combos,name',
            'category_id' => 'required|exists:service_categories,id',
            'combo_price' => 'required|numeric|min:0',
            'combo_duration' => 'nullable|integer|min:0',
            'combo_items' => 'required|array|min:1',
            'combo_items.*.service_id' => 'required|exists:services,id',
            'combo_items.*.service_variant_id' => 'nullable|exists:service_variants,id',
            'combo_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'combo_status' => 'nullable|in:Hoạt động,Vô hiệu hóa',
            'combo_description' => 'nullable|string',
        ], [
            'combo_name.unique' => 'Dịch vụ này đã tồn tại',
        ]);

        DB::transaction(function () use ($request) {
            $combo = Combo::create([
                'name' => $request->input('combo_name'),
                'slug' => Str::slug($request->input('combo_name')) . '-' . uniqid(),
                'category_id' => $request->input('category_id'),
                'price' => $request->input('combo_price'),
                'duration' => $request->input('combo_duration'),
                'status' => $request->input('combo_status', 'Hoạt động'),
                'description' => $request->input('combo_description'),
            ]);

            if ($request->hasFile('combo_image')) {
                $image = $request->file('combo_image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('legacy/images/products'), $imageName);
                $combo->image = $imageName;
                $combo->save();
            }

            $comboItems = $request->input('combo_items', []);
            foreach ($comboItems as $item) {
                if (!empty($item['service_id'])) {
                    $combo->comboItems()->create([
                        'service_id' => $item['service_id'],
                        'service_variant_id' => !empty($item['service_variant_id']) ? $item['service_variant_id'] : null,
                        'quantity' => 1,
                    ]);
                }
            }
        });

        return redirect()->route('admin.services.index')
            ->with('success', 'Combo dịch vụ đã được thêm thành công!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $type = $request->get('type');

        // Check if it's a combo
        if ($type === 'combo') {
            $combo = Combo::findOrFail($id);
            $categories = $this->serviceCategoryService->getActive();
            // Nếu category hiện tại của combo bị ẩn, vẫn thêm vào danh sách
            if ($combo->category_id && !$categories->contains('id', $combo->category_id)) {
                $currentCategory = \App\Models\ServiceCategory::find($combo->category_id);
                if ($currentCategory) {
                    $categories->push($currentCategory);
                }
            }
            $singleServices = Service::whereNull('deleted_at')
                ->whereDoesntHave('serviceVariants')
                ->get();
            
            // Lấy cả dịch vụ có biến thể
            $variantServices = Service::whereNull('deleted_at')
                ->whereHas('serviceVariants')
                ->with('serviceVariants')
                ->get();
            
            $combo->load('comboItems.service.serviceVariants');
            return view('admin.services.edit', compact('combo', 'categories', 'singleServices', 'variantServices'))->with('service_type', 'combo');
        }

        // Check if it's a variant (single variant edit)
        if ($type === 'variant') {
            // Kiểm tra xem $id có phải là ServiceVariant ID không
            // Nhưng ưu tiên kiểm tra xem có phải là Service ID không
            $service = Service::find($id);
            if ($service) {
                // Đây là edit service có variants (ID là service ID)
                $service->load(['category', 'serviceVariants.variantAttributes']);
                $categories = $this->serviceCategoryService->getActive();
                // Nếu category hiện tại của service bị ẩn, vẫn thêm vào danh sách
                if ($service->category_id && !$categories->contains('id', $service->category_id)) {
                    $currentCategory = \App\Models\ServiceCategory::find($service->category_id);
                    if ($currentCategory) {
                        $categories->push($currentCategory);
                    }
                }
                $singleServices = Service::whereNull('deleted_at')
                    ->whereDoesntHave('serviceVariants')
                    ->get();
                $serviceType = 'variant';
                // Đảm bảo cả service_type và serviceType đều được truyền
                return view('admin.services.edit', compact('service', 'categories', 'singleServices', 'serviceType'))
                    ->with('service_type', 'variant');
            } else {
                // Thử tìm variant với ID này (trường hợp edit variant đơn lẻ)
                $variant = ServiceVariant::find($id);
                if ($variant) {
                    // Đây là edit một variant đơn lẻ
                    $variant->load('service');
                    $categories = $this->serviceCategoryService->getActive();
                    // Nếu category hiện tại của service bị ẩn, vẫn thêm vào danh sách
                    if ($variant->service && $variant->service->category_id && !$categories->contains('id', $variant->service->category_id)) {
                        $currentCategory = \App\Models\ServiceCategory::find($variant->service->category_id);
                        if ($currentCategory) {
                            $categories->push($currentCategory);
                        }
                    }
                    $singleServices = Service::whereNull('deleted_at')
                        ->whereDoesntHave('serviceVariants')
                        ->get();
                    // Truyền cả service_type và serviceType để đảm bảo tương thích
                    return view('admin.services.edit', compact('variant', 'categories', 'singleServices'))
                        ->with('service_type', 'variant')
                        ->with('serviceType', 'variant');
                } else {
                    // Không tìm thấy cả service và variant
                    abort(404, 'Service or Variant not found');
                }
            }
        }

        // It's a service
        $service = Service::with(['category', 'serviceVariants.variantAttributes'])->findOrFail($id);
        $categories = $this->serviceCategoryService->getActive();
        // Nếu category hiện tại của service bị ẩn, vẫn thêm vào danh sách
        if ($service->category_id && !$categories->contains('id', $service->category_id)) {
            $currentCategory = \App\Models\ServiceCategory::find($service->category_id);
            if ($currentCategory) {
                $categories->push($currentCategory);
            }
        }
        $singleServices = Service::whereNull('deleted_at')
            ->whereDoesntHave('serviceVariants')
            ->get();

        // Determine service type
        $serviceType = 'single';
        if ($service->serviceVariants->count() > 0) {
            $serviceType = 'variant';
        }

        return view('admin.services.edit', compact('service', 'categories', 'singleServices', 'serviceType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $serviceType = $request->input('service_type');

        switch ($serviceType) {
            case 'single':
                return $this->updateSingleService($request, $id);
            case 'variant':
                // Nếu là dịch vụ biến thể (service có variants), xử lý khác
                $service = Service::findOrFail($id);
                if ($service->serviceVariants->count() > 0) {
                    return $this->updateVariantService($request, $id);
                }
                return $this->updateVariant($request, $id);
            case 'combo':
                return $this->updateCombo($request, $id);
            default:
                return redirect()->back()->with('error', 'Vui lòng chọn loại dịch vụ');
        }
    }

    /**
     * Update single service
     */
    protected function updateSingleService(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:services,name,' . $id,
            'category_id' => 'required|exists:service_categories,id',
            'base_price' => 'required|numeric|min:0',
            'base_duration' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|in:Hoạt động,Vô hiệu hóa',
            'description' => 'nullable|string',
        ], [
            'name.unique' => 'Dịch vụ này đã tồn tại',
        ]);

        $data = $request->only([
            'name',
            'category_id',
            'base_price',
            'base_duration',
            'status',
            'description',
        ]);

        if ($request->hasFile('image')) {
            if ($service->image && file_exists(public_path('legacy/images/products/' . $service->image))) {
                unlink(public_path('legacy/images/products/' . $service->image));
            }
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('legacy/images/products'), $imageName);
            $data['image'] = $imageName;
        }

        $service->update($data);

        return redirect()->route('admin.services.index')
            ->with('success', 'Dịch vụ đơn đã được cập nhật thành công!');
    }

    /**
     * Update variant service (single variant)
     */
    protected function updateVariant(Request $request, $id)
    {
        $variant = ServiceVariant::findOrFail($id);

        $request->validate([
            'variant_name' => 'required|string|max:255',
            'variant_price' => 'required|numeric|min:0',
            'variant_duration' => 'required|integer|min:0',
        ]);

        $variant->update([
            'name' => $request->input('variant_name'),
            'price' => $request->input('variant_price'),
            'duration' => $request->input('variant_duration'),
            'is_default' => $request->input('is_default', false),
            'is_active' => $request->input('is_active', true),
        ]);

        return redirect()->route('admin.services.index')
            ->with('success', 'Biến thể dịch vụ đã được cập nhật thành công!');
    }

    /**
     * Update variant service (service with multiple variants)
     */
    protected function updateVariantService(Request $request, $id)
    {
        $service = Service::with('serviceVariants.variantAttributes')->findOrFail($id);

        $request->validate([
            'service_name' => 'required|string|max:255|unique:services,name,' . $id,
            'category_id' => 'required|exists:service_categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'variants' => 'required|array|min:1',
            'variants.*.name' => 'required|string|max:255',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.duration' => 'required|integer|min:0',
            'variants.*.is_active' => 'nullable|boolean',
            'variants.*.notes' => 'nullable|string',
            'variants.*.attributes' => 'nullable|array',
            'variants.*.attributes.*.name' => 'required_with:variants.*.attributes|string|max:100',
            'variants.*.attributes.*.value' => 'required_with:variants.*.attributes|string|max:100',
        ], [
            'service_name.unique' => 'Dịch vụ này đã tồn tại',
        ]);

        DB::beginTransaction();
        try {
            // Cập nhật thông tin dịch vụ chính
            $serviceData = [
                'name' => $request->input('service_name'),
                'category_id' => $request->input('category_id'),
                'description' => $request->input('description'),
            ];

            if ($request->hasFile('image')) {
                if ($service->image && file_exists(public_path('legacy/images/products/' . $service->image))) {
                    unlink(public_path('legacy/images/products/' . $service->image));
                }
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('legacy/images/products'), $imageName);
                $serviceData['image'] = $imageName;
            }

            $service->update($serviceData);

            // Lấy danh sách ID biến thể hiện tại và mới
            $existingVariantIds = $service->serviceVariants->pluck('id')->toArray();
            $newVariantIds = [];
            $variants = $request->input('variants', []);

            // Cập nhật hoặc tạo mới các biến thể
            foreach ($variants as $variantData) {
                if (isset($variantData['id']) && in_array($variantData['id'], $existingVariantIds)) {
                    // Cập nhật biến thể hiện có
                    $variant = ServiceVariant::find($variantData['id']);
                    if ($variant) {
                        $variant->update([
                            'name' => $variantData['name'],
                            'price' => $variantData['price'],
                            'duration' => $variantData['duration'],
                            'is_default' => false,
                            'is_active' => isset($variantData['is_active']) && $variantData['is_active'] == '1',
                            'notes' => $variantData['notes'] ?? null,
                        ]);
                        $newVariantIds[] = $variant->id;

                        // Xóa các thuộc tính cũ và tạo mới
                        $variant->variantAttributes()->delete();
                        if (isset($variantData['attributes']) && is_array($variantData['attributes'])) {
                            foreach ($variantData['attributes'] as $attrData) {
                                if (!empty($attrData['name']) && !empty($attrData['value'])) {
                                    \App\Models\VariantAttribute::create([
                                        'service_variant_id' => $variant->id,
                                        'attribute_name' => $attrData['name'],
                                        'attribute_value' => $attrData['value'],
                                    ]);
                                }
                            }
                        }
                    }
                } else {
                    // Tạo biến thể mới
                    $variant = ServiceVariant::create([
                        'service_id' => $service->id,
                        'name' => $variantData['name'],
                        'price' => $variantData['price'],
                        'duration' => $variantData['duration'],
                        'is_default' => false,
                        'is_active' => isset($variantData['is_active']) && $variantData['is_active'] == '1',
                        'notes' => $variantData['notes'] ?? null,
                    ]);
                    $newVariantIds[] = $variant->id;

                    // Tạo các thuộc tính cho biến thể mới
                    if (isset($variantData['attributes']) && is_array($variantData['attributes'])) {
                        foreach ($variantData['attributes'] as $attrData) {
                            if (!empty($attrData['name']) && !empty($attrData['value'])) {
                                \App\Models\VariantAttribute::create([
                                    'service_variant_id' => $variant->id,
                                    'attribute_name' => $attrData['name'],
                                    'attribute_value' => $attrData['value'],
                                ]);
                            }
                        }
                    }
                }
            }

            // Xóa các biến thể không còn trong danh sách
            $variantsToDelete = array_diff($existingVariantIds, $newVariantIds);
            if (!empty($variantsToDelete)) {
                ServiceVariant::whereIn('id', $variantsToDelete)->delete();
            }

            DB::commit();
            return redirect()->route('admin.services.index')
                ->with('success', 'Dịch vụ biến thể đã được cập nhật thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Update combo service
     */
    protected function updateCombo(Request $request, $id)
    {
        $combo = Combo::findOrFail($id);

        // Lọc bỏ các combo items trống trước khi validate
        $comboItems = $request->input('combo_items', []);
        $filteredComboItems = [];
        foreach ($comboItems as $key => $item) {
            // Chỉ giữ lại item có service_id
            if (!empty($item['service_id'])) {
                $filteredComboItems[$key] = $item;
            }
        }
        
        // Merge lại vào request để validate
        $request->merge(['combo_items' => $filteredComboItems]);

        $request->validate([
            'combo_name' => 'required|string|max:255|unique:combos,name,' . $id,
            'category_id' => 'required|exists:service_categories,id',
            'combo_price' => 'required|numeric|min:0',
            'combo_duration' => 'nullable|integer|min:0',
            'combo_items' => 'required|array|min:1',
            'combo_items.*.service_id' => 'required|exists:services,id',
            'combo_items.*.service_variant_id' => 'nullable|exists:service_variants,id',
            'combo_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'combo_status' => 'nullable|in:Hoạt động,Vô hiệu hóa',
            'combo_description' => 'nullable|string',
        ], [
            'combo_name.unique' => 'Dịch vụ này đã tồn tại',
        ]);

        DB::transaction(function () use ($request, $combo) {
            $combo->update([
                'name' => $request->input('combo_name'),
                'category_id' => $request->input('category_id'),
                'price' => $request->input('combo_price'),
                'duration' => $request->input('combo_duration'),
                'status' => $request->input('combo_status', 'Hoạt động'),
                'description' => $request->input('combo_description'),
            ]);

            if ($request->hasFile('combo_image')) {
                if ($combo->image && file_exists(public_path('legacy/images/products/' . $combo->image))) {
                    unlink(public_path('legacy/images/products/' . $combo->image));
                }
                $image = $request->file('combo_image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('legacy/images/products'), $imageName);
                $combo->image = $imageName;
                $combo->save();
            }

            // Xóa tất cả combo items cũ
            $combo->comboItems()->delete();
            
            // Tạo lại combo items mới
            $comboItems = $request->input('combo_items', []);
            foreach ($comboItems as $item) {
                if (!empty($item['service_id'])) {
                    $combo->comboItems()->create([
                        'service_id' => $item['service_id'],
                        'service_variant_id' => !empty($item['service_variant_id']) ? $item['service_variant_id'] : null,
                        'quantity' => 1,
                    ]);
                }
            }
        });

        return redirect()->route('admin.services.index')
            ->with('success', 'Combo dịch vụ đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Request $request, $id)
    {
        $type = $request->input('type');

        // Check if it's a combo
        if ($type === 'combo') {
            $combo = Combo::findOrFail($id);
            $combo->delete();
            return redirect()->route('admin.services.trash')
                ->with('success', 'Combo dịch vụ đã được xóa tạm thời!');
        }

        // Check if it's a variant
        if ($type === 'variant') {
            $variant = ServiceVariant::findOrFail($id);
            $variant->delete();
            return redirect()->route('admin.services.trash')
                ->with('success', 'Biến thể dịch vụ đã được xóa tạm thời!');
        }

        // It's a service
        $service = Service::findOrFail($id);
        $service->delete();

        return redirect()->route('admin.services.trash')
            ->with('success', 'Dịch vụ đã được xóa tạm thời!');
    }

    /**
     * Display trash page
     */
    public function trash(Request $request)
    {
        $keyword = $request->get('keyword');
        $perPage = $request->get('per_page', 15);

        // Get deleted services
        $servicesQuery = Service::with(['category', 'serviceVariants'])
            ->onlyTrashed();

        if ($keyword) {
            $servicesQuery->where('name', 'like', "%{$keyword}%");
        }

        $services = $servicesQuery->orderBy('deleted_at', 'desc')->paginate($perPage);

        // Get deleted combos
        $combosQuery = Combo::with(['category', 'comboItems.service'])
            ->onlyTrashed();

        if ($keyword) {
            $combosQuery->where('name', 'like', "%{$keyword}%");
        }

        $combos = $combosQuery->orderBy('deleted_at', 'desc')->get();

        // Get deleted variants
        $variantsQuery = ServiceVariant::with('service')->onlyTrashed();

        if ($keyword) {
            $variantsQuery->where('name', 'like', "%{$keyword}%");
        }

        $variants = $variantsQuery->orderBy('deleted_at', 'desc')->get();

        return view('admin.services.trash', compact('services', 'combos', 'variants', 'keyword'));
    }

    /**
     * Restore a soft deleted service
     */
    public function restore(Request $request, $id)
    {
        $type = $request->input('type');

        // Check if it's a combo
        if ($type === 'combo') {
            $combo = Combo::onlyTrashed()->findOrFail($id);
            $combo->restore();
            return redirect()->route('admin.services.trash')
                ->with('success', 'Combo dịch vụ đã được khôi phục thành công!');
        }

        // Check if it's a variant
        if ($type === 'variant') {
            $variant = ServiceVariant::onlyTrashed()->findOrFail($id);
            $variant->restore();
            return redirect()->route('admin.services.trash')
                ->with('success', 'Biến thể dịch vụ đã được khôi phục thành công!');
        }

        // It's a service
        $service = Service::onlyTrashed()->findOrFail($id);
        $service->restore();

        return redirect()->route('admin.services.trash')
            ->with('success', 'Dịch vụ đã được khôi phục thành công!');
    }

    /**
     * Permanently delete a service
     */
    public function forceDelete(Request $request, $id)
    {
        $type = $request->input('type');

        // Check if it's a combo
        if ($type === 'combo') {
            $combo = Combo::onlyTrashed()->findOrFail($id);
            if ($combo->image && file_exists(public_path('legacy/images/products/' . $combo->image))) {
                unlink(public_path('legacy/images/products/' . $combo->image));
            }
            $combo->forceDelete();
            return redirect()->route('admin.services.trash')
                ->with('success', 'Combo dịch vụ đã được xóa vĩnh viễn!');
        }

        // Check if it's a variant
        if ($type === 'variant') {
            $variant = ServiceVariant::onlyTrashed()->findOrFail($id);
            $variant->forceDelete();
            return redirect()->route('admin.services.trash')
                ->with('success', 'Biến thể dịch vụ đã được xóa vĩnh viễn!');
        }

        // It's a service
        $service = Service::onlyTrashed()->findOrFail($id);
        
        if ($service->image && file_exists(public_path('legacy/images/products/' . $service->image))) {
            unlink(public_path('legacy/images/products/' . $service->image));
        }

        $service->forceDelete();

        return redirect()->route('admin.services.trash')
            ->with('success', 'Dịch vụ đã được xóa vĩnh viễn!');
    }
}
