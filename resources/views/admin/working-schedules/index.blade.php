@extends('admin.layouts.app')

@section('title', 'Quản lý lịch nhân viên')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý lịch nhân viên</h1>
    <div>
        <form action="{{ route('admin.working-schedules.delete-all') }}" method="POST" class="d-inline" onsubmit="return confirmDeleteAll();">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger mr-2">
                <i class="fas fa-trash-alt"></i> Xóa tất cả
            </button>
        </form>
        <a href="{{ route('admin.working-schedules.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm mới
        </a>
    </div>
</div>

<!-- Filter -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Lọc lịch</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.working-schedules.index') }}" class="form-row">
            <div class="form-group col-md-4">
                <label for="employee_name">Tên nhân viên</label>
                <input type="text" name="employee_name" id="employee_name" class="form-control" placeholder="Nhập tên nhân viên"
                    value="{{ $filters['employee_name'] ?? '' }}">
            </div>
            <div class="form-group col-md-4">
                <label for="work_date">Ngày làm việc</label>
                <input type="date" name="work_date" id="work_date" class="form-control" value="{{ $filters['work_date'] ?? '' }}">
            </div>
            <div class="form-group col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-filter"></i> Lọc</button>
                <a href="{{ route('admin.working-schedules.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i> Làm mới</a>
                <a href="{{ route('admin.working-schedules.trash') }}" class="btn btn-warning ml-2"><i class="fas fa-trash"></i> Thùng rác</a>
            </div>
        </form>
    </div>
</div>

<!-- Grouped Schedules -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách lịch</h6>
    </div>
    <div class="card-body">
        @forelse($groupedSchedules as $group)
            @php
                $workDate = $group['work_date'];
                $shift = $group['shift'];
                $schedulesByPosition = $group['schedules'];
                $requiredPositions = ['Stylist', 'Barber', 'Shampooer', 'Receptionist'];
            @endphp
            
            <div class="card mb-3 border-left-primary">
                <div class="card-body">
                    <div class="row align-items-center mb-2">
                        <div class="col-md-4">
                            <h6 class="mb-0 font-weight-bold text-primary">
                                <i class="fas fa-calendar"></i> {{ $workDate->format('d/m/Y') }}
                            </h6>
                        </div>
                        <div class="col-md-4">
                            <h6 class="mb-0">
                                <i class="fas fa-clock"></i> {{ $shift->name ?? 'N/A' }} ({{ $shift->display_time ?? 'N/A' }})
                            </h6>
                        </div>
                        <div class="col-md-4 text-right">
                            <a href="{{ route('admin.working-schedules.show', $schedulesByPosition->flatten()->first()->id) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i> Chi tiết
                            </a>
                        </div>
                    </div>
                    
                    <div class="row">
                        @foreach($requiredPositions as $position)
                            @php
                                $positionSchedules = $schedulesByPosition->get($position, collect());
                                $schedule = $positionSchedules->first();
                            @endphp
                            <div class="col-md-3 mb-2">
                                <div class="border rounded p-2 h-100">
                                    <small class="text-muted d-block mb-1">
                                        <strong>{{ $position }}:</strong>
                                    </small>
                                    @if($schedule)
                                        @php
                                            $employee = $schedule->employee;
                                            $user = $employee->user ?? null;
                                            $status = $schedule->status;
                                            $badge = match($status) {
                                                'pending' => 'warning',
                                                'approved' => 'success',
                                                'cancelled' => 'danger',
                                                'completed' => 'info',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="font-weight-bold">{{ $user->name ?? 'N/A' }}</span>
                                            <span class="badge badge-{{ $badge }} badge-sm">{{ $statusOptions[$status] ?? 'N/A' }}</span>
                                        </div>
                                        <div class="mt-1">
                                            <a href="{{ route('admin.working-schedules.edit', $schedule->id) }}" class="btn btn-xs btn-primary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.working-schedules.destroy', $schedule->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa lịch này?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-muted small">Chưa có</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <p class="text-muted mb-0">Chưa có lịch nào</p>
            </div>
        @endforelse

        @if($groupedSchedules->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $groupedSchedules->links() }}
            </div>
        @endif
    </div>
</div>

<style>
.border-left-primary {
    border-left: 4px solid #4e73df !important;
}
.btn-xs {
    padding: 0.125rem 0.25rem;
    font-size: 0.75rem;
    line-height: 1.5;
    border-radius: 0.2rem;
}
.badge-sm {
    font-size: 0.7rem;
    padding: 0.2rem 0.4rem;
}
</style>

@push('scripts')
<script>
function confirmDeleteAll() {
    return confirm('⚠️ CẢNH BÁO: Bạn có chắc chắn muốn xóa TẤT CẢ lịch làm việc?\n\nTất cả lịch sẽ được chuyển vào thùng rác. Hành động này không thể hoàn tác dễ dàng!\n\nNhấn OK để xác nhận xóa tất cả.');
}
</script>
@endpush
@endsection
