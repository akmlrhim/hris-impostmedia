<?php

namespace App\Livewire\Admin\Reimbursement;

use App\Enums\RequestStatus;
use App\Models\Reimbursement;
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
        Reimbursement::findOrFail($id)->update([
            'status' => RequestStatus::Approved,
            'approver_id' => auth()->user()?->employee?->id,
            'approved_at' => now(),
        ]);
    }

    public function reject(int $id): void
    {
        Reimbursement::findOrFail($id)->update([
            'status' => RequestStatus::Rejected,
            'approver_id' => auth()->user()?->employee?->id,
        ]);
    }

    public function markPaid(int $id): void
    {
        Reimbursement::findOrFail($id)->update([
            'status' => RequestStatus::Paid,
            'paid_at' => now(),
        ]);
    }

    public function render(): mixed
    {
        $items = Reimbursement::with(['employee.position', 'category'])
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.reimbursement.index', compact('items'));
    }
}
