<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'job_level_id', 'code', 'name', 'description', 'is_active',
])]
class JobPosition extends Model
{
    protected $table = 'job_positions';

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(JobLevel::class, 'job_level_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
