<?php

namespace App\Livewire\Admin;

use App\Models\Attendance as AttendanceModel;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class Attendance extends Component
{
    use WithPagination;

    #[Url]
    public string $date = '';

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        $this->date = $this->date ?: now()->toDateString();
    }

    public function render(): mixed
    {
        $attendances = AttendanceModel::with(['employee.position', 'shift'])
            ->when($this->date, fn ($q) => $q->whereDate('attendance_date', $this->date))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest('check_in_at')
            ->paginate(20);

        return view('livewire.admin.attendance', compact('attendances'));
    }
}
