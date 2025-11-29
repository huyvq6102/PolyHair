@extends('admin.layouts.app')

@section('title', 'Chi tiết khuyến mãi')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Chi tiết khuyến mãi</h1>
    <div>
        <a href="{{ route('admin.promotions.edit', $promotion->id) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Chỉnh sửa
        </a>
        <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</div>

<!-- Promotion Info -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Thông tin khuyến mãi</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold">Mã khuyến mãi:</label>
                    <p class="form-control-plaintext">{{ $promotion->code }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold">Tên khuyến mãi:</label>
                    <p class="form-control-plaintext">{{ $promotion->name }}</p>
                </div>
            </div>
        </div>

        @if($promotion->description)
        <div class="form-group">
            <label class="font-weight-bold">Mô tả:</label>
            <p class="form-control-plaintext">{{ $promotion->description }}</p>
        </div>
        @endif

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold">% Giảm giá:</label>
                    <p class="form-control-plaintext">
                        <span class="badge badge-success" style="font-size: 1rem; padding: 0.5rem 1rem;">
                            {{ $promotion->discount_percent }}%
                        </span>
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold">Ngày bắt đầu:</label>
                    <p class="form-control-plaintext">
                        {{ $promotion->start_date ? $promotion->start_date->format('d/m/Y') : 'N/A' }}
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold">Ngày kết thúc:</label>
                    <p class="form-control-plaintext">
                        {{ $promotion->end_date ? $promotion->end_date->format('d/m/Y') : 'Không giới hạn' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="font-weight-bold">Trạng thái:</label>
            <p class="form-control-plaintext">
                @php
                    $statusLabel = $statuses[$promotion->status] ?? ucfirst($promotion->status);
                    $badgeClass = match($promotion->status) {
                        'active' => 'success',
                        'inactive' => 'secondary',
                        'scheduled' => 'info',
                        'expired' => 'warning',
                        default => 'secondary'
                    };
                @endphp
                <span class="badge badge-{{ $badgeClass }}" style="font-size: 1rem; padding: 0.5rem 1rem;">
                    {{ $statusLabel }}
                </span>
            </p>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold">Ngày tạo:</label>
                    <p class="form-control-plaintext">
                        {{ $promotion->created_at ? $promotion->created_at->format('d/m/Y H:i:s') : 'N/A' }}
                    </p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold">Ngày cập nhật:</label>
                    <p class="form-control-plaintext">
                        {{ $promotion->updated_at ? $promotion->updated_at->format('d/m/Y H:i:s') : 'N/A' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Services Applied -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Dịch vụ áp dụng</h6>
    </div>
    <div class="card-body">
        @if($promotion->services->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Mã dịch vụ</th>
                            <th>Tên dịch vụ</th>
                            <th>Danh mục</th>
                            <th>Giá gốc</th>
                            <th>Thời lượng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($promotion->services as $service)
                            <tr>
                                <td>{{ $service->service_code ?? 'N/A' }}</td>
                                <td>
                                    <strong>{{ $service->name }}</strong>
                                    @if($service->is_featured)
                                        <span class="badge badge-warning ml-2">Nổi bật</span>
                                    @endif
                                </td>
                                <td>
                                    @if($service->category)
                                        <span class="badge badge-info">{{ $service->category->name }}</span>
                                    @else
                                        <span class="text-muted">Chưa phân loại</span>
                                    @endif
                                </td>
                                <td>
                                    @if($service->base_price)
                                        {{ number_format($service->base_price, 0, ',', '.') }} đ
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($service->base_duration)
                                        {{ $service->base_duration }} phút
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <p class="text-muted">
                    <i class="fas fa-info-circle"></i> 
                    Tổng cộng: <strong>{{ $promotion->services->count() }}</strong> dịch vụ được áp dụng khuyến mãi này
                </p>
            </div>
        @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                Khuyến mãi này áp dụng cho <strong>tất cả dịch vụ</strong> trong hệ thống.
            </div>
        @endif
    </div>
</div>

<!-- Promotion Usage Stats (if needed in future) -->
{{-- @if($promotion->promotionUsages && $promotion->promotionUsages->count() > 0)
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Thống kê sử dụng</h6>
    </div>
    <div class="card-body">
        <p class="form-control-plaintext">
            <strong>Số lần sử dụng:</strong> {{ $promotion->promotionUsages->count() }} lần
        </p>
    </div>
</div>
@endif --}}
@endsection

