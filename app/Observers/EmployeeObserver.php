<?php

namespace App\Observers;

use App\Models\Employee;

class EmployeeObserver
{
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
