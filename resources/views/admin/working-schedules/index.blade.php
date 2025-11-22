@extends('admin.layouts.app')

@section('title', 'Quản lý lịch nhân viên')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý lịch nhân viên</h1>
    <a href="{{ route('admin.working-schedules.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Thêm mới
    </a>
</div>

<!-- Filter -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Lọc lịch</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.working-schedules.index') }}" class="form-row">
            <div class="form-group col-md-4">
                <label for="employee_name">Tên nhân viên</label>
                <input type="text" name="employee_name" id="employee_name" class="form-control" placeholder="Nhập tên nhân viên"
                    value="{{ $filters['employee_name'] ?? '' }}">
            </div>
            <div class="form-group col-md-4">
                <label for="work_date">Ngày làm việc</label>
                <input type="date" name="work_date" id="work_date" class="form-control" value="{{ $filters['work_date'] ?? '' }}">
            </div>
            <div class="form-group col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-filter"></i> Lọc</button>
                <a href="{{ route('admin.working-schedules.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i> Làm mới</a>
                <a href="{{ route('admin.working-schedules.trash') }}" class="btn btn-warning ml-2"><i class="fas fa-trash"></i> Thùng rác</a>
            </div>
        </form>
    </div>
</div>

<!-- Data Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách lịch</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nhân viên</th>
                        <th>Ngày làm việc</th>
                        <th>Ca làm việc</th>
                        <th>Thời gian</th>
                        <th>Vị trí</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $schedule)
                        <tr>
                            <td>{{ $schedule->id }}</td>
                            <td>{{ $schedule->employee->user->name ?? 'N/A' }}</td>
                            <td>{{ optional($schedule->work_date)->format('d/m/Y') ?? 'N/A' }}</td>
                            <td>{{ $schedule->shift->name ?? 'N/A' }}</td>
                            <td>{{ $schedule->shift->display_time ?? 'N/A' }}</td>
                            <td>{{ $schedule->employee->position ?? 'N/A' }}</td>
                            <td>
                                @php
                                    $status = $schedule->status;
                                    $badge = $status === 'available' ? 'success' : ($status === 'busy' ? 'warning' : 'secondary');
                                @endphp
                                <span class="badge badge-{{ $badge }}">{{ $statusOptions[$status] ?? ucfirst($status ?? 'N/A') }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.working-schedules.show', $schedule->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.working-schedules.edit', $schedule->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.working-schedules.destroy', $schedule->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa lịch này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Chưa có lịch nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($schedules->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $schedules->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

