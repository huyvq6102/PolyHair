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
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = WorkingSchedule::with(['employee.user', 'shift']);

        if ($request->filled('employee_name')) {
            $query->whereHas('employee.user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->employee_name . '%');
            });
        }

        if ($request->filled('work_date')) {
            $query->whereDate('work_date', $request->work_date);
        }

        $allSchedules = $query->orderBy('work_date', 'desc')
            ->orderBy('shift_id')
            ->get();

        // Nhóm lịch theo ngày và ca
        $groupedSchedules = $allSchedules->groupBy(function ($schedule) {
            return $schedule->work_date->format('Y-m-d') . '_' . $schedule->shift_id;
        })->map(function ($schedules) {
            return [
                'work_date' => $schedules->first()->work_date,
                'shift' => $schedules->first()->shift,
                'schedules' => $schedules->groupBy(function ($schedule) {
                    return $schedule->employee->position ?? 'Other';
                }),
            ];
        })->sortBy(function ($group) {
            // Sắp xếp với thứ 2 là đầu tiên
            $workDate = $group['work_date'];
            if ($workDate instanceof \Carbon\Carbon) {
                $carbon = $workDate;
            } elseif ($workDate instanceof \DateTime) {
                $carbon = \Carbon\Carbon::instance($workDate);
            } else {
                $carbon = \Carbon\Carbon::parse($workDate);
            }
            // Chuyển đổi dayOfWeek: 0 (CN) -> 7, 1 (T2) -> 1, 2 (T3) -> 2, ...
            $dayOfWeek = $carbon->dayOfWeek;
            $adjustedDay = $dayOfWeek == 0 ? 7 : $dayOfWeek;
            // Sắp xếp theo năm-tuần-thứ để nhóm theo tuần, sau đó theo thứ trong tuần (T2 đầu tiên)
            $yearWeek = $carbon->format('Y') . '-' . str_pad($carbon->week, 2, '0', STR_PAD_LEFT);
            return $yearWeek . '-' . str_pad($adjustedDay, 2, '0', STR_PAD_LEFT) . '-' . $carbon->format('Y-m-d');
        })->values()->reverse(); // Reverse để mới nhất lên đầu

        // Phân trang thủ công
        $perPage = 15;
        $currentPage = (int) $request->get('page', 1);
        $currentItems = $groupedSchedules->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $total = $groupedSchedules->count();

        // Tạo paginator với query string đầy đủ
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => 'page',
            ]
        );

        // Set paginator path để giữ query string
        $paginator->setPath($request->url());

        return view('admin.working-schedules.index', [
            'groupedSchedules' => $paginator,
            'filters' => $request->only('employee_name', 'work_date'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Lấy nhân viên theo từng vị trí
        $stylists = Employee::with('user')->where('position', 'Stylist')->orderBy('id', 'desc')->get();
        $barbers = Employee::with('user')->where('position', 'Barber')->orderBy('id', 'desc')->get();
        $shampooers = Employee::with('user')->where('position', 'Shampooer')->orderBy('id', 'desc')->get();
        $receptionists = Employee::with('user')->where('position', 'Receptionist')->orderBy('id', 'desc')->get();

        $shifts = WorkingShift::where('name', '!=', 'Ca cả ngày')
            ->orderBy('start_time')
            ->get();

        return view('admin.working-schedules.create', [
            'stylists' => $stylists,
            'barbers' => $barbers,
            'shampooers' => $shampooers,
            'receptionists' => $receptionists,
            'shifts' => $shifts,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'schedule_type' => 'required|in:day,week',
            'stylist_ids' => 'required|array|min:1',
            'stylist_ids.*' => 'required|exists:employees,id',
            'barber_ids' => 'required|array|min:1',
            'barber_ids.*' => 'required|exists:employees,id',
            'shampooer_ids' => 'required|array|min:1',
            'shampooer_ids.*' => 'required|exists:employees,id',
            'receptionist_ids' => 'required|array|min:1',
            'receptionist_ids.*' => 'required|exists:employees,id',
            'work_date' => 'required_if:schedule_type,day|nullable|date|after_or_equal:today',
            'week_start_date' => 'required_if:schedule_type,week|nullable|date|after_or_equal:today',
            'shift_ids' => 'required|array|min:1',
            'shift_ids.*' => 'required|exists:working_shifts,id',
        ], [
            'work_date.after_or_equal' => 'Ngày làm việc không được là ngày trong quá khứ.',
            'week_start_date.after_or_equal' => 'Ngày bắt đầu tuần không được là ngày trong quá khứ.',
        ]);

        // Kiểm tra nhân viên có đúng vị trí không
        $stylistIds = $validated['stylist_ids'];
        $barberIds = $validated['barber_ids'];
        $shampooerIds = $validated['shampooer_ids'];
        $receptionistIds = $validated['receptionist_ids'];

        // Kiểm tra Stylist
        $stylists = Employee::whereIn('id', $stylistIds)->get();
        foreach ($stylists as $stylist) {
            if ($stylist->position !== 'Stylist') {
                $stylistName = $stylist->user->name ?? 'N/A';
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Nhân viên '{$stylistName}' không đúng vị trí Stylist.");
            }
        }

        // Kiểm tra Barber
        $barbers = Employee::whereIn('id', $barberIds)->get();
        foreach ($barbers as $barber) {
            if ($barber->position !== 'Barber') {
                $barberName = $barber->user->name ?? 'N/A';
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Nhân viên '{$barberName}' không đúng vị trí Barber.");
            }
        }

        // Kiểm tra Shampooer
        $shampooers = Employee::whereIn('id', $shampooerIds)->get();
        foreach ($shampooers as $shampooer) {
            if ($shampooer->position !== 'Shampooer') {
                $shampooerName = $shampooer->user->name ?? 'N/A';
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Nhân viên '{$shampooerName}' không đúng vị trí Shampooer.");
            }
        }

        // Kiểm tra Receptionist
        $receptionists = Employee::whereIn('id', $receptionistIds)->get();
        foreach ($receptionists as $receptionist) {
            if ($receptionist->position !== 'Receptionist') {
                $receptionistName = $receptionist->user->name ?? 'N/A';
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Nhân viên '{$receptionistName}' không đúng vị trí Receptionist.");
            }
        }

        // Gộp tất cả nhân viên vào một mảng
        $employeeIds = array_merge($stylistIds, $barberIds, $shampooerIds, $receptionistIds);
        $shiftIds = $validated['shift_ids'];
        $scheduleType = $validated['schedule_type'];

        // Xác định danh sách ngày cần tạo lịch
        $workDates = [];
        if ($scheduleType === 'week') {
            // Tạo lịch cho cả tuần (7 ngày từ thứ 2 đến chủ nhật)
            $startDate = \Carbon\Carbon::parse($validated['week_start_date']);
            // Tìm thứ 2 của tuần
            $monday = $startDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
            for ($i = 0; $i < 7; $i++) {
                $workDates[] = $monday->copy()->addDays($i)->format('Y-m-d');
            }
        } else {
            // Chỉ tạo lịch cho 1 ngày
            $workDates[] = $validated['work_date'];
        }

        $createdCount = 0;
        $skippedCount = 0;
        $conflicts = [];

        // Tạo lịch cho tất cả các tổ hợp: ngày x ca x nhân viên
        // Mỗi ca sẽ có đủ 4 nhân viên (Stylist, Barber, Shampooer, Receptionist)
        foreach ($workDates as $workDate) {
            foreach ($shiftIds as $shiftId) {
                foreach ($employeeIds as $employeeId) {
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
                        $dateLabel = \Carbon\Carbon::parse($workDate)->format('d/m/Y');
                        $conflicts[] = "{$employeeName} ({$employee->position}) - {$dateLabel} - Ca {$shiftName}: " . $conflict;
                        continue;
                    }

                    // Tạo lịch
                    WorkingSchedule::create([
                        'employee_id' => $employeeId,
                        'work_date' => $workDate,
                        'shift_id' => $shiftId,
                    ]);

                    $createdCount++;
                }
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

        // Check permission if user is employee
        $currentUser = auth()->user();
        if (!$currentUser) {
            abort(401, 'Unauthorized');
        }
        
        if ($currentUser->isEmployee() && !$currentUser->isAdmin()) {
            $currentEmployee = \App\Models\Employee::where('user_id', auth()->id())->first();
            if (!$currentEmployee || $schedule->employee_id !== $currentEmployee->id) {
                abort(403, 'Bạn chỉ có thể xem chi tiết lịch làm việc của chính mình.');
            }
        }
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
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $schedule = WorkingSchedule::with(['employee.user', 'shift'])->findOrFail($id);
        $employees = Employee::with('user')->orderBy('id', 'desc')->get();
        $shifts = WorkingShift::where('name', '!=', 'Ca cả ngày')
            ->orderBy('start_time')
            ->get();

        // Lấy tất cả các ca hiện tại của nhân viên trong ngày đó
        $currentShiftIds = WorkingSchedule::where('employee_id', $schedule->employee_id)
            ->whereDate('work_date', $schedule->work_date)
            ->pluck('shift_id')
            ->toArray();

        return view('admin.working-schedules.edit', [
            'schedule' => $schedule,
            'employees' => $employees,
            'shifts' => $shifts,
            'currentShiftIds' => $currentShiftIds,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $schedule = WorkingSchedule::findOrFail($id);

        $validated = $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'required|exists:employees,id',
            'work_date' => 'required|date|after_or_equal:today',
            'shift_ids' => 'required|array|min:1',
            'shift_ids.*' => 'required|exists:working_shifts,id',
        ], [
            'work_date.after_or_equal' => 'Ngày làm việc không được là ngày trong quá khứ.',
        ]);

        $employeeIds = $validated['employee_ids'];
        $workDate = $validated['work_date'];
        $shiftIds = $validated['shift_ids'];

        $createdCount = 0;
        $skippedCount = 0;
        $conflicts = [];

        // Xóa tất cả lịch cũ của các nhân viên được chọn trong ngày cũ (nếu ngày thay đổi)
        // và trong ngày mới (để tránh trùng lặp)
        $oldWorkDate = $schedule->work_date->format('Y-m-d');
        
        // Xóa lịch cũ trong ngày cũ (nếu ngày thay đổi)
        if ($oldWorkDate !== $workDate) {
            WorkingSchedule::whereDate('work_date', $oldWorkDate)
                ->whereIn('employee_id', $employeeIds)
                ->delete();
        }
        
        // Xóa tất cả lịch của các nhân viên được chọn trong ngày mới
        // (để tạo lại với các ca mới)
        WorkingSchedule::whereDate('work_date', $workDate)
            ->whereIn('employee_id', $employeeIds)
            ->delete();

        // Tạo lịch mới cho tất cả tổ hợp nhân viên x ca
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
                    $dateLabel = \Carbon\Carbon::parse($workDate)->format('d/m/Y');
                    $conflicts[] = "{$employeeName} ({$employee->position}) - {$dateLabel} - Ca {$shiftName}: " . $conflict;
                    continue;
                }

                // Tạo lịch
                WorkingSchedule::create([
                    'employee_id' => $employeeId,
                    'work_date' => $workDate,
                    'shift_id' => $shiftId,
                ]);

                $createdCount++;
            }
        }

        // Thông báo kết quả
        $message = '';
        if ($createdCount > 0) {
            $message = "Đã cập nhật thành công {$createdCount} lịch làm việc!";
        }
        if ($skippedCount > 0) {
            $message .= ($message ? ' ' : '') . "Bỏ qua {$skippedCount} lịch do trùng.";
        }

        if (empty($conflicts)) {
            return redirect()->route('admin.working-schedules.index')
                ->with('success', $message ?: 'Lịch nhân viên đã được cập nhật thành công!');
        } else {
            return redirect()->back()
                ->withInput()
                ->with('warning', $message)
                ->with('conflicts', $conflicts);
        }
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
     * Delete all working schedules.
     */
    public function deleteAll(Request $request)
    {
        $count = WorkingSchedule::count();

        if ($count === 0) {
            return redirect()->route('admin.working-schedules.index')
                ->with('info', 'Không có lịch nào để xóa!');
        }

        WorkingSchedule::query()->delete();

        return redirect()->route('admin.working-schedules.index')
            ->with('success', "Đã xóa thành công {$count} lịch làm việc vào thùng rác!");
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
     * Permanently delete all trashed working schedules.
     */
    public function deleteAllTrash(Request $request)
    {
        $count = WorkingSchedule::onlyTrashed()->count();

        if ($count === 0) {
            return redirect()->route('admin.working-schedules.trash')
                ->with('info', 'Thùng rác trống!');
        }

        WorkingSchedule::onlyTrashed()->forceDelete();

        return redirect()->route('admin.working-schedules.trash')
            ->with('success', "Đã xóa vĩnh viễn {$count} lịch làm việc! Hành động này không thể hoàn tác.");
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
