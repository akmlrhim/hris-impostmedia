<?php

namespace App\Models;

use App\Enums\EmploymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id', 'job_position_id', 'manager_id',
    'employee_number', 'full_name', 'nickname', 'gender', 'date_of_birth',
    'place_of_birth', 'religion', 'marital_status', 'blood_type',
    'nik_ktp', 'npwp', 'passport_number', 'bpjs_kesehatan', 'bpjs_ketenagakerjaan',
    'phone', 'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation',
    'address', 'city', 'province', 'postal_code',
    'employment_status', 'join_date', 'probation_end_date', 'contract_end_date',
    'resign_date', 'resign_reason',
    'bank_name', 'bank_account_number', 'bank_account_holder',
    'ptkp_status', 'basic_salary',
    'avatar_path', 'is_active',
])]
class Employee extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'join_date' => 'date',
            'probation_end_date' => 'date',
            'contract_end_date' => 'date',
            'resign_date' => 'date',
            'basic_salary' => 'decimal:2',
            'employment_status' => EmploymentStatus::class,
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class, 'job_position_id');
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

    public function workSchedules(): HasMany
    {
        return $this->hasMany(WorkSchedule::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function reimbursements(): HasMany
    {
        return $this->hasMany(Reimbursement::class);
    }

    public function overtimes(): HasMany
    {
        return $this->hasMany(Overtime::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function families(): HasMany
    {
        return $this->hasMany(EmployeeFamily::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }
}
