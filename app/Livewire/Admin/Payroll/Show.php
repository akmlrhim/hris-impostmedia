<?php

namespace App\Livewire\Admin\Payroll;

use App\Concerns\HandlesAdminActions;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Services\Payroll\PayrollGenerator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class Show extends Component
{
    use HandlesAdminActions, WithPagination;

    public PayrollPeriod $period;

    #[Url]
    public string $search = '';

    public bool $showAddForm = false;

    /** @var array<int, int> */
    public array $selectedEmployees = [];

    public bool $addSelectAll = false;

    public string $employeeSearch = '';

    public function mount(PayrollPeriod $period): void
    {
        Gate::authorize('manage_payroll');

        $this->period = $period;
    }

    public function regenerate(PayrollGenerator $generator): void
    {
        $this->safeAction(function () use ($generator) {
            if ($this->period->locked_at) {
                $this->toast('warning', 'Periode terkunci, tidak bisa di-regenerate.');

                return;
            }

            // Only recompute slips for employees already in this period so the
            // selection made at generate time is preserved.
            $employeeIds = $this->period->payrolls()->pluck('employee_id')->all();

            $generator->generateForPeriod($this->period, $employeeIds ?: null);
            $this->period->refresh();
            $this->logActivity('payroll.regenerated', "Regenerate payroll periode {$this->period->code}", $this->period);
            $this->toast('success', 'Payroll berhasil di-regenerate.');
        }, permission: 'manage_payroll', genericError: 'Gagal regenerate payroll.');
    }

    public function openAddForm(): void
    {
        $this->reset(['selectedEmployees', 'addSelectAll', 'employeeSearch']);
        $this->resetValidation();
        $this->showAddForm = true;
    }

    public function updatedAddSelectAll(bool $value): void
    {
        $this->selectedEmployees = $value
            ? $this->availableEmployees()->pluck('id')->all()
            : [];
    }

    public function addEmployees(PayrollGenerator $generator): void
    {
        if ($this->period->locked_at) {
            $this->toast('warning', 'Periode terkunci, tidak bisa menambah karyawan.');

            return;
        }

        // Keep only employees that are still available (active & not yet in period).
        $availableIds = $this->availableEmployees()->pluck('id')->all();
        $employeeIds = array_values(array_intersect($this->selectedEmployees, $availableIds));

        if (empty($employeeIds)) {
            $this->addError('selectedEmployees', 'Pilih minimal satu karyawan untuk ditambahkan.');

            return;
        }

        $this->safeAction(function () use ($generator, $employeeIds) {
            $generator->generateForPeriod($this->period, $employeeIds);
            $this->period->refresh();
            $this->showAddForm = false;
            $this->reset(['selectedEmployees', 'addSelectAll', 'employeeSearch']);
            $this->logActivity('payroll.employees_added', count($employeeIds)." karyawan ditambahkan ke periode {$this->period->code}", $this->period, ['employee_ids' => $employeeIds]);
            $this->toast('success', count($employeeIds).' karyawan berhasil ditambahkan.');
        }, permission: 'manage_payroll', genericError: 'Gagal menambah karyawan.');
    }

    public function removePayroll(int $payrollId): void
    {
        $this->safeAction(function () use ($payrollId) {
            if ($this->period->locked_at) {
                $this->toast('warning', 'Periode terkunci, slip tidak bisa dihapus.');

                return;
            }

            $payroll = Payroll::with('employee')->where('payroll_period_id', $this->period->id)->find($payrollId);

            if (! $payroll) {
                return;
            }

            $name = $payroll->employee?->full_name ?? '-';
            $payroll->delete();
            $this->period->refresh();
            $this->logActivity('payroll.employee_removed', "Menghapus slip {$name} dari periode {$this->period->code}", $this->period, ['payroll_id' => $payrollId]);
            $this->toast('success', "Slip {$name} dihapus dari periode.");
        }, permission: 'manage_payroll', genericError: 'Gagal menghapus slip.');
    }

    /**
     * Active employees not yet included in this period.
     *
     * @return Collection<int, Employee>
     */
    private function availableEmployees(): Collection
    {
        $existingIds = $this->period->payrolls()->pluck('employee_id');

        return Employee::where('is_active', true)
            ->whereNotIn('id', $existingIds)
            ->when($this->employeeSearch, fn ($q) => $q->where(function ($q) {
                $q->where('full_name', 'like', "%{$this->employeeSearch}%")
                    ->orWhere('employee_number', 'like', "%{$this->employeeSearch}%");
            }))
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'employee_number']);
    }

    public function finalize(): void
    {
        $this->safeAction(function () {
            $this->period->update(['status' => 'final', 'locked_at' => now()]);
            $this->period->payrolls()->update(['status' => 'final']);
            $this->logActivity('payroll.finalized', "Mengunci periode payroll {$this->period->code}", $this->period);
            $this->toast('success', 'Periode dikunci. Slip gaji sekarang dapat dilihat karyawan.');
        }, permission: 'manage_payroll', genericError: 'Gagal mengunci periode.');
    }

    public function markPaid(): void
    {
        $this->safeAction(function () {
            if ($this->period->status !== 'final') {
                $this->toast('warning', 'Finalize periode terlebih dahulu.');

                return;
            }
            $this->period->update(['status' => 'paid']);
            $this->period->payrolls()->update(['status' => 'paid']);
            $this->logActivity('payroll.paid', "Menandai periode {$this->period->code} sudah dibayar", $this->period);
            $this->toast('success', 'Periode ditandai sudah dibayar.');
        }, permission: 'manage_payroll', genericError: 'Gagal menandai periode sebagai dibayar.');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): mixed
    {
        $payrolls = $this->period->payrolls()
            ->with(['employee'])
            ->when($this->search, fn ($q) => $q->whereHas('employee', function ($qq) {
                $qq->where('full_name', 'like', "%{$this->search}%")
                    ->orWhere('employee_number', 'like', "%{$this->search}%");
            }))
            ->orderBy('id')
            ->paginate(25);

        $totals = $this->period->payrolls()->selectRaw('SUM(gross_salary) as gross, SUM(net_salary) as net')->first();

        $availableEmployees = $this->showAddForm ? $this->availableEmployees() : collect();

        return view('livewire.admin.payroll.show', compact('payrolls', 'totals', 'availableEmployees'))
            ->title('Payroll '.$this->period->code);
    }
}
