<?php

namespace App\Jobs;

use App\Models\Holiday;
use App\Models\User;
use App\Notifications\HolidayAddedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendHolidayNotificationsJob implements ShouldQueue
{
	use Queueable;

	public function __construct(
		private readonly Holiday $holiday,
	) {}

	public function handle(): void
	{
		$recipients = User::where('is_active', true)
			->whereNotNull('email')
			->where('email', '!=', '')
			->get();

		Log::info('Mengirim notifikasi hari libur', [
			'holiday_id' => $this->holiday->id,
			'total_recipients' => $recipients->count(),
		]);

		foreach ($recipients as $index => $recipient) {
			// Resend rate limit: 2 req/sec — jeda 600ms agar aman
			if ($index > 0) {
				usleep(600_000);
			}

			try {
				$recipient->notify(new HolidayAddedNotification($this->holiday));
			} catch (\Throwable $e) {
				Log::error('Gagal mengirim notifikasi hari libur', [
					'holiday_id' => $this->holiday->id,
					'user_id' => $recipient->id,
					'email' => $recipient->email,
					'message' => $e->getMessage(),
				]);
			}
		}
	}
}
