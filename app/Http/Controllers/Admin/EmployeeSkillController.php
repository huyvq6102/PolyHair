<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Service;
use Illuminate\Http\Request;

class EmployeeSkillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Employee::with(['user', 'services:id,name']);

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->whereHas('user', function ($q) use ($keyword) {
                $q->where('name', 'like', '%'.$keyword.'%');
            });
        }

        if ($request->filled('service_id')) {
            $serviceId = $request->service_id;
            $query->whereHas('services', function ($q) use ($serviceId) {
                $q->where('services.id', $serviceId);
            });
        }

        $employees = $query->orderByDesc('id')
            ->paginate(15)
            ->appends($request->query());

        $services = Service::select('id', 'name')->orderBy('name')->get();

        return view('admin.employee-skills.index', [
            'employees' => $employees,
            'services' => $services,
            'filters' => $request->only('keyword', 'service_id'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $employee = Employee::with(['user', 'services:id,name'])->findOrFail($id);
        $services = Service::select('id', 'name')->orderBy('name')->get();

        return view('admin.employee-skills.edit', [
            'employee' => $employee,
            'services' => $services,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'services' => 'nullable|array',
            'services.*' => 'exists:services,id',
        ]);

        $serviceIds = $validated['services'] ?? [];
        $serviceIds = array_unique(array_filter($serviceIds));

        $employee->services()->sync($serviceIds);

        return redirect()->route('admin.employee-skills.index')
            ->with('success', 'Chuyên môn của nhân viên đã được cập nhật!');
    }
}
