<?php

namespace App\Livewire\Admin;

use App\Models\Holiday as HolidayModel;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Hari Libur')]
#[Layout('components.layouts.admin')]
class Holiday extends Component
{
    #[Url]
    public int $year;

    public bool $showForm = false;

    public ?int $editingId = null;

    public ?string $date = null;

    public string $name = '';

    public string $description = '';

    public bool $is_national = true;

    public function mount(): void
    {
        Gate::authorize('manage_holidays');

        $this->year = (int) (request()->query('year') ?: now()->year);
    }

    public function open(?int $id = null): void
    {
        $this->reset(['editingId', 'date', 'name', 'description', 'is_national']);
        $this->resetValidation();
        $this->is_national = true;

        if ($id) {
            $h = HolidayModel::findOrFail($id);
            $this->editingId = $h->id;
            $this->date = $h->date->format('Y-m-d');
            $this->name = $h->name;
            $this->description = (string) $h->description;
            $this->is_national = (bool) $h->is_national;
        } else {
            $this->date = now()->setYear($this->year)->toDateString();
        }

        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'date' => [
                'required', 'date',
                Rule::unique('holidays', 'date')->ignore($this->editingId),
            ],
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
        ]);

        HolidayModel::updateOrCreate(
            ['id' => $this->editingId],
            [
                'date' => $this->date,
                'name' => $this->name,
                'description' => $this->description ?: null,
                'is_national' => $this->is_national,
            ],
        );

        $this->showForm = false;
        $this->editingId = null;
        $this->dispatch('notify', type: 'success', message: 'Hari libur disimpan.');
    }

    public function delete(int $id): void
    {
        HolidayModel::whereKey($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Hari libur dihapus.');
    }

    public function render(): mixed
    {
        $holidays = HolidayModel::query()
            ->whereYear('date', $this->year)
            ->orderBy('date')
            ->get();

        $availableYears = collect(range(now()->year - 2, now()->year + 2))->reverse()->values();

        return view('livewire.admin.holiday', [
            'holidays' => $holidays,
            'availableYears' => $availableYears,
        ]);
    }
}
