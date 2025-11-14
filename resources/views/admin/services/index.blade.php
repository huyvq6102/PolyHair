@extends('admin.layouts.app')

@section('title', 'Quản lý dịch vụ')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý dịch vụ</h1>
    <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Thêm mới
    </a>
</div>

<!-- Search -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Tìm kiếm</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.services.index') }}" class="form-inline">
            <div class="form-group mr-3">
                <input type="text" name="keyword" class="form-control" placeholder="Tìm kiếm theo tên..." value="{{ request('keyword') }}">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Tìm kiếm
            </button>
            @if(request('keyword'))
                <a href="{{ route('admin.services.index') }}" class="btn btn-secondary ml-2">
                    <i class="fas fa-times"></i> Xóa bộ lọc
                </a>
            @endif
        </form>
    </div>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách dịch vụ</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Mã dịch vụ</th>
                        <th>Tên dịch vụ</th>
                        <th>Danh mục</th>
                        <th>Hình ảnh</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                        <tr>
                            <td>{{ $service->id }}</td>
                            <td>{{ $service->name }}</td>
                            <td>{{ $service->category->name ?? 'N/A' }}</td>
                            <td>
                                @if($service->image)
                                    <img src="{{ asset('legacy/images/products/' . $service->image) }}" alt="{{ $service->name }}" width="50" height="50">
                                @else
                                    <span class="text-muted">Không có ảnh</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $service->status == 'Hoạt động' ? 'success' : 'secondary' }}">
                                    {{ $service->status }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.services.edit', $service->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <form action="{{ route('admin.services.destroy', $service->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?');">
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
                            <td colspan="6" class="text-center">Chưa có dịch vụ nào</td>
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
            }
        });
    });
</script>
@endpush

