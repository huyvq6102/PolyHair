@extends('layouts.site')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Chỉnh sửa đánh giá')

@push('styles')
<style>
    .review-page {
        padding: 150px 0 80px;
        background: #f8f9fa;
        min-height: 100vh;
    }
    
    .review-form-container {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        padding: 30px;
        margin-bottom: 30px;
    }
    
    .review-header {
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .review-title {
        font-size: 28px;
        font-weight: 600;
        color: #4A3600;
        margin-bottom: 10px;
    }
    
    .appointment-info {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 25px;
        border-left: 4px solid #BC9321;
    }
    
    .star-rating {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin: 20px 0;
        flex-direction: row-reverse;
    }
    
    .star-rating input {
        display: none;
    }
    
    .star-rating label {
        font-size: 40px;
        color: #ddd;
        cursor: pointer;
        transition: color 0.2s;
    }
    
    .star-rating label:hover,
    .star-rating label:hover ~ label,
    .star-rating input:checked ~ label {
        color: #ffc107;
    }
    
    .image-preview {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 15px;
    }
    
    .image-preview-item {
        position: relative;
        width: 120px;
        height: 120px;
        border-radius: 8px;
        overflow: hidden;
        border: 2px solid #ddd;
    }
    
    .image-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .image-preview-item .remove-image {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(220, 53, 69, 0.9);
        color: white;
        border: none;
        border-radius: 50%;
        width: 25px;
        height: 25px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush

@section('content')
<div class="review-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="review-form-container">
                    <div class="review-header">
                        <h1 class="review-title">
                            <i class="fa fa-edit"></i> Chỉnh sửa đánh giá
                        </h1>
                        <p class="text-muted">Cập nhật đánh giá của bạn</p>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Appointment Info -->
                    @if($appointment)
                    <div class="appointment-info">
                        <h5 class="mb-2"><i class="fa fa-calendar"></i> Thông tin lịch hẹn</h5>
                        <p class="mb-1"><strong>Mã lịch hẹn:</strong> #{{ str_pad($appointment->id, 6, '0', STR_PAD_LEFT) }}</p>
                        <p class="mb-1"><strong>Ngày:</strong> {{ $appointment->start_at ? $appointment->start_at->format('d/m/Y') : 'N/A' }}</p>
                        @if($appointment->employee && $appointment->employee->user)
                            <p class="mb-0"><strong>Nhân viên:</strong> {{ $appointment->employee->user->name }}</p>
                        @endif
                    </div>
                    @endif

                    <form action="{{ route('site.reviews.update', $review->id) }}" method="POST" enctype="multipart/form-data" id="reviewForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- Service Selection -->
                        @if(count($services) > 1)
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fa fa-scissors"></i> Dịch vụ đánh giá <span class="text-danger">*</span>
                            </label>
                            <select name="service_id" class="form-control @error('service_id') is-invalid @enderror" required>
                                <option value="">-- Chọn dịch vụ --</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" {{ old('service_id', $review->service_id) == $service->id ? 'selected' : '' }}>
                                        {{ $service->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('service_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @elseif(count($services) == 1)
                            <input type="hidden" name="service_id" value="{{ $services[0]->id }}">
                        @endif

                        <!-- Employee Selection -->
                        @if($appointment && $appointment->employee_id)
                            <input type="hidden" name="employee_id" value="{{ $appointment->employee_id }}">
                        @endif

                        <!-- Rating -->
                        <div class="mb-4">
                            <label class="form-label fw-bold d-block text-center mb-3">
                                <i class="fa fa-star"></i> Đánh giá của bạn <span class="text-danger">*</span>
                            </label>
                            <div class="star-rating">
                                <input type="radio" id="star5" name="rating" value="5" {{ old('rating', $review->rating) == '5' ? 'checked' : '' }} required>
                                <label for="star5"><i class="fa fa-star"></i></label>
                                <input type="radio" id="star4" name="rating" value="4" {{ old('rating', $review->rating) == '4' ? 'checked' : '' }}>
                                <label for="star4"><i class="fa fa-star"></i></label>
                                <input type="radio" id="star3" name="rating" value="3" {{ old('rating', $review->rating) == '3' ? 'checked' : '' }}>
                                <label for="star3"><i class="fa fa-star"></i></label>
                                <input type="radio" id="star2" name="rating" value="2" {{ old('rating', $review->rating) == '2' ? 'checked' : '' }}>
                                <label for="star2"><i class="fa fa-star"></i></label>
                                <input type="radio" id="star1" name="rating" value="1" {{ old('rating', $review->rating) == '1' ? 'checked' : '' }}>
                                <label for="star1"><i class="fa fa-star"></i></label>
                            </div>
                            @error('rating')
                                <div class="text-danger text-center">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Comment -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fa fa-comment"></i> Bình luận chi tiết <span class="text-danger">*</span>
                            </label>
                            <textarea name="comment" 
                                      rows="6" 
                                      class="form-control @error('comment') is-invalid @enderror" 
                                      placeholder="Chia sẻ trải nghiệm của bạn về dịch vụ..."
                                      required>{{ old('comment', $review->comment) }}</textarea>
                            <small class="form-text text-muted">Tối đa 5000 ký tự</small>
                            @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Existing Images -->
                        @if($review->images && count($review->images) > 0)
                        <div class="mb-4">
                            <label class="form-label fw-bold">Hình ảnh hiện tại</label>
                            <div class="image-preview" id="existingImages">
                                @foreach($review->images as $image)
                                    @php
                                        $imagePath = is_string($image) ? $image : ($image['path'] ?? $image);
                                        $imageUrl = Storage::disk('public')->exists('reviews/' . $imagePath) 
                                            ? Storage::disk('public')->url('reviews/' . $imagePath)
                                            : asset('storage/reviews/' . $imagePath);
                                    @endphp
                                    <div class="image-preview-item">
                                        <img src="{{ $imageUrl }}" alt="Review Image" onerror="this.src='{{ asset('legacy/images/default-image.jpg') }}'; this.onerror=null;">
                                        <button type="button" class="remove-image" onclick="removeExistingImage('{{ $imagePath }}', this)">
                                            <i class="fa fa-times"></i>
                                        </button>
                                        <input type="hidden" name="remove_images[]" value="" id="remove_{{ $imagePath }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Image Upload -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fa fa-image"></i> Thêm hình ảnh mới (tùy chọn)
                            </label>
                            <input type="file" 
                                   name="images[]" 
                                   id="images" 
                                   class="form-control @error('images.*') is-invalid @enderror" 
                                   accept="image/jpeg,image/png,image/jpg,image/gif"
                                   multiple>
                            <small class="form-text text-muted">Có thể upload nhiều hình ảnh (JPG, PNG, GIF - tối đa 2MB mỗi ảnh)</small>
                            @error('images.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <div class="image-preview" id="imagePreview"></div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fa fa-save"></i> Cập nhật đánh giá
                            </button>
                            <a href="{{ route('site.appointment.show', $review->appointment_id) }}" class="btn btn-secondary btn-lg px-5 ml-2">
                                <i class="fa fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function removeExistingImage(imageName, button) {
        if (confirm('Bạn có chắc muốn xóa hình ảnh này?')) {
            const hiddenInput = document.getElementById('remove_' + imageName);
            if (hiddenInput) {
                hiddenInput.value = imageName;
            }
            button.closest('.image-preview-item').style.display = 'none';
        }
    }

    let newSelectedFiles = [];
    
    // Image preview for new images
    document.getElementById('images').addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        
        // Add new files to newSelectedFiles array
        files.forEach(file => {
            if (file.type.startsWith('image/')) {
                // Check if file already exists
                const exists = newSelectedFiles.some(f => f.name === file.name && f.size === file.size);
                if (!exists) {
                    newSelectedFiles.push(file);
                }
            }
        });
        
        // Update preview for new images
        updateNewImagePreview();
        
        // Update input files
        updateNewInputFiles();
    });

    function updateNewImagePreview() {
        const preview = document.getElementById('imagePreview');
        if (!preview) return;
        
        // Clear only new image previews (not existing ones)
        const existingPreviews = preview.querySelectorAll('.image-preview-item[data-new="true"]');
        existingPreviews.forEach(el => el.remove());
        
        newSelectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'image-preview-item';
                div.setAttribute('data-index', index);
                div.setAttribute('data-new', 'true');
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="remove-image" onclick="removeNewImage(${index})">
                        <i class="fa fa-times"></i>
                    </button>
                `;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }

    function removeNewImage(index) {
        newSelectedFiles.splice(index, 1);
        updateNewImagePreview();
        updateNewInputFiles();
    }

    function updateNewInputFiles() {
        const input = document.getElementById('images');
        const dt = new DataTransfer();
        newSelectedFiles.forEach(file => {
            dt.items.add(file);
        });
        input.files = dt.files;
    }

    // Form validation
    document.getElementById('reviewForm').addEventListener('submit', function(e) {
        const rating = document.querySelector('input[name="rating"]:checked');
        if (!rating) {
            e.preventDefault();
            alert('Vui lòng chọn số sao đánh giá!');
            return false;
        }
    });
</script>
@endpush

