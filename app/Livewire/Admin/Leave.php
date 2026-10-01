<?php

namespace App\Livewire\Admin;

use App\Concerns\HandlesAdminActions;
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
    use HandlesAdminActions, WithPagination;

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
        $this->safeAction(function () use ($id) {
            $request = LeaveRequest::with('employee')->findOrFail($id);
            $request->update([
                'status' => LeaveStatus::Approved,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);
            $range = $request->start_date->format('d M Y H:i').' - '.$request->end_date->format('d M Y H:i');
            $this->logActivity(
                'leave.approved',
                "Menyetujui {$request->type?->label()} {$request->employee?->full_name} ({$range})",
                $request,
                ['start' => $request->start_date->toIso8601String(), 'end' => $request->end_date->toIso8601String()],
            );
            $this->toast('success', 'Pengajuan disetujui.');
        }, permission: 'manage_leave', genericError: 'Gagal menyetujui pengajuan.');
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
            $request = LeaveRequest::with('employee')->findOrFail($this->rejectingId);
            $request->update([
                'status' => LeaveStatus::Rejected,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'rejection_reason' => $this->rejectionReason,
            ]);

            $range = $request->start_date->format('d M Y H:i').' - '.$request->end_date->format('d M Y H:i');
            $this->logActivity(
                'leave.rejected',
                "Menolak {$request->type?->label()} {$request->employee?->full_name} ({$range})",
                $request,
                ['reason' => $this->rejectionReason, 'start' => $request->start_date->toIso8601String(), 'end' => $request->end_date->toIso8601String()],
            );
            $this->showRejectForm = false;
            $this->toast('success', 'Pengajuan ditolak.');
        }, permission: 'manage_leave', genericError: 'Gagal menolak pengajuan.');
    }

    public function delete(int $id): void
    {
        $this->safeAction(function () use ($id) {
            $request = LeaveRequest::findOrFail($id);

            if ($request->status !== LeaveStatus::Pending) {
                $this->toast('warning', 'Pengajuan yang sudah diproses tidak bisa dihapus.');

                return;
            }

            $request->delete();
            $this->toast('success', 'Pengajuan cuti/izin dihapus.');
        }, permission: 'manage_leave', genericError: 'Gagal menghapus pengajuan.');
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
