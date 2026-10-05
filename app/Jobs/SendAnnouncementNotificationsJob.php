<?php

namespace App\Jobs;

use App\Models\Announcement;
use App\Models\User;
use App\Notifications\AnnouncementPublishedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendAnnouncementNotificationsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 110;

    public function __construct(public readonly int $announcementId) {}

    public function handle(): void
    {
        $announcement = Announcement::find($this->announcementId);

        if (! $announcement) {
            return;
        }

        $recipients = $announcement->audience === 'selected'
            ? $announcement->recipients()->get()
            : User::where('is_active', true)->whereNotNull('email')->where('email', '!=', '')->get();

        Log::info('Mengirim notifikasi pengumuman', [
            'announcement_id' => $announcement->id,
            'total_recipients' => $recipients->count(),
        ]);

        foreach ($recipients as $index => $recipient) {
            // ponytail: jeda 600ms/recipient demi rate limit Resend — ganti ShouldQueue per-user bila recipient > ~150
            if ($index > 0) {
                usleep(600_000);
            }

            try {
                $recipient->notify(new AnnouncementPublishedNotification($announcement));
            } catch (\Throwable $e) {
                Log::error('Gagal mengirim notifikasi pengumuman', [
                    'announcement_id' => $announcement->id,
                    'user_id' => $recipient->id,
                    'email' => $recipient->email,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }
}
