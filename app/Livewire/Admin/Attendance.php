<?php

namespace App\Livewire\Admin;

use App\Concerns\HandlesAdminActions;
use App\Models\Attendance as AttendanceModel;
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

    public function render(): mixed
    {
        $attendances = AttendanceModel::with(['employee'])
            ->when($this->date, fn ($q) => $q->whereDate('attendance_date', $this->date))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest('check_in_at')
            ->paginate(20);

        return view('livewire.admin.attendance', compact('attendances'));
    }
}
