<?php

namespace App\Models;

use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'employee_id', 'approver_id', 'overtime_date',
    'start_time', 'end_time', 'total_hours', 'reason',
    'status', 'approved_at', 'rejection_reason',
])]
class Overtime extends Model
{
    protected function casts(): array
    {
        return [
            'overtime_date' => 'date',
            'approved_at' => 'datetime',
            'status' => RequestStatus::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }
}
