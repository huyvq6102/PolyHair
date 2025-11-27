<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Combo;
use App\Services\ServiceService;
use App\Services\TypeService;
use App\Services\ServiceCategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    protected $serviceService;
    protected $typeService;
    protected $categoryService;

    public function __construct(
        ServiceService $serviceService, 
        TypeService $typeService,
        ServiceCategoryService $categoryService
    ) {
        $this->serviceService = $serviceService;
        $this->typeService = $typeService;
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of services.
     */
    public function index(Request $request)
    {
        $types = $this->typeService->getAll();
        $categories = $this->categoryService->getAll();
        
        // Get filter parameters
        $filterType = $request->get('filter_type', 'all'); // 'all', 'service', 'combo'
        $categoryId = $request->get('category');
        $typeId = $request->get('type'); // For backward compatibility

        $items = collect();

        // Get Services
        if ($filterType === 'all' || $filterType === 'service') {
            $serviceQuery = Service::with(['category', 'serviceVariants', 'ownedCombos']);

            if ($categoryId) {
                $serviceQuery->where('category_id', $categoryId);
            } elseif ($typeId) {
                // For backward compatibility
                $serviceQuery->where('category_id', $typeId);
            }

            $services = $serviceQuery->orderBy('id', 'desc')->get();
            
            foreach ($services as $service) {
                $items->push([
                    'type' => 'service',
                    'id' => $service->id,
                    'name' => $service->name,
                    'image' => $service->image,
                    'price' => $service->serviceVariants->where('is_active', true)->min('price') 
                             ?? $service->serviceVariants->min('price') 
                             ?? $service->base_price 
                             ?? 0,
                    'category' => $service->category,
                    'serviceVariants' => $service->serviceVariants,
                    'link' => route('site.services.show', $service->id),
                ]);
            }
        }

        // Get Combos
        if ($filterType === 'all' || $filterType === 'combo') {
            $comboQuery = Combo::with(['category', 'services']);

            if ($categoryId) {
                $comboQuery->where('category_id', $categoryId);
            }

            $combos = $comboQuery->orderBy('id', 'desc')->get();
            
            foreach ($combos as $combo) {
                $items->push([
                    'type' => 'combo',
                    'id' => $combo->id,
                    'name' => $combo->name,
                    'image' => $combo->image,
                    'price' => $combo->price ?? 0,
                    'category' => $combo->category,
                    'link' => '#',
                ]);
            }
        }

        // Sort by id desc and paginate
        $items = $items->sortByDesc('id')->values();
        $perPage = 6;
        $currentPage = $request->get('page', 1);
        
        // Get query parameters without 'page' to avoid duplication
        $queryParams = $request->except('page');
        
        $items = new \Illuminate\Pagination\LengthAwarePaginator(
            $items->forPage($currentPage, $perPage),
            $items->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $queryParams
            ]
        );

        return view('site.service-list', compact('items', 'types', 'typeId', 'categories', 'filterType', 'categoryId'));
    }

    /**
     * Display the specified service.
     */
    public function show($id)
    {
        $service = $this->serviceService->getOne($id);
        $relatedServices = $this->serviceService->getRelated($service->category_id ?? 0, $id);

        return view('site.service-detail', compact('service', 'relatedServices'));
    }
}
