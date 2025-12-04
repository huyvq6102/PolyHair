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
        $filterType = $request->get('filter_type', 'all'); // 'all', 'service_single', 'service_variant', 'combo'
        $categoryId = $request->get('category');
        $typeId = $request->get('type'); // For backward compatibility
        $keyword = $request->get('keyword', '');
        $minPrice = $request->get('min_price');
        $maxPrice = $request->get('max_price');
        $sortBy = $request->get('sort_by', 'id_desc'); // 'id_desc', 'name_asc', 'name_desc', 'price_asc', 'price_desc'

        $items = collect();

        // Get Services (single or variant)
        if ($filterType === 'all' || $filterType === 'service_single' || $filterType === 'service_variant') {
            $serviceQuery = Service::with(['category', 'serviceVariants', 'ownedCombos']);

            // Filter by service type (single or variant)
            if ($filterType === 'service_single') {
                $serviceQuery->whereDoesntHave('serviceVariants');
            } elseif ($filterType === 'service_variant') {
                $serviceQuery->whereHas('serviceVariants');
            }

            // Filter by category
            if ($categoryId) {
                $serviceQuery->where('category_id', $categoryId);
            } elseif ($typeId) {
                // For backward compatibility
                $serviceQuery->where('category_id', $typeId);
            }

            // Filter by keyword (name)
            if ($keyword) {
                $serviceQuery->where('name', 'like', "%{$keyword}%");
            }

            $services = $serviceQuery->orderBy('id', 'desc')->get();
            
            foreach ($services as $service) {
                $price = $service->serviceVariants->where('is_active', true)->min('price') 
                        ?? $service->serviceVariants->min('price') 
                        ?? $service->base_price 
                        ?? 0;
                
                // Filter by price range
                if ($minPrice !== null && $price < $minPrice) {
                    continue;
                }
                if ($maxPrice !== null && $price > $maxPrice) {
                    continue;
                }

                $serviceType = $service->serviceVariants->count() > 0 ? 'service_variant' : 'service_single';
                
                $items->push([
                    'type' => $serviceType,
                    'id' => $service->id,
                    'name' => $service->name,
                    'image' => $service->image,
                    'price' => $price,
                    'category' => $service->category,
                    'serviceVariants' => $service->serviceVariants,
                    'link' => route('site.services.show', $service->id),
                ]);
            }
        }

        // Get Combos
        if ($filterType === 'all' || $filterType === 'combo') {
            $comboQuery = Combo::with(['category', 'services']);

            // Filter by category
            if ($categoryId) {
                $comboQuery->where('category_id', $categoryId);
            }

            // Filter by keyword (name)
            if ($keyword) {
                $comboQuery->where('name', 'like', "%{$keyword}%");
            }

            $combos = $comboQuery->orderBy('id', 'desc')->get();
            
            foreach ($combos as $combo) {
                $price = $combo->price ?? 0;
                
                // Filter by price range
                if ($minPrice !== null && $price < $minPrice) {
                    continue;
                }
                if ($maxPrice !== null && $price > $maxPrice) {
                    continue;
                }
                
                $items->push([
                    'type' => 'combo',
                    'id' => $combo->id,
                    'name' => $combo->name,
                    'image' => $combo->image,
                    'price' => $price,
                    'category' => $combo->category,
                    'link' => '#',
                ]);
            }
        }

        // Sort items
        switch ($sortBy) {
            case 'name_asc':
                $items = $items->sortBy('name')->values();
                break;
            case 'name_desc':
                $items = $items->sortByDesc('name')->values();
                break;
            case 'price_asc':
                $items = $items->sortBy('price')->values();
                break;
            case 'price_desc':
                $items = $items->sortByDesc('price')->values();
                break;
            default: // 'id_desc'
                $items = $items->sortByDesc('id')->values();
                break;
        }

        // Paginate
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

        return view('site.service-list', compact('items', 'types', 'typeId', 'categories', 'filterType', 'categoryId', 'keyword', 'minPrice', 'maxPrice', 'sortBy'));
    }

    /**
     * Display the specified service.
     */
    public function show($id)
    {
        $service = $this->serviceService->getOne($id);
        $relatedServices = $this->serviceService->getRelated($service->category_id ?? 0, $id);
        
        // Kiểm tra category hoặc tên dịch vụ để quyết định lấy ảnh từ thư mục nào
        $categoryName = strtolower($service->category->name ?? '');
        $serviceName = strtolower($service->name ?? '');
        $isGoiService = (strpos($categoryName, 'gội') !== false || strpos($serviceName, 'gội') !== false);
        
        if ($isGoiService) {
            // Dịch vụ gội - lấy từ thư mục gội
            $imageDir = base_path('resources/views/image/gội');
            $publicImageDir = public_path('legacy/images/goi');
        } else {
            // Dịch vụ cắt (mặc định) - lấy từ thư mục Cắt
            $imageDir = base_path('resources/views/image/Cắt');
            $publicImageDir = public_path('legacy/images/cat');
        }
        
        $randomImages = [];
        if (is_dir($imageDir)) {
            $allImages = array_filter(scandir($imageDir), function($file) {
                return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']);
            });
            $allImages = array_values($allImages); // Reset keys
            if (count($allImages) > 0) {
                shuffle($allImages);
                $randomImages = array_slice($allImages, 0, 3);
            }
        }
        
        // Đảm bảo ảnh đã được copy vào public để hiển thị
        if (!is_dir($publicImageDir)) {
            mkdir($publicImageDir, 0755, true);
        }
        foreach ($randomImages as $img) {
            $sourcePath = $imageDir . '/' . $img;
            $destPath = $publicImageDir . '/' . $img;
            if (file_exists($sourcePath) && !file_exists($destPath)) {
                copy($sourcePath, $destPath);
            }
        }

        return view('site.service-detail', compact('service', 'relatedServices', 'randomImages'));
    }
}
