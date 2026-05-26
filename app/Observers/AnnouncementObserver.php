<?php

namespace App\Observers;

use App\Jobs\SendAnnouncementNotificationsJob;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Support\Collection;

class AnnouncementObserver
{
	public function created(Announcement $announcement): void
	{
		if ($announcement->published_at !== null) {
			$this->dispatchNotifications($announcement);
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
			$this->dispatchNotifications($announcement);
		}
	}

	private function dispatchNotifications(Announcement $announcement): void
	{
		$recipientIds = $this->getRecipientIds($announcement);

		if ($recipientIds->isEmpty()) {
			return;
		}

		SendAnnouncementNotificationsJob::dispatch($announcement, $recipientIds->all());
	}

	private function getRecipientIds(Announcement $announcement): Collection
	{
		$query = User::where('is_active', true)
			->whereNotNull('email')
			->where('email', '!=', '');

		if ($announcement->audience === 'employee') {
			$query->whereJsonContains('roles', 'employee');
		} elseif ($announcement->audience === 'admin') {
			$query->where(function ($q) {
				$q->whereJsonContains('roles', 'admin')
					->orWhereJsonContains('roles', 'hr');
			});
		}

		return $query->pluck('id');
	}
}
