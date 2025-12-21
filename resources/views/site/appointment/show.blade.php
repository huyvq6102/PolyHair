@extends('layouts.site')

@section('title', 'Chi tiết lịch đặt')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/appointment-show.css') }}">
<style>
    .btn-primary:hover {
        background: linear-gradient(135deg, #d8b26a 0%, #8b5a2b 100%) !important;
        color: #fff !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(139, 90, 43, 0.3);
    }
    
    .appointment-header-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 25px 30px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 25px;
    }
    
    .appointment-title {
        color: #d8b26a !important;
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .appointment-title-icon {
        width: 40px;
        height: 40px;
        background: #ffc107;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
    }
    
    .appointment-id {
        margin-top: 15px;
        font-size: 1rem;
        color: #6c757d;
    }
    
    .appointment-id strong {
        color: #667eea;
        font-size: 1.1em;
        font-weight: 600;
    }
    
    .two-column-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
        margin-top: 0;
    }
    
    .info-card, .services-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .card-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #212529;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .card-title-icon {
        width: 32px;
        height: 32px;
        background: #ff9800;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 16px;
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
        font-weight: 500;
        color: #495057;
        font-size: 0.95rem;
    }
    
    .info-value {
        color: #212529;
        font-size: 0.95rem;
        font-weight: 500;
        text-align: right;
    }
    
    .status-badge {
        background-color: #ffc107;
        color: #fff;
        padding: 6px 14px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 13px;
        display: inline-block;
    }
    
    .service-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 15px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 8px;
        border: 1px solid #e9ecef;
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
    
    .service-tags {
        display: flex;
        gap: 12px;
        font-size: 12px;
        color: #999;
    }
    
    .service-tag {
        font-size: 11px;
        color: #666;
        background: #e9ecef;
        padding: 3px 8px;
        border-radius: 12px;
        font-weight: 500;
    }
    
    .service-price-row {
        display: flex;
        align-items: baseline;
        margin-left: 12px;
        flex-shrink: 0;
    }
    
    .service-price {
        font-size: 18px;
        font-weight: 700;
        color: #333;
    }
    
    .pricing-summary {
        margin-top: 25px;
        padding: 20px;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        background: #ffffff;
    }
    
    .pricing-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        color: #212529;
        font-size: 0.95rem;
    }
    
    .pricing-row.discount {
        color: #212529;
    }
    
    .pricing-row.discount .pricing-value {
        color: #dc3545;
    }
    
    .pricing-row.total {
        border-top: 1px solid #e0e0e0;
        margin-top: 10px;
        padding-top: 15px;
        font-weight: 700;
        font-size: 1rem;
    }
    
    .pricing-label {
        font-weight: 500;
    }
    
    .pricing-value {
        font-weight: 600;
    }
    
    .appointment-detail-section {
        padding-top: 120px;
    }
    
    @media (max-width: 992px) {
        .two-column-layout {
            grid-template-columns: 1fr;
        }
        
        .appointment-detail-section {
            padding-top: 100px;
        }
    }
</style>
@endpush

