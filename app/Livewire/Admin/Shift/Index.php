<?php

namespace App\Livewire\Admin\Shift;

use App\Models\Shift;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Index extends Component
{
    public bool $showForm = false;

    public ?int $editingId = null;

    #[Validate('required|string|max:30|unique:shifts,code')]
    public string $code = '';

    #[Validate('required|string|max:100')]
    public string $name = '';

    #[Validate('required')]
    public string $start_time = '09:00';

    #[Validate('required')]
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
        $this->resetExcept();
        $this->reset(['code', 'name', 'start_time', 'end_time', 'break_start', 'break_end', 'late_tolerance_minutes']);
        $this->resetValidation();

        if ($id) {
            $shift = Shift::findOrFail($id);
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
            Shift::findOrFail($this->editingId)->update($data + [
                'break_start' => $this->break_start,
                'break_end' => $this->break_end,
            ]);
        } else {
            Shift::create($data + [
                'break_start' => $this->break_start,
                'break_end' => $this->break_end,
            ]);
        }

        $this->showForm = false;
        $this->editingId = null;
    }

    public function delete(int $id): void
    {
        Shift::findOrFail($id)->delete();
    }

    public function render(): mixed
    {
        return view('livewire.admin.shift.index', [
            'shifts' => Shift::orderBy('code')->get(),
        ]);
    }
}
