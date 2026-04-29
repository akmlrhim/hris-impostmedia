<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'code', 'name', 'type', 'calculation_type',
    'default_amount', 'is_taxable', 'is_bpjs_subject', 'is_active',
])]
class PayrollComponent extends Model
{
    protected function casts(): array
    {
        return [
            'default_amount' => 'decimal:2',
            'is_taxable' => 'boolean',
            'is_bpjs_subject' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
