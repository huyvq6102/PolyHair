@extends('admin.layouts.app')

@section('title', 'Lịch đã hủy')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Lịch đã hủy</h1>
    <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại danh sách
    </a>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách lịch đã hủy</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Tên</th>
                        <th>SĐT</th>
                        <th>Email</th>
                        <th>Tên nhân viên</th>
                        <th>Dịch vụ</th>
                        <th>Ngày đặt</th>
                        <th>Lý do hủy</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appointment)
                        <tr>
                            <td>{{ $appointment->booking_code ?? 'N/A' }}</td>
                            <td>{{ $appointment->user->name ?? 'N/A' }}</td>
                            <td>{{ $appointment->user->phone ?? 'N/A' }}</td>
                            <td>{{ $appointment->user->email ?? 'N/A' }}</td>
                            <td>{{ $appointment->employee->user->name ?? 'Chưa phân công' }}</td>
                            <td>
                                @if($appointment->appointmentDetails->count() > 0)
                                    @foreach($appointment->appointmentDetails as $detail)
                                        @if($detail->combo_id)
                                            {{ $detail->combo->name ?? ($detail->notes ?? 'Combo') }}
                                        @elseif($detail->serviceVariant)
                                            {{ $detail->serviceVariant->name ?? ($detail->serviceVariant->service->name ?? 'N/A') }}
                                        @else
                                            {{ $detail->notes ?? 'Dịch vụ đơn' }}
                                        @endif
                                        @if(!$loop->last), @endif
                                    @endforeach
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>{{ $appointment->start_at ? $appointment->start_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            <td>{{ $appointment->cancellation_reason ?? 'N/A' }}</td>
                            <td>
                                <form action="{{ route('admin.appointments.restore', $appointment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn khôi phục lịch này không?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" title="Khôi phục">
                                        <i class="fas fa-undo"></i> Khôi phục
                                    </button>
                                </form>
                                <form action="{{ route('admin.appointments.force-delete', $appointment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa vĩnh viễn lịch này không? Hành động này không thể hoàn tác!');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Xóa vĩnh viễn">
                                        <i class="fas fa-trash"></i> Xóa vĩnh viễn
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Chưa có lịch đã hủy nào</td>
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
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Vietnamese.json"
            },
            "order": [[0, "desc"]]
        });
    });
</script>
@endpush

