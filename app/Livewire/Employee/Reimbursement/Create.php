<?php

namespace App\Livewire\Employee\Reimbursement;

use App\Enums\RequestStatus;
use App\Models\Reimbursement;
use App\Models\ReimbursementCategory;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
class Create extends Component
{
    #[Validate('required|exists:reimbursement_categories,id')]
    public ?int $category_id = null;

    #[Validate('required|date|before_or_equal:today')]
    public string $expense_date = '';

    #[Validate('required|string|min:3|max:200')]
    public string $title = '';

    public string $description = '';

    #[Validate('required|numeric|min:1')]
    public float $amount = 0;

    public function mount(): void
    {
        $this->expense_date = now()->toDateString();
    }

    public function submit(): void
    {
        $this->validate();

        $employee = auth()->user()?->employee;
        abort_if(! $employee, 403);

        Reimbursement::create([
            'employee_id' => $employee->id,
            'category_id' => $this->category_id,
            'request_number' => 'RB-'.now()->format('Ymd').'-'.Str::upper(Str::random(4)),
            'expense_date' => $this->expense_date,
            'title' => $this->title,
            'description' => $this->description ?: null,
            'amount' => $this->amount,
            'status' => RequestStatus::Pending,
        ]);

        session()->flash('success', 'Klaim berhasil dikirim.');
        $this->redirect(route('mobile.reimbursement'), navigate: false);
    }

    public function render(): mixed
    {
        return view('livewire.employee.reimbursement.create', [
            'categories' => ReimbursementCategory::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
