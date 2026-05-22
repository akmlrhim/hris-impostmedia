<?php

namespace App\Livewire\Admin\Payroll;

use App\Models\PayrollPeriod;
use App\Services\Payroll\PayrollGenerator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Payroll')]
#[Layout('components.layouts.admin')]
class Index extends Component
{
    use WithPagination;

    public bool $showForm = false;

    #[Validate('required|integer|min:2020|max:2099')]
    public int $year;

    #[Validate('required|integer|min:1|max:12')]
    public int $month;

    public ?string $payment_date = null;

    public function mount(): void
    {
        Gate::authorize('manage_payroll');

        $this->year = (int) now()->year;
        $this->month = (int) now()->month;
        $this->payment_date = now()->endOfMonth()->toDateString();
    }

    public function openForm(): void
    {
        $this->resetValidation();
        $this->showForm = true;
    }

    public function createPeriod(PayrollGenerator $generator): void
    {
        $this->validate();

        $start = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $period = PayrollPeriod::firstOrCreate(
            ['year' => $this->year, 'month' => $this->month],
            [
                'code' => sprintf('PR-%d-%02d', $this->year, $this->month),
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'payment_date' => $this->payment_date,
                'status' => 'draft',
            ]
        );

        $generator->generateForPeriod($period);

        $this->showForm = false;
        session()->flash('success', "Periode {$period->code} berhasil digenerate.");
        $this->redirect(route('admin.payroll.show', $period->id), navigate: false);
    }

    public function delete(int $id): void
    {
        $period = PayrollPeriod::findOrFail($id);
        if ($period->locked_at) {
            session()->flash('error', 'Periode sudah dikunci, tidak dapat dihapus.');

            return;
        }
        $period->delete();
    }

    public function render(): mixed
    {
        $periods = PayrollPeriod::withCount('payrolls')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->paginate(12);

        return view('livewire.admin.payroll.index', compact('periods'));
    }
}
