<?php

namespace App\Livewire\Admin;

use App\Concerns\HandlesAdminActions;
use App\Enums\AttendanceStatus;
use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Models\Attendance as AttendanceModel;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Services\WorkScheduleService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Data Absensi')]
#[Layout('components.layouts.admin')]
class Attendance extends Component
{
    use HandlesAdminActions, WithPagination;

    /** Pseudo-status for employees with no attendance row on the selected date. */
    public const STATUS_MISSING = 'not_recorded';

    public const TAB_DAILY = 'daily';

    public const TAB_RECAP = 'recap';

    /** Statuses counted as physically present when summing "Total Hadir". */
    private const PRESENT_STATUSES = [
        AttendanceStatus::Present,
        AttendanceStatus::Late,
        AttendanceStatus::EarlyLeave,
    ];

    #[Url]
    public string $tab = self::TAB_DAILY;

    #[Url]
    public string $date = '';

    #[Url]
    public string $status = '';

    /** Recap period in Y-m format. */
    #[Url]
    public string $month = '';

    protected WorkScheduleService $schedule;

    public function boot(WorkScheduleService $schedule): void
    {
        $this->schedule = $schedule;
    }

    public function mount(): void
    {
        Gate::authorize('manage_attendance');

        $this->date = $this->date ?: now()->toDateString();
        $this->month = $this->normalizeMonth($this->month);

        if (! in_array($this->tab, [self::TAB_DAILY, self::TAB_RECAP], true)) {
            $this->tab = self::TAB_DAILY;
        }
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, [self::TAB_DAILY, self::TAB_RECAP], true)) {
            return;
        }

        $this->tab = $tab;
        $this->resetPage();
    }

    public function updatingDate(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatedMonth(): void
    {
        $this->month = $this->normalizeMonth($this->month);
        $this->resetPage();
    }

    public function render(): mixed
    {
        return $this->tab === self::TAB_RECAP
            ? view('livewire.admin.attendance', $this->recapData())
            : view('livewire.admin.attendance', $this->dailyData());
    }

    /**
     * The roster is driven by employees rather than attendance rows, so people
     * who never checked in on the selected date still show up.
     *
     * @return array{employees: LengthAwarePaginator}
     */
    private function dailyData(): array
    {
        $date = $this->date ?: now()->toDateString();

        $onDate = fn ($query) => $query->whereDate('attendance_date', $date);

        $employees = Employee::query()
            ->where('is_active', true)
            ->with(['attendances' => $onDate])
            ->when(
                $this->status === self::STATUS_MISSING,
                fn ($q) => $q->whereDoesntHave('attendances', $onDate),
            )
            ->when(
                $this->status !== '' && $this->status !== self::STATUS_MISSING,
                fn ($q) => $q->whereHas(
                    'attendances',
                    fn ($a) => $onDate($a)->where('status', $this->status),
                ),
            )
            ->orderBy('full_name')
            ->paginate(20);

        return ['employees' => $employees];
    }

    /**
     * Per-employee attendance tally for the selected month.
     *
     * Each working day is classified exactly once, in priority order:
     * an attendance record wins, then an approved leave request, and anything
     * left over on an elapsed working day counts as absent without notice.
     * Days off never count against anyone.
     *
     * @return array{employees: LengthAwarePaginator, recap: array<int, array<string, int>>, monthLabel: string, workingDays: int}
     */
    private function recapData(): array
    {
        $start = $this->monthStart();
        $end = $start->copy()->endOfMonth();

        $employees = Employee::query()
            ->where('is_active', true)
            ->orderBy('full_name')
            ->paginate(20);

        $employeeIds = $employees->pluck('id');

        // Hari yang belum lewat tidak bisa dinilai: tanpa batas ini semua orang
        // akan terlihat bolos sisa bulan setiap kali rekap dibuka di awal bulan.
        $lastAssessableDay = now()->subDay()->startOfDay()->min($end);
        $workingDates = $this->schedule->workingDatesBetween($start, $lastAssessableDay);

        $records = AttendanceModel::query()
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get(['employee_id', 'attendance_date', 'status', 'late_minutes', 'work_minutes'])
            ->groupBy('employee_id');

        $leaves = LeaveRequest::query()
            ->whereIn('employee_id', $employeeIds)
            ->where('status', LeaveStatus::Approved)
            ->whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date', '>=', $start->toDateString())
            ->get(['employee_id', 'type', 'start_date', 'end_date'])
            ->groupBy('employee_id');

        $recap = [];

        foreach ($employees as $employee) {
            $recap[$employee->id] = $this->tallyEmployee(
                $employee,
                $workingDates,
                $records->get($employee->id, collect()),
                $leaves->get($employee->id, collect()),
            );
        }

        return [
            'employees' => $employees,
            'recap' => $recap,
            'monthLabel' => $start->translatedFormat('F Y'),
            'workingDays' => count($workingDates),
        ];
    }

    /**
     * @param  array<int, string>  $workingDates
     * @param  Collection<int, AttendanceModel>  $records
     * @param  Collection<int, LeaveRequest>  $leaves
     * @return array<string, int>
     */
    private function tallyEmployee(Employee $employee, array $workingDates, Collection $records, Collection $leaves): array
    {
        $row = array_fill_keys(array_column(AttendanceStatus::cases(), 'value'), 0);
        $row['permission'] = 0;
        $row['present_total'] = 0;
        $row['recorded_total'] = 0;
        $row['late_minutes'] = 0;
        $row['work_minutes'] = 0;
        $row['working_days'] = 0;

        // Baris absensi menang atas apa pun, termasuk yang jatuh di hari libur
        // (lembur akhir pekan tetap dihitung hadir).
        $covered = [];

        foreach ($records as $record) {
            $status = $record->status;

            if ($status === null) {
                continue;
            }

            $covered[$record->attendance_date->toDateString()] = true;
            $row[$status->value]++;
            $row['recorded_total']++;
            $row['late_minutes'] += (int) $record->late_minutes;
            $row['work_minutes'] += (int) $record->work_minutes;

            if (in_array($status, self::PRESENT_STATUSES, true)) {
                $row['present_total']++;
            }
        }

        $leaveByDate = $this->expandLeaveDates($leaves);

        foreach ($workingDates as $date) {
            if (! $this->isUnderContract($employee, $date)) {
                continue;
            }

            $row['working_days']++;

            if (isset($covered[$date])) {
                continue;
            }

            // Tanpa baris absensi: cuti/izin/sakit yang disetujui menutupi hari
            // ini, sisanya berarti tidak hadir tanpa keterangan.
            match ($leaveByDate[$date] ?? null) {
                LeaveType::Annual => $row['leave']++,
                LeaveType::Sick => $row['sick']++,
                LeaveType::Permission => $row['permission']++,
                default => $row['absent']++,
            };
        }

        return $row;
    }

    /**
     * @param  Collection<int, LeaveRequest>  $leaves
     * @return array<string, LeaveType>
     */
    private function expandLeaveDates(Collection $leaves): array
    {
        $byDate = [];

        foreach ($leaves as $leave) {
            $day = $leave->start_date->copy()->startOfDay();
            $last = $leave->end_date->copy()->startOfDay();

            for (; $day->lte($last); $day->addDay()) {
                $byDate[$day->toDateString()] = $leave->type;
            }
        }

        return $byDate;
    }

    /** Nobody is marked absent before they joined or after their contract ended. */
    private function isUnderContract(Employee $employee, string $date): bool
    {
        $start = $employee->contract_start_date;
        $end = $employee->contract_end_date;

        if ($start && $date < $start->toDateString()) {
            return false;
        }

        return ! ($end && $date > $end->toDateString());
    }

    private function monthStart(): Carbon
    {
        return Carbon::createFromFormat('Y-m', $this->normalizeMonth($this->month))->startOfMonth();
    }

    /** Falls back to the current month when the URL carries a malformed value. */
    private function normalizeMonth(?string $month): string
    {
        if (! $month || preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) !== 1) {
            return now()->format('Y-m');
        }

        return $month;
    }
}
