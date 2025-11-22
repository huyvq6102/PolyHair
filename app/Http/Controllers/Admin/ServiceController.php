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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = $this->serviceCategoryService->getAll();
        $singleServices = Service::whereNull('deleted_at')
            ->whereDoesntHave('serviceVariants')
            ->get();
        
        return view('admin.services.create', compact('categories', 'singleServices'));
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
            'service_code' => 'nullable|string|max:50|unique:services,service_code',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:service_categories,id',
            'base_price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|in:Hoạt động,Vô hiệu hóa',
            'description' => 'nullable|string',
        ]);

        $data = $request->only([
            'service_code',
            'name',
            'category_id',
            'base_price',
            'status',
            'description',
        ]);

        if (!$data['service_code']) {
            $data['service_code'] = 'DV' . str_pad(Service::max('id') + 1, 6, '0', STR_PAD_LEFT);
        }

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
            'service_id' => 'required|exists:services,id',
            'variant_name' => 'required|string|max:255',
            'variant_price' => 'required|numeric|min:0',
            'variant_duration' => 'required|integer|min:0',
        ]);

        ServiceVariant::create([
            'service_id' => $request->input('service_id'),
            'name' => $request->input('variant_name'),
            'price' => $request->input('variant_price'),
            'duration' => $request->input('variant_duration'),
            'is_default' => $request->input('is_default', false),
            'is_active' => $request->input('is_active', true),
        ]);

        return redirect()->route('admin.services.index')
            ->with('success', 'Biến thể dịch vụ đã được thêm thành công!');
    }

    /**
     * Store combo service
     */
    protected function storeCombo(Request $request)
    {
        $request->validate([
            'combo_name' => 'required|string|max:255',
            'category_id' => 'required|exists:service_categories,id',
            'combo_price' => 'required|numeric|min:0',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'exists:services,id',
            'combo_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'combo_status' => 'nullable|in:Hoạt động,Vô hiệu hóa',
            'combo_description' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $combo = Combo::create([
                'name' => $request->input('combo_name'),
                'slug' => Str::slug($request->input('combo_name')) . '-' . uniqid(),
                'category_id' => $request->input('category_id'),
                'price' => $request->input('combo_price'),
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

            $serviceIds = $request->input('service_ids');
            foreach ($serviceIds as $serviceId) {
                $service = Service::find($serviceId);
                if ($service) {
                    $variant = $service->serviceVariants()->first();
                    $combo->comboItems()->create([
                        'service_id' => $serviceId,
                        'service_variant_id' => $variant ? $variant->id : null,
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
            $categories = $this->serviceCategoryService->getAll();
            $singleServices = Service::whereNull('deleted_at')
                ->whereDoesntHave('serviceVariants')
                ->get();
            $combo->load('comboItems.service');
            return view('admin.services.edit', compact('combo', 'categories', 'singleServices'))->with('service_type', 'combo');
        }

        // Check if it's a variant
        if ($type === 'variant') {
            $variant = ServiceVariant::findOrFail($id);
            $variant->load('service');
            $categories = $this->serviceCategoryService->getAll();
            $singleServices = Service::whereNull('deleted_at')
                ->whereDoesntHave('serviceVariants')
                ->get();
            return view('admin.services.edit', compact('variant', 'categories', 'singleServices'))->with('service_type', 'variant');
        }

        // It's a service
        $service = Service::with(['category', 'serviceVariants'])->findOrFail($id);
        $categories = $this->serviceCategoryService->getAll();
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
            'service_code' => 'nullable|string|max:50|unique:services,service_code,' . $id,
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:service_categories,id',
            'base_price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|in:Hoạt động,Vô hiệu hóa',
            'description' => 'nullable|string',
        ]);

        $data = $request->only([
            'service_code',
            'name',
            'category_id',
            'base_price',
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
     * Update variant service
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
     * Update combo service
     */
    protected function updateCombo(Request $request, $id)
    {
        $combo = Combo::findOrFail($id);

        $request->validate([
            'combo_name' => 'required|string|max:255',
            'category_id' => 'required|exists:service_categories,id',
            'combo_price' => 'required|numeric|min:0',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'exists:services,id',
            'combo_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'combo_status' => 'nullable|in:Hoạt động,Vô hiệu hóa',
            'combo_description' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $combo) {
            $combo->update([
                'name' => $request->input('combo_name'),
                'category_id' => $request->input('category_id'),
                'price' => $request->input('combo_price'),
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

            $combo->comboItems()->delete();
            $serviceIds = $request->input('service_ids');
            foreach ($serviceIds as $serviceId) {
                $service = Service::find($serviceId);
                if ($service) {
                    $variant = $service->serviceVariants()->first();
                    $combo->comboItems()->create([
                        'service_id' => $serviceId,
                        'service_variant_id' => $variant ? $variant->id : null,
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
