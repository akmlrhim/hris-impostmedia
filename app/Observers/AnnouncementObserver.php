<?php

namespace App\Observers;

use App\Models\Announcement;
use App\Models\User;
use App\Notifications\AnnouncementPublishedNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;

class AnnouncementObserver
{
    public function created(Announcement $announcement): void
    {
        if ($announcement->published_at !== null) {
            $this->sendNotifications($announcement);
        }
    }

    public function updated(Announcement $announcement): void
    {
        // Kirim notifikasi hanya saat status berubah dari draft → published
        if (
            $announcement->wasChanged('published_at') &&
            $announcement->published_at !== null &&
            $announcement->getOriginal('published_at') === null
        ) {
            $this->sendNotifications($announcement);
        }
    }

    private function sendNotifications(Announcement $announcement): void
    {
        $recipients = $this->getRecipients($announcement);

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new AnnouncementPublishedNotification($announcement));
    }

    private function getRecipients(Announcement $announcement): Collection
    {
        $query = User::where('is_active', true)->whereNotNull('email');

        if ($announcement->audience === 'employee') {
            $query->whereJsonContains('roles', 'employee');
        } elseif ($announcement->audience === 'admin') {
            $query->where(function ($q) {
                $q->whereJsonContains('roles', 'admin')
                    ->orWhereJsonContains('roles', 'hr');
            });
        }

        return $query->get();
    }
}
