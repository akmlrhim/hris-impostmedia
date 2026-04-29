<?php

namespace App\Livewire\Admin\Payroll;

use App\Models\PayrollPeriod;
use App\Services\Payroll\PayrollGenerator;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Show extends Component
{
    public PayrollPeriod $period;

    public string $search = '';

    public function mount(PayrollPeriod $period): void
    {
        $this->period = $period;
    }

    public function regenerate(PayrollGenerator $generator): void
    {
        if ($this->period->locked_at) {
            session()->flash('error', 'Periode terkunci, tidak bisa di-regenerate.');

            return;
        }

        $generator->generateForPeriod($this->period);
        $this->period->refresh();
        session()->flash('success', 'Payroll berhasil di-regenerate.');
    }

    public function finalize(): void
    {
        $this->period->update(['status' => 'final', 'locked_at' => now()]);
        $this->period->payrolls()->update(['status' => 'final']);
        session()->flash('success', 'Periode dikunci. Slip gaji sekarang dapat dilihat karyawan.');
    }

    public function markPaid(): void
    {
        if ($this->period->status !== 'final') {
            session()->flash('error', 'Finalize periode terlebih dahulu.');

            return;
        }
        $this->period->update(['status' => 'paid']);
        $this->period->payrolls()->update(['status' => 'paid']);
        session()->flash('success', 'Periode ditandai sudah dibayar.');
    }

    public function render(): mixed
    {
        $payrolls = $this->period->payrolls()
            ->with(['employee.position'])
            ->when($this->search, fn ($q) => $q->whereHas('employee', fn ($q) => $q->where('full_name', 'like', "%{$this->search}%")
                ->orWhere('employee_number', 'like', "%{$this->search}%")))
            ->get();

        $totals = [
            'gross' => $payrolls->sum('gross_salary'),
            'deductions' => $payrolls->sum('total_deductions'),
            'net' => $payrolls->sum('net_salary'),
        ];

        return view('livewire.admin.payroll.show', compact('payrolls', 'totals'));
    }
}
