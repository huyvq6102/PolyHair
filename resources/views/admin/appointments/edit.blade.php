@extends('admin.layouts.app')

@section('title', 'Sửa lịch hẹn')

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
                        <input type="text" name="name" id="name" value="{{ old('name', $appointment->user->name ?? $appointment->guest_name ?? '') }}"
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
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $appointment->user->phone ?? $appointment->guest_phone ?? '') }}"
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
                        <input type="email" name="email" id="email" value="{{ old('email', $appointment->user->email ?? $appointment->guest_email ?? '') }}"
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
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($appointment->appointmentDetails as $detail)
                                        @php
                                            // Lấy giá gốc từ service/variant/combo
                                            $originalPrice = 0;
                                            if ($detail->combo_id) {
                                                if ($detail->combo) {
                                                    $originalPrice = $detail->combo->price ?? 0;
                                                } else {
                                                    // Nếu không load được relation, query trực tiếp
                                                    $combo = \App\Models\Combo::withTrashed()->find($detail->combo_id);
                                                    $originalPrice = $combo ? ($combo->price ?? 0) : 0;
                                                }
                                            } elseif ($detail->service_variant_id) {
                                                if ($detail->serviceVariant) {
                                                    $originalPrice = $detail->serviceVariant->price ?? 0;
                                                } else {
                                                    // Nếu không load được relation, query trực tiếp
                                                    $variant = \App\Models\ServiceVariant::withTrashed()->find($detail->service_variant_id);
                                                    $originalPrice = $variant ? ($variant->price ?? 0) : 0;
                                                }
                                            } elseif ($detail->notes) {
                                                // Dịch vụ đơn - tìm Service theo tên trong notes
                                                $service = \App\Models\Service::where('name', $detail->notes)->first();
                                                if ($service) {
                                                    $originalPrice = $service->base_price ?? 0;
                                                }
                                            }

                                            // Giá sau giảm (price_snapshot) - đây là giá đã được áp dụng discount tự động
                                            $finalPrice = $detail->price_snapshot ?? $originalPrice;

                                            // Tính discount = giá gốc - giá sau giảm
                                            $discountAmount = $originalPrice > 0 ? ($originalPrice - $finalPrice) : 0;
                                            $hasDiscount = $discountAmount > 0 && $originalPrice > 0;
                                        @endphp
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
                                            <td>
                                                @if($hasDiscount && $originalPrice > 0)
                                                    <div style="display: flex; flex-direction: column; gap: 2px;">
                                                        <span style="text-decoration: line-through; color: #999; font-size: 12px;">
                                                            {{ number_format($originalPrice, 0, ',', '.') }} đ
                                                        </span>
                                                        <span style="color: #28a745; font-weight: 600;">
                                                            {{ number_format($finalPrice, 0, ',', '.') }} đ
                                                        </span>
                                                        <small style="color: #ff4444; font-size: 11px;">
                                                            Giảm: {{ number_format($discountAmount, 0, ',', '.') }} đ
                                                        </small>
                                                    </div>
                                                @else
                                                    {{ number_format($finalPrice, 0, ',', '.') }} đ
                                                @endif
                                            </td>
                                            <td>{{ $detail->duration ?? 0 }}</td>
                                            <td>
                                                <form action="{{ route('admin.appointments.remove-service', [$appointment->id, $detail->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa dịch vụ này?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i> Xóa
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">Chưa có dịch vụ nào</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    @php
                                        // Tính tổng giá gốc và tổng discount từ từng dịch vụ
                                        $totalOriginalPrice = 0;
                                        $totalServiceDiscount = 0; // Discount tự động từ promotion service-level

                                        foreach ($appointment->appointmentDetails as $detail) {
                                            // Lấy giá gốc
                                            $originalPrice = 0;
                                            if ($detail->combo_id) {
                                                if ($detail->combo) {
                                                    $originalPrice = $detail->combo->price ?? 0;
                                                } else {
                                                    $combo = \App\Models\Combo::withTrashed()->find($detail->combo_id);
                                                    $originalPrice = $combo ? ($combo->price ?? 0) : 0;
                                                }
                                            } elseif ($detail->service_variant_id) {
                                                if ($detail->serviceVariant) {
                                                    $originalPrice = $detail->serviceVariant->price ?? 0;
                                                } else {
                                                    $variant = \App\Models\ServiceVariant::withTrashed()->find($detail->service_variant_id);
                                                    $originalPrice = $variant ? ($variant->price ?? 0) : 0;
                                                }
                                            } elseif ($detail->notes) {
                                                // Dịch vụ đơn - tìm Service theo tên trong notes
                                                $service = \App\Models\Service::where('name', $detail->notes)->first();
                                                if ($service) {
                                                    $originalPrice = $service->base_price ?? 0;
                                                }
                                            }

                                            $finalPrice = $detail->price_snapshot ?? $originalPrice;
                                            $serviceDiscount = $originalPrice > 0 ? ($originalPrice - $finalPrice) : 0;

                                            $totalOriginalPrice += $originalPrice;
                                            $totalServiceDiscount += $serviceDiscount;
                                        }

                                        // Lấy promotion từ payment nếu có (order-level discount)
                                        $payment = \App\Models\Payment::where('appointment_id', $appointment->id)->first();
                                        $orderLevelDiscount = 0;
                                        $promotionCode = null;
                                        $promotion = null;

                                        if ($payment && $payment->promotion_id) {
                                            $promotion = \App\Models\Promotion::find($payment->promotion_id);
                                        } else {
                                            // Nếu chưa có payment, kiểm tra promotionUsages
                                            $promotionUsage = $appointment->promotionUsages()->first();
                                            if ($promotionUsage && $promotionUsage->promotion_id) {
                                                $promotion = \App\Models\Promotion::find($promotionUsage->promotion_id);
                                            }
                                        }

                                        // Tính order-level discount (áp dụng trên tổng giá gốc)
                                        if ($promotion && $promotion->apply_scope === 'order') {
                                            $promotionCode = $promotion->code;
                                            if ($promotion->discount_type === 'percent') {
                                                $orderLevelDiscount = ($totalOriginalPrice * ($promotion->discount_percent ?? 0)) / 100;
                                                if ($promotion->max_discount_amount) {
                                                    $orderLevelDiscount = min($orderLevelDiscount, $promotion->max_discount_amount);
                                                }
                                            } else {
                                                $orderLevelDiscount = min($promotion->discount_amount ?? 0, $totalOriginalPrice);
                                            }
                                        }

                                        // Tổng discount = discount từ service + discount từ order
                                        $totalDiscount = $totalServiceDiscount + $orderLevelDiscount;

                                        // Tổng thanh toán = giá gốc - tổng discount
                                        $total = max(0, $totalOriginalPrice - $totalDiscount);
                                    @endphp
                                    <tr style="background-color: #f8f9fa;">
                                        <td colspan="3" class="text-right font-weight-bold">Tổng giá gốc:</td>
                                        <td class="font-weight-bold">{{ number_format($totalOriginalPrice, 0, ',', '.') }} đ</td>
                                    </tr>
                                    @if($totalServiceDiscount > 0)
                                    <tr style="background-color: #e7f3ff;">
                                        <td colspan="3" class="text-right text-info font-weight-bold">
                                            Giảm giá tự động (từng dịch vụ):
                                        </td>
                                        <td class="text-info font-weight-bold">-{{ number_format($totalServiceDiscount, 0, ',', '.') }} đ</td>
                                    </tr>
                                    @endif
                                    @if($orderLevelDiscount > 0)
                                    <tr style="background-color: #fff3cd;">
                                        <td colspan="3" class="text-right text-success font-weight-bold">
                                            Giảm giá @if($promotionCode)({{ $promotionCode }})@endif:
                                        </td>
                                        <td class="text-success font-weight-bold">-{{ number_format($orderLevelDiscount, 0, ',', '.') }} đ</td>
                                    </tr>
                                    @endif
                                    @if($totalDiscount > 0)
                                    <tr style="background-color: #d1ecf1; border-top: 1px solid #bee5eb;">
                                        <td colspan="3" class="text-right font-weight-bold">Tổng giảm giá:</td>
                                        <td class="font-weight-bold text-danger">-{{ number_format($totalDiscount, 0, ',', '.') }} đ</td>
                                    </tr>
                                    @endif
                                    <tr style="background-color: #d4edda; border-top: 2px solid #28a745;">
                                        <td colspan="3" class="text-right font-weight-bold" style="font-size: 16px;">Tổng thanh toán:</td>
                                        <td class="font-weight-bold" style="font-size: 18px; color: #28a745;">{{ number_format($total, 0, ',', '.') }} đ</td>
                                    </tr>
                                </tfoot>
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
                                                    <div class="col-md-4 mb-2">
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
                                                           name="new_services[]"
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
                               class="form-control @error('appointment_date') is-invalid @enderror"
                               min="{{ date('Y-m-d') }}">
                        @error('appointment_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Không được chọn ngày trong quá khứ</small>
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
                        <small class="form-text text-muted">Nếu chọn ngày hôm nay, giờ phải lớn hơn giờ hiện tại</small>
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

@push('styles')
<style>
    #status option:disabled {
        color: #999 !important;
        background-color: #f5f5f5 !important;
        font-style: italic;
        opacity: 0.6;
    }

    #status option:not(:disabled) {
        color: #333 !important;
        background-color: #fff !important;
    }
