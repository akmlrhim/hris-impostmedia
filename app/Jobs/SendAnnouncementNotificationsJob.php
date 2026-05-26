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

	/** @param int[] $recipientIds */
	public function __construct(
		private readonly Announcement $announcement,
		private readonly array $recipientIds,
	) {}

	public function handle(): void
	{
		$recipients = User::whereIn('id', $this->recipientIds)->get();

		Log::info('Mengirim notifikasi pengumuman', [
			'announcement_id' => $this->announcement->id,
			'total_recipients' => $recipients->count(),
		]);

		foreach ($recipients as $index => $recipient) {
			// Resend rate limit: 2 req/sec — jeda 600ms agar aman
			if ($index > 0) {
				usleep(600_000);
			}

			try {
				$recipient->notify(new AnnouncementPublishedNotification($this->announcement));
			} catch (\Throwable $e) {
				Log::error('Gagal mengirim notifikasi pengumuman', [
					'announcement_id' => $this->announcement->id,
					'user_id' => $recipient->id,
					'email' => $recipient->email,
					'message' => $e->getMessage(),
				]);
			}
		}
	}
}
