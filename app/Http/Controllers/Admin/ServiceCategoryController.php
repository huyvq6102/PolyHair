<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ServiceCategoryService;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function __construct(protected ServiceCategoryService $serviceCategoryService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $keyword = $request->get('keyword');
        $categories = $keyword
            ? $this->serviceCategoryService->search($keyword)
            : $this->serviceCategoryService->getAll();

        return view('admin.service_categories.index', compact('categories', 'keyword'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.service_categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);
        $this->serviceCategoryService->create($validated);

        return redirect()->route('admin.service-categories.index')
            ->with('success', 'Danh mục dịch vụ đã được tạo!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $category = $this->serviceCategoryService->getOne($id);
        return view('admin.service_categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = $this->serviceCategoryService->getOne($id);
        $validated = $this->validateRequest($request, $category->id);
        $this->serviceCategoryService->update($category->id, $validated);

        return redirect()->route('admin.service-categories.index')
            ->with('success', 'Danh mục dịch vụ đã được cập nhật!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->serviceCategoryService->delete($id);

        return redirect()->route('admin.service-categories.index')
            ->with('success', 'Danh mục dịch vụ đã được xóa!');
    }

    protected function validateRequest(Request $request, ?int $ignoreId = null): array
    {
        $uniqueRule = 'unique:service_categories,slug';
        if ($ignoreId) {
            $uniqueRule .= ',' . $ignoreId;
        }

        return $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', $uniqueRule],
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
    }
}

