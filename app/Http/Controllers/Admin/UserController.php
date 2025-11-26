<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $keyword = $request->get('keyword');
        $roleId = $request->get('role_id');
        $status = $request->get('status');
        
        $query = User::with('role');
        
        if ($keyword) {
            $query->where('name', 'like', '%' . $keyword . '%');
        }
        
        if ($roleId) {
            $query->where('role_id', $roleId);
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(10);
        
        $roles = Role::all();
        
        return view('admin.users.index', compact('users', 'keyword', 'roleId', 'status', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::where('name', '!=', 'Employee')->get();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'dob' => 'nullable|date',
            'role_id' => 'nullable|exists:roles,id',
            'status' => 'nullable|in:Hoạt động,Vô hiệu hóa,Cấm',
            'gender' => 'nullable|in:Nam,Nữ,Khác',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = time() . '_' . $avatar->getClientOriginalName();
            $avatar->move(public_path('legacy/images/avatars'), $avatarName);
            $validated['avatar'] = $avatarName;
        }

        // Hash password
        $validated['password'] = Hash::make($validated['password']);
        
        // Set default status if not provided
        if (!isset($validated['status'])) {
            $validated['status'] = 'Hoạt động';
        }

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Người dùng đã được tạo thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::with('role')->findOrFail($id);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $roles = Role::where('name', '!=', 'Employee')->get();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'dob' => 'nullable|date',
            'role_id' => 'nullable|exists:roles,id',
            'status' => 'nullable|in:Hoạt động,Vô hiệu hóa,Cấm',
            'gender' => 'nullable|in:Nam,Nữ,Khác',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($user->avatar && file_exists(public_path('legacy/images/avatars/' . $user->avatar))) {
                unlink(public_path('legacy/images/avatars/' . $user->avatar));
            }
            
            $avatar = $request->file('avatar');
            $avatarName = time() . '_' . $avatar->getClientOriginalName();
            $avatar->move(public_path('legacy/images/avatars'), $avatarName);
            $validated['avatar'] = $avatarName;
        } else {
            // Keep existing avatar if not uploading new one
            unset($validated['avatar']);
        }

        // Hash password only if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Người dùng đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        
        // Check if user has associated employee record and soft delete it
        // This ensures that when a user with employee role is deleted,
        // the corresponding employee record is also moved to trash
        $employee = Employee::where('user_id', $user->id)->first();
        if ($employee) {
            $employee->delete(); // Soft delete employee
        }
        
        // Soft delete user
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Người dùng đã được chuyển vào thùng rác thành công!');
    }

    /**
     * Display trashed users.
     */
    public function trash(Request $request)
    {
        $users = User::onlyTrashed()
            ->with('role')
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);
        
        $roles = Role::all();
        
        return view('admin.users.trash', compact('users', 'roles'));
    }

    /**
     * Restore a trashed user.
     */
    public function restore(string $id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();
        
        // Also restore associated employee if exists
        $employee = Employee::onlyTrashed()->where('user_id', $user->id)->first();
        if ($employee) {
            $employee->restore();
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Người dùng đã được khôi phục thành công!');
    }

    /**
     * Permanently delete a user.
     */
    public function forceDelete(string $id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $userId = $user->id;
        
        // Permanently delete associated employee if exists
        $employee = Employee::onlyTrashed()->where('user_id', $userId)->first();
        if ($employee) {
            $employee->forceDelete();
        }
        
        // Delete user avatar if exists
        if ($user->avatar && file_exists(public_path('legacy/images/avatars/' . $user->avatar))) {
            unlink(public_path('legacy/images/avatars/' . $user->avatar));
        }
        
        // Permanently delete user
        $user->forceDelete();

        return redirect()->route('admin.users.trash')
            ->with('success', 'Người dùng đã được xóa vĩnh viễn thành công!');
    }
}
