<?php

namespace App\Observers;

use App\Jobs\SendHolidayNotificationsJob;
use App\Models\Holiday;

class HolidayObserver
{
	public function created(Holiday $holiday): void
	{
		SendHolidayNotificationsJob::dispatch($holiday);
	}
}
