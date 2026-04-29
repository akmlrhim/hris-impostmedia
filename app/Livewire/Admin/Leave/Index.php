<?php

namespace App\Livewire\Admin\Leave;

use App\Enums\RequestStatus;
use App\Models\LeaveRequest;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    public function approve(int $id): void
    {
        $leave = LeaveRequest::findOrFail($id);
        $leave->update([
            'status' => RequestStatus::Approved,
            'approver_id' => auth()->user()?->employee?->id,
            'approved_at' => now(),
        ]);

        $this->dispatch('notify', message: 'Pengajuan disetujui.');
    }

    public function reject(int $id): void
    {
        $leave = LeaveRequest::findOrFail($id);
        $leave->update([
            'status' => RequestStatus::Rejected,
            'approver_id' => auth()->user()?->employee?->id,
        ]);

        $this->dispatch('notify', message: 'Pengajuan ditolak.');
    }

    public function render(): mixed
    {
        $requests = LeaveRequest::with(['employee.position', 'leaveType', 'approver'])
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.leave.index', compact('requests'));
    }
}
