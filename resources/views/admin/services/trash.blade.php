@extends('admin.layouts.app')

@section('title', 'Thùng rác dịch vụ')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Thùng rác dịch vụ</h1>
    <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại danh sách
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<!-- Search -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Tìm kiếm</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.services.trash') }}" class="form-inline">
            <div class="form-group mr-3">
                <input type="text" name="keyword" class="form-control" placeholder="Tìm kiếm theo tên..." value="{{ request('keyword') }}">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Tìm kiếm
            </button>
            @if(request('keyword'))
                <a href="{{ route('admin.services.trash') }}" class="btn btn-secondary ml-2">
                    <i class="fas fa-times"></i> Xóa bộ lọc
                </a>
            @endif
        </form>
    </div>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách dịch vụ đã xóa</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Loại</th>
                        <th>Mã/Tên</th>
                        <th>Giá</th>
                        <th>Hình ảnh</th>
                        <th>Nhóm dịch vụ</th>
                        <th>Trạng thái</th>
                        <th>Ngày xóa</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                        <tr>
                            <td><span class="badge badge-primary">Dịch vụ</span></td>
                            <td>
                                <strong>{{ $service->name }}</strong>
                                <br><small class="text-muted">{{ $service->service_code ?? 'N/A' }}</small>
                            </td>
                            <td>
                                @if($service->base_price)
                                    {{ number_format($service->base_price, 0, ',', '.') }} đ
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($service->image)
                                    <img src="{{ asset('legacy/images/products/' . $service->image) }}" alt="{{ $service->name }}" width="60" height="60" class="img-thumbnail">
                                @else
                                    <span class="text-muted">Không có ảnh</span>
                                @endif
                            </td>
                            <td>{{ $service->category->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge badge-{{ $service->status == 'Hoạt động' ? 'success' : 'secondary' }}">
                                    {{ $service->status }}
                                </span>
                            </td>
                            <td>{{ $service->deleted_at ? $service->deleted_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            <td>
                                <form action="{{ route('admin.services.restore', $service->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-undo"></i> Khôi phục
                                    </button>
                                </form>
                                <form action="{{ route('admin.services.force-delete', $service->id) }}" method="POST" class="d-inline force-delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Xóa vĩnh viễn
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        @if($combos->count() == 0 && $variants->count() == 0)
                            <tr>
                                <td colspan="8" class="text-center">Thùng rác trống</td>
                            </tr>
                        @endif
                    @endforelse
                    
                    @foreach($combos as $combo)
                        <tr>
                            <td><span class="badge badge-info">Combo</span></td>
                            <td>
                                <strong>{{ $combo->name }}</strong>
                                <br><small class="text-muted">COMBO-{{ $combo->id }}</small>
                            </td>
                            <td>{{ number_format($combo->price, 0, ',', '.') }} đ</td>
                            <td>
                                @if($combo->image)
                                    <img src="{{ asset('legacy/images/products/' . $combo->image) }}" alt="{{ $combo->name }}" width="60" height="60" class="img-thumbnail">
                                @else
                                    <span class="text-muted">Không có ảnh</span>
                                @endif
                            </td>
                            <td>{{ $combo->category->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge badge-{{ $combo->status == 'Hoạt động' ? 'success' : 'secondary' }}">
                                    {{ $combo->status }}
                                </span>
                            </td>
                            <td>{{ $combo->deleted_at ? $combo->deleted_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            <td>
                                <form action="{{ route('admin.services.restore', $combo->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="type" value="combo">
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-undo"></i> Khôi phục
                                    </button>
                                </form>
                                <form action="{{ route('admin.services.force-delete', $combo->id) }}" method="POST" class="d-inline force-delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="type" value="combo">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Xóa vĩnh viễn
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    
                    @foreach($variants as $variant)
                        <tr>
                            <td><span class="badge badge-secondary">Biến thể</span></td>
                            <td>
                                <strong>{{ $variant->name }}</strong>
                                <br><small class="text-muted">Thuộc: {{ $variant->service->name ?? 'N/A' }}</small>
                            </td>
                            <td>{{ number_format($variant->price, 0, ',', '.') }} đ</td>
                            <td>-</td>
                            <td>-</td>
                            <td>
                                <span class="badge badge-{{ $variant->is_active ? 'success' : 'secondary' }}">
                                    {{ $variant->is_active ? 'Hoạt động' : 'Vô hiệu hóa' }}
                                </span>
                            </td>
                            <td>{{ $variant->deleted_at ? $variant->deleted_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            <td>
                                <form action="{{ route('admin.services.restore', $variant->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="type" value="variant">
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-undo"></i> Khôi phục
                                    </button>
                                </form>
                                <form action="{{ route('admin.services.force-delete', $variant->id) }}" method="POST" class="d-inline force-delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="type" value="variant">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Xóa vĩnh viễn
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($services->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $services->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const forceDeleteForms = document.querySelectorAll('.force-delete-form');
        forceDeleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (confirm('Bạn có chắc xóa dịch vụ này vĩnh viễn không?')) {
                    this.submit();
                }
            });
        });
    });
</script>
@endpush
