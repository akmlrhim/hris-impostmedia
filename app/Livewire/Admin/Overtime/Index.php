<?php

namespace App\Livewire\Admin\Overtime;

use App\Enums\RequestStatus;
use App\Models\Overtime;
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
        $ot = Overtime::findOrFail($id);
        $ot->update([
            'status' => RequestStatus::Approved,
            'approver_id' => auth()->user()?->employee?->id,
            'approved_at' => now(),
        ]);
    }

    public function reject(int $id): void
    {
        $ot = Overtime::findOrFail($id);
        $ot->update([
            'status' => RequestStatus::Rejected,
            'approver_id' => auth()->user()?->employee?->id,
        ]);
    }

    public function render(): mixed
    {
        $overtimes = Overtime::with(['employee.position', 'approver'])
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.overtime.index', compact('overtimes'));
    }
}
