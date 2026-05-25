<?php

namespace App\Livewire\Admin;

use App\Concerns\HandlesAdminActions;
use App\Models\Shift as ShiftModel;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Manajemen Shift')]
#[Layout('components.layouts.admin')]
class Shift extends Component
{
	use HandlesAdminActions, WithPagination;

	public bool $showForm = false;

	public ?int $editingId = null;

	public string $code = '';

	public string $name = '';

	public string $start_time = '09:00';

	public string $end_time = '18:00';

	public ?string $break_start = '12:00';

	public ?string $break_end = '13:00';

	public int $late_tolerance_minutes = 15;

	public function rules(): array
	{
		return [
			'code' => ['required', 'string', 'max:30', Rule::unique('shifts')->ignore($this->editingId)],
			'name' => 'required|string|max:100',
			'start_time' => 'required',
			'end_time' => 'required',
			'late_tolerance_minutes' => 'integer|min:0|max:120',
		];
	}

	public function open(?int $id = null): void
	{
		$this->reset(['code', 'name', 'start_time', 'end_time', 'break_start', 'break_end', 'late_tolerance_minutes', 'editingId']);
		$this->resetValidation();

		if ($id) {
			$shift = ShiftModel::findOrFail($id);
			$this->editingId = $id;
			$this->code = $shift->code;
			$this->name = $shift->name;
			$this->start_time = substr((string) $shift->start_time, 0, 5);
			$this->end_time = substr((string) $shift->end_time, 0, 5);
			$this->break_start = $shift->break_start ? substr((string) $shift->break_start, 0, 5) : null;
			$this->break_end = $shift->break_end ? substr((string) $shift->break_end, 0, 5) : null;
			$this->late_tolerance_minutes = (int) $shift->late_tolerance_minutes;
		}

		$this->showForm = true;
	}

	public function save(): void
	{
		$data = $this->validate();

		$this->safeAction(function () use ($data) {
			if ($this->editingId) {
				ShiftModel::findOrFail($this->editingId)->update($data + [
					'break_start' => $this->break_start,
					'break_end' => $this->break_end,
				]);
				$this->toast('success', 'Shift diperbarui.');
			} else {
				ShiftModel::create($data + [
					'break_start' => $this->break_start,
					'break_end' => $this->break_end,
				]);
				$this->toast('success', 'Shift ditambahkan.');
			}

			$this->showForm = false;
			$this->editingId = null;
		}, permission: 'manage_shifts', genericError: 'Gagal menyimpan shift.');
	}

	public function delete(int $id): void
	{
		$this->safeAction(function () use ($id) {
			$shift = ShiftModel::withCount('schedules')->findOrFail($id);

			if ($shift->schedules_count > 0) {
				$this->toast('warning', "Shift dipakai {$shift->schedules_count} jadwal kerja. Hapus jadwal terkait dulu.");

				return;
			}

			$shiftSnapshot = $shift->only(['id', 'code', 'name']);
			$shift->delete();
			$this->logActivity('shift.deleted', "Menghapus shift {$shiftSnapshot['code']} ({$shiftSnapshot['name']})", null, $shiftSnapshot);
			$this->toast('success', 'Shift dihapus.');
		}, permission: 'manage_shifts', genericError: 'Gagal menghapus shift.');
	}

	public function mount(): void
	{
		Gate::authorize('manage_shifts');
	}

	public function render(): mixed
	{
		return view('livewire.admin.shift', [
			'shifts' => ShiftModel::orderBy('code')->paginate(15),
		]);
	}
}
