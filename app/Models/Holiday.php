<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['date', 'holiday_name', 'description'])]
class Holiday extends Model
{
    protected $table = 'days_off';

    protected function casts(): array
    {
        return ['date' => 'date:Y-m-d'];
    }

    public static function isHoliday(string $date): bool
    {
        return self::where('date', $date)->exists();
    }
}
