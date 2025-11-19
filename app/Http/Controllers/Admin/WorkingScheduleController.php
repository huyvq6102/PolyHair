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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $this->storeImage($request->file('image'));
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($schedule->image && file_exists(public_path('legacy/images/working-schedules/'.$schedule->image))) {
                @unlink(public_path('legacy/images/working-schedules/'.$schedule->image));
            }
            $validated['image'] = $this->storeImage($request->file('image'));
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
     * Store uploaded image and return file name.
     */
    protected function storeImage($image): string
    {
        $directory = public_path('legacy/images/working-schedules');
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $imageName = time().'_'.$image->getClientOriginalName();
        $image->move($directory, $imageName);

        return $imageName;
    }
}

