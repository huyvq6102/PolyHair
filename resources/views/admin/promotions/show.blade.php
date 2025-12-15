@extends('admin.layouts.app')

@section('title', 'Chi tiết khuyến mãi')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Chi tiết khuyến mãi: {{ $promotion->code }}</h1>
    <div>
        <a href="{{ route('admin.promotions.edit', $promotion->id) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-edit"></i> Chỉnh sửa
        </a>
        <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Quay lại danh sách
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Thông tin chung</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Mã khuyến mãi</dt>
                    <dd class="col-sm-8">{{ $promotion->code }}</dd>

                    <dt class="col-sm-4">Tên khuyến mãi</dt>
                    <dd class="col-sm-8">{{ $promotion->name }}</dd>

                    @if($promotion->description)
                        <dt class="col-sm-4">Mô tả</dt>
                        <dd class="col-sm-8">{{ $promotion->description }}</dd>
                    @endif

                    <dt class="col-sm-4">Trạng thái</dt>
                    <dd class="col-sm-8">
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
                        <span class="badge badge-{{ $badgeClass }}">{{ $statusLabel }}</span>
                    </dd>

                    <dt class="col-sm-4">Thời gian áp dụng</dt>
                    <dd class="col-sm-8">
                        {{ $promotion->start_date ? $promotion->start_date->format('d/m/Y') : 'N/A' }}
                        -
                        {{ $promotion->end_date ? $promotion->end_date->format('d/m/Y') : 'Không giới hạn' }}
                    </dd>

                    <dt class="col-sm-4">Ngày tạo</dt>
                    <dd class="col-sm-8">
                        {{ $promotion->created_at ? $promotion->created_at->format('d/m/Y H:i') : 'N/A' }}
                    </dd>

                    <dt class="col-sm-4">Ngày cập nhật</dt>
                    <dd class="col-sm-8">
                        {{ $promotion->updated_at ? $promotion->updated_at->format('d/m/Y H:i') : 'N/A' }}
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quy tắc giảm giá</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Loại giảm giá</dt>
                    <dd class="col-sm-8">
                        @if($promotion->discount_type === 'percent')
                            Giảm theo %
                        @else
                            Giảm theo số tiền
                        @endif
                    </dd>

                    <dt class="col-sm-4">Giá trị giảm</dt>
                    <dd class="col-sm-8">
                        @if($promotion->discount_type === 'percent')
                            {{ $promotion->discount_percent !== null ? $promotion->discount_percent . '%' : '-' }}
                            @if($promotion->max_discount_amount)
                                <span class="text-muted">
                                    (tối đa {{ number_format($promotion->max_discount_amount, 0, ',', '.') }} đ)
                                </span>
                            @endif
                        @else
                            {{ $promotion->discount_amount !== null ? number_format($promotion->discount_amount, 0, ',', '.') . ' đ' : '-' }}
                        @endif
                    </dd>

                    <dt class="col-sm-4">Áp dụng theo</dt>
                    <dd class="col-sm-8">
                        @if($promotion->apply_scope === 'order')
                            <span class="badge badge-primary">Theo hóa đơn</span>
                        @elseif($promotion->apply_scope === 'customer_tier')
                            <span class="badge badge-warning">Theo hạng khách hàng</span>
                        @else
                            <span class="badge badge-info">Theo dịch vụ</span>
                        @endif
                    </dd>

                    @if($promotion->apply_scope === 'order')
                        <dt class="col-sm-4">Hóa đơn tối thiểu</dt>
                        <dd class="col-sm-8">
                            @if($promotion->min_order_amount)
                                {{ number_format($promotion->min_order_amount, 0, ',', '.') }} đ
                            @else
                                Không yêu cầu
                            @endif
                        </dd>
                    @endif

                    @if($promotion->apply_scope === 'customer_tier')
                        <dt class="col-sm-4">Hạng khách hàng</dt>
                        <dd class="col-sm-8">
                            Áp dụng cho khách từ hạng 
                            <strong>{{ $promotion->min_customer_tier ?? 'Khách thường' }}</strong> trở lên
                        </dd>
                    @endif

                    <dt class="col-sm-4">Số lần / tài khoản</dt>
                    <dd class="col-sm-8">
                        @if($promotion->per_user_limit)
                            Tối đa {{ $promotion->per_user_limit }} lần
                        @else
                            Không giới hạn
                        @endif
                    </dd>

                    <dt class="col-sm-4">Tổng lượt dùng</dt>
                    <dd class="col-sm-8">
                        @if($promotion->usage_limit)
                            Tối đa {{ $promotion->usage_limit }} lượt cho toàn hệ thống
                        @else
                            Không giới hạn
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>

@if($promotion->apply_scope === 'service')
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Dịch vụ áp dụng</h6>
        </div>
        <div class="card-body">
            @php
                $servicesCount = $promotion->services->count();
                $combosCount = $promotion->combos->count();
                $variantsCount = $promotion->serviceVariants->count();
                $totalItems = $servicesCount + $combosCount + $variantsCount;
            @endphp

            @if($totalItems === 0)
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle"></i>
                    Khuyến mãi này hiện đang áp dụng cho <strong>tất cả dịch vụ</strong>.
                </div>
            @else
                <div class="row">
                    <div class="col-md-4">
                        <h6 class="font-weight-bold">Dịch vụ</h6>
                        @forelse($promotion->services as $service)
                            <div class="mb-2">
                                <span class="badge badge-info">{{ $service->name }}</span>
                                @if($service->category)
                                    <small class="text-muted">({{ $service->category->name }})</small>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted mb-0">Không có dịch vụ cụ thể.</p>
                        @endforelse
                    </div>
                    <div class="col-md-4">
                        <h6 class="font-weight-bold">Combo</h6>
                        @forelse($promotion->combos as $combo)
                            <div class="mb-2">
                                <span class="badge badge-warning">
                                    <i class="fas fa-layer-group"></i> {{ $combo->name }}
                                </span>
                                @if($combo->price)
                                    <small class="text-muted">
                                        - {{ number_format($combo->price, 0, ',', '.') }} đ
                                    </small>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted mb-0">Không có combo nào.</p>
                        @endforelse
                    </div>
                    <div class="col-md-4">
                        <h6 class="font-weight-bold">Dịch vụ biến thể</h6>
                        @forelse($promotion->serviceVariants as $variant)
                            <div class="mb-2">
                                <span class="badge badge-success">
                                    <i class="fas fa-tags"></i> {{ $variant->name }}
                                </span>
                                @if($variant->service)
                                    <small class="text-muted">({{ $variant->service->name }})</small>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted mb-0">Không có dịch vụ biến thể.</p>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif
@endsection


