<?php

namespace App\Livewire\Admin;

use App\Enums\LeaveStatus;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Pengajuan Cuti & Izin')]
#[Layout('components.layouts.admin')]
class Leave extends Component
{
    use WithPagination;

    public string $filterStatus = 'pending';

    public string $filterType = '';

    public string $rejectionReason = '';

    public ?int $rejectingId = null;

    public bool $showRejectForm = false;

    public function mount(): void
    {
        Gate::authorize('manage_leave');
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function approve(int $id): void
    {
        $request = LeaveRequest::findOrFail($id);
        $request->update([
            'status' => LeaveStatus::Approved,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);
        $this->dispatch('notify', type: 'success', message: 'Pengajuan disetujui.');
    }

    public function openRejectForm(int $id): void
    {
        $this->rejectingId = $id;
        $this->rejectionReason = '';
        $this->resetValidation();
        $this->showRejectForm = true;
    }

    public function reject(): void
    {
        $this->validate(['rejectionReason' => 'required|string|min:5|max:500']);

        LeaveRequest::findOrFail($this->rejectingId)->update([
            'status' => LeaveStatus::Rejected,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => $this->rejectionReason,
        ]);

        $this->showRejectForm = false;
        $this->dispatch('notify', type: 'success', message: 'Pengajuan ditolak.');
    }

    public function render(): mixed
    {
        $requests = LeaveRequest::with(['employee', 'reviewer'])
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
            ->latest()
            ->paginate(20);

        $pendingCount = LeaveRequest::where('status', LeaveStatus::Pending->value)->count();

        return view('livewire.admin.leave', compact('requests', 'pendingCount'));
    }
}
