@extends('admin.layouts.app')

@section('title', 'Sửa dịch vụ')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Sửa dịch vụ</h1>
    <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Thông tin dịch vụ</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.services.update', $service->id) }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="name">Tên dịch vụ <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $service->name) }}" class="form-control @error('name') is-invalid @enderror" placeholder="Nhập tên dịch vụ" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Vui lòng nhập tên dịch vụ</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="category_id">Danh mục <span class="text-danger">*</span></label>
                <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                    <option value="">-- Chọn danh mục --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $service->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Vui lòng chọn danh mục</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Mô tả</label>
                <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror" placeholder="Nhập mô tả dịch vụ">{{ old('description', $service->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="image">Hình ảnh</label>
                @if($service->image)
                    <div class="mb-2">
                        <img src="{{ asset('legacy/images/products/' . $service->image) }}" alt="{{ $service->name }}" width="100" height="100" class="img-thumbnail">
                    </div>
                @endif
                <input type="file" class="form-control-file border @error('image') is-invalid @enderror" id="image" name="image" accept="image/jpeg,image/png,image/jpg,image/gif">
                <small class="form-text text-muted">Chấp nhận: JPG, PNG, GIF (tối đa 2MB). Để trống nếu không muốn thay đổi.</small>
                @error('image')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="status">Trạng thái</label>
                <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                    <option value="Hoạt động" {{ old('status', $service->status) == 'Hoạt động' ? 'selected' : '' }}>Hoạt động</option>
                    <option value="Vô hiệu hóa" {{ old('status', $service->status) == 'Vô hiệu hóa' ? 'selected' : '' }}>Vô hiệu hóa</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Cập nhật
                </button>
                <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">Hủy</a>
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

