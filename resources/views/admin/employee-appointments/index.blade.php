@extends('admin.layouts.app')

@section('title', 'Quản lý đơn đặt')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý đơn đặt</h1>
    <a href="{{ route('employee.appointments.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tạo lịch hẹn mới
    </a>
</div>

<!-- Filter -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Tìm kiếm và lọc</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('employee.appointments.index') }}" class="form-row">
            <div class="form-group col-md-3">
                <label for="customer_name">Tên khách hàng:</label>
                <input type="text" name="customer_name" id="customer_name" class="form-control" 
                       value="{{ $filters['customer_name'] ?? '' }}" placeholder="Nhập tên khách hàng">
            </div>
            <div class="form-group col-md-3">
                <label for="phone">Số điện thoại:</label>
                <input type="text" name="phone" id="phone" class="form-control" 
                       value="{{ $filters['phone'] ?? '' }}" placeholder="Nhập số điện thoại">
            </div>
            <div class="form-group col-md-2">
                <label for="date">Ngày đặt:</label>
                <input type="date" name="date" id="date" class="form-control" 
                       value="{{ $filters['date'] ?? '' }}">
            </div>
            <div class="form-group col-md-2">
                <label for="status">Trạng thái:</label>
                <select name="status" id="status" class="form-control">
                    <option value="">Tất cả</option>
                    <option value="Chờ xử lý" {{ ($filters['status'] ?? '') == 'Chờ xử lý' ? 'selected' : '' }}>Chờ xử lý</option>
                    <option value="Chờ xác nhận" {{ ($filters['status'] ?? '') == 'Chờ xác nhận' ? 'selected' : '' }}>Chờ xác nhận</option>
                    <option value="Đã xác nhận" {{ ($filters['status'] ?? '') == 'Đã xác nhận' ? 'selected' : '' }}>Đã xác nhận</option>
                    <option value="Đang thực hiện" {{ ($filters['status'] ?? '') == 'Đang thực hiện' ? 'selected' : '' }}>Đang thực hiện</option>
                    <option value="Hoàn thành" {{ ($filters['status'] ?? '') == 'Hoàn thành' ? 'selected' : '' }}>Hoàn thành</option>
                    <option value="Đã hủy" {{ ($filters['status'] ?? '') == 'Đã hủy' ? 'selected' : '' }}>Đã hủy</option>
                </select>
            </div>
            <div class="form-group col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary mr-2">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
                <a href="{{ route('employee.appointments.index') }}" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Làm mới
                </a>
            </div>
        </form>
    </div>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách đơn đặt</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Mã đơn đặt</th>
                        <th>Tên khách hàng</th>
                        <th>Số điện thoại</th>
                        <th>Dịch vụ</th>
                        <th>Ngày và giờ</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appointment)
                        <tr>
                            <td>{{ $appointment->booking_code ?? 'N/A' }}</td>
                            <td>{{ $appointment->user->name ?? 'N/A' }}</td>
                            <td>{{ $appointment->user->phone ?? 'N/A' }}</td>
                            <td>
                                @if($appointment->appointmentDetails->count() > 0)
                                    @foreach($appointment->appointmentDetails->take(2) as $detail)
                                        <span class="badge badge-info">
                                            {{ $detail->serviceVariant->service->name ?? 'N/A' }}
                                        </span>
                                    @endforeach
                                    @if($appointment->appointmentDetails->count() > 2)
                                        <span class="badge badge-secondary">
                                            +{{ $appointment->appointmentDetails->count() - 2 }} khác
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted">Chưa có dịch vụ</span>
                                @endif
                            </td>
                            <td>
                                @if($appointment->start_at)
                                    {{ $appointment->start_at->format('d/m/Y H:i') }}
                                @else
                                    <span class="text-muted">Chưa có</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ 
                                    $appointment->status == 'Hoàn thành' ? 'success' : 
                                    ($appointment->status == 'Đã hủy' ? 'danger' : 
                                    ($appointment->status == 'Đã xác nhận' || $appointment->status == 'Đang thực hiện' ? 'info' : 'warning')) 
                                }}">
                                    {{ $appointment->status }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('employee.appointments.show', $appointment->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Xem
                                </a>
                                @if(in_array($appointment->status, ['Chờ xử lý', 'Chờ xác nhận']))
                                    <form action="{{ route('employee.appointments.destroy', $appointment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đơn đặt này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                @php
                                    $hasFilters = !empty($filters['customer_name']) || 
                                                 !empty($filters['phone']) || 
                                                 !empty($filters['date']) || 
                                                 !empty($filters['status']);
                                    $activeFilters = [];
                                    if (!empty($filters['customer_name'])) {
                                        $activeFilters[] = 'tên khách hàng "' . $filters['customer_name'] . '"';
                                    }
                                    if (!empty($filters['phone'])) {
                                        $activeFilters[] = 'số điện thoại "' . $filters['phone'] . '"';
                                    }
                                    if (!empty($filters['date'])) {
                                        $activeFilters[] = 'ngày "' . \Carbon\Carbon::parse($filters['date'])->format('d/m/Y') . '"';
                                    }
                                    if (!empty($filters['status'])) {
                                        $activeFilters[] = 'trạng thái "' . $filters['status'] . '"';
                                    }
                                @endphp
                                
                                @if($hasFilters)
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle"></i> 
                                        <strong>Không tìm thấy đơn đặt nào</strong>
                                        @if(count($activeFilters) > 0)
                                            <br>
                                            <small>Với {{ implode(', ', $activeFilters) }}</small>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-muted">Chưa có đơn đặt nào</div>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($appointments->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap">
                <div class="text-muted mb-2 mb-md-0">
                    Hiển thị {{ $appointments->firstItem() }} đến {{ $appointments->lastItem() }} trong tổng số {{ $appointments->total() }} kết quả
                </div>
                <div>
                    {{ $appointments->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
                </div>
            </div>
        @elseif($appointments->total() > 0)
            <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap">
                <div class="text-muted mb-2 mb-md-0">
                    Hiển thị {{ $appointments->firstItem() }} đến {{ $appointments->lastItem() }} trong tổng số {{ $appointments->total() }} kết quả
                </div>
                <div>
                    <!-- No pagination needed for single page -->
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .pagination {
        margin-bottom: 0;
    }
    .pagination .page-link {
        color: #5a5c69;
        border-color: #d1d3e2;
    }
    .pagination .page-item.active .page-link {
        background-color: #4e73df;
        border-color: #4e73df;
    }
    .pagination .page-link:hover {
        color: #4e73df;
        background-color: #eaecf4;
        border-color: #d1d3e2;
    }
    .pagination .disabled .page-link {
        color: #858796;
        background-color: #fff;
        border-color: #d1d3e2;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Auto-submit form on date change
        $('#date').on('change', function() {
            // Optional: auto-submit if needed
        });
    });
</script>
@endpush