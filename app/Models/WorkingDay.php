<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * One row per weekday, flagging whether the company works that day.
 * Read through WorkScheduleService rather than querying this directly.
 */
#[Fillable(['weekday', 'is_working'])]
class WorkingDay extends Model
{
    protected function casts(): array
    {
        return [
            'weekday' => 'integer',
            'is_working' => 'boolean',
        ];
    }
}
