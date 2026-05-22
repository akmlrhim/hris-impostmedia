<div class="space-y-4">
  <x-page-header title="Pengumuman" description="Sebarkan informasi internal ke karyawan.">
    <x-slot:action>
      <button wire:click="open" class="btn-primary">Pengumuman Baru</button>
    </x-slot:action>
  </x-page-header>

  <x-modal show="showForm" max-width="2xl" :title="$editingId ? 'Ubah Pengumuman' : 'Pengumuman Baru'">
    <form
      @submit.prevent="
        const el = $el.querySelector('.ql-editor');
        const html = el ? el.innerHTML : '';
        const isEmpty = html === '<p><br></p>' || !html.trim();
        await $wire.set('content', isEmpty ? '' : html);
        $wire.save();
      "
      class="space-y-4"
      wire:key="announcement-form-{{ $editingId ?? 'new' }}"
    >
      <div>
        <label class="label">Judul</label>
        <input wire:model="title" placeholder="Masukkan judul pengumuman" class="input" maxlength="200">
        @error('title')
          <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
      </div>
      <div>
        <label class="label">Konten</label>
        <div wire:ignore x-data="quillEditor($wire, 'content')">
          <div x-ref="editor"></div>
        </div>
        @error('content')
          <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="label">Target</label>
          <select wire:model="audience" class="input">
            <option value="all">Semua Karyawan</option>
          </select>
        </div>
        <label class="flex items-center gap-2 mt-7 text-sm">
          <input type="checkbox" wire:model="is_pinned" class="rounded border-slate-300">
          Pin di atas
        </label>
        <label class="flex items-center gap-2 mt-7 text-sm">
          <input type="checkbox" wire:model="publish_now" class="rounded border-slate-300">
          Publikasikan sekarang
        </label>
      </div>
      <x-form-actions />
    </form>
  </x-modal>

  <div class="space-y-3">
    @forelse ($announcements as $a)
      <div class="card p-4 sm:p-5">
        <div class="flex items-start justify-between gap-3">
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
              @if ($a->is_pinned)
                <span class="badge bg-amber-100 text-amber-700">📌 Pinned</span>
              @endif
              @if ($a->published_at)
                <span class="badge bg-emerald-100 text-emerald-700">Published</span>
              @else
                <span class="badge bg-slate-100 text-slate-700">Draft</span>
              @endif
            </div>
            <h3 class="font-semibold text-slate-900">{{ $a->title }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">
              Oleh {{ $a->author?->name ?? '-' }}
              @if ($a->published_at)
                · {{ $a->published_at->diffForHumans() }}
              @endif
            </p>
            <div class="ql-snow mt-2">
              <div class="ql-editor ql-readonly line-clamp-3">{!! $a->content !!}</div>
            </div>
          </div>
          <div class="flex flex-col gap-1.5 shrink-0">
            <button wire:click="open({{ $a->id }})"
              class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-amber-50 text-amber-600 hover:bg-amber-100 transition">
              Edit
            </button>
            <button wire:click="delete({{ $a->id }})" wire:confirm="Hapus pengumuman?"
              class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition">
              Hapus
            </button>
          </div>
        </div>
      </div>
    @empty
      <div class="card p-10 text-center text-sm text-slate-500">Belum ada pengumuman.</div>
    @endforelse
  </div>

  {{ $announcements->links() }}
</div>
