<?php

namespace App\Livewire\Employee;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Models\LeaveRequest;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Pengajuan Cuti & Izin')]
class Leave extends Component
{
    public string $type = '';

    public string $start_date = '';

    public string $end_date = '';

    public string $reason = '';

    public bool $showForm = false;

    public bool $showConfirm = false;

    public function mount(): void
    {
        $this->resetDates();
        $this->type = LeaveType::Annual->value;
    }

    private function resetDates(): void
    {
        $start = now()->addDay()->setTime(9, 0);
        $end = $start->copy()->setTime(17, 0);
        $this->start_date = $start->format('Y-m-d\TH:i');
        $this->end_date = $end->format('Y-m-d\TH:i');
    }

    public function openForm(): void
    {
        $this->reset(['reason']);
        $this->type = LeaveType::Annual->value;
        $this->resetDates();
        $this->resetValidation();
        $this->showForm = true;
        $this->showConfirm = false;
    }

    private function validationRules(): array
    {
        return [
            'type' => 'required|in:'.implode(',', array_column(LeaveType::cases(), 'value')),
            'start_date' => 'required|date|after_or_equal:'.now()->startOfDay()->toDateTimeString(),
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10|max:500',
        ];
    }

    public function requestConfirm(): void
    {
        $this->validate($this->validationRules());

        $this->showConfirm = true;
    }

    public function submit(): void
    {
        $employee = auth()->user()?->employee;

        if (! $employee) {
            $this->dispatch('notify', type: 'error', message: 'Akun belum terhubung ke data karyawan.');

            return;
        }

        $this->validate($this->validationRules());

        LeaveRequest::create([
            'employee_id' => $employee->id,
            'type' => $this->type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'reason' => $this->reason,
            'status' => LeaveStatus::Pending,
        ]);

        $this->showForm = false;
        $this->showConfirm = false;
        $this->dispatch('notify', type: 'success', message: 'Pengajuan berhasil dikirim.');
    }

    public function cancel(int $id): void
    {
        $employee = auth()->user()?->employee;

        $request = LeaveRequest::where('id', $id)
            ->where('employee_id', $employee?->id)
            ->where('status', LeaveStatus::Pending->value)
            ->firstOrFail();

        $request->delete();
        $this->dispatch('notify', type: 'success', message: 'Pengajuan dibatalkan.');
    }

    public function render(): mixed
    {
        $employee = auth()->user()?->employee;

        $requests = $employee
            ? LeaveRequest::where('employee_id', $employee->id)
                ->orderByDesc('created_at')
                ->limit(20)
                ->get()
            : collect();

        return view('livewire.employee.leave', compact('requests'));
    }
}
