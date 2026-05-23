<?php

namespace App\Models;

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
    'work_type',
    'phone',
    'nik',
    'email',
    'address',
    'last_education',
    'major_school_university',
    'bank_name',
    'bank_account_number',
    'bank_account_holder',
    'basic_salary',
    'emergency_contact_name',
    'emergency_contact_number',
    'contract_start_date',
    'contract_end_date',
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
            'contract_start_date' => 'date',
            'contract_end_date' => 'date',
            'basic_salary' => 'decimal:2',
            'work_type' => WorkType::class,
            'face_descriptor' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
