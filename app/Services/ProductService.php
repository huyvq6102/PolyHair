<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    /**
     * Get all products with category.
     */
    public function getAll()
    {
        return Product::with('category')
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get all products ordered by sale.
     */
    public function getAllBySale()
    {
        return Product::orderBy('sale', 'desc')->get();
    }

    /**
     * Get all products ordered by views.
     */
    public function getAllByViews()
    {
        return Product::orderBy('views', 'desc')->get();
    }

    /**
     * Get all products ordered by price (low to high).
     */
    public function getAllByPriceLow()
    {
        return Product::orderBy('price', 'asc')->get();
    }

    /**
     * Get all products ordered by price (high to low).
     */
    public function getAllByPriceHigh()
    {
        return Product::orderBy('price', 'desc')->get();
    }

    /**
     * Get one product by id.
     */
    public function getOne($id)
    {
        return Product::with('category')->findOrFail($id);
    }

    /**
     * Get products by category.
     */
    public function getByCategory($categoryId, $orderBy = 'id')
    {
        $query = Product::where('id_category', $categoryId);
        
        switch ($orderBy) {
            case 'sale':
                $query->orderBy('sale', 'desc');
                break;
            case 'views':
                $query->orderBy('views', 'desc');
                break;
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            default:
                $query->orderBy('id', 'desc');
        }
        
        return $query->get();
    }

    /**
     * Get products by category with pagination.
     */
    public function getByCategoryPaginated($categoryId, $limit = 10, $offset = 0)
    {
        return Product::where('id_category', $categoryId)
            ->orderBy('id', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Count products by category.
     */
    public function countByCategory($categoryId)
    {
        return Product::where('id_category', $categoryId)->count();
    }

    /**
     * Get related products.
     */
    public function getRelated($categoryId, $productId, $limit = 4)
    {
        return Product::where('id_category', $categoryId)
            ->where('id', '!=', $productId)
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get products by status.
     */
    public function getByStatus($status)
    {
        return Product::with('category')
            ->where('status', $status)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get products with limit.
     */
    public function getWithLimit($limit = 10, $offset = 0)
    {
        return Product::orderBy('id', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Get products on sale.
     */
    public function getOnSale($limit = 10, $offset = 0)
    {
        return Product::where('sale', '>', 0)
            ->orderBy('sale', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Count products on sale.
     */
    public function countOnSale()
    {
        return Product::where('sale', '>', 0)->count();
    }

    /**
     * Create a new product.
     */
    public function create(array $data)
    {
        $data['views'] = $data['views'] ?? 0;
        return Product::create($data);
    }

    /**
     * Update a product.
     */
    public function update($id, array $data)
    {
        $product = Product::findOrFail($id);
        $product->update($data);
        return $product;
    }

    /**
     * Delete a product.
     */
    public function delete($id)
    {
        $product = Product::findOrFail($id);
        
        // Delete image if exists
        if ($product->images && Storage::disk('public')->exists('legacy/images/products/' . $product->images)) {
            Storage::disk('public')->delete('legacy/images/products/' . $product->images);
        }
        
        return $product->delete();
    }

    /**
     * Update product views.
     */
    public function incrementViews($id)
    {
        return Product::where('id', $id)->increment('views');
    }

    /**
     * Search products by name.
     */
    public function search($name)
    {
        return Product::with('category')
            ->where('name', 'like', "%{$name}%")
            ->get();
    }

    /**
     * Get product statistics by category.
     */
    public function getStatisticsByCategory()
    {
        return Product::selectRaw('
                categories.id,
                categories.name,
                COUNT(*) as so_luong,
                MIN(products.price) as gia_min,
                MAX(products.price) as gia_max,
                AVG(products.price) as gia_avg
            ')
            ->join('categories', 'categories.id', '=', 'products.id_category')
            ->groupBy('categories.id', 'categories.name')
            ->get();
    }
}
