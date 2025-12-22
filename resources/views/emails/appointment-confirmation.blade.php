<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đặt lịch</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color:#0b57d0;
            color: #fff;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .info-box {
            background-color: #fff;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 4px solid #0b57d0;
        }
        .info-row {
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color:rgb(0, 0, 0);
            display: inline-block;
            width: 150px;
        }
        .value {
            color: #333;
        }
        .service-list {
            margin: 10px 0;
            padding-left: 20px;
        }
        .service-item {
            margin: 5px 0;
            padding: 5px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #ddd;
            margin-top: 20px;
        }
     
    </style>
</head>
<body>
    <div class="header">
        <h1>Xác nhận đặt lịch thành công</h1>
    </div>
    
    <div class="content">
        <p>Xin chào <strong>{{ $appointment->user->name }}</strong>,</p>
        
        <p>Cảm ơn bạn đã đặt lịch tại PolyHair. Chúng tôi đã nhận được yêu cầu đặt lịch của bạn và đang xử lý.</p>
        
        <div class="info-box">
            <h3 style="margin-top: 0; color: #4A3600;">Thông tin đặt lịch</h3>
            
            <div class="info-row">
                <span class="label">Mã đặt lịch:</span>
                <span class="value">#{{ $appointment->id }}</span>
            </div>
            
            <div class="info-row">
                <span class="label">Họ và tên:</span>
                <span class="value">{{ $appointment->user->name }}</span>
            </div>
            
            <div class="info-row">
                <span class="label">Số điện thoại:</span>
                <span class="value">{{ $appointment->user->phone }}</span>
            </div>
            
            @if($appointment->user->email)
            <div class="info-row">
                <span class="label">Email:</span>
                <span class="value">{{ $appointment->user->email }}</span>
            </div>
            @endif
            
            @if($appointment->employee)
            <div class="info-row">
                <span class="label">Kỹ thuật viên:</span>
                <span class="value">{{ $appointment->employee->user->name }}</span>
            </div>
            @endif
            
            <div class="info-row">
                <span class="label">Ngày đặt lịch:</span>
                <span class="value">{{ \Carbon\Carbon::parse($appointment->start_at)->format('d/m/Y') }}</span>
            </div>
            
            <div class="info-row">
                <span class="label">Giờ bắt đầu:</span>
                <span class="value">{{ \Carbon\Carbon::parse($appointment->start_at)->format('H:i') }}</span>
            </div>
            
            <div class="info-row">
                <span class="label">Giờ kết thúc:</span>
                <span class="value">{{ \Carbon\Carbon::parse($appointment->end_at)->format('H:i') }}</span>
            </div>
            
            <div class="info-row">
                <span class="label">Trạng thái:</span>
                <span class="value">{{ $appointment->status }}</span>
            </div>
            
            @if($appointment->appointmentDetails && $appointment->appointmentDetails->count() > 0)
            <div class="info-row">
                <span class="label">Dịch vụ:</span>
                <div class="value">
                    <ul class="service-list">
                        @foreach($appointment->appointmentDetails as $detail)
                            <li class="service-item">
                                @if($detail->serviceVariant)
                                    {{ $detail->serviceVariant->service->name }} - {{ $detail->serviceVariant->name }}
                                @elseif($detail->combo)
                                    {{ $detail->combo->name }} (Combo)
                                @elseif($detail->notes)
                                    {{ $detail->notes }}
                                @endif
                                @if($detail->price_snapshot)
                                    - {{ number_format($detail->price_snapshot, 0, ',', '.') }} VNĐ
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
            
            @if($appointment->note)
            <div class="info-row">
                <span class="label">Ghi chú:</span>
                <span class="value">{{ $appointment->note }}</span>
            </div>
            @endif
        </div>
        
        {{-- <p><strong>Lưu ý:</strong> Vui lòng thanh toán để hoàn tất đặt lịch. Bạn có thể thanh toán tại giỏ hàng của mình.</p>
        
        <div style="text-align: center;">
            <a href="{{ route('site.cart.index') }}" class="button">Xem giỏ hàng</a>
        </div> --}}
        
        <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi qua số điện thoại: <strong>+10 367 267 2678</strong></p>
    </div>
    
    <div class="footer">
        <p>© {{ date('Y') }} PolyHair. Tất cả quyền được bảo lưu.</p>
        <p>154, Cầu Giấy, Hà Nội | Email: contact@barbershop.com</p>
    </div>
</body>
</html>

