@extends('admin.layouts.app')

@section('title', 'Thêm mới lịch hẹn')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Thêm mới lịch hẹn</h1>
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
        <form action="{{ route('admin.appointments.store') }}" method="POST" class="needs-validation" novalidate>
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Tên khách hàng <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" 
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
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" 
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
                        <input type="email" name="email" id="email" value="{{ old('email') }}" 
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
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Chọn dịch vụ <span class="text-danger">*</span></label>
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
                                                           name="services[]" 
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
                                                    <div class="col-md-4 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input service-checkbox" type="checkbox" 
                                                                   name="services[]" 
                                                                   value="variant_{{ $variant->id }}" 
                                                                   id="service_variant_{{ $variant->id }}"
                                                                   data-type="variant"
                                                                   data-id="{{ $variant->id }}"
                                                                   data-service-id="{{ $service->id }}"
                                                                   data-price="{{ $variant->price }}"
                                                                   data-duration="{{ $variant->duration }}">
                                                            <label class="form-check-label" for="service_variant_{{ $variant->id }}">
                                                                {{ $variant->name }} - {{ number_format($variant->price, 0, ',', '.') }} đ
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
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input service-checkbox" type="checkbox" 
                                                           name="services[]" 
                                                           value="combo_{{ $combo->id }}" 
                                                           id="service_combo_{{ $combo->id }}"
                                                           data-type="combo"
                                                           data-id="{{ $combo->id }}"
                                                           data-price="{{ $combo->price }}"
                                                           data-duration="0">
                                                    <label class="form-check-label" for="service_combo_{{ $combo->id }}">
                                                        {{ $combo->name }} - {{ number_format($combo->price, 0, ',', '.') }} đ
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div id="service_error" class="invalid-feedback" style="display: none;">Vui lòng chọn ít nhất một dịch vụ</div>
                        @error('services')
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
                            <option value="Chờ xử lý" {{ old('status', 'Chờ xử lý') == 'Chờ xử lý' ? 'selected' : '' }}>Chờ xử lý</option>
                            <option value="Đã xác nhận" {{ old('status') == 'Đã xác nhận' ? 'selected' : '' }}>Đã xác nhận</option>
                            <option value="Đang thực hiện" {{ old('status') == 'Đang thực hiện' ? 'selected' : '' }}>Đang thực hiện</option>
                            <option value="Hoàn thành" {{ old('status') == 'Hoàn thành' ? 'selected' : '' }}>Hoàn thành</option>
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
                               value="{{ old('appointment_date') }}" 
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
                               value="{{ old('appointment_time') }}" 
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
                          placeholder="Nhập mô tả (nếu có)">{{ old('note') }}</textarea>
                @error('note')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Thêm
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
    
    // Xử lý validation cho checkbox dịch vụ
    document.addEventListener('DOMContentLoaded', function() {
        var serviceError = document.getElementById('service_error');
        var form = document.querySelector('.needs-validation');
        
        // Validation khi submit
        if (form) {
            form.addEventListener('submit', function(e) {
                var checkedServices = document.querySelectorAll('.service-checkbox:checked');
                
                if (checkedServices.length === 0) {
                    e.preventDefault();
                    e.stopPropagation();
                    serviceError.style.display = 'block';
                    serviceError.textContent = 'Vui lòng chọn ít nhất một dịch vụ';
                } else {
                    serviceError.style.display = 'none';
                }
            });
        }
    });
</script>
@endpush
