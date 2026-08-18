<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use App\Enums\WorkType;
use Carbon\Carbon;
use Database\Factories\AttendanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'employee_id',
    'attendance_date',
    'check_in_at',
    'check_out_at',
    'check_in_latitude',
    'check_in_longitude',
    'check_out_latitude',
    'check_out_longitude',
    'check_in_photo_path',
    'check_out_photo_path',
    'check_in_address',
    'check_out_address',
    'status',
    'work_type',
    'late_minutes',
    'work_minutes',
    'notes',
    'timezone',
])]
class Attendance extends Model
{
    /** @use HasFactory<AttendanceFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'status' => AttendanceStatus::class,
            'work_type' => WorkType::class,
        ];
    }

    /** Parse check_in_at in the record's stored timezone so Carbon UTC math is correct. */
    protected function checkInAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value
                ? Carbon::parse($value, $this->timezone ?: config('app.timezone'))
                : null,
        );
    }

    /** Parse check_out_at in the record's stored timezone so Carbon UTC math is correct. */
    protected function checkOutAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value
                ? Carbon::parse($value, $this->timezone ?: config('app.timezone'))
                : null,
        );
    }

    /**
     * Indonesian abbreviation for the timezone the times were recorded in.
     * check_in_at / check_out_at are stored as wall-clock time in that zone,
     * so the label is what makes an 08:00 unambiguous across regions.
     */
    protected function timezoneLabel(): Attribute
    {
        return Attribute::make(
            get: fn (): string => match ($this->timezone ?: config('app.timezone')) {
                'Asia/Makassar' => 'WITA',
                'Asia/Jayapura' => 'WIT',
                default => 'WIB',
            },
        );
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
