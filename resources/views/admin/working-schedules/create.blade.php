@extends('admin.layouts.app')

@section('title', 'Thêm lịch nhân viên')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Thêm lịch nhân viên mới</h1>
    <a href="{{ route('admin.working-schedules.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Thông tin lịch nhân viên</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.working-schedules.store') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="employee_id">Nhân viên <span class="text-danger">*</span></label>
                        <select name="employee_id" id="employee_id" class="form-control @error('employee_id') is-invalid @enderror" required>
                            <option value="">-- Chọn nhân viên --</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->user->name ?? 'N/A' }} - {{ $employee->position ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">Vui lòng chọn nhân viên</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="work_date">Ngày làm việc <span class="text-danger">*</span></label>
                        <input type="date" name="work_date" id="work_date" value="{{ old('work_date') }}" class="form-control @error('work_date') is-invalid @enderror" required>
                        @error('work_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">Vui lòng chọn ngày làm việc</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="shift_id">Ca làm việc <span class="text-danger">*</span></label>
                        <select name="shift_id" id="shift_id" class="form-control @error('shift_id') is-invalid @enderror" required>
                            <option value="">-- Chọn ca làm việc --</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ old('shift_id') == $shift->id ? 'selected' : '' }}>
                                    {{ $shift->name }} 
                                    ({{ $shift->formatted_start_time }} - 
                                    {{ $shift->formatted_end_time }})
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
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="status">Trạng thái <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                            <option value="">-- Chọn trạng thái --</option>
                            <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}>Rảnh</option>
                            <option value="busy" {{ old('status') == 'busy' ? 'selected' : '' }}>Bận</option>
                            <option value="off" {{ old('status') == 'off' ? 'selected' : '' }}>Nghỉ</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">Vui lòng chọn trạng thái</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="image">Ảnh</label>
                <input type="file" class="form-control-file border @error('image') is-invalid @enderror" id="image" name="image" accept="image/jpeg,image/png,image/jpg,image/gif">
                <small class="form-text text-muted">Chấp nhận: JPG, PNG, GIF (tối đa 2MB)</small>
                @error('image')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Xác nhận
                </button>
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
            var validation = Array.prototype.filter.call(forms, function(form) {
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

