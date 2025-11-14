<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\NewsService;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    protected $newsService;

    public function __construct(NewsService $newsService)
    {
        $this->newsService = $newsService;
    }

    /**
     * Display a listing of news/blog.
     */
    public function index()
    {
        $news = $this->newsService->getAll();
        return view('site.blog', compact('news'));
    }

    /**
     * Display the specified news/blog.
     */
    public function show($id)
    {
        $news = $this->newsService->getOne($id);
        
        // Increment views
        $this->newsService->incrementViews($id);
        
        // Get latest news
        $latestNews = $this->newsService->getWithLimit(5, 0);

        return view('site.blog-detail', compact('news', 'latestNews'));
    }

    /**
     * Search news/blog.
     */
    public function search(Request $request)
    {
        $keyword = $request->get('keyword', '');
        $news = [];
        
        if ($keyword) {
            $news = $this->newsService->search($keyword);
        }

        return view('site.search-blog', compact('news', 'keyword'));
    }
}
