<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TypeService;
use Illuminate\Http\Request;

class TypeController extends Controller
{
    protected $typeService;

    public function __construct(TypeService $typeService)
    {
        $this->typeService = $typeService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $types = $this->typeService->getAll();
        return view('admin.types.index', compact('types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'images' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('images')) {
            $image = $request->file('images');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('legacy/images/categories'), $imageName);
            $validated['images'] = $imageName;
        }

        $this->typeService->create($validated);

        return redirect()->route('admin.types.index')
            ->with('success', 'Loại dịch vụ đã được tạo thành công!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $type = $this->typeService->getOne($id);
        return view('admin.types.edit', compact('type'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'images' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('images')) {
            $type = $this->typeService->getOne($id);
            
            // Delete old image
            if ($type->images && file_exists(public_path('legacy/images/categories/' . $type->images))) {
                unlink(public_path('legacy/images/categories/' . $type->images));
            }
            
            $image = $request->file('images');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('legacy/images/categories'), $imageName);
            $validated['images'] = $imageName;
        }

        $this->typeService->update($id, $validated);

        return redirect()->route('admin.types.index')
            ->with('success', 'Loại dịch vụ đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->typeService->delete($id);

        return redirect()->route('admin.types.index')
            ->with('success', 'Loại dịch vụ đã được xóa thành công!');
    }
}
