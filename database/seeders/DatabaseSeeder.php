<?php

namespace Database\Seeders;

use App\Enums\EmploymentStatus;
use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\JobLevel;
use App\Models\JobPosition;
use App\Models\Payroll;
use App\Models\PayrollComponent;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\Shift;
use App\Models\User;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $positionMap = $this->seedOrganization();
            $this->seedShifts();
            $this->seedPayrollComponents();

            $employees = $this->seedUsersAndEmployees($positionMap);
            $this->seedWorkSchedules($employees);
            $this->seedHolidays();
            $this->seedAttendanceAndLogs($employees);
            $this->seedPayrollData($employees);
            $this->seedAnnouncements();
        });
    }

    private function seedOrganization(): array
    {
        $levels = [
            ['code' => 'STAFF', 'name' => 'Staff', 'rank' => 1],
            ['code' => 'SPV', 'name' => 'Supervisor', 'rank' => 2],
            ['code' => 'MGR', 'name' => 'Manager', 'rank' => 3],
            ['code' => 'DIR', 'name' => 'Director', 'rank' => 4],
        ];

        $levelMap = [];
        foreach ($levels as $level) {
            $levelMap[$level['code']] = JobLevel::firstOrCreate(['code' => $level['code']], $level);
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
        foreach ($positions as $position) {
            $positionMap[$position['code']] = JobPosition::firstOrCreate(
                ['code' => $position['code']],
                ['name' => $position['name'], 'job_level_id' => $levelMap[$position['level']]->id]
            );
        }

        return $positionMap;
    }

    private function seedShifts(): void
    {
        $shifts = [
            ['code' => 'REG', 'name' => 'Regular 09-18', 'start_time' => '09:00', 'end_time' => '18:00', 'break_start' => '12:00', 'break_end' => '13:00'],
            ['code' => 'EARLY', 'name' => 'Early 07-16', 'start_time' => '07:00', 'end_time' => '16:00', 'break_start' => '12:00', 'break_end' => '13:00'],
            ['code' => 'LATE', 'name' => 'Late 13-22', 'start_time' => '13:00', 'end_time' => '22:00', 'break_start' => '18:00', 'break_end' => '19:00'],
        ];

        foreach ($shifts as $shift) {
            Shift::firstOrCreate(['code' => $shift['code']], $shift);
        }
    }

    private function seedPayrollComponents(): void
    {
        $components = [
            ['code' => 'BASIC', 'name' => 'Gaji Pokok', 'type' => 'earning'],
        ];

        foreach ($components as $component) {
            PayrollComponent::firstOrCreate(['code' => $component['code']], $component);
        }
    }

    private function seedUsersAndEmployees(array $positionMap): array
    {
        $adminUser = User::firstOrCreate(['email' => 'admin@company.test'], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'role' => UserRole::SuperAdmin,
            'email_verified_at' => now(),
        ]);

        $hrUser = User::firstOrCreate(['email' => 'hr@company.test'], [
            'name' => 'HR Manager',
            'password' => Hash::make('password'),
            'role' => UserRole::HR,
            'email_verified_at' => now(),
        ]);

        $managerUser = User::firstOrCreate(['email' => 'manager@company.test'], [
            'name' => 'IT Manager',
            'password' => Hash::make('password'),
            'role' => UserRole::Manager,
            'email_verified_at' => now(),
        ]);

        $financeUser = User::firstOrCreate(['email' => 'finance@company.test'], [
            'name' => 'Finance Admin',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
        ]);

        $budiUser = User::firstOrCreate(['email' => 'budi@company.test'], [
            'name' => 'Budi Santoso',
            'password' => Hash::make('password'),
            'role' => UserRole::Employee,
            'email_verified_at' => now(),
        ]);

        $sitiUser = User::firstOrCreate(['email' => 'siti@company.test'], [
            'name' => 'Siti Nurhaliza',
            'password' => Hash::make('password'),
            'role' => UserRole::Employee,
            'email_verified_at' => now(),
        ]);

        $hrEmployee = Employee::firstOrCreate(
            ['user_id' => $hrUser->id],
            [
                'job_position_id' => $positionMap['HR-MGR']->id,
                'employee_number' => 'EMP-0002',
                'full_name' => 'Hannah Rahma',
                'nickname' => 'Hannah',
                'gender' => 'female',
                'date_of_birth' => '1990-11-08',
                'religion' => 'Islam',
                'phone' => '081298765432',
                'address' => 'Jl. Kenanga No. 24',
                'city' => 'Jakarta',
                'employment_status' => EmploymentStatus::Permanent,
                'join_date' => '2022-07-01',
                'bank_name' => 'Mandiri',
                'bank_account_number' => '0987654321',
                'bank_account_holder' => 'Hannah Rahma',
                'ptkp_status' => 'K/1',
                'basic_salary' => 12000000,
            ]
        );

        $managerEmployee = Employee::firstOrCreate(
            ['user_id' => $managerUser->id],
            [
                'job_position_id' => $positionMap['IT-MGR']->id,
                'employee_number' => 'EMP-0003',
                'full_name' => 'Anton Wijaya',
                'nickname' => 'Anton',
                'gender' => 'male',
                'date_of_birth' => '1988-02-14',
                'religion' => 'Islam',
                'phone' => '081345678901',
                'address' => 'Jl. Melati No. 8',
                'city' => 'Bandung',
                'employment_status' => EmploymentStatus::Permanent,
                'join_date' => '2021-03-15',
                'bank_name' => 'BNI',
                'bank_account_number' => '1122334455',
                'bank_account_holder' => 'Anton Wijaya',
                'ptkp_status' => 'K/0',
                'basic_salary' => 15000000,
            ]
        );

        $financeEmployee = Employee::firstOrCreate(
            ['user_id' => $financeUser->id],
            [
                'job_position_id' => $positionMap['ACC']->id,
                'employee_number' => 'EMP-0004',
                'full_name' => 'Fitri Andini',
                'nickname' => 'Fitri',
                'gender' => 'female',
                'date_of_birth' => '1992-05-20',
                'religion' => 'Islam',
                'phone' => '081212345678',
                'address' => 'Jl. Dahlia No. 12',
                'city' => 'Surabaya',
                'employment_status' => EmploymentStatus::Permanent,
                'join_date' => '2023-02-10',
                'bank_name' => 'BRI',
                'bank_account_number' => '5566778899',
                'bank_account_holder' => 'Fitri Andini',
                'ptkp_status' => 'TK/0',
                'basic_salary' => 9500000,
            ]
        );

        $budiEmployee = Employee::firstOrCreate(
            ['user_id' => $budiUser->id],
            [
                'job_position_id' => $positionMap['DEV']->id,
                'manager_id' => $managerEmployee->id,
                'employee_number' => 'EMP-0001',
                'full_name' => 'Budi Santoso',
                'nickname' => 'Budi',
                'gender' => 'male',
                'date_of_birth' => '1995-04-12',
                'religion' => 'Islam',
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

        $sitiEmployee = Employee::firstOrCreate(
            ['user_id' => $sitiUser->id],
            [
                'job_position_id' => $positionMap['MKT']->id,
                'manager_id' => $hrEmployee->id,
                'employee_number' => 'EMP-0005',
                'full_name' => 'Siti Nurhaliza',
                'nickname' => 'Siti',
                'gender' => 'female',
                'date_of_birth' => '1996-08-18',
                'religion' => 'Islam',
                'phone' => '081987654321',
                'address' => 'Jl. Melati No. 15',
                'city' => 'Jakarta',
                'employment_status' => EmploymentStatus::Permanent,
                'join_date' => '2024-01-05',
                'bank_name' => 'CIMB',
                'bank_account_number' => '6677889900',
                'bank_account_holder' => 'Siti Nurhaliza',
                'ptkp_status' => 'TK/0',
                'basic_salary' => 7000000,
            ]
        );

        return [
            'admin' => $adminUser,
            'hr' => $hrEmployee,
            'manager' => $managerEmployee,
            'finance' => $financeEmployee,
            'budi' => $budiEmployee,
            'siti' => $sitiEmployee,
        ];
    }

    private function seedWorkSchedules(array $employees): void
    {
        $shift = Shift::where('code', 'REG')->first() ?? Shift::first();
        if (! $shift) {
            return;
        }

        foreach ($this->recentBusinessDays(7) as $date) {
            WorkSchedule::firstOrCreate(
                ['employee_id' => $employees['budi']->id, 'work_date' => $date],
                ['shift_id' => $shift->id, 'day_type' => 'workday']
            );

            WorkSchedule::firstOrCreate(
                ['employee_id' => $employees['siti']->id, 'work_date' => $date],
                ['shift_id' => $shift->id, 'day_type' => 'workday']
            );
        }
    }

    private function seedHolidays(): void
    {
        $holidays = [
            ['date' => now()->startOfYear()->toDateString(), 'name' => 'Tahun Baru Masehi'],
            ['date' => now()->month(5)->day(1)->toDateString(), 'name' => 'Hari Buruh'],
            ['date' => now()->month(12)->day(25)->toDateString(), 'name' => 'Hari Natal'],
        ];

        foreach ($holidays as $holiday) {
            Holiday::firstOrCreate(['date' => $holiday['date']], $holiday);
        }
    }

    private function seedAttendanceAndLogs(array $employees): void
    {
        foreach ($this->recentBusinessDays(5) as $date) {
            foreach (['budi', 'siti'] as $key) {
                $employee = $employees[$key];
                $checkIn = Carbon::parse("{$date} 09:05:00");
                $checkOut = Carbon::parse("{$date} 17:50:00");

                $attendance = Attendance::firstOrCreate(
                    ['employee_id' => $employee->id, 'attendance_date' => $date],
                    [
                        'shift_id' => Shift::where('code', 'REG')->first()?->id,
                        'check_in_at' => $checkIn,
                        'check_out_at' => $checkOut,
                        'status' => 'present',
                        'late_minutes' => 5,
                        'early_leave_minutes' => 0,
                        'work_minutes' => 525,
                    ]
                );

                AttendanceLog::firstOrCreate(
                    ['attendance_id' => $attendance->id, 'event' => 'check_in'],
                    [
                        'employee_id' => $employee->id,
                        'event_at' => $checkIn,
                        'ip_address' => '192.168.1.10',
                        'device_info' => 'Browser',
                    ]
                );

                AttendanceLog::firstOrCreate(
                    ['attendance_id' => $attendance->id, 'event' => 'check_out'],
                    [
                        'employee_id' => $employee->id,
                        'event_at' => $checkOut,
                        'ip_address' => '192.168.1.10',
                        'device_info' => 'Browser',
                    ]
                );
            }
        }
    }

    private function seedPayrollData(array $employees): void
    {
        $period = PayrollPeriod::firstOrCreate(
            ['year' => now()->year, 'month' => now()->month],
            [
                'code' => now()->format('Y-m'),
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->endOfMonth()->toDateString(),
                'payment_date' => now()->endOfMonth()->subDays(2)->toDateString(),
                'status' => 'processed',
                'processed_at' => now(),
                'locked_at' => now(),
            ]
        );

        PayrollPeriod::firstOrCreate(
            ['year' => now()->year, 'month' => now()->subMonth()->month],
            [
                'code' => now()->subMonth()->format('Y-m'),
                'start_date' => now()->subMonth()->startOfMonth()->toDateString(),
                'end_date' => now()->subMonth()->endOfMonth()->toDateString(),
                'payment_date' => now()->subMonth()->endOfMonth()->subDays(2)->toDateString(),
                'status' => 'draft',
            ]
        );

        $components = PayrollComponent::all()->keyBy('code');

        foreach (['budi', 'siti'] as $key) {
            $employee = $employees[$key];
            $basic = $employee->basic_salary;

            $payroll = Payroll::firstOrCreate(
                ['payroll_period_id' => $period->id, 'employee_id' => $employee->id],
                [
                    'basic_salary' => $basic,
                    'total_earnings' => $basic,
                    'total_deductions' => 0,
                    'total_bpjs' => 0,
                    'total_tax_pph21' => 0,
                    'gross_salary' => $basic,
                    'net_salary' => $basic,
                    'working_days' => 22,
                    'present_days' => 20,
                    'absent_days' => 2,
                    'leave_days' => 0,
                    'overtime_minutes' => 0,
                    'overtime_amount' => 0,
                    'status' => 'approved',
                ]
            );

            $payrollItems = [
                ['component' => $components['BASIC'] ?? null, 'amount' => $basic],
            ];

            foreach ($payrollItems as $item) {
                if (! $item['component']) {
                    continue;
                }

                PayrollItem::firstOrCreate(
                    ['payroll_id' => $payroll->id, 'component_code' => $item['component']->code],
                    [
                        'component_id' => $item['component']->id,
                        'component_name' => $item['component']->name,
                        'type' => $item['component']->type,
                        'amount' => $item['amount'],
                    ]
                );
            }
        }
    }

    private function seedAnnouncements(): void
    {
        Announcement::firstOrCreate(
            ['title' => 'Selamat Datang di HRIS Impost Media'],
            [
                'content' => 'Gunakan akun demo untuk mengakses panel admin, cuti, dan payroll.',
                'published_at' => now()->subDays(10),
                'expires_at' => now()->addDays(20),
                'is_pinned' => false,
            ]
        );

        Announcement::firstOrCreate(
            ['title' => 'Jadwal Maintenance Terjadwal'],
            [
                'content' => 'Sistem akan menjalani maintenance ringan pada hari Minggu mendatang pukul 02:00.',
                'published_at' => now()->subDays(1),
                'expires_at' => now()->addDays(7),
                'is_pinned' => false,
            ]
        );
    }

    private function recentBusinessDays(int $count): array
    {
        $dates = [];
        $date = Carbon::today()->subDay();

        while (count($dates) < $count) {
            if (! $date->isWeekend()) {
                $dates[] = $date->toDateString();
            }
            $date = $date->subDay();
        }

        return array_reverse($dates);
    }
}
