<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Keep the linked employee's active state in sync with the user.
     * Deactivating a user deactivates its employee, and vice versa.
     */
    public function updated(User $user): void
    {
        if (! $user->wasChanged('is_active')) {
            return;
        }

        $employee = $user->employee;

        if ($employee && $employee->is_active !== $user->is_active) {
            $employee->update(['is_active' => $user->is_active]);
        }
    }
}
