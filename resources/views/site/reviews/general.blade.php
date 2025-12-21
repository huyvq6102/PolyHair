@extends('layouts.site')

@section('title', 'Gửi cảm nhận chung')

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
        color: #d8b26a;
        margin-bottom: 10px;
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
                            <i class="fa fa-comment"></i> Gửi bình luận
                        </h1>
                        <p class="text-muted">
                            Dù bạn chưa từng sử dụng dịch vụ, bạn vẫn có thể chia sẻ cảm nhận, góp ý hoặc kỳ vọng của mình để PolyHair cải thiện tốt hơn.
                        </p>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('site.reviews.general.store') }}" method="POST" enctype="multipart/form-data" id="generalReviewForm">
                        @csrf

                        <!-- Service Selection (optional) -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fa fa-scissors"></i> Bạn muốn góp ý cho dịch vụ nào? <span class="text-muted">(không bắt buộc)</span>
                            </label>
                            <select name="service_id" class="form-control @error('service_id') is-invalid @enderror">
                                <option value="">-- Chọn (nếu có) --</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                        {{ $service->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('service_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Comment -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fa fa-comment"></i> Nội dung bình luận <span class="text-danger">*</span>
                            </label>
                            <textarea name="comment" 
                                      rows="6" 
                                      class="form-control @error('comment') is-invalid @enderror" 
                                      placeholder="Ví dụ: Kỳ vọng của bạn về không gian, dịch vụ, phong cách phục vụ, giá cả..."
                                      required>{{ old('comment') }}</textarea>
                            <small class="form-text text-muted">Tối đa 5000 ký tự</small>
                            @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Image Upload -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fa fa-image"></i> Hình ảnh minh họa (tùy chọn)
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
                                <i class="fa fa-paper-plane"></i> Gửi bình luận
                            </button>
                            <a href="{{ route('site.reviews.index') }}" class="btn btn-secondary btn-lg px-5 ms-2">
                                <i class="fa fa-arrow-left"></i> Xem danh sách đánh giá
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
    let selectedFiles = [];
    
    // Image preview
    document.getElementById('images').addEventListener('change', function(e) {
        const preview = document.getElementById('imagePreview');
        const files = Array.from(e.target.files);
        
        // Add new files to selectedFiles array
        files.forEach(file => {
            if (file.type.startsWith('image/')) {
                // Check if file already exists
                const exists = selectedFiles.some(f => f.name === file.name && f.size === file.size);
                if (!exists) {
                    selectedFiles.push(file);
                }
            }
        });
        
        // Update preview
        updatePreview();
        
        // Update input files
        updateInputFiles();
    });

    function updatePreview() {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';
        
        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'image-preview-item';
                div.setAttribute('data-index', index);
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="remove-image" onclick="removeImage(${index})">
                        <i class="fa fa-times"></i>
                    </button>
                `;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }

    function removeImage(index) {
        selectedFiles.splice(index, 1);
        updatePreview();
        updateInputFiles();
    }

    function updateInputFiles() {
        const input = document.getElementById('images');
        const dt = new DataTransfer();
        selectedFiles.forEach(file => {
            dt.items.add(file);
        });
        input.files = dt.files;
    }

    // Form validation - rating is optional for comments
    document.getElementById('generalReviewForm').addEventListener('submit', function(e) {
        // Rating is optional, so no validation needed
        // Just ensure comment is filled (handled by HTML5 required attribute)
    });
</script>
@endpush


