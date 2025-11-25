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
    // Kiểm tra type từ URL parameter
    $urlType = request()->get('type', '');
    
    // Ưu tiên serviceType từ controller, sau đó mới đến session
    if (isset($combo)) {
        $serviceType = 'combo';
    } elseif (isset($variant)) {
        $serviceType = 'variant';
    } elseif (isset($service)) {
        // Nếu có serviceType từ controller thì dùng, không thì kiểm tra service có variants không
        if (isset($serviceType) && $serviceType == 'variant') {
            // Giữ nguyên variant
        } elseif (isset($serviceType) && $serviceType == 'single') {
            // Giữ nguyên single
        } elseif ($urlType === 'variant') {
            // Ưu tiên type từ URL
            $serviceType = 'variant';
        } elseif ($service->serviceVariants && $service->serviceVariants->count() > 0) {
            $serviceType = 'variant';
        } else {
            $serviceType = $serviceType ?? 'single';
        }
    } else {
        // Nếu có type trong URL, ưu tiên dùng nó
        if ($urlType) {
            $serviceType = $urlType;
        } else {
            $serviceType = session('service_type', 'single');
        }
    }
    
    // Debug: Log để kiểm tra
    \Log::info('Service Edit Debug', [
        'hasService' => isset($service),
        'hasVariant' => isset($variant),
        'hasCombo' => isset($combo),
        'urlType' => $urlType,
        'serviceType' => $serviceType,
        'variantCount' => isset($service) && $service->serviceVariants ? $service->serviceVariants->count() : 0
    ]);
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
@if(isset($service) && ($serviceType ?? '') == 'single' && ($service->serviceVariants->count() ?? 0) == 0)
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
@php
    $shouldShowVariantForm = false;
    
    // Kiểm tra serviceType từ nhiều nguồn
    $currentServiceType = $serviceType ?? ($service_type ?? '');
    
    // Kiểm tra từ URL parameter (lấy lại từ request)
    $urlTypeParam = request()->get('type', '');
    if ($urlTypeParam === 'variant') {
        $currentServiceType = 'variant';
    }
    
    if (isset($service)) {
        $variantCount = 0;
        if ($service->relationLoaded('serviceVariants')) {
            $variantCount = $service->serviceVariants->count();
        } elseif (method_exists($service, 'serviceVariants')) {
            $variantCount = $service->serviceVariants()->count();
        }
        // Hiển thị form nếu: type là variant HOẶC có variants
        $shouldShowVariantForm = ($currentServiceType == 'variant' || $variantCount > 0);
    }
    
    // Debug: Log để kiểm tra
    \Log::info('Variant Form Debug', [
        'hasService' => isset($service),
        'serviceType' => $serviceType ?? 'not set',
        'service_type' => $service_type ?? 'not set',
        'urlTypeParam' => $urlTypeParam,
        'currentServiceType' => $currentServiceType,
        'variantCount' => $variantCount ?? 0,
        'shouldShow' => $shouldShowVariantForm
    ]);
