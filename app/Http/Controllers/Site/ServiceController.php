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
        $priceRange = $request->get('price_range');
        $minPrice = $request->get('min_price');
        $maxPrice = $request->get('max_price');
        
        // Xử lý price_range nếu có
        if ($priceRange && $priceRange !== 'custom') {
            $rangeParts = explode('-', $priceRange);
            if (count($rangeParts) == 2) {
                $minPrice = $rangeParts[0];
                $maxPrice = $rangeParts[1] == '999999999' ? null : $rangeParts[1];
            }
        }
        
        $sortBy = $request->get('sort_by', 'id_desc'); // 'id_desc', 'name_asc', 'name_desc', 'price_asc', 'price_desc'

        // Load active promotions trước để tính giá cuối cùng
        $activePromotions = \App\Models\Promotion::with(['services', 'combos', 'serviceVariants'])
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->where(function($query) {
                $now = \Carbon\Carbon::now();
                $query->where(function($q) use ($now) {
                    $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
                })->where(function($q) use ($now) {
                    $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
                });
            })
            ->get();

        $items = collect();

        // Get Services (single or variant)
        if ($filterType === 'all' || $filterType === 'service_single' || $filterType === 'service_variant') {
            $serviceQuery = Service::with(['category', 'serviceVariants.variantAttributes', 'ownedCombos']);

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

            // Filter by keyword - tìm sâu vào service name và variant names
            if ($keyword) {
                $serviceQuery->where(function($q) use ($keyword) {
                    // Tìm trong tên service
                    $q->where('name', 'like', "%{$keyword}%")
                      // Hoặc tìm trong tên các variants
                      ->orWhereHas('serviceVariants', function($variantQuery) use ($keyword) {
                          $variantQuery->where('name', 'like', "%{$keyword}%");
                      });
                });
            }

            $services = $serviceQuery->orderBy('id', 'desc')->get();
            
            foreach ($services as $service) {
                $finalPrice = 0;
                
                // Nếu service có variants, tính discount cho từng variant và lấy giá cuối cùng thấp nhất
                if ($service->serviceVariants->count() > 0) {
                    $activeVariants = $service->serviceVariants->where('is_active', true);
                    if ($activeVariants->count() == 0) {
                        $activeVariants = $service->serviceVariants;
                    }
                    
                    $bestFinalPrice = null;
                    foreach ($activeVariants as $variant) {
                        $variantFinalPrice = $this->calculateFinalPriceForVariant($variant, $activePromotions);
                        if ($bestFinalPrice === null || $variantFinalPrice < $bestFinalPrice) {
                            $bestFinalPrice = $variantFinalPrice;
                        }
                    }
                    $finalPrice = $bestFinalPrice ?? 0;
                } else {
                    // Service đơn - tính discount trực tiếp
                    $originalPrice = $service->base_price ?? 0;
                    $finalPrice = $this->calculateFinalPriceForService($service, $originalPrice, $activePromotions);
                }
                
                // Filter by price range - sử dụng finalPrice
                if ($minPrice !== null && $finalPrice < $minPrice) {
                    continue;
                }
                if ($maxPrice !== null && $finalPrice > $maxPrice) {
                    continue;
                }

                $serviceType = $service->serviceVariants->count() > 0 ? 'service_variant' : 'service_single';
                
                $items->push([
                    'type' => $serviceType,
                    'id' => $service->id,
                    'name' => $service->name,
                    'image' => $service->image,
                    'price' => $finalPrice, // Sử dụng finalPrice thay vì originalPrice
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

            // Filter by keyword - tìm sâu vào combo name và service names trong combo
            if ($keyword) {
                $comboQuery->where(function($q) use ($keyword) {
                    // Tìm trong tên combo
                    $q->where('name', 'like', "%{$keyword}%")
                      // Hoặc tìm trong tên các service thuộc combo
                      ->orWhereHas('services', function($serviceQuery) use ($keyword) {
                          $serviceQuery->where('name', 'like', "%{$keyword}%");
                      });
                });
            }

            $combos = $comboQuery->orderBy('id', 'desc')->get();
            
            foreach ($combos as $combo) {
                $originalPrice = $combo->price ?? 0;
                
                // Tính giá cuối cùng sau khi áp dụng promotion
                $finalPrice = $this->calculateFinalPriceForCombo($combo, $originalPrice, $activePromotions);
                
                // Filter by price range - sử dụng finalPrice
                if ($minPrice !== null && $finalPrice < $minPrice) {
                    continue;
                }
                if ($maxPrice !== null && $finalPrice > $maxPrice) {
                    continue;
                }
                
                $items->push([
                    'type' => 'combo',
                    'id' => $combo->id,
                    'name' => $combo->name,
                    'image' => $combo->image,
                    'price' => $finalPrice, // Sử dụng finalPrice thay vì originalPrice
                    'category' => $combo->category,
                    'link' => route('site.services.show', $combo->id),
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


        // If AJAX request, return JSON or HTML partial
        if ($request->ajax()) {
            $html = view('site.partials.service-list-items', compact('items', 'activePromotions'))->render();
            $pagination = view('site.partials.service-pagination', compact('items'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $pagination,
                'count' => $items->count()
            ]);
        }
        //dd($items, $types, $typeId, $categories, $filterType, $categoryId, $keyword, $minPrice, $maxPrice, $sortBy, $activePromotions);
        return view('site.service-list', compact('items', 'types', 'typeId', 'categories', 'filterType', 'categoryId', 'keyword', 'minPrice', 'maxPrice', 'sortBy', 'activePromotions'));
    }

    /**
     * Display the specified service or combo.
     */
    public function show($id)
    {
        // Kiểm tra xem là service hay combo
        $service = Service::find($id);
        $combo = null;
        
        if (!$service) {
            // Nếu không tìm thấy service, kiểm tra xem có phải combo không
            $combo = Combo::with([
                'category', 
                'comboItems.service.category',
                'comboItems.service.serviceVariants',
                'comboItems.serviceVariant.service'
            ])->find($id);
            
            if (!$combo) {
                abort(404, 'Dịch vụ hoặc combo không tồn tại');
            }
            
            // Render view cho combo
            return view('site.combo-detail', compact('combo'));
        }
        
        // Xử lý service như cũ
        $service = $this->serviceService->getOne($id);
        $relatedServices = $this->serviceService->getRelated($service->category_id ?? 0, $id);
        
        // Kiểm tra category hoặc tên dịch vụ để quyết định lấy ảnh từ thư mục nào
        $categoryName = strtolower($service->category->name ?? '');
        $serviceName = strtolower($service->name ?? '');
        $isGoiService = (strpos($categoryName, 'gội') !== false || strpos($serviceName, 'gội') !== false);
        $isNhuomService = (strpos($categoryName, 'nhuộm') !== false || strpos($serviceName, 'nhuộm') !== false);
        $isUonService = (strpos($categoryName, 'uốn') !== false || strpos($serviceName, 'uốn') !== false);
        
        if ($isUonService) {
            // Dịch vụ uốn - lấy từ thư mục uốn
            $imageDir = base_path('resources/views/image/uốn');
            $publicImageDir = public_path('legacy/images/uon');
        } elseif ($isNhuomService) {
            // Dịch vụ nhuộm - lấy từ thư mục nhuộm
            $imageDir = base_path('resources/views/image/nhuộm');
            $publicImageDir = public_path('legacy/images/nhuom');
        } elseif ($isGoiService) {
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

        // Load active promotions for discount calculation
        $now = \Carbon\Carbon::now();
        $activePromotions = \App\Models\Promotion::with(['services', 'combos', 'serviceVariants'])
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->where('apply_scope', 'service') // Chỉ lấy promotion có apply_scope = 'service'
            ->where(function($query) use ($now) {
                $query->where(function($q) use ($now) {
                    $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
                })->where(function($q) use ($now) {
                    $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
                });
            })
            ->get();

        return view('site.service-detail', compact('service', 'relatedServices', 'randomImages', 'activePromotions'));
    }

    /**
     * Tính giá cuối cùng cho variant sau khi áp dụng promotion
     */
    protected function calculateFinalPriceForVariant($variant, $activePromotions)
    {
        $originalPrice = $variant->price ?? 0;
        if ($originalPrice <= 0) {
            return 0;
        }

        $discount = 0;
        $now = \Carbon\Carbon::now();

        foreach ($activePromotions ?? [] as $promo) {
            if ($promo->apply_scope !== 'service') {
                continue;
            }
            if ($promo->status !== 'active') continue;
            if ($promo->start_date && $promo->start_date > $now) continue;
            if ($promo->end_date && $promo->end_date < $now) continue;

            // Check usage_limit
            if ($promo->usage_limit) {
                $totalUsage = \App\Models\PromotionUsage::where('promotion_id', $promo->id)->count();
                if ($totalUsage >= $promo->usage_limit) {
                    continue;
                }
            }

            $applies = false;
            $hasSpecificServices = ($promo->services && $promo->services->count() > 0)
                || ($promo->combos && $promo->combos->count() > 0)
                || ($promo->serviceVariants && $promo->serviceVariants->count() > 0);
            $applyToAll = !$hasSpecificServices ||
                (($promo->services ? $promo->services->count() : 0) +
                 ($promo->combos ? $promo->combos->count() : 0) +
                 ($promo->serviceVariants ? $promo->serviceVariants->count() : 0)) >= 20;

            // Kiểm tra variant có trong promotion không
            if ($applyToAll) {
                $applies = true;
            } elseif ($promo->serviceVariants && $promo->serviceVariants->contains('id', $variant->id)) {
                $applies = true;
            } elseif ($variant->service_id && $promo->services && $promo->services->contains('id', $variant->service_id)) {
                $applies = true;
            }

            if ($applies) {
                $currentDiscount = 0;
                if ($promo->discount_type === 'percent') {
                    $currentDiscount = ($originalPrice * ($promo->discount_percent ?? 0)) / 100;
                    if ($promo->max_discount_amount) {
                        $currentDiscount = min($currentDiscount, $promo->max_discount_amount);
                    }
                } else {
                    $currentDiscount = min($promo->discount_amount ?? 0, $originalPrice);
                }

                if ($currentDiscount > $discount) {
                    $discount = $currentDiscount;
                }
            }
        }

        return max(0, $originalPrice - $discount);
    }

    /**
     * Tính giá cuối cùng cho service sau khi áp dụng promotion
     */
    protected function calculateFinalPriceForService($service, $originalPrice, $activePromotions)
    {
        if ($originalPrice <= 0) {
            return 0;
        }

        $discount = 0;
        $now = \Carbon\Carbon::now();

        foreach ($activePromotions ?? [] as $promo) {
            // Chỉ áp dụng discount trực tiếp cho service khi promotion được cấu hình "By service"
            if ($promo->apply_scope !== 'service') {
                continue;
            }
            if ($promo->status !== 'active') continue;
            if ($promo->start_date && $promo->start_date > $now) continue;
            if ($promo->end_date && $promo->end_date < $now) continue;

            // Check usage_limit
            if ($promo->usage_limit) {
                $totalUsage = \App\Models\PromotionUsage::where('promotion_id', $promo->id)->count();
                if ($totalUsage >= $promo->usage_limit) {
                    continue;
                }
            }

            $applies = false;
            $hasSpecificServices = ($promo->services && $promo->services->count() > 0)
                || ($promo->combos && $promo->combos->count() > 0)
                || ($promo->serviceVariants && $promo->serviceVariants->count() > 0);
            $applyToAll = !$hasSpecificServices ||
                (($promo->services ? $promo->services->count() : 0) +
                 ($promo->combos ? $promo->combos->count() : 0) +
                 ($promo->serviceVariants ? $promo->serviceVariants->count() : 0)) >= 20;

            // Kiểm tra service có variants hay không
            if ($service->serviceVariants->count() > 0) {
                // Service có variants - kiểm tra từng variant
                foreach ($service->serviceVariants->where('is_active', true) as $variant) {
                    if ($applyToAll) {
                        $applies = true;
                        break;
                    } elseif ($promo->serviceVariants && $promo->serviceVariants->contains('id', $variant->id)) {
                        $applies = true;
                        break;
                    } elseif ($promo->services && $promo->services->contains('id', $service->id)) {
                        $applies = true;
                        break;
                    }
                }
            } else {
                // Service đơn
                if ($applyToAll) {
                    $applies = true;
                } elseif ($promo->services && $promo->services->contains('id', $service->id)) {
                    $applies = true;
                }
            }

            if ($applies) {
                $currentDiscount = 0;
                if ($promo->discount_type === 'percent') {
                    $currentDiscount = ($originalPrice * ($promo->discount_percent ?? 0)) / 100;
                    if ($promo->max_discount_amount) {
                        $currentDiscount = min($currentDiscount, $promo->max_discount_amount);
                    }
                } else {
                    $currentDiscount = min($promo->discount_amount ?? 0, $originalPrice);
                }

                if ($currentDiscount > $discount) {
                    $discount = $currentDiscount;
                }
            }
        }

        return max(0, $originalPrice - $discount);
    }

    /**
     * Tính giá cuối cùng cho combo sau khi áp dụng promotion
     */
    protected function calculateFinalPriceForCombo($combo, $originalPrice, $activePromotions)
    {
        if ($originalPrice <= 0) {
            return 0;
        }

        $discount = 0;
        $now = \Carbon\Carbon::now();

        foreach ($activePromotions ?? [] as $promo) {
            // Chỉ áp dụng discount trực tiếp cho combo khi promotion được cấu hình "By service"
            if ($promo->apply_scope !== 'service') {
                continue;
            }
            if ($promo->status !== 'active') continue;
            if ($promo->start_date && $promo->start_date > $now) continue;
            if ($promo->end_date && $promo->end_date < $now) continue;

            // Check usage_limit
            if ($promo->usage_limit) {
                $totalUsage = \App\Models\PromotionUsage::where('promotion_id', $promo->id)->count();
                if ($totalUsage >= $promo->usage_limit) {
                    continue;
                }
            }

            $applies = false;
            $hasSpecificServices = ($promo->services && $promo->services->count() > 0)
                || ($promo->combos && $promo->combos->count() > 0)
                || ($promo->serviceVariants && $promo->serviceVariants->count() > 0);
            $applyToAll = !$hasSpecificServices ||
                (($promo->services ? $promo->services->count() : 0) +
                 ($promo->combos ? $promo->combos->count() : 0) +
                 ($promo->serviceVariants ? $promo->serviceVariants->count() : 0)) >= 20;

            if ($applyToAll) {
                $applies = true;
            } elseif ($promo->combos && $promo->combos->contains('id', $combo->id)) {
                $applies = true;
            }

            if ($applies) {
                $currentDiscount = 0;
                if ($promo->discount_type === 'percent') {
                    $currentDiscount = ($originalPrice * ($promo->discount_percent ?? 0)) / 100;
                    if ($promo->max_discount_amount) {
                        $currentDiscount = min($currentDiscount, $promo->max_discount_amount);
                    }
                } else {
                    $currentDiscount = min($promo->discount_amount ?? 0, $originalPrice);
                }

                if ($currentDiscount > $discount) {
                    $discount = $currentDiscount;
                }
            }
        }

        return max(0, $originalPrice - $discount);
    }
}
