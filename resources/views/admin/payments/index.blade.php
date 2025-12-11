@extends('admin.layouts.app')

@section('title', 'Quản lý hóa đơn')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý hóa đơn</h1>
    <a href="{{ route('admin.payments.export') }}" class="btn btn-success">
        <i class="fas fa-file-excel"></i> Xuất Excel
    </a>
</div>

<!-- Filter -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Tìm kiếm và lọc hóa đơn</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.payments.index') }}">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="invoice_code">Mã hóa đơn</label>
                        <input type="text" name="invoice_code" id="invoice_code" class="form-control" 
                               value="{{ $filters['invoice_code'] ?? '' }}" placeholder="Nhập mã hóa đơn">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="customer_name">Tên khách hàng</label>
                        <input type="text" name="customer_name" id="customer_name" class="form-control" 
                               value="{{ $filters['customer_name'] ?? '' }}" placeholder="Nhập tên khách hàng">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="date_from">Từ ngày</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" 
                               value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="date_to">Đến ngày</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" 
                               value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="status">Trạng thái</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">-- Tất cả --</option>
                            <option value="pending" {{ ($filters['status'] ?? '') == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                            <option value="completed" {{ ($filters['status'] ?? '') == 'completed' ? 'selected' : '' }}>Thành công</option>
                            <option value="failed" {{ ($filters['status'] ?? '') == 'failed' ? 'selected' : '' }}>Thất bại</option>
                            <option value="refunded" {{ ($filters['status'] ?? '') == 'refunded' ? 'selected' : '' }}>Hoàn tiền</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="type">Loại</label>
                        <select name="type" id="type" class="form-control">
                            <option value="">-- Tất cả --</option>
                            <option value="appointment" {{ ($filters['type'] ?? '') == 'appointment' ? 'selected' : '' }}>Dịch vụ</option>
                            <option value="order" {{ ($filters['type'] ?? '') == 'order' ? 'selected' : '' }}>Đơn hàng</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group" style="margin-top: 32px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                        <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Làm mới
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách hóa đơn</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Mã hóa đơn</th>
                        <th>Khách hàng</th>
                        <th>Loại</th>
                        <th>Trạng thái</th>
                        <th>Tổng tiền</th>
                        <th>Ngày tạo</th>
                        <th>Người tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                    <tr>
                        <td>{{ $payment->invoice_code ?? 'N/A' }}</td>
                        <td>{{ $payment->user->name ?? 'Khách vãng lai' }}</td>
                        <td>
                            @if($payment->appointment_id)
                                <span class="badge badge-info">Dịch vụ</span>
                            @elseif($payment->order_id)
                                <span class="badge badge-success">Đơn hàng</span>
                            @else
                                <span class="badge badge-secondary">Khác</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $status = $payment->status ?? 'pending';
                                $badgeClass = 'secondary';
                                $statusText = 'Chờ xử lý';
                                
                                if ($status == 'completed') {
                                    $badgeClass = 'success';
                                    $statusText = 'Thành công';
                                } elseif ($status == 'failed') {
                                    $badgeClass = 'danger';
                                    $statusText = 'Thất bại';
                                } elseif ($status == 'refunded') {
                                    $badgeClass = 'warning';
                                    $statusText = 'Hoàn tiền';
                                }
                            @endphp
                            <span class="badge badge-{{ $badgeClass }}">{{ $statusText }}</span>
                        </td>
                        <td>{{ number_format($payment->total) }} VNĐ</td>
                        <td>{{ $payment->created_at ? $payment->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                        <td>{{ $payment->created_by }}</td>
                        <td>
                            <a href="{{ route('admin.payments.show', $payment->id) }}" class="btn btn-info btn-sm" title="Xem chi tiết">
                                <i class="fas fa-eye"></i> Xem
                            </a>
                            <form action="{{ route('admin.payments.destroy', $payment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa hóa đơn này? Hành động này không thể hoàn tác!');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Xóa hóa đơn">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-3">
                {{ $payments->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
