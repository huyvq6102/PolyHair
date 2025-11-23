<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkingSchedule;
use App\Models\Employee;
use App\Models\WorkingShift;
use App\Models\Appointment;
use Illuminate\Http\Request;

class WorkingScheduleController extends Controller
{
    protected array $statusOptions = [
        'available' => 'Rảnh',
        'busy' => 'Bận',
        'off' => 'Nghỉ',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = WorkingSchedule::with(['employee.user', 'shift']);

        if ($request->filled('employee_name')) {
            $query->whereHas('employee.user', function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->employee_name.'%');
            });
        }

        if ($request->filled('work_date')) {
            $query->whereDate('work_date', $request->work_date);
        }

        $schedules = $query->orderBy('work_date', 'desc')
            ->orderBy('shift_id')
            ->paginate(15)
            ->appends($request->query());

        return view('admin.working-schedules.index', [
            'schedules' => $schedules,
            'filters' => $request->only('employee_name', 'work_date'),
            'statusOptions' => $this->statusOptions,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::with('user')->orderBy('id', 'desc')->get();
        $shifts = WorkingShift::orderBy('start_time')->get();

        return view('admin.working-schedules.create', [
            'employees' => $employees,
            'shifts' => $shifts,
            'statusOptions' => $this->statusOptions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'required|exists:employees,id',
            'work_date' => 'required|date',
            'shift_ids' => 'required|array|min:1',
            'shift_ids.*' => 'required|exists:working_shifts,id',
            'status' => 'required|in:available,busy,off',
        ]);

        $employeeIds = $validated['employee_ids'];
        $shiftIds = $validated['shift_ids'];
        $workDate = $validated['work_date'];
        $status = $validated['status'];

        $createdCount = 0;
        $skippedCount = 0;
        $conflicts = [];

        // Tạo lịch cho tất cả các tổ hợp nhân viên x ca làm việc
        foreach ($employeeIds as $employeeId) {
            foreach ($shiftIds as $shiftId) {
                // Kiểm tra trùng lịch
                $conflict = $this->checkScheduleConflict(
                    $employeeId,
                    $workDate,
                    $shiftId
                );

                if ($conflict) {
                    $skippedCount++;
                    $employee = Employee::with('user')->find($employeeId);
                    $shift = WorkingShift::find($shiftId);
                    $employeeName = $employee->user->name ?? "ID: {$employeeId}";
                    $shiftName = $shift->name ?? "ID: {$shiftId}";
                    $conflicts[] = "{$employeeName} - Ca {$shiftName}: " . $conflict;
                    continue;
                }

                // Tạo lịch
                WorkingSchedule::create([
                    'employee_id' => $employeeId,
                    'work_date' => $workDate,
                    'shift_id' => $shiftId,
                    'status' => $status,
                ]);

                $createdCount++;
            }
        }

        // Thông báo kết quả
        $message = '';
        if ($createdCount > 0) {
            $message = "Đã tạo thành công {$createdCount} lịch làm việc!";
        }
        if ($skippedCount > 0) {
            $message .= ($message ? ' ' : '') . "Bỏ qua {$skippedCount} lịch do trùng.";
        }

        if (empty($conflicts)) {
            return redirect()->route('admin.working-schedules.index')
                ->with('success', $message ?: 'Lịch nhân viên đã được tạo thành công!');
        } else {
            return redirect()->back()
                ->withInput()
                ->with('warning', $message)
                ->with('conflicts', $conflicts);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $schedule = WorkingSchedule::with(['employee.user', 'shift'])->findOrFail($id);
        $workDate = $schedule->work_date;

        // Lấy tất cả lịch của ngày đó, nhóm theo ca làm việc
        $schedulesByShift = WorkingSchedule::whereDate('work_date', $workDate)
            ->with(['employee.user', 'shift'])
            ->get()
            ->groupBy('shift_id')
            ->map(function ($schedules) {
                return $schedules->sortBy(function ($schedule) {
                    return $schedule->employee->user->name ?? '';
                });
            });

        // Lấy danh sách ca làm việc đã sắp xếp theo thời gian
        $shifts = WorkingShift::orderBy('start_time')->get();

        return view('admin.working-schedules.show', [
            'workDate' => $workDate,
            'schedulesByShift' => $schedulesByShift,
            'shifts' => $shifts,
            'statusOptions' => $this->statusOptions,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $schedule = WorkingSchedule::with(['employee.user', 'shift'])->findOrFail($id);
        $employees = Employee::with('user')->orderBy('id', 'desc')->get();
        $shifts = WorkingShift::orderBy('start_time')->get();

        return view('admin.working-schedules.edit', [
            'schedule' => $schedule,
            'employees' => $employees,
            'shifts' => $shifts,
            'statusOptions' => $this->statusOptions,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $schedule = WorkingSchedule::findOrFail($id);

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'work_date' => 'required|date',
            'shift_id' => 'required|exists:working_shifts,id',
            'status' => 'required|in:available,busy,off',
        ]);

        $schedule->update($validated);

        return redirect()->route('admin.working-schedules.index')
            ->with('success', 'Lịch nhân viên đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $schedule = WorkingSchedule::findOrFail($id);
        $schedule->delete();

        return redirect()->route('admin.working-schedules.index')
            ->with('success', 'Lịch nhân viên đã được chuyển vào thùng rác!');
    }

    /**
     * Display trashed resources.
     */
    public function trash()
    {
        $schedules = WorkingSchedule::onlyTrashed()
            ->with(['employee.user', 'shift'])
            ->orderBy('deleted_at', 'desc')
            ->paginate(15);

        return view('admin.working-schedules.trash', [
            'schedules' => $schedules,
            'statusOptions' => $this->statusOptions,
        ]);
    }

    /**
     * Restore a trashed resource.
     */
    public function restore(string $id)
    {
        $schedule = WorkingSchedule::onlyTrashed()->findOrFail($id);
        $schedule->restore();

        return redirect()->route('admin.working-schedules.trash')
            ->with('success', 'Lịch nhân viên đã được phục hồi!');
    }

    /**
     * Permanently delete a resource.
     */
    public function forceDelete(string $id)
    {
        $schedule = WorkingSchedule::onlyTrashed()->findOrFail($id);
        $schedule->forceDelete();

        return redirect()->route('admin.working-schedules.trash')
            ->with('success', 'Lịch nhân viên đã được xóa vĩnh viễn!');
    }

    /**
     * Kiểm tra xem có lịch trùng với lịch hiện tại không
     * 
     * @param int $employeeId
     * @param string $workDate
     * @param int $shiftId
     * @param int|null $excludeId ID của lịch cần loại trừ (khi update)
     * @return string|null Thông báo lỗi nếu có trùng, null nếu không trùng
     */
    protected function checkScheduleConflict(int $employeeId, string $workDate, int $shiftId, ?int $excludeId = null): ?string
    {
        // Lấy thông tin ca làm việc mới
        $newShift = WorkingShift::findOrFail($shiftId);
        $newStartTime = $this->parseTime($newShift->start_time);
        $newEndTime = $this->parseTime($newShift->end_time);

        // Tìm tất cả lịch của nhân viên trong cùng ngày
        $query = WorkingSchedule::where('employee_id', $employeeId)
            ->whereDate('work_date', $workDate)
            ->with(['shift', 'employee.user']);

        // Loại trừ lịch hiện tại khi update
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $existingSchedules = $query->get();

        // Kiểm tra từng lịch có trùng không
        foreach ($existingSchedules as $schedule) {
            if (!$schedule->shift) {
                continue;
            }

            $existingStartTime = $this->parseTime($schedule->shift->start_time);
            $existingEndTime = $this->parseTime($schedule->shift->end_time);

            // Kiểm tra trùng lịch: hai khoảng thời gian trùng nhau nếu
            // start1 < end2 AND start2 < end1
            if ($this->isTimeOverlapping($newStartTime, $newEndTime, $existingStartTime, $existingEndTime)) {
                $shiftName = $schedule->shift->name ?? '';
                $shiftTime = $schedule->shift->display_time ?? '';
                
                return "Trùng với ca '{$shiftName}' ({$shiftTime}) trong cùng ngày.";
            }
        }

        return null;
    }

    /**
     * Chuyển đổi thời gian từ nhiều định dạng sang phút (0-1439)
     * 
     * @param mixed $time
     * @return int Số phút từ đầu ngày
     */
    protected function parseTime($time): int
    {
        if ($time instanceof \DateTimeInterface) {
            $time = $time->format('H:i:s');
        }

        $time = (string) $time;
        
        // Loại bỏ phần ngày nếu có
        if (strpos($time, ' ') !== false) {
            $parts = explode(' ', $time);
            $time = end($parts);
        }

        // Lấy phần giờ:phút
        $parts = explode(':', $time);
        $hours = (int) ($parts[0] ?? 0);
        $minutes = (int) ($parts[1] ?? 0);

        return $hours * 60 + $minutes;
    }

    /**
     * Kiểm tra xem hai khoảng thời gian có trùng nhau không
     * 
     * @param int $start1 Thời gian bắt đầu của khoảng 1 (phút)
     * @param int $end1 Thời gian kết thúc của khoảng 1 (phút)
     * @param int $start2 Thời gian bắt đầu của khoảng 2 (phút)
     * @param int $end2 Thời gian kết thúc của khoảng 2 (phút)
     * @return bool
     */
    protected function isTimeOverlapping(int $start1, int $end1, int $start2, int $end2): bool
    {
        // Xử lý trường hợp ca làm việc qua đêm (end < start)
        // Ví dụ: 22:00 - 06:00
        if ($end1 < $start1) {
            $end1 += 24 * 60; // Thêm 24 giờ
        }
        if ($end2 < $start2) {
            $end2 += 24 * 60; // Thêm 24 giờ
        }

        // Hai khoảng thời gian trùng nhau nếu: start1 < end2 AND start2 < end1
        return $start1 < $end2 && $start2 < $end1;
    }
}

