@extends('layouts.site')

@section('title', 'Đặt lịch ngay')

@section('content')
<div class="appointment-page" style="padding: 100px 0 50px; background: #f8f9fa; min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="appointment-form-container" style="background: #fff; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); padding: 40px; margin-bottom: 30px;">
                    
                    <!-- Header -->
                    <div class="text-center mb-5" style="margin-top: 30px;">
                        <h2 class="fw-bold mb-2" style="color: #000; font-size: 32px;">
                            <i class="fa fa-calendar-check-o"></i> ĐẶT LỊCH NGAY
                        </h2>
                        <p class="text-muted" style="font-size: 16px; color: #000;">Hãy liên hệ ngay với chúng tôi để được tư vấn sớm nhất về các mẫu tóc hot nhất hiện nay!</p>
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

                        <!-- Thông tin khách hàng -->
                        <div class="mb-4">
                            <h5 class="fw-semibold mb-3" style="color: #000;">
                                <i class="fa fa-user"></i> Thông tin khách hàng
                            </h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
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

                                <div class="col-md-6 mb-3">
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
                            <h5 class="fw-semibold mb-3" style="color: #000;">
                                <i class="fa fa-scissors"></i> DỊCH VỤ <span class="text-danger">*</span>
                            </h5>

                            <!-- Select box 1: Chọn danh mục dịch vụ -->
                            <div class="mb-3 custom-select-wrapper" id="category-select-wrapper">
                                <div class="custom-select-input">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="custom-select-text">Chọn danh mục dịch vụ</span>
                                        <i class="fa fa-chevron-down" style="font-size: 12px;"></i>
                                    </div>
                                </div>
                                <div class="custom-select-dropdown" style="display: none; position: absolute; background: #fff; border: 1px solid #ddd; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); z-index: 1000; max-height: 300px; overflow-y: auto; width: 100%; margin-top: 2px;">
                                    <div class="custom-select-option" data-value="" style="padding: 10px 15px; cursor: pointer; color: #000; border-bottom: 1px solid #f0f0f0;">
                                        Chọn danh mục dịch vụ
                                    </div>
                                    @foreach($serviceCategories as $category)
                                        <div class="custom-select-option category-option" 
                                             data-value="{{ $category->id }}"
                                             data-category-name="{{ $category->name }}"
                                             style="padding: 10px 15px; cursor: pointer; color: #000; border-bottom: 1px solid #f0f0f0;">
                                            {{ $category->name }}
                                        </div>
                                    @endforeach
                                </div>
                                <input type="hidden" id="selected-category-id" value="">
                            </div>

                            <!-- Select box 2: Hiển thị tên danh mục đã chọn, dropdown hiển thị các dịch vụ -->
                            <div class="mb-3 custom-select-wrapper" id="service-select-wrapper" style="display: none;">
                                <div class="custom-select-input">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="custom-select-text">Chọn dịch vụ</span>
                                        <i class="fa fa-chevron-down" style="font-size: 12px;"></i>
                                    </div>
                                </div>
                                <div class="custom-select-dropdown" id="service-dropdown" style="display: none; position: absolute; background: #fff; border: 1px solid #ddd; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); z-index: 1000; max-height: 300px; overflow-y: auto; width: 100%; margin-top: 2px;">
                                    <div class="custom-select-option" data-value="" style="padding: 10px 15px; cursor: pointer; color: #000; border-bottom: 1px solid #f0f0f0;">
                                        Chọn dịch vụ
                                    </div>
                                </div>
                                <input type="hidden" id="selected-service-id" value="">
                            </div>

                            <!-- Select box 3: Hiển thị tên dịch vụ đã chọn, dropdown hiển thị các biến thể -->
                            <div class="mb-3 custom-select-wrapper" id="variant-select-wrapper" style="display: none;">
                                <div class="custom-select-input">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="custom-select-text">Chọn biến thể</span>
                                        <i class="fa fa-chevron-down" style="font-size: 12px;"></i>
                                    </div>
                                </div>
                                <div class="custom-select-dropdown" id="variant-dropdown" style="display: none; position: absolute; background: #fff; border: 1px solid #ddd; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); z-index: 1000; max-height: 300px; overflow-y: auto; width: 100%; margin-top: 2px;">
                                    <div class="custom-select-option" data-value="" style="padding: 10px 15px; cursor: pointer; color: #000; border-bottom: 1px solid #f0f0f0;">
                                        Chọn biến thể
                                    </div>
                                </div>
                                <input type="hidden" id="selected-variant-id" value="">
                            </div>

                            <!-- Danh sách các biến thể đã chọn -->
                            <div class="selected-variants-list" id="selected-variants-list">
                            </div>
                        </div>

                        <!-- Chọn combo -->
                        <div class="mb-4">
                            <h5 class="fw-semibold mb-3" style="color: #000;">
                                <i class="fa fa-scissors"></i> COMBO
                            </h5>

                            <!-- Select box chọn combo -->
                            <div class="mb-3 custom-select-wrapper" id="combo-select-wrapper">
                                <div class="custom-select-input">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="custom-select-text">Chọn combo</span>
                                        <i class="fa fa-chevron-down" style="font-size: 12px;"></i>
                                    </div>
                                </div>
                                <div class="custom-select-dropdown" id="combo-dropdown" style="display: none; position: absolute; background: #fff; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); z-index: 1000; max-height: 300px; overflow-y: auto; width: 100%; margin-top: 2px;">
                                    <div class="custom-select-option" data-value="" style="padding: 10px 15px; cursor: pointer; color: #000; border-bottom: 1px solid #f0f0f0;">
                                        Chọn combo
                                    </div>
                                    @forelse($combos as $combo)
                                        @php
                                            // Combo model có price, không có base_price
                                            $formattedPrice = number_format($combo->price ?? 0, 0, ',', '.');
                                            // Tính duration từ combo items hoặc dùng mặc định
                                            $duration = 60; // Default duration
                                            if ($combo->comboItems && $combo->comboItems->count() > 0) {
                                                $duration = $combo->comboItems->sum(function($item) {
                                                    return $item->serviceVariant->duration ?? 60;
                                                });
                                            }
                                            $displayText = $combo->name . ' - ' . $formattedPrice . 'đ (' . $duration . ' phút)';
                                        @endphp
                                        <div class="custom-select-option combo-option" 
                                             data-value="{{ $combo->id }}"
                                             data-combo-name="{{ $combo->name }}"
                                             data-combo-price="{{ $combo->price ?? 0 }}"
                                             data-combo-duration="{{ $duration }}"
                                             data-description="{{ $combo->description ?? '' }}"
                                             style="padding: 10px 15px; cursor: pointer; color: #000; border-bottom: 1px solid #f0f0f0;">
                                            {{ $displayText }}
                                        </div>
                                    @empty
                                        <div class="custom-select-option" style="padding: 10px 15px; color: #999; font-style: italic; border-bottom: 1px solid #f0f0f0;">
                                            Không có combo nào
                                        </div>
                                    @endforelse
                                </div>
                                <input type="hidden" id="selected-combo-id" value="">
                            </div>

                            <!-- Danh sách combo đã chọn -->
                            <div class="selected-combos-list" id="selected-combos-list">
                                @if(old('combo_id'))
                                    @php
                                        $oldCombo = \App\Models\Combo::find(old('combo_id'));
                                    @endphp
                                    @if($oldCombo)
                                        @php
                                            $formattedPrice = number_format($oldCombo->price ?? 0, 0, ',', '.');
                                            $duration = 60; // Default duration
                                            if ($oldCombo->comboItems && $oldCombo->comboItems->count() > 0) {
                                                $duration = $oldCombo->comboItems->sum(function($item) {
                                                    return $item->serviceVariant->duration ?? 60;
                                                });
                                            }
                                            $displayText = $oldCombo->name . ' - ' . $formattedPrice . 'đ (' . $duration . ' phút)';
                                        @endphp
                                        <div class="mb-2 selected-combo-item" data-combo-id="{{ $oldCombo->id }}">
                                            <input type="hidden" name="combo_id" value="{{ $oldCombo->id }}">
                                            <div class="d-flex justify-content-between align-items-center border rounded p-2 bg-light">
                                                <span style="color: #000;"><strong>Combo:</strong> {{ $displayText }}</span>
                                                <button type="button" class="btn btn-sm btn-link text-danger remove-combo" data-combo-id="{{ $oldCombo->id }}">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <!-- Kỹ thuật viên -->
                        <div class="mb-4">
                            <h5 class="fw-semibold mb-3" style="color: #000;">
                                <i class="fa fa-users"></i> KỸ THUẬT VIÊN
                            </h5>

                            <select name="employee_id" id="employee_id" class="form-select">
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
                        <div class="mb-4">
                            <h5 class="fw-semibold mb-3" style="color: #000;">
                                <i class="fa fa-clock-o"></i> CHỌN NGÀY GIỜ
                            </h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fa fa-calendar"></i> Ngày đặt lịch <span class="text-danger">*</span>
                                    </label>
                                    <input type="date"
                                           name="appointment_date"
                                           id="appointment_date"
                                           class="form-control"
                                           value="{{ old('appointment_date') }}"
                                           min="{{ date('Y-m-d') }}"
                                           required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fa fa-clock-o"></i> Chọn giờ <span class="text-danger">*</span>
                                    </label>
                                    <div id="time_slot_grid" class="time-slot-grid" style="display: none;">
                                        <!-- Time slots will be rendered here -->
                                    </div>
                                    <div id="time_slot_message" class="text-muted" style="padding: 10px; color: #000;">
                                        Vui lòng chọn kỹ thuật viên và ngày trước
                                    </div>
                                    <input type="hidden" name="time_slot" id="time_slot" value="">
                                    <input type="hidden" name="word_time_id" id="word_time_id" value="">
                                </div>
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
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary px-5 py-3 submit-appointment-btn" style="background: #000; border: none; font-size: 16px; font-weight: 600; min-width: 200px; color: #fff; transition: all 0.3s ease;">
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

    /* Time Slot Grid */
    .time-slot-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
        gap: 10px;
        margin-top: 10px;
    }

    .time-slot-btn {
        padding: 10px 15px;
        border: 1px solid #000;
        border-radius: 8px;
        background: #fff;
        color: #000;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        min-width: 80px;
    }

    .time-slot-btn:hover {
        background: #f5f5f5;
        transform: translateY(-2px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .time-slot-btn.selected {
        background: #000;
        color: #fff;
        border-color: #000;
    }

    .time-slot-btn.unavailable {
        background: #e0e0e0;
        color: #9e9e9e;
        border: none;
        cursor: not-allowed;
    }

    .time-slot-btn.unavailable:hover {
        background: #e0e0e0;
        transform: none;
        box-shadow: none;
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
        
        // Combo Select Box functionality
        $('#combo-select-wrapper .custom-select-input').on('click', function(e) {
            e.stopPropagation();
            const $dropdown = $('#combo-select-wrapper .custom-select-dropdown');
            const $input = $(this);
            
            // Close other dropdowns
            $('.custom-select-dropdown').not($dropdown).hide();
            $('.custom-select-input').not($input).removeClass('active');
            
            // Toggle current dropdown
            $dropdown.toggle();
            $input.toggleClass('active');
        });
        
        // Handle combo option click
        $(document).on('click', '#combo-select-wrapper .combo-option', function() {
            const $option = $(this);
            const comboId = $option.data('value');
            const comboName = $option.data('combo-name');
            const comboPrice = $option.data('combo-price') || 0;
            const comboDuration = $option.data('combo-duration') || 60;
            const description = $option.data('description') || '';
            
            if (!comboId) {
                // Reset
                $('#combo-select-wrapper .custom-select-text').text('Chọn combo');
                $('#selected-combo-id').val('');
                $('#selected-combos-list').empty();
                $('#selected-combo-id-input').remove();
                return;
            }
            
            // Format price for display (for selected list only)
            const formattedPrice = parseFloat(comboPrice).toLocaleString('vi-VN');
            const displayText = comboName + ' - ' + formattedPrice + 'đ (' + comboDuration + ' phút)';
            
            // Update combo select box with combo name only
            $('#combo-select-wrapper .custom-select-text').text(comboName);
            $('#selected-combo-id').val(comboId);
            $('#combo-select-wrapper .custom-select-dropdown').find('.custom-select-option').removeClass('selected');
            $option.addClass('selected');
            $('#combo-select-wrapper .custom-select-dropdown').hide();
            $('#combo-select-wrapper .custom-select-input').removeClass('active');
            
            // Add hidden input for combo_id
            $('#selected-combo-id-input').remove();
            $('#appointmentForm').append('<input type="hidden" id="selected-combo-id-input" name="combo_id" value="' + comboId + '">');
            
            // Add combo to selected list (same format as service)
            const $list = $('#selected-combos-list');
            // Remove any existing combo item
            $list.find('.selected-combo-item').remove();
            
            // Add combo item (same style as service item)
            const $comboItem = $('<div class="mb-2 selected-combo-item" data-combo-id="' + comboId + '">' +
                '<div class="d-flex justify-content-between align-items-center border rounded p-2 bg-light">' +
                '<span style="color: #000;"><strong>Combo:</strong> ' + displayText + '</span>' +
                '<button type="button" class="btn btn-sm btn-link text-danger remove-combo" data-combo-id="' + comboId + '">' +
                '<i class="fa fa-times"></i>' +
                '</button>' +
                '</div>' +
                '</div>');
            $list.append($comboItem);
        });
        
        // Handle remove combo
        $(document).on('click', '.remove-combo', function() {
            const $btn = $(this);
            const comboId = $btn.data('combo-id');
            $btn.closest('.selected-combo-item').remove();
            $('#selected-combo-id-input').remove();
            
            // Reset combo select
            $('#combo-select-wrapper .custom-select-text').text('Chọn combo');
            $('#selected-combo-id').val('');
        });
        
        // Custom Select Box functionality - Category Select
        $('#category-select-wrapper .custom-select-input').on('click', function(e) {
            e.stopPropagation();
            const $dropdown = $('#category-select-wrapper .custom-select-dropdown');
            const $input = $(this);
            
            // Close other dropdowns
            $('.custom-select-dropdown').not($dropdown).hide();
            $('.custom-select-input').not($input).removeClass('active');
            
            // Toggle current dropdown
            $dropdown.toggle();
            $input.toggleClass('active');
        });
        
        // Handle category option click
        $(document).on('click', '#category-select-wrapper .category-option', function() {
            const $option = $(this);
            const categoryId = $option.data('value');
            const categoryName = $option.data('category-name');
            
            if (!categoryId) {
                // Reset
                $('#category-select-wrapper .custom-select-text').text('Chọn danh mục dịch vụ');
                $('#selected-category-id').val('');
                $('#service-select-wrapper').hide();
                $('#variant-select-wrapper').hide();
                $('#service-select-wrapper .custom-select-text').text('Chọn dịch vụ');
                $('#variant-select-wrapper .custom-select-text').text('Chọn biến thể');
                $('#selected-service-id').val('');
                $('#selected-variant-id').val('');
                $('#selected-variants-list').empty();
                return;
            }
            
            // Update category select box
            $('#category-select-wrapper .custom-select-text').text(categoryName);
            $('#selected-category-id').val(categoryId);
            $('#category-select-wrapper .custom-select-dropdown').find('.custom-select-option').removeClass('selected');
            $option.addClass('selected');
            $('#category-select-wrapper .custom-select-dropdown').hide();
            $('#category-select-wrapper .custom-select-input').removeClass('active');
            
            // Show service select box with category name
            $('#service-select-wrapper').show();
            $('#service-select-wrapper .custom-select-text').text(categoryName);
            
            // Reset service and variant selects
            $('#variant-select-wrapper').hide();
            $('#service-select-wrapper .custom-select-dropdown').html('<div class="custom-select-option" data-value="" style="padding: 10px 15px; cursor: pointer; color: #000; border-bottom: 1px solid #f0f0f0;">Chọn dịch vụ</div>');
            $('#variant-select-wrapper .custom-select-dropdown').html('<div class="custom-select-option" data-value="" style="padding: 10px 15px; cursor: pointer; color: #000; border-bottom: 1px solid #f0f0f0;">Chọn biến thể</div>');
            $('#selected-service-id').val('');
            $('#selected-variant-id').val('');
            $('#selected-variants-list').empty();
            $('#selected-service-id-input').remove();
        });
        
        // Service Select Box - Click to load services
        $('#service-select-wrapper .custom-select-input').on('click', function(e) {
            e.stopPropagation();
            const categoryId = $('#selected-category-id').val();
            
            if (!categoryId) {
                // Silently return - user should select category first
                return;
            }
            
            const $dropdown = $('#service-select-wrapper .custom-select-dropdown');
            const $input = $(this);
            
            // If dropdown already has services, just toggle
            if ($dropdown.find('.service-option').length > 0) {
                $('.custom-select-dropdown').not($dropdown).hide();
                $('.custom-select-input').not($input).removeClass('active');
                $dropdown.toggle();
                $input.toggleClass('active');
                return;
            }
            
            // Load services via AJAX
            $.ajax({
                url: '{{ route("site.appointment.services-by-category") }}',
                method: 'GET',
                data: { category_id: categoryId },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.services) {
                        $dropdown.html('<div class="custom-select-option" data-value="" style="padding: 10px 15px; cursor: pointer; color: #000; border-bottom: 1px solid #f0f0f0;">Chọn dịch vụ</div>');
                        
                        response.services.forEach(function(service) {
                            // Check if service has variants
                            const hasVariants = service.variants && service.variants.length > 0;
                            
                            // If service has variants, only show name. If no variants, show name + price + duration
                            let displayText;
                            if (hasVariants) {
                                // Service with variants: only show name
                                displayText = service.name;
                            } else {
                                // Service without variants (single service): show name + price + duration
                                const formattedPrice = parseFloat(service.base_price || 0).toLocaleString('vi-VN');
                                displayText = service.name + ' - ' + formattedPrice + 'đ (' + (service.base_duration || 60) + ' phút)';
                            }
                            
                            const $option = $('<div class="custom-select-option service-option" data-value="' + service.id + '" data-service-name="' + service.name + '" data-base-price="' + (service.base_price || 0) + '" data-base-duration="' + (service.base_duration || 60) + '" data-has-variants="' + (hasVariants ? '1' : '0') + '" style="padding: 10px 15px; cursor: pointer; color: #000; border-bottom: 1px solid #f0f0f0;">' + displayText + '</div>');
                            $dropdown.append($option);
                        });
                        
                        // Show dropdown
                        $('.custom-select-dropdown').not($dropdown).hide();
                        $('.custom-select-input').not($input).removeClass('active');
                        $dropdown.show();
                        $input.addClass('active');
                    }
                },
                error: function(xhr) {
                    // Silently handle error - services may not be available
                    console.error('Error loading services:', xhr);
                }
            });
        });
        
        // Handle service option click
        $(document).on('click', '#service-select-wrapper .service-option', function() {
            const $option = $(this);
            const serviceId = $option.data('value');
            const serviceName = $option.data('service-name');
            const basePrice = $option.data('base-price') || 0;
            const baseDuration = $option.data('base-duration') || 60;
            
            if (!serviceId) {
                // Reset
                const categoryName = $('#category-select-wrapper .custom-select-text').text();
                $('#service-select-wrapper .custom-select-text').text(categoryName);
                $('#selected-service-id').val('');
                $('#variant-select-wrapper').hide();
                $('#selected-variants-list').empty();
                $('#selected-service-id-input').remove();
                return;
            }
            
            // Format price for display (for selected list only)
            const formattedPrice = parseFloat(basePrice).toLocaleString('vi-VN');
            const displayText = serviceName + ' - ' + formattedPrice + 'đ (' + baseDuration + ' phút)';
            
            // Update service select box with service name only
            $('#service-select-wrapper .custom-select-text').text(serviceName);
            $('#selected-service-id').val(serviceId);
            $('#service-select-wrapper .custom-select-dropdown').find('.custom-select-option').removeClass('selected');
            $option.addClass('selected');
            $('#service-select-wrapper .custom-select-dropdown').hide();
            $('#service-select-wrapper .custom-select-input').removeClass('active');
            
            // Remove any existing variants first (service takes priority when selected)
            $('#selected-variants-list').find('.selected-variant-item').remove();
            $('input[name="service_variants[]"]').remove();
            
            // Add hidden input for service_id (always create new to ensure it exists)
            $('#selected-service-id-input').remove();
            $('#appointmentForm').append('<input type="hidden" id="selected-service-id-input" name="service_id" value="' + serviceId + '">');
            
            // Add service to selected list (dịch vụ đơn)
            const $list = $('#selected-variants-list');
            // Remove any existing service item
            $list.find('.selected-service-item').remove();
            
            // Add service item
            const $serviceItem = $('<div class="mb-2 selected-service-item" data-service-id="' + serviceId + '">' +
                '<div class="d-flex justify-content-between align-items-center border rounded p-2 bg-light">' +
                '<span style="color: #000;"><strong>Dịch vụ đơn:</strong> ' + displayText + '</span>' +
                '<button type="button" class="btn btn-sm btn-link text-danger remove-service" data-service-id="' + serviceId + '">' +
                '<i class="fa fa-times"></i>' +
                '</button>' +
                '</div>' +
                '</div>');
            $list.append($serviceItem);
            
            // Show variant select box with service name (optional)
            $('#variant-select-wrapper').show();
            $('#variant-select-wrapper .custom-select-text').text(serviceName);
            
            // Load variants for this service
            const categoryId = $('#selected-category-id').val();
            $.ajax({
                url: '{{ route("site.appointment.services-by-category") }}',
                method: 'GET',
                data: { category_id: categoryId },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.services) {
                        const service = response.services.find(s => s.id == serviceId);
                        if (service && service.variants && service.variants.length > 0) {
                            const $variantDropdown = $('#variant-select-wrapper .custom-select-dropdown');
                            $variantDropdown.html('<div class="custom-select-option" data-value="" style="padding: 10px 15px; cursor: pointer; color: #000; border-bottom: 1px solid #f0f0f0;">Chọn biến thể</div>');
                            
                            service.variants.forEach(function(variant) {
                                const formattedPrice = parseFloat(variant.price).toLocaleString('vi-VN');
                                const $option = $('<div class="custom-select-option variant-option" data-value="' + variant.id + '" data-variant-id="' + variant.id + '" data-price="' + variant.price + '" data-duration="' + variant.duration + '" data-variant-name="' + variant.name + '" style="padding: 10px 15px; cursor: pointer; color: #000; border-bottom: 1px solid #f0f0f0;">' + variant.name + ' - ' + formattedPrice + 'đ (' + variant.duration + ' phút)</div>');
                                $variantDropdown.append($option);
                            });
                        } else {
                            // No variants, hide variant select box but keep service_id
                            $('#variant-select-wrapper').hide();
                        }
                    }
                }
            });
        });
        
        // Variant Select Box - Click to show variants
        $('#variant-select-wrapper .custom-select-input').on('click', function(e) {
            e.stopPropagation();
            const serviceId = $('#selected-service-id').val();
            
            if (!serviceId) {
                // Silently return - user should select service first
                return;
            }
            
            const $dropdown = $('#variant-select-wrapper .custom-select-dropdown');
            const $input = $(this);
            
            // Close other dropdowns
            $('.custom-select-dropdown').not($dropdown).hide();
            $('.custom-select-input').not($input).removeClass('active');
            
            // Toggle current dropdown
            $dropdown.toggle();
            $input.toggleClass('active');
        });
        
        // Handle variant option click
        $(document).on('click', '#variant-select-wrapper .variant-option', function() {
            const $option = $(this);
            const variantId = $option.data('variant-id');
            const variantName = $option.data('variant-name');
            const price = $option.data('price');
            const duration = $option.data('duration');
            
            if (!variantId) {
                // Reset
                const serviceName = $('#service-select-wrapper .custom-select-text').text();
                $('#variant-select-wrapper .custom-select-text').text(serviceName);
                $('#selected-variant-id').val('');
                return;
            }
            
            // Check if variant already selected
            const $list = $('#selected-variants-list');
            if ($list.find('.selected-variant-item[data-variant-id="' + variantId + '"]').length > 0) {
                // Variant already selected - silently return
                return;
            }
            
            // Update variant select box
            $('#variant-select-wrapper .custom-select-text').text(variantName);
            $('#selected-variant-id').val(variantId);
            $('#variant-select-wrapper .custom-select-dropdown').find('.custom-select-option').removeClass('selected');
            $option.addClass('selected');
            $('#variant-select-wrapper .custom-select-dropdown').hide();
            $('#variant-select-wrapper .custom-select-input').removeClass('active');
            
            // Remove service item if variant is selected (variants take priority)
            const serviceId = $('#selected-service-id').val();
            if (serviceId) {
                $('#selected-variants-list').find('.selected-service-item[data-service-id="' + serviceId + '"]').remove();
                $('#selected-service-id-input').remove();
            }
            
            // Add to selected variants list
            const formattedPrice = parseFloat(price).toLocaleString('vi-VN');
            const $item = $('<div class="mb-2 selected-variant-item" data-variant-id="' + variantId + '">' +
                '<input type="hidden" name="service_variants[]" value="' + variantId + '">' +
                '<div class="d-flex justify-content-between align-items-center border rounded p-2 bg-light">' +
                '<span style="color: #000;">' + variantName + ' - ' + formattedPrice + 'đ (' + duration + ' phút)</span>' +
                '<button type="button" class="btn btn-sm btn-link text-danger remove-variant" data-variant-id="' + variantId + '">' +
                '<i class="fa fa-times"></i>' +
                '</button>' +
                '</div>' +
                '</div>');
            
            $list.append($item);
            
            // Reset variant select box after adding
            setTimeout(function() {
                const serviceName = $('#service-select-wrapper .custom-select-text').text();
                $('#variant-select-wrapper .custom-select-text').text(serviceName);
                $('#selected-variant-id').val('');
                $('#variant-select-wrapper .custom-select-dropdown').find('.custom-select-option').removeClass('selected');
            }, 100);
        });
        
        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.custom-select-wrapper').length) {
                $('.custom-select-dropdown').hide();
                $('.custom-select-input').removeClass('active');
            }
        });
        
        // Handle remove variant
        $(document).on('click', '.remove-variant', function() {
            const $btn = $(this);
            const variantId = $btn.data('variant-id');
            $btn.closest('.selected-variant-item').remove();
            
            // If no variants left, restore service item if service is still selected
            const remainingVariants = $('input[name="service_variants[]"]').length;
            if (remainingVariants === 0) {
                const serviceId = $('#selected-service-id').val();
                if (serviceId) {
                    // Get service info from the selected option
                    const $serviceOption = $('#service-select-wrapper .service-option[data-value="' + serviceId + '"]');
                    if ($serviceOption.length > 0) {
                        const serviceName = $serviceOption.data('service-name');
                        const basePrice = $serviceOption.data('base-price') || 0;
                        const baseDuration = $serviceOption.data('base-duration') || 60;
                        const formattedPrice = parseFloat(basePrice).toLocaleString('vi-VN');
                        const displayText = serviceName + ' - ' + formattedPrice + 'đ (' + baseDuration + ' phút)';
                        
                        // Restore service_id input
                        if ($('#selected-service-id-input').length === 0) {
                            $('#appointmentForm').append('<input type="hidden" id="selected-service-id-input" name="service_id" value="' + serviceId + '">');
                        } else {
                            $('#selected-service-id-input').val(serviceId);
                        }
                        
                        // Restore service item
                        if ($('#selected-variants-list').find('.selected-service-item[data-service-id="' + serviceId + '"]').length === 0) {
                            const $serviceItem = $('<div class="mb-2 selected-service-item" data-service-id="' + serviceId + '">' +
                                '<div class="d-flex justify-content-between align-items-center border rounded p-2 bg-light">' +
                                '<span style="color: #000;"><strong>Dịch vụ đơn:</strong> ' + displayText + '</span>' +
                                '<button type="button" class="btn btn-sm btn-link text-danger remove-service" data-service-id="' + serviceId + '">' +
                                '<i class="fa fa-times"></i>' +
                                '</button>' +
                                '</div>' +
                                '</div>');
                            $('#selected-variants-list').append($serviceItem);
                        }
                    }
                }
            }
        });
        
        // Handle remove service
        $(document).on('click', '.remove-service', function() {
            const $btn = $(this);
            const serviceId = $btn.data('service-id');
            $btn.closest('.selected-service-item').remove();
            $('#selected-service-id-input').remove();
            
            // Reset service select
            const categoryName = $('#category-select-wrapper .custom-select-text').text();
            $('#service-select-wrapper .custom-select-text').text(categoryName);
            $('#selected-service-id').val('');
            $('#variant-select-wrapper').hide();
        });
        
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
            timeSlotGrid.hide().empty();
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
                        
                        // Render grid
                        sortedSlots.forEach(function(slot) {
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
                            
                            timeSlotGrid.append(btn);
                        });
                        
                        if (availableCount === 0) {
                            timeSlotGrid.hide();
                            if (employeeId) {
                                timeSlotMessage.text('Không còn khung giờ trống trong ca làm việc của nhân viên này');
                            } else {
                                timeSlotMessage.text('Không còn khung giờ trống');
                            }
                        } else {
                            timeSlotGrid.show();
                            timeSlotMessage.hide();
                        }
                    } else {
                        // No time slots available
                        timeSlotGrid.hide();
                        if (employeeId) {
                            timeSlotMessage.text('Nhân viên này không có ca làm việc vào ngày đã chọn');
                        } else {
                            timeSlotMessage.text('Vui lòng chọn kỹ thuật viên và ngày trước');
                        }
                    }
                },
                error: function(xhr) {
                    timeSlotGrid.hide();
                    let errorMessage = 'Có lỗi xảy ra khi tải khung giờ';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    timeSlotMessage.text(errorMessage);
                }
            });
        }
        
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
            
            // Prepare service_id input if service item exists
            const serviceVariants = $('input[name="service_variants[]"]');
            const serviceIdInput = $('#selected-service-id-input');
            const hasServiceItem = $('.selected-service-item').length > 0;
            
            // If no service_id input but has service item, create it from the item
            if (serviceVariants.length === 0 && hasServiceItem) {
                const serviceIdFromItem = $('.selected-service-item').first().data('service-id');
                if (serviceIdFromItem) {
                    // Remove old input if exists
                    $('#selected-service-id-input').remove();
                    // Create new input
                    $('#appointmentForm').append('<input type="hidden" id="selected-service-id-input" name="service_id" value="' + serviceIdFromItem + '">');
                }
            }
            
            // If variants are selected, remove service_id to avoid confusion
            if (serviceVariants.length > 0) {
                $('#selected-service-id-input').remove();
                $('.selected-service-item').remove();
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

