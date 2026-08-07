<?php

namespace App\Livewire\Admin;

use App\Concerns\HandlesAdminActions;
use App\Models\Employee;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Data Absensi')]
#[Layout('components.layouts.admin')]
class Attendance extends Component
{
    use HandlesAdminActions, WithPagination;

    /** Pseudo-status for employees with no attendance row on the selected date. */
    public const STATUS_MISSING = 'not_recorded';

    #[Url]
    public string $date = '';

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('manage_attendance');

        $this->date = $this->date ?: now()->toDateString();
    }

    public function updatingDate(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    /**
     * The roster is driven by employees rather than attendance rows, so people
     * who never checked in on the selected date still show up.
     */
    public function render(): mixed
    {
        $date = $this->date ?: now()->toDateString();

        $onDate = fn ($query) => $query->whereDate('attendance_date', $date);

        $employees = Employee::query()
            ->where('is_active', true)
            ->with(['attendances' => $onDate])
            ->when(
                $this->status === self::STATUS_MISSING,
                fn ($q) => $q->whereDoesntHave('attendances', $onDate),
            )
            ->when(
                $this->status !== '' && $this->status !== self::STATUS_MISSING,
                fn ($q) => $q->whereHas(
                    'attendances',
                    fn ($a) => $onDate($a)->where('status', $this->status),
                ),
            )
            ->orderBy('full_name')
            ->paginate(20);

        return view('livewire.admin.attendance', compact('employees'));
    }
}
