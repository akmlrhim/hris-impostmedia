<?php

namespace App\Livewire\Employee;

use App\Models\Employee;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.mobile')]
#[Title('Direktori Karyawan')]
class Directory extends Component
{
	use WithPagination;

	#[Url(as: 'q')]
	public string $search = '';

	public function updatingSearch(): void
	{
		$this->resetPage();
	}

	public function render(): mixed
	{
		$employees = Employee::query()
			->with(['user:id,email'])
			->where('is_active', true)
			->when($this->search, fn($q) => $q->where(function ($q) {
				$q->where('full_name', 'like', "%{$this->search}%")
					->orWhere('nickname', 'like', "%{$this->search}%")
					->orWhere('phone', 'like', "%{$this->search}%");
			}))
			->orderBy('full_name')
			->paginate(20);

		return view('livewire.employee.directory', [
			'employees' => $employees,
		]);
	}
}
