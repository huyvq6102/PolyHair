<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Support\Facades\Storage;

class ServiceService
{
    /**
     * Get all services with category.
     */
    public function getAll()
    {
        return Service::with('category')
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get one service by id.
     */
    public function getOne($id)
    {
        return Service::with(['category', 'serviceVariants'])->findOrFail($id);
    }

    /**
     * Get services by category.
     */
    public function getByCategory($categoryId)
    {
        return Service::where('category_id', $categoryId)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get services by category with pagination.
     */
    public function getByCategoryPaginated($categoryId, $limit = 10, $offset = 0)
    {
        return Service::where('category_id', $categoryId)
            ->orderBy('id', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Count services by category.
     */
    public function countByCategory($categoryId)
    {
        return Service::where('category_id', $categoryId)->count();
    }

    /**
     * Get related services.
     */
    public function getRelated($categoryId, $serviceId)
    {
        return Service::where('category_id', $categoryId)
            ->where('id', '!=', $serviceId)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get services with limit.
     */
    public function getWithLimit($limit = 10, $offset = 0)
    {
        return Service::orderBy('id', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Create a new service.
     */
    public function create(array $data)
    {
        return Service::create($data);
    }

    /**
     * Update a service.
     */
    public function update($id, array $data)
    {
        $service = Service::findOrFail($id);
        $service->update($data);
        return $service;
    }

    /**
     * Delete a service.
     */
    public function delete($id)
    {
        $service = Service::findOrFail($id);
        
        // Delete image if exists
        if ($service->image && Storage::disk('public')->exists('legacy/images/products/' . $service->image)) {
            Storage::disk('public')->delete('legacy/images/products/' . $service->image);
        }
        
        return $service->delete();
    }

    /**
     * Search services by name.
     */
    public function search($name)
    {
        return Service::with('category')
            ->where('name', 'like', "%{$name}%")
            ->get();
    }

    /**
     * Get service statistics by category.
     */
    public function getStatisticsByCategory()
    {
        return Service::selectRaw('
                service_categories.id,
                service_categories.name,
                COUNT(*) as so_luong
            ')
            ->join('service_categories', 'service_categories.id', '=', 'services.category_id')
            ->groupBy('service_categories.id', 'service_categories.name')
            ->get();
    }
}
