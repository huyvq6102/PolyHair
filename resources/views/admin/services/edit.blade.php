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

@php
    // Ưu tiên serviceType từ controller, sau đó mới đến session
    if (isset($combo)) {
        $serviceType = 'combo';
    } elseif (isset($variant)) {
        $serviceType = 'variant';
    } elseif (isset($service)) {
        // Nếu có serviceType từ controller thì dùng, không thì kiểm tra service có variants không
        if (isset($serviceType) && $serviceType == 'variant') {
            // Giữ nguyên variant
        } elseif ($service->serviceVariants->count() > 0) {
            $serviceType = 'variant';
        } else {
            $serviceType = $serviceType ?? 'single';
        }
    } else {
        $serviceType = session('service_type', 'single');
    }
@endphp

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
                <option value="single" {{ $serviceType == 'single' ? 'selected' : '' }}>Dịch vụ đơn</option>
                <option value="variant" {{ $serviceType == 'variant' ? 'selected' : '' }}>Dịch vụ biến thể</option>
                <option value="combo" {{ $serviceType == 'combo' ? 'selected' : '' }}>Combo</option>
            </select>
        </div>
    </div>
</div>

<!-- Form for Single Service -->
@if(isset($service) && $serviceType == 'single')
<div id="single-form" class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Sửa dịch vụ đơn</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.services.update', $service->id) }}" method="POST" enctype="multipart/form-data" id="singleForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="service_type" value="single">
            <div class="form-group">
                <label for="service_code">Mã dịch vụ</label>
                <input type="text" name="service_code" id="service_code" class="form-control @error('service_code') is-invalid @enderror" 
                       value="{{ old('service_code', $service->service_code) }}">
                @error('service_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="name">Tên dịch vụ <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                       value="{{ old('name', $service->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="category_id">Nhóm dịch vụ <span class="text-danger">*</span></label>
                    <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                        <option value="">-- Chọn nhóm dịch vụ --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $service->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
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
                           value="{{ old('base_price', $service->base_price) }}" required>
                    @error('base_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="status">Trạng thái</label>
                    <select name="status" id="status" class="form-control">
                        <option value="Hoạt động" {{ old('status', $service->status) == 'Hoạt động' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="Vô hiệu hóa" {{ old('status', $service->status) == 'Vô hiệu hóa' ? 'selected' : '' }}>Vô hiệu hóa</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="image">Hình ảnh</label>
                    @if($service->image)
                        <div class="mb-2">
                            <img src="{{ asset('legacy/images/products/' . $service->image) }}" alt="{{ $service->name }}" width="100" height="100" class="img-thumbnail">
                        </div>
                    @endif
                    <input type="file" name="image" id="image" class="form-control-file @error('image') is-invalid @enderror" accept="image/*">
                    @error('image')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="description">Mô tả</label>
                <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $service->description) }}</textarea>
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
@endif

<!-- Form for Variant Service (Service with variants) -->
@if(isset($service) && $serviceType == 'variant')
<div id="variant-service-form" class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Sửa dịch vụ biến thể</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.services.update', $service->id) }}" method="POST" enctype="multipart/form-data" id="variantServiceForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="service_type" value="variant">
            
            <!-- Thông tin dịch vụ chính -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="m-0 font-weight-bold">Thông tin dịch vụ</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="variant_service_code">Mã dịch vụ</label>
                        <input type="text" name="service_code" id="variant_service_code" class="form-control @error('service_code') is-invalid @enderror" 
                               value="{{ old('service_code', $service->service_code) }}">
                        @error('service_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="variant_service_name">Tên dịch vụ biến thể <span class="text-danger">*</span></label>
                        <input type="text" name="service_name" id="variant_service_name" class="form-control @error('service_name') is-invalid @enderror" 
                               value="{{ old('service_name', $service->name) }}" required>
                        @error('service_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="variant_category_id">Nhóm dịch vụ <span class="text-danger">*</span></label>
                            <select name="category_id" id="variant_category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                                <option value="">-- Chọn nhóm dịch vụ --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $service->category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="variant_image">Hình ảnh</label>
                            @if($service->image)
                                <div class="mb-2">
                                    <img src="{{ asset('legacy/images/products/' . $service->image) }}" alt="{{ $service->name }}" width="100" height="100" class="img-thumbnail">
                                </div>
                            @endif
                            <input type="file" name="image" id="variant_image" class="form-control-file @error('image') is-invalid @enderror" accept="image/*">
                            @error('image')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="variant_description">Mô tả</label>
                        <textarea name="description" id="variant_description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $service->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Danh sách biến thể -->
            <div class="card mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">Biến thể dịch vụ</h6>
                    <button type="button" class="btn btn-sm btn-primary" id="addVariantBtn">
                        <i class="fas fa-plus"></i> Thêm biến thể
                    </button>
                </div>
                <div class="card-body">
                    <div id="variantsContainer">
                        @foreach($service->serviceVariants as $index => $variant)
                            <div class="variant-item border rounded p-3 mb-3" data-variant-index="{{ $index }}" data-variant-id="{{ $variant->id }}">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="m-0 font-weight-bold">Biến thể <span class="variant-number">{{ $index + 1 }}</span></h6>
                                    <button type="button" class="btn btn-sm btn-danger remove-variant-btn" {{ $service->serviceVariants->count() === 1 ? 'disabled' : '' }}>
                                        <i class="fas fa-times"></i> Xóa
                                    </button>
                                </div>
                                <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant->id }}">
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Tên biến thể <span class="text-danger">*</span></label>
                                        <input type="text" name="variants[{{ $index }}][name]" class="form-control" value="{{ old("variants.$index.name", $variant->name) }}" required>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Giá (VNĐ) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" name="variants[{{ $index }}][price]" class="form-control" value="{{ old("variants.$index.price", $variant->price) }}" required>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Thời lượng (phút) <span class="text-danger">*</span></label>
                                        <input type="number" name="variants[{{ $index }}][duration]" class="form-control" value="{{ old("variants.$index.duration", $variant->duration) }}" required>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label>Kích hoạt</label>
                                        <div class="custom-control custom-switch mt-2">
                                            <input type="hidden" name="variants[{{ $index }}][is_active]" value="0">
                                            <input type="checkbox" class="custom-control-input" name="variants[{{ $index }}][is_active]" value="1" id="variant_active_{{ $index }}" {{ old("variants.$index.is_active", $variant->is_active) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="variant_active_{{ $index }}">Kích hoạt</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Ghi chú</label>
                                    <textarea name="variants[{{ $index }}][notes]" class="form-control" rows="2">{{ old("variants.$index.notes", $variant->notes) }}</textarea>
                                </div>
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="mb-0">Thuộc tính biến thể</label>
                                        <button type="button" class="btn btn-sm btn-outline-primary add-attribute-btn" data-variant-index="{{ $index }}">
                                            <i class="fas fa-plus"></i> Thêm thuộc tính
                                        </button>
                                    </div>
                                    <div class="attributes-container" data-variant-index="{{ $index }}">
                                        @foreach($variant->variantAttributes as $attrIndex => $attribute)
                                            <div class="attribute-item border rounded p-2 mb-2" data-attribute-index="{{ $attrIndex }}">
                                                <div class="form-row align-items-end">
                                                    <div class="form-group col-md-5 mb-0">
                                                        <label>Tên thuộc tính <span class="text-danger">*</span></label>
                                                        <input type="text" name="variants[{{ $index }}][attributes][{{ $attrIndex }}][name]" class="form-control" value="{{ old("variants.$index.attributes.$attrIndex.name", $attribute->attribute_name) }}" required>
                                                    </div>
                                                    <div class="form-group col-md-5 mb-0">
                                                        <label>Giá trị <span class="text-danger">*</span></label>
                                                        <input type="text" name="variants[{{ $index }}][attributes][{{ $attrIndex }}][value]" class="form-control" value="{{ old("variants.$index.attributes.$attrIndex.value", $attribute->attribute_value) }}" required>
                                                    </div>
                                                    <div class="form-group col-md-2 mb-0">
                                                        <button type="button" class="btn btn-sm btn-danger remove-attribute-btn w-100">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu dịch vụ biến thể
                </button>
                <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>

<!-- Template cho biến thể mới -->
<template id="variantTemplate">
    <div class="variant-item border rounded p-3 mb-3" data-variant-index="__INDEX__">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="m-0 font-weight-bold">Biến thể <span class="variant-number"></span></h6>
            <button type="button" class="btn btn-sm btn-danger remove-variant-btn">
                <i class="fas fa-times"></i> Xóa
            </button>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Tên biến thể <span class="text-danger">*</span></label>
                <input type="text" name="variants[__INDEX__][name]" class="form-control" required>
            </div>
            <div class="form-group col-md-3">
                <label>Giá (VNĐ) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" name="variants[__INDEX__][price]" class="form-control" required>
            </div>
            <div class="form-group col-md-3">
                <label>Thời lượng (phút) <span class="text-danger">*</span></label>
                <input type="number" name="variants[__INDEX__][duration]" class="form-control" required>
            </div>
            <div class="form-group col-md-2">
                <label>Kích hoạt</label>
                <div class="custom-control custom-switch mt-2">
                    <input type="hidden" name="variants[__INDEX__][is_active]" value="0">
                    <input type="checkbox" class="custom-control-input" name="variants[__INDEX__][is_active]" value="1" id="variant_active___INDEX__" checked>
                    <label class="custom-control-label" for="variant_active___INDEX__">Kích hoạt</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>Ghi chú</label>
            <textarea name="variants[__INDEX__][notes]" class="form-control" rows="2"></textarea>
        </div>
        <div class="form-group">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="mb-0">Thuộc tính biến thể</label>
                <button type="button" class="btn btn-sm btn-outline-primary add-attribute-btn" data-variant-index="__INDEX__">
                    <i class="fas fa-plus"></i> Thêm thuộc tính
                </button>
            </div>
            <div class="attributes-container" data-variant-index="__INDEX__">
                <!-- Thuộc tính sẽ được thêm vào đây -->
            </div>
        </div>
    </div>
</template>

<!-- Template cho thuộc tính -->
<template id="attributeTemplate">
    <div class="attribute-item border rounded p-2 mb-2" data-attribute-index="__ATTR_INDEX__">
        <div class="form-row align-items-end">
            <div class="form-group col-md-5 mb-0">
                <label>Tên thuộc tính <span class="text-danger">*</span></label>
                <input type="text" name="variants[__VARIANT_INDEX__][attributes][__ATTR_INDEX__][name]" class="form-control" required>
            </div>
            <div class="form-group col-md-5 mb-0">
                <label>Giá trị <span class="text-danger">*</span></label>
                <input type="text" name="variants[__VARIANT_INDEX__][attributes][__ATTR_INDEX__][value]" class="form-control" required>
            </div>
            <div class="form-group col-md-2 mb-0">
                <button type="button" class="btn btn-sm btn-danger remove-attribute-btn w-100">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</template>
@endif

<!-- Form for Variant Service (Single variant edit) -->
@if(isset($variant))
<div id="variant-form" class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Sửa biến thể dịch vụ</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.services.update', $variant->id) }}" method="POST" id="variantForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="service_type" value="variant">
            <div class="form-group">
                <label for="service_id">Dịch vụ đơn <span class="text-danger">*</span></label>
                <select name="service_id" id="service_id" class="form-control @error('service_id') is-invalid @enderror" required disabled>
                    <option value="{{ $variant->service_id }}">{{ $variant->service->name }} ({{ $variant->service->service_code ?? 'N/A' }})</option>
                </select>
                <input type="hidden" name="service_id" value="{{ $variant->service_id }}">
                @error('service_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="variant_name">Tên biến thể <span class="text-danger">*</span></label>
                    <input type="text" name="variant_name" id="variant_name" class="form-control @error('variant_name') is-invalid @enderror" 
                           value="{{ old('variant_name', $variant->name) }}" required>
                    @error('variant_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group col-md-4">
                    <label for="variant_price">Giá <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="variant_price" id="variant_price" class="form-control @error('variant_price') is-invalid @enderror" 
                           value="{{ old('variant_price', $variant->price) }}" required>
                    @error('variant_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group col-md-4">
                    <label for="variant_duration">Thời lượng (phút) <span class="text-danger">*</span></label>
                    <input type="number" name="variant_duration" id="variant_duration" class="form-control @error('variant_duration') is-invalid @enderror" 
                           value="{{ old('variant_duration', $variant->duration) }}" required>
                    @error('variant_duration')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" name="is_default" id="is_default" class="form-check-input" value="1" {{ old('is_default', $variant->is_default) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_default">Mặc định</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', $variant->is_active ?? true) ? 'checked' : '' }}>
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
@endif

<!-- Form for Combo Service -->
@if(isset($combo))
<div id="combo-form" class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Sửa combo dịch vụ</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.services.update', $combo->id) }}" method="POST" enctype="multipart/form-data" id="comboForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="service_type" value="combo">
            <div class="form-group">
                <label for="combo_name">Tên combo <span class="text-danger">*</span></label>
                <input type="text" name="combo_name" id="combo_name" class="form-control @error('combo_name') is-invalid @enderror" 
                       value="{{ old('combo_name', $combo->name) }}" required>
                @error('combo_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="combo_category_id">Nhóm dịch vụ <span class="text-danger">*</span></label>
                    <select name="category_id" id="combo_category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                        <option value="">-- Chọn nhóm dịch vụ --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $combo->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
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
                           value="{{ old('combo_price', $combo->price) }}" required>
                    @error('combo_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="combo_status">Trạng thái</label>
                    <select name="combo_status" id="combo_status" class="form-control">
                        <option value="Hoạt động" {{ old('combo_status', $combo->status) == 'Hoạt động' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="Vô hiệu hóa" {{ old('combo_status', $combo->status) == 'Vô hiệu hóa' ? 'selected' : '' }}>Vô hiệu hóa</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="combo_image">Hình ảnh</label>
                    @if($combo->image)
                        <div class="mb-2">
                            <img src="{{ asset('legacy/images/products/' . $combo->image) }}" alt="{{ $combo->name }}" width="100" height="100" class="img-thumbnail">
                        </div>
                    @endif
                    <input type="file" name="combo_image" id="combo_image" class="form-control-file @error('combo_image') is-invalid @enderror" accept="image/*">
                    @error('combo_image')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="combo_description">Mô tả</label>
                <textarea name="combo_description" id="combo_description" rows="4" class="form-control @error('combo_description') is-invalid @enderror">{{ old('combo_description', $combo->description) }}</textarea>
                @error('combo_description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label>Chọn dịch vụ và biến thể <span class="text-danger">*</span></label>
                @php
                    $selectedItems = old('combo_items', []);
                    if (empty($selectedItems)) {
                        foreach ($combo->comboItems as $item) {
                            $selectedItems[$item->service_id] = [
                                'service_id' => $item->service_id,
                                'service_variant_id' => $item->service_variant_id
                            ];
                        }
                    }
                @endphp
                <div class="border rounded p-2" style="max-height: 400px; overflow-y: auto; background-color: #fff; border-color: #ced4da !important;">
                    @if($singleServices->count() > 0)
                        <h6 class="mb-2 text-primary"><i class="fas fa-tag"></i> Dịch vụ đơn</h6>
                        @foreach($singleServices as $singleService)
                            <div class="form-check ml-3 mb-2">
                                <input type="checkbox" name="combo_items[{{ $singleService->id }}][service_id]" 
                                       id="service_{{ $singleService->id }}" 
                                       class="form-check-input combo-service-checkbox" 
                                       value="{{ $singleService->id }}" 
                                       data-service-id="{{ $singleService->id }}"
                                       {{ isset($selectedItems[$singleService->id]) ? 'checked' : '' }}>
                                <label class="form-check-label" for="service_{{ $singleService->id }}" style="cursor: pointer; width: 100%;">
                                    <strong>{{ $singleService->name }}</strong> 
                                    ({{ $singleService->service_code ?? 'N/A' }}) - 
                                    <span class="text-primary">{{ number_format($singleService->base_price ?? 0, 0, ',', '.') }} đ</span>
                                </label>
                                <input type="hidden" name="combo_items[{{ $singleService->id }}][service_variant_id]" value="">
                            </div>
                        @endforeach
                    @endif

                    @if(isset($variantServices) && $variantServices->count() > 0)
                        <hr class="my-3">
                        <h6 class="mb-2 text-info"><i class="fas fa-layer-group"></i> Dịch vụ biến thể</h6>
                        @foreach($variantServices as $variantService)
                            @php
                                $isSelected = isset($selectedItems[$variantService->id]);
                                $selectedVariantId = $selectedItems[$variantService->id]['service_variant_id'] ?? null;
                            @endphp
                            <div class="mb-3 ml-3">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           id="variant_service_{{ $variantService->id }}" 
                                           class="form-check-input variant-service-checkbox" 
                                           data-service-id="{{ $variantService->id }}"
                                           {{ $isSelected ? 'checked' : '' }}>
                                    <label class="form-check-label" for="variant_service_{{ $variantService->id }}" style="cursor: pointer;">
                                        <strong>{{ $variantService->name }}</strong> 
                                        ({{ $variantService->service_code ?? 'N/A' }})
                                        <span class="badge badge-info ml-2">{{ $variantService->serviceVariants->count() }} biến thể</span>
                                    </label>
                                </div>
                                <div class="ml-4 mt-2 variant-options" id="variants_{{ $variantService->id }}" style="display: {{ $isSelected ? 'block' : 'none' }};">
                                    @foreach($variantService->serviceVariants as $variant)
                                        <div class="form-check mb-1">
                                            <input type="radio" 
                                                   name="combo_items[{{ $variantService->id }}][service_variant_id]" 
                                                   id="variant_{{ $variant->id }}" 
                                                   class="form-check-input" 
                                                   value="{{ $variant->id }}"
                                                   data-service-id="{{ $variantService->id }}"
                                                   {{ $selectedVariantId == $variant->id ? 'checked' : '' }}>
                                            <label class="form-check-label" for="variant_{{ $variant->id }}" style="cursor: pointer; font-size: 0.9em;">
                                                {{ $variant->name }} - 
                                                <span class="text-primary">{{ number_format($variant->price, 0, ',', '.') }} đ</span>
                                                @if($variant->is_active)
                                                    <span class="badge badge-success badge-sm">Hoạt động</span>
                                                @else
                                                    <span class="badge badge-secondary badge-sm">Vô hiệu hóa</span>
                                                @endif
                                            </label>
                                        </div>
                                    @endforeach
                                    <input type="hidden" name="combo_items[{{ $variantService->id }}][service_id]" value="{{ $variantService->id }}">
                                </div>
                            </div>
                        @endforeach
                    @endif

                    @if($singleServices->count() == 0 && (!isset($variantServices) || $variantServices->count() == 0))
                        <p class="text-muted mb-0">Chưa có dịch vụ nào. Vui lòng thêm dịch vụ trước.</p>
                    @endif
                </div>
                @error('service_ids')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
                @error('combo_items')
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
@endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const serviceTypeSelect = document.getElementById('service_type');
        const singleForm = document.getElementById('single-form');
        const variantForm = document.getElementById('variant-form');
        const variantServiceForm = document.getElementById('variant-service-form');
        const comboForm = document.getElementById('combo-form');

        function showForm(type) {
            if (singleForm) singleForm.style.display = 'none';
            if (variantForm) variantForm.style.display = 'none';
            if (variantServiceForm) variantServiceForm.style.display = 'none';
            if (comboForm) comboForm.style.display = 'none';

            if (type === 'single' && singleForm) {
                singleForm.style.display = 'block';
            } else if (type === 'variant') {
                if (variantServiceForm) {
                    variantServiceForm.style.display = 'block';
                    initVariantServiceForm();
                } else if (variantForm) {
                variantForm.style.display = 'block';
                }
            } else if (type === 'combo' && comboForm) {
                comboForm.style.display = 'block';
            }
        }

        if (serviceTypeSelect) {
            serviceTypeSelect.addEventListener('change', function() {
                showForm(this.value);
            });

            // Show form based on current type
            const selectedType = serviceTypeSelect.value;
            if (selectedType) {
                showForm(selectedType);
            }
        }

        // Khởi tạo form dịch vụ biến thể
        function initVariantServiceForm() {
            const container = document.getElementById('variantsContainer');
            if (!container) return;

            let variantIndex = container.querySelectorAll('.variant-item').length;
            let attributeIndexes = {};

            // Khởi tạo chỉ số thuộc tính cho các biến thể hiện có
            container.querySelectorAll('.variant-item').forEach((variantItem, index) {
                const variantIdx = variantItem.getAttribute('data-variant-index');
                const attrContainer = variantItem.querySelector('.attributes-container');
                if (attrContainer) {
                    const attrCount = attrContainer.querySelectorAll('.attribute-item').length;
                    attributeIndexes[variantIdx] = attrCount;
                } else {
                    attributeIndexes[variantIdx] = 0;
                }
            });

            // Xử lý thêm biến thể
            function addVariant() {
                const template = document.getElementById('variantTemplate');
                if (!template) return;

                const variantHtml = template.innerHTML.replace(/__INDEX__/g, variantIndex);
                const variantDiv = document.createElement('div');
                variantDiv.innerHTML = variantHtml;
                variantDiv.querySelector('.variant-item').setAttribute('data-variant-index', variantIndex);
                variantDiv.querySelector('.variant-number').textContent = container.querySelectorAll('.variant-item').length + 1;
                
                container.appendChild(variantDiv);
                
                // Khởi tạo chỉ số thuộc tính cho biến thể này
                attributeIndexes[variantIndex] = 0;
                
                // Gắn sự kiện cho nút xóa biến thể
                variantDiv.querySelector('.remove-variant-btn').addEventListener('click', function() {
                    if (container.querySelectorAll('.variant-item').length > 1) {
                        variantDiv.remove();
                        updateVariantNumbers();
                    } else {
                        alert('Phải có ít nhất một biến thể!');
                    }
                });
                
                // Gắn sự kiện cho nút thêm thuộc tính
                const addAttrBtn = variantDiv.querySelector('.add-attribute-btn');
                if (addAttrBtn) {
                    const currentVariantIndex = variantIndex;
                    addAttrBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        addAttribute(currentVariantIndex);
                    });
                }
                
                variantIndex++;
                updateVariantNumbers();
            }

            // Xử lý thêm thuộc tính
            function addAttribute(variantIndex) {
                const template = document.getElementById('attributeTemplate');
                if (!template) return;

                if (!attributeIndexes[variantIndex]) {
                    attributeIndexes[variantIndex] = 0;
                }
                
                const container = document.querySelector(`.attributes-container[data-variant-index="${variantIndex}"]`);
                if (!container) return;

                const attrIndex = attributeIndexes[variantIndex];
                const attrHtml = template.innerHTML
                    .replace(/__VARIANT_INDEX__/g, variantIndex)
                    .replace(/__ATTR_INDEX__/g, attrIndex);
                
                const attrDiv = document.createElement('div');
                attrDiv.innerHTML = attrHtml;
                attrDiv.querySelector('.attribute-item').setAttribute('data-attribute-index', attrIndex);
                
                container.appendChild(attrDiv);
                
                // Gắn sự kiện cho nút xóa thuộc tính
                attrDiv.querySelector('.remove-attribute-btn').addEventListener('click', function() {
                    attrDiv.remove();
                });
                
                attributeIndexes[variantIndex]++;
            }

            // Cập nhật số thứ tự biến thể
            function updateVariantNumbers() {
                const variants = container.querySelectorAll('.variant-item');
                variants.forEach((variant, index) => {
                    variant.querySelector('.variant-number').textContent = index + 1;
                });
            }

            // Gắn sự kiện cho nút thêm biến thể
            const addVariantBtn = document.getElementById('addVariantBtn');
            if (addVariantBtn) {
                addVariantBtn.addEventListener('click', addVariant);
            }

            // Gắn sự kiện cho các nút xóa biến thể hiện có
            container.querySelectorAll('.remove-variant-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const variantItem = this.closest('.variant-item');
                    if (container.querySelectorAll('.variant-item').length > 1) {
                        variantItem.remove();
                        updateVariantNumbers();
                    } else {
                        alert('Phải có ít nhất một biến thể!');
                    }
                });
            });

            // Gắn sự kiện cho các nút thêm thuộc tính hiện có
            container.querySelectorAll('.add-attribute-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const variantIndex = this.getAttribute('data-variant-index');
                    if (attributeIndexes[variantIndex] === undefined) {
                        const attrContainer = this.closest('.variant-item').querySelector('.attributes-container');
                        attributeIndexes[variantIndex] = attrContainer ? attrContainer.querySelectorAll('.attribute-item').length : 0;
                    }
                    addAttribute(variantIndex);
                });
            });

            // Gắn sự kiện cho các nút xóa thuộc tính hiện có
            container.querySelectorAll('.remove-attribute-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.closest('.attribute-item').remove();
                });
            });

        }

        // Khởi tạo form nếu đã có sẵn
        if (variantServiceForm && variantServiceForm.style.display !== 'none') {
            initVariantServiceForm();
        }

        // Xử lý chọn dịch vụ biến thể trong form combo
        document.querySelectorAll('.variant-service-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const serviceId = this.getAttribute('data-service-id');
                const variantOptions = document.getElementById('variants_' + serviceId);
                if (variantOptions) {
                    if (this.checked) {
                        variantOptions.style.display = 'block';
                        // Tự động chọn biến thể đầu tiên nếu chưa có biến thể nào được chọn
                        const firstVariant = variantOptions.querySelector('input[type="radio"]');
                        if (firstVariant && !variantOptions.querySelector('input[type="radio"]:checked')) {
                            firstVariant.checked = true;
                        }
                    } else {
                        variantOptions.style.display = 'none';
                        // Bỏ chọn tất cả biến thể
                        variantOptions.querySelectorAll('input[type="radio"]').forEach(radio => {
                            radio.checked = false;
                        });
                    }
                }
            });
        });

        // Khởi tạo hiển thị cho các dịch vụ biến thể đã được chọn (khi có old input)
        document.querySelectorAll('.variant-service-checkbox:checked').forEach(checkbox => {
            checkbox.dispatchEvent(new Event('change'));
        });
    });
</script>
@endpush
