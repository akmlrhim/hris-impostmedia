<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'employee_id', 'leave_type_id', 'year',
    'quota_days', 'used_days', 'carried_over_days', 'expires_at',
])]
class LeaveBalance extends Model
{
    protected function casts(): array
    {
        return [
            'quota_days' => 'decimal:2',
            'used_days' => 'decimal:2',
            'carried_over_days' => 'decimal:2',
            'expires_at' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function getRemainingDaysAttribute(): float
    {
        return (float) $this->quota_days + (float) $this->carried_over_days - (float) $this->used_days;
    }
}
