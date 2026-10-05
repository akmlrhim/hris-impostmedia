<?php

namespace App\Livewire\Employee;

use App\Models\Announcement;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Pengumuman')]
class AnnouncementShow extends Component
{
    public Announcement $announcement;

    public function mount(Announcement $announcement): void
    {
        abort_unless(
            $announcement->currentlyPublished()->visibleTo(auth()->user())->exists(),
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
