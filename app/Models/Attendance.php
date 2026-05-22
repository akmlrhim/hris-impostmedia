<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'employee_id', 'attendance_date',
    'check_in_at', 'check_out_at',
    'check_in_latitude', 'check_in_longitude', 'check_out_latitude', 'check_out_longitude',
    'check_in_photo_path', 'check_out_photo_path',
    'check_in_address', 'check_out_address',
    'status', 'late_minutes', 'work_minutes', 'notes',
])]
class Attendance extends Model
{
    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
            'status' => AttendanceStatus::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }
}
