<?php

namespace App\Services;

use App\Models\ServiceCategory;

class ServiceCategoryService
{
    /**
     * Get all service categories.
     */
    public function getAll()
    {
        return ServiceCategory::orderBy('id', 'desc')->get();
    }

    /**
     * Get one service category by id.
     */
    public function getOne($id)
    {
        return ServiceCategory::findOrFail($id);
    }

    /**
     * Create a new service category.
     */
    public function create(array $data)
    {
        return ServiceCategory::create($data);
    }

    /**
     * Update a service category.
     */
    public function update($id, array $data)
    {
        $category = ServiceCategory::findOrFail($id);
        $category->update($data);
        return $category;
    }

    /**
     * Delete a service category.
     */
    public function delete($id)
    {
        $category = ServiceCategory::findOrFail($id);
        return $category->delete();
    }

    /**
     * Search service categories by name.
     */
    public function search($name)
    {
        return ServiceCategory::where('name', 'like', "%{$name}%")->get();
    }
}

