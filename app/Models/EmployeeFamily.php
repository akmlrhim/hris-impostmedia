<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'employee_id',
    'name',
    'relationship',
    'gender',
    'date_of_birth',
    'phone',
    'is_dependent',
])]
class EmployeeFamily extends Model
{
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_dependent' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
