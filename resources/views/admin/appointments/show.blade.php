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
                    <p class="form-control-plaintext">{{ $appointment->user->name ?? 'N/A' }}</p>
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
                        <span class="badge badge-{{ $appointment->status == 'Hoàn thành' ? 'success' : ($appointment->status == 'Đã hủy' ? 'danger' : ($appointment->status == 'Đã xác nhận' ? 'info' : ($appointment->status == 'Đã thanh toán' ? 'success' : 'warning'))) }} badge-lg" style="font-size: 14px; padding: 8px 12px;">
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
            <a href="{{ route('admin.appointments.edit', $appointment->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Chỉnh sửa lịch hẹn
            </a>
            @if($appointment->status !== 'Đã thanh toán' && $appointment->status !== 'Đã hủy')
            <a href="{{ route('checkout', ['appointment_id' => $appointment->id]) }}" class="btn btn-success">
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
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Dịch vụ</th>
                        <th>Biến thể</th>
                        <th>Giá</th>
                        <th>Thời lượng (phút)</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointment->appointmentDetails as $detail)
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
                            <td>{{ number_format($detail->price_snapshot ?? 0, 0, ',', '.') }} đ</td>
                            <td>{{ $detail->duration ?? 0 }}</td>
                            <td>
                                <span class="badge badge-info">{{ $detail->status ?? 'N/A' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Chưa có dịch vụ nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

