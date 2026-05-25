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

	public function createPeriod(PayrollGenerator $generator): mixed
	{
		$this->validate();

		return $this->safeAction(function () use ($generator) {
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
