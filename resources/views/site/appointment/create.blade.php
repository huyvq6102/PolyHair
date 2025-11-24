@extends('layouts.site')

@section('title', 'Đặt lịch ngay')

@section('content')
<div class="appointment-page" style="padding: 30px 0 10px; background: #f8f9fa; min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-xl-5">
                <div class="appointment-form-container" style="background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 15px; margin-bottom: 10px; margin-top: 120px;">
                    
                    <!-- Header -->
                    <div class="text-center mb-2" style="margin-top: 5px;">
                        <h2 class="fw-bold mb-1" style="color: #000; font-size: 18px;">
                            <i class="fa fa-calendar-check-o"></i> ĐẶT LỊCH NGAY
                        </h2>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('site.appointment.store') }}" method="POST" id="appointmentForm">
                        @csrf
                        
                        @if(request('service_id'))
                            <input type="hidden" name="service_id" value="{{ request('service_id') }}">
                        @endif

                        @if(request('service_variants'))
                            @foreach(request('service_variants') as $variantId)
                                <input type="hidden" name="service_variants[]" value="{{ $variantId }}">
                            @endforeach
                        @endif

                        @if(request('combo_id'))
                            <input type="hidden" name="combo_id" value="{{ request('combo_id') }}">
                        @endif

                        <!-- Thông tin khách hàng -->
                        <div class="mb-2">
                            <h5 class="fw-semibold mb-1" style="color: #000; font-size: 13px;">
                                <i class="fa fa-user"></i> Thông tin khách hàng
                            </h5>

                            <div class="row">
                                <div class="col-md-6 mb-1">
                                    <label class="form-label" style="font-size: 12px;">
                                        <i class="fa fa-user-circle"></i> Họ và tên <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           name="name"
                                           class="form-control"
                                           style="font-size: 12px; padding: 5px 8px;"
                                           placeholder="Nhập họ và tên"
                                           value="{{ old('name', auth()->user()->name ?? '') }}"
                                           required>
                                </div>

                                <div class="col-md-6 mb-1">
                                    <label class="form-label" style="font-size: 12px;">
                                        <i class="fa fa-phone"></i> Số điện thoại <span class="text-danger">*</span>
                                    </label>
                                    <input type="tel" 
                                           name="phone"
                                           class="form-control"
                                           style="font-size: 12px; padding: 5px 8px;"
                                           placeholder="Nhập số điện thoại"
                                           value="{{ old('phone', auth()->user()->phone ?? '') }}"
                                           required>
                                </div>
                            </div>

                            <div class="mb-1">
                                <label class="form-label" style="font-size: 12px;">
                                    <i class="fa fa-envelope"></i> Email
                                </label>
                                <input type="email" 
                                       name="email"
                                       class="form-control"
                                       style="font-size: 12px; padding: 5px 8px;"
                                       placeholder="Nhập email (tùy chọn)"
                                       value="{{ old('email', auth()->user()->email ?? '') }}">
                            </div>
                        </div>

                        <!-- Chọn dịch vụ -->
                        <div class="mb-2">
                            <h5 class="fw-semibold mb-1" style="color: #000; font-size: 13px;">
                                <i class="fa fa-scissors"></i> DỊCH VỤ <span class="text-danger">*</span>
                            </h5>

                            @if(request('service_id'))
                                @php
                                    $selectedService = \App\Models\Service::find(request('service_id'));
                                @endphp
                                @if($selectedService)
                                    <div class="selected-service-display" style="background: #f8f9fa; border: 2px solid #000; border-radius: 8px; padding: 8px; margin-bottom: 6px;">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div style="flex: 1;">
                                                <div style="color: #000; font-size: 12px; font-weight: 700; margin-bottom: 4px;">
                                                    <i class="fa fa-check-circle" style="color: #28a745;"></i> {{ $selectedService->name }}
                                                </div>
                                                <div style="color: #666; font-size: 11px;">
                                                    <span style="margin-right: 15px;">
                                                        <i class="fa fa-money"></i> <strong style="color: #c08a3f;">{{ number_format($selectedService->base_price ?? 0, 0, ',', '.') }}vnđ</strong>
                                                    </span>
                                                    <span>
                                                        <i class="fa fa-clock-o"></i> <strong>{{ $selectedService->base_duration ?? 60 }} phút</strong>
                                                    </span>
                                                </div>
                                            </div>
                                            <a href="{{ route('site.appointment.create') }}" class="btn btn-sm" style="background: #fff; border: 1px solid #dc3545; color: #dc3545; margin-left: 10px;">
                                                <i class="fa fa-times"></i> Đổi
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            @elseif(request('service_variants'))
                                @php
                                    $selectedVariants = \App\Models\ServiceVariant::whereIn('id', request('service_variants'))->with('service')->get();
                                @endphp
                                @if($selectedVariants->count() > 0)
                                    <div class="selected-variants-display" style="background: #f8f9fa; border: 2px solid #000; border-radius: 8px; padding: 8px; margin-bottom: 6px;">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div style="flex: 1;">
                                                <div style="color: #000; font-size: 12px; font-weight: 700; margin-bottom: 6px;">
                                                    <i class="fa fa-check-circle" style="color: #28a745;"></i> Biến thể đã chọn:
                                                </div>
                                                @foreach($selectedVariants as $variant)
                                                    <div style="color: #000; font-size: 11px; margin-bottom: 5px; padding: 6px; background: #fff; border-radius: 6px; border-left: 3px solid #000;">
                                                        <div style="font-weight: 600; margin-bottom: 3px;">
                                                            {{ $variant->name }}
                                                            @if($variant->service)
                                                                <span style="color: #666; font-size: 10px; font-weight: 400;">({{ $variant->service->name }})</span>
                                                            @endif
                                                        </div>
                                                        <div style="color: #666; font-size: 10px;">
                                                            <span style="margin-right: 15px;">
                                                                <i class="fa fa-money"></i> <strong style="color: #c08a3f;">{{ number_format($variant->price ?? 0, 0, ',', '.') }}vnđ</strong>
                                                            </span>
                                                            <span>
                                                                <i class="fa fa-clock-o"></i> <strong>{{ $variant->duration ?? 60 }} phút</strong>
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <a href="{{ route('site.appointment.create') }}" class="btn btn-sm" style="background: #fff; border: 1px solid #dc3545; color: #dc3545; margin-left: 10px;">
                                                <i class="fa fa-times"></i> Đổi
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            @elseif(request('combo_id'))
                                @php
                                    $selectedCombo = \App\Models\Combo::with('comboItems.serviceVariant')->find(request('combo_id'));
                                @endphp
                                @if($selectedCombo)
                                    @php
                                        $comboDuration = 60;
                                        if ($selectedCombo->comboItems && $selectedCombo->comboItems->count() > 0) {
                                            $comboDuration = $selectedCombo->comboItems->sum(function($item) {
                                                return $item->serviceVariant->duration ?? 60;
                                            });
                                        }
                                    @endphp
                                    <div class="selected-combo-display" style="background: #f8f9fa; border: 2px solid #000; border-radius: 8px; padding: 8px; margin-bottom: 6px;">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div style="flex: 1;">
                                                <div style="color: #000; font-size: 12px; font-weight: 700; margin-bottom: 4px;">
                                                    <i class="fa fa-check-circle" style="color: #28a745;"></i> {{ $selectedCombo->name }}
                                                    <span style="color: #666; font-size: 10px; font-weight: 400; margin-left: 5px;">(COMBO)</span>
                                                </div>
                                                <div style="color: #666; font-size: 11px;">
                                                    <span style="margin-right: 15px;">
                                                        <i class="fa fa-money"></i> <strong style="color: #c08a3f;">{{ number_format($selectedCombo->price ?? 0, 0, ',', '.') }}vnđ</strong>
                                                    </span>
                                                    <span>
                                                        <i class="fa fa-clock-o"></i> <strong>{{ $comboDuration }} phút</strong>
                                                    </span>
                                                </div>
                                            </div>
                                            <a href="{{ route('site.appointment.create') }}" class="btn btn-sm" style="background: #fff; border: 1px solid #dc3545; color: #dc3545; margin-left: 10px;">
                                                <i class="fa fa-times"></i> Đổi
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <a href="{{ route('site.appointment.select-services') }}" class="btn btn-primary w-100" style="background: #000; border: 1px solid #000; color: #fff; padding: 6px 10px; font-size: 12px; font-weight: 600; border-radius: 8px; transition: all 0.3s ease; text-decoration: none; display: inline-block; text-align: center;">
                                    <i class="fa fa-scissors"></i> Xem tất cả dịch vụ hấp dẫn
                                </a>
                            @endif
                            <style>
                                .btn-primary:hover {
                                    background: #FFC107 !important;
                                    color: #000 !important;
                                    border: 1px solid #FFC107 !important;
                                }
                            </style>
                        </div>


                        <!-- Kỹ thuật viên -->
                        <div class="mb-2">
                            <h5 class="fw-semibold mb-1" style="color: #000; font-size: 13px;">
                                <i class="fa fa-users"></i> KỸ THUẬT VIÊN
                            </h5>

                            <select name="employee_id" id="employee_id" class="form-select" style="font-size: 12px; padding: 5px 8px;">
                                <option value="">Hãy chọn kỹ thuật viên</option>
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
                        <div class="mb-2">
                            <h5 class="fw-semibold mb-1" style="color: #000; font-size: 13px;">
                                <i class="fa fa-clock-o"></i> CHỌN NGÀY GIỜ
                            </h5>

                            <div class="mb-1">
                                <label class="form-label" style="font-size: 12px;">
                                    <i class="fa fa-calendar"></i> Ngày đặt lịch <span class="text-danger">*</span>
                                </label>
                                <input type="date"
                                       name="appointment_date"
                                       id="appointment_date"
                                       class="form-control"
                                       style="font-size: 12px; padding: 5px 8px;"
                                       value="{{ old('appointment_date') }}"
                                       min="{{ date('Y-m-d') }}"
                                       required>
                            </div>

                            <div class="mb-1">
                                <label class="form-label" style="font-size: 12px;">
                                    <i class="fa fa-clock-o"></i> Chọn giờ <span class="text-danger">*</span>
                                </label>
                                <div class="time-slot-container" style="position: relative; display: none;">
                                    <button type="button" class="time-slot-nav-btn time-slot-prev" style="position: absolute; left: -35px; top: 50%; transform: translateY(-50%); background: #000; color: #fff; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa fa-chevron-left"></i>
                                    </button>
                                    <div id="time_slot_grid" class="time-slot-grid" style="overflow: hidden;">
                                        <div class="time-slot-slider" style="transition: transform 0.3s ease;">
                                            <!-- Time slots will be rendered here -->
                                        </div>
                                    </div>
                                    <button type="button" class="time-slot-nav-btn time-slot-next" style="position: absolute; right: -35px; top: 50%; transform: translateY(-50%); background: #000; color: #fff; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa fa-chevron-right"></i>
                                    </button>
                                </div>
                                <div id="time_slot_message" class="text-muted" style="padding: 6px; color: #000; font-size: 11px;">
                                    Vui lòng chọn kỹ thuật viên và ngày trước
                                </div>
                                <input type="hidden" name="time_slot" id="time_slot" value="">
                                <input type="hidden" name="word_time_id" id="word_time_id" value="">
                            </div>
                        </div>

                        <!-- Ghi chú -->
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 12px;">
                                <i class="fa fa-comment-o"></i> Ghi chú
                            </label>
                            <textarea name="note" class="form-control" style="font-size: 12px; padding: 5px 8px;" rows="2" placeholder="Nhập ghi chú (tùy chọn)">{{ old('note') }}</textarea>
                        </div>

                        <!-- Submit -->
                        <div class="text-center mt-2">
                            <button type="submit" class="btn btn-primary px-3 py-2 submit-appointment-btn" style="background: #000; border: none; font-size: 13px; font-weight: 600; min-width: 160px; color: #fff; transition: all 0.3s ease;">
                                <i class="fa fa-calendar-check-o"></i> GỬI ĐẶT LỊCH
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .appointment-form-container {
        animation: fadeIn 0.5s ease-in;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .form-label {
        display: block;
        font-weight: 500;
        color: #000;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-label i {
        margin-right: 5px;
        color: #000;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #4A3600;
        box-shadow: 0 0 0 0.2rem rgba(74, 54, 0, 0.25);
    }

    .form-select,
    .variant-chooser,
    .variant-select,
    select.form-select {
        border: 1px solid #000 !important;
        transition: all 0.3s ease;
    }

    .form-select:hover,
    .variant-chooser:hover,
    .variant-select:hover,
    select.form-select:hover {
        border-color: #333 !important;
        background-color: #f8f9fa !important;
    }

    .form-select:focus,
    .variant-chooser:focus,
    .variant-select:focus,
    select.form-select:focus {
        border-color: #000 !important;
        background-color: #fff !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.1);
    }

    .card {
        border: none;
        border-radius: 8px;
    }

    .card-header {
        border-radius: 8px 8px 0 0 !important;
    }

    label.bg-light:hover {
        background: #fff8e1 !important;
        border-color: #4A3600 !important;
    }

    .form-check-input:checked {
        background-color: #4A3600;
        border-color: #4A3600;
    }

    /* Service Header */
    .service-header:hover {
        background: #333 !important;
    }

    .service-header.active .service-arrow {
        transform: rotate(180deg);
    }

    .service-dropdown {
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            max-height: 0;
        }
        to {
            opacity: 1;
            max-height: 500px;
        }
    }

    .service-variant-select {
        min-height: 120px;
    }

    /* Submit button hover effect - giống nút Đặt lịch ngay trên menu */
    .submit-appointment-btn {
        transition: all 0.3s ease;
    }

    .submit-appointment-btn:hover {
        background: #FFC107 !important;
        color: #000 !important;
        border: 1px solid #FFC107 !important;
    }

    .submit-appointment-btn:hover i {
        color: #000 !important;
    }

    /* Custom Select Box */
    .custom-select-wrapper {
        position: relative;
    }

    .custom-select-input {
        border: 1px solid #000;
        padding: 10px 15px;
        background: #000;
        cursor: pointer;
        position: relative;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .custom-select-input:hover {
        background-color: #333;
    }

    .custom-select-input.active {
        border-color: #000;
        background: #000;
    }

    .custom-select-text {
        color: #fff;
        font-size: 14px;
    }
    
    .custom-select-input i {
        color: #fff !important;
    }

    .custom-select-dropdown {
        position: absolute;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        z-index: 1000;
        max-height: 300px;
        overflow-y: auto;
        width: 100%;
        margin-top: 2px;
        top: 100%;
        left: 0;
    }

    .custom-select-option {
        padding: 10px 15px;
        cursor: pointer;
        color: #000;
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.2s ease;
        font-size: 14px;
    }

    .custom-select-option:last-child {
        border-bottom: none;
    }

    .custom-select-option:hover {
        background-color: #f5f5f5;
    }

    .custom-select-option.selected {
        background-color: #007bff;
        color: #fff;
    }

    .custom-select-option.selected:hover {
        background-color: #0056b3;
    }

    /* Time Slot Container */
    .time-slot-container {
        margin-top: 8px;
        padding: 0 40px;
    }

    /* Time Slot Grid */
    .time-slot-grid {
        width: 100%;
        position: relative;
    }

    .time-slot-slider {
        display: flex;
        gap: 0;
        width: max-content;
    }

    .time-slot-page {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        grid-template-rows: repeat(3, auto);
        gap: 10px;
        width: 100%;
        flex-shrink: 0;
        box-sizing: border-box;
    }

    .time-slot-nav-btn {
        transition: all 0.3s ease;
    }

    .time-slot-nav-btn:hover {
        background: #FFC107 !important;
        color: #000 !important;
    }

    .time-slot-nav-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .time-slot-btn {
        padding: 12px 15px;
        border: 1px solid #000;
        border-radius: 6px;
        background: #fff;
        color: #000;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
        min-width: 0;
    }

    .time-slot-btn:hover:not(.unavailable) {
        background: #f8f8f8;
        border-color: #333;
    }

    .time-slot-btn.selected {
        background: #000;
        color: #fff;
        border-color: #000;
        font-weight: 600;
    }

    .time-slot-btn.unavailable {
        background: #e8e8e8;
        color: #b0b0b0;
        border: 1px solid #e0e0e0;
        cursor: not-allowed;
        opacity: 0.6;
    }

    .time-slot-btn.unavailable:hover {
        background: #e8e8e8;
        transform: none;
        box-shadow: none;
        border-color: #e0e0e0;
    }

    @media (max-width: 768px) {
        .appointment-form-container {
            padding: 25px !important;
        }

        .appointment-page {
            padding: 80px 0 30px !important;
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
        
        // Load employees by service on page load
        loadEmployeesByService();
        
        // Function to load employees by service
        function loadEmployeesByService() {
            const serviceId = $('input[name="service_id"]').val();
            const serviceVariants = [];
            $('input[name="service_variants[]"]').each(function() {
                serviceVariants.push($(this).val());
            });
            const comboId = $('input[name="combo_id"]').val();
            
            // Only load if there's a service selected
            if (!serviceId && serviceVariants.length === 0 && !comboId) {
                return;
            }
            
            $.ajax({
                url: '{{ route("site.appointment.employees-by-service") }}',
                method: 'GET',
                data: {
                    service_id: serviceId || '',
                    service_variants: serviceVariants,
                    combo_id: comboId || ''
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success && response.employees) {
                        const $select = $('#employee_id');
                        const currentValue = $select.val();
                        
                        // Clear existing options except the first one
                        $select.find('option:not(:first)').remove();
                        
                        // Add new options
                        if (response.employees.length > 0) {
                            response.employees.forEach(function(employee) {
                                const $option = $('<option></option>')
                                    .attr('value', employee.id)
                                    .text(employee.display_name);
                                
                                if (currentValue == employee.id) {
                                    $option.attr('selected', 'selected');
                                }
                                
                                $select.append($option);
                            });
                        } else {
                            // No employees found
                            $select.append($('<option></option>').text('Không có nhân viên phù hợp'));
                        }
                    }
                },
                error: function(xhr) {
                    console.error('Error loading employees:', xhr);
                }
            });
        }
        
        // Format time from HH:MM to HHhMM
        function formatTimeSlot(time) {
            return time.replace(':', 'h');
        }

        // Load available time slots when employee or date changes
        function loadAvailableTimeSlots() {
            const employeeId = $('#employee_id').val();
            const appointmentDate = $('#appointment_date').val();
            const timeSlotGrid = $('#time_slot_grid');
            const timeSlotMessage = $('#time_slot_message');
            const timeSlotHidden = $('#time_slot');
            const wordTimeIdInput = $('#word_time_id');
            
            // Reset
            $('.time-slot-container').hide();
            $('.time-slot-slider').empty();
            timeSlotMessage.show();
            timeSlotHidden.val('');
            wordTimeIdInput.val('');
            
            // Check if date is selected
            if (!appointmentDate) {
                timeSlotMessage.text('Vui lòng chọn ngày trước');
                return;
            }
            
            // Show loading
            timeSlotMessage.text('Đang tải khung giờ...');
            
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
                    if (response.success && response.time_slots && response.time_slots.length > 0) {
                        const currentlySelectedTime = timeSlotHidden.val();
                        let availableCount = 0;
                        
                        // Sort time slots by time
                        const sortedSlots = response.time_slots.sort(function(a, b) {
                            return a.time.localeCompare(b.time);
                        });
                        
                        const $slider = $('.time-slot-slider');
                        $slider.empty();
                        
                        // Render grid (3 rows x 5 columns = 15 slots per page)
                        const slotsPerPage = 15;
                        let currentPage = null;
                        let slotIndex = 0;
                        
                        sortedSlots.forEach(function(slot) {
                            // Create new page if needed
                            if (slotIndex % slotsPerPage === 0) {
                                currentPage = $('<div></div>').addClass('time-slot-page');
                                $slider.append(currentPage);
                            }
                            
                            const isAvailable = slot.available !== false;
                            const formattedTime = formatTimeSlot(slot.time);
                            const isSelected = currentlySelectedTime === slot.time;
                            
                            const btn = $('<button></button>')
                                .attr('type', 'button')
                                .addClass('time-slot-btn')
                                .attr('data-time', slot.time)
                                .attr('data-word-time-id', slot.word_time_id)
                                .text(formattedTime);
                            
                            if (!isAvailable) {
                                btn.addClass('unavailable');
                            } else {
                                availableCount++;
                                if (isSelected) {
                                    btn.addClass('selected');
                                    timeSlotHidden.val(slot.time);
                                    wordTimeIdInput.val(slot.word_time_id);
                                }
                            }
                            
                            currentPage.append(btn);
                            slotIndex++;
                        });
                        
                        if (availableCount === 0) {
                            $('.time-slot-container').hide();
                            if (employeeId) {
                                timeSlotMessage.text('Không còn khung giờ trống trong ca làm việc của nhân viên này');
                            } else {
                                timeSlotMessage.text('Không còn khung giờ trống');
                            }
                        } else {
                            $('.time-slot-container').show();
                            timeSlotMessage.hide();
                            updateNavigationButtons();
                        }
                    } else {
                        // No time slots available
                        $('.time-slot-container').hide();
                        if (employeeId) {
                            timeSlotMessage.text('Nhân viên này không có ca làm việc vào ngày đã chọn');
                        } else {
                            timeSlotMessage.text('Vui lòng chọn kỹ thuật viên và ngày trước');
                        }
                    }
                },
                error: function(xhr) {
                    $('.time-slot-container').hide();
                    let errorMessage = 'Có lỗi xảy ra khi tải khung giờ';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    timeSlotMessage.text(errorMessage);
                }
            });
        }

        // Update navigation buttons state
        function updateNavigationButtons() {
            const $slider = $('.time-slot-slider');
            const $container = $('.time-slot-container');
            const containerWidth = $container.width();
            const sliderWidth = $slider[0].scrollWidth;
            const currentTransform = $slider.css('transform');
            
            // Parse current transform
            let currentX = 0;
            if (currentTransform && currentTransform !== 'none') {
                const matrix = currentTransform.match(/matrix\(([^)]+)\)/);
                if (matrix) {
                    currentX = parseFloat(matrix[1].split(',')[4]) || 0;
                }
            }
            
            // Show/hide buttons based on scroll position
            $('.time-slot-prev').prop('disabled', currentX >= 0);
            $('.time-slot-next').prop('disabled', Math.abs(currentX) >= sliderWidth - containerWidth - 10);
        }

        // Navigation button handlers
        $(document).on('click', '.time-slot-prev', function() {
            const $slider = $('.time-slot-slider');
            const containerWidth = $('.time-slot-container').width();
            const currentTransform = $slider.css('transform');
            
            let currentX = 0;
            if (currentTransform && currentTransform !== 'none') {
                const matrix = currentTransform.match(/matrix\(([^)]+)\)/);
                if (matrix) {
                    currentX = parseFloat(matrix[1].split(',')[4]) || 0;
                }
            }
            
            const newX = Math.min(0, currentX + containerWidth);
            $slider.css('transform', 'translateX(' + newX + 'px)');
            
            setTimeout(updateNavigationButtons, 300);
        });

        $(document).on('click', '.time-slot-next', function() {
            const $slider = $('.time-slot-slider');
            const $container = $('.time-slot-container');
            const containerWidth = $container.width();
            const sliderWidth = $slider[0].scrollWidth;
            const currentTransform = $slider.css('transform');
            
            let currentX = 0;
            if (currentTransform && currentTransform !== 'none') {
                const matrix = currentTransform.match(/matrix\(([^)]+)\)/);
                if (matrix) {
                    currentX = parseFloat(matrix[1].split(',')[4]) || 0;
                }
            }
            
            const maxX = -(sliderWidth - containerWidth);
            const newX = Math.max(maxX, currentX - containerWidth);
            $slider.css('transform', 'translateX(' + newX + 'px)');
            
            setTimeout(updateNavigationButtons, 300);
        });
        
        // Handle time slot button click
        $(document).on('click', '.time-slot-btn:not(.unavailable)', function() {
            // Remove previous selection
            $('.time-slot-btn').removeClass('selected');
            
            // Add selection to clicked button
            $(this).addClass('selected');
            
            // Set hidden inputs
            const time = $(this).data('time');
            const wordTimeId = $(this).data('word-time-id');
            $('#time_slot').val(time);
            $('#word_time_id').val(wordTimeId);
        });
        
        // Handle employee selection change
        $('#employee_id').on('change', function() {
            loadAvailableTimeSlots();
        });
        
        // Handle date selection change
        $('#appointment_date').on('change', function() {
            loadAvailableTimeSlots();
        });
        
        // Flag to prevent multiple submissions
        let isSubmitting = false;
        
        // Handle form submission via AJAX (remove previous listeners to prevent duplicates)
        $('#appointmentForm').off('submit').on('submit', function(e) {
            e.preventDefault();
            
            // Prevent multiple submissions
            if (isSubmitting) {
                return false;
            }
            
            // Validate time slot
            const timeSlot = $('#time_slot').val();
            if (!timeSlot) {
                // Remove previous messages
                $('.alert-danger, .alert-warning').remove();
                
                // Show error message on form instead of alert
                $('#appointmentForm').prepend(
                    '<div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin-bottom: 20px; border-left: 4px solid #dc3545; background-color: #f8d7da; color: #721c24; padding: 15px 20px; border-radius: 5px;">' +
                    '<strong><i class="fa fa-exclamation-triangle"></i> Lỗi!</strong><br>' +
                    'Vui lòng chọn khung giờ trước khi đặt lịch.' +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="float: right; border: none; background: none; font-size: 20px; cursor: pointer; color: #721c24;">&times;</button>' +
                    '</div>'
                );
                
                // Scroll to top to show message
                $('html, body').animate({ scrollTop: 0 }, 300);
                
                return false;
            }
            
            // Remove previous messages
            $('.success-message, .error-message').remove();
            
            // Set submitting flag
            isSubmitting = true;
            
            // Disable submit button to prevent double submission
            const $submitBtn = $('.submit-appointment-btn');
            const originalBtnText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Đang xử lý...');
            
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
                        // Remove any previous messages
                        $('.alert-success, .alert-danger, .alert-warning').remove();
                        
                        // Extract only the text message without icon
                        let messageText = response.message.replace(/<i[^>]*>.*?<\/i>/gi, '').trim();
                        
                        // Show success message with better styling
                        $('#appointmentForm').prepend(
                            '<div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-bottom: 20px; border-left: 4px solid #28a745; background-color: #d4edda; color: #155724; padding: 15px 20px; border-radius: 5px;">' +
                            messageText +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="float: right; border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>' +
                            '</div>'
                        );
                        
                        // Scroll to top to show message
                        $('html, body').animate({ scrollTop: 0 }, 300);
                        
                        // Prevent any further submissions
                        isSubmitting = true;
                        $('#appointmentForm').off('submit');
                        
                        // Redirect to cart page after 3 seconds
                        setTimeout(function() {
                            if (response.redirect_url) {
                                window.location.href = response.redirect_url;
                            } else {
                                window.location.href = '{{ route("site.cart.index") }}';
                            }
                        }, 3000);
                    } else {
                        // Re-enable button if not successful
                        isSubmitting = false;
                        $submitBtn.prop('disabled', false).html(originalBtnText);
                    }
                },
                error: function(xhr) {
                    // Re-enable button on error
                    isSubmitting = false;
                    $submitBtn.prop('disabled', false).html(originalBtnText);
                    let errorMessage = 'Có lỗi xảy ra khi đặt lịch. Vui lòng thử lại.';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        let errors = '';
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            errors += value[0] + '<br>';
                        });
                        errorMessage = errors;
                    }
                    
                    $('#appointmentForm').prepend(
                        '<div class="alert alert-danger">' + errorMessage + '</div>'
                    );
                    
                    // Scroll to top to show error
                    $('html, body').animate({
                        scrollTop: 0
                    }, 500);
                }
            });
        });
    });
</script>
@endpush

