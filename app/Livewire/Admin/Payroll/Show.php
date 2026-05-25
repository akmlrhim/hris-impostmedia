<?php

namespace App\Livewire\Admin\Payroll;

use App\Concerns\HandlesAdminActions;
use App\Models\PayrollPeriod;
use App\Services\Payroll\PayrollGenerator;
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

			$generator->generateForPeriod($this->period);
			$this->period->refresh();
			$this->logActivity('payroll.regenerated', "Regenerate payroll periode {$this->period->code}", $this->period);
			$this->toast('success', 'Payroll berhasil di-regenerate.');
		}, permission: 'manage_payroll', genericError: 'Gagal regenerate payroll.');
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
		// Full list (untuk cetak + total kalkulasi)
		$allPayrolls = $this->period->payrolls()
			->with(['employee'])
			->orderBy('id')
			->get();

		// Daftar tampilan: paginate + filter pencarian di DB
		$payrolls = $this->period->payrolls()
			->with(['employee'])
			->when($this->search, fn($q) => $q->whereHas('employee', function ($qq) {
				$qq->where('full_name', 'like', "%{$this->search}%")
					->orWhere('employee_number', 'like', "%{$this->search}%");
			}))
			->orderBy('id')
			->paginate(25);

		$totals = [
			'gross' => $allPayrolls->sum('gross_salary'),
			'net' => $allPayrolls->sum('net_salary'),
		];

		return view('livewire.admin.payroll.show', compact('payrolls', 'allPayrolls', 'totals'))
			->title('Payroll ' . $this->period->code);
	}
}
