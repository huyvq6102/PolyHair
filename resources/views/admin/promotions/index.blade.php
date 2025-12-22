@extends('admin.layouts.app')

@section('title', 'Quản lý khuyến mãi')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý khuyến mãi</h1>
    <div>
            @if(auth()->user()->isAdmin())
                @if(isset($isTrash) && $isTrash)
                    <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary" title="Quay lại">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                @else
                    <a href="{{ route('admin.promotions.trash') }}" class="btn btn-warning" title="Thùng rác">
                        <i class="fas fa-trash-restore"></i>
                    </a>
                    <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary" title="Thêm khuyến mãi">
                        <i class="fas fa-plus"></i>
                    </a>
                @endif
            @endif
    </div>
</div>

<!-- Filter Section -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-filter"></i> Bộ lọc
        </h6>
    </div>
    <div class="card-body">
            <form method="GET" action="{{ route('admin.promotions.index') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filter_code">Mã KM</label>
                            <input type="text" 
                                   name="filter_code" 
                                   id="filter_code" 
                                   class="form-control" 
                                   value="{{ request('filter_code') }}"
                                   placeholder="Nhập mã KM...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filter_scope">Phạm vi áp dụng</label>
                            <select name="filter_scope" id="filter_scope" class="form-control">
                                <option value="">Tất cả</option>
                                <option value="service" {{ request('filter_scope') == 'service' ? 'selected' : '' }}>Theo dịch vụ</option>
                                <option value="order" {{ request('filter_scope') == 'order' ? 'selected' : '' }}>Theo hóa đơn</option>
                                <option value="customer_tier" {{ request('filter_scope') == 'customer_tier' ? 'selected' : '' }}>Theo hạng thành viên</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filter_discount_type">Loại giảm giá</label>
                            <select name="filter_discount_type" id="filter_discount_type" class="form-control">
                                <option value="">Tất cả</option>
                                <option value="percent" {{ request('filter_discount_type') == 'percent' ? 'selected' : '' }}>Theo phần trăm (%)</option>
                                <option value="amount" {{ request('filter_discount_type') == 'amount' ? 'selected' : '' }}>Theo số tiền (₫)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filter_discount_amount">Số tiền giảm (từ)</label>
                            <input type="number" 
                                   name="filter_discount_amount" 
                                   id="filter_discount_amount" 
                                   class="form-control" 
                                   value="{{ request('filter_discount_amount') }}"
                                   placeholder="Nhập số tiền...">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Lọc
                        </button>
                        <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Xóa bộ lọc
                        </a>
                        @if(request()->hasAny(['filter_code', 'filter_scope', 'filter_discount_type', 'filter_discount_amount']))
                            <span class="ml-2 text-muted">
                                <i class="fas fa-info-circle"></i> Đang lọc: {{ $promotions->count() }} kết quả
                            </span>
                        @endif
                    </div>
                </div>
            </form>
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
                        <th class="text-center">Thao tác</th>
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
                            <td class="text-center">
                                <a href="{{ route('admin.promotions.show', $promotion->id) }}" class="btn btn-sm btn-info" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(auth()->user()->isAdmin())
                                    @if(isset($isTrash) && $isTrash)
                                        <form action="{{ route('admin.promotions.restore', $promotion->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn khôi phục khuyến mãi này?');">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-success" title="Khôi phục">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.promotions.force-delete', $promotion->id) }}" method="POST" class="d-inline force-delete-form" data-id="{{ $promotion->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Xóa vĩnh viễn">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('admin.promotions.edit', $promotion->id) }}" class="btn btn-sm btn-primary" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.promotions.destroy', $promotion->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn xóa khuyến mãi này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
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
        
        // Tự động lọc khi chọn "Phạm vi áp dụng"
        const filterScope = document.getElementById('filter_scope');
        if (filterScope) {
            filterScope.addEventListener('change', function() {
                // Tự động submit form khi chọn phạm vi áp dụng
                document.getElementById('filterForm').submit();
            });
        }
        
        // Tự động lọc khi chọn "Loại giảm giá"
        const filterDiscountType = document.getElementById('filter_discount_type');
        if (filterDiscountType) {
            filterDiscountType.addEventListener('change', function() {
                // Tự động submit form khi chọn loại giảm giá
                document.getElementById('filterForm').submit();
            });
        }
        
        // Tự động lọc khi nhập "Mã KM" (với debounce để tránh submit quá nhiều)
        const filterCode = document.getElementById('filter_code');
        let filterCodeTimeout = null;
        if (filterCode) {
            filterCode.addEventListener('input', function() {
                // Clear timeout trước đó nếu có
                if (filterCodeTimeout) {
                    clearTimeout(filterCodeTimeout);
                }
                
                // Đợi 500ms sau khi user ngừng gõ mới submit
                filterCodeTimeout = setTimeout(function() {
                    document.getElementById('filterForm').submit();
                }, 500);
            });
            
            // Cũng submit khi blur (rời khỏi input)
            filterCode.addEventListener('blur', function() {
                if (filterCodeTimeout) {
                    clearTimeout(filterCodeTimeout);
                }
                document.getElementById('filterForm').submit();
            });
        }
    });
</script>
@endpush

