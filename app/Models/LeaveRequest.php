<?php

namespace App\Models;

use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'employee_id',
    'leave_type_id',
    'approver_id',
    'request_number',
    'start_date',
    'end_date',
    'total_days',
    'is_half_day',
    'half_day_period',
    'reason',
    'attachment_path',
    'status',
    'approved_at',
    'rejection_reason',
    'cancelled_at',
])]
class LeaveRequest extends Model
{
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'total_days' => 'decimal:2',
            'is_half_day' => 'boolean',
            'approved_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'status' => RequestStatus::class,
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }
}
