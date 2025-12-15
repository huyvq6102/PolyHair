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

@php
    // Determine which entity we're editing
    $editEntity = null;
    $editId = null;
    if (isset($combo)) {
        $editEntity = $combo;
        $editId = $combo->id;
    } elseif (isset($variant) && $variant->service) {
        // When editing a variant, we edit the service that contains it
        $editEntity = $variant->service;
        $editId = $variant->service->id;
        // Also set $service for compatibility with the rest of the form
        $service = $variant->service;
        // Load variants if not already loaded
        if (!$service->relationLoaded('serviceVariants')) {
            $service->load('serviceVariants.variantAttributes');
        }
    } elseif (isset($service)) {
        $editEntity = $service;
        $editId = $service->id;
    }
    
    // Get common field values
    $commonName = old('name', old('service_name', old('combo_name', $editEntity->name ?? '')));
    $commonCategoryId = old('category_id', $editEntity->category_id ?? '');
    $commonDescription = old('description', old('combo_description', $editEntity->description ?? ''));
    $commonImage = $editEntity->image ?? null;
    $commonStatus = old('status', old('combo_status', $editEntity->status ?? 'Hoạt động'));
@endphp

<!-- Main Form with Common Fields -->
@if($editEntity)
<form action="{{ route('admin.services.update', $editId) }}" method="POST" enctype="multipart/form-data" id="serviceForm">
    @csrf
    @method('PUT')
    <input type="hidden" name="service_type" id="service_type_hidden" value="{{ $serviceType }}">
    
    <!-- Common Fields Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin chung</h6>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="name">Tên dịch vụ <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                           value="{{ $commonName }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group col-md-6">
                    <label for="service_type">Loại dịch vụ <span class="text-danger">*</span></label>
                    <select name="service_type" id="service_type" class="form-control @error('service_type') is-invalid @enderror" required>
                        <option value="">-- Chọn loại dịch vụ --</option>
                        <option value="single" {{ $serviceType == 'single' ? 'selected' : '' }}>Dịch vụ đơn</option>
                        <option value="variant" {{ $serviceType == 'variant' ? 'selected' : '' }}>Dịch vụ biến thể</option>
                        <option value="combo" {{ $serviceType == 'combo' ? 'selected' : '' }}>Combo</option>
                    </select>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle"></i> Form sẽ tự động hiển thị các trường phù hợp
                    </small>
                    @error('service_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="category_id">Nhóm dịch vụ <span class="text-danger">*</span></label>
                    <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                        <option value="">-- Chọn nhóm dịch vụ --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $commonCategoryId == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group col-md-6">
                    <label for="status">Trạng thái</label>
                    <select name="status" id="status" class="form-control">
                        <option value="Hoạt động" {{ $commonStatus == 'Hoạt động' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="Vô hiệu hóa" {{ $commonStatus == 'Vô hiệu hóa' ? 'selected' : '' }}>Vô hiệu hóa</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="description">Mô tả</label>
                <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ $commonDescription }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="image">Hình ảnh</label>
                @if($commonImage)
                    <div class="mb-2">
                        <img src="{{ asset('legacy/images/products/' . $commonImage) }}" alt="{{ $commonName }}" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                    </div>
                @endif
                <div class="custom-file">
                    <input type="file" name="image" id="image" class="custom-file-input @error('image') is-invalid @enderror" accept="image/*" onchange="previewImage(this)">
                    <label class="custom-file-label" for="image" id="imageLabel">Chọn tệp mới (để trống nếu giữ nguyên)</label>
                </div>
                <small class="form-text text-muted">Định dạng: JPG, PNG, GIF (tối đa 2MB)</small>
                <div id="imagePreview" class="mt-3" style="display: none;">
                    <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                </div>
                @error('image')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <!-- Type-Specific Fields: Single Service -->
    @if(isset($service) && $serviceType == 'single' && ($service->serviceVariants->count() ?? 0) == 0)
    <div id="single-form" class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin dịch vụ đơn</h6>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="base_price">Giá <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="base_price" id="base_price" class="form-control @error('base_price') is-invalid @enderror" 
                           value="{{ old('base_price', $service->base_price) }}" required>
                    @error('base_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group col-md-6">
                    <label for="base_duration">Thời lượng (phút)</label>
                    <input type="number" name="base_duration" id="base_duration" class="form-control @error('base_duration') is-invalid @enderror" 
                           value="{{ old('base_duration', $service->base_duration) }}" min="0">
                    @error('base_duration')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Type-Specific Fields: Variant Service -->
    @php
        $shouldShowVariantForm = false;
        if (isset($service)) {
            $variantCount = 0;
            if ($service->relationLoaded('serviceVariants')) {
                $variantCount = $service->serviceVariants->count();
            } elseif (method_exists($service, 'serviceVariants')) {
                $variantCount = $service->serviceVariants()->count();
            }
            $shouldShowVariantForm = ($serviceType == 'variant' || $variantCount > 0);
        }
    @endphp
    @if($shouldShowVariantForm && isset($service))
    <div id="variant-form" class="card shadow mb-4" style="display: {{ $serviceType == 'variant' ? 'block' : 'none' }};">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin dịch vụ biến thể</h6>
        </div>
        <div class="card-body">

            <!-- Danh sách biến thể -->
            <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle"></i> <strong>Lưu ý:</strong> Mỗi biến thể có thể có giá và thời lượng riêng. Bạn có thể thêm nhiều thuộc tính cho mỗi biến thể.
            </div>
            <div class="card mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-layer-group"></i> Biến thể dịch vụ
                    </h6>
                    <button type="button" class="btn btn-sm btn-primary" id="addVariantBtn">
                        <i class="fas fa-plus"></i> Thêm biến thể
                    </button>
                </div>
                <div class="card-body">
                    <div id="variantsContainer">
                        @foreach($service->serviceVariants as $index => $variant)
                            <div class="variant-item border rounded p-4 mb-3 bg-light" data-variant-index="{{ $index }}" data-variant-id="{{ $variant->id }}" style="background-color: #f8f9fa !important;">
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-tag"></i> Biến thể <span class="variant-number">{{ $index + 1 }}</span>
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-danger remove-variant-btn" {{ $service->serviceVariants->count() === 1 ? 'disabled' : '' }}>
                                        <i class="fas fa-times"></i> Xóa
                                    </button>
                                </div>
                                <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant->id }}">
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label class="font-weight-bold">Tên biến thể <span class="text-danger">*</span></label>
                                        <input type="text" name="variants[{{ $index }}][name]" class="form-control" value="{{ old("variants.$index.name", $variant->name) }}" required>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label class="font-weight-bold">Giá (VNĐ) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" name="variants[{{ $index }}][price]" class="form-control" value="{{ old("variants.$index.price", $variant->price) }}" min="0" required>
                                            <div class="input-group-append">
                                                <span class="input-group-text">đ</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label class="font-weight-bold">Thời lượng (phút) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" name="variants[{{ $index }}][duration]" class="form-control" value="{{ old("variants.$index.duration", $variant->duration) }}" min="0" required>
                                            <div class="input-group-append">
                                                <span class="input-group-text">phút</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label class="font-weight-bold">Trạng thái</label>
                                        <div class="custom-control custom-switch mt-2">
                                            <input type="hidden" name="variants[{{ $index }}][is_active]" value="0">
                                            <input type="checkbox" class="custom-control-input" name="variants[{{ $index }}][is_active]" value="1" id="variant_active_{{ $index }}" {{ old("variants.$index.is_active", $variant->is_active) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="variant_active_{{ $index }}">Kích hoạt</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold">Ghi chú</label>
                                    <textarea name="variants[{{ $index }}][notes]" class="form-control" rows="2">{{ old("variants.$index.notes", $variant->notes) }}</textarea>
                                </div>
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="mb-0 font-weight-bold">
                                            <i class="fas fa-list"></i> Thuộc tính biến thể
                                        </label>
                                        <button type="button" class="btn btn-sm btn-outline-primary add-attribute-btn" data-variant-index="{{ $index }}">
                                            <i class="fas fa-plus"></i> Thêm thuộc tính
                                        </button>
                                    </div>
                                    <div class="attributes-container" data-variant-index="{{ $index }}">
                                        @foreach($variant->variantAttributes as $attrIndex => $attribute)
                                            <div class="attribute-item border rounded p-3 mb-2 bg-white" data-attribute-index="{{ $attrIndex }}">
                                                <div class="form-row align-items-end">
                                                    <div class="form-group col-md-5 mb-0">
                                                        <label class="font-weight-bold">Tên thuộc tính <span class="text-danger">*</span></label>
                                                        <input type="text" name="variants[{{ $index }}][attributes][{{ $attrIndex }}][name]" class="form-control" value="{{ old("variants.$index.attributes.$attrIndex.name", $attribute->attribute_name) }}" required>
                                                    </div>
                                                    <div class="form-group col-md-5 mb-0">
                                                        <label class="font-weight-bold">Giá trị <span class="text-danger">*</span></label>
                                                        <input type="text" name="variants[{{ $index }}][attributes][{{ $attrIndex }}][value]" class="form-control" value="{{ old("variants.$index.attributes.$attrIndex.value", $attribute->attribute_value) }}" required>
                                                    </div>
                                                    <div class="form-group col-md-2 mb-0">
                                                        <button type="button" class="btn btn-sm btn-danger remove-attribute-btn w-100" title="Xóa thuộc tính">
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

        </div>
    </div>
    @endif

<!-- Template cho biến thể mới -->
<template id="variantTemplate">
    <div class="variant-item border rounded p-4 mb-3 bg-light" data-variant-index="__INDEX__" style="background-color: #f8f9fa !important;">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-tag"></i> Biến thể <span class="variant-number"></span>
            </h6>
            <button type="button" class="btn btn-sm btn-danger remove-variant-btn">
                <i class="fas fa-times"></i> Xóa
            </button>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4">
                <label class="font-weight-bold">Tên biến thể <span class="text-danger">*</span></label>
                <input type="text" name="variants[__INDEX__][name]" class="form-control" placeholder="Nhập tên biến thể" required>
            </div>
            <div class="form-group col-md-3">
                <label class="font-weight-bold">Giá (VNĐ) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" step="0.01" name="variants[__INDEX__][price]" class="form-control" placeholder="0" min="0" required>
                    <div class="input-group-append">
                        <span class="input-group-text">đ</span>
                    </div>
                </div>
            </div>
            <div class="form-group col-md-3">
                <label class="font-weight-bold">Thời lượng (phút) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" name="variants[__INDEX__][duration]" class="form-control" placeholder="0" min="0" required>
                    <div class="input-group-append">
                        <span class="input-group-text">phút</span>
                    </div>
                </div>
            </div>
            <div class="form-group col-md-2">
                <label class="font-weight-bold">Trạng thái</label>
                <div class="custom-control custom-switch mt-2">
                    <input type="hidden" name="variants[__INDEX__][is_active]" value="0">
                    <input type="checkbox" class="custom-control-input" name="variants[__INDEX__][is_active]" value="1" id="variant_active___INDEX__" checked>
                    <label class="custom-control-label" for="variant_active___INDEX__">Kích hoạt</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="font-weight-bold">Ghi chú</label>
            <textarea name="variants[__INDEX__][notes]" class="form-control" rows="2" placeholder="Nhập ghi chú (tùy chọn)"></textarea>
        </div>
        <div class="form-group">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="mb-0 font-weight-bold">
                    <i class="fas fa-list"></i> Thuộc tính biến thể
                </label>
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
    <div class="attribute-item border rounded p-3 mb-2 bg-white" data-attribute-index="__ATTR_INDEX__">
        <div class="form-row align-items-end">
            <div class="form-group col-md-5 mb-0">
                <label class="font-weight-bold">Tên thuộc tính <span class="text-danger">*</span></label>
                <input type="text" name="variants[__VARIANT_INDEX__][attributes][__ATTR_INDEX__][name]" class="form-control" placeholder="Ví dụ: Loại tóc" required>
            </div>
            <div class="form-group col-md-5 mb-0">
                <label class="font-weight-bold">Giá trị <span class="text-danger">*</span></label>
                <input type="text" name="variants[__VARIANT_INDEX__][attributes][__ATTR_INDEX__][value]" class="form-control" placeholder="Ví dụ: Dài" required>
            </div>
            <div class="form-group col-md-2 mb-0">
                <button type="button" class="btn btn-sm btn-danger remove-attribute-btn w-100" title="Xóa thuộc tính">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</template>

    <!-- Type-Specific Fields: Combo Service -->
    @if(isset($combo))
    <div id="combo-form" class="card shadow mb-4" style="display: {{ $serviceType == 'combo' ? 'block' : 'none' }};">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin combo</h6>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="combo_price">Giá combo <span class="text-danger">*</span></label>
                <input type="number" step="0.01" name="combo_price" id="combo_price" class="form-control @error('combo_price') is-invalid @enderror" 
                       value="{{ old('combo_price', $combo->price) }}" required>
                @error('combo_price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="combo_duration">Thời lượng (phút)</label>
                <input type="number" name="combo_duration" id="combo_duration" class="form-control @error('combo_duration') is-invalid @enderror" 
                       value="{{ old('combo_duration', $combo->duration) }}" min="0">
                @error('combo_duration')
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
                                <input type="checkbox" 
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
                                @if(isset($selectedItems[$singleService->id]))
                                    <input type="hidden" name="combo_items[{{ $singleService->id }}][service_id]" value="{{ $singleService->id }}">
                                    <input type="hidden" name="combo_items[{{ $singleService->id }}][service_variant_id]" value="">
                                @endif
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
                                    @if($isSelected)
                                        <input type="hidden" name="combo_items[{{ $variantService->id }}][service_id]" value="{{ $variantService->id }}">
                                    @endif
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
        </div>
    </div>
    @endif

    <!-- Submit Buttons -->
    <div class="form-group mt-4">
        <div class="d-flex justify-content-start">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> Cập nhật dịch vụ
            </button>
        </div>
    </div>
</form>
@endif
@endsection

@push('scripts')
<script>
    // Preview hình ảnh
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        const label = document.getElementById('imageLabel');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            };
            
            reader.readAsDataURL(input.files[0]);
            label.textContent = input.files[0].name;
        } else {
            preview.style.display = 'none';
            label.textContent = 'Chọn tệp mới (để trống nếu giữ nguyên)';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const serviceTypeSelect = document.getElementById('service_type');
        const singleForm = document.getElementById('single-form');
        const variantServiceForm = document.getElementById('variant-form');
        const comboForm = document.getElementById('combo-form');
        const serviceForm = document.getElementById('serviceForm');
        const serviceTypeHidden = document.getElementById('service_type_hidden');
        const nameField = document.getElementById('name');

        function showForm(type) {
            // Update hidden field
            if (serviceTypeHidden) {
                serviceTypeHidden.value = type;
            }

            // Remove required from all hidden form fields to prevent validation errors
            function removeRequiredFromForm(formElement) {
                if (formElement) {
                    const requiredFields = formElement.querySelectorAll('[required]');
                    requiredFields.forEach(function(field) {
                        field.removeAttribute('required');
                        field.setAttribute('data-was-required', 'true');
                    });
                }
            }

            function addRequiredToForm(formElement) {
                if (formElement) {
                    const fields = formElement.querySelectorAll('[data-was-required="true"]');
                    fields.forEach(function(field) {
                        field.setAttribute('required', 'required');
                    });
                }
            }

            // Hide all type-specific forms and remove required
            if (singleForm) {
                removeRequiredFromForm(singleForm);
                singleForm.style.display = 'none';
            }
            if (variantServiceForm) {
                removeRequiredFromForm(variantServiceForm);
                variantServiceForm.style.display = 'none';
            }
            if (comboForm) {
                removeRequiredFromForm(comboForm);
                comboForm.style.display = 'none';
            }

            // Show appropriate form based on type and restore required
            if (type === 'single' && singleForm) {
                singleForm.style.display = 'block';
                addRequiredToForm(singleForm);
            } else if (type === 'variant' && variantServiceForm) {
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

        // Handle form submission - rename fields based on service type
        if (serviceForm) {
            serviceForm.addEventListener('submit', function(e) {
                const serviceType = serviceTypeSelect.value;
                
                // Remove required from all hidden forms to prevent browser validation errors
                if (singleForm && singleForm.style.display === 'none') {
                    singleForm.querySelectorAll('[required]').forEach(function(field) {
                        field.removeAttribute('required');
                    });
                }
                if (variantServiceForm && variantServiceForm.style.display === 'none') {
                    variantServiceForm.querySelectorAll('[required]').forEach(function(field) {
                        field.removeAttribute('required');
                    });
                }
                if (comboForm && comboForm.style.display === 'none') {
                    comboForm.querySelectorAll('[required]').forEach(function(field) {
                        field.removeAttribute('required');
                    });
                }
                
                // Validate variant form
                if (serviceType === 'variant') {
                    const variantsContainer = document.getElementById('variantsContainer');
                    const variants = variantsContainer ? variantsContainer.querySelectorAll('.variant-item') : [];
                    
                    if (variants.length === 0) {
                        e.preventDefault();
                        alert('Vui lòng thêm ít nhất một biến thể!');
                        return false;
                    }
                    
                    // Validate each variant has required fields
                    let isValid = true;
                    variants.forEach(function(variant) {
                        const name = variant.querySelector('input[name*="[name]"]');
                        const price = variant.querySelector('input[name*="[price]"]');
                        const duration = variant.querySelector('input[name*="[duration]"]');
                        
                        if (!name || !name.value.trim()) {
                            isValid = false;
                            name.classList.add('is-invalid');
                        }
                        if (!price || !price.value || parseFloat(price.value) < 0) {
                            isValid = false;
                            price.classList.add('is-invalid');
                        }
                        if (!duration || !duration.value || parseInt(duration.value) < 0) {
                            isValid = false;
                            duration.classList.add('is-invalid');
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                        alert('Vui lòng điền đầy đủ thông tin cho tất cả biến thể!');
                        return false;
                    }
                    
                    // For variant, rename 'name' to 'service_name'
                    const nameInput = document.createElement('input');
                    nameInput.type = 'hidden';
                    nameInput.name = 'service_name';
                    nameInput.value = nameField.value;
                    serviceForm.appendChild(nameInput);
                    nameField.disabled = true;
                } else if (serviceType === 'combo') {
                    // For combo, rename 'name' to 'combo_name'
                    const nameInput = document.createElement('input');
                    nameInput.type = 'hidden';
                    nameInput.name = 'combo_name';
                    nameInput.value = nameField.value;
                    serviceForm.appendChild(nameInput);
                    nameField.disabled = true;
                    
                    // Rename 'image' to 'combo_image'
                    const imageField = document.getElementById('image');
                    if (imageField) {
                        imageField.name = 'combo_image';
                    }
                    
                    // Rename 'status' to 'combo_status'
                    const statusField = document.getElementById('status');
                    if (statusField) {
                        statusField.name = 'combo_status';
                    }
                    
                    // Rename 'description' to 'combo_description'
                    const descriptionField = document.getElementById('description');
                    if (descriptionField) {
                        descriptionField.name = 'combo_description';
                    }
                    
                    // Đảm bảo chỉ gửi các combo items được chọn
                    // Xử lý dịch vụ đơn - disable checkbox không được check
                    document.querySelectorAll('.combo-service-checkbox').forEach(checkbox => {
                        if (!checkbox.checked) {
                            checkbox.disabled = true;
                        }
                    });
                    
                    // Xử lý dịch vụ biến thể - disable checkbox và ẩn các radio không được check
                    document.querySelectorAll('.variant-service-checkbox').forEach(checkbox => {
                        if (!checkbox.checked) {
                            checkbox.disabled = true;
                            const serviceId = checkbox.getAttribute('data-service-id');
                            const variantOptions = document.getElementById('variants_' + serviceId);
                            if (variantOptions) {
                                // Disable tất cả radio buttons
                                variantOptions.querySelectorAll('input[type="radio"]').forEach(radio => {
                                    radio.disabled = true;
                                });
                            }
                        }
                    });
                }
            });
        }

        if (serviceTypeSelect) {
            serviceTypeSelect.addEventListener('change', function() {
                const selectedType = this.value;
                
                // Thêm hiệu ứng khi chọn loại dịch vụ
                if (selectedType) {
                    // Scroll đến phần form tương ứng
                    setTimeout(function() {
                        if (selectedType === 'single' && singleForm) {
                            singleForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        } else if (selectedType === 'variant' && variantServiceForm) {
                            variantServiceForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        } else if (selectedType === 'combo' && comboForm) {
                            comboForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }
                    }, 100);
                }
                
                showForm(selectedType);
            });

            // Show form based on current type
            const selectedType = serviceTypeSelect.value;
            console.log('Service type từ dropdown:', selectedType);
            if (selectedType) {
                console.log('Gọi showForm với type:', selectedType);
                showForm(selectedType);
            } else {
                // If no type selected, remove required from all hidden forms
                if (singleForm) {
                    singleForm.querySelectorAll('[required]').forEach(function(field) {
                        field.removeAttribute('required');
                    });
                }
                if (variantServiceForm) {
                    variantServiceForm.querySelectorAll('[required]').forEach(function(field) {
                        field.removeAttribute('required');
                    });
                }
                if (comboForm) {
                    comboForm.querySelectorAll('[required]').forEach(function(field) {
                        field.removeAttribute('required');
                    });
                }
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
                        var attrItem = btn.closest('.attribute-item');
                        if (attrItem) {
                            if (confirm('Bạn có chắc muốn xóa thuộc tính này không?')) {
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

        // Xử lý chọn dịch vụ đơn trong form combo
        document.querySelectorAll('.combo-service-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const serviceId = this.value;
                const checkboxContainer = this.closest('.form-check');
                
                if (this.checked) {
                    // Tạo hidden input nếu chưa có
                    let serviceIdInput = checkboxContainer.querySelector('input[type="hidden"][name*="[service_id]"]');
                    if (!serviceIdInput) {
                        serviceIdInput = document.createElement('input');
                        serviceIdInput.type = 'hidden';
                        serviceIdInput.name = 'combo_items[' + serviceId + '][service_id]';
                        serviceIdInput.value = serviceId;
                        checkboxContainer.appendChild(serviceIdInput);
                    }
                    
                    // Tạo hidden input cho service_variant_id nếu chưa có
                    let variantIdInput = checkboxContainer.querySelector('input[type="hidden"][name*="[service_variant_id]"]');
                    if (!variantIdInput) {
                        variantIdInput = document.createElement('input');
                        variantIdInput.type = 'hidden';
                        variantIdInput.name = 'combo_items[' + serviceId + '][service_variant_id]';
                        variantIdInput.value = '';
                        checkboxContainer.appendChild(variantIdInput);
                    }
                } else {
                    // Xóa hidden inputs khi uncheck
                    const hiddenInputs = checkboxContainer.querySelectorAll('input[type="hidden"]');
                    hiddenInputs.forEach(input => {
                        if (input.name.includes('combo_items[' + serviceId)) {
                            input.remove();
                        }
                    });
                }
            });
        });

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
                        // Tạo hidden input service_id nếu chưa có
                        let hiddenInput = variantOptions.querySelector('input[type="hidden"][name*="[service_id]"]');
                        if (!hiddenInput) {
                            hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'combo_items[' + serviceId + '][service_id]';
                            hiddenInput.value = serviceId;
                            variantOptions.appendChild(hiddenInput);
                        }
                    } else {
                        variantOptions.style.display = 'none';
                        // Bỏ chọn tất cả biến thể
                        variantOptions.querySelectorAll('input[type="radio"]').forEach(radio => {
                            radio.checked = false;
                        });
                        // Xóa hidden input service_id
                        const hiddenInput = variantOptions.querySelector('input[type="hidden"][name*="[service_id]"]');
                        if (hiddenInput) {
                            hiddenInput.remove();
                        }
                    }
                }
            });
        });

        // Khởi tạo hidden inputs cho các dịch vụ đơn đã được chọn khi trang load
        document.querySelectorAll('.combo-service-checkbox:checked').forEach(checkbox => {
            checkbox.dispatchEvent(new Event('change'));
        });
        
        // Khởi tạo hiển thị cho các dịch vụ biến thể đã được chọn (khi có old input)
        document.querySelectorAll('.variant-service-checkbox:checked').forEach(checkbox => {
            checkbox.dispatchEvent(new Event('change'));
        });
    });
</script>
@endpush
