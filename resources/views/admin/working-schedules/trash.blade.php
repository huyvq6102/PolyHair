@extends('admin.layouts.app')

@section('title', 'Thùng rác - Lịch nhân viên')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Thùng rác - Lịch nhân viên</h1>
    <a href="{{ route('admin.working-schedules.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách lịch nhân viên đã xóa</h6>
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
                        <th>Ngày xóa</th>
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
                            <td>{{ $schedule->deleted_at ? \Carbon\Carbon::parse($schedule->deleted_at)->format('d/m/Y H:i') : 'N/A' }}</td>
                            <td>
                                <form action="{{ route('admin.working-schedules.restore', $schedule->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn phục hồi lịch này?');">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-undo"></i> Phục hồi
                                    </button>
                                </form>
                                <form action="{{ route('admin.working-schedules.force-delete', $schedule->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn lịch này? Hành động này không thể hoàn tác!');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash-alt"></i> Xóa vĩnh viễn
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Thùng rác trống</td>
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

