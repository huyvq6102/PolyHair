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
    <div class="form-group col-md-4">
        <label for="discount_percent">% giảm</label>
        <input type="number" name="discount_percent" id="discount_percent" class="form-control @error('discount_percent') is-invalid @enderror"
               value="{{ old('discount_percent', $promotion->discount_percent ?? 0) }}" min="0" max="100" required>
        @error('discount_percent')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-4">
        <label for="start_date">Ngày bắt đầu</label>
        <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror"
               value="{{ old('start_date', isset($promotion->start_date) ? $promotion->start_date->format('Y-m-d') : '') }}" required>
        @error('start_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-4">
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

<div class="form-group">
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

