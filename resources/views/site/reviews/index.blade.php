@extends('layouts.site')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Đánh giá khách hàng')

@push('styles')
<style>
    .reviews-page {
        padding: 150px 0 80px;
        background: #f8f9fa;
        min-height: 100vh;
    }
    
    .reviews-header {
        text-align: center;
        margin-bottom: 40px;
    }
    
    .reviews-title {
        font-size: 32px;
        font-weight: 600;
        color: #4A3600;
        margin-bottom: 10px;
    }
    
    .review-card {
        background: #fff;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .review-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .review-header-info {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .reviewer-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .reviewer-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #BC9321 0%, #4A3600 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 20px;
    }
    
    .reviewer-details h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #333;
    }
    
    .reviewer-details p {
        margin: 0;
        font-size: 13px;
        color: #666;
    }
    
    .review-date {
        font-size: 13px;
        color: #999;
        text-align: right;
    }
    
    .review-rating {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 15px;
    }
    
    .review-rating .stars {
        color: #ffc107;
        font-size: 18px;
    }
    
    .review-rating .rating-text {
        font-size: 14px;
        color: #666;
        margin-left: 5px;
    }
    
    .review-comment {
        color: #555;
        line-height: 1.8;
        margin-bottom: 15px;
    }
    
    .review-images {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 15px;
    }
    
    .review-image {
        width: 100px;
        height: 100px;
        border-radius: 8px;
        object-fit: cover;
        cursor: pointer;
        border: 2px solid #ddd;
        transition: transform 0.3s;
    }
    
    .review-image:hover {
        transform: scale(1.1);
    }
    
    .review-service {
        display: inline-block;
        background: #f0f0f0;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        color: #666;
        margin-top: 10px;
    }
    
    .filter-section {
        background: #fff;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('content')
<div class="reviews-page">
    <div class="container">
        <div class="reviews-header">
            <h1 class="reviews-title">
                <i class="fa fa-star"></i> Đánh giá khách hàng
            </h1>
            <p class="text-muted">Xem những đánh giá và cảm nhận từ khách hàng về PolyHair</p>

            @auth
                <div class="mt-3">
                    <a href="{{ route('site.reviews.general.create') }}" class="btn btn-outline-primary">
                        <i class="fa fa-comment"></i> Gửi cảm nhận chung (chưa từng sử dụng dịch vụ vẫn bình luận được)
                    </a>
                </div>
            @endauth
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('site.reviews.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Dịch vụ</label>
                    <select name="service_id" class="form-control">
                        <option value="">Tất cả dịch vụ</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                {{ $service->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Số sao</label>
                    <select name="rating" class="form-control">
                        <option value="">Tất cả</option>
                        <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 sao</option>
                        <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 sao</option>
                        <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 sao</option>
                        <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 sao</option>
                        <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 sao</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa fa-search"></i> Tìm kiếm
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Reviews List -->
        @forelse($reviews as $review)
            <div class="review-card">
                <div class="review-header-info">
                    <div class="reviewer-info">
                        <div class="reviewer-avatar">
                            {{ strtoupper(substr($review->user->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="reviewer-details">
                            <h5>{{ $review->user->name ?? 'Khách hàng' }}</h5>
                            <p>
                                @if($review->service)
                                    <i class="fa fa-scissors"></i> {{ $review->service->name }}
                                @elseif($review->appointment && $review->appointment->appointmentDetails->count() > 0)
                                    @php
                                        $serviceNames = [];
                                        foreach($review->appointment->appointmentDetails as $detail) {
                                            if($detail->serviceVariant && $detail->serviceVariant->service) {
                                                $serviceNames[] = $detail->serviceVariant->service->name;
                                            } elseif($detail->combo) {
                                                $serviceNames[] = $detail->combo->name;
                                            } elseif($detail->notes) {
                                                $serviceNames[] = $detail->notes;
                                            }
                                        }
                                    @endphp
                                    <i class="fa fa-scissors"></i> {{ !empty($serviceNames) ? implode(', ', array_unique($serviceNames)) : 'Dịch vụ' }}
                                @else
                                    <i class="fa fa-scissors"></i> Dịch vụ
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="review-date">
                        <i class="fa fa-calendar"></i> {{ $review->created_at->format('d/m/Y') }}<br>
                        <i class="fa fa-clock-o"></i> {{ $review->created_at->format('H:i') }}
                    </div>
                </div>

                <div class="review-rating">
                    <div class="stars">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= ($review->rating ?? 0))
                                <i class="fa fa-star"></i>
                            @else
                                <i class="fa fa-star-o"></i>
                            @endif
                        @endfor
                    </div>
                    <span class="rating-text">({{ $review->rating ?? 0 }}/5)</span>
                </div>

                <div class="review-comment">
                    {{ $review->comment ?? 'Không có bình luận' }}
                </div>

                @php
                    // Get images from review
                    $reviewImages = $review->images;
                    
                    // Handle different data formats
                    if (is_string($reviewImages)) {
                        $decoded = json_decode($reviewImages, true);
                        $reviewImages = is_array($decoded) ? $decoded : [];
                    }
                    
                    // Ensure it's an array
                    if (!is_array($reviewImages)) {
                        $reviewImages = [];
                    }
                    
                    // Filter out empty values
                    $reviewImages = array_filter($reviewImages, function($img) {
                        return !empty($img);
                    });
                @endphp
                
                @if(count($reviewImages) > 0)
                    <div class="review-images">
                        @foreach($reviewImages as $image)
                            @php
                                // Get image filename - handle both string and array
                                $imageName = '';
                                if (is_string($image)) {
                                    $imageName = $image;
                                } elseif (is_array($image)) {
                                    $imageName = $image['name'] ?? $image['path'] ?? $image[0] ?? '';
                                }
                                
                                // Skip if empty
                                if (empty($imageName)) {
                                    continue;
                                }
                                
                                // Build image URL - use asset for public storage
                                $imageUrl = asset('storage/reviews/' . $imageName);
                            @endphp
                            <img src="{{ $imageUrl }}" 
                                 alt="Review Image" 
                                 class="review-image"
                                 onerror="console.error('Image error: {{ $imageName }}'); this.style.display='none';"
                                 onclick="openImageModal('{{ $imageUrl }}')">
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-5">
                <i class="fa fa-star-o" style="font-size: 64px; color: #ddd;"></i>
                <p class="mt-3 text-muted">Chưa có đánh giá nào</p>
            </div>
        @endforelse

        <!-- Pagination -->
        @if($reviews->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $reviews->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="Review Image" style="max-width: 100%; max-height: 70vh;">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openImageModal(imageSrc) {
        document.getElementById('modalImage').src = imageSrc;
        const modal = new bootstrap.Modal(document.getElementById('imageModal'));
        modal.show();
    }
</script>
@endpush

