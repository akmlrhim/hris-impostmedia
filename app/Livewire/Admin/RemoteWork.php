<?php

namespace App\Livewire\Admin;

use App\Concerns\HandlesAdminActions;
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
    use HandlesAdminActions, WithPagination;

    public string $filterStatus = 'pending';

    public string $rejectionReason = '';

    public ?int $rejectingId = null;

    public bool $showRejectForm = false;

    public function mount(): void
    {
        Gate::authorize('manage_remote_work');
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function approve(int $id): void
    {
        $this->safeAction(function () use ($id) {
            $request = RemoteWorkRequest::with('employee')->findOrFail($id);
            $request->update([
                'status' => RemoteWorkStatus::Approved,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);
            $range = $request->start_date->format('d M Y H:i').' - '.$request->end_date->format('d M Y H:i');
            $this->logActivity(
                'remote_work.approved',
                "Menyetujui {$request->work_type?->shortLabel()} {$request->employee?->full_name} ({$range})",
                $request,
                ['start' => $request->start_date->toIso8601String(), 'end' => $request->end_date->toIso8601String()],
            );
            $this->toast('success', 'Pengajuan disetujui.');
        }, permission: 'manage_remote_work', genericError: 'Gagal menyetujui pengajuan.');
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

        $this->safeAction(function () {
            $request = RemoteWorkRequest::with('employee')->findOrFail($this->rejectingId);
            $request->update([
                'status' => RemoteWorkStatus::Rejected,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'rejection_reason' => $this->rejectionReason,
            ]);

            $range = $request->start_date->format('d M Y H:i').' - '.$request->end_date->format('d M Y H:i');
            $this->logActivity(
                'remote_work.rejected',
                "Menolak {$request->work_type?->shortLabel()} {$request->employee?->full_name} ({$range})",
                $request,
                ['reason' => $this->rejectionReason, 'start' => $request->start_date->toIso8601String(), 'end' => $request->end_date->toIso8601String()],
            );
            $this->showRejectForm = false;
            $this->toast('success', 'Pengajuan ditolak.');
        }, permission: 'manage_remote_work', genericError: 'Gagal menolak pengajuan.');
    }

    public function delete(int $id): void
    {
        $this->safeAction(function () use ($id) {
            $request = RemoteWorkRequest::findOrFail($id);

            if ($request->status !== RemoteWorkStatus::Pending) {
                $this->toast('warning', 'Pengajuan yang sudah diproses tidak bisa dihapus.');

                return;
            }

            $request->delete();
            $this->toast('success', 'Pengajuan WFA dihapus.');
        }, permission: 'manage_remote_work', genericError: 'Gagal menghapus pengajuan.');
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
