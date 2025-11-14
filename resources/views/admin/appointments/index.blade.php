@extends('admin.layouts.app')

@section('title', 'Quản lý lịch hẹn')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý lịch hẹn</h1>
</div>

<!-- Filter -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Lọc lịch hẹn</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.appointments.index') }}" class="form-inline">
            <div class="form-group mr-3">
                <label for="status" class="mr-2">Trạng thái:</label>
                <select name="status" id="status" class="form-control">
                    <option value="">Tất cả</option>
                    <option value="Chờ xử lý" {{ request('status') == 'Chờ xử lý' ? 'selected' : '' }}>Chờ xử lý</option>
                    <option value="Đã xác nhận" {{ request('status') == 'Đã xác nhận' ? 'selected' : '' }}>Đã xác nhận</option>
                    <option value="Đang thực hiện" {{ request('status') == 'Đang thực hiện' ? 'selected' : '' }}>Đang thực hiện</option>
                    <option value="Hoàn thành" {{ request('status') == 'Hoàn thành' ? 'selected' : '' }}>Hoàn thành</option>
                    <option value="Đã hủy" {{ request('status') == 'Đã hủy' ? 'selected' : '' }}>Đã hủy</option>
                    <option value="Chưa thanh toán" {{ request('status') == 'Chưa thanh toán' ? 'selected' : '' }}>Chưa thanh toán</option>
                    <option value="Đã thanh toán" {{ request('status') == 'Đã thanh toán' ? 'selected' : '' }}>Đã thanh toán</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Lọc
            </button>
            <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary ml-2">
                <i class="fas fa-redo"></i> Làm mới
            </a>
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
                        <th>Mã lịch hẹn</th>
                        <th>Khách hàng</th>
                        <th>Nhân viên</th>
                        <th>Trạng thái</th>
                        <th>Thời gian bắt đầu</th>
                        <th>Thời gian kết thúc</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appointment)
                        <tr>
                            <td>{{ $appointment->id }}</td>
                            <td>{{ $appointment->user->name ?? 'N/A' }}</td>
                            <td>{{ $appointment->employee->user->name ?? 'Chưa phân công' }}</td>
                            <td>
                                <span class="badge badge-{{ $appointment->status == 'Hoàn thành' ? 'success' : ($appointment->status == 'Đã hủy' ? 'danger' : ($appointment->status == 'Đã xác nhận' ? 'info' : 'warning')) }}">
                                    {{ $appointment->status }}
                                </span>
                            </td>
                            <td>{{ $appointment->start_at ? $appointment->start_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            <td>{{ $appointment->end_at ? $appointment->end_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            <td>
                                <a href="{{ route('admin.appointments.show', $appointment->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Xem
                                </a>
                                <form action="{{ route('admin.appointments.destroy', $appointment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?');">
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
                            <td colspan="7" class="text-center">Chưa có lịch hẹn nào</td>
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

