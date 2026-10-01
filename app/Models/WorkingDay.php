<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

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
