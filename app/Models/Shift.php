<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code', 'name', 'start_time', 'end_time', 'break_start', 'break_end',
    'late_tolerance_minutes', 'early_leave_tolerance_minutes',
    'is_overnight', 'is_active',
])]
class Shift extends Model
{
    protected function casts(): array
    {
        return [
            'is_overnight' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(WorkSchedule::class);
    }
}
