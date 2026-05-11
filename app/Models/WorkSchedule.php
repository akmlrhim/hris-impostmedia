<?php

namespace App\Models;

use App\Enums\WorkType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['employee_id', 'shift_id', 'work_date', 'day_type', 'work_type', 'notes'])]
class WorkSchedule extends Model
{
    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'work_type' => WorkType::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
