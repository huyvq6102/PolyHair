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
    <div class="card-body p-0">
        @php
            // Nhóm lại theo ngày
            $items = $groupedSchedules->items();
            $schedulesByDate = collect($items)->groupBy(function($group) {
                if (is_array($group) && isset($group['work_date'])) {
                    $workDate = $group['work_date'];
                    if ($workDate instanceof \Carbon\Carbon || $workDate instanceof \DateTime) {
                        return $workDate->format('Y-m-d');
                    }
                    if (is_string($workDate)) {
                        return \Carbon\Carbon::parse($workDate)->format('Y-m-d');
                    }
                }
                return 'unknown';
            })->sortBy(function($dateGroups, $dateKey) {
                // Sắp xếp với thứ 2 luôn lên đầu tiên trong mỗi tuần
                $firstGroup = $dateGroups->first();
                if (!is_array($firstGroup) || !isset($firstGroup['work_date'])) {
                    return '9999-99-99'; // Đẩy xuống cuối
                }
                $workDate = $firstGroup['work_date'];
                if ($workDate instanceof \Carbon\Carbon || $workDate instanceof \DateTime) {
                    $carbon = $workDate instanceof \Carbon\Carbon ? $workDate : \Carbon\Carbon::instance($workDate);
                } else {
                    $carbon = \Carbon\Carbon::parse($workDate);
                }
                // Chuyển đổi dayOfWeek: 0 (CN) -> 7, 1 (T2) -> 1, 2 (T3) -> 2, ...
                // Thứ 2 (1) sẽ có giá trị nhỏ nhất, nên sẽ lên đầu trong mỗi tuần
                $dayOfWeek = $carbon->dayOfWeek;
                $adjustedDay = $dayOfWeek == 0 ? 7 : $dayOfWeek;
                // Sắp xếp: năm-tuần-thứ (T2=1 nhỏ nhất trong tuần), để mới nhất lên đầu
                $yearWeek = $carbon->format('Y') . '-' . str_pad($carbon->week, 2, '0', STR_PAD_LEFT);
                return $yearWeek . '-' . str_pad($adjustedDay, 2, '0', STR_PAD_LEFT);
            })->reverse(); // Reverse để tuần mới nhất lên đầu, và trong mỗi tuần thì T2 trước
        @endphp

        @forelse($schedulesByDate as $dateKey => $dateGroups)
            @php
                $firstGroup = $dateGroups->first();
                if (!is_array($firstGroup) || !isset($firstGroup['work_date'])) {
                    continue;
                }
                $workDate = $firstGroup['work_date'];
                $requiredPositions = ['Stylist', 'Barber', 'Shampooer', 'Receptionist'];
            @endphp

            <!-- Header ngày -->
            <div class="schedule-date-header">
                <div class="d-flex align-items-center justify-content-between p-3 bg-light border-bottom">
                    <div>
                        <h5 class="mb-0 font-weight-bold text-primary">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            {{ $workDate->format('d/m/Y') }}
                            @php
                                $dayNames = ['Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy'];
                                $dayName = $dayNames[$workDate->dayOfWeek] ?? '';
                            @endphp
                            <span class="text-muted font-weight-normal ml-2">({{ $dayName }})</span>
                        </h5>
                    </div>
                    <div>
                        <span class="badge badge-info">{{ $dateGroups->count() }} ca</span>
                    </div>
                </div>

                <!-- Bảng lịch cho ngày này -->
        <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0 schedule-table">
                        <thead class="thead-light">
                    <tr>
                                <th style="width: 15%;" class="text-center">Ca làm việc</th>
                                <th style="width: 21.25%;" class="text-center">Stylist</th>
                                <th style="width: 21.25%;" class="text-center">Barber</th>
                                <th style="width: 21.25%;" class="text-center">Shampooer</th>
                                <th style="width: 21.25%;" class="text-center">Receptionist</th>
                    </tr>
                </thead>
                <tbody>
                            @foreach($dateGroups as $group)
                                @php
                                    if (!is_array($group) || !isset($group['shift']) || !isset($group['schedules'])) {
                                        continue;
                                    }
                                    $shift = $group['shift'];
                                    $schedulesByPosition = $group['schedules'];
                                @endphp
                                <tr class="schedule-row">
                                    <td class="align-middle text-center">
                                        <div class="shift-info">
                                            <strong class="d-block">{{ $shift->name ?? 'N/A' }}</strong>
                                            <small class="text-muted">{{ $shift->display_time ?? 'N/A' }}</small>
                                        </div>
                                        @php
                                            $firstSchedule = ($schedulesByPosition instanceof \Illuminate\Support\Collection) 
                                                ? $schedulesByPosition->flatten()->first() 
                                                : null;
                                        @endphp
                                        @if($firstSchedule)
                                            <a href="{{ route('admin.working-schedules.show', $firstSchedule->id) }}" 
                                               class="btn btn-sm btn-info btn-block mt-2" 
                                               title="Xem chi tiết">
                                                <i class="fas fa-eye"></i> Chi tiết
                                            </a>
                                        @endif
                            </td>
                                    @foreach($requiredPositions as $position)
                                        @php
                                            $positionSchedules = ($schedulesByPosition instanceof \Illuminate\Support\Collection) 
                                                ? $schedulesByPosition->get($position, collect()) 
                                                : collect();
                                            $schedule = $positionSchedules instanceof \Illuminate\Support\Collection 
                                                ? $positionSchedules->first() 
                                                : null;
                                        @endphp
                                        <td class="align-middle position-cell">
                                            @if($schedule)
                                                @php
                                                    $employee = $schedule->employee;
                                                    $user = $employee->user ?? null;
                                                @endphp
                                                <div class="employee-info">
                                                    <div class="employee-name">
                                                        <strong>{{ $user->name ?? 'N/A' }}</strong>
                                                    </div>
                                                    <div class="employee-actions">
                                                        <a href="{{ route('admin.working-schedules.edit', $schedule->id) }}" 
                                                           class="btn btn-xs btn-primary" 
                                                           title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                                        <form action="{{ route('admin.working-schedules.destroy', $schedule->id) }}" 
                                                              method="POST" 
                                                              class="d-inline" 
                                                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa lịch này?');">
                                    @csrf
                                    @method('DELETE')
                                                            <button type="submit" class="btn btn-xs btn-danger" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="text-center text-muted">
                                                    <i class="fas fa-user-slash fa-2x mb-2"></i>
                                                    <div class="small">Chưa có</div>
                                                </div>
                                            @endif
                            </td>
                                    @endforeach
                        </tr>
                            @endforeach
                </tbody>
            </table>
        </div>
            </div>
        @empty
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">Chưa có lịch nào</p>
            </div>
        @endforelse

        @if($groupedSchedules->hasPages())
            <div class="pagination-wrapper">
                {{ $groupedSchedules->links('pagination::simple-bootstrap-4') }}
            </div>
        @endif
    </div>