</style>
@endpush

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

            // Validate các form khác (form xóa dịch vụ)
            var otherForms = document.querySelectorAll('.needs-validation:not(#appointment-edit-form)');
            otherForms.forEach(function(form) {
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

    // Không cần lọc dịch vụ theo nhân viên - hiển thị tất cả dịch vụ

    // Validation: Không cho phép chọn ngày giờ trong quá khứ
    $(document).ready(function() {
        const appointmentDateInput = $('#appointment_date');
        const appointmentTimeInput = $('#appointment_time');
        const today = new Date();
        const todayStr = today.toISOString().split('T')[0]; // Format: YYYY-MM-DD

        // Set min date to today
        appointmentDateInput.attr('min', todayStr);

        // Validate date and time on change
        function validateDateTime() {
            const selectedDate = appointmentDateInput.val();
            const selectedTime = appointmentTimeInput.val();

            if (!selectedDate || !selectedTime) {
                return true; // Allow empty values
            }

            const selectedDateTime = new Date(selectedDate + ' ' + selectedTime);
            const now = new Date();

            // Check if selected datetime is in the past
            if (selectedDateTime < now) {
                alert('Không được chọn ngày giờ trong quá khứ! Vui lòng chọn ngày giờ từ bây giờ trở đi.');
                // Reset to current date/time
                appointmentDateInput.val(todayStr);
                const currentTime = now.toTimeString().slice(0, 5); // Format: HH:MM
                appointmentTimeInput.val(currentTime);
                return false;
            }

            return true;
        }

        // Validate when date changes
        appointmentDateInput.on('change', function() {
            const selectedDate = $(this).val();
            const today = new Date();
            const todayStr = today.toISOString().split('T')[0];

            // If selected date is today, set min time to current time
            if (selectedDate === todayStr) {
                const now = new Date();
                const currentTime = now.toTimeString().slice(0, 5);
                appointmentTimeInput.attr('min', currentTime);
            } else {
                // If future date, remove min time restriction
                appointmentTimeInput.removeAttr('min');
            }

            validateDateTime();
        });

        // Validate when time changes
        appointmentTimeInput.on('change', function() {
            validateDateTime();
        });

        // Validate on form submit
        $('#appointment-edit-form').on('submit', function(e) {
            if (!validateDateTime()) {
                e.preventDefault();
                return false;
            }
        });

        // Logic kiểm soát trạng thái: chỉ cho phép chọn trạng thái theo thứ tự
        function updateStatusOptions() {
            const statusSelect = document.getElementById('status');
            if (!statusSelect) return;

            // Lấy trạng thái hiện tại của appointment
            const currentStatus = '{{ $appointment->status }}';

            // Định nghĩa thứ tự trạng thái
            const statusOrder = {
                'Chờ xử lý': 0,
                'Đã xác nhận': 1,
                'Đang thực hiện': 2,
                'Hoàn thành': 3,
                'Đã hủy': -1 // Đặc biệt
            };

            const currentOrder = statusOrder[currentStatus] ?? 0;

            // Duyệt qua tất cả các option
            Array.from(statusSelect.options).forEach(function(option) {
                const optionValue = option.value;
                const optionOrder = statusOrder[optionValue] ?? 0;

                // Nếu là trạng thái hiện tại, luôn cho phép chọn (nhưng sẽ disable nếu cần)
                if (optionValue === currentStatus) {
                    option.disabled = false;
                    return;
                }

                // Nếu đang ở "Hoàn thành", không cho phép chọn trạng thái khác
                if (currentOrder === 3) {
                    // Chỉ cho phép giữ nguyên trạng thái hiện tại
                    option.disabled = true;
                    option.style.color = '#999';
                    option.style.backgroundColor = '#f5f5f5';
                    return;
                }

                // Nếu đang ở "Đã hủy", không cho phép chọn trạng thái khác
                if (currentStatus === 'Đã hủy') {
                    option.disabled = true;
                    option.style.color = '#999';
                    option.style.backgroundColor = '#f5f5f5';
                    return;
                }

                // Logic chính: chỉ cho phép chọn trạng thái tiếp theo ngay (currentOrder + 1)
                // Hoặc cho phép hủy từ các trạng thái: Chờ xử lý, Đã xác nhận, Đang thực hiện
                if (optionValue === 'Đã hủy') {
                    // Cho phép hủy từ các trạng thái: Chờ xử lý, Đã xác nhận, Đang thực hiện
                    if (currentOrder >= 0 && currentOrder <= 2) {
                        option.disabled = false;
                    } else {
                        option.disabled = true;
                        option.style.color = '#999';
                        option.style.backgroundColor = '#f5f5f5';
                    }
                } else if (optionOrder === currentOrder + 1) {
                    // Chỉ cho phép chọn trạng thái tiếp theo ngay (không được nhảy cóc)
                    option.disabled = false;
                } else {
                    // Không cho phép chọn trạng thái có thứ tự thấp hơn, bằng, hoặc cao hơn quá nhiều
                    option.disabled = true;
                    option.style.color = '#999';
                    option.style.backgroundColor = '#f5f5f5';
                }
            });
        }

        // Gọi hàm khi trang load
        updateStatusOptions();

        // Cập nhật lại khi user thay đổi trạng thái (để validate real-time)
        $('#status').on('change', function() {
            const selectedValue = $(this).val();
            const currentStatus = '{{ $appointment->status }}';

            // Nếu user chọn trạng thái không hợp lệ, reset về trạng thái hiện tại
            const statusOrder = {
                'Chờ xử lý': 0,
                'Đã xác nhận': 1,
                'Đang thực hiện': 2,
                'Hoàn thành': 3,
                'Đã hủy': -1
            };

            const currentOrder = statusOrder[currentStatus] ?? 0;
            const selectedOrder = statusOrder[selectedValue] ?? 0;

            // Kiểm tra hợp lệ
            let isValid = false;

            if (selectedValue === currentStatus) {
                isValid = true;
            } else if (currentOrder === 3) {
                isValid = false; // Không cho phép thay đổi từ Hoàn thành
            } else if (currentStatus === 'Đã hủy') {
                isValid = false; // Không cho phép thay đổi từ Đã hủy
            } else if (selectedValue === 'Đã hủy' && currentOrder >= 0 && currentOrder <= 2) {
                isValid = true; // Cho phép hủy từ Chờ xử lý, Đã xác nhận, Đang thực hiện
            } else if (selectedOrder === currentOrder + 1) {
                isValid = true; // Chỉ cho phép chọn trạng thái tiếp theo ngay (không được nhảy cóc)
            }

            if (!isValid) {
                alert('Không thể chọn trạng thái này. Vui lòng chọn trạng thái theo thứ tự hoặc trạng thái hợp lệ.');
                $(this).val(currentStatus);
                updateStatusOptions();
            }
        });
    });
</script>
@endpush
