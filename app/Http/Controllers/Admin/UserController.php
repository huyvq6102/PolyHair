<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        
        // Delete avatar if exists
        if ($user->avatar && file_exists(public_path('legacy/images/avatars/' . $user->avatar))) {
            unlink(public_path('legacy/images/avatars/' . $user->avatar));
        }
        
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Người dùng đã được xóa thành công!');
    }
}