</div>

<style>
/* Header ngày */
.schedule-date-header {
    border-bottom: 2px solid #e3e6f0;
    margin-bottom: 0;
}

.schedule-date-header:last-child {
    border-bottom: none;
}

/* Bảng lịch */
.schedule-table {
    margin-bottom: 0;
}

.schedule-table thead th {
    background-color: #f8f9fc;
    border-bottom: 2px solid #e3e6f0;
    font-weight: 600;
    vertical-align: middle;
    padding: 12px 8px;
}

.schedule-table tbody td {
    vertical-align: middle;
    padding: 15px 10px;
}

.schedule-row {
    transition: background-color 0.2s;
}

.schedule-row:hover {
    background-color: #f8f9fc;
}

/* Thông tin ca */
.shift-info {
    padding: 5px 0;
}

.shift-info strong {
    color: #4e73df;
    font-size: 0.95rem;
}

.shift-info small {
    font-size: 0.8rem;
}

/* Ô vị trí nhân viên */
.position-cell {
    min-height: 120px;
}

.employee-info {
    text-align: center;
}

.employee-name {
    font-size: 0.9rem;
    margin-bottom: 5px;
}

.employee-name strong {
    color: #2c3e50;
    word-break: break-word;
}

.employee-status {
    margin: 8px 0;
}

.employee-actions {
    display: flex;
    justify-content: center;
    gap: 5px;
    margin-top: 8px;
}

.btn-xs {
    padding: 0.2rem 0.4rem;
    font-size: 0.75rem;
    line-height: 1.3;
    border-radius: 0.2rem;
}

/* Responsive */
@media (max-width: 768px) {
    .schedule-table {
        font-size: 0.85rem;
    }
    
    .schedule-table thead th,
    .schedule-table tbody td {
        padding: 8px 5px;
    }
    
    .employee-name {
        font-size: 0.8rem;
    }
    
    .btn-xs {
        padding: 0.15rem 0.3rem;
        font-size: 0.7rem;
    }
}

/* Màu sắc phân biệt cho các vị trí */
.position-cell:nth-child(2) {
    background-color: #fff5f5;
}

.position-cell:nth-child(3) {
    background-color: #f0f9ff;
}

.position-cell:nth-child(4) {
    background-color: #f0fff4;
}

.position-cell:nth-child(5) {
    background-color: #fffbf0;
}

.schedule-row:hover .position-cell {
    background-color: #f8f9fc;
}

/* Pagination gọn gàng */
.pagination-wrapper {
    padding: 10px 15px;
    border-top: 1px solid #e3e6f0;
    background-color: #f8f9fc;
}

.pagination-wrapper .pagination {
    margin-bottom: 0;
    justify-content: center;
}

.pagination-wrapper .pagination .page-item .page-link {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    line-height: 1.5;
}

.pagination-wrapper .pagination .page-item.disabled .page-link {
    opacity: 0.5;
    cursor: not-allowed;
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
