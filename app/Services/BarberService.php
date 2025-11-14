<?php

namespace App\Services;

use App\Models\Employee;

/**
 * BarberService - Backward compatibility wrapper for EmployeeService
 * @deprecated Use EmployeeService instead
 */
class BarberService
{
    protected $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    /**
     * Get all barbers (employees).
     */
    public function getAll()
    {
        return $this->employeeService->getAll();
    }

    /**
     * Get one barber (employee) by id.
     */
    public function getOne($id)
    {
        return $this->employeeService->getOne($id);
    }

    /**
     * Get barber by account (deprecated - use getByUserId instead).
     */
    public function getByAccount($account)
    {
        // Try to find by user email or name
        $employee = \App\Models\Employee::with('user')
            ->whereHas('user', function($query) use ($account) {
                $query->where('email', $account)
                      ->orWhere('name', 'like', "%{$account}%");
            })
            ->first();
        
        return $employee;
    }

    /**
     * Create a new barber (employee).
     */
    public function create(array $data)
    {
        return $this->employeeService->create($data);
    }

    /**
     * Update a barber (employee).
     */
    public function update($id, array $data)
    {
        return $this->employeeService->update($id, $data);
    }

    /**
     * Delete a barber (employee).
     */
    public function delete($id)
    {
        return $this->employeeService->delete($id);
    }

    /**
     * Search barbers (employees) by name.
     */
    public function search($name)
    {
        return $this->employeeService->search($name);
    }
}

