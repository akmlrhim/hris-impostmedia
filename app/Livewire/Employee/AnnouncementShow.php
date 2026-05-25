<?php

namespace App\Livewire\Employee;

use App\Models\Announcement;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
class AnnouncementShow extends Component
{
	public Announcement $announcement;

	public function mount(Announcement $announcement): void
	{
		abort_unless(
			$announcement->published_at && $announcement->published_at->isPast()
				&& (! $announcement->expires_at || $announcement->expires_at->isFuture()),
			404
		);

		$this->announcement = $announcement->load('author');
	}

	public function render(): mixed
	{
		return view('livewire.employee.announcement-show')
			->title($this->announcement->title);
	}
}
