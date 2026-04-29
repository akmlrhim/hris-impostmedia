<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'rank'])]
class JobLevel extends Model
{
    public function positions(): HasMany
    {
        return $this->hasMany(JobPosition::class);
    }
}
