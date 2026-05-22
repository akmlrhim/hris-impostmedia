<?php

namespace App\Livewire\Employee;

use App\Enums\RemoteWorkStatus;
use App\Enums\WorkType;
use App\Models\RemoteWorkRequest;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Pengajuan WFA / WFH')]
class RemoteWork extends Component
{
    public string $start_date = '';

    public string $end_date = '';

    public string $reason = '';

    public bool $showForm = false;

    public bool $showConfirm = false;

    public function mount(): void
    {
        $this->start_date = now()->addDay()->toDateString();
        $this->end_date = now()->addDay()->toDateString();
    }

    public function openForm(): void
    {
        $employee = auth()->user()?->employee;

        if ($employee?->work_type !== WorkType::WFO) {
            $this->dispatch('notify', type: 'warning', message: 'Hanya karyawan WFO yang dapat mengajukan WFA.');

            return;
        }

        $this->reset(['reason']);
        $this->start_date = now()->addDay()->toDateString();
        $this->end_date = now()->addDay()->toDateString();
        $this->resetValidation();
        $this->showForm = true;
        $this->showConfirm = false;
    }

    public function requestConfirm(): void
    {
        $employee = auth()->user()?->employee;

        if (! $employee || $employee->work_type !== WorkType::WFO) {
            $this->dispatch('notify', type: 'warning', message: 'Hanya karyawan WFO yang dapat mengajukan WFA.');

            return;
        }

        $this->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10|max:500',
        ]);

        $this->showConfirm = true;
    }

    public function submit(): void
    {
        $employee = auth()->user()?->employee;

        if (! $employee) {
            $this->dispatch('notify', type: 'error', message: 'Akun belum terhubung ke data karyawan.');

            return;
        }

        if ($employee->work_type !== WorkType::WFO) {
            $this->dispatch('notify', type: 'warning', message: 'Hanya karyawan WFO yang dapat mengajukan WFA.');

            return;
        }

        $this->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10|max:500',
        ]);

        RemoteWorkRequest::create([
            'employee_id' => $employee->id,
            'work_type' => WorkType::WFA,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'reason' => $this->reason,
            'status' => RemoteWorkStatus::Pending,
        ]);

        $this->showForm = false;
        $this->showConfirm = false;
        $this->dispatch('notify', type: 'success', message: 'Pengajuan WFA berhasil dikirim.');
    }

    public function cancel(int $id): void
    {
        $employee = auth()->user()?->employee;

        $request = RemoteWorkRequest::where('id', $id)
            ->where('employee_id', $employee?->id)
            ->where('status', RemoteWorkStatus::Pending->value)
            ->firstOrFail();

        $request->delete();
        $this->dispatch('notify', type: 'success', message: 'Pengajuan dibatalkan.');
    }

    public function render(): mixed
    {
        $employee = auth()->user()?->employee;

        $requests = $employee
            ? RemoteWorkRequest::where('employee_id', $employee->id)
                ->orderByDesc('created_at')
                ->limit(20)
                ->get()
            : collect();

        $isWfo = $employee?->work_type === WorkType::WFO;
        $todayApproved = $employee ? RemoteWorkRequest::approvedFor($employee->id, now()->toDateString()) : null;

        return view('livewire.employee.remote-work', compact('requests', 'isWfo', 'todayApproved'));
    }
}
