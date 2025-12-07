@extends('admin.layouts.app')

@section('title', 'Chi tiết đơn đặt')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Chi tiết đơn đặt {{ $appointment->booking_code ?? '#' . $appointment->id }}</h1>
    <a href="{{ route('employee.appointments.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<!-- Appointment Info -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Thông tin đơn đặt</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold">Mã đơn đặt:</label>
                    <p class="form-control-plaintext">{{ $appointment->booking_code ?? '#' . $appointment->id }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold">Trạng thái:</label>
                    <p>
                        <span class="badge badge-{{ 
                            $appointment->status == 'Hoàn thành' ? 'success' : 
                            ($appointment->status == 'Đã hủy' ? 'danger' : 
                            ($appointment->status == 'Đã xác nhận' || $appointment->status == 'Đang thực hiện' ? 'info' : 'warning')) 
                        }} badge-lg">
                            {{ $appointment->status }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold">Tên khách hàng:</label>
                    <p class="form-control-plaintext">{{ $appointment->user->name ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold">Số điện thoại:</label>
                    <p class="form-control-plaintext">{{ $appointment->user->phone ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold">Ngày và giờ:</label>
                    <p class="form-control-plaintext">
                        @if($appointment->start_at)
                            {{ $appointment->start_at->format('d/m/Y H:i') }}
                            @if($appointment->end_at)
                                - {{ $appointment->end_at->format('H:i') }}
                            @endif
                        @else
                            <span class="text-muted">Chưa có</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold">Nhân viên phụ trách:</label>
                    <p class="form-control-plaintext">{{ $appointment->employee->user->name ?? 'Chưa phân công' }}</p>
                </div>
            </div>
        </div>

        @if($appointment->promotionUsages && $appointment->promotionUsages->count() > 0)
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label class="font-weight-bold">Khuyến mãi áp dụng:</label>
                    <ul class="list-unstyled mb-0">
                        @foreach($appointment->promotionUsages as $usage)
                            @if($usage->promotion)
                                <li>
                                    <span class="badge badge-info">
                                        {{ $usage->promotion->code }}
                                    </span>
                                    - {{ $usage->promotion->name }}
                                    @if($usage->promotion->discount_percent)
                                        ({{ $usage->promotion->discount_percent }}%)
                                    @elseif($usage->promotion->discount_amount)
                                        ({{ number_format($usage->promotion->discount_amount, 0, ',', '.') }}đ)
                                    @endif
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        @if($appointment->note)
        <div class="form-group">
            <label class="font-weight-bold">Ghi chú:</label>
            <p class="form-control-plaintext">{{ $appointment->note }}</p>
        </div>
        @endif

        @if($appointment->cancellation_reason)
        <div class="form-group">
            <label class="font-weight-bold text-danger">Lý do hủy:</label>
            <p class="form-control-plaintext text-danger">{{ $appointment->cancellation_reason }}</p>
        </div>
        @endif
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
                            <td>{{ $detail->serviceVariant->service->name ?? 'N/A' }}</td>
                            <td>{{ $detail->serviceVariant->name ?? 'N/A' }}</td>
                            <td>{{ number_format($detail->price_snapshot ?? 0, 0, ',', '.') }} đ</td>
                            <td>{{ $detail->duration ?? 'N/A' }}</td>
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

<!-- Action Buttons -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Thao tác</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                @if($appointment->status == 'Chờ xử lý' || $appointment->status == 'Chờ xác nhận')
                    <form action="{{ route('employee.appointments.confirm', $appointment->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-check"></i> Xác nhận đơn
                        </button>
                    </form>
                @endif

                @if($appointment->status == 'Đã xác nhận')
                    <form action="{{ route('employee.appointments.start', $appointment->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-info btn-lg">
                            <i class="fas fa-play"></i> Bắt đầu thực hiện
                        </button>
                    </form>
                @endif

                @if($appointment->status == 'Đang thực hiện')
                    <form action="{{ route('employee.appointments.complete', $appointment->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-check-circle"></i> Hoàn thành
                        </button>
                    </form>
                @endif

                @if($appointment->status == 'Chờ xử lý' || $appointment->status == 'Chờ xác nhận')
                    <button type="button" class="btn btn-danger btn-lg" data-toggle="modal" data-target="#cancelModal">
                        <i class="fas fa-times"></i> Hủy đơn
                    </button>
                    <form action="{{ route('employee.appointments.destroy', $appointment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đơn đặt này? Hành động này không thể hoàn tác.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-lg">
                            <i class="fas fa-trash"></i> Xóa đơn
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('employee.appointments.cancel', $appointment->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel">Hủy đơn đặt</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="cancellation_reason">Lý do hủy <span class="text-danger">*</span></label>
                        <textarea name="cancellation_reason" id="cancellation_reason" class="form-control @error('cancellation_reason') is-invalid @enderror" 
                                  rows="4" required placeholder="Nhập lý do hủy đơn...">{{ old('cancellation_reason') }}</textarea>
                        @error('cancellation_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger">Xác nhận hủy</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .badge-lg {
        font-size: 1rem;
        padding: 0.5rem 1rem;
    }
</style>
@endpush

