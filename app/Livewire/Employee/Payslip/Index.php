<?php

namespace App\Livewire\Employee\Payslip;

use App\Models\Payroll;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
class Index extends Component
{
    public function render(): mixed
    {
        $employee = auth()->user()?->employee;

        $payslips = $employee
            ? Payroll::with('period')
                ->where('employee_id', $employee->id)
                ->whereIn('status', ['final', 'paid'])
                ->orderByDesc('id')
                ->limit(24)
                ->get()
            : collect();

        return view('livewire.employee.payslip.index', compact('payslips'));
    }
}
