<?php

namespace App\Livewire\Admin;

use App\Concerns\HandlesAdminActions;
use App\Models\Holiday as HolidayModel;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Hari Libur')]
#[Layout('components.layouts.admin')]
class Holiday extends Component
{
    use HandlesAdminActions, WithPagination;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $date = '';

    public string $holiday_name = '';

    public string $description = '';

    public function rules(): array
    {
        return [
            'date' => ['required', 'date', Rule::unique('days_off', 'date')->ignore($this->editingId)],
            'holiday_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ];
    }

    public function open(?int $id = null): void
    {
        $this->reset(['date', 'holiday_name', 'description', 'editingId']);
        $this->resetValidation();

        if ($id) {
            $holiday = HolidayModel::findOrFail($id);
            $this->editingId = $id;
            $this->date = $holiday->date->toDateString();
            $this->holiday_name = $holiday->holiday_name;
            $this->description = (string) $holiday->description;
        }

        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        $this->safeAction(function () use ($data) {
            if ($this->editingId) {
                HolidayModel::findOrFail($this->editingId)->update($data);
                $this->toast('success', 'Hari libur diperbarui.');
            } else {
                HolidayModel::create($data);
                $this->toast('success', 'Hari libur ditambahkan.');
            }

            $this->showForm = false;
            $this->editingId = null;
        }, permission: 'manage_holidays', genericError: 'Gagal menyimpan hari libur.');
    }

    public function delete(int $id): void
    {
        $this->safeAction(function () use ($id) {
            $holiday = HolidayModel::findOrFail($id);
            $snapshot = $holiday->only(['id', 'date', 'holiday_name']);
            $holiday->delete();
            $this->logActivity('holiday.deleted', "Menghapus hari libur {$snapshot['holiday_name']}", null, $snapshot);
            $this->toast('success', 'Hari libur dihapus.');
        }, permission: 'manage_holidays', genericError: 'Gagal menghapus hari libur.');
    }

    public function mount(): void
    {
        Gate::authorize('manage_holidays');
    }

    public function render(): mixed
    {
        return view('livewire.admin.holiday', [
            'holidays' => HolidayModel::orderBy('date')->paginate(15),
        ]);
    }
}
