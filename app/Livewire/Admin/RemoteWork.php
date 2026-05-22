<?php

namespace App\Livewire\Admin;

use App\Enums\RemoteWorkStatus;
use App\Models\RemoteWorkRequest;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Pengajuan WFA')]
#[Layout('components.layouts.admin')]
class RemoteWork extends Component
{
    use WithPagination;

    public string $filterStatus = 'pending';

    public string $rejectionReason = '';

    public ?int $rejectingId = null;

    public bool $showRejectForm = false;

    public function mount(): void
    {
        Gate::authorize('manage_attendance');
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function approve(int $id): void
    {
        $request = RemoteWorkRequest::findOrFail($id);
        $request->update([
            'status' => RemoteWorkStatus::Approved,
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

        RemoteWorkRequest::findOrFail($this->rejectingId)->update([
            'status' => RemoteWorkStatus::Rejected,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => $this->rejectionReason,
        ]);

        $this->showRejectForm = false;
        $this->dispatch('notify', type: 'success', message: 'Pengajuan ditolak.');
    }

    public function render(): mixed
    {
        $requests = RemoteWorkRequest::with(['employee', 'reviewer'])
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->paginate(20);

        $pendingCount = RemoteWorkRequest::where('status', RemoteWorkStatus::Pending->value)->count();

        return view('livewire.admin.remote-work', compact('requests', 'pendingCount'));
    }
}
