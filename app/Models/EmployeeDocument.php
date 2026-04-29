<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['employee_id', 'type', 'name', 'file_path', 'issued_date', 'expired_date', 'notes'])]
class EmployeeDocument extends Model
{
    protected function casts(): array
    {
        return [
            'issued_date' => 'date',
            'expired_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
