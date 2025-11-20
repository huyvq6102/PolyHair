@extends('admin.layouts.app')

@section('title', 'Chi tiết lịch nhân viên')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Chi tiết lịch nhân viên</h1>
    <a href="{{ route('admin.working-schedules.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Thông tin lịch</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th>Nhân viên:</th>
                        <td>{{ $schedule->employee->user->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Vị trí:</th>
                        <td>{{ $schedule->employee->position ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Ngày làm việc:</th>
                        <td>{{ optional($schedule->work_date)->format('d/m/Y') ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Trạng thái:</th>
                        @php
                            $status = $schedule->status;
                            $badge = $status === 'available' ? 'success' : ($status === 'busy' ? 'warning' : 'secondary');
                        @endphp
                        <td><span class="badge badge-{{ $badge }}">{{ $statusOptions[$status] ?? ucfirst($status ?? 'N/A') }}</span></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h5 class="mb-3">Giờ làm việc</h5>
                @if($schedule->shift)
                    <table class="table table-bordered">
                        <tr>
                            <th>Ca:</th>
                            <td>{{ $schedule->shift->name }}</td>
                        </tr>
                        <tr>
                            <th>Bắt đầu:</th>
                            <td>{{ $schedule->shift->formatted_start_time ?? '--:--' }}</td>
                        </tr>
                        <tr>
                            <th>Kết thúc:</th>
                            <td>{{ $schedule->shift->formatted_end_time ?? '--:--' }}</td>
                        </tr>
                        <tr>
                            <th>Khung giờ:</th>
                            <td>{{ $schedule->shift->display_time ?? 'Chưa xác định' }}</td>
                        </tr>
                    </table>
                @else
                    <p class="text-muted">Chưa xác định ca làm việc</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Lịch hẹn trong ngày</h6>
    </div>
    <div class="card-body">
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
                    @forelse($appointments as $appointment)
                        <tr>
                            <td>
                                {{ optional($appointment->start_at)->format('H:i') }} -
                                {{ optional($appointment->end_at)->format('H:i') }}
                            </td>
                            <td>{{ $appointment->user->name ?? 'N/A' }}</td>
                            <td>
                                @foreach($appointment->appointmentDetails as $detail)
                                    {{ $detail->serviceVariant->service->name ?? 'N/A' }}<br>
                                @endforeach
                            </td>
                            <td>{{ $appointment->status }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Không có lịch hẹn nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

