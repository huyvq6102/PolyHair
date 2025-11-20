<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Skill;
use Illuminate\Http\Request;

class EmployeeSkillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Employee::with(['user', 'skills']);

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->whereHas('user', function ($q) use ($keyword) {
                $q->where('name', 'like', '%'.$keyword.'%');
            });
        }

        if ($request->filled('skill_id')) {
            $skillId = $request->skill_id;
            $query->whereHas('skills', function ($q) use ($skillId) {
                $q->where('skills.id', $skillId);
            });
        }

        $employees = $query->orderByDesc('id')
            ->paginate(15)
            ->appends($request->query());

        $skills = Skill::orderBy('name')->get();

        return view('admin.employee-skills.index', [
            'employees' => $employees,
            'skills' => $skills,
            'filters' => $request->only('keyword', 'skill_id'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $employee = Employee::with(['user', 'skills'])->findOrFail($id);
        $skills = Skill::orderBy('name')->get();

        return view('admin.employee-skills.edit', [
            'employee' => $employee,
            'skills' => $skills,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'skills' => 'nullable|array',
            'skills.*' => 'exists:skills,id',
            'new_skills' => 'nullable|string|max:500',
        ]);

        $skillIds = $validated['skills'] ?? [];

        if (!empty($validated['new_skills'])) {
            $names = preg_split('/[,;\n]+/', $validated['new_skills']);
            foreach ($names as $name) {
                $name = trim($name);
                if ($name === '') {
                    continue;
                }
                $skill = Skill::firstOrCreate(['name' => $name]);
                $skillIds[] = $skill->id;
            }
        }

        $skillIds = array_unique(array_filter($skillIds));

        $employee->skills()->sync($skillIds);

        return redirect()->route('admin.skills.index', ['tab' => 'employees'])
            ->with('success', 'Chuyên môn của nhân viên đã được cập nhật!');
    }
}
