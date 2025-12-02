@extends('admin.layouts.app')

@section('title', 'Sửa lịch hẹn')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Sửa lịch hẹn #{{ $appointment->id }}</h1>
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
        <form action="{{ route('admin.appointments.update', $appointment->id) }}" method="POST" class="needs-validation" novalidate>
            @csrf
            @method('PUT')
            
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

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="service_type">Loại dịch vụ <span class="text-danger">*</span></label>
                        <select name="service_type" id="service_type" class="form-control @error('service_type') is-invalid @enderror" required>
                            <option value="">-- Chọn loại dịch vụ --</option>
                            <option value="single" {{ old('service_type', $currentServiceType ?? '') == 'single' ? 'selected' : '' }}>Dịch vụ đơn</option>
                            <option value="variant" {{ old('service_type', $currentServiceType ?? '') == 'variant' ? 'selected' : '' }}>Dịch vụ biến thể</option>
                            <option value="combo" {{ old('service_type', $currentServiceType ?? '') == 'combo' ? 'selected' : '' }}>Combo</option>
                        </select>
                        @error('service_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">Vui lòng chọn loại dịch vụ</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="service_id">Dịch vụ <span class="text-danger">*</span></label>
                        <!-- Dịch vụ đơn -->
                        <select name="service_id" id="service_single" class="form-control service-select" style="display: none;" disabled>
                            <option value="">-- Chọn dịch vụ đơn --</option>
                            @foreach($singleServices as $service)
                                <option value="{{ $service->id }}" data-service-id="{{ $service->id }}" data-price="{{ $service->base_price ?? 0 }}" data-duration="{{ $service->base_duration ?? 0 }}"
                                    {{ old('service_id', $currentServiceId) == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }} - {{ number_format($service->base_price ?? 0, 0, ',', '.') }} đ
                                </option>
                            @endforeach
                        </select>
                        
                        <!-- Dịch vụ biến thể -->
                        <select name="service_variant_id" id="service_variant" class="form-control service-select" style="display: none;" disabled>
                            <option value="">-- Chọn dịch vụ biến thể --</option>
                            @foreach($variantServices as $service)
                                <optgroup label="{{ $service->name }}" data-service-id="{{ $service->id }}">
                                    @foreach($service->serviceVariants as $variant)
                                        <option value="{{ $variant->id }}" data-service-id="{{ $service->id }}" data-price="{{ $variant->price }}" data-duration="{{ $variant->duration }}"
                                            {{ old('service_variant_id', $currentServiceVariantId) == $variant->id ? 'selected' : '' }}>
                                            {{ $variant->name }} - {{ number_format($variant->price, 0, ',', '.') }} đ
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        
                        <!-- Combo -->
                        <select name="combo_id" id="service_combo" class="form-control service-select" style="display: none;" disabled>
                            <option value="">-- Chọn combo --</option>
                            @foreach($combos as $combo)
                                <option value="{{ $combo->id }}" data-price="{{ $combo->price }}" data-duration="0"
                                    {{ old('combo_id', $currentComboId) == $combo->id ? 'selected' : '' }}>
                                    {{ $combo->name }} - {{ number_format($combo->price, 0, ',', '.') }} đ
                                </option>
                            @endforeach
                        </select>
                        
                        <div id="service_error" class="invalid-feedback" style="display: none;">Vui lòng chọn dịch vụ</div>
                        @error('service_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('service_variant_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('combo_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
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
                            <option value="Chưa thanh toán" {{ old('status', $appointment->status) == 'Chưa thanh toán' ? 'selected' : '' }}>Chưa thanh toán</option>
                            <option value="Đã thanh toán" {{ old('status', $appointment->status) == 'Đã thanh toán' ? 'selected' : '' }}>Đã thanh toán</option>
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
                               class="form-control @error('appointment_date') is-invalid @enderror">
                        @error('appointment_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="appointment_time">Giờ đặt</label>
                        <input type="time" name="appointment_time" id="appointment_time" 
                               value="{{ old('appointment_time', $appointment->start_at ? $appointment->start_at->format('H:i') : '') }}" 
                               class="form-control @error('appointment_time') is-invalid @enderror">
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
    
    // Xử lý chọn loại dịch vụ và lọc theo nhân viên
    document.addEventListener('DOMContentLoaded', function() {
        var serviceTypeSelect = document.getElementById('service_type');
        var serviceSingle = document.getElementById('service_single');
        var serviceVariant = document.getElementById('service_variant');
        var serviceCombo = document.getElementById('service_combo');
        var serviceError = document.getElementById('service_error');
        var employeeSelect = document.getElementById('employee_id');
        
        // Lưu tất cả dịch vụ ban đầu
        var allSingleServices = Array.from(serviceSingle.options).map(opt => ({
            value: opt.value,
            text: opt.text,
            html: opt.outerHTML,
            serviceId: opt.getAttribute('data-service-id'),
            price: opt.getAttribute('data-price'),
            duration: opt.getAttribute('data-duration'),
            selected: opt.selected
        }));
        
        var allVariantServices = [];
        Array.from(serviceVariant.querySelectorAll('optgroup')).forEach(optgroup => {
            var variants = Array.from(optgroup.querySelectorAll('option')).map(opt => ({
                value: opt.value,
                text: opt.text,
                html: opt.outerHTML,
                serviceId: opt.getAttribute('data-service-id'),
                price: opt.getAttribute('data-price'),
                duration: opt.getAttribute('data-duration'),
                selected: opt.selected
            }));
            allVariantServices.push({
                label: optgroup.label,
                serviceId: optgroup.getAttribute('data-service-id'),
                variants: variants,
                html: optgroup.outerHTML
            });
        });
        
        var allCombos = Array.from(serviceCombo.options).map(opt => ({
            value: opt.value,
            text: opt.text,
            html: opt.outerHTML,
            price: opt.getAttribute('data-price'),
            duration: opt.getAttribute('data-duration'),
            selected: opt.selected
        }));
        
        // Xử lý khi chọn nhân viên
        if (employeeSelect) {
            employeeSelect.addEventListener('change', function() {
                var employeeId = this.value;
                if (employeeId) {
                    // Gọi API để lấy dịch vụ của nhân viên
                    fetch('/admin/appointments/employee/' + employeeId + '/services')
                        .then(response => response.json())
                        .then(data => {
                            updateServices(data);
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                } else {
                    // Nếu không chọn nhân viên, hiển thị tất cả dịch vụ
                    restoreAllServices();
                }
            });
            
            // Nếu đã có nhân viên được chọn, load dịch vụ ngay
            if (employeeSelect.value) {
                employeeSelect.dispatchEvent(new Event('change'));
            }
        }
        
        function updateServices(data) {
            // Lưu giá trị đã chọn trước khi cập nhật
            var selectedSingle = serviceSingle.value;
            var selectedVariant = serviceVariant.value;
            var selectedCombo = serviceCombo.value;
            
            // Cập nhật dịch vụ đơn
            serviceSingle.innerHTML = '<option value="">-- Chọn dịch vụ đơn --</option>';
            data.singleServices.forEach(function(service) {
                var option = document.createElement('option');
                option.value = service.id;
                option.textContent = service.name + ' - ' + new Intl.NumberFormat('vi-VN').format(service.price) + ' đ';
                option.setAttribute('data-service-id', service.id);
                option.setAttribute('data-price', service.price);
                option.setAttribute('data-duration', service.duration);
                if (selectedSingle == service.id) {
                    option.selected = true;
                }
                serviceSingle.appendChild(option);
            });
            
            // Cập nhật dịch vụ biến thể
            serviceVariant.innerHTML = '<option value="">-- Chọn dịch vụ biến thể --</option>';
            data.variantServices.forEach(function(service) {
                var optgroup = document.createElement('optgroup');
                optgroup.label = service.name;
                optgroup.setAttribute('data-service-id', service.id);
                service.variants.forEach(function(variant) {
                    var option = document.createElement('option');
                    option.value = variant.id;
                    option.textContent = variant.name + ' - ' + new Intl.NumberFormat('vi-VN').format(variant.price) + ' đ';
                    option.setAttribute('data-service-id', service.id);
                    option.setAttribute('data-price', variant.price);
                    option.setAttribute('data-duration', variant.duration);
                    if (selectedVariant == variant.id) {
                        option.selected = true;
                    }
                    optgroup.appendChild(option);
                });
                serviceVariant.appendChild(optgroup);
            });
        }
        
        function restoreAllServices() {
            // Lưu giá trị đã chọn
            var selectedSingle = serviceSingle.value;
            var selectedVariant = serviceVariant.value;
            var selectedCombo = serviceCombo.value;
            
            // Khôi phục dịch vụ đơn
            serviceSingle.innerHTML = '<option value="">-- Chọn dịch vụ đơn --</option>';
            allSingleServices.forEach(function(service) {
                if (service.value) {
                    var tempDiv = document.createElement('div');
                    tempDiv.innerHTML = service.html;
                    var option = tempDiv.firstElementChild;
                    if (selectedSingle == service.value) {
                        option.selected = true;
                    }
                    serviceSingle.appendChild(option);
                }
            });
            
            // Khôi phục dịch vụ biến thể
            serviceVariant.innerHTML = '<option value="">-- Chọn dịch vụ biến thể --</option>';
            allVariantServices.forEach(function(service) {
                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = service.html;
                var optgroup = tempDiv.firstElementChild;
                // Khôi phục selected cho các option
                Array.from(optgroup.querySelectorAll('option')).forEach(opt => {
                    if (selectedVariant == opt.value) {
                        opt.selected = true;
                    }
                });
                serviceVariant.appendChild(optgroup);
            });
        }
        
        // Khôi phục giá trị cũ nếu có
        var oldServiceType = '{{ old("service_type", $currentServiceType ?? "") }}';
        if (oldServiceType) {
            showServiceSelect(oldServiceType);
        } else if (serviceTypeSelect.value) {
            // Nếu không có old value, dùng giá trị hiện tại của select
            showServiceSelect(serviceTypeSelect.value);
        }
        
        serviceTypeSelect.addEventListener('change', function() {
            var selectedType = this.value;
            showServiceSelect(selectedType);
        });
        
        function showServiceSelect(type) {
            // Ẩn tất cả các select
            serviceSingle.style.display = 'none';
            serviceSingle.disabled = true;
            serviceSingle.removeAttribute('required');
            
            serviceVariant.style.display = 'none';
            serviceVariant.disabled = true;
            serviceVariant.removeAttribute('required');
            
            serviceCombo.style.display = 'none';
            serviceCombo.disabled = true;
            serviceCombo.removeAttribute('required');
            
            // Hiển thị select tương ứng
            if (type === 'single') {
                serviceSingle.style.display = 'block';
                serviceSingle.disabled = false;
                serviceSingle.setAttribute('required', 'required');
            } else if (type === 'variant') {
                serviceVariant.style.display = 'block';
                serviceVariant.disabled = false;
                serviceVariant.setAttribute('required', 'required');
            } else if (type === 'combo') {
                serviceCombo.style.display = 'block';
                serviceCombo.disabled = false;
                serviceCombo.setAttribute('required', 'required');
            }
        }
        
        // Validation khi submit
        var form = document.querySelector('.needs-validation');
        if (form) {
            form.addEventListener('submit', function(e) {
                var serviceType = serviceTypeSelect.value;
                var hasService = false;
                
                if (serviceType === 'single' && serviceSingle.value) {
                    hasService = true;
                } else if (serviceType === 'variant' && serviceVariant.value) {
                    hasService = true;
                } else if (serviceType === 'combo' && serviceCombo.value) {
                    hasService = true;
                }
                
                if (serviceType && !hasService) {
                    e.preventDefault();
                    e.stopPropagation();
                    serviceError.style.display = 'block';
                    serviceError.textContent = 'Vui lòng chọn dịch vụ';
                } else {
                    serviceError.style.display = 'none';
                }
            });
        }
    });
</script>
@endpush
