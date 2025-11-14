<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class CategoryService
{
    /**
     * Get all categories.
     */
    public function getAll()
    {
        return Category::orderBy('id', 'desc')->get();
    }

    /**
     * Get one category by id.
     */
    public function getOne($id)
    {
        return Category::findOrFail($id);
    }

    /**
     * Create a new category.
     */
    public function create(array $data)
    {
        return Category::create($data);
    }

    /**
     * Update a category.
     */
    public function update($id, array $data)
    {
        $category = Category::findOrFail($id);
        $category->update($data);
        return $category;
    }

    /**
     * Delete a category.
     */
    public function delete($id)
    {
        $category = Category::findOrFail($id);
        
        // Delete image if exists
        if ($category->images && Storage::disk('public')->exists('legacy/images/categories/' . $category->images)) {
            Storage::disk('public')->delete('legacy/images/categories/' . $category->images);
        }
        
        return $category->delete();
    }

    /**
     * Search categories by name.
     */
    public function search($name)
    {
        return Category::where('name', 'like', "%{$name}%")->get();
    }
}
