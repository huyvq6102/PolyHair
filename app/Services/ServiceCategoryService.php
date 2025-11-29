<?php

namespace App\Services;

use App\Models\ServiceCategory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class ServiceCategoryService
{
    /**
     * Get all service categories.
     */
    public function getAll()
    {
        $query = ServiceCategory::query();

        if (Schema::hasColumn('service_categories', 'sort_order')) {
            $query->orderBy('sort_order');
        }

        return $query->orderBy('name')->get();
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
        return ServiceCategory::create($this->preparePayload($data));
    }

    /**
     * Update a service category.
     */
    public function update($id, array $data)
    {
        $category = ServiceCategory::findOrFail($id);
        $category->update($this->preparePayload($data, $category));
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

    /**
     * Prepare payload for insert/update.
     */
    protected function preparePayload(array $data, ?ServiceCategory $category = null): array
    {
        $name = Arr::get($data, 'name');
        $slug = Arr::get($data, 'slug');

        if (!$slug && $name) {
            $slug = Str::slug($name);
        }

        $payload = [
            'name' => $name,
            'description' => Arr::get($data, 'description'),
        ];

        if (Schema::hasColumn('service_categories', 'slug')) {
            $payload['slug'] = $slug;
        }

        if (Schema::hasColumn('service_categories', 'sort_order')) {
            $payload['sort_order'] = Arr::get($data, 'sort_order', 0);
        }

        if (Schema::hasColumn('service_categories', 'is_active')) {
            $payload['is_active'] = Arr::get($data, 'is_active', true);
        }

        return $payload;
    }
}

