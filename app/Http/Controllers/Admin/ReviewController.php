<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Service;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Review::with(['user', 'service', 'appointment.appointmentDetails.serviceVariant.service', 'appointment.appointmentDetails.combo']);

        // Filter by rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Filter by service
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        // Filter by date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by time (hour)
        if ($request->filled('time_from')) {
            $query->whereTime('created_at', '>=', $request->time_from);
        }
        if ($request->filled('time_to')) {
            $query->whereTime('created_at', '<=', $request->time_to);
        }

        // Only show non-hidden reviews by default, but allow admin to see all
        if (!$request->has('show_hidden')) {
            $query->where('is_hidden', false);
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(10);
        $services = Service::whereNull('deleted_at')->orderBy('name')->get();

        return view('admin.reviews.index', compact('reviews', 'services'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $review = Review::with(['user', 'service', 'appointment', 'employee'])
            ->findOrFail($id);

        return view('admin.reviews.show', compact('review'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $review = Review::with(['user', 'service', 'appointment'])
            ->findOrFail($id);

        return view('admin.reviews.edit', compact('review'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:5000',
        ]);

        $review = Review::findOrFail($id);
        $review->update([
            'comment' => $validated['comment'],
        ]);

        return redirect()->route('admin.reviews.show', $review->id)
            ->with('success', 'Bình luận đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $review = Review::findOrFail($id);
        $review->delete();

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Bình luận đã được xóa vĩnh viễn thành công!');
    }

    /**
     * Hide/Unhide the specified resource.
     */
    public function hide(string $id)
    {
        $review = Review::findOrFail($id);
        $review->update([
            'is_hidden' => !$review->is_hidden,
        ]);

        $message = $review->is_hidden 
            ? 'Bình luận đã được ẩn thành công!' 
            : 'Bình luận đã được hiển thị lại thành công!';

        return redirect()->route('admin.reviews.index')
            ->with('success', $message);
    }
}

