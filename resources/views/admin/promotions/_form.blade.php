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
    <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
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
            <p class="text-muted mb-0">Chưa có dịch vụ nào. Vui lòng tạo dịch vụ trước.</p>
        @endforelse
    </div>
    <small class="form-text text-muted">Chọn các dịch vụ mà khuyến mãi này sẽ áp dụng. Để trống nếu áp dụng cho tất cả dịch vụ.</small>
    @error('services')
        <div class="text-danger small">{{ $message }}</div>
    @enderror
</div>