@endphp
@if($shouldShowVariantForm && isset($service))
<div id="variant-service-form" class="card shadow mb-4" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
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
        console.log('=== DOM CONTENT LOADED ===');
        const serviceTypeSelect = document.getElementById('service_type');
        const singleForm = document.getElementById('single-form');
        const variantServiceForm = document.getElementById('variant-service-form');
        const comboForm = document.getElementById('combo-form');
        
        console.log('serviceTypeSelect:', !!serviceTypeSelect);
        console.log('singleForm:', !!singleForm);
        console.log('variantServiceForm:', !!variantServiceForm);
        console.log('comboForm:', !!comboForm);
        
        if (variantServiceForm) {
            console.log('variantServiceForm display:', variantServiceForm.style.display);
            console.log('variantServiceForm offsetParent:', variantServiceForm.offsetParent !== null);
            console.log('variantServiceForm computed display:', window.getComputedStyle(variantServiceForm).display);
            
            // Đảm bảo form được hiển thị nếu đang ở trang variant
            var currentType = serviceTypeSelect ? serviceTypeSelect.value : '';
            if (currentType === 'variant') {
                console.log('Type là variant, force hiển thị form...');
                variantServiceForm.style.setProperty('display', 'block', 'important');
                variantServiceForm.style.setProperty('visibility', 'visible', 'important');
                variantServiceForm.style.setProperty('opacity', '1', 'important');
                variantServiceForm.style.setProperty('height', 'auto', 'important');
            } else if (variantServiceForm.offsetParent !== null) {
                console.log('Form variant đang hiển thị, đảm bảo nó visible...');
                variantServiceForm.style.setProperty('display', 'block', 'important');
                variantServiceForm.style.setProperty('visibility', 'visible', 'important');
                variantServiceForm.style.setProperty('opacity', '1', 'important');
            }
        }

        function showForm(type) {
            console.log('showForm được gọi với type:', type);
            // Chỉ ẩn các form khác, không ẩn form đang cần hiển thị
            if (type !== 'single' && singleForm) {
                singleForm.style.display = 'none';
            }
            if (type !== 'variant' && variantServiceForm) {
                console.log('Ẩn variantServiceForm vì type không phải variant');
                variantServiceForm.style.display = 'none';
            } else if (type === 'variant' && variantServiceForm) {
                // Đảm bảo form variant luôn được hiển thị
                console.log('Đảm bảo form variant được hiển thị');
                variantServiceForm.style.display = 'block';
                variantServiceForm.style.visibility = 'visible';
                variantServiceForm.style.opacity = '1';
            }
            if (type !== 'combo' && comboForm) {
                comboForm.style.display = 'none';
            }

            if (type === 'single' && singleForm) {
                singleForm.style.display = 'block';
            } else if (type === 'variant') {
                console.log('Type là variant, kiểm tra variantServiceForm...');
                if (variantServiceForm) {
                    console.log('✅ variantServiceForm tồn tại, hiển thị form...');
                    // Đảm bảo form được hiển thị
                    variantServiceForm.style.display = 'block';
                    variantServiceForm.style.visibility = 'visible';
                    console.log('Form variant đã được hiển thị, kiểm tra container...');
                    console.log('Form display:', variantServiceForm.style.display);
                    console.log('Form offsetParent:', variantServiceForm.offsetParent !== null);
                    
                    // Đợi một chút để DOM render xong, đặc biệt khi có nhiều dữ liệu
                    setTimeout(function() {
                        try {
                            console.log('Bắt đầu khởi tạo form variant...');
                            var container = document.getElementById('variantsContainer');
                            console.log('Container tìm thấy:', !!container);
                            if (container) {
                                var variantCount = container.querySelectorAll('.variant-item').length;
                                console.log('Số biến thể trong container:', variantCount);
                                if (variantCount > 0) {
                                    console.log('Có', variantCount, 'biến thể, bắt đầu khởi tạo...');
                                }
                            } else {
                                console.error('❌ Không tìm thấy variantsContainer!');
                            }
                            initVariantServiceForm();
                        } catch (e) {
                            console.error('❌ Lỗi khi khởi tạo form trong showForm:', e);
                            console.error('Stack trace:', e.stack);
                        }
                    }, 300); // Tăng thời gian chờ khi có nhiều dữ liệu
                } else {
                    console.error('❌ variantServiceForm không tồn tại!');
                }
            } else if (type === 'combo' && comboForm) {
                comboForm.style.display = 'block';
            }
        }

        if (serviceTypeSelect) {
            serviceTypeSelect.addEventListener('change', function() {
                console.log('Dropdown thay đổi, giá trị mới:', this.value);
                showForm(this.value);
            });

            // Show form based on current type
            const selectedType = serviceTypeSelect.value;
            console.log('Service type từ dropdown:', selectedType);
            if (selectedType) {
                console.log('Gọi showForm với type:', selectedType);
                showForm(selectedType);
            } else {
                // Nếu không có type từ dropdown, kiểm tra xem form variant có đang hiển thị không
                if (variantServiceForm && variantServiceForm.offsetParent !== null) {
                    console.log('Form variant đang hiển thị, khởi tạo...');
                    setTimeout(function() {
                        try {
                            initVariantServiceForm();
                        } catch (e) {
                            console.error('Lỗi khi khởi tạo form variant (fallback):', e);
                        }
                    }, 200);
                }
            }
        } else {
            console.warn('Không tìm thấy serviceTypeSelect');
        }

        // Khởi tạo form dịch vụ biến thể
        var variantFormData = {
            initialized: false,
            container: null,
            variantIndex: 0,
            attributeIndexes: {},
            attributeClickHandler: null  // Lưu handler để có thể xóa sau
        };
        
        function initVariantServiceForm() {
            console.log('=== BẮT ĐẦU KHỞI TẠO FORM VARIANT ===');
            var container = document.getElementById('variantsContainer');
            if (!container) {
                console.error('❌ Không tìm thấy variantsContainer');
                console.log('Đang tìm lại container sau 500ms...');
                setTimeout(function() {
                    container = document.getElementById('variantsContainer');
                    if (container) {
                        console.log('✅ Tìm thấy container sau delay');
                        initVariantServiceForm();
                    } else {
                        console.error('❌ Vẫn không tìm thấy container');
                    }
                }, 500);
                return;
            }

            console.log('✅ Tìm thấy variantsContainer');

            // Nếu đã khởi tạo rồi, không khởi tạo lại để tránh gắn event listener nhiều lần
            if (variantFormData.initialized) {
                console.log('⚠️ Form đã được khởi tạo, bỏ qua khởi tạo lại...');
                return;
            }

            variantFormData.container = container;
            
            // Đếm số biến thể hiện có và set variantIndex = số lượng đó (để biến thể tiếp theo sẽ có index đúng)
            var existingVariants = container.querySelectorAll('.variant-item');
            variantFormData.variantIndex = existingVariants.length;
            variantFormData.attributeIndexes = {};
            
            console.log('📊 Khởi tạo form variant service, số biến thể hiện có:', existingVariants.length);
            console.log('📊 variantIndex được set thành:', variantFormData.variantIndex);

            // Khởi tạo chỉ số thuộc tính cho các biến thể hiện có
            try {
                container.querySelectorAll('.variant-item').forEach(function(variantItem, index) {
                    try {
                        var variantIdx = variantItem.getAttribute('data-variant-index');
                        if (variantIdx === null || variantIdx === undefined || variantIdx === '') {
                            // Nếu không có data-variant-index, set lại dựa trên index trong DOM
                            variantIdx = index.toString();
                            variantItem.setAttribute('data-variant-index', variantIdx);
                        }
                        var attrContainer = variantItem.querySelector('.attributes-container');
                        if (attrContainer) {
                            var attrCount = attrContainer.querySelectorAll('.attribute-item').length;
                            variantFormData.attributeIndexes[variantIdx] = attrCount;
                        } else {
                            variantFormData.attributeIndexes[variantIdx] = 0;
                        }
                        console.log('✅ Khởi tạo variant:', variantIdx, 'số thuộc tính:', variantFormData.attributeIndexes[variantIdx]);
                    } catch (e) {
                        console.error('Lỗi khi khởi tạo variant tại index', index, ':', e);
                    }
                });
            } catch (e) {
                console.error('Lỗi khi khởi tạo các variant:', e);
            }
            
            // Đảm bảo variantIndex >= số lượng biến thể hiện có
            if (variantFormData.variantIndex < existingVariants.length) {
                variantFormData.variantIndex = existingVariants.length;
                console.log('Cập nhật variantIndex thành:', variantFormData.variantIndex);
            }

            // Xử lý thêm biến thể
            window.addVariant = function() {
                var template = document.getElementById('variantTemplate');
                if (!template) {
                    console.error('Không tìm thấy variantTemplate');
                    return;
                }

                var container = variantFormData.container;
                var variantIndex = variantFormData.variantIndex;
                var variantHtml = template.innerHTML.replace(/__INDEX__/g, variantIndex);
                var variantDiv = document.createElement('div');
                variantDiv.innerHTML = variantHtml;
                var variantItem = variantDiv.querySelector('.variant-item');
                if (variantItem) {
                    variantItem.setAttribute('data-variant-index', variantIndex);
                }
                var variantNumber = variantDiv.querySelector('.variant-number');
                if (variantNumber) {
                    variantNumber.textContent = container.querySelectorAll('.variant-item').length + 1;
                }
                
                container.appendChild(variantDiv);
                
                // Khởi tạo chỉ số thuộc tính cho biến thể này
                variantFormData.attributeIndexes[variantIndex] = 0;
                
                // Gắn sự kiện cho nút xóa biến thể
                var removeBtn = variantDiv.querySelector('.remove-variant-btn');
                if (removeBtn) {
                    removeBtn.addEventListener('click', function() {
                        if (container.querySelectorAll('.variant-item').length > 1) {
                            if (confirm('Bạn có chắc muốn xóa biến thể này không?')) {
                                variantDiv.remove();
                                updateVariantNumbers();
                            }
                        } else {
                            alert('Phải có ít nhất một biến thể!');
                        }
                    });
                }
                
                variantFormData.variantIndex++;
                updateVariantNumbers();
            };

            // Xử lý thêm thuộc tính
            window.addAttribute = function(variantIndex) {
                var template = document.getElementById('attributeTemplate');
                if (!template) {
                    console.error('Không tìm thấy template attributeTemplate');
                    return;
                }

                // Đảm bảo có index cho variant này
                if (variantFormData.attributeIndexes[variantIndex] === undefined || variantFormData.attributeIndexes[variantIndex] === null) {
                    var variantItem = document.querySelector('.variant-item[data-variant-index="' + variantIndex + '"]');
                    var attrContainer = variantItem ? variantItem.querySelector('.attributes-container') : null;
                    variantFormData.attributeIndexes[variantIndex] = attrContainer ? attrContainer.querySelectorAll('.attribute-item').length : 0;
                }
                
                var container = document.querySelector('.attributes-container[data-variant-index="' + variantIndex + '"]');
                if (!container) {
                    console.error('Không tìm thấy container cho variant:', variantIndex);
                    return;
                }

                var attrIndex = variantFormData.attributeIndexes[variantIndex];
                var attrHtml = template.innerHTML
                    .replace(/__VARIANT_INDEX__/g, variantIndex)
                    .replace(/__ATTR_INDEX__/g, attrIndex);
                
                var attrDiv = document.createElement('div');
                attrDiv.innerHTML = attrHtml;
                var attrItem = attrDiv.querySelector('.attribute-item');
                if (attrItem) {
                    attrItem.setAttribute('data-attribute-index', attrIndex);
                }
                
                container.appendChild(attrDiv);
                
                // Gắn sự kiện cho nút xóa thuộc tính
                var removeBtn = attrDiv.querySelector('.remove-attribute-btn');
                if (removeBtn) {
                    removeBtn.addEventListener('click', function() {
                        if (confirm('Bạn có chắc muốn xóa thuộc tính này không?')) {
                            attrDiv.remove();
                        }
                    });
                }
                
                variantFormData.attributeIndexes[variantIndex]++;
            };

            // Cập nhật số thứ tự biến thể
            window.updateVariantNumbers = function() {
                var container = variantFormData.container;
                if (!container) return;
                var variants = container.querySelectorAll('.variant-item');
                variants.forEach(function(variant, index) {
                    var numberEl = variant.querySelector('.variant-number');
                    if (numberEl) {
                        numberEl.textContent = index + 1;
                    }
                });
            };

            // Gắn sự kiện cho nút thêm biến thể (chỉ gắn một lần)
            var addVariantBtn = document.getElementById('addVariantBtn');
            if (addVariantBtn) {
                // Xóa event listener cũ nếu có
                var newAddVariantBtn = addVariantBtn.cloneNode(true);
                addVariantBtn.parentNode.replaceChild(newAddVariantBtn, addVariantBtn);
                addVariantBtn = newAddVariantBtn;
                
                addVariantBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Click thêm biến thể');
                    if (window.addVariant) {
                        window.addVariant();
                    }
                });
            } else {
                console.error('Không tìm thấy nút addVariantBtn');
            }

            // Gắn sự kiện cho các nút xóa biến thể hiện có
            container.querySelectorAll('.remove-variant-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var variantItem = this.closest('.variant-item');
                    var container = variantFormData.container;
                    if (container && container.querySelectorAll('.variant-item').length > 1) {
                        if (confirm('Bạn có chắc muốn xóa biến thể này không?')) {
                            variantItem.remove();
                            if (window.updateVariantNumbers) {
                                window.updateVariantNumbers();
                            }
                        }
                    } else {
                        alert('Phải có ít nhất một biến thể!');
                    }
                });
            });

            // Sử dụng event delegation để gắn sự kiện cho tất cả nút thêm thuộc tính
            // Chỉ gắn một lần bằng cách kiểm tra flag
            if (!variantFormData.attributeClickHandler) {
                variantFormData.attributeClickHandler = function(e) {
                    var target = e.target;
                    // Kiểm tra nếu click vào nút hoặc icon bên trong nút
                    var btn = target.closest('.add-attribute-btn');
                    if (!btn && target.classList.contains('add-attribute-btn')) {
                        btn = target;
                    }
                    
                    if (btn) {
                        e.preventDefault();
                        e.stopPropagation();
                        var variantIdx = btn.getAttribute('data-variant-index');
                        console.log('Click thêm thuộc tính cho variant:', variantIdx);
                        
                        if (!variantIdx) {
                            console.error('Không tìm thấy variant-index');
                            return;
                        }
                        
                        // Đảm bảo có index cho variant này
                        if (variantFormData.attributeIndexes[variantIdx] === undefined || variantFormData.attributeIndexes[variantIdx] === null) {
                            var variantItem = btn.closest('.variant-item');
                            var attrContainer = variantItem ? variantItem.querySelector('.attributes-container') : null;
                            variantFormData.attributeIndexes[variantIdx] = attrContainer ? attrContainer.querySelectorAll('.attribute-item').length : 0;
                        }
                        
                        if (window.addAttribute) {
                            window.addAttribute(variantIdx);
                        } else {
                            console.error('Hàm addAttribute không tồn tại');
                        }
                    }
                };
                container.addEventListener('click', variantFormData.attributeClickHandler);
            }

            // Sử dụng event delegation cho nút xóa thuộc tính (áp dụng cho cả thuộc tính được thêm động)
            if (!variantFormData.removeAttributeClickHandler) {
                variantFormData.removeAttributeClickHandler = function(e) {
                    var target = e.target;
                    // Kiểm tra nếu click vào nút xóa hoặc icon bên trong nút
                    var btn = target.closest('.remove-attribute-btn');
                    if (!btn && target.classList.contains('remove-attribute-btn')) {
                        btn = target;
                    }
                    
                    if (btn) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (confirm('Bạn có chắc muốn xóa thuộc tính này không?')) {
                            var attrItem = btn.closest('.attribute-item');
                            if (attrItem) {
                                attrItem.remove();
                            }
                        }
                    }
                };
                container.addEventListener('click', variantFormData.removeAttributeClickHandler);
            }
            
            // Gắn sự kiện cho các nút xóa thuộc tính hiện có (nếu chưa dùng event delegation)
            try {
                container.querySelectorAll('.remove-attribute-btn').forEach(function(btn) {
                    // Kiểm tra xem đã có event listener chưa
                    if (!btn.hasAttribute('data-listener-attached')) {
                        btn.setAttribute('data-listener-attached', 'true');
                        btn.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            if (confirm('Bạn có chắc muốn xóa thuộc tính này không?')) {
                                var attrItem = this.closest('.attribute-item');
                                if (attrItem) {
                                    attrItem.remove();
                                }
                            }
                        });
                    }
                });
            } catch (e) {
                console.error('Lỗi khi gắn sự kiện cho nút xóa thuộc tính:', e);
            }

            // Đánh dấu đã khởi tạo
            variantFormData.initialized = true;
            console.log('Form variant service đã được khởi tạo thành công!');
        }

        // Khởi tạo form nếu đã có sẵn (khi load trang với type=variant)
        if (variantServiceForm) {
            // Kiểm tra nếu form đang hiển thị
            var isVisible = variantServiceForm.style.display !== 'none' || !variantServiceForm.style.display || variantServiceForm.offsetParent !== null;
            console.log('Kiểm tra form variant:', {
                exists: !!variantServiceForm,
                isVisible: isVisible,
                display: variantServiceForm.style.display
            });
            
            if (isVisible) {
                // Đợi một chút để DOM sẵn sàng
                setTimeout(function() {
                    try {
                        console.log('Đang khởi tạo form variant...');
                        initVariantServiceForm();
                    } catch (e) {
                        console.error('Lỗi khi khởi tạo form variant:', e);
                        console.error(e.stack);
                    }
                }, 200);
            }
        }
        
        // Đảm bảo khởi tạo khi form variant được chọn từ dropdown
        if (serviceTypeSelect) {
            var currentType = serviceTypeSelect.value;
            console.log('Service type hiện tại:', currentType);
            if (currentType === 'variant' && variantServiceForm) {
                setTimeout(function() {
                    try {
                        console.log('Khởi tạo form variant từ dropdown...');
                        initVariantServiceForm();
                    } catch (e) {
                        console.error('Lỗi khi khởi tạo form variant từ dropdown:', e);
                        console.error(e.stack);
                    }
                }, 400);
            }
        }
        
        // Fallback: Thử khởi tạo lại sau 1 giây nếu chưa được khởi tạo
        setTimeout(function() {
            if (variantServiceForm && variantServiceForm.offsetParent !== null) {
                var container = document.getElementById('variantsContainer');
                if (container && !variantFormData.initialized) {
                    console.log('Fallback: Khởi tạo form variant...');
                    try {
                        initVariantServiceForm();
                    } catch (e) {
                        console.error('Lỗi khi khởi tạo form variant (fallback):', e);
                        console.error(e.stack);
                    }
                }
            }
        }, 1000);

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
