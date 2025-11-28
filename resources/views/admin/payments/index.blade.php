@extends('admin.layouts.app')

@section('title', 'Quản lý hóa đơn')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách hóa đơn</h6>
        <a href="{{ route('admin.payments.export') }}" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel"></i> Xuất Excel
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Mã hóa đơn</th>
                        <th>Khách hàng</th>
                        <th>Loại</th>
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
                        <td>{{ number_format($payment->total) }} VNĐ</td>
                        <td>{{ $payment->created_at ? $payment->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                        <td>{{ $payment->created_by }}</td>
                        <td>
                            <a href="{{ route('admin.payments.show', $payment->id) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i> Xem
                            </a>
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
