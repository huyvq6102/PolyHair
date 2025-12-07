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
        <form action="{{ route('admin.appointments.update', $appointment->id) }}" method="POST">
            @csrf
            @method('PUT')
            
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
            </div>

            <div class="form-group">
                <label for="status">Trạng thái <span class="text-danger">*</span></label>
                <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                    <option value="Chờ xử lý" {{ $appointment->status == 'Chờ xử lý' ? 'selected' : '' }}>Chờ xử lý</option>
                    <option value="Đã xác nhận" {{ $appointment->status == 'Đã xác nhận' ? 'selected' : '' }}>Đã xác nhận</option>
                    <option value="Đang thực hiện" {{ $appointment->status == 'Đang thực hiện' ? 'selected' : '' }}>Đang thực hiện</option>
                    <option value="Hoàn thành" {{ $appointment->status == 'Hoàn thành' ? 'selected' : '' }}>Hoàn thành</option>
                    <option value="Đã hủy" {{ $appointment->status == 'Đã hủy' ? 'selected' : '' }}>Đã hủy</option>
                    <option value="Chưa thanh toán" {{ $appointment->status == 'Chưa thanh toán' ? 'selected' : '' }}>Chưa thanh toán</option>
                    <option value="Đã thanh toán" {{ $appointment->status == 'Đã thanh toán' ? 'selected' : '' }}>Đã thanh toán</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
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
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Cập nhật trạng thái
                </button>
                <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
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

