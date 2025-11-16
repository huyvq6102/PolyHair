<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkingSchedule;
use App\Models\Employee;
use App\Models\WorkingShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkingScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = WorkingSchedule::with(['employee.user', 'shift']);

        // Filter by employee name
        if ($request->filled('employee_name')) {
            $query->whereHas('employee.user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->employee_name . '%');
            });
        }

        // Filter by work date
        if ($request->filled('work_date')) {
            $query->where('work_date', $request->work_date);
        }

        $schedules = $query->orderBy('work_date', 'desc')
                          ->orderBy('shift_id', 'asc')
                          ->paginate(15);

        return view('admin.working-schedules.index', compact('schedules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::with('user')->get();
        $shifts = WorkingShift::all();
        return view('admin.working-schedules.create', compact('employees', 'shifts'));
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
        return view('admin.working-schedules.show', compact('schedule'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $schedule = WorkingSchedule::with(['employee.user', 'shift'])->findOrFail($id);
        $employees = Employee::with('user')->get();
        $shifts = WorkingShift::all();
        return view('admin.working-schedules.edit', compact('schedule', 'employees', 'shifts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $schedule = WorkingSchedule::findOrFail($id);

        $validated = $request->validate([
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

        return view('admin.working-schedules.trash', compact('schedules'));
    }

    /**
     * Restore a trashed resource.
     */
    public function restore(string $id)
    {
        $schedule = WorkingSchedule::onlyTrashed()->findOrFail($id);
        $schedule->restore();

        return redirect()->route('admin.working-schedules.trash')
            ->with('success', 'Lịch nhân viên đã được phục hồi thành công!');
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
}

