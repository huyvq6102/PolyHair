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
            'employee_id' => 'required|exists:employees,id',
            'work_date' => 'required|date',
            'shift_id' => 'required|exists:working_shifts,id',
            'status' => 'required|in:available,busy,off',
        ]);

        // Kiểm tra trùng lịch
        $conflict = $this->checkScheduleConflict(
            $validated['employee_id'],
            $validated['work_date'],
            $validated['shift_id']
        );

        if ($conflict) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['shift_id' => $conflict]);
        }

        WorkingSchedule::create($validated);

        return redirect()->route('admin.working-schedules.index')
            ->with('success', 'Lịch nhân viên đã được tạo thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $schedule = WorkingSchedule::with(['employee.user', 'shift'])->findOrFail($id);

        $appointments = Appointment::where('employee_id', $schedule->employee_id)
            ->whereDate('start_at', $schedule->work_date)
            ->with(['appointmentDetails.serviceVariant.service', 'user'])
            ->orderBy('start_at')
            ->get();

        return view('admin.working-schedules.show', [
            'schedule' => $schedule,
            'appointments' => $appointments,
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

        // Kiểm tra trùng lịch (loại trừ bản ghi hiện tại)
        $conflict = $this->checkScheduleConflict(
            $validated['employee_id'],
            $validated['work_date'],
            $validated['shift_id'],
            $id
        );

        if ($conflict) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['shift_id' => $conflict]);
        }

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
                $employeeName = $schedule->employee->user->name ?? 'Nhân viên';
                $shiftName = $schedule->shift->name ?? '';
                $shiftTime = $schedule->shift->display_time ?? '';
                
                return "Lịch này bị trùng với ca '{$shiftName}' ({$shiftTime}) của {$employeeName} trong cùng ngày. Vui lòng chọn ca khác hoặc ngày khác.";
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

