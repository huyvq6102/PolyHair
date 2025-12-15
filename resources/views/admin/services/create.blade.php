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

<!-- Main Form with Common Fields -->
<form action="{{ route('admin.services.store') }}" method="POST" enctype="multipart/form-data" id="serviceForm">
    @csrf
    <input type="hidden" name="service_type" id="service_type_hidden" value="{{ old('service_type', '') }}">
    
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
                           value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group col-md-6">
                    <label for="service_type">Loại dịch vụ <span class="text-danger">*</span></label>
                    <select name="service_type" id="service_type" class="form-control @error('service_type') is-invalid @enderror" required>
                        <option value="">-- Chọn loại dịch vụ --</option>
                        <option value="single" {{ old('service_type') == 'single' ? 'selected' : '' }}>Dịch vụ đơn</option>
                        <option value="variant" {{ old('service_type') == 'variant' ? 'selected' : '' }}>Dịch vụ biến thể</option>
                        <option value="combo" {{ old('service_type') == 'combo' ? 'selected' : '' }}>Combo</option>
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
                    <label for="status">Trạng thái</label>
                    <select name="status" id="status" class="form-control">
                        <option value="Hoạt động" {{ old('status', 'Hoạt động') == 'Hoạt động' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="Vô hiệu hóa" {{ old('status') == 'Vô hiệu hóa' ? 'selected' : '' }}>Vô hiệu hóa</option>
                    </select>
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
                <label for="image">Hình ảnh</label>
                <div class="custom-file">
                    <input type="file" name="image" id="image" class="custom-file-input @error('image') is-invalid @enderror" accept="image/*" onchange="previewImage(this)">
                    <label class="custom-file-label" for="image" id="imageLabel">Chọn tệp</label>
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
    <div id="single-form" class="card shadow mb-4" style="display: none;">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin dịch vụ đơn</h6>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="base_price">Giá <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="base_price" id="base_price" class="form-control @error('base_price') is-invalid @enderror" 
                           value="{{ old('base_price') }}" required>
                    @error('base_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group col-md-6">
                    <label for="base_duration">Thời lượng (phút)</label>
                    <input type="number" name="base_duration" id="base_duration" class="form-control @error('base_duration') is-invalid @enderror" 
                           value="{{ old('base_duration') }}" min="0">
                    @error('base_duration')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Type-Specific Fields: Variant Service -->
    <div id="variant-form" class="card shadow mb-4" style="display: none;">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-layer-group"></i> Thông tin dịch vụ biến thể
            </h6>
            <button type="button" class="btn btn-sm btn-primary" id="addVariantBtn">
                <i class="fas fa-plus"></i> Thêm biến thể
            </button>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> <strong>Lưu ý:</strong> Mỗi biến thể có thể có giá và thời lượng riêng. Bạn có thể thêm nhiều thuộc tính cho mỗi biến thể.
            </div>
            <div id="variantsContainer" class="mt-3">
                <!-- Biến thể sẽ được thêm vào đây bằng JavaScript -->
            </div>
        </div>
    </div>

<!-- Template cho biến thể -->
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
    <div id="combo-form" class="card shadow mb-4" style="display: none;">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin combo</h6>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="combo_price">Giá combo <span class="text-danger">*</span></label>
                <input type="number" step="0.01" name="combo_price" id="combo_price" class="form-control @error('combo_price') is-invalid @enderror" 
                       value="{{ old('combo_price') }}" required>
                @error('combo_price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="combo_duration">Thời lượng (phút)</label>
                <input type="number" name="combo_duration" id="combo_duration" class="form-control @error('combo_duration') is-invalid @enderror" 
                       value="{{ old('combo_duration') }}" min="0">
                @error('combo_duration')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label>Chọn dịch vụ và biến thể <span class="text-danger">*</span></label>
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
                                       {{ in_array($singleService->id, old('service_ids', [])) ? 'checked' : '' }}>
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
                            <div class="mb-3 ml-3">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           id="variant_service_{{ $variantService->id }}" 
                                           class="form-check-input variant-service-checkbox" 
                                           data-service-id="{{ $variantService->id }}"
                                           {{ in_array($variantService->id, old('service_ids', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="variant_service_{{ $variantService->id }}" style="cursor: pointer;">
                                        <strong>{{ $variantService->name }}</strong> 
                                        ({{ $variantService->service_code ?? 'N/A' }})
                                        <span class="badge badge-info ml-2">{{ $variantService->serviceVariants->count() }} biến thể</span>
                                    </label>
                                </div>
                                <div class="ml-4 mt-2 variant-options" id="variants_{{ $variantService->id }}" style="display: none;">
                                    @foreach($variantService->serviceVariants as $variant)
                                        <div class="form-check mb-1">
                                            <input type="radio" 
                                                   name="combo_items[{{ $variantService->id }}][service_variant_id]" 
                                                   id="variant_{{ $variant->id }}" 
                                                   class="form-check-input" 
                                                   value="{{ $variant->id }}"
                                                   data-service-id="{{ $variantService->id }}"
                                                   {{ old("combo_items.{$variantService->id}.service_variant_id") == $variant->id ? 'checked' : '' }}>
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
                                    <!-- Hidden input chỉ được thêm khi checkbox được check (xử lý bằng JavaScript) -->
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

    <!-- Submit Buttons -->
    <div class="form-group mt-4">
        <div class="d-flex justify-content-start">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> Lưu dịch vụ
            </button>
        </div>
    </div>
</form>
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
            label.textContent = 'Chọn tệp';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const serviceTypeSelect = document.getElementById('service_type');
        const serviceTypeHidden = document.getElementById('service_type_hidden');
        const serviceForm = document.getElementById('serviceForm');
        const singleForm = document.getElementById('single-form');
        const variantForm = document.getElementById('variant-form');
        const comboFormDiv = document.getElementById('combo-form');
        const nameField = document.getElementById('name');
        const categoryField = document.getElementById('category_id');
        const descriptionField = document.getElementById('description');
        const imageField = document.getElementById('image');
        const statusField = document.getElementById('status');

        let variantIndex = 0;
        let attributeIndexes = {};

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
            if (variantForm) {
                removeRequiredFromForm(variantForm);
                variantForm.style.display = 'none';
            }
            if (comboFormDiv) {
                removeRequiredFromForm(comboFormDiv);
                comboFormDiv.style.display = 'none';
            }

            // Show appropriate form based on type and restore required
            if (type === 'single') {
                singleForm.style.display = 'block';
                addRequiredToForm(singleForm);
            } else if (type === 'variant') {
                variantForm.style.display = 'block';
                addRequiredToForm(variantForm);
                // Thêm biến thể đầu tiên khi hiển thị form (chỉ khi chưa có biến thể nào)
                setTimeout(function() {
                    const container = document.getElementById('variantsContainer');
                    if (container && container.querySelectorAll('.variant-item').length === 0) {
                        addVariant();
                    }
                }, 100);
            } else if (type === 'combo') {
                comboFormDiv.style.display = 'block';
                addRequiredToForm(comboFormDiv);
            }
        }

        // Handle form submission - rename fields based on service type
        serviceForm.addEventListener('submit', function(e) {
            const serviceType = serviceTypeSelect.value;
            
            // Validate service type is selected
            if (!serviceType) {
                e.preventDefault();
                alert('Vui lòng chọn loại dịch vụ!');
                return false;
            }
            
            // Remove required from all hidden forms to prevent browser validation errors
            if (singleForm && singleForm.style.display === 'none') {
                singleForm.querySelectorAll('[required]').forEach(function(field) {
                    field.removeAttribute('required');
                });
            }
            if (variantForm && variantForm.style.display === 'none') {
                variantForm.querySelectorAll('[required]').forEach(function(field) {
                    field.removeAttribute('required');
                });
            }
            if (comboFormDiv && comboFormDiv.style.display === 'none') {
                comboFormDiv.querySelectorAll('[required]').forEach(function(field) {
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
                if (imageField) {
                    imageField.name = 'combo_image';
                }
                
                // Rename 'status' to 'combo_status'
                if (statusField) {
                    statusField.name = 'combo_status';
                }
                
                // Rename 'description' to 'combo_description'
                if (descriptionField) {
                    descriptionField.name = 'combo_description';
                }
            }
        });

        serviceTypeSelect.addEventListener('change', function() {
            const selectedType = this.value;
            
            // Thêm hiệu ứng khi chọn loại dịch vụ
            if (selectedType) {
                // Scroll đến phần form tương ứng
                setTimeout(function() {
                    if (selectedType === 'single' && singleForm) {
                        singleForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    } else if (selectedType === 'variant' && variantForm) {
                        variantForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    } else if (selectedType === 'combo' && comboFormDiv) {
                        comboFormDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                }, 100);
            }
            
            showForm(selectedType);
        });

        // Show form based on old input or selected type
        const selectedType = serviceTypeSelect.value;
        if (selectedType) {
            showForm(selectedType);
        } else {
            // If no type selected, remove required from all hidden forms
            if (singleForm) {
                singleForm.querySelectorAll('[required]').forEach(function(field) {
                    field.removeAttribute('required');
                });
            }
            if (variantForm) {
                variantForm.querySelectorAll('[required]').forEach(function(field) {
                    field.removeAttribute('required');
                });
            }
            if (comboFormDiv) {
                comboFormDiv.querySelectorAll('[required]').forEach(function(field) {
                    field.removeAttribute('required');
                });
            }
        }

        // Xử lý chọn dịch vụ biến thể
        document.querySelectorAll('.variant-service-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const serviceId = this.getAttribute('data-service-id');
                const variantOptions = document.getElementById('variants_' + serviceId);
                if (variantOptions) {
                    // Tìm hoặc tạo hidden input cho service_id
                    let hiddenInput = variantOptions.querySelector('input[type="hidden"][name*="[service_id]"]');
                    
                    if (this.checked) {
                        variantOptions.style.display = 'block';
                        // Tự động chọn biến thể đầu tiên nếu chưa có biến thể nào được chọn
                        const firstVariant = variantOptions.querySelector('input[type="radio"]');
                        if (firstVariant && !variantOptions.querySelector('input[type="radio"]:checked')) {
                            firstVariant.checked = true;
                        }
                        // Thêm hidden input nếu chưa có
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
                        // Xóa hidden input
                        if (hiddenInput) {
                            hiddenInput.remove();
                        }
                    }
                }
            });
        });

        // Khởi tạo hiển thị cho các dịch vụ biến thể đã được chọn (khi có old input)
        document.querySelectorAll('.variant-service-checkbox:checked').forEach(checkbox => {
            checkbox.dispatchEvent(new Event('change'));
        });
        
        // Xử lý khi form submit - chỉ gửi các combo items đã được chọn (cho combo type)
        if (serviceForm && !serviceForm.hasAttribute('data-combo-submit-handler')) {
            serviceForm.setAttribute('data-combo-submit-handler', 'true');
            serviceForm.addEventListener('submit', function(e) {
                const serviceType = serviceTypeSelect.value;
                
                if (serviceType === 'combo') {
                    // Xóa tất cả hidden input của dịch vụ đơn không được check
                    document.querySelectorAll('.combo-service-checkbox').forEach(checkbox => {
                        if (!checkbox.checked) {
                            // Tìm và xóa hidden input service_variant_id tương ứng
                            const serviceId = checkbox.value;
                            const hiddenVariantInput = document.querySelector('input[type="hidden"][name="combo_items[' + serviceId + '][service_variant_id]"]');
                            if (hiddenVariantInput) {
                                hiddenVariantInput.remove();
                            }
                            // Disable checkbox để không gửi lên
                            checkbox.disabled = true;
                        }
                    });
                    
                    // Xóa tất cả hidden input và radio buttons của các dịch vụ biến thể không được check
                    document.querySelectorAll('.variant-service-checkbox').forEach(checkbox => {
                        if (!checkbox.checked) {
                            const serviceId = checkbox.getAttribute('data-service-id');
                            const variantOptions = document.getElementById('variants_' + serviceId);
                            if (variantOptions) {
                                // Xóa hidden input service_id
                                const hiddenInput = variantOptions.querySelector('input[type="hidden"][name*="[service_id]"]');
                                if (hiddenInput) {
                                    hiddenInput.remove();
                                }
                                // Xóa tất cả radio buttons (service_variant_id)
                                variantOptions.querySelectorAll('input[type="radio"]').forEach(radio => {
                                    radio.remove();
                                });
                            }
                        }
                    });
                }
            });
        }

        // Xử lý thêm biến thể
        function addVariant() {
            const template = document.getElementById('variantTemplate');
            const container = document.getElementById('variantsContainer');
            const variantHtml = template.innerHTML.replace(/__INDEX__/g, variantIndex);
            
            const variantDiv = document.createElement('div');
            variantDiv.innerHTML = variantHtml;
            variantDiv.querySelector('.variant-item').setAttribute('data-variant-index', variantIndex);
            variantDiv.querySelector('.variant-number').textContent = variantIndex + 1;
            
            container.appendChild(variantDiv);
            
            // Khởi tạo chỉ số thuộc tính cho biến thể này
            attributeIndexes[variantIndex] = 0;
            
            // Gắn sự kiện cho nút xóa biến thể
            const removeBtn = variantDiv.querySelector('.remove-variant-btn');
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
            
            // Gắn sự kiện cho nút thêm thuộc tính
            const addAttrBtn = variantDiv.querySelector('.add-attribute-btn');
            if (addAttrBtn) {
                // Lưu variantIndex vào closure
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
            if (attributeIndexes[variantIndex] === undefined) {
                attributeIndexes[variantIndex] = 0;
            }
            
            const template = document.getElementById('attributeTemplate');
            if (!template) {
                console.error('Template attributeTemplate not found');
                return;
            }
            
            const container = document.querySelector(`.attributes-container[data-variant-index="${variantIndex}"]`);
            if (!container) {
                console.error('Container not found for variant index:', variantIndex);
                return;
            }
            
            const attrIndex = attributeIndexes[variantIndex];
            
            const attrHtml = template.innerHTML
                .replace(/__VARIANT_INDEX__/g, variantIndex)
                .replace(/__ATTR_INDEX__/g, attrIndex);
            
            const attrDiv = document.createElement('div');
            attrDiv.innerHTML = attrHtml;
            const attrItem = attrDiv.querySelector('.attribute-item');
            if (attrItem) {
                attrItem.setAttribute('data-attribute-index', attrIndex);
            }
            
            container.appendChild(attrDiv);
            
            // Gắn sự kiện cho nút xóa thuộc tính
            const removeAttrBtn = attrDiv.querySelector('.remove-attribute-btn');
            if (removeAttrBtn) {
                removeAttrBtn.addEventListener('click', function() {
                    if (confirm('Bạn có chắc muốn xóa thuộc tính này không?')) {
                        attrDiv.remove();
                    }
                });
            }
            
            attributeIndexes[variantIndex]++;
        }

        // Cập nhật số thứ tự biến thể
        function updateVariantNumbers() {
            const variants = document.querySelectorAll('.variant-item');
            variants.forEach((variant, index) => {
                variant.querySelector('.variant-number').textContent = index + 1;
            });
        }

        // Gắn sự kiện cho nút thêm biến thể
        const addVariantBtn = document.getElementById('addVariantBtn');
        if (addVariantBtn) {
            addVariantBtn.addEventListener('click', addVariant);
        }

    });
</script>
@endpush

