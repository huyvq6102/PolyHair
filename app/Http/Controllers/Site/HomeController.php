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
        $services = $this->serviceService->getWithLimit(6, 0);
        $news = $this->newsService->getWithLimit(3, 0);

        return view('site.home', compact('settings', 'types', 'products', 'productsOnSale', 'services', 'news'));
    }
}
