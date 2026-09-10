<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Services\WorkScheduleService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollPeriodSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::where('is_active', true)->get();
        if ($employees->isEmpty()) {
            $this->command?->info('Tidak ada karyawan aktif — skip payroll seeder.');

            return;
        }

        $schedule = app(WorkScheduleService::class);

        // Generate 3 recent months (1 draft, 1 processed, 1 paid)
        $statuses = ['paid', 'processed', 'draft'];
        $now = Carbon::now();

        DB::transaction(function () use ($employees, $schedule, $statuses, $now) {
            for ($i = 2; $i >= 0; $i--) {
                $date = $now->copy()->subMonths($i);
                $year = $date->year;
                $month = $date->month;
                $status = $statuses[$i];

                $period = PayrollPeriod::firstOrCreate(
                    ['year' => $year, 'month' => $month],
                    [
                        'code' => sprintf('PP-%04d-%02d', $year, $month),
                        'start_date' => Carbon::create($year, $month, 1)->toDateString(),
                        'end_date' => Carbon::create($year, $month, 1)->endOfMonth()->toDateString(),
                        'payment_date' => Carbon::create($year, $month, 1)->addDays(25)->toDateString(),
                        'status' => $status,
                        'processed_at' => $status !== 'draft' ? Carbon::create($year, $month, 1)->addDays(5)->toDateTimeString() : null,
                        'locked_at' => $status === 'paid' ? Carbon::create($year, $month, 1)->addDays(25)->toDateTimeString() : null,
                    ],
                );

                $start = Carbon::parse($period->start_date);
                $end = Carbon::parse($period->end_date);
                $workingDays = count($schedule->workingDatesBetween($start, $end));

                foreach ($employees as $emp) {
                    $basic = (float) ($emp->basic_salary ?? 5000000);

                    $present = Attendance::where('employee_id', $emp->id)
                        ->whereBetween('attendance_date', [$start, $end])
                        ->whereIn('status', [AttendanceStatus::Present, AttendanceStatus::Late])
                        ->count();

                    $absent = max(0, $workingDays - $present);
                    $prorata = $workingDays > 0 ? $present / $workingDays : 1;

                    $transport = 500000;
                    $meal = 350000;
                    $totalEarnings = $basic + $transport + $meal;
                    $bpjsKes = round($basic * 0.01);
                    $bpjsJht = round($basic * 0.02);
                    $bpjsJp = round($basic * 0.01);
                    $totalDeductions = $bpjsKes + $bpjsJht + $bpjsJp;
                    $gross = $totalEarnings;
                    $net = $gross - $totalDeductions;

                    $payroll = Payroll::firstOrCreate(
                        ['payroll_period_id' => $period->id, 'employee_id' => $emp->id],
                        [
                            'basic_salary' => $basic,
                            'total_earnings' => $totalEarnings,
                            'total_deductions' => $totalDeductions,
                            'total_bpjs' => $bpjsKes + $bpjsJht + $bpjsJp + round($basic * 0.0024) + round($basic * 0.003),
                            'total_tax_pph21' => 0,
                            'gross_salary' => $gross,
                            'net_salary' => $net,
                            'working_days' => $workingDays,
                            'present_days' => $present,
                            'absent_days' => $absent,
                            'leave_days' => 0,
                            'overtime_minutes' => 0,
                            'overtime_amount' => 0,
                            'status' => $status,
                        ],
                    );

                    $items = [
                        ['code' => 'BASIC', 'name' => 'Gaji Pokok', 'type' => 'earning', 'amount' => $basic],
                        ['code' => 'TRANSPORT', 'name' => 'Tunjangan Transportasi', 'type' => 'earning', 'amount' => $transport],
                        ['code' => 'MEAL', 'name' => 'Tunjangan Makan', 'type' => 'earning', 'amount' => $meal],
                        ['code' => 'BPJS_KES', 'name' => 'BPJS Kesehatan', 'type' => 'deduction', 'amount' => $bpjsKes],
                        ['code' => 'BPJS_JHT', 'name' => 'BPJS JHT (Karyawan)', 'type' => 'deduction', 'amount' => $bpjsJht],
                        ['code' => 'BPJS_JP', 'name' => 'BPJS JP (Karyawan)', 'type' => 'deduction', 'amount' => $bpjsJp],
                    ];

                    foreach ($items as $item) {
                        PayrollItem::firstOrCreate(
                            ['payroll_id' => $payroll->id, 'component_code' => $item['code']],
                            [
                                'component_name' => $item['name'],
                                'type' => $item['type'],
                                'amount' => $item['amount'],
                            ],
                        );
                    }
                }
            }
        });

        $periods = PayrollPeriod::count();
        $payrolls = Payroll::count();
        $this->command?->info("Periode payroll dibuat: {$periods} periode, {$payrolls} slip gaji.");
    }
}
