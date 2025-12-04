@extends('layouts.site')

@section('title', 'Chi tiết lịch đặt')

@push('styles')
<style>
    .appointment-detail-section {
        padding: 120px 0 80px;
        background: #f8f9fa;
        min-height: 70vh;
    }
    
    .appointment-header {
        background: #fff;
        padding: 30px;
        border-radius: 10px;
        margin-bottom: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .appointment-title {
        font-size: 32px;
        font-weight: 600;
        color: #4A3600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .appointment-title i {
        color: #BC9321;
    }
    
    .appointment-id {
        font-size: 16px;
        color: #666;
        margin-top: 10px;
    }
    
    .appointment-content {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
        margin-bottom: 30px;
    }
    
    @media (max-width: 991px) {
        .appointment-content {
            grid-template-columns: 1fr;
        }
    }
    
    .appointment-card {
        background: #fff;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .card-title {
        font-size: 20px;
        font-weight: 600;
        color: #4A3600;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .card-title i {
        color: #BC9321;
    }
    
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 15px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-weight: 500;
        color: #666;
        min-width: 150px;
    }
    
    .info-value {
        color: #333;
        font-weight: 500;
        text-align: right;
        flex: 1;
    }
    
    .status-badge {
        display: inline-block;
        padding: 6px 15px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-confirmed {
        background: #d4edda;
        color: #155724;
    }
    
    .status-processing {
        background: #cfe2ff;
        color: #084298;
    }
    
    .status-completed {
        background: #d1e7dd;
        color: #0f5132;
    }
    
    .status-cancelled {
        background: #f8d7da;
        color: #721c24;
    }
    
    .service-list {
        margin-top: 20px;
    }
    
    .service-item {
        display: flex;
        justify-content: space-between;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 10px;
    }
    
    .service-item:last-child {
        margin-bottom: 0;
    }
    
    .service-info {
        flex: 1;
    }
    
    .service-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }
    
    .service-details {
        font-size: 13px;
        color: #666;
    }
    
    .service-price {
        font-size: 18px;
        font-weight: 600;
        color: #BC9321;
        margin-left: 15px;
    }
    
    .total-section {
        background: linear-gradient(135deg, #4A3600 0%, #5a4a00 100%);
        color: #fff;
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
    }
    
    .total-row {
        display: flex;
        justify-content: space-between;
        font-size: 18px;
        margin-bottom: 10px;
    }
    
    .total-label {
        font-weight: 500;
    }
    
    .total-value {
        font-weight: 700;
        font-size: 24px;
    }
    
    .action-buttons {
        display: flex;
        gap: 15px;
        margin-top: 30px;
    }
    
    .btn-action {
        flex: 1;
        padding: 12px 20px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
    }
    
    .btn-back {
        background: #6c757d;
        color: #fff;
    }
    
    .btn-back:hover {
        background: #5a6268;
        transform: translateY(-2px);
    }
    
    .btn-print {
        background: #BC9321;
        color: #fff;
    }
    
    .btn-print:hover {
        background: #a8821a;
        transform: translateY(-2px);
    }
    
    .note-box {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-top: 15px;
        border-left: 4px solid #BC9321;
    }
    
    .note-label {
        font-weight: 600;
        color: #4A3600;
        margin-bottom: 8px;
    }
    
    .note-text {
        color: #666;
        line-height: 1.6;
    }
</style>
@endpush

@section('content')
<div class="appointment-detail-section">
    <div class="container">
        <div class="appointment-header">
            <h1 class="appointment-title">
                <i class="fa fa-calendar-check-o"></i>
                Chi tiết lịch đặt
            </h1>
            <div class="appointment-id">
                Mã lịch đặt: #{{ str_pad($appointment->id, 6, '0', STR_PAD_LEFT) }}
            </div>
        </div>
        
        <div class="appointment-content">
            <div>
                <!-- Thông tin lịch đặt -->
                <div class="appointment-card">
                    <h3 class="card-title">
                        <i class="fa fa-info-circle"></i>
                        Thông tin lịch đặt
                    </h3>
                    
                    <div class="info-row">
                        <span class="info-label">Trạng thái:</span>
                        <span class="info-value">
                            <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $appointment->status ?? 'pending')) }}">
                                {{ $appointment->status ?? 'Chờ xử lý' }}
                            </span>
                        </span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Ngày đặt:</span>
                        <span class="info-value">
                            {{ $appointment->start_at ? $appointment->start_at->format('d/m/Y') : 'N/A' }}
                        </span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Giờ bắt đầu:</span>
                        <span class="info-value">
                            {{ $appointment->start_at ? $appointment->start_at->format('H:i') : 'N/A' }}
                        </span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Giờ kết thúc:</span>
                        <span class="info-value">
                            {{ $appointment->end_at ? $appointment->end_at->format('H:i') : 'N/A' }}
                        </span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Thời lượng:</span>
                        <span class="info-value">
                            @if($appointment->start_at && $appointment->end_at)
                                {{ $appointment->start_at->diffInMinutes($appointment->end_at) }} phút
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                    
                    @if($appointment->employee && $appointment->employee->user)
                    <div class="info-row">
                        <span class="info-label">Nhân viên:</span>
                        <span class="info-value">
                            {{ $appointment->employee->user->name }}
                        </span>
                    </div>
                    @endif
                    
                    @if($appointment->user)
                    <div class="info-row">
                        <span class="info-label">Khách hàng:</span>
                        <span class="info-value">
                            {{ $appointment->user->name }}
                        </span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Số điện thoại:</span>
                        <span class="info-value">
                            {{ $appointment->user->phone ?? 'N/A' }}
                        </span>
                    </div>
                    
                    @if($appointment->user->email)
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value">
                            {{ $appointment->user->email }}
                        </span>
                    </div>
                    @endif
                    @endif
                    
                    @if($appointment->note)
                    <div class="note-box">
                        <div class="note-label">Ghi chú:</div>
                        <div class="note-text">{{ $appointment->note }}</div>
                    </div>
                    @endif
                </div>
                
                <!-- Danh sách dịch vụ -->
                @if($appointment->appointmentDetails->count() > 0)
                <div class="appointment-card" style="margin-top: 30px;">
                    <h3 class="card-title">
                        <i class="fa fa-scissors"></i>
                        Danh sách dịch vụ
                    </h3>
                    
                    <div class="service-list">
                        @foreach($appointment->appointmentDetails as $detail)
                            @if($detail->serviceVariant)
                            <div class="service-item">
                                <div class="service-info">
                                    <div class="service-name">
                                        {{ $detail->serviceVariant->name }}
                                    </div>
                                    <div class="service-details">
                                        @if($detail->serviceVariant->service)
                                            Danh mục: {{ $detail->serviceVariant->service->name }}
                                        @endif
                                        @if($detail->duration)
                                            | Thời lượng: {{ $detail->duration }} phút
                                        @endif
                                        @if($detail->status)
                                            | Trạng thái: {{ $detail->status }}
                                        @endif
                                    </div>
                                </div>
                                <div class="service-price">
                                    {{ number_format($detail->price_snapshot ?? 0, 0, ',', '.') }}đ
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                    
                    <div class="total-section">
                        <div class="total-row">
                            <span class="total-label">Tổng cộng:</span>
                            <span class="total-value">{{ number_format($totalPrice, 0, ',', '.') }}đ</span>
                        </div>
                    </div>
                </div>
                @endif
                
            </div>
            
            <!-- Sidebar -->
            <div>
                <div class="appointment-card">
                    <h3 class="card-title">
                        <i class="fa fa-cog"></i>
                        Thao tác
                    </h3>
                    
                    <div class="action-buttons">
                        <a href="{{ route('site.cart.index') }}" class="btn-action btn-back">
                            <i class="fa fa-arrow-left"></i> Quay lại
                        </a>
                        <button type="button" class="btn-action btn-print" onclick="window.print()">
                            <i class="fa fa-print"></i> In
                        </button>
                        @if(isset($canReview) && $canReview)
                            <a href="{{ route('site.reviews.create', ['appointment_id' => $appointment->id]) }}" class="btn-action" style="background: #ffc107; color: #000;">
                                <i class="fa fa-star"></i> Đánh giá dịch vụ
                            </a>
                        @elseif(isset($existingReview) && $existingReview)
                            <a href="{{ route('site.reviews.edit', $existingReview->id) }}" class="btn-action" style="background: #17a2b8; color: #fff;">
                                <i class="fa fa-edit"></i> Xem/Sửa đánh giá
                            </a>
                        @endif
                    </div>
                    
                    @if($appointment->status === 'Hoàn thành' && auth()->check() && $appointment->user_id == auth()->id())
                        @if(isset($existingReview) && $existingReview)
                            <div class="alert alert-warning mt-3" style="border-left: 4px solid #ffc107;">
                                <i class="fa fa-exclamation-triangle"></i> 
                                <strong>Bạn đã đánh giá lịch hẹn này rồi.</strong> Mỗi lịch hẹn chỉ có thể đánh giá một lần. Bạn có thể sửa đánh giá hiện có.
                            </div>
                        @else
                            <div class="alert alert-info mt-3" style="border-left: 4px solid #17a2b8;">
                                <i class="fa fa-info-circle"></i> 
                                <strong>Bạn đã hoàn thành dịch vụ!</strong> Hãy chia sẻ trải nghiệm của bạn để giúp chúng tôi cải thiện dịch vụ.
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

