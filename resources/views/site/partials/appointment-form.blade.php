@php
    // Lấy tất cả nhân viên từ database
    $allEmployees = \App\Models\Employee::with(['user.role'])
        ->whereNotNull('user_id')
        ->orderBy('id', 'desc')
        ->get();
    
    // Lọc nhân viên: loại trừ admin và nhân viên bị vô hiệu hóa
    $employees = $allEmployees->filter(function($employee) {
        // Bỏ qua nếu không có user
        if (!$employee->user) {
            return false;
        }
        
        // Loại trừ admin - kiểm tra role_id
        if ($employee->user->role_id == 1) {
            return false;
        }
        
        // Kiểm tra role name nếu có
        if ($employee->user->role) {
            $roleName = strtolower(trim($employee->user->role->name ?? ''));
            if (in_array($roleName, ['admin', 'administrator'])) {
                return false;
            }
        }
        
        // Loại trừ nhân viên bị vô hiệu hóa
        if ($employee->status === 'Vô hiệu hóa') {
            return false;
        }
        
        return true;
    })->values();
    
    $wordTimes = app(\App\Services\WordTimeService::class)->getAll();
    $serviceVariants = \App\Models\ServiceVariant::with('service')->get();
@endphp

<!-- Appointment Form Popup -->
<div id="test-form" class="mfp-hide white-popup-block">
    <div class="popup_box appointment-popup">
        <div class="popup_inner p-4">

            <!-- Header -->
            <div class="text-center mb-4">
                <h3 class="fw-bold">
                    <i class="fa fa-calendar-check-o"></i> Đặt lịch cắt tóc
                </h3>
                <p class="text-muted">Điền thông tin để đặt lịch hẹn với chúng tôi</p>
            </div>

            <form action="{{ route('site.appointment.store') }}" method="POST" id="appointmentForm">
                @csrf

                <!-- Thông tin khách hàng -->
                <div class="mb-4">
                    <h5 class="fw-semibold mb-3">
                        <i class="fa fa-user"></i> Thông tin khách hàng
                    </h5>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fa fa-user-circle"></i> Họ và tên <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               name="name"
                               class="form-control"
                               placeholder="Nhập họ và tên"
                               value="{{ old('name', auth()->user()->name ?? '') }}"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fa fa-phone"></i> Số điện thoại <span class="text-danger">*</span>
                        </label>
                        <input type="tel" 
                               name="phone"
                               class="form-control"
                               placeholder="Nhập số điện thoại"
                               value="{{ old('phone', auth()->user()->phone ?? '') }}"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fa fa-envelope"></i> Email
                        </label>
                        <input type="email" 
                               name="email"
                               class="form-control"
                               placeholder="Nhập email (tùy chọn)"
                               value="{{ old('email', auth()->user()->email ?? '') }}">
                    </div>
                </div>

                <!-- Chọn dịch vụ -->
                <div class="mb-4">
                    <h5 class="fw-semibold mb-3">
                        <i class="fa fa-scissors"></i> Chọn dịch vụ <span class="text-danger">*</span>
                    </h5>

                    @php $groupedVariants = $serviceVariants->groupBy('service_id'); @endphp

                    @foreach($groupedVariants as $serviceId => $variants)
                        @php $service = $variants->first()->service; @endphp

                        <div class="card mb-3 shadow-sm">
                            <div class="card-header fw-bold">
                                {{ $service->name }}
                            </div>

                            <div class="card-body">
                                @foreach($variants as $variant)
                                    <label class="d-flex justify-content-between align-items-center border rounded p-2 mb-2 bg-light">
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="checkbox"
                                                   name="service_variants[]"
                                                   value="{{ $variant->id }}"
                                                   class="form-check-input"
                                                   {{ in_array($variant->id, old('service_variants', [])) ? 'checked' : '' }}>
                                            <span>{{ $variant->name }}</span>
                                        </div>

                                        <div class="text-end small">
                                            <div class="fw-bold">{{ number_format($variant->price, 0, ',', '.') }}đ</div>
                                            <div><i class="fa fa-clock-o"></i> {{ $variant->duration }} phút</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                    @endforeach
                </div>

                <!-- Nhân viên -->
                <div class="mb-4">
                    <h5 class="fw-semibold mb-3">
                        <i class="fa fa-users"></i> Chọn nhân viên
                    </h5>

                    <select name="employee_id" class="form-select">
                        <option value="">Không chọn - để chúng tôi sắp xếp</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->user->name }}
                                @if($employee->position) - {{ $employee->position }} @endif
                                @if($employee->level) ({{ $employee->level }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Thời gian -->
                <div class="mb-4">
                    <h5 class="fw-semibold mb-3">
                        <i class="fa fa-clock-o"></i> Chọn thời gian
                    </h5>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fa fa-calendar"></i> Ngày đặt lịch <span class="text-danger">*</span>
                        </label>
                        <input type="date"
                               name="appointment_date"
                               class="form-control"
                               value="{{ old('appointment_date') }}"
                               min="{{ date('Y-m-d') }}"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fa fa-clock-o"></i> Chọn giờ <span class="text-danger">*</span>
                        </label>
                        <select name="time_slot" id="time_slot" class="form-select" disabled required>
                            <option value="">-- Vui lòng chọn nhân viên và ngày trước --</option>
                        </select>
                    </div>
                </div>

                <!-- Ghi chú -->
                <div class="mb-4">
                    <label class="form-label">
                        <i class="fa fa-comment-o"></i> Ghi chú
                    </label>
                    <textarea name="note" class="form-control" rows="3" placeholder="Nhập ghi chú...">{{ old('note') }}</textarea>
                </div>

                <!-- Submit -->
                <div class="text-center">
                    <button type="submit" class="btn btn-primary px-4 py-2">
                        <i class="fa fa-calendar-check-o"></i> Đặt lịch ngay
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>


@push('styles')
<style>
    /* Popup Container */
    .appointment-popup {
        background: #fff;
        padding: 0;
        border-radius: 12px;
        max-width: 90%;
        width: 100%;
        max-width: 480px;
        max-height: 85vh;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        margin: 0 auto;
    }
    
    .popup_inner {
        padding: 20px 25px;
        max-height: 85vh;
        overflow-y: auto;
    }
    
    /* Form Header */
    .form-header {
        text-align: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .form-header h3 {
        color: #4A3600;
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 6px;
    }
    
    .form-header h3 i {
        margin-right: 8px;
        color: #BC9321;
        font-size: 18px;
    }
    
    .form-subtitle {
        color: #666;
        font-size: 12px;
        margin: 0;
    }
    
    /* Form Sections */
    .form-section {
        margin-bottom: 18px;
    }
    
    .section-title {
        color: #4A3600;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        padding-bottom: 8px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .section-title i {
        margin-right: 6px;
        color: #BC9321;
        font-size: 16px;
    }
    
    .section-title .required {
        color: #dc3545;
        margin-left: 5px;
    }
    
    .section-title .optional {
        color: #999;
        font-weight: 400;
        font-size: 13px;
        margin-left: 5px;
    }
    
    /* Form Groups */
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-label {
        display: block;
        font-weight: 500;
        color: #333;
        margin-bottom: 6px;
        font-size: 13px;
    }
    
    .form-label i {
        margin-right: 5px;
        color: #4A3600;
        width: 14px;
        font-size: 13px;
    }
    
    .form-label .required {
        color: #dc3545;
        margin-left: 3px;
    }
    
    .form-label .optional {
        color: #999;
        font-weight: 400;
        font-size: 12px;
        margin-left: 3px;
    }
    
    /* Form Inputs */
    .form-input,
    .form-select,
    .form-textarea {
        width: 100%;
        padding: 10px 12px;
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        font-size: 13px;
        transition: all 0.3s ease;
        background: #fff;
        font-family: inherit;
    }
    
    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        border-color: #4A3600;
        outline: none;
        box-shadow: 0 0 0 3px rgba(74, 54, 0, 0.1);
    }
    
    .form-input::placeholder,
    .form-textarea::placeholder {
        color: #999;
    }
    
    .form-select {
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%234A3600' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 15px center;
        padding-right: 40px;
    }
    
    .form-textarea {
        resize: vertical;
        min-height: 60px;
    }
    
    /* Service Variants */
    .service-variants-container {
        max-height: 200px;
        overflow-y: auto;
        padding: 8px;
        background: #f8f9fa;
        border-radius: 6px;
        border: 2px solid #e9ecef;
    }
    
    .service-group {
        margin-bottom: 20px;
    }
    
    .service-group:last-child {
        margin-bottom: 0;
    }
    
    .service-category {
        background: #4A3600;
        color: #fff;
        padding: 10px 15px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
    }
    
    .service-category i {
        margin-right: 8px;
        font-size: 16px;
    }
    
    .variants-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .variant-item {
        display: flex;
        align-items: center;
        padding: 12px;
        background: #fff;
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .variant-item:hover {
        border-color: #4A3600;
        background: #fff8e1;
        transform: translateX(3px);
    }
    
    .variant-checkbox {
        margin-right: 12px;
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #4A3600;
    }
    
    .variant-checkbox:checked + .variant-content .variant-name {
        color: #4A3600;
        font-weight: 600;
    }
    
    /* Style checked variant item - using JavaScript class for better browser support */
    .variant-item.checked {
        border-color: #4A3600 !important;
        background: #fff8e1 !important;
        box-shadow: 0 2px 8px rgba(74, 54, 0, 0.15) !important;
    }
    
    .variant-item.checked .variant-name {
        color: #4A3600;
        font-weight: 600;
    }
    
    .variant-content {
        flex: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .variant-name {
        font-weight: 500;
        color: #333;
        font-size: 13px;
    }
    
    .variant-meta {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .variant-price {
        color: #BC9321;
        font-weight: 600;
        font-size: 13px;
    }
    
    .variant-duration {
        color: #666;
        font-size: 11px;
        display: flex;
        align-items: center;
        gap: 3px;
    }
    
    .variant-duration i {
        font-size: 10px;
    }
    
    /* Submit Button */
    .form-submit {
        margin-top: 20px;
        padding-top: 15px;
        border-top: 2px solid #f0f0f0;
    }
    
    .submit-btn {
        width: 100%;
        padding: 12px 20px;
        background: linear-gradient(135deg, #4A3600 0%, #5a4a00 100%);
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    
    .submit-btn:hover {
        background: linear-gradient(135deg, #5a4a00 0%, #4A3600 100%);
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(74, 54, 0, 0.3);
    }
    
    .submit-btn:active {
        transform: translateY(0);
    }
    
    .submit-btn i {
        font-size: 18px;
    }
    
    /* Messages */
    .success-message {
        background: #d4edda;
        color: #155724;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        text-align: center;
        border: 1px solid #c3e6cb;
        font-weight: 500;
    }
    
    .error-message {
        background: #f8d7da;
        color: #721c24;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #f5c6cb;
    }
    
    /* Scrollbar Styling */
    .service-variants-container::-webkit-scrollbar {
        width: 6px;
    }
    
    .service-variants-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .service-variants-container::-webkit-scrollbar-thumb {
        background: #4A3600;
        border-radius: 10px;
    }
    
    .service-variants-container::-webkit-scrollbar-thumb:hover {
        background: #5a4a00;
    }
    
    .popup_inner::-webkit-scrollbar {
        width: 8px;
    }
    
    .popup_inner::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    .popup_inner::-webkit-scrollbar-thumb {
        background: #4A3600;
        border-radius: 10px;
    }
    
    /* Responsive */
    @media (max-width: 991px) {
        .appointment-popup {
            max-width: 95%;
            width: 95%;
        }
        
        .popup_inner {
            padding: 20px 25px;
        }
    }
    
    @media (max-width: 767px) {
        .appointment-popup {
            max-width: 100%;
            width: 100%;
            border-radius: 0;
            max-height: 100vh;
        }
        
        .popup_inner {
            padding: 20px 15px;
        }
        
        .form-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
        }
        
        .form-header h3 {
            font-size: 20px;
        }
        
        .form-subtitle {
            font-size: 12px;
        }
        
        .section-title {
            font-size: 14px;
            margin-bottom: 12px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-input,
        .form-select,
        .form-textarea {
            padding: 10px 12px;
            font-size: 14px;
        }
        
        .service-variants-container {
            max-height: 200px;
            padding: 8px;
        }
        
        .variant-item {
            padding: 10px;
        }
        
        .variant-content {
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
        }
        
        .variant-checkbox {
            width: 16px;
            height: 16px;
        }
        
        .submit-btn {
            padding: 12px 20px;
            font-size: 14px;
        }
    }
    
    @media (max-width: 480px) {
        .popup_inner {
            padding: 15px 12px;
        }
        
        .form-header h3 {
            font-size: 18px;
        }
        
        .form-section {
            margin-bottom: 20px;
        }
        
        .service-variants-container {
            max-height: 150px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Set min date to today
        const today = new Date().toISOString().split('T')[0];
        $('#appointment_date').attr('min', today);
        
        // Handle checkbox checked state styling
        $('.variant-checkbox').on('change', function() {
            if ($(this).is(':checked')) {
                $(this).closest('.variant-item').addClass('checked');
            } else {
                $(this).closest('.variant-item').removeClass('checked');
            }
        });
        
        // Initialize checked state on load
        $('.variant-checkbox:checked').each(function() {
            $(this).closest('.variant-item').addClass('checked');
        });
        
        // Load available time slots when employee or date changes
        function loadAvailableTimeSlots() {
            const employeeId = $('#employee_id').val();
            const appointmentDate = $('#appointment_date').val();
            const timeSlotSelect = $('#time_slot');
            const wordTimeIdInput = $('#word_time_id');
            const timeSlotHelp = $('#timeSlotHelp');
            
            // Reset time slot selection
            timeSlotSelect.prop('disabled', true).html('<option value="">-- Đang tải khung giờ --</option>');
            wordTimeIdInput.val('');
            
            // Check if date is selected
            if (!appointmentDate) {
                timeSlotSelect.html('<option value="">-- Vui lòng chọn ngày trước --</option>');
                timeSlotHelp.hide();
                return;
            }
            
            // Load time slots via AJAX
            $.ajax({
                url: '{{ route("site.appointment.available-time-slots") }}',
                method: 'GET',
                data: {
                    employee_id: employeeId || '',
                    appointment_date: appointmentDate
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success && response.time_slots) {
                        timeSlotSelect.html('<option value="">-- Chọn giờ --</option>');
                        
                        let availableCount = 0;
                        const currentlySelectedTime = timeSlotSelect.val(); // Keep track of selected time
                        
                        response.time_slots.forEach(function(slot) {
                            if (slot.available !== false) {
                                const option = $('<option></option>')
                                    .attr('value', slot.time)
                                    .attr('data-word-time-id', slot.word_time_id)
                                    .text(slot.display);
                                
                                // If this is the currently selected time, keep it even if it becomes unavailable
                                if (currentlySelectedTime && slot.time === currentlySelectedTime) {
                                    option.text(slot.display + ' (Đã chọn)');
                                }
                                
                                timeSlotSelect.append(option);
                                availableCount++;
                            }
                        });
                        
                        // Restore selected value if it was selected before
                        if (currentlySelectedTime) {
                            const selectedOption = timeSlotSelect.find('option[value="' + currentlySelectedTime + '"]');
                            if (selectedOption.length) {
                                timeSlotSelect.val(currentlySelectedTime);
                                $('#word_time_id').val(selectedOption.data('word-time-id'));
                            }
                        }
                        
                        if (availableCount === 0) {
                            timeSlotSelect.html('<option value="" disabled>Không còn khung giờ trống</option>');
                        } else {
                            timeSlotSelect.prop('disabled', false);
                            timeSlotHelp.show();
                        }
                    } else {
                        timeSlotSelect.html('<option value="">-- Có lỗi xảy ra --</option>');
                    }
                },
                error: function() {
                    timeSlotSelect.html('<option value="">-- Có lỗi xảy ra khi tải khung giờ --</option>');
                }
            });
        }
        
        // Handle employee selection change
        $('#employee_id').on('change', function() {
            loadAvailableTimeSlots();
        });
        
        // Handle date selection change
        $('#appointment_date').on('change', function() {
            loadAvailableTimeSlots();
        });
        
        // Handle time slot selection - set word_time_id
        $('#time_slot').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const selectedTime = $(this).val();
            const wordTimeId = selectedOption.data('word-time-id');
            
            if (wordTimeId && selectedTime) {
                $('#word_time_id').val(wordTimeId);
            } else {
                $('#word_time_id').val('');
            }
        });
        
        // Handle form submission via AJAX
        $('#appointmentForm').on('submit', function(e) {
            e.preventDefault();
            
            // Validate service variants
            const serviceVariants = $('input[name="service_variants[]"]:checked');
            if (serviceVariants.length === 0) {
                alert('Vui lòng chọn ít nhất một dịch vụ!');
                return false;
            }
            
            // Remove previous messages
            $('.success-message, .error-message').remove();
            
            // Submit form via AJAX
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        $('#appointmentForm').prepend(
                            '<div class="success-message">' + response.message + '</div>'
                        );
                        
                        // Reset form after 2 seconds
                        setTimeout(function() {
                            $('#appointmentForm')[0].reset();
                            $.magnificPopup.close();
                            // Show toast notification if available
                            if (typeof toastr !== 'undefined') {
                                toastr.success(response.message);
                            } else {
                                alert(response.message);
                            }
                        }, 2000);
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Có lỗi xảy ra khi đặt lịch. Vui lòng thử lại.';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        // Display validation errors
                        let errors = '';
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            errors += value[0] + '<br>';
                        });
                        errorMessage = errors;
                    }
                    
                    $('#appointmentForm').prepend(
                        '<div class="error-message" style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 5px; margin-bottom: 15px;">' + errorMessage + '</div>'
                    );
                    
                    // Show toast notification if available
                    if (typeof toastr !== 'undefined') {
                        toastr.error(errorMessage);
                    }
                }
            });
        });
    });
</script>
@endpush

