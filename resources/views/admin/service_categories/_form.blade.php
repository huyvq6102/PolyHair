@csrf
@if(isset($category))
    @method('PUT')
@endif

<div class="form-group">
    <label for="name">Tên danh mục <span class="text-danger">*</span></label>
    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $category->name ?? '') }}" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="slug">Slug</label>
    <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror"
           value="{{ old('slug', $category->slug ?? '') }}" placeholder="Tự động tạo nếu bỏ trống">
    @error('slug')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="description">Mô tả</label>
    <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $category->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="sort_order">Thứ tự</label>
        <input type="number" name="sort_order" id="sort_order" min="0" class="form-control @error('sort_order') is-invalid @enderror"
               value="{{ old('sort_order', $category->sort_order ?? 0) }}">
        @error('sort_order')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-6">
        <label for="is_active">Trạng thái</label>
        <select name="is_active" id="is_active" class="form-control @error('is_active') is-invalid @enderror">
            <option value="1" {{ old('is_active', ($category->is_active ?? true)) ? 'selected' : '' }}>Hoạt động</option>
            <option value="0" {{ old('is_active', ($category->is_active ?? true)) ? '' : 'selected' }}>Ẩn</option>
        </select>
        @error('is_active')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-group">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> {{ isset($category) ? 'Cập nhật' : 'Tạo mới' }}
    </button>
    <a href="{{ route('admin.service-categories.index') }}" class="btn btn-secondary">Hủy</a>
</div>

