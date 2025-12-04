@php
    $statuses = $statuses ?? [];
@endphp

<div class="form-group">
    <label for="code">Mã khuyến mãi</label>
    <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror"
           value="{{ old('code', $promotion->code ?? '') }}" required>
    @error('code')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="name">Tên khuyến mãi</label>
    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $promotion->name ?? '') }}" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="description">Mô tả</label>
    <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $promotion->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="start_date">Ngày bắt đầu</label>
        <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror"
               value="{{ old('start_date', isset($promotion->start_date) ? $promotion->start_date->format('Y-m-d') : '') }}" required>
        @error('start_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-6">
        <label for="end_date">Ngày kết thúc</label>
        <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror"
               value="{{ old('end_date', isset($promotion->end_date) ? $promotion->end_date->format('Y-m-d') : '') }}">
        @error('end_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-group">
    <label for="status">Trạng thái</label>
    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
        @foreach($statuses as $value => $label)
            <option value="{{ $value }}" {{ old('status', $promotion->status ?? 'inactive') === $value ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>
    @error('status')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-row">
    <div class="form-group col-md-4">
        <label for="discount_type">Loại giảm giá</label>
        <select name="discount_type" id="discount_type" class="form-control @error('discount_type') is-invalid @enderror" required>
            @php
                $currentType = old('discount_type', $promotion->discount_type ?? 'percent');
            @endphp
            <option value="percent" {{ $currentType === 'percent' ? 'selected' : '' }}>Giảm theo %</option>
            <option value="amount" {{ $currentType === 'amount' ? 'selected' : '' }}>Giảm theo số tiền</option>
        </select>
        @error('discount_type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-4 js-discount-percent">
        <label for="discount_percent">% giảm</label>
        <input type="number" name="discount_percent" id="discount_percent" class="form-control @error('discount_percent') is-invalid @enderror"
               value="{{ old('discount_percent', $promotion->discount_percent ?? 0) }}" min="0" max="100">
        @error('discount_percent')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-4 js-discount-amount">
        <label for="discount_amount">Giảm tiền (VNĐ)</label>
        <input type="number" name="discount_amount" id="discount_amount" class="form-control @error('discount_amount') is-invalid @enderror"
               value="{{ old('discount_amount', $promotion->discount_amount ?? null) }}" min="0" step="1000">
        @error('discount_amount')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-4">
        <label for="apply_scope">Áp dụng theo</label>
        @php
            $currentScope = old('apply_scope', $promotion->apply_scope ?? 'service');
        @endphp
        <select name="apply_scope" id="apply_scope" class="form-control @error('apply_scope') is-invalid @enderror" required>
            <option value="service" {{ $currentScope === 'service' ? 'selected' : '' }}>Theo dịch vụ</option>
            <option value="order" {{ $currentScope === 'order' ? 'selected' : '' }}>Theo hóa đơn</option>
        </select>
        @error('apply_scope')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-4 js-min-order-field">
        <label for="min_order_amount">Hóa đơn tối thiểu (VNĐ)</label>
        <input type="number" name="min_order_amount" id="min_order_amount" class="form-control @error('min_order_amount') is-invalid @enderror"
               value="{{ old('min_order_amount', $promotion->min_order_amount ?? null) }}" min="0" step="1000">
        @error('min_order_amount')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-4 js-max-discount-field">
        <label for="max_discount_amount">Giảm tối đa (VNĐ)</label>
        <input type="number" name="max_discount_amount" id="max_discount_amount" class="form-control @error('max_discount_amount') is-invalid @enderror"
               value="{{ old('max_discount_amount', $promotion->max_discount_amount ?? null) }}" min="0" step="1000">
        @error('max_discount_amount')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-group">
    <label for="per_user_limit">Số lần tối đa mỗi tài khoản được dùng</label>
    <input type="number" name="per_user_limit" id="per_user_limit" class="form-control @error('per_user_limit') is-invalid @enderror"
           value="{{ old('per_user_limit', $promotion->per_user_limit ?? null) }}" min="1">
    @error('per_user_limit')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group js-service-selection">
    <label>Chọn dịch vụ áp dụng</label>
    
    <!-- Dịch vụ -->
    <div class="mb-3">
        <h6 class="font-weight-bold">Dịch vụ</h6>
        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
            @php
                $services = $services ?? [];
                $selectedServiceIds = $selectedServiceIds ?? [];
                $oldServices = old('services', []);
                $checkedServiceIds = !empty($oldServices) ? $oldServices : $selectedServiceIds;
            @endphp
            
            @forelse($services as $service)
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="services[]" 
                           value="{{ $service->id }}" 
                           id="service_{{ $service->id }}"
                           {{ in_array($service->id, $checkedServiceIds) ? 'checked' : '' }}>
                    <label class="form-check-label" for="service_{{ $service->id }}">
                        {{ $service->name }}
                        @if($service->category)
                            <small class="text-muted">({{ $service->category->name }})</small>
                        @endif
                    </label>
                </div>
            @empty
                <p class="text-muted mb-0">Chưa có dịch vụ nào.</p>
            @endforelse
        </div>
    </div>

    <!-- Combo -->
    <div class="mb-3">
        <h6 class="font-weight-bold">Combo</h6>
        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
            @php
                $combos = $combos ?? [];
                $selectedComboIds = $selectedComboIds ?? [];
                $oldCombos = old('combos', []);
                $checkedComboIds = !empty($oldCombos) ? $oldCombos : $selectedComboIds;
            @endphp
            
            @forelse($combos as $combo)
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="combos[]" 
                           value="{{ $combo->id }}" 
                           id="combo_{{ $combo->id }}"
                           {{ in_array($combo->id, $checkedComboIds) ? 'checked' : '' }}>
                    <label class="form-check-label" for="combo_{{ $combo->id }}">
                        {{ $combo->name }}
                        @if($combo->category)
                            <small class="text-muted">({{ $combo->category->name }})</small>
                        @endif
                        @if($combo->price)
                            <small class="text-muted"> - {{ number_format($combo->price, 0, ',', '.') }} đ</small>
                        @endif
                    </label>
                </div>
            @empty
                <p class="text-muted mb-0">Chưa có combo nào.</p>
            @endforelse
        </div>
    </div>

    <!-- Dịch vụ biến thể -->
    <div class="mb-3">
        <h6 class="font-weight-bold">Dịch vụ biến thể</h6>
        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
            @php
                $serviceVariants = $serviceVariants ?? [];
                $selectedVariantIds = $selectedVariantIds ?? [];
                $oldVariants = old('service_variants', []);
                $checkedVariantIds = !empty($oldVariants) ? $oldVariants : $selectedVariantIds;
            @endphp
            
            @forelse($serviceVariants as $variant)
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="service_variants[]" 
                           value="{{ $variant->id }}" 
                           id="variant_{{ $variant->id }}"
                           {{ in_array($variant->id, $checkedVariantIds) ? 'checked' : '' }}>
                    <label class="form-check-label" for="variant_{{ $variant->id }}">
                        {{ $variant->name }}
                        @if($variant->service)
                            <small class="text-muted">({{ $variant->service->name }})</small>
                        @endif
                        @if($variant->price)
                            <small class="text-muted"> - {{ number_format($variant->price, 0, ',', '.') }} đ</small>
                        @endif
                    </label>
                </div>
            @empty
                <p class="text-muted mb-0">Chưa có dịch vụ biến thể nào.</p>
            @endforelse
        </div>
    </div>

    <small class="form-text text-muted">Chọn các dịch vụ, combo hoặc dịch vụ biến thể mà khuyến mãi này sẽ áp dụng. Để trống nếu áp dụng cho tất cả.</small>
    @error('services')
        <div class="text-danger small">{{ $message }}</div>
    @enderror
    @error('combos')
        <div class="text-danger small">{{ $message }}</div>
    @enderror
    @error('service_variants')
        <div class="text-danger small">{{ $message }}</div>
    @enderror
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('discount_type');
    const percentGroup = document.querySelector('.js-discount-percent');
    const amountGroup = document.querySelector('.js-discount-amount');
    const percentInput = document.getElementById('discount_percent');
    const amountInput = document.getElementById('discount_amount');
    const maxDiscountGroup = document.querySelector('.js-max-discount-field');
    const maxDiscountInput = document.getElementById('max_discount_amount');

    const scopeSelect = document.getElementById('apply_scope');
    const minOrderGroup = document.querySelector('.js-min-order-field');
    const minOrderInput = document.getElementById('min_order_amount');
    const serviceSelectionGroup = document.querySelector('.js-service-selection');

    function toggleDiscountFields() {
        if (!typeSelect) return;
        const isPercent = typeSelect.value === 'percent';

        if (percentGroup) {
            percentGroup.style.display = isPercent ? 'block' : 'none';
        }
        if (amountGroup) {
            amountGroup.style.display = isPercent ? 'none' : 'block';
        }

        if (percentInput) percentInput.disabled = !isPercent;
        if (amountInput) amountInput.disabled = isPercent;

        // Giảm tối đa chỉ có ý nghĩa khi giảm theo %
        if (maxDiscountGroup) {
            maxDiscountGroup.style.display = isPercent ? 'block' : 'none';
        }
        if (maxDiscountInput) {
            maxDiscountInput.disabled = !isPercent;
            if (!isPercent) {
                maxDiscountInput.value = '';
            }
        }
    }

    function toggleScopeFields() {
        if (!scopeSelect || !minOrderGroup) return;
        const isOrder = scopeSelect.value === 'order';
        minOrderGroup.style.display = isOrder ? 'block' : 'none';
        if (minOrderInput) {
            minOrderInput.disabled = !isOrder;
            if (!isOrder) {
                minOrderInput.value = '';
            }
        }

        if (serviceSelectionGroup) {
            serviceSelectionGroup.style.display = isOrder ? 'none' : 'block';
            serviceSelectionGroup.querySelectorAll('input[type="checkbox"]').forEach((input) => {
                input.disabled = isOrder;
                if (isOrder) {
                    input.checked = false;
                }
            });
        }
    }

    typeSelect?.addEventListener('change', toggleDiscountFields);
    scopeSelect?.addEventListener('change', toggleScopeFields);

    toggleDiscountFields();
    toggleScopeFields();
});
</script>
@endpush

