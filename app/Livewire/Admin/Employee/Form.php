<?php

namespace App\Livewire\Admin\Employee;

use App\Enums\EmploymentStatus;
use App\Enums\UserRole;
use App\Models\Employee;
use App\Models\JobPosition;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Form extends Component
{
    public ?int $employeeId = null;

    public string $employee_number = '';

    public string $full_name = '';

    public string $email = '';

    public string $phone = '';

    public string $gender = 'male';

    public ?string $date_of_birth = null;

    public ?int $job_position_id = null;

    public string $employment_status = 'permanent';

    public ?string $join_date = null;

    public float $basic_salary = 0;

    public string $bank_name = '';

    public string $bank_account_number = '';

    public bool $is_active = true;

    // Inline new position
    public bool $showNewPosition = false;

    public string $new_position_name = '';

    public function mount(?int $employee = null): void
    {
        if ($employee) {
            $emp = Employee::findOrFail($employee);
            $this->employeeId = $emp->id;
            $this->employee_number = $emp->employee_number;
            $this->full_name = $emp->full_name;
            $this->email = $emp->user?->email ?? '';
            $this->phone = (string) $emp->phone;
            $this->gender = (string) $emp->gender;
            $this->date_of_birth = $emp->date_of_birth?->format('Y-m-d');
            $this->job_position_id = $emp->job_position_id;
            $this->employment_status = $emp->employment_status?->value ?? 'permanent';
            $this->join_date = $emp->join_date?->format('Y-m-d');
            $this->basic_salary = (float) $emp->basic_salary;
            $this->bank_name = (string) $emp->bank_name;
            $this->bank_account_number = (string) $emp->bank_account_number;
            $this->is_active = (bool) $emp->is_active;
        } else {
            $this->join_date = now()->toDateString();
        }
    }

    public function addPosition(): void
    {
        $this->validate([
            'new_position_name' => 'required|string|min:2|max:100',
        ]);

        $code = strtoupper(Str::slug($this->new_position_name, ''));
        if (strlen($code) > 28) {
            $code = substr($code, 0, 28);
        }
        $base = $code;
        $i = 1;
        while (JobPosition::where('code', $code)->exists()) {
            $code = substr($base, 0, 26).'-'.$i++;
        }

        $pos = JobPosition::create([
            'code' => $code,
            'name' => $this->new_position_name,
            'is_active' => true,
        ]);

        $this->job_position_id = $pos->id;
        $this->new_position_name = '';
        $this->showNewPosition = false;

        $this->dispatch('notify', type: 'success', message: 'Posisi baru ditambahkan.');
    }

    public function save(): void
    {
        $rules = [
            'employee_number' => [
                'required',
                'string',
                'max:30',
                Rule::unique('employees')->ignore($this->employeeId),
            ],
            'full_name' => 'required|string|max:200',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore(
                    Employee::find($this->employeeId)?->user_id
                ),
            ],
            'gender' => 'required|in:male,female',
            'employment_status' => 'required',
            'join_date' => 'required|date',
            'basic_salary' => 'required|numeric|min:0',
            'job_position_id' => 'nullable|exists:job_positions,id',
        ];
        $this->validate($rules);

        DB::transaction(function (): void {
            if ($this->employeeId) {
                $emp = Employee::findOrFail($this->employeeId);
                $emp->user?->update([
                    'name' => $this->full_name,
                    'email' => $this->email,
                ]);
            } else {
                $user = User::create([
                    'name' => $this->full_name,
                    'email' => $this->email,
                    'password' => Hash::make('password'),
                    'role' => UserRole::Employee,
                    'email_verified_at' => now(),
                ]);
                $emp = new Employee(['user_id' => $user->id]);
            }

            $emp->fill([
                'employee_number' => $this->employee_number,
                'full_name' => $this->full_name,
                'phone' => $this->phone ?: null,
                'gender' => $this->gender,
                'date_of_birth' => $this->date_of_birth ?: null,
                'job_position_id' => $this->job_position_id,
                'employment_status' => $this->employment_status,
                'join_date' => $this->join_date,
                'basic_salary' => $this->basic_salary,
                'bank_name' => $this->bank_name ?: null,
                'bank_account_number' => $this->bank_account_number ?: null,
                'is_active' => $this->is_active,
            ])->save();

            // Provision leave balances if new
            if (! $this->employeeId) {
                $year = now()->year;
                foreach (LeaveType::all() as $type) {
                    $emp->leaveBalances()->firstOrCreate(
                        ['leave_type_id' => $type->id, 'year' => $year],
                        ['quota_days' => $type->default_quota_days, 'used_days' => 0]
                    );
                }
            }
        });

        session()->flash('success', $this->employeeId ? 'Karyawan diperbarui.' : 'Karyawan ditambahkan.');
        $this->redirect(route('admin.employees'), navigate: false);
    }

    public function render(): mixed
    {
        return view('livewire.admin.employee.form', [
            'positions' => JobPosition::where('is_active', true)->orderBy('name')->get(),
            'employmentStatuses' => EmploymentStatus::cases(),
        ]);
    }
}
