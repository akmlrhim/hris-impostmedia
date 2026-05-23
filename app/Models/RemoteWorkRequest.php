<?php

namespace App\Models;

use App\Enums\RemoteWorkStatus;
use App\Enums\WorkType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'employee_id',
    'start_date',
    'end_date',
    'work_type',
    'reason',
    'status',
    'reviewed_by',
    'reviewed_at',
    'rejection_reason',
])]
class RemoteWorkRequest extends Model
{
    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'reviewed_at' => 'datetime',
            'work_type' => WorkType::class,
            'status' => RemoteWorkStatus::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public static function approvedFor(int $employeeId, string $date): ?self
    {
        return static::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->first();
    }
}
