@extends('admin.layouts.app')

@section('title', 'Quản lý dịch vụ')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý dịch vụ</h1>
    <div>
        <a href="{{ route('admin.services.trash') }}" class="btn btn-warning">
            <i class="fas fa-trash-restore"></i> Thùng rác
        </a>
        <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm mới
        </a>
    </div>
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
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Mã dịch vụ</th>
                        <th>Tên dịch vụ</th>
                        <th>Giá</th>
                        <th>Hình ảnh</th>
                        <th>Nhóm dịch vụ</th>
                        <th>Trạng thái</th>
                        <th>Mô tả</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                        <tr>
                            <td>{{ $service->service_code ?? 'N/A' }}</td>
                            <td>
                                <strong>{{ $service->name }}</strong>
                                @if($service->serviceVariants->count() > 0)
                                    <br><small class="text-muted">(Có {{ $service->serviceVariants->count() }} biến thể)</small>
                                @endif
                            </td>
                            <td>
                                @if($service->base_price)
                                    {{ number_format($service->base_price, 0, ',', '.') }} đ
                                @elseif($service->serviceVariants->count() > 0)
                                    @php
                                        $minPrice = $service->serviceVariants->min('price');
                                        $maxPrice = $service->serviceVariants->max('price');
                                    @endphp
                                    @if($minPrice == $maxPrice)
                                        {{ number_format($minPrice, 0, ',', '.') }} đ
                                    @else
                                        {{ number_format($minPrice, 0, ',', '.') }} - {{ number_format($maxPrice, 0, ',', '.') }} đ
                                    @endif
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
                            <td>
                                <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $service->description }}">
                                    {{ Str::limit($service->description, 50) }}
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('admin.services.edit', $service->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <form action="{{ route('admin.services.destroy', $service->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @if($service->serviceVariants->count() > 0)
                            @foreach($service->serviceVariants as $variant)
                                <tr class="table-light">
                                    <td></td>
                                    <td>
                                        <i class="fas fa-arrow-right text-muted mr-2"></i>
                                        <span class="text-muted">{{ $variant->name }}</span>
                                    </td>
                                    <td>{{ number_format($variant->price, 0, ',', '.') }} đ</td>
                                    <td></td>
                                    <td></td>
                                    <td>
                                        <span class="badge badge-{{ $variant->is_active ? 'success' : 'secondary' }}">
                                            {{ $variant->is_active ? 'Hoạt động' : 'Vô hiệu hóa' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $variant->notes ?? '' }}">
                                            {{ Str::limit($variant->notes ?? '', 50) }}
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.services.edit', $variant->id) }}?type=variant" class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i> Sửa biến thể
                                        </a>
                                        <form action="{{ route('admin.services.destroy', $variant->id) }}" method="POST" class="d-inline delete-form" data-type="variant">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="type" value="variant">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Xóa
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Chưa có dịch vụ nào</td>
                        </tr>
                    @endforelse
                    
                    @if($combos->count() > 0)
                        @foreach($combos as $combo)
                            <tr class="table-info">
                                <td>COMBO-{{ $combo->id }}</td>
                                <td>
                                    <strong>{{ $combo->name }}</strong>
                                    <br><small class="text-muted">(Combo - {{ $combo->comboItems->count() }} dịch vụ)</small>
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
                                <td>
                                    <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $combo->description }}">
                                        {{ Str::limit($combo->description, 50) }}
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('admin.services.edit', $combo->id) }}?type=combo" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Sửa
                                    </a>
                                    <form action="{{ route('admin.services.destroy', $combo->id) }}" method="POST" class="d-inline delete-form" data-type="combo">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="type" value="combo">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $services->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteForms = document.querySelectorAll('.delete-form');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (confirm('Bạn có chắc muốn xóa tạm thời dịch vụ này không?')) {
                    this.submit();
                }
            });
        });
    });
</script>
@endpush
