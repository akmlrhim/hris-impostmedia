<?php

namespace App\Livewire\Admin;

use App\Concerns\HandlesAdminActions;
use App\Models\Announcement;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Pengumuman')]
#[Layout('components.layouts.admin')]
class Announcements extends Component
{
	use HandlesAdminActions, WithPagination;

	public bool $showForm = false;

	public ?int $editingId = null;

	#[Validate('required|string|max:200')]
	public string $title = '';

	#[Validate('required|string')]
	public string $content = '';

	public string $audience = 'all';

	public bool $is_pinned = false;

	public bool $publish_now = true;

	public function open(?int $id = null): void
	{
		$this->reset(['title', 'content', 'audience', 'is_pinned', 'publish_now', 'editingId']);
		$this->resetValidation();

		if ($id) {
			$a = Announcement::findOrFail($id);
			$this->editingId = $id;
			$this->title = $a->title;
			$this->content = $a->content;
			$this->audience = $a->audience;
			$this->is_pinned = (bool) $a->is_pinned;
			$this->publish_now = (bool) $a->published_at;
		}

		$this->showForm = true;
	}

	public function save(): void
	{
		$this->validate();

		$allowedTags = '<p><br><strong><em><u><s><h1><h2><h3><ul><ol><li><a><blockquote><span>';

		$data = [
			'title' => $this->title,
			'content' => strip_tags($this->content, $allowedTags),
			'audience' => $this->audience,
			'is_pinned' => $this->is_pinned,
			'published_at' => $this->publish_now ? now() : null,
			'author_id' => auth()->id(),
		];

		$this->safeAction(function () use ($data) {
			if ($this->editingId) {
				Announcement::findOrFail($this->editingId)->update($data);
				$this->toast('success', 'Pengumuman diperbarui.');
			} else {
				Announcement::create($data);
				$this->toast('success', 'Pengumuman ditambahkan.');
			}

			$this->showForm = false;
		}, permission: 'manage_announcements', genericError: 'Gagal menyimpan pengumuman.');
	}

	public function delete(int $id): void
	{
		$this->safeAction(function () use ($id) {
			$ann = Announcement::findOrFail($id);
			$title = $ann->title;
			$ann->delete();
			$this->logActivity('announcement.deleted', "Menghapus pengumuman: {$title}", null, ['id' => $id, 'title' => $title]);
			$this->toast('success', 'Pengumuman dihapus.');
		}, permission: 'manage_announcements', genericError: 'Gagal menghapus pengumuman.');
	}

	public function mount(): void
	{
		Gate::authorize('manage_announcements');
	}

	public function render(): mixed
	{
		return view('livewire.admin.announcements', [
			'announcements' => Announcement::with('author')
				->orderByDesc('is_pinned')
				->latest()
				->paginate(10),
		]);
	}
}
