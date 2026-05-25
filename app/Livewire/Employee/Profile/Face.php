<?php

namespace App\Livewire\Employee\Profile;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Data Wajah')]
class Face extends Component
{
	public bool $hasFaceEnrolled = false;

	public function mount(): void
	{
		$employee = auth()->user()?->employee;
		$this->hasFaceEnrolled = ! empty($employee?->face_descriptor);
	}

	public function enrollFace(array $descriptor): void
	{
		abort_unless(count($descriptor) === 128, 422);

		$employee = auth()->user()?->employee;
		abort_if(! $employee, 403);

		$employee->update(['face_descriptor' => $descriptor]);
		$this->hasFaceEnrolled = true;

		$this->dispatch('notify', type: 'success', message: 'Data wajah berhasil didaftarkan.');
	}

	public function deleteFace(): void
	{
		$employee = auth()->user()?->employee;
		abort_if(! $employee, 403);

		$employee->update(['face_descriptor' => null]);
		$this->hasFaceEnrolled = false;

		$this->dispatch('notify', type: 'success', message: 'Data wajah dihapus.');
	}

	public function render(): mixed
	{
		return view('livewire.employee.profile.face');
	}
}
