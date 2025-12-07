@extends('admin.layouts.app')

@section('title', 'Tạo lịch hẹn mới')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Tạo lịch hẹn mới</h1>
    <a href="{{ route('employee.appointments.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="border-left: 4px solid #dc3545; border-radius: 0.35rem;">
        <div class="d-flex align-items-start">
            <i class="fas fa-exclamation-circle fa-2x mr-3 text-danger mt-1"></i>
            <div class="flex-grow-1">
                <h5 class="alert-heading mb-2" style="font-weight: 600;">
                    <i class="fas fa-exclamation-triangle"></i> Vui lòng kiểm tra lại thông tin!
                </h5>
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="border-left: 4px solid #dc3545; border-radius: 0.35rem;">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-circle fa-2x mr-3 text-danger"></i>
            <div class="flex-grow-1">
                <h5 class="alert-heading mb-1" style="font-weight: 600;">
                    <i class="fas fa-exclamation-triangle"></i> Có lỗi xảy ra!
                </h5>
                <p class="mb-0">{{ session('error') }}</p>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>
@endif

<!-- Form -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Thông tin lịch hẹn</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('employee.appointments.store') }}" id="appointmentForm">
            @csrf

            <!-- Chọn hoặc thêm khách hàng -->
            <div class="form-group">
                <label>Khách hàng <span class="text-danger">*</span></label>
                <div class="mb-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="customer_type" id="customer_existing" value="existing" checked>
                        <label class="form-check-label" for="customer_existing">
                            Chọn khách hàng có sẵn
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="customer_type" id="customer_new" value="new">
                        <label class="form-check-label" for="customer_new">
                            Thêm khách hàng mới
                        </label>
                    </div>
                </div>

                <!-- Chọn khách hàng có sẵn -->
                <div id="existing_customer_section">
                    <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror">
                        <option value="">-- Chọn khách hàng --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('user_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }} - {{ $customer->phone }} {{ $customer->email ? '(' . $customer->email . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                </div>
                </div>

                <!-- Form thêm khách hàng mới -->
                <div id="new_customer_section" style="display: none;">
                    <div class="border rounded p-3 bg-light">
                        <div class="form-group">
                            <label for="new_customer_name">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="new_customer_name" 
                                   id="new_customer_name" 
                                   class="form-control @error('new_customer_name') is-invalid @enderror" 
                                   value="{{ old('new_customer_name') }}"
                                   placeholder="Nhập họ và tên">
                            @error('new_customer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="new_customer_phone">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" 
                                   name="new_customer_phone" 
                                   id="new_customer_phone" 
                                   class="form-control @error('new_customer_phone') is-invalid @enderror" 
                                   value="{{ old('new_customer_phone') }}"
                                   placeholder="Nhập số điện thoại">
                            @error('new_customer_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="new_customer_email">Email</label>
                            <input type="email" 
                                   name="new_customer_email" 
                                   id="new_customer_email" 
                                   class="form-control @error('new_customer_email') is-invalid @enderror" 
                                   value="{{ old('new_customer_email') }}"
                                   placeholder="Nhập email (tùy chọn)">
                            @error('new_customer_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

                <!-- Chọn nhân viên thực hiện -->
                <div class="mt-4">
                    <label for="staff_id">Nhân viên thực hiện <span class="text-danger">*</span></label>
                    <select name="staff_id" id="staff_id" class="form-control @error('staff_id') is-invalid @enderror">
                        <option value="">-- Chọn nhân viên (Stylist/Barber) --</option>
                        @foreach($staffMembers as $staff)
                            <option value="{{ $staff->id }}" {{ old('staff_id') == $staff->id ? 'selected' : '' }}>
                                {{ $staff->user->name ?? 'N/A' }} ({{ $staff->position }})
                            </option>
                        @endforeach
                    </select>
                    @error('staff_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">
                        Vui lòng chọn Stylist hoặc Barber sẽ thực hiện dịch vụ.
                    </small>
                </div>
            </div>

            <!-- Chọn dịch vụ -->
            <div class="form-group">
                <label>Dịch vụ <span class="text-danger">*</span></label>
                <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                    @php
                        $hasServices = false;
                    @endphp
                    @foreach($categories as $category)
                        @php
                            $categoryServices = $services->where('category_id', $category->id)->where('status', 'Hoạt động');
                        @endphp
                        @if($categoryServices->count() > 0)
                            @php $hasServices = true; @endphp
                            <div class="mb-3">
                                <h6 class="font-weight-bold text-primary">{{ $category->name }}</h6>
                                @foreach($categoryServices as $service)
                                    <div class="ml-3 mb-2">
                                        <strong class="text-dark">{{ $service->name }}</strong>
                                        @php
                                            $activeVariants = $service->serviceVariants?->where('is_active', true) ?? collect();
                                        @endphp
                                        @if($activeVariants->count() > 0)
                                            @foreach($activeVariants as $variant)
                                                <div class="form-check ml-4">
                                                    <input class="form-check-input service-variant-checkbox" 
                                                           type="checkbox" 
                                                           name="service_variants[]" 
                                                           value="{{ $variant->id }}" 
                                                           id="variant_{{ $variant->id }}"
                                                           data-price="{{ $variant->price }}"
                                                           data-duration="{{ $variant->duration ?? 60 }}"
                                                           {{ in_array($variant->id, old('service_variants', [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="variant_{{ $variant->id }}">
                                                        {{ $variant->name }} - 
                                                        <strong class="text-success">{{ number_format($variant->price, 0, ',', '.') }}đ</strong>
                                                        @if($variant->duration)
                                                            <span class="text-muted">({{ $variant->duration }} phút)</span>
                                                        @endif
                                                    </label>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="form-check ml-4">
                                                <input class="form-check-input service-simple-checkbox" 
                                                       type="checkbox" 
                                                       name="simple_services[]" 
                                                       value="{{ $service->id }}" 
                                                       id="service_{{ $service->id }}"
                                                       data-price="{{ $service->base_price ?? 0 }}"
                                                       data-duration="{{ $service->base_duration ?? 60 }}"
                                                       {{ in_array($service->id, old('simple_services', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="service_{{ $service->id }}">
                                                    {{ $service->name }} 
                                                    @if(!is_null($service->base_price))
                                                        - <strong class="text-success">{{ number_format($service->base_price, 0, ',', '.') }}đ</strong>
                                                    @endif
                                                    @if(!is_null($service->base_duration))
                                                        <span class="text-muted">({{ $service->base_duration }} phút)</span>
                                                    @endif
                                                    <span class="text-muted small d-block">Dịch vụ cơ bản (không có biến thể)</span>
                                                </label>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                    @if(!$hasServices)
                        <div class="text-muted text-center py-3">Chưa có dịch vụ nào</div>
                    @endif
                </div>
                @error('service_variants')
                    <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
            </div>

            <!-- Ngày đặt -->
            <div class="form-group">
                <label for="appointment_date">Ngày đặt <span class="text-danger">*</span></label>
                <input type="date" 
                       name="appointment_date" 
                       id="appointment_date" 
                       class="form-control @error('appointment_date') is-invalid @enderror" 
                       value="{{ old('appointment_date', date('Y-m-d')) }}"
                       min="{{ date('Y-m-d') }}"
                       required>
                @error('appointment_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Giờ đặt -->
            <div class="form-group">
                <label for="appointment_time">Giờ đặt <span class="text-danger">*</span></label>
                <input type="time" 
                       name="appointment_time" 
                       id="appointment_time" 
                       class="form-control @error('appointment_time') is-invalid @enderror" 
                       value="{{ old('appointment_time') }}"
                       required>
                @error('appointment_time')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Ví dụ: 09:00, 14:30</small>
            </div>

            <!-- Khuyến mãi / giảm giá -->
            <div class="form-group">
                <label for="promotion_code">Mã khuyến mãi áp dụng cho khách này (nếu có)</label>
                <select name="promotion_code" id="promotion_code" class="form-control @error('promotion_code') is-invalid @enderror">
                    <option value="">-- Không áp dụng khuyến mãi --</option>
                    @foreach($promotions as $promotion)
                        <option value="{{ $promotion->code }}" {{ old('promotion_code') == $promotion->code ? 'selected' : '' }}>
                            {{ $promotion->code }} - {{ $promotion->name }}
                        </option>
                    @endforeach
                </select>
                @error('promotion_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">
                    Nhân viên có thể chọn một mã giảm giá hiện có để áp dụng sẵn cho lịch hẹn này. 
                    Khi thanh toán, khách dùng đúng mã này sẽ được giảm giá.
                </small>
            </div>

            <!-- Ghi chú -->
            <div class="form-group">
                <label for="note">Ghi chú</label>
                <textarea name="note" 
                          id="note" 
                          class="form-control @error('note') is-invalid @enderror" 
                          rows="3"
                          placeholder="Nhập ghi chú (nếu có)">{{ old('note') }}</textarea>
                @error('note')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Tạo lịch hẹn
                </button>
                <a href="{{ route('employee.appointments.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Hủy
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Toggle between existing and new customer
        $('input[name="customer_type"]').on('change', function() {
            if ($(this).val() === 'existing') {
                $('#existing_customer_section').show();
                $('#new_customer_section').hide();
                $('#user_id').prop('required', true);
                $('#new_customer_name, #new_customer_phone').prop('required', false);
            } else {
                $('#existing_customer_section').hide();
                $('#new_customer_section').show();
                $('#user_id').prop('required', false).val('');
                $('#new_customer_name, #new_customer_phone').prop('required', true);
            }
        });

        // Set initial state
        if ($('input[name="customer_type"]:checked').val() === 'new') {
            $('#existing_customer_section').hide();
            $('#new_customer_section').show();
            $('#user_id').prop('required', false);
            $('#new_customer_name, #new_customer_phone').prop('required', true);
        }

        // Validate at least one service or variant is selected
        $('#appointmentForm').on('submit', function(e) {
            var checkedVariants = $('.service-variant-checkbox:checked').length;
            var checkedSimple = $('.service-simple-checkbox:checked').length;
            if (checkedVariants === 0 && checkedSimple === 0) {
                e.preventDefault();
                alert('Vui lòng chọn ít nhất một dịch vụ!');
                return false;
            }

            // Validate customer selection
            var customerType = $('input[name="customer_type"]:checked').val();
            if (customerType === 'existing') {
                if (!$('#user_id').val()) {
                    e.preventDefault();
                    alert('Vui lòng chọn khách hàng!');
                    return false;
                }
            } else {
                if (!$('#new_customer_name').val() || !$('#new_customer_phone').val()) {
                    e.preventDefault();
                    alert('Vui lòng nhập đầy đủ thông tin khách hàng mới!');
                    return false;
                }
            }
        });

        // Check for time conflicts when date or time changes
        $('#appointment_date, #appointment_time').on('change', function() {
            var date = $('#appointment_date').val();
            var time = $('#appointment_time').val();
            
            if (date && time) {
                // You can add AJAX call here to check availability
                // For now, just validate format
            }
        });
    });
</script>
@endpush

