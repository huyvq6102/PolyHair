@extends('admin.layouts.app')

@section('title', 'Quản lý khuyến mãi')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý khuyến mãi</h1>
    <div>
        @if(isset($isTrash) && $isTrash)
            <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        @else
            <a href="{{ route('admin.promotions.trash') }}" class="btn btn-warning">
                <i class="fas fa-trash-restore"></i> Thùng rác
            </a>
            <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm khuyến mãi
            </a>
        @endif
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            @if(isset($isTrash) && $isTrash)
                Khuyến mãi đã xóa
            @else
                Danh sách khuyến mãi
            @endif
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="promotionsTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Mã KM</th>
                        <th>Tên</th>
                        <th>Giảm giá</th>
                        <th>Phạm vi áp dụng</th>
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
                            <td>
                                @php
                                    if ($promotion->discount_type === 'percent') {
                                        $discountText = $promotion->discount_percent !== null
                                            ? $promotion->discount_percent . '%'
                                            : '-';
                                    } else {
                                        $discountText = $promotion->discount_amount !== null
                                            ? number_format($promotion->discount_amount, 0, ',', '.') . ' đ'
                                            : '-';
                                    }
                                @endphp
                                {{ $discountText }}
                            </td>
                            <td>
                                @if($promotion->apply_scope === 'order')
                                    <div class="small">
                                        <span class="badge badge-primary">Theo hóa đơn</span>
                                        @if($promotion->min_order_amount)
                                            <div>Hóa đơn từ {{ number_format($promotion->min_order_amount, 0, ',', '.') }} đ</div>
                                        @endif
                                        @if($promotion->max_discount_amount && $promotion->discount_type === 'percent')
                                            <div>Giảm tối đa {{ number_format($promotion->max_discount_amount, 0, ',', '.') }} đ</div>
                                        @endif
                                    </div>
                                @elseif($promotion->apply_scope === 'customer_tier')
                                    <div class="small">
                                        <span class="badge badge-warning">Theo hạng khách hàng</span>
                                        <div>
                                            Áp dụng cho khách từ hạng 
                                            <strong>{{ $promotion->min_customer_tier ?? 'Khách thường' }}</strong> trở lên
                                        </div>
                                        <div class="text-muted">
                                            Giảm trực tiếp trên giá trị hóa đơn khi khách thanh toán.
                                        </div>
                                    </div>
                                @else
                                    @php
                                        $servicesCount = $promotion->services->count();
                                        $combosCount = $promotion->combos->count();
                                        $variantsCount = $promotion->serviceVariants->count();
                                        $totalItems = $servicesCount + $combosCount + $variantsCount;
                                    @endphp
                                    
                                    @if($totalItems > 0)
                                        <div class="small">
                                            @if($servicesCount > 0)
                                                @foreach($promotion->services->take(2) as $service)
                                                    <span class="badge badge-info">{{ $service->name }}</span>
                                                @endforeach
                                                @if($servicesCount > 2)
                                                    <span class="text-muted">+{{ $servicesCount - 2 }} dịch vụ</span>
                                                @endif
                                            @endif
                                            
                                            @if($combosCount > 0)
                                                @foreach($promotion->combos->take(2) as $combo)
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-layer-group"></i> {{ $combo->name }}
                                                    </span>
                                                @endforeach
                                                @if($combosCount > 2)
                                                    <span class="text-muted">+{{ $combosCount - 2 }} combo</span>
                                                @endif
                                            @endif
                                            
                                            @if($variantsCount > 0)
                                                @foreach($promotion->serviceVariants->take(2) as $variant)
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-tags"></i> {{ $variant->name }}
                                                    </span>
                                                @endforeach
                                                @if($variantsCount > 2)
                                                    <span class="text-muted">+{{ $variantsCount - 2 }} biến thể</span>
                                                @endif
                                            @endif
                                            
                                            @php
                                                $displayedCount = min(2, $servicesCount) + min(2, $combosCount) + min(2, $variantsCount);
                                                $remaining = $totalItems - $displayedCount;
                                            @endphp
                                            @if($remaining > 0)
                                                <span class="text-muted">+{{ $remaining }} mục khác</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">Tất cả dịch vụ</span>
                                    @endif
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
                                @if(isset($isTrash) && $isTrash)
                                    <a href="{{ route('admin.promotions.show', $promotion->id) }}" class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i> Xem
                                    </a>
                                    <form action="{{ route('admin.promotions.restore', $promotion->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn khôi phục khuyến mãi này?');">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-success" title="Khôi phục">
                                            <i class="fas fa-undo"></i> Khôi phục
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.promotions.force-delete', $promotion->id) }}" method="POST" class="d-inline force-delete-form" data-id="{{ $promotion->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Xóa vĩnh viễn">
                                            <i class="fas fa-trash-alt"></i> Xóa vĩnh viễn
                                        </button>
                                    </form>
                                @else
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
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                @if(isset($isTrash) && $isTrash)
                                    Thùng rác trống
                                @else
                                    Chưa có khuyến mãi nào
                                @endif
                            </td>
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

        // Xử lý xóa vĩnh viễn
        const forceDeleteForms = document.querySelectorAll('.force-delete-form');
        forceDeleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const promotionId = this.getAttribute('data-id');
                if (confirm('Bạn có chắc chắn muốn xóa vĩnh viễn khuyến mãi này? Hành động này không thể hoàn tác!')) {
                    this.submit();
                }
            });
        });
    });
</script>
@endpush

