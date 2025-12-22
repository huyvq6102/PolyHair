@extends('admin.layouts.app')

@section('title', 'Chi tiết lịch hẹn')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Chi tiết lịch hẹn {{ $appointment->booking_code ?? '#' . $appointment->id }}</h1>
    <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<!-- Appointment Info -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Thông tin lịch hẹn</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Khách hàng:</label>
                    <p class="form-control-plaintext">{{ $appointment->user->name ?? $appointment->guest_name ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Nhân viên:</label>
                    <p class="form-control-plaintext">{{ $appointment->employee->user->name ?? 'Chưa phân công' }}</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Thời gian bắt đầu:</label>
                    <p class="form-control-plaintext">{{ $appointment->start_at ? $appointment->start_at->format('d/m/Y H:i') : 'N/A' }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Thời gian kết thúc:</label>
                    <p class="form-control-plaintext">{{ $appointment->end_at ? $appointment->end_at->format('d/m/Y H:i') : 'N/A' }}</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Trạng thái:</label>
                    <div>
                        @php
                            // Định nghĩa màu cho từng trạng thái (giống index.blade.php)
                            $statusColors = [
                                'Chờ xử lý' => 'secondary',      // Xám
                                'Đã xác nhận' => 'info',        // Xanh dương
                                'Đang thực hiện' => 'warning',   // Cam/vàng
                                'Hoàn thành' => 'success',       // Xanh lá
                                'Đã thanh toán' => 'primary',    // Xanh đậm
                                'Đã hủy' => 'danger',           // Đỏ
                                'Chưa thanh toán' => 'warning',  // Cam/vàng
                            ];
                            $badgeClass = $statusColors[$appointment->status] ?? 'secondary';
                        @endphp
                        <span class="badge badge-{{ $badgeClass }} badge-lg" style="font-size: 14px; padding: 8px 12px;">
                            {{ $appointment->status }}
                        </span>
                    </div>
                    <small class="form-text text-muted">Trạng thái chỉ có thể thay đổi ở trang chỉnh sửa</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Mã đơn:</label>
                    <p class="form-control-plaintext">
                        <strong>{{ $appointment->booking_code ?? '#' . str_pad($appointment->id, 6, '0', STR_PAD_LEFT) }}</strong>
                    </p>
                </div>
            </div>
        </div>

        @if($appointment->note)
        <div class="form-group">
            <label>Ghi chú:</label>
            <p class="form-control-plaintext">{{ $appointment->note }}</p>
        </div>
        @endif

        @if($appointment->cancellation_reason)
        <div class="form-group">
            <label>Lý do hủy:</label>
            <p class="form-control-plaintext text-danger">{{ $appointment->cancellation_reason }}</p>
        </div>
        @endif

        <div class="form-group">
            @if($appointment->status != 'Hoàn thành' && $appointment->status != 'Đã thanh toán')
            <a href="{{ route('admin.appointments.edit', $appointment->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Chỉnh sửa lịch hẹn
            </a>
            @endif
            @if($appointment->status == 'Hoàn thành' || $appointment->status == 'Chưa thanh toán')
            <a href="{{ route('admin.appointments.checkout', ['appointment_id' => $appointment->id]) }}" class="btn btn-success">
                <i class="fas fa-money-bill-wave"></i> Thanh toán
            </a>
            @endif
            <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>
</div>

<!-- Appointment Details -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Chi tiết dịch vụ</h6>
    </div>
    <div class="card-body">
        @php
            // Tính tổng giá gốc và tổng giá sau giảm theo dịch vụ (price_snapshot)
            $totalOriginalPriceForAllocation = 0;
            $detailOriginalPrices = [];
            $totalAfterServiceLevel = 0; // Tổng sau khi áp dụng giảm giá theo từng dịch vụ
            
            foreach ($appointment->appointmentDetails as $detail) {
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
                    $service = \App\Models\Service::where('name', $detail->notes)->first();
                    if ($service) {
                        $originalPrice = $service->base_price ?? 0;
                    }
                }
                
                $detailOriginalPrices[$detail->id] = $originalPrice;
                $totalOriginalPriceForAllocation += $originalPrice;

                // Giá sau giảm của từng dịch vụ (service-level discount đã nằm trong price_snapshot)
                $finalAfterService = $detail->price_snapshot ?? $originalPrice;
                $totalAfterServiceLevel += $finalAfterService;
            }
            
            // Tính order-level discount dựa trên Payment (nếu có)
            // Payment.total đã là tổng sau khi trừ mã giảm giá hóa đơn
            $orderLevelDiscount = 0;
            $payment = \App\Models\Payment::where('appointment_id', $appointment->id)
                ->orderByDesc('id')
                ->first();
            
            if ($payment && $totalAfterServiceLevel > 0) {
                // Chênh lệch giữa tổng sau giảm theo dịch vụ và tổng thanh toán thực tế chính là order-level discount
                $orderLevelDiscount = max(0, $totalAfterServiceLevel - ($payment->total ?? $totalAfterServiceLevel));
            }
        @endphp
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Dịch vụ</th>
                        <th>Biến thể</th>
                        <th>Giá</th>
                        <th>Thời lượng (phút)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointment->appointmentDetails as $detail)
                        @php
                            // Lấy giá gốc đã tính ở trên
                            $originalPrice = $detailOriginalPrices[$detail->id] ?? 0;
                            
                            // Giá sau giảm từ service-level discount (price_snapshot)
                            $serviceLevelFinalPrice = $detail->price_snapshot ?? $originalPrice;
                            
                            // Discount từ service-level (tự động áp dụng khi tạo)
                            $serviceLevelDiscount = $originalPrice > 0 ? ($originalPrice - $serviceLevelFinalPrice) : 0;
                            
                            // Phân bổ order-level discount cho dịch vụ này theo tỷ lệ giá
                            $allocatedOrderDiscount = 0;
                            if ($orderLevelDiscount > 0 && $totalOriginalPriceForAllocation > 0 && $originalPrice > 0) {
                                // Tính tỷ lệ giá của dịch vụ này so với tổng
                                $priceRatio = $originalPrice / $totalOriginalPriceForAllocation;
                                // Phân bổ discount theo tỷ lệ
                                $allocatedOrderDiscount = $orderLevelDiscount * $priceRatio;
                            }
                            
                            // Tổng discount cho dịch vụ này = service-level + order-level
                            $totalDiscountForService = $serviceLevelDiscount + $allocatedOrderDiscount;
                            
                            // Giá cuối cùng sau tất cả discount
                            $finalPrice = max(0, $originalPrice - $totalDiscountForService);
                            
                            // Có discount nếu tổng discount > 0
                            $hasDiscount = $totalDiscountForService > 0 && $originalPrice > 0;
                        @endphp
                        <tr>
                            <td>
                                @if($detail->combo_id)
                                    {{ $detail->combo->name ?? ($detail->notes ?? 'Combo') }}
                                @elseif($detail->serviceVariant)
                                    {{ $detail->serviceVariant->service->name ?? 'N/A' }}
                                @else
                                    {{ $detail->notes ?? 'Dịch vụ đơn' }}
                                @endif
                            </td>
                            <td>
                                @if($detail->combo_id)
                                    <span class="badge badge-secondary">Combo</span>
                                @elseif($detail->serviceVariant)
                                    {{ $detail->serviceVariant->name ?? 'N/A' }}
                                @else
                                    <span class="badge badge-primary">Dịch vụ đơn</span>
                                @endif
                            </td>
                            <td>
                                @if($hasDiscount && $originalPrice > 0)
                                    <div style="display: flex; flex-direction: column; gap: 2px;">
                                        <span style="text-decoration: line-through; color: #999; font-size: 12px;">
                                            {{ number_format($originalPrice, 0, ',', '.') }} đ
                                        </span>
                                        <span style="color: #28a745; font-weight: 600; font-size: 14px;">
                                            {{ number_format($finalPrice, 0, ',', '.') }} đ
                                        </span>
                                        <small style="color: #ff4444; font-size: 11px; font-weight: 500;">
                                            Giảm: {{ number_format($totalDiscountForService, 0, ',', '.') }} đ
                                        </small>
                                    </div>
                                @else
                                    <span style="font-weight: 500;">{{ number_format($finalPrice, 0, ',', '.') }} đ</span>
                                @endif
                            </td>
                            <td>{{ $detail->duration ?? 0 }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Chưa có dịch vụ nào</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    @php
                        // Sử dụng lại các biến đã tính ở trên
                        $totalOriginalPrice = $totalOriginalPriceForAllocation;
                        
                        // Tính tổng service-level discount
                        $totalServiceDiscount = 0;
                        foreach ($appointment->appointmentDetails as $detail) {
                            $originalPrice = $detailOriginalPrices[$detail->id] ?? 0;
                            $serviceLevelFinalPrice = $detail->price_snapshot ?? $originalPrice;
                            $serviceLevelDiscount = $originalPrice > 0 ? ($originalPrice - $serviceLevelFinalPrice) : 0;
                            $totalServiceDiscount += $serviceLevelDiscount;
                        }
                        
                        // Order-level discount đã được tính ở trên (theo Payment)
                        // Không còn object $promotion, chỉ hiển thị tổng số tiền giảm
                        $promotionCode = null;
                        $promotionName = null;
                        
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
                            @if($promotionName)
                                <br><small class="text-muted">{{ $promotionName }}</small>
                            @endif
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
@endsection

