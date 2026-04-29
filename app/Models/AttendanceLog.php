<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'attendance_id', 'employee_id', 'event', 'event_at',
    'latitude', 'longitude', 'photo_path', 'ip_address', 'device_info',
])]
class AttendanceLog extends Model
{
    protected function casts(): array
    {
        return ['event_at' => 'datetime'];
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
