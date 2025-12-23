@extends('admin.layouts.app')

@section('title', 'Quản lý dịch vụ')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý dịch vụ</h1>
    <div>
        {{-- <a href="{{ route('admin.services.trash') }}" class="btn btn-warning" title="Thùng rác">
            <i class="fas fa-trash-restore"></i>
        </a> --}}
        <a href="{{ route('admin.services.create') }}" class="btn btn-primary" title="Thêm mới">
            <i class="fas fa-plus"></i>
        </a>
    </div>
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
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Mã dịch vụ</th>
                        <th>Tên dịch vụ</th>
                        <th>Loại</th>
                        <th>Giá</th>
                        <th>Thời lượng</th>
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
                            </td>
                            <td>
                                @if($service->serviceVariants->count() > 0)
                                    <span class="badge badge-info">
                                        <i class="fas fa-layer-group"></i> Biến thể ({{ $service->serviceVariants->count() }})
                                    </span>
                                @else
                                    <span class="badge badge-primary">
                                        <i class="fas fa-tag"></i> Đơn
                                    </span>
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
                                @if($service->base_duration)
                                    {{ $service->base_duration }} phút
                                @elseif($service->serviceVariants->count() > 0)
                                    @php
                                        $minDuration = $service->serviceVariants->min('duration');
                                        $maxDuration = $service->serviceVariants->max('duration');
                                    @endphp
                                    @if($minDuration == $maxDuration)
                                        {{ $minDuration }} phút
                                    @else
                                        {{ $minDuration }} - {{ $maxDuration }} phút
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
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-info view-detail-btn" data-id="{{ $service->id }}" data-type="service" title="Xem">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if($service->serviceVariants->count() > 0)
                                    <a href="{{ route('admin.services.edit', $service->id) }}?type=variant" class="btn btn-sm btn-primary" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @else
                                    <a href="{{ route('admin.services.edit', $service->id) }}" class="btn btn-sm btn-primary" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                {{-- <form action="{{ route('admin.services.destroy', $service->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form> --}}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">Chưa có dịch vụ nào</td>
                        </tr>
                    @endforelse
                    
                    @if($combos->count() > 0)
                        @foreach($combos as $combo)
                            <tr class="table-info">
                                <td>COMBO-{{ $combo->id }}</td>
                                <td>
                                    <strong>{{ $combo->name }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge-warning">
                                        <i class="fas fa-box"></i> Combo ({{ $combo->comboItems->count() }})
                                    </span>
                                </td>
                                <td>{{ number_format($combo->price, 0, ',', '.') }} đ</td>
                                <td>
                                    @if($combo->duration)
                                        {{ $combo->duration }} phút
                                    @else
                                        N/A
                                    @endif
                                </td>
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
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info view-detail-btn" data-id="{{ $combo->id }}" data-type="combo" title="Xem">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="{{ route('admin.services.edit', $combo->id) }}?type=combo" class="btn btn-sm btn-primary" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    {{-- <form action="{{ route('admin.services.destroy', $combo->id) }}" method="POST" class="d-inline delete-form" data-type="combo">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="type" value="combo">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form> --}}
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

<!-- Modal Xem chi tiết dịch vụ -->
<div class="modal fade" id="serviceDetailModal" tabindex="-1" role="dialog" aria-labelledby="serviceDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="serviceDetailModalLabel">Chi tiết dịch vụ</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="serviceDetailContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
            </div>
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

        // Xử lý nút xem chi tiết
        const viewDetailBtns = document.querySelectorAll('.view-detail-btn');
        viewDetailBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const type = this.getAttribute('data-type');
                loadServiceDetail(id, type);
            });
        });

        function loadServiceDetail(id, type) {
            const modal = $('#serviceDetailModal');
            const content = $('#serviceDetailContent');
            
            // Hiển thị loading
            content.html('<div class="text-center"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div>');
            modal.modal('show');

            // Lấy dữ liệu từ server
            const url = type === 'combo' 
                ? `{{ route('admin.services.detail', ':id') }}?type=combo`.replace(':id', id)
                : `{{ route('admin.services.detail', ':id') }}?type=service`.replace(':id', id);
            
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    content.html(data.html);
                } else {
                    content.html('<div class="alert alert-danger">Không thể tải chi tiết dịch vụ</div>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                content.html('<div class="alert alert-danger">Đã xảy ra lỗi khi tải dữ liệu</div>');
            });
        }
    });
</script>
@endpush
