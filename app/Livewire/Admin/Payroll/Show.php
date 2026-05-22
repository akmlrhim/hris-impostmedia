<?php

namespace App\Livewire\Admin\Payroll;

use App\Models\PayrollPeriod;
use App\Services\Payroll\PayrollGenerator;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Show extends Component
{
    public PayrollPeriod $period;

    public string $search = '';

    public function mount(PayrollPeriod $period): void
    {
        Gate::authorize('manage_payroll');

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
        $allPayrolls = $this->period->payrolls()
            ->with(['employee'])
            ->orderBy('id')
            ->get();

        $payrolls = $this->search
            ? $allPayrolls->filter(fn ($p) => str_contains(strtolower($p->employee->full_name), strtolower($this->search))
                || str_contains(strtolower($p->employee->employee_number), strtolower($this->search)))
            : $allPayrolls;

        $totals = [
            'gross' => $allPayrolls->sum('gross_salary'),
            'net' => $allPayrolls->sum('net_salary'),
        ];

        return view('livewire.admin.payroll.show', compact('payrolls', 'allPayrolls', 'totals'))
            ->title('Payroll '.$this->period->code);
    }
}
