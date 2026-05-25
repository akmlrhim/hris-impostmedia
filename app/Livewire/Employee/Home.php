<?php

namespace App\Livewire\Employee;

use App\Enums\WorkType;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Holiday;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Beranda')]
class Home extends Component
{
	public function render(): mixed
	{
		$employee = auth()->user()?->employee;
		$today = now()->toDateString();

		$todayAttendance = $employee
			? Attendance::where('employee_id', $employee->id)
			->whereDate('attendance_date', $today)
			->first()
			: null;

		$announcements = Announcement::whereNotNull('published_at')
			->where('published_at', '<=', now())
			->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
			->orderByDesc('is_pinned')
			->latest('published_at')
			->limit(5)
			->get();

		$upcomingHolidays = Holiday::where('date', '>=', now()->toDateString())
			->orderBy('date')
			->limit(3)
			->get();

		$isWfo = $employee?->work_type === WorkType::WFO;

		return view('livewire.employee.home', compact('employee', 'todayAttendance', 'announcements', 'upcomingHolidays', 'isWfo'));
	}
}
