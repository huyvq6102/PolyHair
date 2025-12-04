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
                    @php
                        // Nhóm nhân viên theo vị trí
                        $schedulesByPosition = $shiftSchedules->groupBy(function($schedule) {
                            return $schedule->employee->position ?? 'Other';
                        });
                        $requiredPositions = ['Stylist', 'Barber', 'Shampooer', 'Receptionist'];
                    @endphp
                    
                    @foreach($requiredPositions as $position)
                        @php
                            $positionSchedules = $schedulesByPosition->get($position, collect());
                        @endphp
                        <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <h6 class="mb-2 font-weight-bold" style="color: #4e73df; font-size: 0.95rem;">
                                <i class="fas fa-user-tag"></i> {{ $position }}
                            </h6>
                            @if($positionSchedules->isNotEmpty())
                                @foreach($positionSchedules as $schedule)
                                    @php
                                        $employee = $schedule->employee;
                                        $user = $employee->user ?? null;
                                    @endphp
                                    <div class="ml-3 mb-2">
                                        <strong style="color: #5a5c69;">{{ $user->name ?? 'N/A' }}</strong>
                                        <span class="text-muted small">
                                            • {{ $user->email ?? 'N/A' }}
                                            @if($employee->level)
                                                • Level: {{ strtolower($employee->level) }}
                                            @endif
                                            @if($employee->experience_years)
                                                • Kinh nghiệm: {{ $employee->experience_years }} năm
                                            @endif
                                        </span>
            </div>
                                @endforeach
                @else
                                <div class="ml-3 text-danger small">
                                    <i class="fas fa-exclamation-triangle"></i> Chưa có nhân viên cho vị trí này
                                </div>
                @endif
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

