<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;

class EmployeeService
{
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
            $employee->user->update($data['user_data']);
            unset($data['user_data']);
        }
        
        $employee->update($data);
        return $employee->load('user');
    }

    /**
     * Delete an employee.
     */
    public function delete($id)
    {
        $employee = Employee::findOrFail($id);
        return $employee->delete();
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

