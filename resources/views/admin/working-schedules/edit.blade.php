@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa lịch nhân viên')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Chỉnh sửa lịch nhân viên</h1>
    <a href="{{ route('admin.working-schedules.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Thông tin lịch</h6>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Nhân viên:</strong> {{ $schedule->employee->user->name ?? 'N/A' }}
            </div>
            <div class="col-md-6">
                <strong>Vị trí:</strong> {{ $schedule->employee->position ?? 'N/A' }}
            </div>
        </div>

        <form action="{{ route('admin.working-schedules.update', $schedule->id) }}" method="POST" class="needs-validation" novalidate onsubmit="return confirm('Xác nhận cập nhật lịch nhân viên?');">
            @csrf
            @method('PUT')

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="employee_ids">Nhân viên <span class="text-danger">*</span></label>
                    <select name="employee_ids[]" id="employee_ids" class="form-control select2-multiple @error('employee_ids') is-invalid @enderror @error('employee_ids.*') is-invalid @enderror" multiple required>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ (old('employee_ids') && in_array($employee->id, old('employee_ids'))) || $schedule->employee_id == $employee->id ? 'selected' : '' }}>
                                {{ $employee->user->name ?? 'N/A' }} - {{ $employee->position ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                    @error('employee_ids')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @elseif($errors->has('employee_ids.*'))
                        <div class="invalid-feedback d-block">{{ $errors->first('employee_ids.*') }}</div>
                    @else
                        <div class="invalid-feedback">Vui lòng chọn ít nhất một nhân viên</div>
                    @enderror
                    <small class="form-text text-muted">Bạn có thể chọn nhiều nhân viên. Hệ thống sẽ tạo lịch cho tất cả nhân viên đã chọn.</small>
                </div>
                <div class="form-group col-md-6">
                    <label for="work_date">Ngày làm việc <span class="text-danger">*</span></label>
                    <input type="date" name="work_date" id="work_date" value="{{ old('work_date', optional($schedule->work_date)->format('Y-m-d')) }}" class="form-control @error('work_date') is-invalid @enderror" required>
                    @error('work_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="invalid-feedback">Vui lòng chọn ngày làm việc</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="shift_id">Ca làm việc <span class="text-danger">*</span></label>
                    <select name="shift_id" id="shift_id" class="form-control @error('shift_id') is-invalid @enderror" required>
                        <option value="">-- Chọn ca --</option>
                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}" {{ old('shift_id', $schedule->shift_id) == $shift->id ? 'selected' : '' }}>
                                {{ $shift->name }} ({{ $shift->display_time }})
                            </option>
                        @endforeach
                    </select>
                    @error('shift_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="invalid-feedback">Vui lòng chọn ca làm việc</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
                <a href="{{ route('admin.working-schedules.index') }}" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
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

// Khởi tạo Select2 cho multi-select
$(document).ready(function() {
    $('#employee_ids').select2({
        placeholder: 'Chọn nhân viên',
        allowClear: false,
        width: '100%',
        closeOnSelect: false
    });

    // Cập nhật validation khi thay đổi
    $('#employee_ids').on('change', function() {
        const employeeCount = $('#employee_ids').val() ? $('#employee_ids').val().length : 0;
        
        if (employeeCount > 0) {
            $('#employee_ids')[0].setCustomValidity('');
        } else {
            $('#employee_ids')[0].setCustomValidity('Vui lòng chọn ít nhất một nhân viên');
        }
    });
});
</script>
@endpush

