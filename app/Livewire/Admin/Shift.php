<?php

namespace App\Livewire\Admin;

use App\Models\Shift as ShiftModel;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Manajemen Shift')]
#[Layout('components.layouts.admin')]
class Shift extends Component
{
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

        if ($this->editingId) {
            ShiftModel::findOrFail($this->editingId)->update($data + [
                'break_start' => $this->break_start,
                'break_end' => $this->break_end,
            ]);
            $this->dispatch('notify', type: 'success', message: 'Shift diperbarui.');
        } else {
            ShiftModel::create($data + [
                'break_start' => $this->break_start,
                'break_end' => $this->break_end,
            ]);
            $this->dispatch('notify', type: 'success', message: 'Shift ditambahkan.');
        }

        $this->showForm = false;
        $this->editingId = null;
    }

    public function delete(int $id): void
    {
        ShiftModel::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Shift dihapus.');
    }

    public function mount(): void
    {
        Gate::authorize('manage_shifts');
    }

    public function render(): mixed
    {
        return view('livewire.admin.shift', [
            'shifts' => ShiftModel::orderBy('code')->get(),
        ]);
    }
}
