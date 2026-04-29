<?php

namespace App\Livewire\Admin\Payroll;

use App\Models\Payroll;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Payslip extends Component
{
    public Payroll $payroll;

    public function mount(Payroll $payroll): void
    {
        $this->payroll = $payroll->load(['employee.position', 'period', 'items']);
    }

    public function render(): mixed
    {
        $earnings = $this->payroll->items->where('type', 'earning');
        $deductions = $this->payroll->items->whereIn('type', ['deduction', 'tax']);

        return view('livewire.admin.payroll.payslip', compact('earnings', 'deductions'));
    }
}