@section('content')
<div class="appointment-detail-section" style="min-height: 80vh;">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 30px 15px;">
        @if(!auth()->check())
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-bottom: 30px; border-left: 4px solid #28a745; background-color: #d4edda; color: #155724; padding: 20px 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fa fa-check-circle" style="font-size: 24px; color: #28a745;"></i>
                    <strong style="font-size: 16px;">Đặt lịch thành công!</strong>
                    <span style="font-size: 14px;">Vui lòng kiểm tra thông tin lịch đặt của bạn bên dưới.</span>
                </div>
                <a href="{{ route('site.home') }}" class="btn btn-primary" style="background: #000; border: none; color: #fff; padding: 10px 20px; border-radius: 6px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; white-space: nowrap;">
                    <i class="fa fa-home"></i> Quay về trang chủ
                </a>
            </div>
        </div>
        @endif
        
        <div class="appointment-header-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="appointment-title">
                        <span class="appointment-title-icon">
                            <i class="fa fa-check"></i>
                        </span>
                        Chi tiết lịch đặt
                    </h1>
                    <div class="appointment-id">
                        @if($appointment->booking_code)
                            Mã đơn đặt: <strong>{{ $appointment->booking_code }}</strong>
                        @else
                            Mã lịch đặt: #{{ str_pad($appointment->id, 6, '0', STR_PAD_LEFT) }}
                        @endif
                    </div>
                </div>
                @auth
                    <a href="{{ route('site.customers.show', auth()->id()) }}?tab=history" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Quay lại
                    </a>
                @endauth
            </div>
        </div>
        
        <div class="two-column-layout">
            <!-- Cột trái: Thông tin lịch đặt -->
            <div class="info-card">
                <h3 class="card-title">
                    <span class="card-title-icon">
                        <i class="fa fa-info"></i>
                    </span>
                    Thông tin lịch đặt
                </h3>
                    
                    <div class="info-row">
                        <span class="info-label">Trạng thái:</span>
                        <span class="info-value">
                            @php
                                $status = $appointment->status ?? 'Chờ xử lý';
                                $statusClass = 'status-pending';
                                $statusColor = '#ffc107'; // Mặc định vàng
                                
                                if ($status === 'Đã xác nhận') {
                                    $statusClass = 'status-confirmed';
                                    $statusColor = '#28a745'; // Xanh lá
                                } elseif ($status === 'Chờ xử lý') {
                                    $statusClass = 'status-pending';
                                    $statusColor = '#ffc107'; // Vàng
                                } elseif ($status === 'Đang thực hiện') {
                                    $statusClass = 'status-in-progress';
                                    $statusColor = '#007bff'; // Xanh dương
                                } elseif ($status === 'Hoàn thành') {
                                    $statusClass = 'status-completed';
                                    $statusColor = '#28a745'; // Xanh lá
                                } elseif ($status === 'Đã thanh toán') {
                                    $statusClass = 'status-paid';
                                    $statusColor = '#17a2b8'; // Xanh nhạt
                                } elseif ($status === 'Chưa thanh toán') {
                                    $statusClass = 'status-unpaid';
                                    $statusColor = '#dc3545'; // Đỏ
                                } elseif ($status === 'Đã hủy') {
                                    $statusClass = 'status-cancelled';
                                    $statusColor = '#6c757d'; // Xám
                                }
                            @endphp
                            <span id="appointment-status-badge" class="status-badge {{ $statusClass }}" style="background-color: {{ $statusColor }}; color: #fff; padding: 8px 16px; border-radius: 20px; font-weight: bold; font-size: 14px; display: inline-block; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                {{ $status }}
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
                    
                    <div class="info-row">
                        <span class="info-label">Khách hàng:</span>
                        <span class="info-value">
                            {{ $appointment->user ? $appointment->user->name : ($appointment->guest_name ?? 'N/A') }}
                        </span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Số điện thoại:</span>
                        <span class="info-value">
                            {{ $appointment->user ? ($appointment->user->phone ?? 'N/A') : ($appointment->guest_phone ?? 'N/A') }}
                        </span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value">
                            {{ $appointment->user ? ($appointment->user->email ?? 'N/A') : ($appointment->guest_email ?? 'N/A') }}
                        </span>
                    </div>
                    
                    @if($appointment->note)
                    <div class="info-row">
                        <span class="info-label">Ghi chú:</span>
                        <span class="info-value" style="text-align: left;">{{ $appointment->note }}</span>
                    </div>
                    @endif
            </div>
            
            <!-- Cột phải: Danh sách dịch vụ -->
            @if($appointment->appointmentDetails->count() > 0)
            <div class="services-card">
                <h3 class="card-title">
                    <span class="card-title-icon">
                        <i class="fa fa-scissors"></i>
                    </span>
                    Danh sách dịch vụ
                </h3>
                
                <div class="service-list">
                    @foreach($appointment->appointmentDetails as $detail)
                        <div class="service-item">
                            <div class="service-info">
                                <div class="service-name">
                                    @if($detail->serviceVariant)
                                        {{ $detail->serviceVariant->name }}
                                    @elseif($detail->combo_id && $detail->combo)
                                        {{ $detail->combo->name }}
                                    @elseif($detail->notes)
                                        {{ $detail->notes }}
                                    @else
                                        Dịch vụ không xác định
                                    @endif
                                </div>
                                <div class="service-tags">
                                    @if($detail->serviceVariant && $detail->serviceVariant->variantAttributes)
                                        @foreach($detail->serviceVariant->variantAttributes as $attr)
                                            <span class="service-tag">{{ $attr->attribute_name }}: {{ $attr->attribute_value }}</span>
                                        @endforeach
                                    @endif
                                    @if($detail->duration)
                                        <span class="service-tag">{{ $detail->duration }} phút</span>
                                    @endif
                                </div>
                            </div>
                            <div class="service-price-row">
                                <span class="service-price">{{ number_format($detail->price_snapshot ?? 0, 0, ',', '.') }} ₫</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Phần tổng tiền -->
                <div class="pricing-summary">
                    <div class="pricing-row">
                        <span class="pricing-label">Tổng giá gốc:</span>
                        <span class="pricing-value">{{ number_format($totalOriginalPrice, 0, ',', '.') }} ₫</span>
                    </div>
                    @if($totalDiscount > 0)
                    <div class="pricing-row discount">
                        <span class="pricing-label">Giảm giá tự động (từng dịch vụ):</span>
                        <span class="pricing-value">-{{ number_format($totalDiscount, 0, ',', '.') }} ₫</span>
                    </div>
                    <div class="pricing-row discount">
                        <span class="pricing-label">Tổng giảm giá:</span>
                        <span class="pricing-value">-{{ number_format($totalDiscount, 0, ',', '.') }} ₫</span>
                    </div>
                    @endif
                    <div class="pricing-row total">
                        <span class="pricing-label">Tổng cộng:</span>
                        <span class="pricing-value">{{ number_format($totalPrice, 0, ',', '.') }} ₫</span>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Pusher configuration
        const pusherKey = '{{ config("broadcasting.connections.pusher.key", env("PUSHER_APP_KEY")) }}';
        const pusherCluster = '{{ config("broadcasting.connections.pusher.options.cluster", env("PUSHER_APP_CLUSTER", "ap1")) }}';
        
        if (!pusherKey) {
            console.error('Pusher key is not configured. Please set PUSHER_APP_KEY in .env file.');
            return;
        }
        
        const pusher = new Pusher(pusherKey, {
            cluster: pusherCluster,
            encrypted: true,
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                }
            }
        });

        // Lắng nghe private channel cho appointment này
        const channel = pusher.subscribe('private-appointment.{{ $appointment->id }}');
        
        // Xử lý subscription success
        channel.bind('pusher:subscription_succeeded', function() {
            console.log('Successfully subscribed to appointment channel:', '{{ $appointment->id }}');
        });
        
        // Xử lý subscription error
        channel.bind('pusher:subscription_error', function(status) {
            console.error('Failed to subscribe to appointment channel:', status);
        });
        
        // Lắng nghe event status.updated
        channel.bind('status.updated', function(data) {
            console.log('Appointment status updated event received:', data);
            console.log('New status:', data.status);
            
            // Cập nhật status badge
            const statusBadge = document.getElementById('appointment-status-badge');
            if (!statusBadge) {
                console.error('Status badge element not found!');
                return;
            }
            
            const status = data.status;
            console.log('Updating status badge to:', status);
                const statusColors = {
                    'Chờ xử lý': '#ffc107',
                    'Đã xác nhận': '#28a745',
                    'Đang thực hiện': '#007bff',
                    'Hoàn thành': '#28a745',
                    'Đã hủy': '#6c757d',
                    'Đã thanh toán': '#17a2b8',
                    'Chưa thanh toán': '#dc3545'
                };
                
                const statusClasses = {
                    'Chờ xử lý': 'status-pending',
                    'Đã xác nhận': 'status-confirmed',
                    'Đang thực hiện': 'status-in-progress',
                    'Hoàn thành': 'status-completed',
                    'Đã hủy': 'status-cancelled',
                    'Đã thanh toán': 'status-paid',
                    'Chưa thanh toán': 'status-unpaid'
                };
                
                const color = statusColors[status] || '#ffc107';
                const statusClass = statusClasses[status] || 'status-pending';
                
                // Cập nhật text và style
                statusBadge.textContent = status;
                statusBadge.className = 'status-badge ' + statusClass;
                statusBadge.style.backgroundColor = color;
                
                // Thêm animation
                statusBadge.style.transition = 'all 0.3s ease';
                statusBadge.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    statusBadge.style.transform = 'scale(1)';
                }, 300);
                
                // Hiển thị thông báo
                if (typeof toastr !== 'undefined') {
                    toastr.success('Trạng thái lịch hẹn đã được cập nhật: ' + status, 'Thông báo', {
                        timeOut: 3000,
                        positionClass: 'toast-top-right'
                    });
                } else {
                    alert('Trạng thái lịch hẹn đã được cập nhật: ' + status);
                }
            }
        });
        
        // Xử lý lỗi kết nối
        pusher.connection.bind('error', function(err) {
            console.error('Pusher connection error:', err);
        });
        
        pusher.connection.bind('connected', function() {
            console.log('Pusher connected successfully');
        });
    });
</script>
@endpush

@endsection

