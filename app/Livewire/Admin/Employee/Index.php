<?php

namespace App\Livewire\Admin\Employee;

use App\Models\Employee;
use App\Models\JobPosition;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $position = '';

    #[Url]
    public string $status = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): mixed
    {
        $employees = Employee::query()
            ->with(['position'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('full_name', 'like', "%{$this->search}%")
                    ->orWhere('employee_number', 'like', "%{$this->search}%");
            }))
            ->when($this->position, fn ($q) => $q->where('job_position_id', $this->position))
            ->when($this->status, fn ($q) => $q->where('employment_status', $this->status))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.employee.index', [
            'employees' => $employees,
            'positions' => JobPosition::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
