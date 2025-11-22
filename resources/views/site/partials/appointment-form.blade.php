@php
    $services = app(\App\Services\ServiceService::class)->getAll();
    $employees = app(\App\Services\EmployeeService::class)->getAll();
@endphp

<!-- Appointment Booking Form -->
<div id="test-form" class="white-popup-block mfp-hide" style="max-width: 600px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 10px;">
    <div class="popup_box">
        <div class="popup_inner">
            <h3>Đặt lịch hẹn</h3>
            <form id="appointment-form" action="{{ route('site.appointments.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-xl-12">
                        <div class="form-group">
                            <input type="text" name="name" id="name" placeholder="Họ và tên *" required 
                                   value="{{ auth()->user()->name ?? old('name') }}" 
                                   class="form-control @error('name') is-invalid @enderror">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="form-group">
                            <input type="text" name="phone" id="phone" placeholder="Số điện thoại *" required 
                                   value="{{ auth()->user()->phone ?? old('phone') }}" 
                                   class="form-control @error('phone') is-invalid @enderror">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="form-group">
                            <input type="email" name="email" id="email" placeholder="Email" 
                                   value="{{ auth()->user()->email ?? old('email') }}" 
                                   class="form-control @error('email') is-invalid @enderror">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="form-group">
                            <select name="service_variant_id[]" id="service_variant_id" multiple required 
                                    class="form-control select2 @error('service_variant_id') is-invalid @enderror" 
                                    style="width: 100%;">
                                <option value="">Chọn dịch vụ *</option>
                                @foreach($services as $service)
                                    @if($service->serviceVariants && $service->serviceVariants->count() > 0)
                                        <optgroup label="{{ $service->name }}">
                                            @foreach($service->serviceVariants as $variant)
                                                <option value="{{ $variant->id }}" 
                                                        data-price="{{ $variant->price }}"
                                                        data-duration="{{ $variant->duration ?? 60 }}">
                                                    {{ $variant->name }} - {{ number_format($variant->price, 0, ',', '.') }}đ
                                                    @if($variant->duration)
                                                        ({{ $variant->duration }} phút)
                                                    @endif
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                @endforeach
                            </select>
                            @error('service_variant_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="form-group">
                            <select name="employee_id" id="employee_id" 
                                    class="form-control @error('employee_id') is-invalid @enderror">
                                <option value="">Chọn nhân viên (tùy chọn)</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->user->name ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="form-group">
                            <input type="date" name="date" id="date" placeholder="Ngày *" required 
                                   min="{{ date('Y-m-d') }}" 
                                   value="{{ old('date') }}" 
                                   class="form-control @error('date') is-invalid @enderror">
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="form-group">
                            <input type="time" name="time" id="time" placeholder="Giờ *" required 
                                   value="{{ old('time') }}" 
                                   class="form-control @error('time') is-invalid @enderror">
                            @error('time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="form-group">
                            <textarea name="note" id="note" rows="3" placeholder="Ghi chú (tùy chọn)" 
                                      class="form-control @error('note') is-invalid @enderror">{{ old('note') }}</textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div id="form-message" class="alert" style="display: none;"></div>
                    </div>
                    <div class="col-xl-12">
                        <button type="submit" class="boxed-btn3" id="submit-btn">
                            Đặt lịch ngay
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize Select2 for service selection when popup opens
    $(document).on('click', '.popup-with-form', function() {
        setTimeout(function() {
            if ($.fn.select2 && $('#service_variant_id').length && !$('#service_variant_id').hasClass('select2-hidden-accessible')) {
                $('#service_variant_id').select2({
                    placeholder: 'Chọn dịch vụ *',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#test-form')
                });
            }
        }, 300);
    });
    
    // Also initialize on page load if form is visible
    if ($.fn.select2 && $('#service_variant_id').length) {
        $('#service_variant_id').select2({
            placeholder: 'Chọn dịch vụ *',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#test-form')
        });
    }

    // Handle form submission
    $('#appointment-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = $('#submit-btn');
        var messageDiv = $('#form-message');
        
        // Disable submit button
        submitBtn.prop('disabled', true).text('Đang xử lý...');
        messageDiv.hide().removeClass('alert-success alert-danger');
        
        // Submit form via AJAX
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                messageDiv.addClass('alert-success')
                    .html('<i class="fa fa-check-circle"></i> ' + response.message)
                    .fadeIn();
                
                // Close popup and reload page after 1.5 seconds
                setTimeout(function() {
                    $.magnificPopup.close();
                    // Reload the page
                    window.location.reload();
                }, 1500);
            },
            error: function(xhr) {
                var errorMessage = 'Có lỗi xảy ra. Vui lòng thử lại.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    var errorList = '<ul>';
                    $.each(errors, function(key, value) {
                        errorList += '<li>' + value[0] + '</li>';
                    });
                    errorList += '</ul>';
                    errorMessage = errorList;
                }
                
                messageDiv.addClass('alert-danger')
                    .html('<i class="fa fa-exclamation-circle"></i> ' + errorMessage)
                    .fadeIn();
            },
            complete: function() {
                submitBtn.prop('disabled', false).text('Đặt lịch ngay');
            }
        });
    });
});
</script>

<style>
.white-popup-block {
    position: relative;
    background: #FFF;
    padding: 20px;
    width: auto;
    max-width: 600px;
    margin: 20px auto;
}

.popup_box h3 {
    margin-bottom: 20px;
    color: #4A3600;
    font-size: 24px;
    font-weight: 600;
}

.popup_box .form-group {
    margin-bottom: 15px;
}

.popup_box .form-control {
    height: 45px;
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 10px 15px;
}

.popup_box .form-control:focus {
    border-color: #4A3600;
    box-shadow: 0 0 0 0.2rem rgba(74, 54, 0, 0.25);
}

.popup_box textarea.form-control {
    height: auto;
    resize: vertical;
}

.popup_box .boxed-btn3 {
    width: 100%;
    padding: 12px 30px;
    background: #4A3600;
    color: #fff;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.popup_box .boxed-btn3:hover {
    background: #5a4a00;
}

.popup_box .boxed-btn3:disabled {
    background: #ccc;
    cursor: not-allowed;
}

#form-message {
    margin-top: 15px;
    padding: 10px 15px;
    border-radius: 5px;
}

#form-message.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

#form-message.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

#form-message ul {
    margin: 5px 0 0 0;
    padding-left: 20px;
}

.select2-container {
    width: 100% !important;
}
</style>

