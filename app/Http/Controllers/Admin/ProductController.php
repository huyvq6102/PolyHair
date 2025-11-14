<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productService;
    protected $categoryService;

    public function __construct(ProductService $productService, CategoryService $categoryService)
    {
        $this->productService = $productService;
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $keyword = $request->get('keyword');
        
        if ($keyword) {
            $products = $this->productService->search($keyword);
        } else {
            $products = $this->productService->getAll();
        }

        return view('admin.products.index', compact('products', 'keyword'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = $this->categoryService->getAll();
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'price' => 'required|numeric|min:0',
            'sale' => 'nullable|numeric|min:0',
            'images' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'id_category' => 'required|exists:categories,id',
            'status' => 'required|integer|in:0,1',
            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('images')) {
            $image = $request->file('images');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('legacy/images/products'), $imageName);
            $validated['images'] = $imageName;
        }

        $this->productService->create($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Sản phẩm đã được tạo thành công!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = $this->productService->getOne($id);
        $categories = $this->categoryService->getAll();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'price' => 'required|numeric|min:0',
            'sale' => 'nullable|numeric|min:0',
            'images' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'id_category' => 'required|exists:categories,id',
            'status' => 'required|integer|in:0,1',
            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('images')) {
            $product = $this->productService->getOne($id);
            
            // Delete old image
            if ($product->images && file_exists(public_path('legacy/images/products/' . $product->images))) {
                unlink(public_path('legacy/images/products/' . $product->images));
            }
            
            $image = $request->file('images');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('legacy/images/products'), $imageName);
            $validated['images'] = $imageName;
        }

        $this->productService->update($id, $validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Sản phẩm đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->productService->delete($id);

        return redirect()->route('admin.products.index')
            ->with('success', 'Sản phẩm đã được xóa thành công!');
    }
}
