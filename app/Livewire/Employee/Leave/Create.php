<?php

namespace App\Livewire\Employee\Leave;

use App\Enums\RequestStatus;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
class Create extends Component
{
    #[Validate('required|exists:leave_types,id')]
    public ?int $leave_type_id = null;

    #[Validate('required|date|after_or_equal:today')]
    public string $start_date = '';

    #[Validate('required|date|after_or_equal:start_date')]
    public string $end_date = '';

    #[Validate('required|string|min:5|max:500')]
    public string $reason = '';

    public bool $is_half_day = false;

    public function submit(): void
    {
        $this->validate();

        $employee = auth()->user()?->employee;
        abort_if(! $employee, 403);

        $days = Carbon::parse($this->start_date)->diffInDays(Carbon::parse($this->end_date)) + 1;

        LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $this->leave_type_id,
            'request_number' => 'LR-'.now()->format('Ymd').'-'.Str::upper(Str::random(4)),
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'total_days' => $this->is_half_day ? 0.5 : $days,
            'is_half_day' => $this->is_half_day,
            'reason' => $this->reason,
            'status' => RequestStatus::Pending,
        ]);

        session()->flash('success', 'Pengajuan cuti berhasil dikirim.');
        $this->redirect(route('mobile.leave'), navigate: false);
    }

    public function render(): mixed
    {
        return view('livewire.employee.leave.create', [
            'leaveTypes' => LeaveType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
