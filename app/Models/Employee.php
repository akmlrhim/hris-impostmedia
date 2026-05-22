<?php

namespace App\Models;

use App\Enums\EmploymentStatus;
use App\Enums\WorkType;
use App\Traits\HasRouteHash;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'employee_number',
    'full_name',
    'nickname',
    'gender',
    'date_of_birth',
    'place_of_birth',
    'religion',
    'phone',
    'address',
    'city',
    'province',
    'postal_code',
    'employment_status',
    'work_type',
    'join_date',
    'probation_end_date',
    'contract_end_date',
    'bank_name',
    'bank_account_number',
    'bank_account_holder',
    'basic_salary',
    'avatar_path',
    'face_descriptor',
    'is_active',
])]
class Employee extends Model
{
    use HasRouteHash, SoftDeletes;

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'join_date' => 'date',
            'probation_end_date' => 'date',
            'contract_end_date' => 'date',
            'basic_salary' => 'decimal:2',
            'employment_status' => EmploymentStatus::class,
            'work_type' => WorkType::class,
            'face_descriptor' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(self::class, 'manager_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }
}
