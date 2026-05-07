<?php

namespace App\Models;

use App\Traits\HasRouteHash;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code', 'year', 'month', 'start_date', 'end_date',
    'payment_date', 'status', 'processed_at', 'locked_at',
])]
class PayrollPeriod extends Model
{
    use HasRouteHash;

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'payment_date' => 'date',
            'processed_at' => 'datetime',
            'locked_at' => 'datetime',
        ];
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }
}
