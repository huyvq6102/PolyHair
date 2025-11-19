@php
    $service = $service ?? null;
    $variantOldInput = old('variants', []);
    $comboOldInput = old('combos', []);

    $variantFormData = collect($variantOldInput)->mapWithKeys(function ($variant, $uid) {
        $attributes = collect($variant['attributes'] ?? [])->mapWithKeys(function ($attribute, $attrKey) {
            $key = is_string($attrKey) ? $attrKey : ('attr_' . uniqid());
            return [$key => [
                'key' => $key,
                'name' => $attribute['name'] ?? '',
                'value' => $attribute['value'] ?? '',
            ]];
        });

        if ($attributes->isEmpty()) {
            $key = 'attr_' . uniqid();
            $attributes = collect([$key => ['key' => $key, 'name' => '', 'value' => '']]);
        }

        $variant['uid'] = $uid;
        $variant['attributes'] = $attributes;

        return [$uid => $variant];
    });

    if ($variantFormData->isEmpty() && $service) {
        $variantFormData = $service->serviceVariants->mapWithKeys(function ($variant) {
            $uid = 'variant_' . $variant->id;
            $attributes = $variant->variantAttributes->mapWithKeys(function ($attribute) {
                $key = 'attr_' . $attribute->id;
                return [$key => [
                    'key' => $key,
                    'name' => $attribute->attribute_name,
                    'value' => $attribute->attribute_value,
                ]];
            });

            if ($attributes->isEmpty()) {
                $key = 'attr_' . uniqid();
                $attributes = collect([$key => ['key' => $key, 'name' => '', 'value' => '']]);
            }

            return [$uid => [
                'uid' => $uid,
                'id' => $variant->id,
                'name' => $variant->name,
                'price' => $variant->price,
                'duration' => $variant->duration,
                'is_default' => $variant->is_default,
                'is_active' => $variant->is_active,
                'notes' => $variant->notes,
                'attributes' => $attributes,
            ]];
        });
    }

    if ($variantFormData->isEmpty()) {
        $uid = 'variant_' . uniqid();
        $attrKey = 'attr_' . uniqid();
        $variantFormData = collect([$uid => [
            'uid' => $uid,
            'attributes' => collect([$attrKey => ['key' => $attrKey, 'name' => '', 'value' => '']]),
        ]]);
    }

    $comboFormData = collect($comboOldInput)->mapWithKeys(function ($combo, $key) {
        return [$key => [
            'key' => $key,
            'id' => $combo['id'] ?? null,
            'name' => $combo['name'] ?? '',
            'slug' => $combo['slug'] ?? '',
            'description' => $combo['description'] ?? '',
            'price' => $combo['price'] ?? null,
            'status' => $combo['status'] ?? 'Hoạt động',
            'sort_order' => $combo['sort_order'] ?? 0,
            'variant_uids' => array_values($combo['variant_uids'] ?? []),
        ]];
    });

    if ($comboFormData->isEmpty() && $service) {
        $comboFormData = $service->ownedCombos->mapWithKeys(function ($combo) {
            $key = 'combo_' . $combo->id;
            $variantUids = $combo->comboItems->pluck('service_variant_id')->map(function ($id) {
                return 'variant_' . $id;
            })->values()->all();

            return [$key => [
                'key' => $key,
                'id' => $combo->id,
                'name' => $combo->name,
                'slug' => $combo->slug,
                'description' => $combo->description,
                'price' => $combo->price,
                'status' => $combo->status,
                'sort_order' => $combo->sort_order,
                'variant_uids' => $variantUids,
            ]];
        });
    }
@endphp

@csrf
@if(isset($service) && $service?->id)
    @method('PUT')
@endif

