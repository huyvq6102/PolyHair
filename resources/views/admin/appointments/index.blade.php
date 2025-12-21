@extends('admin.layouts.app')

@section('title', 'Quản lý lịch hẹn')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý lịch hẹn</h1>
    <div>
        <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm mới lịch hẹn
        </a>
        <a href="{{ route('admin.appointments.cancelled') }}" class="btn btn-secondary">
            <i class="fas fa-ban"></i> Xem lịch đã hủy
        </a>
    </div>
</div>

<!-- Filter -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Tìm kiếm và lọc lịch hẹn</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.appointments.index') }}">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="customer_name">Tên khách hàng</label>
                        <input type="text" name="customer_name" id="customer_name" class="form-control" 
                               value="{{ $filters['customer_name'] ?? '' }}" placeholder="Nhập tên khách hàng">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="phone">Số điện thoại</label>
                        <input type="text" name="phone" id="phone" class="form-control" 
                               value="{{ $filters['phone'] ?? '' }}" placeholder="Nhập số điện thoại">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" class="form-control" 
                               value="{{ $filters['email'] ?? '' }}" placeholder="Nhập email">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="employee_name">Tên nhân viên</label>
                        <input type="text" name="employee_name" id="employee_name" class="form-control" 
                               value="{{ $filters['employee_name'] ?? '' }}" placeholder="Nhập tên nhân viên">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="service">Dịch vụ</label>
                        <input type="text" name="service" id="service" class="form-control" 
                               value="{{ $filters['service'] ?? '' }}" placeholder="Nhập tên dịch vụ">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="appointment_date">Ngày đặt</label>
                        <input type="date" name="appointment_date" id="appointment_date" class="form-control" 
                               value="{{ $filters['appointment_date'] ?? '' }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="booking_code">Mã đơn</label>
                        <input type="text" name="booking_code" id="booking_code" class="form-control" 
                               value="{{ $filters['booking_code'] ?? '' }}" placeholder="Nhập mã đơn">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group" style="margin-top: 32px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                        <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Làm mới
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách lịch hẹn</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Tên</th>
                        <th>SĐT</th>
                        <th>Email</th>
                        <th>Tên nhân viên</th>
                        <th>Dịch vụ</th>
                        <th>Ngày đặt</th>
                        <th>Trạng thái</th>
                        <th>Mô tả</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appointment)
                        <tr>
                            <td>{{ $appointment->booking_code ?? 'N/A' }}</td>
                            <td>{{ $appointment->user->name ?? $appointment->guest_name ?? 'N/A' }}</td>
                            <td>{{ $appointment->user->phone ?? $appointment->guest_phone ?? 'N/A' }}</td>
                            <td>{{ $appointment->user->email ?? $appointment->guest_email ?? 'N/A' }}</td>
                            <td>{{ $appointment->employee->user->name ?? 'Chưa phân công' }}</td>
                            <td>
                                @if($appointment->appointmentDetails->count() > 0)
                                    @foreach($appointment->appointmentDetails as $detail)
                                        @if($detail->combo_id)
                                            {{ $detail->combo->name ?? ($detail->notes ?? 'Combo') }}
                                        @elseif($detail->serviceVariant)
                                            {{ $detail->serviceVariant->name ?? ($detail->serviceVariant->service->name ?? 'N/A') }}
                                        @else
                                            {{ $detail->notes ?? 'Dịch vụ đơn' }}
                                        @endif
                                        @if(!$loop->last), @endif
                                    @endforeach
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>{{ $appointment->start_at ? $appointment->start_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            <td>
                                <span class="badge badge-{{ $appointment->status == 'Hoàn thành' ? 'success' : ($appointment->status == 'Đã hủy' ? 'danger' : ($appointment->status == 'Đã xác nhận' ? 'info' : 'warning')) }}">
                                    {{ $appointment->status }}
                                </span>
                                @if($appointment->status == 'Đã hủy' && $appointment->cancellation_reason)
                                    <br><small class="text-muted"><i class="fas fa-info-circle"></i> {{ Str::limit($appointment->cancellation_reason, 30) }}</small>
                                @endif
                            </td>
                            <td>{{ Str::limit($appointment->note ?? 'N/A', 50) }}</td>
                            <td>
                                <a href="{{ route('admin.appointments.show', $appointment->id) }}" class="btn btn-sm btn-info" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($appointment->status == 'Hoàn thành' || $appointment->status == 'Chưa thanh toán')
                                    <a href="{{ route('admin.appointments.checkout', ['appointment_id' => $appointment->id]) }}" class="btn btn-sm btn-success" title="Thanh toán">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </a>
                                @endif
                                @if($appointment->status != 'Đã hủy' && $appointment->status != 'Hoàn thành' && $appointment->status != 'Đã thanh toán')
                                    <a href="{{ route('admin.appointments.edit', $appointment->id) }}" class="btn btn-sm btn-warning" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.appointments.cancel', $appointment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn hủy lịch không?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hủy">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </form>
                                @elseif($appointment->status == 'Đã hủy')
                                    <span class="btn btn-sm btn-secondary" title="Lịch đã hủy" disabled>
                                        <i class="fas fa-ban"></i>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">Chưa có lịch hẹn nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Vietnamese.json"
            },
            "order": [[0, "desc"]]
        });
    });
</script>
@endpush

