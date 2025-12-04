@extends('admin.layouts.app')

@section('title', 'Thùng rác lịch nhân viên')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Thùng rác lịch nhân viên</h1>
    <div>
        <form action="{{ route('admin.working-schedules.trash.delete-all') }}" method="POST" class="d-inline" onsubmit="return confirmDeleteAllTrash();">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger mr-2">
                <i class="fas fa-trash-alt"></i> Xóa tất cả vĩnh viễn
            </button>
        </form>
    <a href="{{ route('admin.working-schedules.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại danh sách
    </a>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Các lịch đã xóa</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nhân viên</th>
                        <th>Ngày làm việc</th>
                        <th>Ca làm việc</th>
                        <th>Ngày xóa</th>
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
                            <td>{{ optional($schedule->deleted_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                <form action="{{ route('admin.working-schedules.restore', $schedule->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Phục hồi lịch này?');">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-undo"></i> Phục hồi
                                    </button>
                                </form>
                                <form action="{{ route('admin.working-schedules.force-delete', $schedule->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa vĩnh viễn lịch này? Hành động này không thể hoàn tác.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Xóa vĩnh viễn
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Thùng rác trống</td>
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

@push('scripts')
<script>
function confirmDeleteAllTrash() {
    return confirm('⚠️ CẢNH BÁO NGHIÊM TRỌNG: Bạn có chắc chắn muốn xóa VĨNH VIỄN TẤT CẢ lịch trong thùng rác?\n\nHành động này sẽ XÓA VĨNH VIỄN và KHÔNG THỂ hoàn tác!\n\nTất cả dữ liệu sẽ bị mất mãi mãi!\n\nNhấn OK để xác nhận xóa vĩnh viễn tất cả.');
}
</script>
@endpush
@endsection

