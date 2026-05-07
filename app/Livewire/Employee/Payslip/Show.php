<?php

namespace App\Livewire\Employee\Payslip;

use App\Models\Payroll;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
class Show extends Component
{
    public Payroll $payroll;

    public function mount(Payroll $payroll): void
    {
        $employee = auth()->user()?->employee;
        abort_if(! $employee || $payroll->employee_id !== $employee->id, 403);

        $this->payroll = $payroll->load(['period', 'items']);
    }

    public function render(): mixed
    {
        $earnings = $this->payroll->items->where('type', 'earning');

        return view('livewire.employee.payslip.show', compact('earnings'));
    }
}
