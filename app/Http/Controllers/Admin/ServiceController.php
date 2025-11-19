<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\ServiceCategoryService;
use App\Services\ServiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

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

        if ($keyword) {
            $services = $this->serviceService->search($keyword);
        } else {
            $services = $this->serviceService->getAll();
        }

        return view('admin.services.index', compact('services', 'keyword'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = $this->serviceCategoryService->getAll();
        $service = null;
        return view('admin.services.create', compact('categories', 'service'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validateServiceRequest($request);

        $serviceData = $this->extractServiceData($validated, $request);
        $variants = $validated['variants'] ?? [];
        $combos = $validated['combos'] ?? [];

        $this->serviceService->create($serviceData, $variants, $combos);

        return redirect()->route('admin.services.index')
            ->with('success', 'Dịch vụ đã được tạo thành công!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $service = $this->serviceService->getOne($id);
        $categories = $this->serviceCategoryService->getAll();

        return view('admin.services.edit', compact('service', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $service = $this->serviceService->getOne($id);
        $validated = $this->validateServiceRequest($request, true);

        $serviceData = $this->extractServiceData($validated, $request, $service);
        $variants = $validated['variants'] ?? [];
        $combos = $validated['combos'] ?? [];

        $this->serviceService->update($service->id, $serviceData, $variants, $combos);

        return redirect()->route('admin.services.index')
            ->with('success', 'Dịch vụ đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->serviceService->delete($id);

        return redirect()->route('admin.services.index')
            ->with('success', 'Dịch vụ đã được xóa thành công!');
    }

    /**
     * Validate incoming request for store/update.
     */
    protected function validateServiceRequest(Request $request, bool $isUpdate = false): array
    {
        $imageRule = $isUpdate ? 'nullable' : 'required';

        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => [$imageRule, 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'category_id' => 'required|exists:service_categories,id',
            'status' => 'nullable|in:Hoạt động,Vô hiệu hóa',
            'base_price' => 'nullable|numeric|min:0',
            'base_duration' => 'nullable|integer|min:0',
            'variants' => 'required|array|min:1',
            'variants.*.id' => 'nullable|exists:service_variants,id',
            'variants.*.name' => 'required|string|max:255',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.duration' => 'required|integer|min:0',
            'variants.*.is_default' => 'nullable|boolean',
            'variants.*.is_active' => 'nullable|boolean',
            'variants.*.notes' => 'nullable|string',
            'variants.*.attributes' => 'nullable|array',
            'variants.*.attributes.*.name' => 'nullable|string|max:100',
            'variants.*.attributes.*.value' => 'nullable|string|max:100',
            'combos' => 'nullable|array',
            'combos.*.id' => 'nullable|exists:combos,id',
            'combos.*.name' => 'nullable|string|max:255',
            'combos.*.slug' => 'nullable|string|max:255',
            'combos.*.description' => 'nullable|string',
            'combos.*.price' => 'nullable|numeric|min:0',
            'combos.*.status' => 'nullable|in:Hoạt động,Vô hiệu hóa',
            'combos.*.sort_order' => 'nullable|integer|min:0',
            'combos.*.variant_uids' => 'nullable|array|min:1',
            'combos.*.variant_uids.*' => 'nullable|string',
        ]);
    }

    /**
     * Extract service data and handle image upload.
     */
    protected function extractServiceData(array $validated, Request $request, ?Service $service = null): array
    {
        $data = Arr::only($validated, [
            'name',
            'description',
            'category_id',
            'status',
            'base_price',
            'base_duration',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('legacy/images/products'), $imageName);
            $data['image'] = $imageName;

            if ($service && $service->image && file_exists(public_path('legacy/images/products/' . $service->image))) {
                unlink(public_path('legacy/images/products/' . $service->image));
            }
        } elseif ($service) {
            $data['image'] = $service->image;
        }

        if (empty($data['status'])) {
            $data['status'] = 'Hoạt động';
        }

        return $data;
    }
}
