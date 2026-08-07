<?php

namespace App\Livewire\Admin;

use App\Concerns\HandlesAdminActions;
use App\Enums\OvertimeStatus;
use App\Models\OvertimeRequest;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Pengajuan Lembur')]
#[Layout('components.layouts.admin')]
class Overtime extends Component
{
    use HandlesAdminActions, WithPagination;

    public string $filterStatus = 'pending';

    public string $rejectionReason = '';

    public ?int $rejectingId = null;

    public bool $showRejectForm = false;

    public ?int $viewingId = null;

    public bool $showDetail = false;

    public function mount(): void
    {
        Gate::authorize('manage_overtime');
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function approve(int $id): void
    {
        $this->safeAction(function () use ($id) {
            $request = OvertimeRequest::with('employee')->findOrFail($id);

            if ($request->status !== OvertimeStatus::Pending) {
                $this->toast('warning', 'Pengajuan ini sudah diproses.');

                return;
            }

            $request->update([
                'status' => OvertimeStatus::Approved,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);

            $range = $request->started_at->format('d M Y H:i').' - '.$request->ended_at->format('H:i');
            $this->logActivity(
                'overtime.approved',
                "Menyetujui lembur {$request->employee?->full_name} ({$range})",
                $request,
                [
                    'start' => $request->started_at->toIso8601String(),
                    'end' => $request->ended_at->toIso8601String(),
                    'duration_minutes' => $request->durationMinutes(),
                ],
            );
            $this->toast('success', 'Pengajuan lembur disetujui.');
        }, permission: 'manage_overtime', genericError: 'Gagal menyetujui pengajuan.');
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
            $request = OvertimeRequest::with('employee')->findOrFail($this->rejectingId);

            if ($request->status !== OvertimeStatus::Pending) {
                $this->toast('warning', 'Pengajuan ini sudah diproses.');

                return;
            }

            $request->update([
                'status' => OvertimeStatus::Rejected,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'rejection_reason' => $this->rejectionReason,
            ]);

            $range = $request->started_at->format('d M Y H:i').' - '.$request->ended_at->format('H:i');
            $this->logActivity(
                'overtime.rejected',
                "Menolak lembur {$request->employee?->full_name} ({$range})",
                $request,
                [
                    'reason' => $this->rejectionReason,
                    'start' => $request->started_at->toIso8601String(),
                    'end' => $request->ended_at->toIso8601String(),
                ],
            );
            $this->showRejectForm = false;
            $this->toast('success', 'Pengajuan lembur ditolak.');
        }, permission: 'manage_overtime', genericError: 'Gagal menolak pengajuan.');
    }

    public function openDetail(int $id): void
    {
        $this->viewingId = $id;
        $this->showDetail = true;
    }

    public function render(): mixed
    {
        $requests = OvertimeRequest::with(['employee', 'reviewer'])
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->paginate(20);

        $pendingCount = OvertimeRequest::where('status', OvertimeStatus::Pending->value)->count();

        $viewing = $this->viewingId
            ? OvertimeRequest::with('employee')->find($this->viewingId)
            : null;

        return view('livewire.admin.overtime', compact('requests', 'pendingCount', 'viewing'));
    }
}
