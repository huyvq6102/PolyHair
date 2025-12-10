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

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-times-circle"></i> {{ session('error') }}
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

            <div class="form-group">
                <label>Chế độ xếp lịch <span class="text-danger">*</span></label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="schedule_type" id="schedule_type_day" value="day" {{ old('schedule_type', 'day') == 'day' ? 'checked' : '' }} onchange="toggleScheduleType()">
                    <label class="form-check-label" for="schedule_type_day">
                        Theo ngày
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="schedule_type" id="schedule_type_week" value="week" {{ old('schedule_type') == 'week' ? 'checked' : '' }} onchange="toggleScheduleType()">
                    <label class="form-check-label" for="schedule_type_week">
                        Theo tuần
                    </label>
                </div>
            </div>

            <div class="form-group" id="day_input_group">
                <label for="work_date">Ngày làm việc <span class="text-danger">*</span></label>
                <input type="date" name="work_date" id="work_date" value="{{ old('work_date') }}" class="form-control @error('work_date') is-invalid @enderror">
                @error('work_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Vui lòng chọn ngày làm việc</div>
                @enderror
            </div>

            <div class="form-group" id="week_input_group" style="display: none;">
                <label for="week_start_date">Tuần bắt đầu từ <span class="text-danger">*</span></label>
                <input type="date" name="week_start_date" id="week_start_date" value="{{ old('week_start_date') }}" class="form-control @error('week_start_date') is-invalid @enderror">
                @error('week_start_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Vui lòng chọn ngày bắt đầu tuần</div>
                @enderror
                <small class="form-text text-muted">Chọn thứ 2 của tuần (hoặc ngày bất kỳ, hệ thống sẽ tự động tính tuần từ thứ 2 đến chủ nhật)</small>
            </div>

            <div class="form-group">
                <label for="shift_ids">Ca làm việc <span class="text-danger">*</span></label>
                <select name="shift_ids[]" id="shift_ids" class="form-control select2-multiple @error('shift_ids') is-invalid @enderror @error('shift_ids.*') is-invalid @enderror" multiple required>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}" {{ (old('shift_ids') && in_array($shift->id, old('shift_ids'))) ? 'selected' : '' }}>
                            {{ $shift->name }} ({{ $shift->display_time }})
                        </option>
                    @endforeach
                </select>
                @error('shift_ids')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @elseif($errors->has('shift_ids.*'))
                    <div class="invalid-feedback d-block">{{ $errors->first('shift_ids.*') }}</div>
                @else
                    <div class="invalid-feedback">Vui lòng chọn ít nhất một ca làm việc</div>
                @enderror
                <small class="form-text text-muted">Mỗi ca sẽ có đủ 4 vị trí: Stylist, Barber, Shampooer, Receptionist</small>
            </div>

            <hr class="my-4">
            <h5 class="mb-3">Chọn nhân viên cho từng vị trí <span class="text-danger">*</span></h5>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="stylist_id">Stylist <span class="text-danger">*</span></label>
                    <select name="stylist_id" id="stylist_id" class="form-control @error('stylist_id') is-invalid @enderror" required>
                        <option value="">-- Chọn Stylist --</option>
                        @foreach($stylists as $stylist)
                            <option value="{{ $stylist->id }}" {{ old('stylist_id') == $stylist->id ? 'selected' : '' }}>
                                {{ $stylist->user->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                    @error('stylist_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="invalid-feedback">Vui lòng chọn Stylist</div>
                    @enderror
                </div>

                <div class="form-group col-md-6">
                    <label for="barber_id">Barber <span class="text-danger">*</span></label>
                    <select name="barber_id" id="barber_id" class="form-control @error('barber_id') is-invalid @enderror" required>
                        <option value="">-- Chọn Barber --</option>
                        @foreach($barbers as $barber)
                            <option value="{{ $barber->id }}" {{ old('barber_id') == $barber->id ? 'selected' : '' }}>
                                {{ $barber->user->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                    @error('barber_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="invalid-feedback">Vui lòng chọn Barber</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="shampooer_id">Shampooer <span class="text-danger">*</span></label>
                    <select name="shampooer_id" id="shampooer_id" class="form-control @error('shampooer_id') is-invalid @enderror" required>
                        <option value="">-- Chọn Shampooer --</option>
                        @foreach($shampooers as $shampooer)
                            <option value="{{ $shampooer->id }}" {{ old('shampooer_id') == $shampooer->id ? 'selected' : '' }}>
                                {{ $shampooer->user->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                    @error('shampooer_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="invalid-feedback">Vui lòng chọn Shampooer</div>
                    @enderror
                </div>

                <div class="form-group col-md-6">
                    <label for="receptionist_id">Receptionist <span class="text-danger">*</span></label>
                    <select name="receptionist_id" id="receptionist_id" class="form-control @error('receptionist_id') is-invalid @enderror" required>
                        <option value="">-- Chọn Receptionist --</option>
                        @foreach($receptionists as $receptionist)
                            <option value="{{ $receptionist->id }}" {{ old('receptionist_id') == $receptionist->id ? 'selected' : '' }}>
                                {{ $receptionist->user->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                    @error('receptionist_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="invalid-feedback">Vui lòng chọn Receptionist</div>
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
    $('#shift_ids').select2({
        placeholder: 'Chọn ca làm việc',
        allowClear: false,
        width: '100%',
        closeOnSelect: false
    });

    // Cập nhật validation khi thay đổi
    $('#shift_ids').on('change', function() {
        const shiftCount = $('#shift_ids').val() ? $('#shift_ids').val().length : 0;
        
        if (shiftCount > 0) {
            $('#shift_ids')[0].setCustomValidity('');
        } else {
            $('#shift_ids')[0].setCustomValidity('Vui lòng chọn ít nhất một ca làm việc');
        }
    });
});

// Toggle hiển thị input theo loại lịch
function toggleScheduleType() {
    const scheduleType = $('input[name="schedule_type"]:checked').val();
    if (scheduleType === 'day') {
        $('#day_input_group').show();
        $('#week_input_group').hide();
        $('#work_date').prop('required', true);
        $('#week_start_date').prop('required', false);
    } else {
        $('#day_input_group').hide();
        $('#week_input_group').show();
        $('#work_date').prop('required', false);
        $('#week_start_date').prop('required', true);
    }
}

// Khởi tạo khi trang load
$(document).ready(function() {
    toggleScheduleType();
});

// Xác nhận trước khi submit
function validateAndConfirm() {
    const stylistId = $('#stylist_id').val();
    const barberId = $('#barber_id').val();
    const shampooerId = $('#shampooer_id').val();
    const receptionistId = $('#receptionist_id').val();
    const shiftIds = $('#shift_ids').val();
    const shiftCount = shiftIds ? shiftIds.length : 0;
    const scheduleType = $('input[name="schedule_type"]:checked').val();
    
    if (!stylistId || !barberId || !shampooerId || !receptionistId) {
        alert('Vui lòng chọn đủ 4 nhân viên cho 4 vị trí!');
        return false;
    }
    
    if (shiftCount === 0) {
        alert('Vui lòng chọn ít nhất một ca làm việc!');
        $('#shift_ids').select2('open');
        return false;
    }
    
    let totalSchedules;
    let confirmMessage;
    
    if (scheduleType === 'week') {
        // 4 nhân viên × số ca × 7 ngày
        totalSchedules = 4 * shiftCount * 7;
        confirmMessage = `Bạn sẽ tạo ${totalSchedules} lịch làm việc cho cả tuần (4 nhân viên × ${shiftCount} ca × 7 ngày). Mỗi ca sẽ có đủ 4 vị trí: Stylist, Barber, Shampooer, Receptionist. Xác nhận?`;
    } else {
        // 4 nhân viên × số ca
        totalSchedules = 4 * shiftCount;
        confirmMessage = `Bạn sẽ tạo ${totalSchedules} lịch làm việc (4 nhân viên × ${shiftCount} ca). Mỗi ca sẽ có đủ 4 vị trí: Stylist, Barber, Shampooer, Receptionist. Xác nhận?`;
    }
    
    return confirm(confirmMessage);
}
</script>
@endpush
