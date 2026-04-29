<?php

namespace App\Services\Payroll;

use App\Enums\AttendanceStatus;
use App\Enums\RequestStatus;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\PayrollComponent;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PayrollGenerator
{
    /** Components handled by special-case logic (computed amount, not from default_amount). */
    private const SYSTEM_CODES = ['BASIC', 'OT', 'ABSENT', 'PPH21'];

    /**
     * Generate payrolls for all active employees in a period.
     *
     * @return int Number of payroll records generated
     */
    public function generateForPeriod(PayrollPeriod $period): int
    {
        return DB::transaction(function () use ($period): int {
            // Wipe existing draft payrolls so this is idempotent
            Payroll::where('payroll_period_id', $period->id)
                ->where('status', 'draft')
                ->delete();

            $components = PayrollComponent::where('is_active', true)->get()->keyBy('code');
            $employees = Employee::where('is_active', true)->get();
            $count = 0;

            foreach ($employees as $emp) {
                $this->generateForEmployee($period, $emp, $components);
                $count++;
            }

            $period->update([
                'status' => 'processed',
                'processed_at' => now(),
            ]);

            return $count;
        });
    }

    /**
     * @param  Collection<string, PayrollComponent>  $components
     */
    private function generateForEmployee(PayrollPeriod $period, Employee $employee, Collection $components): Payroll
    {
        $stats = $this->calculateAttendanceStats($period, $employee);
        $overtimeMinutes = $this->calculateOvertimeMinutes($period, $employee);
        $basicSalary = (float) $employee->basic_salary;

        $earnings = [];
        $deductions = [];
        $taxes = [];

        // 1. Basic salary (always present)
        $earnings[] = [
            'code' => 'BASIC',
            'name' => $components['BASIC']->name ?? 'Gaji Pokok',
            'amount' => $basicSalary,
            'taxable' => true,
        ];

        // 2. Overtime (computed)
        $overtimeAmount = 0;
        if ($overtimeMinutes > 0) {
            $hourlyRate = $basicSalary / 173;
            $overtimeAmount = round(($overtimeMinutes / 60) * $hourlyRate * 1.5);
            $earnings[] = [
                'code' => 'OT',
                'name' => $components['OT']->name ?? 'Lembur',
                'amount' => $overtimeAmount,
                'taxable' => true,
            ];
        }

        // 3. Dynamic earnings (anything is_active, type=earning, not BASIC/OT)
        foreach ($components as $code => $c) {
            if ($c->type !== 'earning' || in_array($code, ['BASIC', 'OT'], true)) {
                continue;
            }
            $amount = $this->resolveAmount($c, $basicSalary);
            if ($amount <= 0) {
                continue;
            }
            $earnings[] = [
                'code' => $code,
                'name' => $c->name,
                'amount' => $amount,
                'taxable' => (bool) $c->is_taxable,
            ];
        }

        // 4. Absent deduction (computed)
        if ($stats['absent_days'] > 0 && $stats['working_days'] > 0) {
            $perDay = $basicSalary / $stats['working_days'];
            $deductions[] = [
                'code' => 'ABSENT',
                'name' => $components['ABSENT']->name ?? 'Potongan Tidak Hadir',
                'amount' => round($perDay * $stats['absent_days']),
            ];
        }

        // 5. Dynamic deductions (anything is_active, type=deduction, not ABSENT)
        $totalBpjs = 0;
        foreach ($components as $code => $c) {
            if ($c->type !== 'deduction' || $code === 'ABSENT') {
                continue;
            }
            $amount = $this->resolveAmount($c, $basicSalary);
            if ($amount <= 0) {
                continue;
            }
            $deductions[] = [
                'code' => $code,
                'name' => $c->name,
                'amount' => $amount,
            ];
            if ($c->is_bpjs_subject || str_starts_with($code, 'BPJS')) {
                $totalBpjs += $amount;
            }
        }

        // 6. PPh 21 — calculated on taxable earnings minus PTKP
        $taxableEarnings = collect($earnings)->where('taxable', true)->sum('amount');
        $ptkpMonthly = 4500000; // TK/0 default
        $taxablePerMonth = max(0, $taxableEarnings - $ptkpMonthly);
        $pph21 = round($taxablePerMonth * 0.05);
        if ($pph21 > 0) {
            $taxes[] = [
                'code' => 'PPH21',
                'name' => $components['PPH21']->name ?? 'PPh 21',
                'amount' => $pph21,
            ];
        }

        $totalEarnings = collect($earnings)->sum('amount');
        $totalDeductions = collect($deductions)->sum('amount') + collect($taxes)->sum('amount');
        $gross = $totalEarnings;
        $net = $gross - $totalDeductions;

        $payroll = Payroll::create([
            'payroll_period_id' => $period->id,
            'employee_id' => $employee->id,
            'basic_salary' => $basicSalary,
            'total_earnings' => $totalEarnings,
            'total_deductions' => $totalDeductions,
            'total_bpjs' => $totalBpjs,
            'total_tax_pph21' => $pph21,
            'gross_salary' => $gross,
            'net_salary' => $net,
            'working_days' => $stats['working_days'],
            'present_days' => $stats['present_days'],
            'absent_days' => $stats['absent_days'],
            'leave_days' => $stats['leave_days'],
            'overtime_minutes' => $overtimeMinutes,
            'overtime_amount' => $overtimeAmount,
            'status' => 'draft',
        ]);

        foreach ($earnings as $item) {
            $this->createItem($payroll->id, $components, $item, 'earning');
        }
        foreach ($deductions as $item) {
            $this->createItem($payroll->id, $components, $item, 'deduction');
        }
        foreach ($taxes as $item) {
            $this->createItem($payroll->id, $components, $item, 'tax');
        }

        return $payroll;
    }

    private function resolveAmount(PayrollComponent $component, float $basicSalary): float
    {
        $value = (float) $component->default_amount;
        if ($component->calculation_type === 'percentage') {
            return round($basicSalary * $value / 100);
        }

        return $value;
    }

    /**
     * @param  Collection<string, PayrollComponent>  $components
     * @param  array{code: string, name: string, amount: float|int, taxable?: bool}  $item
     */
    private function createItem(int $payrollId, Collection $components, array $item, string $type): void
    {
        PayrollItem::create([
            'payroll_id' => $payrollId,
            'component_id' => $components[$item['code']]->id ?? null,
            'component_code' => $item['code'],
            'component_name' => $item['name'],
            'type' => $type,
            'amount' => $item['amount'],
        ]);
    }

    /**
     * @return array{working_days: int, present_days: int, absent_days: int, leave_days: int}
     */
    private function calculateAttendanceStats(PayrollPeriod $period, Employee $employee): array
    {
        $start = Carbon::parse($period->start_date);
        $end = Carbon::parse($period->end_date);

        $workingDays = 0;
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if (! $d->isWeekend()) {
                $workingDays++;
            }
        }

        $present = Attendance::where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$start, $end])
            ->whereIn('status', [AttendanceStatus::Present, AttendanceStatus::Late])
            ->count();

        $leaveDays = (int) LeaveRequest::where('employee_id', $employee->id)
            ->where('status', RequestStatus::Approved)
            ->whereBetween('start_date', [$start, $end])
            ->sum('total_days');

        $absent = max(0, $workingDays - $present - $leaveDays);

        return [
            'working_days' => $workingDays,
            'present_days' => $present,
            'absent_days' => $absent,
            'leave_days' => $leaveDays,
        ];
    }

    private function calculateOvertimeMinutes(PayrollPeriod $period, Employee $employee): int
    {
        $totalHours = (float) Overtime::where('employee_id', $employee->id)
            ->where('status', RequestStatus::Approved)
            ->whereBetween('overtime_date', [$period->start_date, $period->end_date])
            ->sum('total_hours');

        return (int) round($totalHours * 60);
    }
}
