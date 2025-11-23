@extends('admin.layouts.app')

@section('title', 'Chi tiết lịch làm việc')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0" style="color: #4e73df; font-weight: bold; font-size: 1.75rem;">
        Chi tiết lịch làm việc - {{ optional($workDate)->format('d/m/Y') ?? 'N/A' }}
    </h1>
    <a href="{{ route('admin.working-schedules.index') }}" class="btn btn-secondary">
        ← Quay lại
    </a>
</div>

@php
    $hasAnySchedule = false;
    foreach($shifts as $shift) {
        if($schedulesByShift->get($shift->id, collect())->isNotEmpty()) {
            $hasAnySchedule = true;
            break;
        }
    }
@endphp

@if($hasAnySchedule)
    @foreach($shifts as $shift)
        @php
            $shiftSchedules = $schedulesByShift->get($shift->id, collect());
        @endphp
        
        @if($shiftSchedules->isNotEmpty())
            <div class="card shadow mb-4">
                <div class="card-header py-3" style="background-color: #4e73df; color: white;">
                    <h6 class="m-0 font-weight-bold" style="font-size: 1rem;">
                        {{ $shift->name }} ({{ $shift->formatted_start_time ?? '--:--' }} - {{ $shift->formatted_end_time ?? '--:--' }})
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($shiftSchedules as $schedule)
                        @php
                            $employee = $schedule->employee;
                            $user = $employee->user ?? null;
                            $status = $schedule->status;
                            $statusLabel = $statusOptions[$status] ?? ucfirst($status ?? 'N/A');
                        @endphp
                        <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 font-weight-bold" style="color: #5a5c69; font-size: 1rem;">
                                        {{ $user->name ?? 'N/A' }}
                                    </h6>
                                    <p class="mb-1 text-muted small" style="font-size: 0.875rem;">
                                        {{ $user->email ?? 'N/A' }}
                                    </p>
                                    <p class="mb-0 text-muted small" style="font-size: 0.875rem;">
                                        Trạng thái: <span class="font-weight-normal">{{ $statusLabel }}</span>
                                        @if($employee->level)
                                            • Level: <span class="font-weight-normal">{{ strtolower($employee->level) }}</span>
                                        @endif
                                        @if($employee->experience_years)
                                            • Kinh nghiệm: <span class="font-weight-normal">{{ $employee->experience_years }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach
@else
    <div class="card shadow mb-4">
        <div class="card-body text-center py-5">
            <p class="text-muted mb-0">Không có lịch làm việc nào trong ngày này</p>
        </div>
    </div>
@endif
@endsection

