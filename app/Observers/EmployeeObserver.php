<?php

namespace App\Observers;

use App\Models\Employee;

class EmployeeObserver
{
    /**
     * Keep the linked user's active state in sync with the employee.
     * Deactivating an employee deactivates its user account, and vice versa.
     */
    public function updated(Employee $employee): void
    {
        if (! $employee->wasChanged('is_active')) {
            return;
        }

        $user = $employee->user;

        if ($user && $user->is_active !== $employee->is_active) {
            $user->update(['is_active' => $employee->is_active]);
        }
    }
}
