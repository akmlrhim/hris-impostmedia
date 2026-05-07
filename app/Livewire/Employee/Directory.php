<?php

namespace App\Livewire\Employee;

use App\Models\Employee;
use App\Models\JobPosition;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.mobile')]
class Directory extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $position = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPosition(): void
    {
        $this->resetPage();
    }

    public function render(): mixed
    {
        $employees = Employee::query()
            ->with(['user:id,email', 'position:id,name'])
            ->where('is_active', true)
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('full_name', 'like', "%{$this->search}%")
                    ->orWhere('nickname', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%");
            }))
            ->when($this->position, fn ($q) => $q->where('job_position_id', $this->position))
            ->orderBy('full_name')
            ->paginate(20);

        return view('livewire.employee.directory', [
            'employees' => $employees,
            'positions' => JobPosition::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
