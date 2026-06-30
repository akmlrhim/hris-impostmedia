<?php

namespace App\Services\Payroll;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollComponent;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PayrollGenerator
{
    /**
     * Generate payrolls for active employees in a period.
     *
     * @param  int[]|null  $employeeIds  When provided, only generate for these employees; otherwise all active employees.
     * @return int Number of payroll records generated
     */
    public function generateForPeriod(PayrollPeriod $period, ?array $employeeIds = null): int
    {
        return DB::transaction(function () use ($period, $employeeIds): int {
            // Wipe existing draft payrolls so this is idempotent. When a subset of
            // employees is selected, only their drafts are replaced so previously
            // generated payrolls for other employees stay intact.
            Payroll::where('payroll_period_id', $period->id)
                ->where('status', 'draft')
                ->when($employeeIds !== null, fn ($q) => $q->whereIn('employee_id', $employeeIds))
                ->delete();

            $components = PayrollComponent::where('is_active', true)->get()->keyBy('code');
            $employees = Employee::where('is_active', true)
                ->when($employeeIds !== null, fn ($q) => $q->whereIn('id', $employeeIds))
                ->get();
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
        $basicSalary = (float) $employee->basic_salary;

        $earnings = [[
            'code' => 'BASIC',
            'name' => $components['BASIC']->name ?? 'Gaji Pokok',
            'amount' => $basicSalary,
        ]];

        $payroll = Payroll::create([
            'payroll_period_id' => $period->id,
            'employee_id' => $employee->id,
            'basic_salary' => $basicSalary,
            'total_earnings' => $basicSalary,
            'total_deductions' => 0,
            'total_bpjs' => 0,
            'total_tax_pph21' => 0,
            'gross_salary' => $basicSalary,
            'net_salary' => $basicSalary,
            'working_days' => $stats['working_days'],
            'present_days' => $stats['present_days'],
            'absent_days' => $stats['absent_days'],
            'leave_days' => 0,
            'overtime_minutes' => 0,
            'overtime_amount' => 0,
            'status' => 'draft',
        ]);

        foreach ($earnings as $item) {
            $this->createItem($payroll->id, $components, $item, 'earning');
        }

        return $payroll;
    }

    /**
     * @param  Collection<string, PayrollComponent>  $components
     * @param  array{code: string, name: string, amount: float|int}  $item
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

        $absent = max(0, $workingDays - $present);

        return [
            'working_days' => $workingDays,
            'present_days' => $present,
            'absent_days' => $absent,
            'leave_days' => 0,
        ];
    }
}
