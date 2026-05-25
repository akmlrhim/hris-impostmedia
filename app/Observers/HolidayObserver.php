<?php

namespace App\Observers;

use App\Models\Holiday;
use App\Models\User;
use App\Notifications\HolidayAddedNotification;
use Illuminate\Support\Facades\Notification;

class HolidayObserver
{
    public function created(Holiday $holiday): void
    {
        $recipients = User::where('is_active', true)
            ->whereNotNull('email')
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new HolidayAddedNotification($holiday));
    }
}
