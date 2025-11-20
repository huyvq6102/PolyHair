@php
    $employees = app(\App\Services\EmployeeService::class)->getAll()->filter(function($employee) {
        return ($employee->status === 'active' || !$employee->status) && $employee->user;
    });
    $wordTimes = app(\App\Services\WordTimeService::class)->getAll();
    $serviceVariants = \App\Models\ServiceVariant::with('service')->get();
@endphp

<!-- Appointment Form Popup -->
<div id="test-form" class="mfp-hide white-popup-block">
    <div class="popup_box appointment-popup">
        <div class="popup_inner">
            <div class="form-header">
                <h3><i class="fa fa-calendar-check-o"></i> Đặt lịch cắt tóc</h3>
                <p class="form-subtitle">Điền thông tin để đặt lịch hẹn với chúng tôi</p>
            </div>
            
            <form action="{{ route('site.appointment.store') }}" method="POST" id="appointmentForm">
                @csrf
                
                <!-- Thông tin khách hàng -->
                <div class="form-section">
                    <h4 class="section-title"><i class="fa fa-user"></i> Thông tin khách hàng</h4>
                    
                    <div class="form-group">
                        <label for="name" class="form-label">
                            <i class="fa fa-user-circle"></i> Họ và tên <span class="required">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               class="form-input" 
                               placeholder="Nhập họ và tên của bạn" 
                               value="{{ old('name', auth()->user()->name ?? '') }}" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="form-label">
                            <i class="fa fa-phone"></i> Số điện thoại <span class="required">*</span>
                        </label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               class="form-input" 
                               placeholder="Nhập số điện thoại" 
                               value="{{ old('phone', auth()->user()->phone ?? '') }}" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fa fa-envelope"></i> Email
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-input" 
                               placeholder="Nhập email (tùy chọn)" 
                               value="{{ old('email', auth()->user()->email ?? '') }}">
                    </div>
                </div>
                
                <!-- Chọn dịch vụ -->
                <div class="form-section">
                    <h4 class="section-title"><i class="fa fa-scissors"></i> Chọn dịch vụ <span class="required">*</span></h4>
                    <div class="service-variants-container">
                        @php
                            $groupedVariants = $serviceVariants->groupBy('service_id');
                        @endphp
                        @foreach($groupedVariants as $serviceId => $variants)
                            @php
                                $service = $variants->first()->service;
                            @endphp
                            <div class="service-group">
                                <div class="service-category">
                                    <i class="flaticon-scissors"></i> {{ $service->name ?? 'Dịch vụ' }}
                                </div>
                                <div class="variants-list">
                                    @foreach($variants as $variant)
                                        <label class="variant-item">
                                            <input type="checkbox" 
                                                   name="service_variants[]" 
                                                   value="{{ $variant->id }}"
                                                   {{ in_array($variant->id, old('service_variants', [])) ? 'checked' : '' }}
                                                   class="variant-checkbox">
                                            <div class="variant-content">
                                                <span class="variant-name">{{ $variant->name }}</span>
                                                <div class="variant-meta">
                                                    <span class="variant-price">{{ number_format($variant->price, 0, ',', '.') }}đ</span>
                                                    <span class="variant-duration">
                                                        <i class="fa fa-clock-o"></i> {{ $variant->duration ?? 60 }} phút
                                                    </span>
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Chọn nhân viên -->
                <div class="form-section">
                    <h4 class="section-title"><i class="fa fa-users"></i> Chọn nhân viên <span class="optional">(Tùy chọn)</span></h4>
                    <div class="form-group">
                        <select name="employee_id" id="employee_id" class="form-select">
                            <option value="">Không chọn - Để chúng tôi sắp xếp</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->user->name ?? 'Nhân viên' }}
                                    @if($employee->position) - {{ $employee->position }} @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <!-- Chọn thời gian -->
                <div class="form-section">
                    <h4 class="section-title"><i class="fa fa-clock-o"></i> Chọn thời gian</h4>
                    
                    <div class="form-group">
                        <label for="appointment_date" class="form-label">
                            <i class="fa fa-calendar"></i> Ngày đặt lịch <span class="required">*</span>
                        </label>
                        <input type="date" 
                               id="appointment_date" 
                               name="appointment_date" 
                               class="form-input" 
                               value="{{ old('appointment_date') }}" 
                               min="{{ date('Y-m-d') }}" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="word_time_id" class="form-label">
                            <i class="fa fa-clock-o"></i> Chọn giờ <span class="required">*</span>
                        </label>
                        <select name="word_time_id" id="word_time_id" class="form-select" required>
                            <option value="">-- Chọn giờ --</option>
                            @foreach($wordTimes as $wordTime)
                                <option value="{{ $wordTime->id }}" {{ old('word_time_id') == $wordTime->id ? 'selected' : '' }}>
                                    {{ date('H:i', strtotime($wordTime->time)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <!-- Ghi chú -->
                <div class="form-section">
                    <div class="form-group">
                        <label for="note" class="form-label">
                            <i class="fa fa-comment-o"></i> Ghi chú <span class="optional">(Tùy chọn)</span>
                        </label>
                        <textarea name="note" 
                                  id="note" 
                                  class="form-textarea" 
                                  rows="3" 
                                  placeholder="Nhập ghi chú nếu có...">{{ old('note') }}</textarea>
                    </div>
                </div>
                
                <!-- Submit button -->
                <div class="form-submit">
                    <button type="submit" class="submit-btn">
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
        border-radius: 15px;
        max-width: 650px;
        max-height: 90vh;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    }
    
    .popup_inner {
        padding: 35px 40px;
        max-height: 90vh;
        overflow-y: auto;
    }
    
    /* Form Header */
    .form-header {
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .form-header h3 {
        color: #4A3600;
        font-size: 26px;
        font-weight: 600;
        margin-bottom: 8px;
    }
    
    .form-header h3 i {
        margin-right: 10px;
        color: #BC9321;
    }
    
    .form-subtitle {
        color: #666;
        font-size: 14px;
        margin: 0;
    }
    
    /* Form Sections */
    .form-section {
        margin-bottom: 25px;
    }
    
    .section-title {
        color: #4A3600;
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        padding-bottom: 10px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .section-title i {
        margin-right: 8px;
        color: #BC9321;
        font-size: 18px;
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
        margin-bottom: 20px;
    }
    
    .form-label {
        display: block;
        font-weight: 500;
        color: #333;
        margin-bottom: 8px;
        font-size: 14px;
    }
    
    .form-label i {
        margin-right: 6px;
        color: #4A3600;
        width: 16px;
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
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
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
        min-height: 80px;
    }
    
    /* Service Variants */
    .service-variants-container {
        max-height: 250px;
        overflow-y: auto;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
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
        font-size: 14px;
    }
    
    .variant-meta {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .variant-price {
        color: #BC9321;
        font-weight: 600;
        font-size: 15px;
    }
    
    .variant-duration {
        color: #666;
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .variant-duration i {
        font-size: 11px;
    }
    
    /* Submit Button */
    .form-submit {
        margin-top: 25px;
        padding-top: 20px;
        border-top: 2px solid #f0f0f0;
    }
    
    .submit-btn {
        width: 100%;
        padding: 15px 30px;
        background: linear-gradient(135deg, #4A3600 0%, #5a4a00 100%);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
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
    @media (max-width: 767px) {
        .appointment-popup {
            max-width: 100%;
            border-radius: 0;
            max-height: 100vh;
        }
        
        .popup_inner {
            padding: 25px 20px;
        }
        
        .form-header h3 {
            font-size: 22px;
        }
        
        .service-variants-container {
            max-height: 200px;
        }
        
        .variant-content {
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
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
                        
                        // Update cart count in header
                        if (response.cart_count !== undefined) {
                            $('.cart-icon .bag').text(response.cart_count);
                        }
                        
                        // Reset form and redirect to cart after 1.5 seconds
                        setTimeout(function() {
                            $('#appointmentForm')[0].reset();
                            $.magnificPopup.close();
                            
                            // Show toast notification if available
                            if (typeof toastr !== 'undefined') {
                                toastr.success(response.message);
                            }
                            
                            // Redirect to cart page
                            if (response.redirect_url) {
                                window.location.href = response.redirect_url;
                            }
                        }, 1500);
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

