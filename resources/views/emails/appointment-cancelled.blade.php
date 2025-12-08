<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo hủy lịch hẹn</title>
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
            background-color: #dc3545;
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
            border-left: 4px solid #dc3545;
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
            color: #dc3545;
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
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #4A3600;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Thông báo hủy lịch hẹn</h1>
    </div>
    
    <div class="content">
        <p>Xin chào <strong>{{ $appointment->user->name }}</strong>,</p>
        
        <p>Chúng tôi rất tiếc phải thông báo rằng lịch hẹn của bạn đã bị hủy.</p>
        
        <div class="warning-box">
            <strong>⚠️ Lưu ý:</strong> Lịch hẹn của bạn đã được hủy bởi quản trị viên. Nếu bạn có bất kỳ thắc mắc nào, vui lòng liên hệ với chúng tôi.
        </div>
        
        <div class="info-box">
            <h3 style="margin-top: 0; color: #dc3545;">Thông tin lịch hẹn đã hủy</h3>
            
            <div class="info-row">
                <span class="label">Mã đặt lịch:</span>
                <span class="value">{{ $appointment->booking_code ?? '#' . str_pad($appointment->id, 6, '0', STR_PAD_LEFT) }}</span>
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
            
            @if($appointment->start_at)
            <div class="info-row">
                <span class="label">Ngày đặt lịch:</span>
                <span class="value">{{ \Carbon\Carbon::parse($appointment->start_at)->format('d/m/Y') }}</span>
            </div>
            
            <div class="info-row">
                <span class="label">Giờ bắt đầu:</span>
                <span class="value">{{ \Carbon\Carbon::parse($appointment->start_at)->format('H:i') }}</span>
            </div>
            
            @if($appointment->end_at)
            <div class="info-row">
                <span class="label">Giờ kết thúc:</span>
                <span class="value">{{ \Carbon\Carbon::parse($appointment->end_at)->format('H:i') }}</span>
            </div>
            @endif
            @endif
            
            <div class="info-row">
                <span class="label">Trạng thái:</span>
                <span class="value" style="color: #dc3545; font-weight: bold;">{{ $appointment->status }}</span>
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
            
            @if($cancellationReason)
            <div class="info-row">
                <span class="label">Lý do hủy:</span>
                <span class="value">{{ $cancellationReason }}</span>
            </div>
            @endif
            
            @if($appointment->note)
            <div class="info-row">
                <span class="label">Ghi chú:</span>
                <span class="value">{{ $appointment->note }}</span>
            </div>
            @endif
        </div>
        
        <p>Nếu bạn muốn đặt lịch mới, vui lòng truy cập website của chúng tôi để đặt lịch lại.</p>
        
        <div style="text-align: center;">
            <a href="{{ route('site.home') }}" class="button">Đặt lịch mới</a>
        </div>
        
        <p>Nếu bạn có bất kỳ câu hỏi nào về việc hủy lịch này, vui lòng liên hệ với chúng tôi qua số điện thoại: <strong>+10 367 267 2678</strong></p>
        
        <p>Chúng tôi xin lỗi vì sự bất tiện này và mong được phục vụ bạn trong tương lai.</p>
    </div>
    
    <div class="footer">
        <p>© {{ date('Y') }} PolyHair. Tất cả quyền được bảo lưu.</p>
        <p>154, Cầu Giấy, Hà Nội | Email: contact@barbershop.com</p>
    </div>
</body>
</html>

