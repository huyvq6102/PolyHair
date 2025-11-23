@extends('admin.layouts.app')

@section('title', 'Thêm dịch vụ')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Thêm dịch vụ mới</h1>
    <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<!-- Service Type Selection -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Chọn loại dịch vụ</h6>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label for="service_type">Loại dịch vụ <span class="text-danger">*</span></label>
            <select name="service_type" id="service_type" class="form-control" required>
                <option value="">-- Chọn loại dịch vụ --</option>
                <option value="single" {{ old('service_type') == 'single' ? 'selected' : '' }}>Dịch vụ đơn</option>
                <option value="variant" {{ old('service_type') == 'variant' ? 'selected' : '' }}>Dịch vụ biến thể</option>
                <option value="combo" {{ old('service_type') == 'combo' ? 'selected' : '' }}>Combo</option>
            </select>
        </div>
    </div>
</div>

<!-- Form for Single Service -->
<div id="single-form" class="card shadow mb-4" style="display: none;">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Thêm dịch vụ đơn</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.services.store') }}" method="POST" enctype="multipart/form-data" id="singleForm">
            @csrf
            <input type="hidden" name="service_type" value="single">
            <div class="form-group">
                <label for="service_code">Mã dịch vụ</label>
                <input type="text" name="service_code" id="service_code" class="form-control @error('service_code') is-invalid @enderror" 
                       value="{{ old('service_code') }}" placeholder="Để trống sẽ tự động tạo">
                @error('service_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="name">Tên dịch vụ <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                       value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="category_id">Nhóm dịch vụ <span class="text-danger">*</span></label>
                    <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                        <option value="">-- Chọn nhóm dịch vụ --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group col-md-6">
                    <label for="base_price">Giá <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="base_price" id="base_price" class="form-control @error('base_price') is-invalid @enderror" 
                           value="{{ old('base_price') }}" required>
                    @error('base_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="status">Trạng thái</label>
                    <select name="status" id="status" class="form-control">
                        <option value="Hoạt động" {{ old('status', 'Hoạt động') == 'Hoạt động' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="Vô hiệu hóa" {{ old('status') == 'Vô hiệu hóa' ? 'selected' : '' }}>Vô hiệu hóa</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="image">Hình ảnh</label>
                    <input type="file" name="image" id="image" class="form-control-file @error('image') is-invalid @enderror" accept="image/*">
                    @error('image')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="description">Mô tả</label>
                <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu dịch vụ đơn
                </button>
                <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>

<!-- Form for Variant Service -->
<div id="variant-form" class="card shadow mb-4" style="display: none;">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Thêm biến thể dịch vụ</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.services.store') }}" method="POST" id="variantForm">
            @csrf
            <input type="hidden" name="service_type" value="variant">
            <div class="form-group">
                <label for="service_id">Dịch vụ đơn <span class="text-danger">*</span></label>
                <select name="service_id" id="service_id" class="form-control @error('service_id') is-invalid @enderror" required>
                    <option value="">-- Chọn dịch vụ đơn --</option>
                    @foreach($singleServices as $singleService)
                        <option value="{{ $singleService->id }}" {{ old('service_id') == $singleService->id ? 'selected' : '' }}>
                            {{ $singleService->name }} ({{ $singleService->service_code ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
                @error('service_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="variant_name">Tên biến thể <span class="text-danger">*</span></label>
                    <input type="text" name="variant_name" id="variant_name" class="form-control @error('variant_name') is-invalid @enderror" 
                           value="{{ old('variant_name') }}" required>
                    @error('variant_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group col-md-4">
                    <label for="variant_price">Giá <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="variant_price" id="variant_price" class="form-control @error('variant_price') is-invalid @enderror" 
                           value="{{ old('variant_price') }}" required>
                    @error('variant_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group col-md-4">
                    <label for="variant_duration">Thời lượng (phút) <span class="text-danger">*</span></label>
                    <input type="number" name="variant_duration" id="variant_duration" class="form-control @error('variant_duration') is-invalid @enderror" 
                           value="{{ old('variant_duration') }}" required>
                    @error('variant_duration')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" name="is_default" id="is_default" class="form-check-input" value="1" {{ old('is_default') ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_default">Mặc định</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Hoạt động</label>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu biến thể
                </button>
                <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>

<!-- Form for Combo Service -->
<div id="combo-form" class="card shadow mb-4" style="display: none;">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Thêm combo dịch vụ</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.services.store') }}" method="POST" enctype="multipart/form-data" id="comboForm">
            @csrf
            <input type="hidden" name="service_type" value="combo">
            <div class="form-group">
                <label for="combo_name">Tên combo <span class="text-danger">*</span></label>
                <input type="text" name="combo_name" id="combo_name" class="form-control @error('combo_name') is-invalid @enderror" 
                       value="{{ old('combo_name') }}" required>
                @error('combo_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="combo_category_id">Nhóm dịch vụ <span class="text-danger">*</span></label>
                    <select name="category_id" id="combo_category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                        <option value="">-- Chọn nhóm dịch vụ --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group col-md-6">
                    <label for="combo_price">Giá combo <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="combo_price" id="combo_price" class="form-control @error('combo_price') is-invalid @enderror" 
                           value="{{ old('combo_price') }}" required>
                    @error('combo_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="combo_status">Trạng thái</label>
                    <select name="combo_status" id="combo_status" class="form-control">
                        <option value="Hoạt động" {{ old('combo_status', 'Hoạt động') == 'Hoạt động' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="Vô hiệu hóa" {{ old('combo_status') == 'Vô hiệu hóa' ? 'selected' : '' }}>Vô hiệu hóa</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="combo_image">Hình ảnh</label>
                    <input type="file" name="combo_image" id="combo_image" class="form-control-file @error('combo_image') is-invalid @enderror" accept="image/*">
                    @error('combo_image')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="combo_description">Mô tả</label>
                <textarea name="combo_description" id="combo_description" rows="4" class="form-control @error('combo_description') is-invalid @enderror">{{ old('combo_description') }}</textarea>
                @error('combo_description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label>Chọn dịch vụ đơn <span class="text-danger">*</span></label>
                <div class="border rounded p-2" style="max-height: 250px; overflow-y: auto; background-color: #fff; border-color: #ced4da !important;">
                    @forelse($singleServices as $singleService)
                        <div class="form-check">
                            <input type="checkbox" name="service_ids[]" id="service_{{ $singleService->id }}" 
                                   class="form-check-input" value="{{ $singleService->id }}" 
                                   {{ in_array($singleService->id, old('service_ids', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="service_{{ $singleService->id }}" style="cursor: pointer; width: 100%;">
                                {{ $singleService->name }} ({{ $singleService->service_code ?? 'N/A' }}) - {{ number_format($singleService->base_price ?? 0, 0, ',', '.') }} đ
                            </label>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Chưa có dịch vụ đơn nào. Vui lòng thêm dịch vụ đơn trước.</p>
                    @endforelse
                </div>
                @error('service_ids')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu combo
                </button>
                <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const serviceTypeSelect = document.getElementById('service_type');
        const singleForm = document.getElementById('single-form');
        const variantForm = document.getElementById('variant-form');
        const comboForm = document.getElementById('combo-form');

        function showForm(type) {
            singleForm.style.display = 'none';
            variantForm.style.display = 'none';
            comboForm.style.display = 'none';

            if (type === 'single') {
                singleForm.style.display = 'block';
            } else if (type === 'variant') {
                variantForm.style.display = 'block';
            } else if (type === 'combo') {
                comboForm.style.display = 'block';
            }
        }

        serviceTypeSelect.addEventListener('change', function() {
            showForm(this.value);
        });

        // Show form based on old input or selected type
        const selectedType = serviceTypeSelect.value;
        if (selectedType) {
            showForm(selectedType);
        }
    });
</script>
@endpush
