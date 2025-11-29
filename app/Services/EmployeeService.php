<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;

class EmployeeService
{
    /**
     * Map employee status to user status.
     * 
     * @param string $employeeStatus
     * @return string
     */
    private function mapEmployeeStatusToUserStatus($employeeStatus)
    {
        $statusMap = [
            'Đang làm việc' => 'Hoạt động',
            'Nghỉ phép' => 'Hoạt động', // Nhân viên nghỉ phép vẫn là hoạt động
            'Vô hiệu hóa' => 'Vô hiệu hóa',
        ];

        return $statusMap[$employeeStatus] ?? 'Hoạt động';
    }

    /**
     * Get all employees with user.
     */
    public function getAll()
    {
        return Employee::with(['user.role'])->orderBy('id', 'desc')->get();
    }

    /**
     * Get one employee by id.
     */
    public function getOne($id)
    {
        return Employee::with('user')->findOrFail($id);
    }

    /**
     * Get employee by user id.
     */
    public function getByUserId($userId)
    {
        return Employee::with('user')->where('user_id', $userId)->first();
    }

    /**
     * Create a new employee with user.
     */
    public function create(array $data)
    {
        // Create user first if not exists
        if (isset($data['user_data'])) {
            // Map employee status to user status
            if (isset($data['status'])) {
                $data['user_data']['status'] = $this->mapEmployeeStatusToUserStatus($data['status']);
            } else {
                $data['user_data']['status'] = 'Hoạt động'; // Default
            }
            
            $user = User::create($data['user_data']);
            $data['user_id'] = $user->id;
            unset($data['user_data']);
        }

        return Employee::create($data);
    }

    /**
     * Update an employee.
     */
    public function update($id, array $data)
    {
        $employee = Employee::findOrFail($id);
        
        // Update user if user_data provided
        if (isset($data['user_data']) && $employee->user_id) {
            // Map employee status to user status if status is being updated
            if (isset($data['status'])) {
                $data['user_data']['status'] = $this->mapEmployeeStatusToUserStatus($data['status']);
            }
            
            $employee->user->update($data['user_data']);
            unset($data['user_data']);
        } elseif (isset($data['status']) && $employee->user_id) {
            // If only status is updated, also update user status
            $employee->user->update([
                'status' => $this->mapEmployeeStatusToUserStatus($data['status'])
            ]);
        }
        
        $employee->update($data);
        return $employee->load('user');
    }

    /**
     * Soft delete an employee (move to trash).
     */
    public function delete($id)
    {
        $employee = Employee::findOrFail($id);
        // Soft delete employee (move to trash)
        $employee->delete();
        
        // Also soft delete associated user if exists
        if ($employee->user_id) {
            $user = User::find($employee->user_id);
            if ($user) {
                $user->delete(); // Soft delete user
            }
        }
        
        return true;
    }

    /**
     * Get all trashed employees.
     */
    public function getTrashed($perPage = 10)
    {
        return Employee::onlyTrashed()
            ->with('user') // user relationship đã được sửa để load cả user đã xóa
            ->orderBy('deleted_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Restore a trashed employee.
     */
    public function restore($id)
    {
        $employee = Employee::onlyTrashed()->findOrFail($id);
        $userId = $employee->user_id;
        
        // Restore employee
        $employee->restore();
        
        // Also restore associated user if exists
        if ($userId) {
            $user = User::onlyTrashed()->find($userId);
            if ($user) {
                $user->restore();
            }
        }
        
        // Reload employee with user relationship
        return Employee::with('user')->findOrFail($employee->id);
    }

    /**
     * Permanently delete an employee.
     */
    public function forceDelete($id)
    {
        $employee = Employee::onlyTrashed()->findOrFail($id);
        $userId = $employee->user_id;
        
        // Permanently delete employee
        $employee->forceDelete();
        
        // Also permanently delete associated user if exists
        if ($userId) {
            $user = User::onlyTrashed()->find($userId);
            if ($user) {
                // Delete user avatar if exists
                if ($user->avatar && file_exists(public_path('legacy/images/avatars/' . $user->avatar))) {
                    unlink(public_path('legacy/images/avatars/' . $user->avatar));
                }
                $user->forceDelete();
            }
        }
        
        return true;
    }

    /**
     * Search employees by name.
     */
    public function search($name)
    {
        return Employee::with('user')
            ->whereHas('user', function($query) use ($name) {
                $query->where('name', 'like', "%{$name}%");
            })
            ->get();
    }

    /**
     * Get employees by position.
     */
    public function getByPosition($position)
    {
        return Employee::with('user')
            ->where('position', $position)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get employees by status.
     */
    public function getByStatus($status)
    {
        return Employee::with('user')
            ->where('status', $status)
            ->orderBy('id', 'desc')
            ->get();
    }
}

