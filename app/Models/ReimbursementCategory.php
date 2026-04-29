<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code',
    'name',
    'description',
    'max_amount',
    'requires_attachment',
    'is_active',
])]
class ReimbursementCategory extends Model
{
    protected function casts(): array
    {
        return [
            'max_amount' => 'decimal:2',
            'requires_attachment' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function reimbursements(): HasMany
    {
        return $this->hasMany(Reimbursement::class, 'category_id');
    }
}
