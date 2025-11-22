@extends('admin.layouts.app')

@section('title', 'Thêm lịch nhân viên')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Thêm lịch nhân viên</h1>
    <a href="{{ route('admin.working-schedules.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Thông tin lịch</h6>
    </div>
    <div class="card-body">
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('conflicts'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="fas fa-times-circle"></i> Các lịch bị trùng:</strong>
                <ul class="mb-0 mt-2">
                    @foreach(session('conflicts') as $conflict)
                        <li>{{ $conflict }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <form action="{{ route('admin.working-schedules.store') }}" method="POST" class="needs-validation" novalidate onsubmit="return validateAndConfirm();">
            @csrf

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="employee_ids">Nhân viên <span class="text-danger">*</span> <small class="text-muted">(Giữ Ctrl/Cmd để chọn nhiều)</small></label>
                    <select name="employee_ids[]" id="employee_ids" class="form-control @error('employee_ids') is-invalid @enderror @error('employee_ids.*') is-invalid @enderror" multiple size="6" required>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ (old('employee_ids') && in_array($employee->id, old('employee_ids'))) ? 'selected' : '' }}>
                                {{ $employee->user->name ?? 'N/A' }} - {{ $employee->position ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                    @error('employee_ids')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @elseif($errors->has('employee_ids.*'))
                        <div class="invalid-feedback">{{ $errors->first('employee_ids.*') }}</div>
                    @else
                        <div class="invalid-feedback">Vui lòng chọn ít nhất một nhân viên</div>
                    @enderror
                    <small class="form-text text-muted">Đã chọn: <span id="selected-employees">0</span> nhân viên</small>
                </div>
                <div class="form-group col-md-6">
                    <label for="work_date">Ngày làm việc <span class="text-danger">*</span></label>
                    <input type="date" name="work_date" id="work_date" value="{{ old('work_date') }}" class="form-control @error('work_date') is-invalid @enderror" required>
                    @error('work_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="invalid-feedback">Vui lòng chọn ngày làm việc</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="shift_ids">Ca làm việc <span class="text-danger">*</span> <small class="text-muted">(Giữ Ctrl/Cmd để chọn nhiều)</small></label>
                    <select name="shift_ids[]" id="shift_ids" class="form-control @error('shift_ids') is-invalid @enderror @error('shift_ids.*') is-invalid @enderror" multiple size="6" required>
                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}" {{ (old('shift_ids') && in_array($shift->id, old('shift_ids'))) ? 'selected' : '' }}>
                                {{ $shift->name }} ({{ $shift->display_time }})
                            </option>
                        @endforeach
                    </select>
                    @error('shift_ids')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @elseif($errors->has('shift_ids.*'))
                        <div class="invalid-feedback">{{ $errors->first('shift_ids.*') }}</div>
                    @else
                        <div class="invalid-feedback">Vui lòng chọn ít nhất một ca làm việc</div>
                    @enderror
                    <small class="form-text text-muted">Đã chọn: <span id="selected-shifts">0</span> ca làm việc</small>
                </div>
                <div class="form-group col-md-6">
                    <label for="status">Trạng thái <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                        <option value="">-- Chọn trạng thái --</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="invalid-feedback">Vui lòng chọn trạng thái</div>
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

// Đếm số nhân viên và ca làm việc đã chọn
document.addEventListener('DOMContentLoaded', function() {
    const employeeSelect = document.getElementById('employee_ids');
    const shiftSelect = document.getElementById('shift_ids');
    const selectedEmployees = document.getElementById('selected-employees');
    const selectedShifts = document.getElementById('selected-shifts');
    
    function updateCounts() {
        const employeeCount = employeeSelect.selectedOptions.length;
        const shiftCount = shiftSelect.selectedOptions.length;
        
        selectedEmployees.textContent = employeeCount;
        selectedShifts.textContent = shiftCount;
        
        // Cập nhật validation
        if (employeeCount > 0) {
            employeeSelect.setCustomValidity('');
        } else {
            employeeSelect.setCustomValidity('Vui lòng chọn ít nhất một nhân viên');
        }
        
        if (shiftCount > 0) {
            shiftSelect.setCustomValidity('');
        } else {
            shiftSelect.setCustomValidity('Vui lòng chọn ít nhất một ca làm việc');
        }
    }
    
    employeeSelect.addEventListener('change', updateCounts);
    shiftSelect.addEventListener('change', updateCounts);
    updateCounts(); // Cập nhật lần đầu
});

// Xác nhận trước khi submit
function validateAndConfirm() {
    const employeeSelect = document.getElementById('employee_ids');
    const shiftSelect = document.getElementById('shift_ids');
    const employeeCount = employeeSelect.selectedOptions.length;
    const shiftCount = shiftSelect.selectedOptions.length;
    
    if (employeeCount === 0) {
        alert('Vui lòng chọn ít nhất một nhân viên!');
        return false;
    }
    
    if (shiftCount === 0) {
        alert('Vui lòng chọn ít nhất một ca làm việc!');
        return false;
    }
    
    const totalSchedules = employeeCount * shiftCount;
    const confirmMessage = `Bạn sẽ tạo ${totalSchedules} lịch làm việc (${employeeCount} nhân viên × ${shiftCount} ca). Xác nhận?`;
    
    return confirm(confirmMessage);
}
</script>
@endpush

