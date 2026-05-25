<?php

namespace App\Models;

use App\Observers\HolidayObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(HolidayObserver::class)]
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
