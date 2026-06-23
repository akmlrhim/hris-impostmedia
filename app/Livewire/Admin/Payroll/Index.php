<?php

namespace App\Livewire\Admin\Payroll;

use App\Concerns\HandlesAdminActions;
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
    use HandlesAdminActions, WithPagination;

    public bool $showForm = false;

    #[Validate('required|integer|min:2020|max:2099')]
    public int $year;

    #[Validate('required|integer|min:1|max:12')]
    public int $month;

    #[Validate('required|date')]
    public ?string $start_date = null;

    #[Validate('required|date|after_or_equal:start_date')]
    public ?string $end_date = null;

    public ?string $payment_date = null;

    public function mount(): void
    {
        Gate::authorize('manage_payroll');

        $this->year = (int) now()->year;
        $this->month = (int) now()->month;
        $this->start_date = now()->startOfMonth()->toDateString();
        $this->end_date = now()->endOfMonth()->toDateString();
        $this->payment_date = now()->endOfMonth()->toDateString();
    }

    public function updatedYear(): void
    {
        $this->syncPeriodDates();
    }

    public function updatedMonth(): void
    {
        $this->syncPeriodDates();
    }

    private function syncPeriodDates(): void
    {
        $start = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $this->start_date = $start->toDateString();
        $this->end_date = $start->copy()->endOfMonth()->toDateString();
        $this->payment_date = $this->end_date;
    }

    public function openForm(): void
    {
        $this->resetValidation();
        $this->showForm = true;
    }

    public function createPeriod(PayrollGenerator $generator): mixed
    {
        $this->validate();

        return $this->safeAction(function () use ($generator) {
            $period = PayrollPeriod::firstOrCreate(
                ['year' => $this->year, 'month' => $this->month],
                [
                    'code' => sprintf('PR-%d-%02d', $this->year, $this->month),
                    'start_date' => $this->start_date,
                    'end_date' => $this->end_date,
                    'payment_date' => $this->payment_date,
                    'status' => 'draft',
                ]
            );

            $generator->generateForPeriod($period);

            $this->showForm = false;
            $this->logActivity('payroll.period_created', "Generate periode payroll {$period->code}", $period);
            $this->toast('success', "Periode {$period->code} berhasil digenerate.");

            return $this->redirect(route('admin.payroll.show', $period->id), navigate: false);
        }, permission: 'manage_payroll', genericError: 'Gagal generate periode payroll.');
    }

    public function delete(int $id): void
    {
        $this->safeAction(function () use ($id) {
            $period = PayrollPeriod::findOrFail($id);
            if ($period->locked_at) {
                $this->toast('warning', 'Periode sudah dikunci, tidak dapat dihapus.');

                return;
            }
            $code = $period->code;
            $period->delete();
            $this->logActivity('payroll.period_deleted', "Menghapus periode payroll {$code}", null, ['id' => $id, 'code' => $code]);
            $this->toast('success', 'Periode dihapus.');
        }, permission: 'manage_payroll', genericError: 'Gagal menghapus periode.');
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
