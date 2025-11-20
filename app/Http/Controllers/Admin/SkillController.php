<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Models\Employee;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Tab 1: Quản lý chuyên môn
        $skillQuery = Skill::query();
        if ($request->filled('keyword')) {
            $skillQuery->where('name', 'like', '%'.$request->keyword.'%');
        }
        $skills = $skillQuery->orderBy('id', 'desc')->paginate(15, ['*'], 'skills_page')->appends($request->except('skills_page'));

        // Tab 2: Chuyên môn nhân viên
        $employeeQuery = Employee::with(['user', 'skills']);
        if ($request->filled('employee_keyword')) {
            $keyword = $request->employee_keyword;
            $employeeQuery->whereHas('user', function ($q) use ($keyword) {
                $q->where('name', 'like', '%'.$keyword.'%');
            });
        }
        if ($request->filled('employee_skill_id')) {
            $skillId = $request->employee_skill_id;
            $employeeQuery->whereHas('skills', function ($q) use ($skillId) {
                $q->where('skills.id', $skillId);
            });
        }
        $employees = $employeeQuery->orderByDesc('id')->paginate(15, ['*'], 'employees_page')->appends($request->except('employees_page'));
        $allSkills = Skill::orderBy('name')->get();

        // Xác định tab active
        $activeTab = $request->get('tab', 'skills');

        return view('admin.skills.index', [
            'skills' => $skills,
            'employees' => $employees,
            'allSkills' => $allSkills,
            'keyword' => $request->keyword,
            'employee_keyword' => $request->employee_keyword,
            'employee_skill_id' => $request->employee_skill_id,
            'activeTab' => $activeTab,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.skills.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:skills,name',
            'description' => 'nullable|string',
        ]);

        Skill::create($validated);

        return redirect()->route('admin.skills.index', ['tab' => 'skills'])
            ->with('success', 'Chuyên môn đã được tạo thành công!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $skill = Skill::findOrFail($id);

        return view('admin.skills.edit', [
            'skill' => $skill,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $skill = Skill::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:skills,name,'.$skill->id,
            'description' => 'nullable|string',
        ]);

        $skill->update($validated);

        return redirect()->route('admin.skills.index', ['tab' => 'skills'])
            ->with('success', 'Chuyên môn đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $skill = Skill::findOrFail($id);
        $skill->delete();

        return redirect()->route('admin.skills.index', ['tab' => 'skills'])
            ->with('success', 'Chuyên môn đã được xóa thành công!');
    }
}
