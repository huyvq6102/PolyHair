@extends('admin.layouts.app')

@section('title', 'Sửa bình luận')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Sửa bình luận #{{ $review->id }}</h1>
    <a href="{{ route('admin.reviews.show', $review->id) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<!-- Edit Form -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Thông tin bình luận</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.reviews.update', $review->id) }}" method="POST" class="needs-validation" novalidate>
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="comment">Nội dung bình luận <span class="text-danger">*</span></label>
                <textarea name="comment" id="comment" rows="6" 
                          class="form-control @error('comment') is-invalid @enderror" 
                          placeholder="Nhập nội dung bình luận" 
                          required>{{ old('comment', $review->comment) }}</textarea>
                @error('comment')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Vui lòng nhập nội dung bình luận</div>
                @enderror
                <small class="form-text text-muted">Tối đa 5000 ký tự</small>
            </div>

            <div class="form-group">
                <label>Thông tin khác:</label>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Người đánh giá:</strong> {{ $review->user->name ?? 'N/A' }}</p>
                        <p><strong>Dịch vụ:</strong> {{ $review->service->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Đánh giá:</strong> 
                            @if($review->rating)
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $review->rating)
                                        <i class="fas fa-star text-warning"></i>
                                    @else
                                        <i class="far fa-star text-secondary"></i>
                                    @endif
                                @endfor
                                ({{ $review->rating }}/5)
                            @else
                                Chưa đánh giá
                            @endif
                        </p>
                        <p><strong>Ngày tạo:</strong> 
                            {{ $review->created_at ? $review->created_at->format('d/m/Y H:i:s') : 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                <strong>Lưu ý:</strong> Sau khi cập nhật, ngày giờ sẽ được cập nhật tự động.
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary" onclick="return confirm('Bạn có chắc muốn cập nhật nội dung bình luận này không?');">
                    <i class="fas fa-save"></i> Xác nhận cập nhật
                </button>
                <a href="{{ route('admin.reviews.show', $review->id) }}" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
</script>
@endpush

