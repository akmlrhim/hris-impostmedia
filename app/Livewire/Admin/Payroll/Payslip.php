<?php

namespace App\Livewire\Admin\Payroll;

use App\Concerns\HandlesAdminActions;
use App\Models\Payroll;
use App\Models\PayrollItem;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Payslip extends Component
{
    use HandlesAdminActions;

    public Payroll $payroll;

    public bool $showItemForm = false;

    public string $itemType = 'earning';

    public string $itemName = '';

    public string $itemCalcType = 'fixed';

    public $itemPercent = 0;

    public $itemAmount = 0;

    public function mount(Payroll $payroll): void
    {
        Gate::authorize('manage_payroll');

        $this->payroll = $payroll->load(['employee', 'period', 'items']);
    }

    public function updatedItemCalcType(): void
    {
        $this->itemPercent = 0;
        $this->itemAmount = 0;
    }

    public function updatedItemPercent(): void
    {
        $percent = (float) $this->itemPercent;
        if ($this->itemCalcType === 'percent' && $percent > 0) {
            $this->itemAmount = round((float) $this->payroll->gross_salary * $percent / 100, 2);
        }
    }

    public function addItem(): void
    {
        if ($this->payroll->period->locked_at) {
            $this->toast('warning', 'Periode sudah dikunci, item tidak bisa diubah.');

            return;
        }

        $rules = [
            'itemType' => ['required', Rule::in(['earning', 'deduction'])],
            'itemName' => 'required|string|max:100',
            'itemCalcType' => ['required', Rule::in(['fixed', 'percent'])],
        ];

        if ($this->itemCalcType === 'percent') {
            $rules['itemPercent'] = 'required|numeric|min:0.01|max:100';
        } else {
            $rules['itemAmount'] = 'required|numeric|min:1';
        }

        $this->validate($rules);

        $this->safeAction(function () {
            $grossSalary = (float) $this->payroll->gross_salary;
            $notes = null;

            if ($this->itemCalcType === 'percent') {
                $percent = (float) $this->itemPercent;
                $this->itemAmount = round($grossSalary * $percent / 100, 2);
                $notes = $percent.'% dari gaji kotor';
            }

            $code = strtoupper(str_replace(' ', '_', $this->itemName));

            PayrollItem::create([
                'payroll_id' => $this->payroll->id,
                'component_code' => $code,
                'component_name' => $this->itemName,
                'type' => $this->itemType,
                'amount' => $this->itemAmount,
                'notes' => $notes,
            ]);

            $this->recalculate();
            $this->reset(['itemType', 'itemName', 'itemCalcType', 'itemPercent', 'itemAmount', 'showItemForm']);
            $this->itemType = 'earning';
            $this->itemCalcType = 'fixed';
            $this->toast('success', 'Item ditambahkan.');
        }, permission: 'manage_payroll', genericError: 'Gagal menambah item.');
    }

    public function removeItem(int $itemId): void
    {
        $this->safeAction(function () use ($itemId) {
            if ($this->payroll->period->locked_at) {
                $this->toast('warning', 'Periode sudah dikunci, item tidak bisa dihapus.');

                return;
            }

            $item = PayrollItem::find($itemId);

            if (! $item || $item->payroll_id !== $this->payroll->id) {
                return;
            }

            if ($item->component_code === 'BASIC') {
                $this->toast('warning', 'Gaji pokok tidak dapat dihapus.');

                return;
            }

            $item->delete();
            $this->recalculate();
            $this->toast('success', 'Item dihapus.');
        }, permission: 'manage_payroll', genericError: 'Gagal menghapus item.');
    }

    private function recalculate(): void
    {
        $items = PayrollItem::where('payroll_id', $this->payroll->id)->get();

        $totalEarnings = $items->where('type', 'earning')->sum('amount');
        $pph21 = $items->where('type', 'deduction')
            ->where('component_code', 'PPH21')
            ->sum('amount');
        $otherDeductions = $items->where('type', 'deduction')
            ->where('component_code', '!=', 'PPH21')
            ->sum('amount');

        $this->payroll->update([
            'total_earnings' => $totalEarnings,
            'total_deductions' => $otherDeductions,
            'total_tax_pph21' => $pph21,
            'gross_salary' => $totalEarnings,
            'net_salary' => max(0, $totalEarnings - $otherDeductions - $pph21 - (float) $this->payroll->total_bpjs),
        ]);

        $this->payroll->refresh();
        $this->payroll->load('items');
    }

    public function render(): mixed
    {
        $earnings = $this->payroll->items->where('type', 'earning');
        $deductions = $this->payroll->items->where('type', 'deduction');

        return view('livewire.admin.payroll.payslip', compact('earnings', 'deductions'))
            ->title('Slip Gaji - '.$this->payroll->employee->full_name);
    }
}