<div class="form-group">
    <label for="name">Tên dịch vụ <span class="text-danger">*</span></label>
    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $service->name ?? '') }}" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="category_id">Danh mục <span class="text-danger">*</span></label>
        <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
            <option value="">-- Chọn danh mục --</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ old('category_id', $service->category_id ?? '') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('category_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-3">
        <label for="base_price">Giá cơ bản</label>
        <input type="number" step="0.01" name="base_price" id="base_price" class="form-control @error('base_price') is-invalid @enderror"
               value="{{ old('base_price', $service->base_price ?? '') }}">
        @error('base_price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-3">
        <label for="base_duration">Thời lượng cơ bản (phút)</label>
        <input type="number" name="base_duration" id="base_duration" class="form-control @error('base_duration') is-invalid @enderror"
               value="{{ old('base_duration', $service->base_duration ?? '') }}">
        @error('base_duration')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-group col-md-4 pl-0">
    <label for="status">Trạng thái</label>
    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
        <option value="Hoạt động" {{ old('status', $service->status ?? 'Hoạt động') == 'Hoạt động' ? 'selected' : '' }}>Hoạt động</option>
        <option value="Vô hiệu hóa" {{ old('status', $service->status ?? '') == 'Vô hiệu hóa' ? 'selected' : '' }}>Vô hiệu hóa</option>
    </select>
    @error('status')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="description">Mô tả</label>
    <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $service->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="image">Hình ảnh</label>
    @if(isset($service) && $service?->image)
        <div class="mb-2">
            <img src="{{ asset('legacy/images/products/' . $service->image) }}" alt="{{ $service->name }}" width="120" class="img-thumbnail">
        </div>
    @endif
    <input type="file" class="form-control-file border @error('image') is-invalid @enderror" id="image" name="image" accept="image/*" {{ isset($service) ? '' : 'required' }}>
    <small class="form-text text-muted">Định dạng: JPG, PNG, GIF (tối đa 2MB)</small>
    @error('image')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

<hr>
<h5 class="text-primary">Biến thể dịch vụ</h5>
<p class="text-muted mb-3">Mỗi biến thể có thể thêm nhiều thuộc tính (ví dụ: "Loại tóc: Dài" hoặc "Gói: VIP").</p>

<div id="variantList">
    @foreach($variantFormData as $variantKey => $variant)
        @php
            $uid = $variant['uid'];
            /** @var \Illuminate\Support\Collection $attributes */
            $attributes = $variant['attributes'] ?? collect();
        @endphp
        <div class="variant-card border rounded p-3 mb-3" data-variant-uid="{{ $uid }}" data-variant-key="{{ $variantKey }}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <strong>Biến thể</strong>
                <button type="button" class="btn btn-sm btn-danger remove-variant" {{ $loop->count === 1 ? 'disabled' : '' }}>
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Tên biến thể <span class="text-danger">*</span></label>
                    <input type="text" class="form-control variant-name-input @error("variants.$variantKey.name") is-invalid @enderror"
                           name="variants[{{ $variantKey }}][name]"
                           value="{{ old("variants.$variantKey.name", $variant['name'] ?? '') }}" required>
                    @error("variants.$variantKey.name")
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group col-md-2">
                    <label>Giá (VNĐ) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control @error("variants.$variantKey.price") is-invalid @enderror"
                           name="variants[{{ $variantKey }}][price]"
                           value="{{ old("variants.$variantKey.price", $variant['price'] ?? '') }}" required>
                    @error("variants.$variantKey.price")
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group col-md-2">
                    <label>Thời lượng (phút) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error("variants.$variantKey.duration") is-invalid @enderror"
                           name="variants[{{ $variantKey }}][duration]"
                           value="{{ old("variants.$variantKey.duration", $variant['duration'] ?? '') }}" required>
                    @error("variants.$variantKey.duration")
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group col-md-2">
                    <label>Mặc định</label>
                    <div class="custom-control custom-switch mt-2">
                        <input type="hidden" name="variants[{{ $variantKey }}][is_default]" value="0">
                        <input type="checkbox" class="custom-control-input"
                               id="variant_default_{{ $uid }}"
                               name="variants[{{ $variantKey }}][is_default]"
                               value="1"
                               {{ old("variants.$variantKey.is_default", $variant['is_default'] ?? false) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="variant_default_{{ $uid }}">Mặc định</label>
                    </div>
                </div>
                <div class="form-group col-md-2">
                    <label>Kích hoạt</label>
                    <div class="custom-control custom-switch mt-2">
                        <input type="hidden" name="variants[{{ $variantKey }}][is_active]" value="0">
                        <input type="checkbox" class="custom-control-input"
                               id="variant_active_{{ $uid }}"
                               name="variants[{{ $variantKey }}][is_active]"
                               value="1"
                               {{ old("variants.$variantKey.is_active", $variant['is_active'] ?? true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="variant_active_{{ $uid }}">Bật</label>
                    </div>
                </div>
                <div class="form-group col-md-2">
                    <label>Ghi chú</label>
                    <input type="text" class="form-control"
                           name="variants[{{ $variantKey }}][notes]"
                           value="{{ old("variants.$variantKey.notes", $variant['notes'] ?? '') }}">
                </div>
            </div>

            <div class="variant-attributes-wrapper">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="font-weight-bold">Thuộc tính biến thể</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary add-attribute-btn" data-variant-key="{{ $variantKey }}">
                        <i class="fas fa-plus"></i> Thêm thuộc tính
                    </button>
                </div>
                <div class="variant-attributes border rounded p-2" data-variant-key="{{ $variantKey }}">
                    @foreach($attributes as $attributeKey => $attribute)
                        <div class="attribute-row row align-items-center mb-2" data-attribute-key="{{ $attribute['key'] }}">
                            <div class="col-md-5">
                                <input type="text" class="form-control"
                                       name="variants[{{ $variantKey }}][attributes][{{ $attribute['key'] }}][name]"
                                       placeholder="Thuộc tính"
                                       value="{{ old("variants.$variantKey.attributes.{$attribute['key']}.name", $attribute['name']) }}">
                            </div>
                            <div class="col-md-5">
                                <input type="text" class="form-control"
                                       name="variants[{{ $variantKey }}][attributes][{{ $attribute['key'] }}][value]"
                                       placeholder="Giá trị"
                                       value="{{ old("variants.$variantKey.attributes.{$attribute['key']}.value", $attribute['value']) }}">
                            </div>
                            <div class="col-md-2 text-right">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-attribute-btn">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <input type="hidden" name="variants[{{ $variantKey }}][id]" value="{{ $variant['id'] ?? '' }}">
        </div>
    @endforeach
</div>

<button type="button" class="btn btn-outline-primary mb-4" id="addVariantBtn">
    <i class="fas fa-plus"></i> Thêm biến thể
</button>

<hr>
<h5 class="text-primary">Combo dịch vụ</h5>
<p class="text-muted mb-3">Chọn các biến thể phía trên để ghép thành combo. Người dùng chỉ cần đánh dấu checkbox để thêm dịch vụ con.</p>

<div id="comboList">
    @forelse($comboFormData as $comboKey => $combo)
        @php
            $selected = collect($combo['variant_uids'] ?? []);
        @endphp
        <div class="combo-card border rounded p-3 mb-3" data-combo-key="{{ $comboKey }}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <strong>Combo</strong>
                <button type="button" class="btn btn-sm btn-danger remove-combo">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Tên combo</label>
                    <input type="text" class="form-control @error("combos.$comboKey.name") is-invalid @enderror"
                           name="combos[{{ $comboKey }}][name]"
                           value="{{ old("combos.$comboKey.name", $combo['name'] ?? '') }}">
                    @error("combos.$comboKey.name")
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group col-md-3">
                    <label>Slug</label>
                    <input type="text" class="form-control"
                           name="combos[{{ $comboKey }}][slug]"
                           value="{{ old("combos.$comboKey.slug", $combo['slug'] ?? '') }}">
                </div>
                <div class="form-group col-md-2">
                    <label>Giá combo</label>
                    <input type="number" step="0.01" class="form-control"
                           name="combos[{{ $comboKey }}][price]"
                           value="{{ old("combos.$comboKey.price", $combo['price'] ?? '') }}">
                </div>
                <div class="form-group col-md-2">
                    <label>Thứ tự</label>
                    <input type="number" class="form-control"
                           name="combos[{{ $comboKey }}][sort_order]"
                           value="{{ old("combos.$comboKey.sort_order", $combo['sort_order'] ?? 0) }}">
                </div>
                <div class="form-group col-md-1">
                    <label>Trạng thái</label>
                    <select class="form-control" name="combos[{{ $comboKey }}][status]">
                        <option value="Hoạt động" {{ old("combos.$comboKey.status", $combo['status'] ?? 'Hoạt động') == 'Hoạt động' ? 'selected' : '' }}>Bật</option>
                        <option value="Vô hiệu hóa" {{ old("combos.$comboKey.status", $combo['status'] ?? '') == 'Vô hiệu hóa' ? 'selected' : '' }}>Tắt</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Mô tả</label>
                <textarea class="form-control" rows="2" name="combos[{{ $comboKey }}][description]">{{ old("combos.$comboKey.description", $combo['description'] ?? '') }}</textarea>
            </div>
            <div class="variant-checkboxes combo-variant-checkboxes border rounded p-3" data-combo-key="{{ $comboKey }}">
                @if($variantFormData->isEmpty())
                    <p class="text-muted mb-0">Hãy thêm ít nhất một biến thể trước khi tạo combo.</p>
                @else
                    @foreach($variantFormData as $variantOptionKey => $variantOption)
                        <div class="form-check form-check-inline mr-3 mb-2">
                            <input type="checkbox"
                                   class="form-check-input"
                                   id="{{ $comboKey }}_{{ $variantOptionKey }}"
                                   name="combos[{{ $comboKey }}][variant_uids][]"
                                   value="{{ $variantOptionKey }}"
                                   {{ $selected->contains($variantOptionKey) ? 'checked' : '' }}>
                            <label class="form-check-label" for="{{ $comboKey }}_{{ $variantOptionKey }}">
                                {{ $variantOption['name'] ?? 'Biến thể' }}
                            </label>
                        </div>
                    @endforeach
                @endif
            </div>
            <input type="hidden" name="combos[{{ $comboKey }}][id]" value="{{ $combo['id'] ?? '' }}">
        </div>
    @empty
        <p class="text-muted combo-empty-state">Chưa có combo nào. Nhấn "Thêm combo" để tạo mới.</p>
    @endforelse
</div>

<button type="button" class="btn btn-outline-primary mb-4" id="addComboBtn">
    <i class="fas fa-layer-group"></i> Thêm combo
</button>

<div class="form-group">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> Lưu dịch vụ
    </button>
    <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">Hủy</a>
</div>

<template id="variant-template">
    <div class="variant-card border rounded p-3 mb-3" data-variant-uid="__UID__" data-variant-key="__KEY__">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <strong>Biến thể</strong>
            <button type="button" class="btn btn-sm btn-danger remove-variant">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Tên biến thể <span class="text-danger">*</span></label>
                <input type="text" class="form-control variant-name-input"
                       name="variants[__KEY__][name]" required>
            </div>
            <div class="form-group col-md-2">
                <label>Giá (VNĐ) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control"
                       name="variants[__KEY__][price]" required>
            </div>
            <div class="form-group col-md-2">
                <label>Thời lượng (phút) <span class="text-danger">*</span></label>
                <input type="number" class="form-control"
                       name="variants[__KEY__][duration]" required>
            </div>
            <div class="form-group col-md-2">
                <label>Mặc định</label>
                <div class="custom-control custom-switch mt-2">
                    <input type="hidden" name="variants[__KEY__][is_default]" value="0">
                    <input type="checkbox" class="custom-control-input"
                           id="variant_default___UID__"
                           name="variants[__KEY__][is_default]" value="1">
                    <label class="custom-control-label" for="variant_default___UID__">Mặc định</label>
                </div>
            </div>
            <div class="form-group col-md-2">
                <label>Kích hoạt</label>
                <div class="custom-control custom-switch mt-2">
                    <input type="hidden" name="variants[__KEY__][is_active]" value="0">
                    <input type="checkbox" class="custom-control-input"
                           id="variant_active___UID__"
                           name="variants[__KEY__][is_active]" value="1" checked>
                    <label class="custom-control-label" for="variant_active___UID__">Bật</label>
                </div>
            </div>
            <div class="form-group col-md-2">
                <label>Ghi chú</label>
                <input type="text" class="form-control"
                       name="variants[__KEY__][notes]">
            </div>
        </div>
        <div class="variant-attributes-wrapper">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="font-weight-bold">Thuộc tính biến thể</span>
                <button type="button" class="btn btn-sm btn-outline-secondary add-attribute-btn" data-variant-key="__KEY__">
                    <i class="fas fa-plus"></i> Thêm thuộc tính
                </button>
            </div>
            <div class="variant-attributes border rounded p-2" data-variant-key="__KEY__">
            </div>
        </div>
    </div>
</template>

<template id="combo-template">
    <div class="combo-card border rounded p-3 mb-3" data-combo-key="__KEY__">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <strong>Combo</strong>
            <button type="button" class="btn btn-sm btn-danger remove-combo">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Tên combo</label>
                <input type="text" class="form-control" name="combos[__KEY__][name]">
            </div>
            <div class="form-group col-md-3">
                <label>Slug</label>
                <input type="text" class="form-control" name="combos[__KEY__][slug]">
            </div>
            <div class="form-group col-md-2">
                <label>Giá combo</label>
                <input type="number" step="0.01" class="form-control" name="combos[__KEY__][price]">
            </div>
            <div class="form-group col-md-2">
                <label>Thứ tự</label>
                <input type="number" class="form-control" name="combos[__KEY__][sort_order]" value="0">
            </div>
            <div class="form-group col-md-1">
                <label>Trạng thái</label>
                <select class="form-control" name="combos[__KEY__][status]">
                    <option value="Hoạt động" selected>Bật</option>
                    <option value="Vô hiệu hóa">Tắt</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label>Mô tả</label>
            <textarea class="form-control" rows="2" name="combos[__KEY__][description]"></textarea>
        </div>
        <div class="variant-checkboxes combo-variant-checkboxes border rounded p-3" data-combo-key="__KEY__">
            <p class="text-muted mb-0">Hãy thêm ít nhất một biến thể trước khi chọn combo.</p>
        </div>
    </div>
</template>

<template id="attribute-template">
    <div class="attribute-row row align-items-center mb-2" data-attribute-key="__ATTR_KEY__">
        <div class="col-md-5">
            <input type="text" class="form-control attribute-name-input"
                   name="variants[__VARIANT_KEY__][attributes][__ATTR_KEY__][name]" placeholder="Thuộc tính">
        </div>
        <div class="col-md-5">
            <input type="text" class="form-control attribute-value-input"
                   name="variants[__VARIANT_KEY__][attributes][__ATTR_KEY__][value]" placeholder="Giá trị">
        </div>
        <div class="col-md-2 text-right">
            <button type="button" class="btn btn-sm btn-outline-danger remove-attribute-btn">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>
</template>

@push('scripts')
<script>
    (function() {
        const variantList = document.getElementById('variantList');
        const comboList = document.getElementById('comboList');
        const variantTemplate = document.getElementById('variant-template').innerHTML;
        const comboTemplate = document.getElementById('combo-template').innerHTML;
        const attributeTemplate = document.getElementById('attribute-template').innerHTML;

        const generateKey = (prefix) => `${prefix}_${Date.now()}_${Math.floor(Math.random() * 1000)}`;

        const ensureVariantRemovalState = () => {
            const cards = variantList.querySelectorAll('.variant-card');
            cards.forEach(card => {
                const removeButton = card.querySelector('.remove-variant');
                if (removeButton) {
                    removeButton.disabled = cards.length === 1;
                }
            });
        };

        const getVariantDefinitions = () => {
            return Array.from(variantList.querySelectorAll('.variant-card')).map(card => {
                const uid = card.dataset.variantUid;
                const key = card.dataset.variantKey;
                const nameInput = card.querySelector('.variant-name-input');
                return {
                    uid,
                    key,
                    label: (nameInput?.value?.trim()) || 'Biến thể chưa đặt tên',
                };
            });
        };

        const refreshComboCheckboxes = () => {
            const variants = getVariantDefinitions();
            comboList.querySelectorAll('.combo-card').forEach(card => {
                const comboKey = card.dataset.comboKey;
                const container = card.querySelector('.combo-variant-checkboxes');
                const previousChecked = new Set(
                    Array.from(container.querySelectorAll('input[type="checkbox"]:checked')).map(input => input.value)
                );
                container.innerHTML = '';

                if (!variants.length) {
                    container.innerHTML = '<p class="text-muted mb-0">Hãy thêm biến thể trước khi chọn combo.</p>';
                    return;
                }

                variants.forEach(variant => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'form-check form-check-inline mr-3 mb-2';

                    const input = document.createElement('input');
                    input.type = 'checkbox';
                    input.className = 'form-check-input';
                    input.name = `combos[${comboKey}][variant_uids][]`;
                    input.value = variant.key;
                    input.id = `${comboKey}_${variant.key}`;
                    input.checked = previousChecked.has(variant.key);

                    const label = document.createElement('label');
                    label.className = 'form-check-label';
                    label.setAttribute('for', input.id);
                    label.textContent = variant.label;

                    wrapper.appendChild(input);
                    wrapper.appendChild(label);
                    container.appendChild(wrapper);
                });
            });
        };

        const addAttributeRow = (variantKey, container, values = { name: '', value: '' }) => {
            const attrKey = generateKey('attr');
            const html = attributeTemplate
                .replace(/__VARIANT_KEY__/g, variantKey)
                .replace(/__ATTR_KEY__/g, attrKey);
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            const row = wrapper.firstElementChild;
            container.appendChild(row);

            row.querySelector('.attribute-name-input').value = values.name || '';
            row.querySelector('.attribute-value-input').value = values.value || '';
        };

        document.getElementById('addVariantBtn').addEventListener('click', () => {
            const variantKey = generateKey('variant');
            const uid = variantKey;
            const html = variantTemplate
                .replace(/__KEY__/g, variantKey)
                .replace(/__UID__/g, uid);
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            const card = wrapper.firstElementChild;
            variantList.appendChild(card);

            const attributeContainer = card.querySelector('.variant-attributes');
            addAttributeRow(variantKey, attributeContainer);

            ensureVariantRemovalState();
            refreshComboCheckboxes();
        });

        variantList.addEventListener('click', (event) => {
            if (event.target.closest('.remove-variant')) {
                const card = event.target.closest('.variant-card');
                card.remove();
                ensureVariantRemovalState();
                refreshComboCheckboxes();
            }

            if (event.target.closest('.add-attribute-btn')) {
                const button = event.target.closest('.add-attribute-btn');
                const variantKey = button.dataset.variantKey;
                const container = variantList.querySelector(`.variant-attributes[data-variant-key="${variantKey}"]`);
                if (container) {
                    addAttributeRow(variantKey, container);
                }
            }

            if (event.target.closest('.remove-attribute-btn')) {
                const row = event.target.closest('.attribute-row');
                const container = row?.parentElement;
                const rows = container?.querySelectorAll('.attribute-row');
                if (rows && rows.length > 1) {
                    row.remove();
                } else if (container) {
                    row.querySelectorAll('input').forEach(input => (input.value = ''));
                }
            }
        });

        variantList.addEventListener('input', (event) => {
            if (event.target.classList.contains('variant-name-input')) {
                refreshComboCheckboxes();
            }
        });

        document.getElementById('addComboBtn').addEventListener('click', () => {
            const comboKey = generateKey('combo');
            const html = comboTemplate.replace(/__KEY__/g, comboKey);
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            const card = wrapper.firstElementChild;
            comboList.appendChild(card);
            const emptyState = comboList.querySelector('.combo-empty-state');
            if (emptyState) {
                emptyState.remove();
            }
            refreshComboCheckboxes();
        });

        comboList.addEventListener('click', (event) => {
            if (event.target.closest('.remove-combo')) {
                event.target.closest('.combo-card').remove();
                if (!comboList.querySelector('.combo-card')) {
                    const emptyState = document.createElement('p');
                    emptyState.className = 'text-muted combo-empty-state';
                    emptyState.textContent = 'Chưa có combo nào. Nhấn "Thêm combo" để tạo mới.';
                    comboList.appendChild(emptyState);
                }
            }
        });

        document.querySelectorAll('.variant-attributes').forEach(container => {
            const variantKey = container.dataset.variantKey;
            if (!container.querySelector('.attribute-row')) {
                addAttributeRow(variantKey, container);
            }
        });

        ensureVariantRemovalState();
        refreshComboCheckboxes();
    })();
</script>
@endpush