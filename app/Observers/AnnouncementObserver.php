<?php

namespace App\Observers;

use App\Jobs\SendAnnouncementNotificationsJob;
use App\Models\Announcement;

class AnnouncementObserver
{
    public function created(Announcement $announcement): void
    {
        if ($announcement->published_at !== null) {
            SendAnnouncementNotificationsJob::dispatch($announcement->id);
        }
    }

    public function updated(Announcement $announcement): void
    {
        if ($announcement->wasChanged('published_at') && $announcement->published_at !== null) {
            SendAnnouncementNotificationsJob::dispatch($announcement->id);
        }
    }
}
