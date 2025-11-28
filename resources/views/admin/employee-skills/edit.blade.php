@extends('admin.layouts.app')

@section('title', 'Cập nhật chuyên môn')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Cập nhật chuyên môn - {{ $employee->user->name ?? 'N/A' }}</h1>
    <a href="{{ route('admin.employee-skills.index') }}" class="btn btn-secondary">
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
                <label for="services">Dịch vụ (Chuyên môn)</label>
                <select name="services[]" id="services" class="form-control select2 @error('services') is-invalid @enderror" multiple data-placeholder="Chọn dịch vụ">
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" {{ in_array($service->id, old('services', $employee->services->pluck('id')->toArray())) ? 'selected' : '' }}>
                            {{ $service->name }}
                        </option>
                    @endforeach
                </select>
                @error('services')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Chọn các dịch vụ mà nhân viên này có thể thực hiện.</small>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
                <a href="{{ route('admin.employee-skills.index') }}" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    if ($.fn.select2) {
        $('#services').select2({
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

