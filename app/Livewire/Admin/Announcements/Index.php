<?php

namespace App\Livewire\Admin\Announcements;

use App\Models\Announcement;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class Index extends Component
{
	use WithPagination;

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

		if ($this->editingId) {
			Announcement::findOrFail($this->editingId)->update($data);
		} else {
			Announcement::create($data);
		}

		$this->showForm = false;
	}

	public function delete(int $id): void
	{
		Announcement::findOrFail($id)->delete();
	}

	public function render(): mixed
	{
		return view('livewire.admin.announcements.index', [
			'announcements' => Announcement::with('author')
				->orderByDesc('is_pinned')
				->latest()
				->paginate(10),
		]);
	}
}
