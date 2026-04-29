<?php

namespace Database\Seeders;

use App\Enums\EmploymentStatus;
use App\Enums\UserRole;
use App\Models\Employee;
use App\Models\JobLevel;
use App\Models\JobPosition;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\PayrollComponent;
use App\Models\ReimbursementCategory;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $position = $this->seedOrganization();
            $this->seedShiftsAndLeaveTypes();
            $this->seedReimbursementCategories();
            $this->seedPayrollComponents();
            $this->seedUsers($position);
        });
    }

    private function seedOrganization(): JobPosition
    {
        $levels = [
            ['code' => 'STAFF', 'name' => 'Staff', 'rank' => 1],
            ['code' => 'SPV', 'name' => 'Supervisor', 'rank' => 2],
            ['code' => 'MGR', 'name' => 'Manager', 'rank' => 3],
            ['code' => 'DIR', 'name' => 'Director', 'rank' => 4],
        ];
        $levelMap = [];
        foreach ($levels as $l) {
            $levelMap[$l['code']] = JobLevel::firstOrCreate(['code' => $l['code']], $l);
        }

        $positions = [
            ['code' => 'DEV', 'name' => 'Software Developer', 'level' => 'STAFF'],
            ['code' => 'IT-MGR', 'name' => 'IT Manager', 'level' => 'MGR'],
            ['code' => 'HR-OFF', 'name' => 'HR Officer', 'level' => 'STAFF'],
            ['code' => 'HR-MGR', 'name' => 'HR Manager', 'level' => 'MGR'],
            ['code' => 'ACC', 'name' => 'Accountant', 'level' => 'STAFF'],
            ['code' => 'MKT', 'name' => 'Marketing Officer', 'level' => 'STAFF'],
        ];
        $positionMap = [];
        foreach ($positions as $p) {
            $positionMap[$p['code']] = JobPosition::firstOrCreate(
                ['code' => $p['code']],
                [
                    'name' => $p['name'],
                    'job_level_id' => $levelMap[$p['level']]->id,
                ]
            );
        }

        return $positionMap['DEV'];
    }

    private function seedShiftsAndLeaveTypes(): void
    {
        $shifts = [
            ['code' => 'REG', 'name' => 'Regular 09-18', 'start_time' => '09:00', 'end_time' => '18:00', 'break_start' => '12:00', 'break_end' => '13:00'],
            ['code' => 'EARLY', 'name' => 'Early 07-16', 'start_time' => '07:00', 'end_time' => '16:00', 'break_start' => '12:00', 'break_end' => '13:00'],
            ['code' => 'LATE', 'name' => 'Late 13-22', 'start_time' => '13:00', 'end_time' => '22:00', 'break_start' => '18:00', 'break_end' => '19:00'],
        ];
        foreach ($shifts as $s) {
            Shift::firstOrCreate(['code' => $s['code']], $s);
        }

        $leaveTypes = [
            ['code' => 'ANN', 'name' => 'Cuti Tahunan', 'default_quota_days' => 12, 'is_paid' => true, 'color' => '#3b82f6'],
            ['code' => 'SICK', 'name' => 'Cuti Sakit', 'default_quota_days' => 14, 'is_paid' => true, 'requires_attachment' => true, 'color' => '#ef4444'],
            ['code' => 'PERS', 'name' => 'Izin Personal', 'default_quota_days' => 3, 'is_paid' => true, 'color' => '#a855f7'],
            ['code' => 'MARR', 'name' => 'Cuti Menikah', 'default_quota_days' => 3, 'is_paid' => true, 'color' => '#ec4899'],
            ['code' => 'MAT', 'name' => 'Cuti Melahirkan', 'default_quota_days' => 90, 'is_paid' => true, 'color' => '#f59e0b'],
            ['code' => 'UNPAID', 'name' => 'Cuti Tanpa Bayar', 'default_quota_days' => 0, 'is_paid' => false, 'color' => '#64748b'],
        ];
        foreach ($leaveTypes as $l) {
            LeaveType::firstOrCreate(['code' => $l['code']], $l);
        }
    }

    private function seedReimbursementCategories(): void
    {
        $cats = [
            ['code' => 'TRANS', 'name' => 'Transportasi', 'max_amount' => 500000],
            ['code' => 'MEAL', 'name' => 'Makan', 'max_amount' => 200000],
            ['code' => 'MED', 'name' => 'Kesehatan', 'max_amount' => 5000000],
            ['code' => 'COMM', 'name' => 'Komunikasi', 'max_amount' => 300000],
            ['code' => 'OTHR', 'name' => 'Lainnya', 'max_amount' => null],
        ];
        foreach ($cats as $c) {
            ReimbursementCategory::firstOrCreate(['code' => $c['code']], $c);
        }
    }

    private function seedPayrollComponents(): void
    {
        $comps = [
            // System components (computed by generator)
            ['code' => 'BASIC', 'name' => 'Gaji Pokok', 'type' => 'earning', 'is_taxable' => true, 'is_bpjs_subject' => true],
            ['code' => 'OT', 'name' => 'Lembur', 'type' => 'earning', 'is_taxable' => true],
            ['code' => 'ABSENT', 'name' => 'Potongan Tidak Hadir', 'type' => 'deduction'],
            ['code' => 'PPH21', 'name' => 'PPh 21', 'type' => 'tax'],
            // Default tunjangan / potongan (admin can edit/delete/add more)
            ['code' => 'TRANS-ALW', 'name' => 'Tunjangan Transport', 'type' => 'earning', 'default_amount' => 500000],
            ['code' => 'MEAL-ALW', 'name' => 'Tunjangan Makan', 'type' => 'earning', 'default_amount' => 600000],
            ['code' => 'BPJS-KES', 'name' => 'BPJS Kesehatan', 'type' => 'deduction', 'calculation_type' => 'percentage', 'default_amount' => 1, 'is_bpjs_subject' => true],
            ['code' => 'BPJS-JHT', 'name' => 'BPJS Ketenagakerjaan (JHT)', 'type' => 'deduction', 'calculation_type' => 'percentage', 'default_amount' => 2, 'is_bpjs_subject' => true],
        ];
        foreach ($comps as $c) {
            PayrollComponent::firstOrCreate(['code' => $c['code']], $c);
        }
    }

    private function seedUsers(JobPosition $position): void
    {
        // Super Admin
        $admin = User::firstOrCreate(['email' => 'admin@company.test'], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'role' => UserRole::SuperAdmin,
            'email_verified_at' => now(),
        ]);

        // HR
        $hrUser = User::firstOrCreate(['email' => 'hr@company.test'], [
            'name' => 'HR Manager',
            'password' => Hash::make('password'),
            'role' => UserRole::HR,
            'email_verified_at' => now(),
        ]);

        // Sample employee
        $empUser = User::firstOrCreate(['email' => 'budi@company.test'], [
            'name' => 'Budi Santoso',
            'password' => Hash::make('password'),
            'role' => UserRole::Employee,
            'email_verified_at' => now(),
        ]);

        $employee = Employee::firstOrCreate(
            ['user_id' => $empUser->id],
            [
                'job_position_id' => $position->id,
                'employee_number' => 'EMP-0001',
                'full_name' => 'Budi Santoso',
                'nickname' => 'Budi',
                'gender' => 'male',
                'date_of_birth' => '1995-04-12',
                'religion' => 'Islam',
                'marital_status' => 'single',
                'phone' => '081234567890',
                'address' => 'Jl. Mawar No. 10',
                'city' => 'Jakarta',
                'employment_status' => EmploymentStatus::Permanent,
                'join_date' => '2023-01-15',
                'bank_name' => 'BCA',
                'bank_account_number' => '1234567890',
                'bank_account_holder' => 'Budi Santoso',
                'ptkp_status' => 'TK/0',
                'basic_salary' => 8000000,
            ]
        );

        // Leave balances for current year
        $year = now()->year;
        foreach (LeaveType::all() as $type) {
            LeaveBalance::firstOrCreate(
                ['employee_id' => $employee->id, 'leave_type_id' => $type->id, 'year' => $year],
                ['quota_days' => $type->default_quota_days, 'used_days' => 0]
            );
        }
    }
}
