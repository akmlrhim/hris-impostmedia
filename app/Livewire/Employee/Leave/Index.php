<?php

namespace App\Livewire\Employee\Leave;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
class Index extends Component
{
    public function render(): mixed
    {
        $employee = auth()->user()?->employee;
        $year = now()->year;

        $balances = $employee
            ? LeaveBalance::with('leaveType')
                ->where('employee_id', $employee->id)
                ->where('year', $year)
                ->get()
            : collect();

        $requests = $employee
            ? LeaveRequest::with('leaveType')
                ->where('employee_id', $employee->id)
                ->latest()
                ->limit(15)
                ->get()
            : collect();

        return view('livewire.employee.leave.index', compact('balances', 'requests'));
    }
}
