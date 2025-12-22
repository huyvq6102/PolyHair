@extends('layouts.site')

@section('title', 'Chi tiết lịch đặt')

@section('content')
@push('styles')
<style>
    .appointment-detail-container {
        background: #f8f8f8;
        min-height: 100vh;
        padding: 120px 20px 40px;
    }

    .appointment-detail-wrapper {
        max-width: 900px;
        margin: 0 auto;
    }

    /* Header Section */
    .appointment-header-section {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .header-icon {
        width: 48px;
        height: 48px;
        background: #ffc107;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        font-weight: bold;
    }

    .header-content {
        flex: 1;
    }

    .header-content h1 {
        font-size: 28px;
        font-weight: 700;
        color: #333;
        margin: 0 0 8px 0;
    }

    .booking-code {
        font-size: 14px;
        color: #666;
    }

    .booking-code strong {
        color: #0d6efd;
        font-weight: 600;
    }

    /* Two Column Layout */
    .appointment-details-grid {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 24px;
        margin-bottom: 24px;
    }

    @media (max-width: 768px) {
        .appointment-details-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Info Card */
    .info-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f0f0f0;
    }

    .card-icon {
        width: 32px;
        height: 32px;
        background: #ff9800;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
    }

    .card-title {
        font-size: 20px;
        font-weight: 600;
        color: #333;
        margin: 0;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-size: 14px;
        color: #666;
        font-weight: 500;
        min-width: 130px;
    }

    .info-value {
        font-size: 14px;
        color: #333;
        font-weight: 500;
        text-align: right;
        flex: 1;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-pending {
        background: #ffc107;
        color: #333;
    }

    .status-confirmed {
        background: #28a745;
        color: #fff;
    }

    .status-cancelled {
        background: #6c757d;
        color: #fff;
    }

    .status-in-progress {
        background: #007bff;
        color: #fff;
    }

    .status-completed {
        background: #28a745;
        color: #fff;
    }

    /* Services Card */
    .service-list-wrapper {
        display: flex;
        flex-direction: column;
        min-height: 0;
    }

    .service-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 15px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        border: 1px solid #e9ecef;
        width: 100%;
        box-sizing: border-box;
    }

    .service-item:hover {
        background: #e9ecef;
        border-color: #BC9321;
        box-shadow: 0 2px 6px rgba(188, 147, 33, 0.15);
    }

    .service-item:last-child {
        margin-bottom: 0;
    }

    .service-info {
        flex: 1;
        min-width: 0;
    }

    .service-name {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        line-height: 1.4;
        word-wrap: break-word;
    }

    .service-meta {
        display: flex;
        gap: 12px;
        font-size: 12px;
        color: #999;
    }

    .service-type {
        font-size: 11px;
        color: #666;
        background: #e9ecef;
        padding: 3px 8px;
        border-radius: 12px;
        font-weight: 500;
    }

    .service-duration {
        font-size: 11px;
        color: #666;
    }

    .service-price {
        display: flex;
        align-items: baseline;
        margin-left: 12px;
        flex-shrink: 0;
    }

    .price-amount {
        font-size: 18px;
        font-weight: 700;
        color: #333;
    }

    .price-currency {
        font-size: 14px;
        color: #333;
        margin-left: 2px;
    }

    .total-section {
        margin-top: 25px;
        padding: 20px;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        background: #ffffff;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        color: #212529;
        font-size: 0.95rem;
    }

   .total-row.discount {
        color: #212529;
    }

    .total-row.discount .total-amount {
        color: #dc3545;
    }

     .total-row:last-child {
        border-top: 1px solid #e0e0e0;
        margin-top: 10px;
        padding-top: 15px;
        font-weight: 700;
        font-size: 1rem;
    }

     .total-label {
        font-weight: 500;
    }

     .total-amount {
        font-weight: 600;
    }

    /* Cancel Button */
    .cancel-section {
        background: white;
        border-radius: 12px;
        padding: 0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .cancel-button {
        background: transparent;
        border: none;
        color: #666;
        font-size: 16px;
        padding: 16px 24px;
        width: 100%;
        text-align: left;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: background-color 0.2s;
    }

    .cancel-button:hover {
        background: #f8f8f8;
        color: #333;
    }

    .cancel-button-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .cancel-icon {
        width: 24px;
        height: 24px;
        border: 1px solid #999;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #999;
        font-size: 18px;
        line-height: 1;
    }

    .chevron-right {
        color: #999;
        font-size: 18px;
    }
</style>
@endpush

<div class="appointment-detail-container">
    <div class="appointment-detail-wrapper">
        <!-- Header Section -->
        <div class="appointment-header-section">
            <div class="header-icon">✓</div>
            <div class="header-content">
                <h1>Chi tiết lịch đặt</h1>
                <div class="booking-code">
                    Mã đơn đặt: <strong>{{ $appointment->booking_code ?? 'POLY-HB-' . str_pad($appointment->id, 3, '0', STR_PAD_LEFT) }}</strong>
                </div>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="appointment-details-grid">
            <!-- Left Panel: Appointment Information -->
            <div class="info-card">
                <div class="card-header">
                    <div class="card-icon">ℹ</div>
                    <h3 class="card-title">Thông tin lịch đặt</h3>
                </div>

                <div class="info-row">
                    <span class="info-label">Trạng thái:</span>
                    <span class="info-value">
                        @php
                            $status = $appointment->status ?? 'Chờ xử lý';
                            $statusClass = 'status-pending';
                            if ($status === 'Đã xác nhận') {
                                $statusClass = 'status-confirmed';
                            } elseif ($status === 'Chờ xử lý') {
                                $statusClass = 'status-pending';
                            } elseif ($status === 'Đang thực hiện') {
                                $statusClass = 'status-in-progress';
                            } elseif ($status === 'Hoàn thành') {
                                $statusClass = 'status-completed';
                            } elseif ($status === 'Đã hủy') {
                                $statusClass = 'status-cancelled';
                            }
                        @endphp
                        <span class="status-badge {{ $statusClass }}">{{ $status }}</span>
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Ngày đặt:</span>
                    <span class="info-value">{{ $appointment->created_at ? \Carbon\Carbon::parse($appointment->created_at)->format('d/m/Y') : 'N/A' }}</span>
                </div>

                @if($appointment->start_at)
                <div class="info-row">
                    <span class="info-label">Giờ bắt đầu:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($appointment->start_at)->format('H:i') }}</span>
                </div>
                @endif

                @if($appointment->end_at)
                <div class="info-row">
                    <span class="info-label">Giờ kết thúc:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($appointment->end_at)->format('H:i') }}</span>
                </div>
                @endif

                @php
                    $totalDuration = 0;
                    foreach ($appointment->appointmentDetails as $detail) {
                        $totalDuration += $detail->duration ?? 0;
                    }
                @endphp

                @if($totalDuration > 0)
                <div class="info-row">
                    <span class="info-label">Thời lượng:</span>
                    <span class="info-value">{{ $totalDuration }} phút</span>
                </div>
                @endif

                @if($appointment->employee && $appointment->employee->user)
                <div class="info-row">
                    <span class="info-label">Nhân viên:</span>
                    <span class="info-value">{{ $appointment->employee->user->name }}</span>
                </div>
                @endif

                @if($appointment->user)
                <div class="info-row">
                    <span class="info-label">Khách hàng:</span>
                    <span class="info-value">{{ $appointment->user->name }}</span>
                </div>

                @if($appointment->user->phone)
                <div class="info-row">
                    <span class="info-label">Số điện thoại:</span>
                    <span class="info-value">{{ $appointment->user->phone }}</span>
                </div>
                @endif

                @if($appointment->user->email)
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $appointment->user->email }}</span>
                </div>
                @endif
                @endif
            </div>

            <!-- Right Panel: Services List -->
            <div class="info-card">
                <div class="card-header">
                    <div class="card-icon">✂</div>
                    <h3 class="card-title">Danh sách dịch vụ</h3>
                </div>

                <div class="service-list-wrapper">
                    @if($appointment->appointmentDetails->count() > 0)
                        @foreach($appointment->appointmentDetails as $detail)
                            <div class="service-item">
                                <div class="service-info">
                                    <div class="service-name">
                                         @if($detail->serviceVariant)
                                            {{ $detail->serviceVariant->name }}
                                        @elseif($detail->combo)
                                            {{ $detail->combo->name }}
                                        @elseif($detail->notes)
                                            {{ $detail->notes }}
                                        @else
                                            Dịch vụ không xác định
                                        @endif
                                    </div>
                                    <div class="service-meta">
                                        @if($detail->duration)
                                            <span class="service-duration">{{ $detail->duration }} phút</span>
                                        @endif
                                    </div>
                                    @if($detail->serviceVariant && $detail->serviceVariant->variantAttributes && $detail->serviceVariant->variantAttributes->count() > 0)
                                    <div class="service-attributes" style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 6px;">
                                        @foreach($detail->serviceVariant->variantAttributes as $attr)
                                        <span class="attribute-tag" style="background-color: #f0f0f0; color: #333; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                            <strong>{{ $attr->attribute_name }}:</strong> {{ $attr->attribute_value }}
                                        </span>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                                <div class="service-price">
                                    @php
                                        $price = $detail->price_snapshot ?? 0;
                                        if ($detail->serviceVariant && !$price) {
                                            $price = $detail->serviceVariant->price ?? 0;
                                        } elseif ($detail->combo && !$price) {
                                            $price = $detail->combo->price ?? 0;
                                        }
                                    @endphp
                                    <span class="price-amount">{{ number_format($price, 0, ',', '.') }}</span>
                                    <span class="price-currency">₫</span>
                                </div>
                            </div>
                        @endforeach

                        <!-- Total Section -->
                        <div class="total-section">
                            @php
                                // Tính toán các giá trị để hiển thị
                                // Tổng tiền = giá gốc của tất cả dịch vụ
                                $displayTotalPrice = $totalOriginalPrice ?? $subtotal ?? 0;

                                // Số tiền đã giảm = tổng tất cả các khoản giảm giá
                                $displayDiscount = $serviceLevelDiscount ?? ($totalDiscount ?? 0);

                                // Số tiền cần thanh toán = tổng sau khi giảm giá
                                $displayTotalAfterDiscount = $totalAfterDiscount ?? $subtotal ?? 0;
                            @endphp


                             <div class="total-row">
                                <span class="total-label">Tổng giá gốc:</span>
                                <span class="total-amount">{{ number_format($displayTotalPrice, 0, ',', '.') }} ₫</span>
                            </div>
                            @if($displayDiscount > 0)
                            <div class="total-row discount">
                                <span class="total-label">Giảm giá tự động (từng dịch vụ):</span>
                                <span class="total-amount">-{{ number_format($displayDiscount, 0, ',', '.') }} ₫</span>
                            </div>
                            <div class="total-row discount">
                                <span class="total-label">Tổng giảm giá:</span>
                                <span class="total-amount">-{{ number_format($displayDiscount, 0, ',', '.') }} ₫</span>
                            </div>
                            @endif
                            <div class="total-row">
                                <span class="total-label">Tổng cộng:</span>
                                <span class="total-amount">{{ number_format($displayTotalAfterDiscount, 0, ',', '.') }} ₫</span>
                            </div>
                        </div>
                    @else
                        <p style="color: #999; text-align: center; padding: 20px;">Chưa có dịch vụ nào</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Cancel Button - Hiển thị cho cả guest và logged in user -->
        @php
            $canCancel = false;
            if ($appointment->status === 'Chờ xử lý') {
                $createdAt = \Carbon\Carbon::parse($appointment->created_at);
                $minutesSinceCreated = $createdAt->diffInMinutes(now());
                $canCancel = $minutesSinceCreated <= 30; // Cho phép hủy trong vòng 30 phút
            }
        @endphp

        @if($appointment->status !== 'Đã hủy' && $canCancel)
            <div class="cancel-section">
                 <form id="cancelAppointmentForm" action="{{ route('site.appointment.cancel', $appointment->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="cancel-button">
                        <div class="cancel-button-left">
                            <div class="cancel-icon">×</div>
                            <span>Hủy lịch</span>
                        </div>
                        <span class="chevron-right">›</span>
                    </button>
                </form>
            </div>
             @elseif($appointment->status !== 'Đã hủy' && $appointment->status === 'Đã xác nhận')
            <div class="cancel-section" style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 12px; padding: 16px; text-align: center; color: #856404;">
                <p style="margin: 0; font-size: 14px;">
                    <i class="fa fa-info-circle"></i> Lịch hẹn đã được xác nhận. Không thể hủy sau 30 phút kể từ khi đặt.
                </p>
            </div>
        @endif
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cancelForm = document.getElementById('cancelAppointmentForm');
    if (cancelForm) {
        cancelForm.addEventListener('submit', function(e) {
            e.preventDefault();

            if (confirm('Bạn có chắc chắn muốn hủy lịch hẹn này?')) {
                // Refresh CSRF token trước khi submit để tránh lỗi 419
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (csrfToken) {
                    const tokenInput = cancelForm.querySelector('input[name="_token"]');
                    if (tokenInput) {
                        tokenInput.value = csrfToken.getAttribute('content');
                    }
                }

                // Disable button để tránh double submit
                const submitBtn = cancelForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.6';
                    submitBtn.style.cursor = 'not-allowed';
                }

                // Submit form
                cancelForm.submit();
            }
        });
    }

    // Tự động ẩn nút hủy sau 30 phút kể từ khi tạo appointment
    @if($appointment->status === 'Chờ xử lý' && isset($appointment->created_at))
        @php
            $createdAt = \Carbon\Carbon::parse($appointment->created_at);
            $minutesSinceCreated = $createdAt->diffInMinutes(now());
            $remainingMinutes = max(0, 30 - $minutesSinceCreated);
            $remainingSeconds = $remainingMinutes * 60;
        @endphp
        
        @if($remainingSeconds > 0)
            setTimeout(function() {
                const cancelSection = document.querySelector('.cancel-section');
                if (cancelSection) {
                    cancelSection.style.display = 'none';
                    
                    // Hiển thị thông báo nếu chưa có
                    const infoSection = document.querySelector('.cancel-section[style*="background: #fff3cd"]');
                    if (!infoSection) {
                        const appointmentDetailWrapper = document.querySelector('.appointment-detail-wrapper');
                        if (appointmentDetailWrapper) {
                            const infoDiv = document.createElement('div');
                            infoDiv.className = 'cancel-section';
                            infoDiv.style.cssText = 'background: #fff3cd; border: 1px solid #ffc107; border-radius: 12px; padding: 16px; text-align: center; color: #856404;';
                            infoDiv.innerHTML = '<p style="margin: 0; font-size: 14px;"><i class="fa fa-info-circle"></i> Lịch hẹn đã được xác nhận. Không thể hủy sau 30 phút kể từ khi đặt.</p>';
                            appointmentDetailWrapper.appendChild(infoDiv);
                        }
                    }
                }
            }, {{ $remainingSeconds * 1000 }}); // Chuyển đổi từ giây sang milliseconds
        @endif
    @endif
});
</script>
@endsection
