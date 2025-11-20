@extends('admin.layouts.app')

@section('title', 'Cập nhật chuyên môn')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Cập nhật chuyên môn - {{ $employee->user->name ?? 'N/A' }}</h1>
    <a href="{{ route('admin.skills.index', ['tab' => 'employees']) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Thông tin nhân viên</h6>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Họ tên:</strong> {{ $employee->user->name ?? 'N/A' }}
            </div>
            <div class="col-md-6">
                <strong>Vị trí:</strong> {{ $employee->position ?? 'N/A' }}
            </div>
        </div>

        <form action="{{ route('admin.employee-skills.update', $employee->id) }}" method="POST" class="needs-validation" novalidate>
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="skills">Chuyên môn hiện có</label>
                <select name="skills[]" id="skills" class="form-control select2 @error('skills') is-invalid @enderror" multiple data-placeholder="Chọn chuyên môn">
                    @foreach($skills as $skill)
                        <option value="{{ $skill->id }}" {{ in_array($skill->id, old('skills', $employee->skills->pluck('id')->toArray())) ? 'selected' : '' }}>
                            {{ $skill->name }}
                        </option>
                    @endforeach
                </select>
                @error('skills')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Giữ Ctrl (Windows) hoặc Command (Mac) để chọn nhiều chuyên môn.</small>
            </div>

            <div class="form-group">
                <label for="new_skills">Thêm chuyên môn mới (ngăn cách bằng dấu phẩy hoặc xuống dòng)</label>
                <textarea name="new_skills" id="new_skills" rows="3" class="form-control @error('new_skills') is-invalid @enderror" placeholder="Ví dụ: Cắt fade, Uốn tóc nam">{{ old('new_skills') }}</textarea>
                @error('new_skills')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
                <a href="{{ route('admin.skills.index', ['tab' => 'employees']) }}" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    if ($.fn.select2) {
        $('#skills').select2({
            width: '100%'
        });
    }
});

(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>
@endpush

