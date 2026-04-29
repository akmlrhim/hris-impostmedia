<?php

namespace App\Livewire\Admin\Payroll;

use App\Models\PayrollComponent;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Components extends Component
{
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $code = '';

    public string $name = '';

    public string $type = 'earning';

    public string $calculation_type = 'fixed';

    public float $default_amount = 0;

    public bool $is_taxable = true;

    public bool $is_bpjs_subject = false;

    public bool $is_active = true;

    public function open(?int $id = null): void
    {
        $this->reset(['code', 'name', 'type', 'calculation_type', 'default_amount', 'is_taxable', 'is_bpjs_subject', 'is_active', 'editingId']);
        $this->resetValidation();
        $this->type = 'earning';
        $this->calculation_type = 'fixed';
        $this->is_taxable = true;
        $this->is_active = true;

        if ($id) {
            $c = PayrollComponent::findOrFail($id);
            $this->editingId = $id;
            $this->code = $c->code;
            $this->name = $c->name;
            $this->type = $c->type;
            $this->calculation_type = $c->calculation_type;
            $this->default_amount = (float) $c->default_amount;
            $this->is_taxable = (bool) $c->is_taxable;
            $this->is_bpjs_subject = (bool) $c->is_bpjs_subject;
            $this->is_active = (bool) $c->is_active;
        }

        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('payroll_components')->ignore($this->editingId)],
            'name' => 'required|string|max:100',
            'type' => 'required|in:earning,deduction,tax',
            'calculation_type' => 'required|in:fixed,percentage',
            'default_amount' => 'required|numeric|min:0',
        ]);

        $data = [
            'code' => strtoupper($this->code),
            'name' => $this->name,
            'type' => $this->type,
            'calculation_type' => $this->calculation_type,
            'default_amount' => $this->default_amount,
            'is_taxable' => $this->is_taxable,
            'is_bpjs_subject' => $this->is_bpjs_subject,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            PayrollComponent::findOrFail($this->editingId)->update($data);
            $this->dispatch('notify', type: 'success', message: 'Komponen diperbarui.');
        } else {
            PayrollComponent::create($data);
            $this->dispatch('notify', type: 'success', message: 'Komponen ditambahkan.');
        }

        $this->showForm = false;
        $this->editingId = null;
    }

    public function delete(int $id): void
    {
        // Reserved system codes that the generator depends on
        $reserved = ['BASIC', 'OT', 'ABSENT', 'PPH21'];
        $c = PayrollComponent::findOrFail($id);

        if (in_array($c->code, $reserved, true)) {
            $this->dispatch('notify', type: 'warning', message: "Komponen sistem {$c->code} tidak bisa dihapus.");

            return;
        }

        $c->delete();
        $this->dispatch('notify', type: 'success', message: 'Komponen dihapus.');
    }

    public function render(): mixed
    {
        return view('livewire.admin.payroll.components', [
            'components' => PayrollComponent::orderByRaw("FIELD(type,'earning','deduction','tax')")
                ->orderBy('code')
                ->get(),
        ]);
    }
}
