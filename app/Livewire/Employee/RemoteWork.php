<?php

namespace App\Livewire\Employee;

use App\Enums\RemoteWorkStatus;
use App\Enums\WorkType;
use App\Models\RemoteWorkRequest;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Pengajuan WFA')]
class RemoteWork extends Component
{
    public string $work_type = WorkType::WFA->value;

    public string $start_date = '';

    public string $end_date = '';

    public string $reason = '';

    public bool $showForm = false;

    public bool $showConfirm = false;

    /** List filters — empty string means "all". */
    public string $statusFilter = '';

    public string $typeFilter = '';

    public function mount(): void
    {
        $this->resetDates();
    }

    private function resetDates(): void
    {
        $start = now()->setTime(9, 0);
        $end = $start->copy()->setTime(18, 0);
        $this->start_date = $start->format('Y-m-d\TH:i');
        $this->end_date = $end->format('Y-m-d\TH:i');
    }

    public function openForm(): void
    {
        $employee = auth()->user()?->employee;

        if ($employee?->work_type !== WorkType::WFO) {
            $this->dispatch('notify', type: 'warning', message: 'Hanya karyawan WFO yang dapat mengajukan WFA.');

            return;
        }

        $this->reset(['reason']);
        $this->work_type = WorkType::WFA->value;
        $this->resetDates();
        $this->resetValidation();
        $this->showForm = true;
        $this->showConfirm = false;
    }

    private function validationRules(): array
    {
        return [
            'work_type' => 'required|in:'.implode(',', array_map(fn ($t) => $t->value, WorkType::remoteRequestable())),
            'start_date' => 'required|date|after_or_equal:'.now()->startOfDay()->toDateTimeString(),
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10|max:500',
        ];
    }

    public function requestConfirm(): void
    {
        $employee = auth()->user()?->employee;

        if (! $employee || $employee->work_type !== WorkType::WFO) {
            $this->dispatch('notify', type: 'warning', message: 'Hanya karyawan WFO yang dapat mengajukan WFA.');

            return;
        }

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

        if ($employee->work_type !== WorkType::WFO) {
            $this->dispatch('notify', type: 'warning', message: 'Hanya karyawan WFO yang dapat mengajukan WFA.');

            return;
        }

        $this->validate($this->validationRules());

        RemoteWorkRequest::create([
            'employee_id' => $employee->id,
            'work_type' => $this->work_type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'reason' => $this->reason,
            'status' => RemoteWorkStatus::Pending,
        ]);

        $this->showForm = false;
        $this->showConfirm = false;
        $this->dispatch('notify', type: 'success', message: 'Pengajuan berhasil dikirim.');
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
                ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
                ->when($this->typeFilter !== '', fn ($q) => $q->where('work_type', $this->typeFilter))
                ->orderByDesc('created_at')
                ->limit(20)
                ->get()
            : collect();

        $isWfo = $employee?->work_type === WorkType::WFO;
        $todayApproved = $employee ? RemoteWorkRequest::approvedFor($employee->id, now()->toDateString()) : null;

        return view('livewire.employee.remote-work', [
            'requests' => $requests,
            'isWfo' => $isWfo,
            'todayApproved' => $todayApproved,
            'requestableTypes' => WorkType::remoteRequestable(),
            'statuses' => RemoteWorkStatus::cases(),
        ]);
    }
}
