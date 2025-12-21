@extends('admin.layouts.app')

@section('title', 'Chi tiết hóa đơn')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Chi tiết hóa đơn: {{ $payment->invoice_code }}</h6>
        <div>
            <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="fas fa-print"></i> In hóa đơn
            </button>
            @if(!auth()->user()->isEmployee())
            <form action="{{ route('admin.payments.destroy', $payment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa hóa đơn này? Hành động này không thể hoàn tác!');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash"></i> Xóa hóa đơn
                </button>
            </form>
            @endif
        </div>
    </div>
    <div class="card-body" id="printableArea">
        <div class="row mb-4">
            <div class="col-sm-6">
                <h5 class="mb-3">Thông tin khách hàng:</h5>
                <div><strong>Tên:</strong> {{ $payment->user->name ?? 'Khách vãng lai' }}</div>
                <div><strong>Email:</strong> {{ $payment->user->email ?? 'N/A' }}</div>
                <div><strong>SĐT:</strong> {{ $payment->user->phone ?? 'N/A' }}</div>
            </div>
            <div class="col-sm-6 text-right">
                <h5 class="mb-3">Thông tin hóa đơn:</h5>
                <div><strong>Mã hóa đơn:</strong> #{{ $payment->invoice_code }}</div>
                <div><strong>Ngày tạo:</strong> {{ $payment->created_at ? $payment->created_at->format('d/m/Y H:i') : 'N/A' }}</div>
                <div><strong>Người lập:</strong> {{ $payment->created_by }}</div>
                <div><strong>Hình thức:</strong> {{ $payment->payment_type == 'cash' ? 'Tiền mặt' : 'Chuyển khoản/Online' }}</div>
                <div class="mt-2">
                    <strong>Trạng thái:</strong>
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
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Mô tả</th>
                        <th class="text-center">Số lượng</th>
                        <th class="text-right">Đơn giá</th>
                        <th class="text-right">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @php $stt = 1; @endphp
                    
                    {{-- Hiển thị chi tiết từ Lịch hẹn (Dịch vụ) --}}
                    @if($payment->appointment)
                        @foreach($payment->appointment->appointmentDetails as $detail)
                        <tr>
                            <td>{{ $stt++ }}</td>
                            <td>
                                {{ $detail->serviceVariant->service->name ?? 'Dịch vụ' }} 
                                <small>({{ $detail->serviceVariant->name ?? '' }})</small>
                            </td>
                            <td class="text-center">1</td>
                            <td class="text-right">{{ number_format($detail->price_snapshot) }}</td>
                            <td class="text-right">{{ number_format($detail->price_snapshot) }}</td>
                        </tr>
                        @endforeach
                    @endif

                    {{-- Hiển thị chi tiết từ Đơn hàng (Sản phẩm) --}}
                    @if($payment->order)
                        @foreach($payment->order->orderDetails as $detail)
                        <tr>
                            <td>{{ $stt++ }}</td>
                            <td>{{ $detail->product->name ?? 'Sản phẩm' }}</td>
                            <td class="text-center">{{ $detail->quantity }}</td>
                            <td class="text-right">{{ number_format($detail->price) }}</td>
                            <td class="text-right">{{ number_format($detail->price * $detail->quantity) }}</td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        <div class="row">
            <div class="col-lg-4 col-sm-5 ml-auto">
                <table class="table table-clear">
                    <tbody>
                        @if($payment->appointment && $payment->appointment->promotionUsages->isNotEmpty())
                            @foreach($payment->appointment->promotionUsages as $usage)
                            <tr>
                                <td class="left text-success">
                                    <strong>Khuyến mại</strong> <br> 
                                    <small>({{ $usage->promotion->code }} - {{ $usage->promotion->discount_percent }}%)</small>
                                </td>
                                <td class="text-right text-success">Đã áp dụng</td>
                            </tr>
                            @endforeach
                        @endif

                        <tr>
                            <td class="left"><strong>Tạm tính {{ ($payment->appointment && $payment->appointment->promotionUsages->isNotEmpty()) ? '(Sau giảm)' : '' }}</strong></td>
                            <td class="text-right">{{ number_format($payment->price) }} VNĐ</td>
                        </tr>
                        <tr>
                            <td class="left"><strong>VAT (10%)</strong></td>
                            <td class="text-right">{{ number_format($payment->VAT) }} VNĐ</td>
                        </tr>
                        <tr>
                            <td class="left"><strong>Tổng cộng</strong></td>
                            <td class="text-right"><strong>{{ number_format($payment->total) }} VNĐ</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Style đơn giản cho việc in ấn --}}
<style>
@media print {
    body * {
        visibility: hidden;
    }
    #printableArea, #printableArea * {
        visibility: visible;
    }
    #printableArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .btn {
        display: none; 
    }
}
</style>
@endsection
