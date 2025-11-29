@extends('admin.layouts.app')

@section('title', 'Quản lý chuyên môn nhân viên')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý chuyên môn nhân viên</h1>
    <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">
        <i class="fas fa-users"></i> Danh sách nhân viên
    </a>
</div>

<!-- Filter -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Lọc nhân viên</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.employee-skills.index') }}" class="form-row">
            <div class="form-group col-md-4">
                <label for="keyword">Tên nhân viên</label>
                <input type="text" name="keyword" id="keyword" class="form-control" placeholder="Nhập tên nhân viên"
                    value="{{ $filters['keyword'] ?? '' }}">
            </div>
            <div class="form-group col-md-4">
                <label for="service_id">Dịch vụ (Chuyên môn)</label>
                <select name="service_id" id="service_id" class="form-control">
                    <option value="">-- Tất cả dịch vụ --</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" {{ ($filters['service_id'] ?? '') == $service->id ? 'selected' : '' }}>
                            {{ $service->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-filter"></i> Lọc</button>
                <a href="{{ route('admin.employee-skills.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i> Làm mới</a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách chuyên môn</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nhân viên</th>
                        <th>Vị trí</th>
                        <th>Chuyên môn</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr>
                            <td>{{ $employee->id }}</td>
                            <td>{{ $employee->user->name ?? 'N/A' }}</td>
                            <td>{{ $employee->position ?? 'N/A' }}</td>
                            <td>
                                @if($employee->services->isEmpty())
                                    <span class="text-muted">Chưa thiết lập</span>
                                @else
                                    @foreach($employee->services as $service)
                                        <span class="badge badge-info">{{ $service->name }}</span>
                                    @endforeach
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.employee-skills.edit', $employee->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Chỉnh sửa
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Không có dữ liệu</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($employees->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $employees->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

