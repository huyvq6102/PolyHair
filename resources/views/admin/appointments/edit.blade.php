@extends('admin.layouts.app')

@section('title', 'Sửa lịch hẹn')

@push('styles')
<style>
    .service-variant-attributes .badge {
        margin-right: 5px;
        margin-bottom: 3px;
        font-size: 11px;
    }
    
    .combo-items .badge {
        margin-right: 5px;
        margin-bottom: 3px;
        font-size: 11px;
    }
    
    .form-check-label {
        line-height: 1.6;
    }
    
    .form-check-label strong {
        color: #333;
    }
</style>
@endpush

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Sửa lịch hẹn {{ $appointment->booking_code ?? '#' . $appointment->id }}</h1>
    <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<!-- Form -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Thông tin lịch hẹn</h6>
    </div>
    <div class="card-body">
        <form id="appointment-edit-form" action="{{ route('admin.appointments.update', $appointment->id) }}" method="POST" class="needs-validation" novalidate>
            @csrf
            <input type="hidden" name="_method" value="PUT" id="form-method-put">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Tên khách hàng <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $appointment->user->name ?? '') }}" 
                               class="form-control @error('name') is-invalid @enderror" 
                               placeholder="Nhập tên khách hàng" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">Vui lòng nhập tên khách hàng</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="phone">Số điện thoại <span class="text-danger">*</span></label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $appointment->user->phone ?? '') }}" 
                               class="form-control @error('phone') is-invalid @enderror" 
                               placeholder="Nhập số điện thoại" required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">Vui lòng nhập số điện thoại</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $appointment->user->email ?? '') }}" 
                               class="form-control @error('email') is-invalid @enderror" 
                               placeholder="Nhập email">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="employee_id">Nhân viên</label>
                        <select name="employee_id" id="employee_id" class="form-control @error('employee_id') is-invalid @enderror">
                            <option value="">-- Chọn nhân viên --</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id', $appointment->employee_id) == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->user->name ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Dịch vụ hiện có -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Dịch vụ hiện có</label>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Dịch vụ</th>
                                        <th>Giá</th>
                                        <th>Thời lượng (phút)</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($appointment->appointmentDetails as $detail)
                                        <tr>
                                            <td>
                                                @if($detail->combo_id)
                                                    {{ $detail->combo->name ?? ($detail->notes ?? 'Combo') }}
                                                @elseif($detail->serviceVariant)
                                                    {{ $detail->serviceVariant->name ?? ($detail->serviceVariant->service->name ?? 'N/A') }}
                                                @else
                                                    {{ $detail->notes ?? 'Dịch vụ đơn' }}
                                                @endif
                                            </td>
                                            <td>{{ number_format($detail->price_snapshot ?? 0, 0, ',', '.') }} đ</td>
                                            <td>{{ $detail->duration ?? 0 }}</td>
                                            <td>
                                                <span class="badge badge-info">{{ $detail->status ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger btn-remove-service" 
                                                        data-appointment-id="{{ $appointment->id }}" 
                                                        data-detail-id="{{ $detail->id }}">
                                                    <i class="fas fa-trash"></i> Xóa
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">Chưa có dịch vụ nào</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thêm dịch vụ mới -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Thêm dịch vụ mới</label>
                        <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                            <!-- Dịch vụ đơn -->
                            @if($singleServices->count() > 0)
                                <div class="mb-3">
                                    <h6 class="text-primary">Dịch vụ đơn</h6>
                                    <div class="row">
                                        @foreach($singleServices as $service)
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input service-checkbox" type="checkbox" 
                                                           name="new_services[]" 
                                                           value="single_{{ $service->id }}" 
                                                           id="service_single_{{ $service->id }}"
                                                           data-type="single"
                                                           data-id="{{ $service->id }}"
                                                           data-price="{{ $service->base_price ?? 0 }}"
                                                           data-duration="{{ $service->base_duration ?? 0 }}">
                                                    <label class="form-check-label" for="service_single_{{ $service->id }}">
                                                        {{ $service->name }} - {{ number_format($service->base_price ?? 0, 0, ',', '.') }} đ
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Dịch vụ biến thể -->
                            @if($variantServices->count() > 0)
                                <div class="mb-3">
                                    <h6 class="text-primary">Dịch vụ biến thể</h6>
                                    @foreach($variantServices as $service)
                                        <div class="mb-2">
                                            <strong>{{ $service->name }}</strong>
                                            <div class="row ml-3">
                                                @foreach($service->serviceVariants as $variant)
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input service-checkbox" type="checkbox" 
                                                                   name="new_services[]" 
                                                                   value="variant_{{ $variant->id }}" 
                                                                   id="service_variant_{{ $variant->id }}"
                                                                   data-type="variant"
                                                                   data-id="{{ $variant->id }}"
                                                                   data-service-id="{{ $service->id }}"
                                                                   data-price="{{ $variant->price }}"
                                                                   data-duration="{{ $variant->duration }}">
                                                            <label class="form-check-label" for="service_variant_{{ $variant->id }}">
                                                                <strong>{{ $variant->name }}</strong> - {{ number_format($variant->price, 0, ',', '.') }} đ
                                                                @if($variant->variantAttributes->count() > 0)
                                                                    <br><small class="text-muted service-variant-attributes">
                                                                        @foreach($variant->variantAttributes as $attr)
                                                                            <span class="badge badge-secondary">{{ $attr->attribute_name }}: {{ $attr->attribute_value }}</span>
                                                                        @endforeach
                                                                    </small>
                                                                @endif
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            
                            <!-- Combo -->
                            @if($combos->count() > 0)
                                <div class="mb-3">
                                    <h6 class="text-primary">Combo</h6>
                                    <div class="row">
                                        @foreach($combos as $combo)
                                            <div class="col-md-6 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input service-checkbox" type="checkbox" 
                                                           name="new_services[]" 
                                                           value="combo_{{ $combo->id }}" 
                                                           id="service_combo_{{ $combo->id }}"
                                                           data-type="combo"
                                                           data-id="{{ $combo->id }}"
                                                           data-price="{{ $combo->price }}"
                                                           data-duration="0">
                                                    <label class="form-check-label" for="service_combo_{{ $combo->id }}">
                                                        <strong>{{ $combo->name }}</strong> - {{ number_format($combo->price, 0, ',', '.') }} đ
                                                        @if($combo->comboItems->count() > 0)
                                                            <br><small class="text-muted combo-items">
                                                                <i class="fas fa-list"></i> Bao gồm:
                                                                @foreach($combo->comboItems as $item)
                                                                    @if($item->serviceVariant)
                                                                        <span class="badge badge-info">{{ $item->serviceVariant->name }}</span>
                                                                    @elseif($item->service)
                                                                        <span class="badge badge-info">{{ $item->service->name }}</span>
                                                                    @endif
                                                                @endforeach
                                                            </small>
                                                        @endif
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                        <small class="text-muted">Chọn dịch vụ để thêm vào lịch hẹn (có thể chọn nhiều)</small>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="status">Trạng thái <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                            <option value="Chờ xử lý" {{ old('status', $appointment->status) == 'Chờ xử lý' ? 'selected' : '' }}>Chờ xử lý</option>
                            <option value="Đã xác nhận" {{ old('status', $appointment->status) == 'Đã xác nhận' ? 'selected' : '' }}>Đã xác nhận</option>
                            <option value="Đang thực hiện" {{ old('status', $appointment->status) == 'Đang thực hiện' ? 'selected' : '' }}>Đang thực hiện</option>
                            <option value="Hoàn thành" {{ old('status', $appointment->status) == 'Hoàn thành' ? 'selected' : '' }}>Hoàn thành</option>
                            <option value="Đã hủy" {{ old('status', $appointment->status) == 'Đã hủy' ? 'selected' : '' }}>Đã hủy</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">Vui lòng chọn trạng thái</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="appointment_date">Ngày đặt</label>
                        <input type="date" name="appointment_date" id="appointment_date" 
                               value="{{ old('appointment_date', $appointment->start_at ? $appointment->start_at->format('Y-m-d') : '') }}" 
                               min="{{ date('Y-m-d') }}"
                               class="form-control @error('appointment_date') is-invalid @enderror"
                               {{ !old('employee_id', $appointment->employee_id) ? 'disabled' : '' }}>
                        <small class="form-text text-muted" id="date_help_text">
                            {{ !old('employee_id', $appointment->employee_id) ? 'Vui lòng chọn nhân viên trước' : 'Chọn ngày đặt lịch' }}
                        </small>
                        @error('appointment_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="appointment_time">Giờ đặt</label>
                        <select name="appointment_time" id="appointment_time" 
                                class="form-control @error('appointment_time') is-invalid @enderror"
                                {{ !old('employee_id', $appointment->employee_id) || !old('appointment_date', $appointment->start_at ? $appointment->start_at->format('Y-m-d') : '') ? 'disabled' : '' }}>
                            <option value="">-- Vui lòng chọn nhân viên và ngày trước --</option>
                        </select>
                        <input type="hidden" name="word_time_id" id="word_time_id" value="{{ old('word_time_id', $appointment->start_at ? (\App\Models\WordTime::where('time', $appointment->start_at->format('H:i'))->first()->id ?? '') : '') }}">
                        <small class="form-text text-muted" id="time_slot_message" style="display: none;"></small>
                        @error('appointment_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="note">Mô tả</label>
                <textarea name="note" id="note" rows="3" 
                          class="form-control @error('note') is-invalid @enderror" 
                          placeholder="Nhập mô tả (nếu có)">{{ old('note', $appointment->note) }}</textarea>
                @error('note')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu
                </button>
                <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary">Hủy</a>
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
            // Chỉ validate form chính (form edit appointment)
            var mainForm = document.getElementById('appointment-edit-form');
            if (mainForm) {
                mainForm.addEventListener('submit', function(event) {
                    // Đảm bảo method là PUT
                    var methodInput = document.getElementById('form-method-put');
                    if (methodInput) {
                        methodInput.value = 'PUT';
                    }
                    
                    // Kiểm tra xem có input _method nào khác không và xóa nếu không phải PUT
                    var allMethodInputs = mainForm.querySelectorAll('input[name="_method"]');
                    allMethodInputs.forEach(function(input) {
                        if (input.id !== 'form-method-put') {
                            input.remove();
                        } else {
                            input.value = 'PUT';
                        }
                    });
                    
                    if (mainForm.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    mainForm.classList.add('was-validated');
                }, false);
            }
            
            // Xử lý xóa dịch vụ bằng cách tạo form động
            var removeServiceButtons = document.querySelectorAll('.btn-remove-service');
            removeServiceButtons.forEach(function(button) {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    var appointmentId = this.getAttribute('data-appointment-id');
                    var detailId = this.getAttribute('data-detail-id');
                    
                    if (!appointmentId || !detailId) {
                        return false;
                    }
                    
                    // Hiển thị xác nhận
                    if (confirm('Bạn có chắc muốn xóa dịch vụ này?')) {
                        // Tạo form động để submit
                        var form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '/admin/appointments/' + appointmentId + '/remove-service/' + detailId;
                        form.style.display = 'none';
                        
                        // Thêm CSRF token
                        var csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        // Lấy CSRF token từ meta tag hoặc từ form chính
                        var csrfToken = document.querySelector('meta[name="csrf-token"]') ? 
                                       document.querySelector('meta[name="csrf-token"]').getAttribute('content') : 
                                       (document.querySelector('#appointment-edit-form input[name="_token"]') ? 
                                        document.querySelector('#appointment-edit-form input[name="_token"]').value : 
                                        '{{ csrf_token() }}');
                        csrfInput.value = csrfToken;
                        form.appendChild(csrfInput);
                        
                        // Thêm method DELETE
                        var methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'DELETE';
                        form.appendChild(methodInput);
                        
                        // Thêm form vào body và submit
                        document.body.appendChild(form);
                        form.submit();
                    }
                    
                    return false;
                }, false);
            });
        }, false);
    })();
    
    // Không cần lọc dịch vụ theo nhân viên - hiển thị tất cả dịch vụ
    
    // Xử lý dependency: Nhân viên -> Ngày -> Giờ
    $(document).ready(function() {
        // Lấy giá trị hiện tại
        var currentEmployeeId = $('#employee_id').val();
        var currentAppointmentDate = $('#appointment_date').val();
        var currentAppointmentTime = '{{ $appointment->start_at ? $appointment->start_at->format("H:i") : "" }}';
        var currentWordTimeId = $('#word_time_id').val();
        
        // Xử lý dependency: Nhân viên -> Ngày -> Giờ
        $('#employee_id').on('change', function() {
            const employeeId = $(this).val();
            const $appointmentDate = $('#appointment_date');
            
            if (employeeId) {
                // Enable input ngày khi đã chọn nhân viên
                $(this).removeClass('is-invalid');
                $appointmentDate.prop('disabled', false);
                $('#date_help_text').text('Chọn ngày đặt lịch');
                
                // Load time slots nếu đã chọn ngày
                if ($appointmentDate.val()) {
                    loadAvailableTimeSlots();
                } else {
                    // Reset time slots
                    $('#appointment_time').prop('disabled', true).html('<option value="">-- Vui lòng chọn ngày trước --</option>');
                    $('#word_time_id').val('');
                    $('#time_slot_message').hide();
                }
            } else {
                // Disable input ngày và reset khi bỏ chọn nhân viên
                $appointmentDate.prop('disabled', true).val('').removeClass('is-invalid');
                $('#date_help_text').text('Vui lòng chọn nhân viên trước');
                
                // Reset time slots
                $('#appointment_time').prop('disabled', true).html('<option value="">-- Vui lòng chọn nhân viên và ngày trước --</option>');
                $('#word_time_id').val('');
                $('#time_slot_message').hide();
            }
        });
        
        $('#appointment_date').on('change', function() {
            const dateValue = $(this).val();
            const employeeId = $('#employee_id').val();
            
            if (dateValue && dateValue.trim() !== '') {
                $(this).removeClass('is-invalid');
                
                // Chỉ load time slots nếu đã chọn nhân viên
                if (employeeId) {
                    loadAvailableTimeSlots();
                } else {
                    $('#appointment_time').prop('disabled', true).html('<option value="">-- Vui lòng chọn nhân viên trước --</option>');
                    $('#word_time_id').val('');
                    $('#time_slot_message').text('Vui lòng chọn nhân viên trước').show();
                }
            } else {
                // Reset time slots nếu xóa ngày
                $('#appointment_time').prop('disabled', true).html('<option value="">-- Vui lòng chọn ngày trước --</option>');
                $('#word_time_id').val('');
                $('#time_slot_message').hide();
            }
        });
        
        // Load available time slots
        function loadAvailableTimeSlots() {
            const employeeId = $('#employee_id').val();
            const appointmentDate = $('#appointment_date').val();
            
            if (!employeeId || !appointmentDate) {
                return;
            }
            
            // Disable time select và hiển thị loading
            const $timeSelect = $('#appointment_time');
            $timeSelect.prop('disabled', true).html('<option value="">Đang tải...</option>');
            $('#time_slot_message').text('Đang tải các khung giờ có sẵn...').show();
            
            // Gọi API để lấy available time slots
            $.ajax({
                url: '{{ route("site.appointment.available-time-slots") }}',
                method: 'GET',
                data: {
                    employee_id: employeeId,
                    appointment_date: appointmentDate
                },
                success: function(response) {
                    if (response.success && response.time_slots) {
                        $timeSelect.html('<option value="">-- Chọn giờ --</option>');
                        
                        let hasAvailableSlots = false;
                        let currentTimeSelected = false;
                        
                        response.time_slots.forEach(function(slot) {
                            // Hiển thị cả available và unavailable slots, nhưng đánh dấu rõ ràng
                            const option = $('<option></option>')
                                .attr('value', slot.time)
                                .attr('data-word-time-id', slot.word_time_id);
                            
                            if (slot.available) {
                                hasAvailableSlots = true;
                                option.text(slot.time + ' - ' + (slot.display_name || 'Có sẵn'));
                                
                                // Nếu là giờ hiện tại của appointment, chọn nó
                                if (slot.time === currentAppointmentTime) {
                                    option.prop('selected', true);
                                    $('#word_time_id').val(slot.word_time_id);
                                    currentTimeSelected = true;
                                }
                            } else {
                                // Vẫn hiển thị nhưng đánh dấu là không available
                                option.text(slot.time + ' - Không có sẵn')
                                      .prop('disabled', true)
                                      .css('color', '#999');
                                
                                // Nếu là giờ hiện tại, vẫn cho phép chọn (để giữ giá trị cũ)
                                if (slot.time === currentAppointmentTime) {
                                    option.prop('disabled', false)
                                          .prop('selected', true)
                                          .css('color', '#000')
                                          .text(slot.time + ' - Giờ hiện tại (có thể đã bị thay đổi)');
                                    $('#word_time_id').val(slot.word_time_id);
                                    currentTimeSelected = true;
                                }
                            }
                            
                            $timeSelect.append(option);
                        });
                        
                        // Nếu giờ hiện tại không có trong danh sách, thêm vào
                        if (currentAppointmentTime && !currentTimeSelected) {
                            const currentOption = $('<option></option>')
                                .attr('value', currentAppointmentTime)
                                .attr('data-word-time-id', currentWordTimeId)
                                .prop('selected', true)
                                .text(currentAppointmentTime + ' - Giờ hiện tại');
                            $timeSelect.prepend(currentOption);
                            $('#word_time_id').val(currentWordTimeId);
                        }
                        
                        if (hasAvailableSlots || currentAppointmentTime) {
                            $timeSelect.prop('disabled', false);
                            $('#time_slot_message').text('Chọn giờ đặt lịch').show();
                        } else {
                            $timeSelect.html('<option value="">Không có khung giờ trống</option>');
                            $('#time_slot_message').text('Không có khung giờ trống cho nhân viên này trong ngày đã chọn').show();
                        }
                    } else {
                        $timeSelect.html('<option value="">Không thể tải khung giờ</option>');
                        $('#time_slot_message').text(response.message || 'Không thể tải khung giờ').show();
                    }
                },
                error: function(xhr) {
                    console.error('Error loading time slots:', xhr);
                    $timeSelect.html('<option value="">Lỗi khi tải khung giờ</option>');
                    $('#time_slot_message').text('Có lỗi xảy ra khi tải khung giờ').show();
                }
            });
        }
        
        // Khi chọn giờ, lưu word_time_id
        $('#appointment_time').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const wordTimeId = selectedOption.attr('data-word-time-id');
            
            if (wordTimeId) {
                $('#word_time_id').val(wordTimeId);
                $(this).removeClass('is-invalid');
            } else {
                $('#word_time_id').val('');
            }
        });
        
        // Khởi tạo: Load time slots nếu đã có nhân viên và ngày
        if (currentEmployeeId && currentAppointmentDate) {
            loadAvailableTimeSlots();
        }
    });
</script>
@endpush
