<?php

namespace App\Models;

use App\Enums\OvertimeStatus;
use App\Traits\HasRouteHash;
use Database\Factories\OvertimeRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'employee_id',
    'started_at',
    'ended_at',
    'reason',
    'head_approval_path',
    'work_documentation',
    'status',
    'reviewed_by',
    'reviewed_at',
    'rejection_reason',
])]
class OvertimeRequest extends Model
{
    /** @use HasFactory<OvertimeRequestFactory> */
    use HasFactory, HasRouteHash;

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'status' => OvertimeStatus::class,
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

    public function durationMinutes(): int
    {
        return (int) $this->started_at->diffInMinutes($this->ended_at);
    }

    /** Human readable duration, e.g. "2j 30m". */
    public function durationLabel(): string
    {
        $minutes = $this->durationMinutes();
        $hours = intdiv($minutes, 60);
        $remainder = $minutes % 60;

        if ($hours === 0) {
            return "{$remainder}m";
        }

        return $remainder === 0 ? "{$hours}j" : "{$hours}j {$remainder}m";
    }

    /**
     * Work documentation stays editable by the employee after HR approval,
     * so an approved overtime can still be documented once the work is done.
     */
    public function documentationIsEditable(): bool
    {
        return $this->status !== OvertimeStatus::Rejected;
    }
}
