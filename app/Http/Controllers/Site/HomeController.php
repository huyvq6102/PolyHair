<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Services\ServiceService;
use App\Services\TypeService;
use App\Services\SettingService;
use App\Services\NewsService;

class HomeController extends Controller
{
    protected $productService;
    protected $serviceService;
    protected $typeService;
    protected $settingService;
    protected $newsService;

    public function __construct(
        ProductService $productService,
        ServiceService $serviceService,
        TypeService $typeService,
        SettingService $settingService,
        NewsService $newsService
    ) {
        $this->productService = $productService;
        $this->serviceService = $serviceService;
        $this->typeService = $typeService;
        $this->settingService = $settingService;
        $this->newsService = $newsService;
    }

    public function index()
    {
        $settings = $this->settingService->getFirst();
        $types = $this->typeService->getAll();
        $products = $this->productService->getWithLimit(8, 0);
        $productsOnSale = $this->productService->getOnSale(8, 0);
        $services = $this->serviceService->getMostBooked(6, 0); // 6 services = 2 hàng x 3 cột
        $news = $this->newsService->getWithLimit(3, 0);

        // Load active promotions
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

        return view('site.home', compact('settings', 'types', 'products', 'productsOnSale', 'services', 'news', 'activePromotions'));
    }
}
