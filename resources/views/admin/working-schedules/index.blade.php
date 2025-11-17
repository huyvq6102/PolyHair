@extends('admin.layouts.app')

@section('title', 'Quản lý lịch nhân viên')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý lịch nhân viên</h1>
    <div>
        <a href="{{ route('admin.working-schedules.trash') }}" class="btn btn-warning">
            <i class="fas fa-trash-restore"></i> Thùng rác
        </a>
        <a href="{{ route('admin.working-schedules.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm mới
        </a>
    </div>
</div>

<!-- Filter -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Lọc lịch nhân viên</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.working-schedules.index') }}" class="form-inline">
            <div class="form-group mr-3">
                <input type="text" name="employee_name" class="form-control" placeholder="Tìm kiếm theo tên nhân viên..." value="{{ request('employee_name') }}">
            </div>
            <div class="form-group mr-3">
                <input type="date" name="work_date" class="form-control" value="{{ request('work_date') }}">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Lọc
            </button>
            <a href="{{ route('admin.working-schedules.index') }}" class="btn btn-secondary ml-2">
                <i class="fas fa-redo"></i> Làm mới
            </a>
        </form>
    </div>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách lịch nhân viên</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên nhân viên</th>
                        <th>Ngày làm việc</th>
                        <th>Ca làm việc</th>
                        <th>Thời gian làm việc</th>
                        <th>Vị trí</th>
                        <th>Trạng thái</th>
                        <th>Ảnh</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $schedule)
                        <tr>
                            <td>{{ $schedule->id }}</td>
                            <td>{{ $schedule->employee->user->name ?? 'N/A' }}</td>
                            <td>{{ $schedule->work_date ? \Carbon\Carbon::parse($schedule->work_date)->format('d/m/Y') : 'N/A' }}</td>
                            <td>{{ $schedule->shift->name ?? 'N/A' }}</td>
                            <td>
                                @if($schedule->shift)
                                    {{ $schedule->shift->formatted_start_time }} - 
                                    {{ $schedule->shift->formatted_end_time }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>{{ $schedule->employee->position ?? 'N/A' }}</td>
                            <td>
                                @if($schedule->status == 'available')
                                    <span class="badge badge-success">Rảnh</span>
                                @elseif($schedule->status == 'busy')
                                    <span class="badge badge-warning">Bận</span>
                                @else
                                    <span class="badge badge-secondary">Nghỉ</span>
                                @endif
                            </td>
                            <td>
                                @if($schedule->image)
                                    <img src="{{ asset('legacy/images/working-schedules/' . $schedule->image) }}" alt="Schedule image" class="img-thumbnail" style="max-width: 50px; max-height: 50px;">
                                @else
                                    <span class="text-muted">Không có</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.working-schedules.show', $schedule->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Xem
                                </a>
                                <a href="{{ route('admin.working-schedules.edit', $schedule->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <form action="{{ route('admin.working-schedules.destroy', $schedule->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa lịch này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Chưa có lịch nhân viên nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($schedules->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $schedules->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "paging": false,
            "info": false,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Vietnamese.json"
            }
        });
    });
</script>
@endpush

