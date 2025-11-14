<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productService;
    protected $categoryService;

    public function __construct(ProductService $productService, CategoryService $categoryService)
    {
        $this->productService = $productService;
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $categories = $this->categoryService->getAll();
        $categoryId = $request->get('category');
        $sort = $request->get('sort', 'id');

        if ($categoryId) {
            $products = $this->productService->getByCategory($categoryId, $sort);
        } else {
            switch ($sort) {
                case 'sale':
                    $products = $this->productService->getAllBySale();
                    break;
                case 'views':
                    $products = $this->productService->getAllByViews();
                    break;
                case 'price_low':
                    $products = $this->productService->getAllByPriceLow();
                    break;
                case 'price_high':
                    $products = $this->productService->getAllByPriceHigh();
                    break;
                default:
                    $products = $this->productService->getAll();
            }
        }

        return view('site.product-list', compact('products', 'categories', 'categoryId', 'sort'));
    }

    /**
     * Display the specified product.
     */
    public function show($id)
    {
        $product = $this->productService->getOne($id);
        
        // Increment views
        $this->productService->incrementViews($id);
        
        // Get related products
        $relatedProducts = $this->productService->getRelated($product->id_category, $id, 4);

        return view('site.product-detail', compact('product', 'relatedProducts'));
    }

    /**
     * Search products.
     */
    public function search(Request $request)
    {
        $keyword = $request->get('keyword', '');
        $products = [];
        
        if ($keyword) {
            $products = $this->productService->search($keyword);
        }

        return view('site.search', compact('products', 'keyword'));
    }
}
