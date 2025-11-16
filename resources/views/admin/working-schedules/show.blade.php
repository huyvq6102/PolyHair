@extends('admin.layouts.app')

@section('title', 'Chi tiết lịch nhân viên')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Chi tiết lịch nhân viên</h1>
    <div>
        <a href="{{ route('admin.working-schedules.edit', $schedule->id) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Sửa
        </a>
        <a href="{{ route('admin.working-schedules.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Thông tin lịch nhân viên</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">Tên nhân viên:</th>
                        <td>{{ $schedule->employee->user->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Vị trí:</th>
                        <td>{{ $schedule->employee->position ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Ca làm việc:</th>
                        <td>{{ $schedule->shift->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Ngày làm việc:</th>
                        <td>{{ $schedule->work_date ? \Carbon\Carbon::parse($schedule->work_date)->format('d/m/Y') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Trạng thái:</th>
                        <td>
                            @if($schedule->status == 'available')
                                <span class="badge badge-success">Rảnh</span>
                            @elseif($schedule->status == 'busy')
                                <span class="badge badge-warning">Bận</span>
                            @else
                                <span class="badge badge-secondary">Nghỉ</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h5 class="mb-3">Giờ làm việc</h5>
                @if($schedule->shift)
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Ca:</th>
                            <td>{{ $schedule->shift->name }}</td>
                        </tr>
                        <tr>
                            <th>Giờ bắt đầu:</th>
                            <td>{{ $schedule->shift->formatted_start_time }}</td>
                        </tr>
                        <tr>
                            <th>Giờ kết thúc:</th>
                            <td>{{ $schedule->shift->formatted_end_time }}</td>
                        </tr>
                        <tr>
                            <th>Thời lượng:</th>
                            <td>{{ $schedule->shift->duration ?? 'N/A' }} phút</td>
                        </tr>
                    </table>
                @else
                    <p class="text-muted">Chưa có thông tin ca làm việc</p>
                @endif
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <h5 class="mb-3">Dịch vụ</h5>
                @php
                    $appointments = \App\Models\Appointment::where('employee_id', $schedule->employee_id)
                        ->whereDate('start_at', $schedule->work_date)
                        ->with(['appointmentDetails.serviceVariant.service', 'user'])
                        ->get();
                @endphp
                
                @if($appointments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Thời gian</th>
                                    <th>Khách hàng</th>
                                    <th>Dịch vụ</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($appointments as $appointment)
                                    <tr>
                                        <td>
                                            {{ \Carbon\Carbon::parse($appointment->start_at)->format('H:i') }} - 
                                            {{ \Carbon\Carbon::parse($appointment->end_at)->format('H:i') }}
                                        </td>
                                        <td>{{ $appointment->user->name ?? 'N/A' }}</td>
                                        <td>
                                            @foreach($appointment->appointmentDetails as $detail)
                                                {{ $detail->serviceVariant->service->name ?? 'N/A' }}<br>
                                            @endforeach
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $appointment->status == 'confirmed' ? 'success' : ($appointment->status == 'pending' ? 'warning' : 'secondary') }}">
                                                {{ $appointment->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">Chưa có lịch hẹn nào trong ngày này</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

