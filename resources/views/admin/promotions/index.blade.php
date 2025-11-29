@extends('admin.layouts.app')

@section('title', 'Quản lý khuyến mãi')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý khuyến mãi</h1>
    <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Thêm khuyến mãi
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách khuyến mãi</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="promotionsTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Mã KM</th>
                        <th>Tên</th>
                        <th>% giảm</th>
                        <th>Dịch vụ áp dụng</th>
                        <th>Thời gian áp dụng</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($promotions as $promotion)
                        <tr>
                            <td>{{ $promotion->code }}</td>
                            <td>{{ $promotion->name }}</td>
                            <td>{{ $promotion->discount_percent }}%</td>
                            <td>
                                @if($promotion->services->count() > 0)
                                    <div class="small">
                                        @foreach($promotion->services->take(3) as $service)
                                            <span class="badge badge-info">{{ $service->name }}</span>
                                        @endforeach
                                        @if($promotion->services->count() > 3)
                                            <span class="text-muted">+{{ $promotion->services->count() - 3 }} dịch vụ khác</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">Tất cả dịch vụ</span>
                                @endif
                            </td>
                            <td>
                                {{ optional($promotion->start_date)->format('d/m/Y') }} -
                                {{ optional($promotion->end_date)->format('d/m/Y') ?? 'Không giới hạn' }}
                            </td>
                            <td>
                                @php
                                    $statusLabels = $statuses ?? [
                                        'inactive' => 'Ngừng áp dụng',
                                        'active' => 'Đang chạy',
                                        'scheduled' => 'Chờ áp dụng',
                                        'expired' => 'Đã kết thúc',
                                    ];
                                    $statusLabel = $statusLabels[$promotion->status] ?? ucfirst($promotion->status);
                                    $badgeClass = match($promotion->status) {
                                        'active' => 'success',
                                        'inactive' => 'secondary',
                                        'scheduled' => 'info',
                                        'expired' => 'warning',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge badge-{{ $badgeClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.promotions.show', $promotion->id) }}" class="btn btn-sm btn-info" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i> Xem
                                </a>
                                <a href="{{ route('admin.promotions.edit', $promotion->id) }}" class="btn btn-sm btn-primary" title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <form action="{{ route('admin.promotions.destroy', $promotion->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn xóa khuyến mãi này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Chưa có khuyến mãi nào</td>
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
    $(function () {
        $('#promotionsTable').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Vietnamese.json"
            }
        });
    });
</script>
@endpush

