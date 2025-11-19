<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EmployeeService;
use App\Models\Role;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    protected $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $keyword = $request->get('keyword');
        $position = $request->get('position');
        $status = $request->get('status');
        
        if ($keyword) {
            $employees = $this->employeeService->search($keyword);
        } elseif ($position) {
            $employees = $this->employeeService->getByPosition($position);
        } elseif ($status) {
            $employees = $this->employeeService->getByStatus($status);
        } else {
            $employees = $this->employeeService->getAll();
        }

        return view('admin.employees.index', compact('employees', 'keyword', 'position', 'status'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.employees.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'position' => 'required|in:Stylist,Barber,Shampooer,Receptionist',
            'level' => 'nullable|in:Intern,Junior,Middle,Senior',
            'experience_years' => 'nullable|integer|min:0|max:50',
            'bio' => 'nullable|string',
            'status' => 'nullable|in:Đang làm việc,Nghỉ phép,Vô hiệu hóa',
        ]);

        // Get Nhân Viên role
        $employeeRole = Role::where('name', 'Nhân Viên')
            ->orWhere('name', 'Nhân viên')
            ->orWhere('name', 'nhân viên')
            ->first();
        
        if (!$employeeRole) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Không tìm thấy role "Nhân viên". Vui lòng tạo role này trước.');
        }

        // Prepare user data
        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'role_id' => $employeeRole->id,
        ];

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = time() . '_' . $avatar->getClientOriginalName();
            $avatar->move(public_path('legacy/images/avatars'), $avatarName);
            $validated['avatar'] = $avatarName;
        }

        // Prepare employee data
        $employeeData = [
            'avatar' => $validated['avatar'] ?? null,
            'position' => $validated['position'],
            'level' => $validated['level'] ?? null,
            'experience_years' => $validated['experience_years'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'status' => $validated['status'] ?? 'Đang làm việc',
            'user_data' => $userData,
        ];

        $this->employeeService->create($employeeData);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Nhân viên đã được tạo thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $employee = $this->employeeService->getOne($id);
        return view('admin.employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $employee = $this->employeeService->getOne($id);
        return view('admin.employees.edit', compact('employee'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $employee = $this->employeeService->getOne($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $employee->user_id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'position' => 'required|in:Stylist,Barber,Shampooer,Receptionist',
            'level' => 'nullable|in:Intern,Junior,Middle,Senior',
            'experience_years' => 'nullable|integer|min:0|max:50',
            'bio' => 'nullable|string',
            'status' => 'nullable|in:Đang làm việc,Nghỉ phép,Vô hiệu hóa',
        ]);

        // Get Nhân Viên role
        $employeeRole = Role::where('name', 'Nhân Viên')
            ->orWhere('name', 'Nhân viên')
            ->orWhere('name', 'nhân viên')
            ->first();
        
        if (!$employeeRole) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Không tìm thấy role "Nhân viên". Vui lòng tạo role này trước.');
        }

        // Prepare user data
        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role_id' => $employeeRole->id,
        ];

        if (!empty($validated['password'])) {
            $userData['password'] = $validated['password'];
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($employee->avatar && file_exists(public_path('legacy/images/avatars/' . $employee->avatar))) {
                unlink(public_path('legacy/images/avatars/' . $employee->avatar));
            }
            
            $avatar = $request->file('avatar');
            $avatarName = time() . '_' . $avatar->getClientOriginalName();
            $avatar->move(public_path('legacy/images/avatars'), $avatarName);
            $validated['avatar'] = $avatarName;
        }

        // Prepare employee data
        $employeeData = [
            'avatar' => $validated['avatar'] ?? $employee->avatar,
            'position' => $validated['position'],
            'level' => $validated['level'] ?? null,
            'experience_years' => $validated['experience_years'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'status' => $validated['status'] ?? $employee->status,
            'user_data' => $userData,
        ];

        $this->employeeService->update($id, $employeeData);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Nhân viên đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->employeeService->delete($id);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Nhân viên đã được xóa thành công!');
    }
}

