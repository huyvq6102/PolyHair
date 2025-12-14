@extends('admin.layouts.app')

@section('title', 'Quản lý nhân viên')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý nhân viên</h1>
    @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm mới
        </a>
    @endif
</div>

<!-- Filter -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Lọc nhân viên</h6>
        @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.employees.trash') }}" class="btn btn-warning btn-sm">
                <i class="fas fa-trash"></i> Thùng rác
            </a>
        @endif
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.employees.index') }}" class="form-inline">
            <div class="form-group mr-3">
                <input type="text" name="keyword" class="form-control" placeholder="Tìm kiếm theo tên..." value="{{ request('keyword') }}">
            </div>
            <div class="form-group mr-3">
                <select name="position" class="form-control">
                    <option value="">Tất cả vị trí</option>
                    <option value="Stylist" {{ request('position') == 'Stylist' ? 'selected' : '' }}>Stylist</option>
                    <option value="Barber" {{ request('position') == 'Barber' ? 'selected' : '' }}>Barber</option>
                    <option value="Shampooer" {{ request('position') == 'Shampooer' ? 'selected' : '' }}>Shampooer</option>
                    <option value="Receptionist" {{ request('position') == 'Receptionist' ? 'selected' : '' }}>Receptionist</option>
                </select>
            </div>
            <div class="form-group mr-3">
                <select name="status" class="form-control">
                    <option value="">Tất cả trạng thái</option>
                    <option value="Đang làm việc" {{ request('status') == 'Đang làm việc' ? 'selected' : '' }}>Đang làm việc</option>
                    <option value="Nghỉ phép" {{ request('status') == 'Nghỉ phép' ? 'selected' : '' }}>Nghỉ phép</option>
                    <option value="Vô hiệu hóa" {{ request('status') == 'Vô hiệu hóa' ? 'selected' : '' }}>Vô hiệu hóa</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Lọc
            </button>
            <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary ml-2">
                <i class="fas fa-redo"></i> Làm mới
            </a>
        </form>
    </div>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách nhân viên</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Mã NV</th>
                        <th>Tên nhân viên</th>
                        <th>Email</th>
                        <th>Vị trí</th>
                        <th>Cấp độ</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr>
                            <td>{{ $employee->id }}</td>
                            <td>{{ $employee->user->name ?? 'N/A' }}</td>
                            <td>{{ $employee->user->email ?? 'N/A' }}</td>
                            <td>{{ $employee->position ?? 'N/A' }}</td>
                            <td>{{ $employee->level ?? 'N/A' }}</td>
                            <td>
                                <span class="badge badge-{{ $employee->status == 'Đang làm việc' ? 'success' : ($employee->status == 'Nghỉ phép' ? 'warning' : 'secondary') }}">
                                    {{ $employee->status }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('admin.employees.show', $employee->id) }}" class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(auth()->user()->isAdmin())
                                        <a href="{{ route('admin.employees.edit', $employee->id) }}" class="btn btn-sm btn-primary" title="Sửa thông tin">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.employees.destroy', $employee->id) }}" method="POST" class="d-inline" onsubmit="return confirmDelete('{{ $employee->user->name ?? 'Nhân viên' }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Xóa nhân viên">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Chưa có nhân viên nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .gap-1 {
        gap: 0.25rem;
    }
    .gap-1 > * {
        margin-right: 0.25rem;
        margin-bottom: 0.25rem;
    }
    .gap-1 form {
        display: inline-block;
        margin-right: 0.25rem;
        margin-bottom: 0.25rem;
    }
    .gap-1 button, .gap-1 a {
        white-space: nowrap;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Vietnamese.json"
            }
        });
    });

    function confirmDelete(employeeName) {
        const today = new Date();
        const dateStr = today.toLocaleDateString('vi-VN', { 
            day: '2-digit', 
            month: '2-digit', 
            year: 'numeric' 
        });
        return confirm('Bạn có chắc chắn muốn xóa tài khoản nhân viên "' + employeeName + '" vào ngày ' + dateStr + ' hay không?');
    }
</script>
@endpush

