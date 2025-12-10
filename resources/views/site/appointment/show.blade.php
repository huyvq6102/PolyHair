@extends('layouts.site')

@section('title', 'Chi tiết lịch đặt')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/appointment-show.css') }}">
@endpush

@section('content')
<div class="appointment-detail-section" style="display: flex; justify-content: center; align-items: center; min-height: 80vh;">
    <div class="container" style="max-width: 900px; margin: 0 auto;">
        @if(!auth()->check())
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-bottom: 20px; border-left: 4px solid #28a745; background-color: #d4edda; color: #155724; padding: 15px 20px; border-radius: 5px;">
            <i class="fa fa-check-circle"></i> Đặt lịch thành công! 
            <a href="{{ route('site.home') }}" class="btn btn-primary btn-sm" style="margin-left: 15px;">
                <i class="fa fa-home"></i> Quay về trang chủ
            </a>
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
                            <span class="status-badge {{ $statusClass }}" style="background-color: {{ $statusColor }}; color: #fff; padding: 8px 16px; border-radius: 20px; font-weight: bold; font-size: 14px; display: inline-block; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
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

@endsection

