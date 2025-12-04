<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    /**
     * Display a listing of reviews for reference.
     */
    public function index(Request $request)
    {
        $query = Review::with([
                'user', 
                'service', 
                'employee',
                'appointment.appointmentDetails.serviceVariant.service',
                'appointment.appointmentDetails.combo'
            ])
            ->where('is_hidden', false)
            ->orderBy('created_at', 'desc');

        // Filter by service
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        // Filter by rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        $reviews = $query->paginate(10);
        $services = Service::whereNull('deleted_at')
            ->where('status', 'Hoạt động')
            ->orderBy('name')
            ->get();

        return view('site.reviews.index', compact('reviews', 'services'));
    }

    /**
     * Show the form for creating a new review.
     */
    public function create(Request $request)
    {
        $appointmentId = $request->get('appointment_id');
        
        if (!$appointmentId) {
            return redirect()->back()->with('error', 'Vui lòng chọn lịch hẹn để đánh giá.');
        }

        $appointment = Appointment::with(['user', 'employee', 'appointmentDetails.serviceVariant.service'])
            ->findOrFail($appointmentId);

        // Check if user owns this appointment
        if (Auth::id() != $appointment->user_id) {
            return redirect()->back()->with('error', 'Bạn không có quyền đánh giá lịch hẹn này.');
        }

        // Check if appointment is completed
        if ($appointment->status !== 'Hoàn thành') {
            return redirect()->back()->with('error', 'Chỉ có thể đánh giá sau khi dịch vụ đã hoàn thành.');
        }

        // Check if already reviewed - prevent duplicate reviews (STRICT CHECK)
        $existingReview = Review::where('appointment_id', $appointmentId)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingReview) {
            return redirect()->route('site.appointment.show', $appointmentId)
                ->with('warning', 'Bạn đã đánh giá lịch hẹn này rồi. Mỗi lịch hẹn chỉ có thể đánh giá một lần. Vui lòng sửa đánh giá hiện có nếu muốn thay đổi.');
        }

        // Get services from appointment
        $services = [];
        foreach ($appointment->appointmentDetails as $detail) {
            if ($detail->serviceVariant && $detail->serviceVariant->service) {
                $serviceId = $detail->serviceVariant->service->id;
                if (!isset($services[$serviceId])) {
                    $services[$serviceId] = $detail->serviceVariant->service;
                }
            }
        }

        return view('site.reviews.create', compact('appointment', 'services'));
    }

    /**
     * Store a newly created review in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'service_id' => 'nullable|exists:services,id',
            'employee_id' => 'nullable|exists:employees,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:5000',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $appointment = Appointment::findOrFail($validated['appointment_id']);

        // Check if user owns this appointment
        if (Auth::id() != $appointment->user_id) {
            return redirect()->back()->with('error', 'Bạn không có quyền đánh giá lịch hẹn này.');
        }

        // Check if appointment is completed
        if ($appointment->status !== 'Hoàn thành') {
            return redirect()->back()->with('error', 'Chỉ có thể đánh giá sau khi dịch vụ đã hoàn thành.');
        }

        // Check if already reviewed - prevent duplicate reviews (STRICT CHECK)
        $existingReview = Review::where('appointment_id', $validated['appointment_id'])
            ->where('user_id', Auth::id())
            ->first();

        if ($existingReview) {
            return redirect()->route('site.appointment.show', $validated['appointment_id'])
                ->with('warning', 'Bạn đã đánh giá lịch hẹn này rồi. Mỗi lịch hẹn chỉ có thể đánh giá một lần. Vui lòng sửa đánh giá hiện có nếu muốn thay đổi.');
        }

        // Handle image uploads
        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('reviews', 'public');
                $images[] = basename($path);
            }
        }

        $review = Review::create([
            'appointment_id' => $validated['appointment_id'],
            'service_id' => $validated['service_id'] ?? null,
            'employee_id' => $validated['employee_id'] ?? $appointment->employee_id,
            'user_id' => Auth::id(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'images' => !empty($images) ? $images : null,
            'is_hidden' => false,
        ]);

        return redirect()->route('site.appointment.show', $appointment->id)
            ->with('success', 'Cảm ơn bạn đã đánh giá! Đánh giá của bạn đã được ghi nhận.');
    }

    /**
     * Show the form for editing the specified review.
     */
    public function edit(string $id)
    {
        $review = Review::with(['appointment', 'service', 'employee'])
            ->findOrFail($id);

        // Check if user owns this review
        if (Auth::id() != $review->user_id) {
            return redirect()->back()->with('error', 'Bạn không có quyền chỉnh sửa đánh giá này.');
        }

        $appointment = $review->appointment;
        $services = [];
        
        if ($appointment && $appointment->appointmentDetails) {
            foreach ($appointment->appointmentDetails as $detail) {
                if ($detail->serviceVariant && $detail->serviceVariant->service) {
                    $serviceId = $detail->serviceVariant->service->id;
                    if (!isset($services[$serviceId])) {
                        $services[$serviceId] = $detail->serviceVariant->service;
                    }
                }
            }
        }

        return view('site.reviews.edit', compact('review', 'appointment', 'services'));
    }

    /**
     * Update the specified review in storage.
     */
    public function update(Request $request, string $id)
    {
        $review = Review::findOrFail($id);

        // Check if user owns this review
        if (Auth::id() != $review->user_id) {
            return redirect()->back()->with('error', 'Bạn không có quyền chỉnh sửa đánh giá này.');
        }

        $validated = $request->validate([
            'service_id' => 'nullable|exists:services,id',
            'employee_id' => 'nullable|exists:employees,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:5000',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_images' => 'nullable|array',
        ]);

        // Handle image uploads
        $images = $review->images ?? [];
        
        // Remove images if requested
        if ($request->has('remove_images')) {
            foreach ($request->remove_images as $imageToRemove) {
                if (in_array($imageToRemove, $images)) {
                    Storage::disk('public')->delete('reviews/' . $imageToRemove);
                    $images = array_diff($images, [$imageToRemove]);
                }
            }
            $images = array_values($images);
        }

        // Add new images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('reviews', 'public');
                $images[] = basename($path);
            }
        }

        $review->update([
            'service_id' => $validated['service_id'] ?? $review->service_id,
            'employee_id' => $validated['employee_id'] ?? $review->employee_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'images' => !empty($images) ? $images : null,
        ]);

        return redirect()->route('site.appointment.show', $review->appointment_id)
            ->with('success', 'Đánh giá của bạn đã được cập nhật thành công!');
    }
}

