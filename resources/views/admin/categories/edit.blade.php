@extends('admin.layouts.app')

@section('title', 'Sửa danh mục')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Sửa danh mục sản phẩm</h1>
    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Thông tin danh mục</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="name">Tên danh mục <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" class="form-control @error('name') is-invalid @enderror" placeholder="Nhập tên danh mục" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Vui lòng nhập tên danh mục sản phẩm</div>
                @enderror
            </div>

            @if($category->images)
                <div class="form-group">
                    <label>Ảnh hiện tại</label>
                    <div>
                        <img src="{{ asset('legacy/images/categories/' . $category->images) }}" alt="{{ $category->name }}" width="120" class="img-thumbnail">
                    </div>
                </div>
            @endif

            <div class="form-group">
                <label for="images">Ảnh danh mục mới</label>
                <input type="file" class="form-control-file border @error('images') is-invalid @enderror" id="images" name="images" accept="image/jpeg,image/png,image/gif,image/tiff">
                <small class="form-text text-muted">Để trống nếu không muốn thay đổi ảnh. Chấp nhận: JPG, PNG, GIF, TIFF (tối đa 5MB)</small>
                @error('images')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Cập nhật
                </button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Hủy</a>
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
