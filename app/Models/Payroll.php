<?php

namespace App\Models;

use App\Traits\HasRouteHash;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'payroll_period_id', 'employee_id',
    'basic_salary', 'total_earnings', 'total_deductions',
    'total_bpjs', 'total_tax_pph21', 'gross_salary', 'net_salary',
    'working_days', 'present_days', 'absent_days', 'leave_days',
    'overtime_minutes', 'overtime_amount', 'status', 'notes',
])]
class Payroll extends Model
{
    use HasRouteHash;

    protected function casts(): array
    {
        return [
            'basic_salary' => 'decimal:2',
            'total_earnings' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'total_bpjs' => 'decimal:2',
            'total_tax_pph21' => 'decimal:2',
            'gross_salary' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'overtime_amount' => 'decimal:2',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function earnings()
    {
        return $this->items()->where('type', 'earning');
    }
}
