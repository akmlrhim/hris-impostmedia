<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Enums\RemoteWorkStatus;
use App\Enums\UserRole;
use App\Enums\WorkType;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\OfficeLocation;
use App\Models\Payroll;
use App\Models\PayrollComponent;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\RemoteWorkRequest;
use App\Models\RolePermission;
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
            $this->seedShifts();
            $this->seedOfficeLocations();
            $this->seedPayrollComponents();
            $this->seedRolePermissions();

            $data = $this->seedUsersAndEmployees();
            $this->seedWorkSchedules($data['employees']);
            $this->seedHolidays();
            $this->seedAttendance($data['employees']);
            $this->seedRemoteWorkRequests($data['employees'], $data['hrUser']);
            $this->seedPayrollData($data['employees'], $data['adminUser']);
            $this->seedAnnouncements($data['adminUser']);
        });
    }

    private function seedShifts(): void
    {
        $shifts = [
            [
                'code' => 'REG',
                'name' => 'Regular 09-18',
                'start_time' => '09:00',
                'end_time' => '18:00',
                'break_start' => '12:00',
                'break_end' => '13:00',
                'late_tolerance_minutes' => 15,
            ],
            [
                'code' => 'EARLY',
                'name' => 'Early 07-16',
                'start_time' => '07:00',
                'end_time' => '16:00',
                'break_start' => '12:00',
                'break_end' => '13:00',
                'late_tolerance_minutes' => 10,
            ],
            [
                'code' => 'LATE',
                'name' => 'Late 13-22',
                'start_time' => '13:00',
                'end_time' => '22:00',
                'break_start' => '18:00',
                'break_end' => '19:00',
                'late_tolerance_minutes' => 15,
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::firstOrCreate(['code' => $shift['code']], $shift);
        }
    }

    private function seedOfficeLocations(): void
    {
        OfficeLocation::firstOrCreate(
            ['name' => 'Kantor Pusat Jakarta'],
            [
                'address' => 'Jl. Sudirman No. 100, Jakarta Pusat',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'radius_meters' => 200,
                'is_active' => true,
            ]
        );
    }

    private function seedPayrollComponents(): void
    {
        $components = [
            ['code' => 'BASIC', 'name' => 'Gaji Pokok', 'type' => 'earning', 'is_taxable' => true],
            ['code' => 'BONUS', 'name' => 'Bonus', 'type' => 'earning', 'is_taxable' => true],
            ['code' => 'TRANSPORT', 'name' => 'Tunjangan Transport', 'type' => 'earning', 'is_taxable' => false],
            ['code' => 'PPH21', 'name' => 'PPh 21', 'type' => 'deduction', 'is_taxable' => false],
        ];

        foreach ($components as $component) {
            PayrollComponent::firstOrCreate(['code' => $component['code']], $component);
        }
    }

    private function seedRolePermissions(): void
    {
        $hrPermissions = [
            'manage_employees',
            'manage_attendance',
            'manage_payroll',
            'manage_holidays',
            'manage_shifts',
            'manage_announcements',
            'manage_remote_work',
            'manage_office_locations',
        ];

        foreach ($hrPermissions as $permission) {
            RolePermission::firstOrCreate([
                'role' => UserRole::HR->value,
                'permission' => $permission,
            ]);
        }
    }

    private function seedUsersAndEmployees(): array
    {
        // Admin - memiliki roles: admin + employee (demo multi-role & panel karyawan)
        $adminUser = User::firstOrCreate(['email' => 'admin@company.test'], [
            'name' => 'Ahmad Fauzi',
            'password' => Hash::make('password'),
            'roles' => [UserRole::Admin->value, UserRole::Employee->value],
            'email_verified_at' => now(),
        ]);

        // HR - memiliki roles: hr + employee (bisa kelola + absen sendiri)
        $hrUser = User::firstOrCreate(['email' => 'hr@company.test'], [
            'name' => 'Hana Pertiwi',
            'password' => Hash::make('password'),
            'roles' => [UserRole::HR->value, UserRole::Employee->value],
            'email_verified_at' => now(),
        ]);

        // Karyawan biasa - hanya role employee
        $budiUser = User::firstOrCreate(['email' => 'budi@company.test'], [
            'name' => 'Budi Santoso',
            'password' => Hash::make('password'),
            'roles' => [UserRole::Employee->value],
            'email_verified_at' => now(),
        ]);

        $sitiUser = User::firstOrCreate(['email' => 'siti@company.test'], [
            'name' => 'Siti Rahayu',
            'password' => Hash::make('password'),
            'roles' => [UserRole::Employee->value],
            'email_verified_at' => now(),
        ]);

        $ekoUser = User::firstOrCreate(['email' => 'eko@company.test'], [
            'name' => 'Eko Prasetyo',
            'password' => Hash::make('password'),
            'roles' => [UserRole::Employee->value],
            'email_verified_at' => now(),
        ]);

        $rizkiUser = User::firstOrCreate(['email' => 'rizki@company.test'], [
            'name' => 'Rizki Maulana',
            'password' => Hash::make('password'),
            'roles' => [UserRole::Employee->value],
            'email_verified_at' => now(),
        ]);

        $adminEmployee = Employee::firstOrCreate(
            ['user_id' => $adminUser->id],
            [
                'employee_number' => 'EMP-0001',
                'full_name' => 'Ahmad Fauzi',
                'nickname' => 'Ahmad',
                'gender' => 'male',
                'date_of_birth' => '1985-03-15',
                'place_of_birth' => 'Jakarta',
                'work_type' => WorkType::WFO,
                'phone' => '08111234567',
                'nik' => '3171010301850001',
                'email' => 'admin@company.test',
                'address' => 'Jl. Gatot Subroto No. 5, Jakarta',
                'last_education' => 'S1',
                'major_school_university' => 'Teknik Informatika - Universitas Indonesia',
                'bank_name' => 'BCA',
                'bank_account_number' => '1234000001',
                'bank_account_holder' => 'Ahmad Fauzi',
                'basic_salary' => 20000000,
                'emergency_contact_name' => 'Siti Fauziah',
                'emergency_contact_number' => '08111000001',
                'contract_start_date' => '2018-01-10',
                'is_active' => true,
            ]
        );

        $hrEmployee = Employee::firstOrCreate(
            ['user_id' => $hrUser->id],
            [
                'employee_number' => 'EMP-0002',
                'full_name' => 'Hana Pertiwi',
                'nickname' => 'Hana',
                'gender' => 'female',
                'date_of_birth' => '1990-07-22',
                'place_of_birth' => 'Bandung',
                'work_type' => WorkType::WFA,
                'phone' => '08121234568',
                'nik' => '3273012207900002',
                'email' => 'hr@company.test',
                'address' => 'Jl. Kenanga No. 24, Jakarta',
                'last_education' => 'S1',
                'major_school_university' => 'Manajemen SDM - Universitas Padjajaran',
                'bank_name' => 'Mandiri',
                'bank_account_number' => '1234000002',
                'bank_account_holder' => 'Hana Pertiwi',
                'basic_salary' => 14000000,
                'emergency_contact_name' => 'Andi Pertiwi',
                'emergency_contact_number' => '08121000002',
                'contract_start_date' => '2020-03-01',
                'is_active' => true,
            ]
        );

        $budiEmployee = Employee::firstOrCreate(
            ['user_id' => $budiUser->id],
            [
                'employee_number' => 'EMP-0003',
                'full_name' => 'Budi Santoso',
                'nickname' => 'Budi',
                'gender' => 'male',
                'date_of_birth' => '1995-04-12',
                'place_of_birth' => 'Yogyakarta',
                'work_type' => WorkType::WFO,
                'phone' => '08131234569',
                'nik' => '3471011204950003',
                'email' => 'budi@company.test',
                'address' => 'Jl. Mawar No. 10, Jakarta',
                'last_education' => 'S1',
                'major_school_university' => 'Sistem Informasi - Universitas Gadjah Mada',
                'bank_name' => 'BNI',
                'bank_account_number' => '1234000003',
                'bank_account_holder' => 'Budi Santoso',
                'basic_salary' => 9000000,
                'emergency_contact_name' => 'Rina Santoso',
                'emergency_contact_number' => '08131000003',
                'contract_start_date' => '2021-06-01',
                'is_active' => true,
            ]
        );

        $sitiEmployee = Employee::firstOrCreate(
            ['user_id' => $sitiUser->id],
            [
                'employee_number' => 'EMP-0004',
                'full_name' => 'Siti Rahayu',
                'nickname' => 'Siti',
                'gender' => 'female',
                'date_of_birth' => '1998-11-05',
                'place_of_birth' => 'Surabaya',
                'work_type' => WorkType::WFH,
                'phone' => '08141234570',
                'nik' => '3578010511980004',
                'email' => 'siti@company.test',
                'address' => 'Jl. Melati No. 7, Jakarta',
                'last_education' => 'D3',
                'major_school_university' => 'Akuntansi - Politeknik Negeri Surabaya',
                'bank_name' => 'BRI',
                'bank_account_number' => '1234000004',
                'bank_account_holder' => 'Siti Rahayu',
                'basic_salary' => 8000000,
                'emergency_contact_name' => 'Joko Rahayu',
                'emergency_contact_number' => '08141000004',
                'contract_start_date' => '2022-09-01',
                'is_active' => true,
            ]
        );

        $ekoEmployee = Employee::firstOrCreate(
            ['user_id' => $ekoUser->id],
            [
                'employee_number' => 'EMP-0005',
                'full_name' => 'Eko Prasetyo',
                'nickname' => 'Eko',
                'gender' => 'male',
                'date_of_birth' => '2000-08-18',
                'place_of_birth' => 'Semarang',
                'work_type' => WorkType::WFA,
                'phone' => '08151234571',
                'nik' => '3374011808000005',
                'email' => 'eko@company.test',
                'address' => 'Jl. Anggrek No. 3, Depok',
                'last_education' => 'S1',
                'major_school_university' => 'Ilmu Komputer - Universitas Diponegoro',
                'bank_name' => 'CIMB',
                'bank_account_number' => '1234000005',
                'bank_account_holder' => 'Eko Prasetyo',
                'basic_salary' => 6500000,
                'emergency_contact_name' => 'Tini Prasetyo',
                'emergency_contact_number' => '08151000005',
                'contract_start_date' => '2024-01-15',
                'contract_end_date' => now()->addMonths(6)->toDateString(),
                'is_active' => true,
            ]
        );

        $rizkiEmployee = Employee::firstOrCreate(
            ['user_id' => $rizkiUser->id],
            [
                'employee_number' => 'EMP-0006',
                'full_name' => 'Rizki Maulana',
                'nickname' => 'Rizki',
                'gender' => 'male',
                'date_of_birth' => '2002-02-28',
                'place_of_birth' => 'Medan',
                'work_type' => WorkType::WFO,
                'phone' => '08161234572',
                'nik' => '1271012802020006',
                'email' => 'rizki@company.test',
                'address' => 'Jl. Dahlia No. 15, Tangerang',
                'last_education' => 'SMK',
                'major_school_university' => 'Multimedia - SMKN 1 Medan',
                'bank_name' => 'BSI',
                'bank_account_number' => '1234000006',
                'bank_account_holder' => 'Rizki Maulana',
                'basic_salary' => 5500000,
                'emergency_contact_name' => 'Maulana Senior',
                'emergency_contact_number' => '08161000006',
                'contract_start_date' => now()->subMonths(2)->toDateString(),
                'is_active' => true,
            ]
        );

        return [
            'employees' => [
                'admin' => $adminEmployee,
                'hr' => $hrEmployee,
                'budi' => $budiEmployee,
                'siti' => $sitiEmployee,
                'eko' => $ekoEmployee,
                'rizki' => $rizkiEmployee,
            ],
            'adminUser' => $adminUser,
            'hrUser' => $hrUser,
        ];
    }

    private function seedWorkSchedules(array $employees): void
    {
        $regShift = Shift::where('code', 'REG')->first();
        $earlyShift = Shift::where('code', 'EARLY')->first() ?? $regShift;

        if (! $regShift) {
            return;
        }

        $days = $this->recentBusinessDays(10);

        $shiftMap = [
            'admin' => $regShift,
            'hr' => $regShift,
            'budi' => $regShift,
            'siti' => $regShift,
            'eko' => $regShift,
            'rizki' => $earlyShift,
        ];

        foreach ($shiftMap as $key => $shift) {
            $emp = $employees[$key];
            foreach ($days as $date) {
                WorkSchedule::firstOrCreate(
                    ['employee_id' => $emp->id, 'work_date' => $date],
                    [
                        'shift_id' => $shift->id,
                        'day_type' => 'workday',
                        'work_type' => $emp->work_type->value,
                    ]
                );
            }
        }
    }

    private function seedHolidays(): void
    {
        $year = now()->year;
        $holidays = [
            ['date' => "{$year}-01-01", 'name' => 'Tahun Baru Masehi', 'is_national' => true],
            ['date' => "{$year}-01-29", 'name' => 'Tahun Baru Imlek', 'is_national' => true],
            ['date' => "{$year}-03-29", 'name' => 'Hari Raya Nyepi', 'is_national' => true],
            ['date' => "{$year}-04-18", 'name' => 'Wafat Yesus Kristus', 'is_national' => true],
            ['date' => "{$year}-05-01", 'name' => 'Hari Buruh Internasional', 'is_national' => true],
            ['date' => "{$year}-05-29", 'name' => 'Kenaikan Isa Al-Masih', 'is_national' => true],
            ['date' => "{$year}-06-01", 'name' => 'Hari Lahir Pancasila', 'is_national' => true],
            ['date' => "{$year}-08-17", 'name' => 'HUT Kemerdekaan RI', 'is_national' => true],
            ['date' => "{$year}-12-25", 'name' => 'Hari Natal', 'is_national' => true],
            ['date' => "{$year}-12-26", 'name' => 'Cuti Bersama Natal', 'is_national' => false],
        ];

        foreach ($holidays as $holiday) {
            Holiday::firstOrCreate(['date' => $holiday['date']], $holiday);
        }
    }

    private function seedAttendance(array $employees): void
    {
        $days = $this->recentBusinessDays(7);
        $dayCount = count($days);

        // Pola absensi per karyawan: ['ci' => jam masuk, 'co' => jam keluar, 'late' => menit terlambat]
        // null = tidak hadir
        $patterns = [
            'admin' => array_fill(0, $dayCount, ['ci' => '08:55', 'co' => '18:05', 'late' => 0]),
            'hr' => [
                ['ci' => '09:20', 'co' => '18:15', 'late' => 20],
                ['ci' => '09:00', 'co' => '18:05', 'late' => 0],
                ['ci' => '08:58', 'co' => '18:00', 'late' => 0],
                ['ci' => '09:25', 'co' => '18:10', 'late' => 25],
                ['ci' => '09:00', 'co' => '18:00', 'late' => 0],
                ['ci' => '08:55', 'co' => '18:05', 'late' => 0],
                ['ci' => '09:05', 'co' => '18:10', 'late' => 5],
            ],
            'budi' => [
                ['ci' => '09:18', 'co' => '18:20', 'late' => 18],
                ['ci' => '09:05', 'co' => '18:10', 'late' => 5],
                ['ci' => '08:55', 'co' => '18:00', 'late' => 0],
                ['ci' => '09:22', 'co' => '18:15', 'late' => 22],
                ['ci' => '09:00', 'co' => '18:05', 'late' => 0],
                ['ci' => '08:58', 'co' => '18:02', 'late' => 0],
                ['ci' => '09:10', 'co' => '18:10', 'late' => 10],
            ],
            'siti' => [
                ['ci' => '09:02', 'co' => '18:05', 'late' => 0],
                ['ci' => '09:00', 'co' => '18:00', 'late' => 0],
                null, // sakit
                ['ci' => '09:05', 'co' => '18:10', 'late' => 5],
                ['ci' => '09:00', 'co' => '18:05', 'late' => 0],
                ['ci' => '08:55', 'co' => '18:00', 'late' => 0],
                ['ci' => '09:00', 'co' => '18:00', 'late' => 0],
            ],
            'eko' => array_fill(0, $dayCount, ['ci' => '09:00', 'co' => '18:05', 'late' => 0]),
            'rizki' => [
                ['ci' => '07:55', 'co' => '16:10', 'late' => 0],
                ['ci' => '07:50', 'co' => '16:05', 'late' => 0],
                ['ci' => '08:10', 'co' => '16:05', 'late' => 10],
                ['ci' => '07:55', 'co' => '16:00', 'late' => 0],
                ['ci' => '07:58', 'co' => '16:10', 'late' => 0],
                ['ci' => '07:50', 'co' => '16:05', 'late' => 0],
                ['ci' => '08:05', 'co' => '16:00', 'late' => 5],
            ],
        ];

        foreach ($patterns as $key => $dayPatterns) {
            $employee = $employees[$key];

            foreach ($days as $i => $date) {
                $pattern = $dayPatterns[$i] ?? null;

                if ($pattern === null) {
                    continue;
                }

                $checkIn = Carbon::parse("{$date} {$pattern['ci']}:00");
                $checkOut = Carbon::parse("{$date} {$pattern['co']}:00");
                $workMinutes = (int) $checkIn->diffInMinutes($checkOut);
                $status = $pattern['late'] > 0 ? AttendanceStatus::Late : AttendanceStatus::Present;

                $attendance = Attendance::firstOrCreate(
                    ['employee_id' => $employee->id, 'attendance_date' => $date],
                    [
                        'check_in_at' => $checkIn,
                        'check_out_at' => $checkOut,
                        'status' => $status,
                        'late_minutes' => $pattern['late'],
                        'work_minutes' => $workMinutes,
                    ]
                );

                AttendanceLog::firstOrCreate(
                    ['attendance_id' => $attendance->id, 'event' => 'check_in'],
                    ['employee_id' => $employee->id, 'event_at' => $checkIn, 'ip_address' => '127.0.0.1', 'device_info' => 'Web']
                );

                AttendanceLog::firstOrCreate(
                    ['attendance_id' => $attendance->id, 'event' => 'check_out'],
                    ['employee_id' => $employee->id, 'event_at' => $checkOut, 'ip_address' => '127.0.0.1', 'device_info' => 'Web']
                );
            }
        }
    }

    private function seedRemoteWorkRequests(array $employees, User $hrUser): void
    {
        // Siti: disetujui WFH minggu ini
        RemoteWorkRequest::firstOrCreate(
            ['employee_id' => $employees['siti']->id, 'start_date' => now()->startOfWeek()->toDateString()],
            [
                'end_date' => now()->endOfWeek()->toDateString(),
                'work_type' => WorkType::WFH,
                'reason' => 'Perbaikan AC kantor. Lebih efektif WFH untuk sementara.',
                'status' => RemoteWorkStatus::Approved,
                'reviewed_by' => $hrUser->id,
                'reviewed_at' => now()->subDays(2),
            ]
        );

        // Eko: disetujui WFA dua minggu
        RemoteWorkRequest::firstOrCreate(
            ['employee_id' => $employees['eko']->id, 'start_date' => now()->startOfWeek()->toDateString()],
            [
                'end_date' => now()->addWeek()->endOfWeek()->toDateString(),
                'work_type' => WorkType::WFA,
                'reason' => 'Urusan keluarga di luar kota, tetap bisa bekerja penuh.',
                'status' => RemoteWorkStatus::Approved,
                'reviewed_by' => $hrUser->id,
                'reviewed_at' => now()->subDay(),
            ]
        );

        // Budi: menunggu persetujuan (untuk demo fitur approval)
        RemoteWorkRequest::firstOrCreate(
            ['employee_id' => $employees['budi']->id, 'start_date' => now()->addWeek()->startOfWeek()->toDateString()],
            [
                'end_date' => now()->addWeek()->endOfWeek()->toDateString(),
                'work_type' => WorkType::WFH,
                'reason' => 'Renovasi apartemen, susah commute ke kantor minggu depan.',
                'status' => RemoteWorkStatus::Pending,
            ]
        );
    }

    private function seedPayrollData(array $employees, User $adminUser): void
    {
        $components = PayrollComponent::all()->keyBy('code');

        // ===== PERIODE LALU (PAID) - dengan variasi bonus & PPh 21 =====
        $prevMonth = now()->subMonth();
        $prevPeriod = PayrollPeriod::firstOrCreate(
            ['year' => $prevMonth->year, 'month' => $prevMonth->month],
            [
                'code' => $prevMonth->format('Y-m'),
                'start_date' => $prevMonth->copy()->startOfMonth()->toDateString(),
                'end_date' => $prevMonth->copy()->endOfMonth()->toDateString(),
                'payment_date' => $prevMonth->copy()->day(28)->toDateString(),
                'status' => 'paid',
                'processed_at' => $prevMonth->copy()->day(25),
                'locked_at' => $prevMonth->copy()->day(27),
            ]
        );

        // bonus = nominal tambahan; pph21_pct = persentase dari gaji kotor
        $prevData = [
            'admin' => ['bonus' => 5000000, 'transport' => 0, 'pph21_pct' => 5.0, 'present' => 22, 'absent' => 0],
            'hr' => ['bonus' => 0, 'transport' => 500000, 'pph21_pct' => 3.0, 'present' => 22, 'absent' => 0],
            'budi' => ['bonus' => 1500000, 'transport' => 300000, 'pph21_pct' => 0, 'present' => 21, 'absent' => 1],
            'siti' => ['bonus' => 0, 'transport' => 200000, 'pph21_pct' => 2.5, 'present' => 20, 'absent' => 2],
            'eko' => ['bonus' => 0, 'transport' => 0, 'pph21_pct' => 0, 'present' => 22, 'absent' => 0],
            'rizki' => ['bonus' => 0, 'transport' => 0, 'pph21_pct' => 0, 'present' => 20, 'absent' => 2],
        ];

        foreach ($prevData as $key => $data) {
            $employee = $employees[$key];
            $basic = (float) $employee->basic_salary;
            $bonus = (float) $data['bonus'];
            $transport = (float) $data['transport'];
            $totalEarnings = $basic + $bonus + $transport;
            $pph21 = $data['pph21_pct'] > 0 ? round($totalEarnings * $data['pph21_pct'] / 100) : 0;

            $payroll = Payroll::firstOrCreate(
                ['payroll_period_id' => $prevPeriod->id, 'employee_id' => $employee->id],
                [
                    'basic_salary' => $basic,
                    'total_earnings' => $totalEarnings,
                    'total_deductions' => 0,
                    'total_bpjs' => 0,
                    'total_tax_pph21' => $pph21,
                    'gross_salary' => $totalEarnings,
                    'net_salary' => max(0, $totalEarnings - $pph21),
                    'working_days' => 22,
                    'present_days' => $data['present'],
                    'absent_days' => $data['absent'],
                    'leave_days' => 0,
                    'overtime_minutes' => 0,
                    'overtime_amount' => 0,
                    'status' => 'paid',
                ]
            );

            $this->createPayrollItem($payroll, $components, 'BASIC', $basic, 'earning');

            if ($bonus > 0) {
                $this->createPayrollItem($payroll, $components, 'BONUS', $bonus, 'earning');
            }

            if ($transport > 0) {
                $this->createPayrollItem($payroll, $components, 'TRANSPORT', $transport, 'earning');
            }

            if ($pph21 > 0) {
                $this->createPayrollItem($payroll, $components, 'PPH21', $pph21, 'deduction', $data['pph21_pct'].'% dari gaji kotor');
            }
        }

        // ===== PERIODE INI (PROCESSED - siap di-review) =====
        $currentPeriod = PayrollPeriod::firstOrCreate(
            ['year' => now()->year, 'month' => now()->month],
            [
                'code' => now()->format('Y-m'),
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->endOfMonth()->toDateString(),
                'payment_date' => now()->endOfMonth()->subDays(2)->toDateString(),
                'status' => 'processed',
                'processed_at' => now()->startOfMonth()->addDays(19),
            ]
        );

        foreach ($employees as $employee) {
            $basic = (float) $employee->basic_salary;

            $payroll = Payroll::firstOrCreate(
                ['payroll_period_id' => $currentPeriod->id, 'employee_id' => $employee->id],
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
                    'status' => 'draft',
                ]
            );

            $this->createPayrollItem($payroll, $components, 'BASIC', $basic, 'earning');
        }
    }

    private function createPayrollItem(Payroll $payroll, $components, string $code, float $amount, string $type, ?string $notes = null): void
    {
        PayrollItem::firstOrCreate(
            ['payroll_id' => $payroll->id, 'component_code' => $code],
            [
                'component_id' => $components[$code]?->id,
                'component_name' => $components[$code]?->name ?? $code,
                'type' => $type,
                'amount' => $amount,
                'notes' => $notes,
            ]
        );
    }

    private function seedAnnouncements(User $adminUser): void
    {
        $announcements = [
            [
                'title' => 'Selamat Datang di HRIS Impost Media',
                'content' => 'Sistem HRIS telah aktif. Admin dapat masuk ke panel admin maupun mode karyawan menggunakan akun yang sama. Gunakan akun demo berikut:'
                    ."\n\n• admin@company.test - Admin (bisa login sebagai admin & karyawan)"
                    ."\n• hr@company.test - HR Manager"
                    ."\n• budi@company.test / siti@company.test / eko@company.test / rizki@company.test - Karyawan"
                    ."\n\nSemua akun menggunakan password: password",
                'published_at' => now()->subDays(30),
                'expires_at' => now()->addMonths(6),
                'is_pinned' => true,
            ],
            [
                'title' => 'Kebijakan Absensi: Minimal 8 Jam Kerja',
                'content' => 'Mulai bulan ini diberlakukan ketentuan minimal jam kerja 8 jam per hari. Jika melakukan checkout sebelum 8 jam, sistem akan menampilkan peringatan. Karyawan tetap dapat melakukan checkout dengan konfirmasi.',
                'published_at' => now()->subDays(14),
                'expires_at' => now()->addMonths(3),
                'is_pinned' => false,
            ],
            [
                'title' => 'Cara Pengajuan WFH / WFA',
                'content' => 'Karyawan yang ingin bekerja dari rumah (WFH) atau dari mana saja (WFA) wajib mengajukan permohonan melalui menu Pengajuan WFA di aplikasi. Pengajuan harus dilakukan minimal H-1 dan menunggu persetujuan HR.',
                'published_at' => now()->subDays(7),
                'expires_at' => now()->addDays(60),
                'is_pinned' => false,
            ],
        ];

        foreach ($announcements as $data) {
            Announcement::firstOrCreate(
                ['title' => $data['title']],
                array_merge($data, ['author_id' => $adminUser->id, 'audience' => 'all'])
            );
        }
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
