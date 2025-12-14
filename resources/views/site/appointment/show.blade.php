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
</style>
@endpush

@section('content')
<div class="appointment-detail-section" style="display: flex; justify-content: center; align-items: center; min-height: 80vh;">
    <div class="container" style="max-width: 900px; margin: 0 auto;">
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
        
        <div class="appointment-header">
            <h1 class="appointment-title">
                <i class="fa fa-calendar-check-o"></i>
                Chi tiết lịch đặt
            </h1>
            <div class="appointment-id">
                @if($appointment->booking_code)
                    Mã đơn đặt: <strong style="color: #667eea; font-size: 1.1em;">{{ $appointment->booking_code }}</strong>
                @else
                    Mã lịch đặt: #{{ str_pad($appointment->id, 6, '0', STR_PAD_LEFT) }}
                @endif
            </div>
        </div>
        
        <div class="appointment-content" style="display: flex; justify-content: center;">
            <div style="width: 100%; max-width: 800px;">
                <!-- Thông tin lịch đặt -->
                <div class="appointment-card">
                    <h3 class="card-title">
                        <i class="fa fa-info-circle"></i>
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
                            @php
                                $serviceImage = null;
                                $serviceId = null;
                                $serviceName = '';
                                $serviceUrl = '#';
                                
                                if ($detail->serviceVariant && $detail->serviceVariant->service) {
                                    $serviceImage = $detail->serviceVariant->service->image;
                                    $serviceId = $detail->serviceVariant->service->id;
                                    $serviceName = $detail->serviceVariant->service->name . ' - ' . $detail->serviceVariant->name;
                                    $serviceUrl = route('site.services.show', $serviceId);
                                } elseif ($detail->combo_id && $detail->combo) {
                                    $serviceImage = $detail->combo->image;
                                    $serviceId = $detail->combo->id;
                                    $serviceName = $detail->combo->name;
                                    // Combo có thể không có route riêng, dùng service route nếu có
                                    $serviceUrl = route('site.services.show', $serviceId);
                                } elseif ($detail->notes) {
                                    // Tìm service đơn theo tên
                                    $singleService = \App\Models\Service::where('name', $detail->notes)
                                        ->whereDoesntHave('serviceVariants')
                                        ->first();
                                    if ($singleService) {
                                        $serviceImage = $singleService->image;
                                        $serviceId = $singleService->id;
                                        $serviceName = $singleService->name;
                                        $serviceUrl = route('site.services.show', $serviceId);
                                    }
                                }
                                
                                // Ảnh được lưu ở legacy/images/products/
                                $imageUrl = $serviceImage 
                                    ? asset('legacy/images/products/' . $serviceImage) 
                                    : asset('legacy/images/products/default.jpg');
                                
                                // Lấy thuộc tính của service variant
                                $attributes = [];
                                if ($detail->serviceVariant && $detail->serviceVariant->variantAttributes) {
                                    foreach ($detail->serviceVariant->variantAttributes as $attr) {
                                        $attributes[] = [
                                            'name' => $attr->attribute_name,
                                            'value' => $attr->attribute_value
                                        ];
                                    }
                                }
                            @endphp
                            <a href="{{ $serviceUrl }}" class="service-item-link" style="text-decoration: none; color: inherit;">
                                <div class="service-item" 
                                     data-service-id="{{ $serviceId }}"
                                     data-service-type="{{ $detail->serviceVariant ? 'variant' : ($detail->combo_id ? 'combo' : 'single') }}">
                                    <div class="service-info">
                                        <div class="service-name">
                                            @if($detail->serviceVariant && $detail->serviceVariant->service)
                                                {{ $detail->serviceVariant->service->name }} - {{ $detail->serviceVariant->name }}
                                            @elseif($detail->combo_id && $detail->combo)
                                                Combo: {{ $detail->combo->name }}
                                            @elseif($detail->notes)
                                                {{ $detail->notes }}
                                            @else
                                                Dịch vụ không xác định
                                            @endif
                                        </div>
                                        <div class="service-details">
                                            @if($detail->serviceVariant && $detail->serviceVariant->service)
                                                Danh mục: {{ $detail->serviceVariant->service->name }}
                                            @elseif($detail->combo_id && $detail->combo)
                                                Loại: Combo
                                            @elseif($detail->notes)
                                                Loại: Dịch vụ đơn
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
                                    @if($serviceImage || count($attributes) > 0)
                                    <div class="service-tooltip">
                                        @if($serviceImage)
                                        <div class="tooltip-image">
                                            <img src="{{ $imageUrl }}" alt="{{ $serviceName }}" onerror="this.src='{{ asset('legacy/images/products/default.jpg') }}'">
                                        </div>
                                        @endif
                                        @if(count($attributes) > 0)
                                        <div class="tooltip-attributes">
                                            <div class="tooltip-attributes-title">Thuộc tính:</div>
                                            <div class="tooltip-attributes-list">
                                                @foreach($attributes as $attr)
                                                <span class="attribute-badge">
                                                    {{ $attr['name'] }}: {{ $attr['value'] }}
                                                </span>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </a>
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

