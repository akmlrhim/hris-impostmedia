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

        $data = [
            'title' => $this->title,
            'content' => $this->sanitizeHtml($this->content),
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

    /**
     * Sanitize rich-text HTML from the WYSIWYG editor.
     * - Strips all disallowed elements (preserving inner text)
     * - Removes every attribute except href on <a>
     * - Rejects javascript:/vbscript:/data: URIs in href
     * - Adds rel="noopener noreferrer" to external links
     */
    private function sanitizeHtml(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        // Phase 1 — strip disallowed elements (strip_tags keeps inner text)
        $allowedTags = '<p><br><strong><em><u><s><h1><h2><h3><ul><ol><li><a><blockquote><span>';
        $html = strip_tags($html, $allowedTags);

        // Phase 2 — parse with DOMDocument and scrub unsafe attributes
        $dom = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><body>'.$html.'</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $body = $dom->getElementsByTagName('body')->item(0);
        if ($body) {
            $this->scrubNode($dom, $body);
        }

        $result = '';
        foreach ($body?->childNodes ?? [] as $child) {
            $result .= $dom->saveHTML($child);
        }

        return $result;
    }

    private function scrubNode(\DOMDocument $dom, \DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            /** @var \DOMElement $child */
            $tag = strtolower($child->nodeName);
            $remove = [];

            foreach ($child->attributes as $attr) {
                $name = strtolower($attr->name);

                // Block all event handlers (onclick, onmouseover, …)
                if (str_starts_with($name, 'on')) {
                    $remove[] = $attr->name;

                    continue;
                }

                // Only allow href on <a> — validate URI scheme
                if ($tag === 'a' && $name === 'href') {
                    $scheme = strtolower(preg_replace('/[\x00-\x1F\x7F\s]/', '', $attr->value));
                    if (str_starts_with($scheme, 'javascript:') || str_starts_with($scheme, 'vbscript:') || str_starts_with($scheme, 'data:')) {
                        $remove[] = $attr->name;
                    }

                    continue;
                }

                // Drop every other attribute (class, style, id, data-*, …)
                $remove[] = $attr->name;
            }

            foreach ($remove as $attrName) {
                $child->removeAttribute($attrName);
            }

            // Ensure external links can't hijack the opener
            if ($tag === 'a' && $child->hasAttribute('href')) {
                $child->setAttribute('rel', 'noopener noreferrer');
                $child->setAttribute('target', '_blank');
            }

            $this->scrubNode($dom, $child);
        }
    }
}
