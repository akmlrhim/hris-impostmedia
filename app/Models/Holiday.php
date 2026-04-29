<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['date', 'name', 'description', 'is_national'])]
class Holiday extends Model
{
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_national' => 'boolean',
        ];
    }
}
