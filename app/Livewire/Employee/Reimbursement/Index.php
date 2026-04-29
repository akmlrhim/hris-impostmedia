<?php

namespace App\Livewire\Employee\Reimbursement;

use App\Models\Reimbursement;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
class Index extends Component
{
    public function render(): mixed
    {
        $employee = auth()->user()?->employee;

        $reimbursements = $employee
            ? Reimbursement::with('category')
                ->where('employee_id', $employee->id)
                ->latest()
                ->limit(20)
                ->get()
            : collect();

        return view('livewire.employee.reimbursement.index', compact('reimbursements'));
    }
}
