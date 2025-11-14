<?php

namespace App\Services;

use App\Models\News;
use Illuminate\Support\Facades\Storage;

class NewsService
{
    /**
     * Get all news with user.
     */
    public function getAll()
    {
        return News::with('user')
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get news with limit.
     */
    public function getWithLimit($limit = 10, $offset = 0)
    {
        return News::with('user')
            ->orderBy('id', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Get one news by id.
     */
    public function getOne($id)
    {
        return News::with('user')->findOrFail($id);
    }

    /**
     * Create a new news.
     */
    public function create(array $data)
    {
        return News::create($data);
    }

    /**
     * Update a news.
     */
    public function update($id, array $data)
    {
        $news = News::findOrFail($id);
        $news->update($data);
        return $news;
    }

    /**
     * Increment news views.
     */
    public function incrementViews($id)
    {
        return News::where('id', $id)->increment('views');
    }

    /**
     * Delete a news.
     */
    public function delete($id)
    {
        $news = News::findOrFail($id);
        
        // Delete image if exists
        if ($news->images && Storage::disk('public')->exists('legacy/images/sliders/' . $news->images)) {
            Storage::disk('public')->delete('legacy/images/sliders/' . $news->images);
        }
        
        return $news->delete();
    }

    /**
     * Search news by title.
     */
    public function search($title)
    {
        return News::with('user')
            ->where('title', 'like', "%{$title}%")
            ->get();
    }
}

