<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ServiceService;
use App\Services\ServiceCategoryService;
use Illuminate\Http\Request;

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
        return view('admin.services.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category_id' => 'required|exists:service_categories,id',
            'status' => 'nullable|in:Hoạt động,Vô hiệu hóa',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('legacy/images/products'), $imageName);
            $validated['image'] = $imageName;
        }

        $validated['status'] = $validated['status'] ?? 'Hoạt động';

        $this->serviceService->create($validated);

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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category_id' => 'required|exists:service_categories,id',
            'status' => 'nullable|in:Hoạt động,Vô hiệu hóa',
        ]);

        if ($request->hasFile('image')) {
            $service = $this->serviceService->getOne($id);
            
            // Delete old image
            if ($service->image && file_exists(public_path('legacy/images/products/' . $service->image))) {
                unlink(public_path('legacy/images/products/' . $service->image));
            }
            
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('legacy/images/products'), $imageName);
            $validated['image'] = $imageName;
        }

        $this->serviceService->update($id, $validated);

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
}
