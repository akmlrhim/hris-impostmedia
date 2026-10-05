<div class="space-y-4">
  <x-page-header title="Pengumuman" description="Sebarkan informasi internal ke karyawan.">
    <x-slot:action>
      <button type="button" @click="$wire.set('showForm', true, false); $wire.open()" class="btn-primary">Pengumuman Baru</button>
    </x-slot:action>
  </x-page-header>

  <x-modal show="showForm" max-width="2xl" :title="$editingId ? 'Ubah Pengumuman' : 'Pengumuman Baru'">
    {{-- Sync konten Quill sebelum submit: debounce 300ms di quill.js bisa belum sempat terkirim. --}}
    <form id="announcement-form" @submit.prevent="
        const html = $el.querySelector('.ql-editor')?.innerHTML ?? '';
        $wire.set('content', html === '<p><br></p>' ? '' : html);
        await $wire.save();
      " class="space-y-4">

      <div>
        <label class="label">Judul</label>
        <input wire:model="title" placeholder="Masukkan judul pengumuman" class="input" maxlength="200">
        @error('title') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="label">Konten</label>
        <div wire:ignore x-data="quillEditor($wire, 'content')">
          <div x-ref="editor"></div>
        </div>
        @error('content') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="label">Target</label>
          <select wire:model.live="audience" class="input">
            <option value="all">Semua Karyawan</option>
            <option value="selected">Pilih Karyawan</option>
          </select>
        </div>
        <div>
          <label class="label">Berlaku Sampai (opsional)</label>
          <input type="date" wire:model="expires_at" class="input">
          <p class="text-[11px] text-slate-500 mt-1">Setelah tanggal ini pengumuman tidak lagi tampil di beranda karyawan.</p>
          @error('expires_at') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
      </div>

      @if ($audience === 'selected')
        {{-- Cari & pilih-semua jalan di Alpine: tanpa request, checkbox tetap wire:model. --}}
        <div x-data="{
            q: '',
            picked: {{ count($target_users) }},
            boxes() { return [...this.$refs.list.querySelectorAll('input[type=checkbox]')].filter(c => c.dataset.name.includes(this.q.toLowerCase())); },
            count() { this.picked = this.boxes().filter(c => c.checked).length; },
            toggleAll(checked) { this.boxes().forEach(c => { if (c.checked !== checked) c.click(); }); },
          }">
          <div class="flex items-center justify-between gap-3 mb-1.5">
            <label class="label mb-0">Karyawan <span class="text-red-500">*</span></label>
            <div class="flex items-center gap-2 text-[11px]">
              <span class="text-slate-500"><span x-text="picked" class="font-medium"></span> dipilih</span>
              <span class="text-slate-300">·</span>
              <button type="button" @click="toggleAll(true)"
                class="font-medium text-brand-600 hover:text-brand-700">Pilih semua</button>
              <span class="text-slate-300">·</span>
              <button type="button" @click="toggleAll(false)"
                class="font-medium text-slate-500 hover:text-slate-700">Kosongkan</button>
            </div>
          </div>

          <div class="relative mb-2">
            <x-icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <input type="search" x-model="q" @input="count()" placeholder="Cari nama karyawan…"
              class="input !py-1.5 pl-9 text-xs">
          </div>

          <div x-ref="list" class="max-h-52 overflow-y-auto rounded-lg border border-slate-200 divide-y divide-slate-100">
            @forelse ($users as $u)
              <label wire:key="pick-{{ $u->id }}"
                x-show="q === '' || @js(mb_strtolower($u->name)).includes(q.toLowerCase())"
                class="flex items-center gap-2.5 px-3 py-2 cursor-pointer hover:bg-slate-50">
                <input type="checkbox" wire:model="target_users" value="{{ $u->id }}"
                  data-name="{{ mb_strtolower($u->name) }}" @change="count()"
                  class="rounded border-slate-300 shrink-0">
                <span class="text-sm text-slate-700 truncate">{{ $u->name }}</span>
              </label>
            @empty
              <p class="px-3 py-4 text-center text-xs text-slate-500">Belum ada karyawan aktif.</p>
            @endforelse
          </div>

          @error('target_users') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
      @endif

      <div class="flex flex-wrap gap-6">
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" wire:model="is_pinned" class="rounded border-slate-300">
          <span class="text-sm text-slate-700">Pin di atas</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" wire:model="publish_now" class="rounded border-slate-300">
          <span class="text-sm text-slate-700">Publikasikan sekarang</span>
        </label>
      </div>
    </form>

    <x-slot:footer>
      <div class="flex items-center gap-3 justify-end">
        <button type="button" @click="$wire.set('showForm', false, true)" class="btn-secondary">Batal</button>
        <button type="submit" form="announcement-form" class="btn-primary">Simpan</button>
      </div>
    </x-slot:footer>
  </x-modal>

  <div class="card-table">
    <div class="overflow-x-auto">
      <table class="table-grid">
        <thead class="bg-slate-50">
          <tr class="text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">
            <th class="px-5 py-3">Pengumuman</th>
            <th class="px-5 py-3 whitespace-nowrap">Target</th>
            <th class="px-5 py-3 whitespace-nowrap">Status</th>
            <th class="px-5 py-3 text-right whitespace-nowrap">Aksi</th>
          </tr>
        </thead>
        <tbody class="text-sm">
          @forelse ($announcements as $a)
            <tr class="hover:bg-slate-50 align-top" wire:key="ann-{{ $a->id }}">
              <td class="px-5 py-3">
                <div class="flex items-center gap-2">
                  <p class="font-medium text-slate-900">{{ $a->title }}</p>
                  @if ($a->is_pinned)
                    <span class="badge bg-amber-100 text-amber-700 shrink-0">Disematkan</span>
                  @endif
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                  Oleh {{ $a->author?->name ?? '-' }}
                  @if ($a->published_at)
                    · {{ $a->published_at->translatedFormat('d M Y H:i') }}
                  @endif
                </p>
                <div class="ql-snow mt-1.5">
                  <div class="ql-editor ql-readonly line-clamp-2">{!! $a->content !!}</div>
                </div>
              </td>
              <td class="px-5 py-3 whitespace-nowrap text-slate-600 text-xs">
                @if ($a->audience === 'selected')
                  {{ $a->recipients_count }} karyawan
                @else
                  Semua karyawan
                @endif
              </td>
              <td class="px-5 py-3 whitespace-nowrap">
                @if (! $a->published_at)
                  <span class="badge bg-slate-100 text-slate-700">Draft</span>
                @elseif ($a->expires_at?->isPast())
                  <span class="badge bg-slate-100 text-slate-500">Kedaluwarsa</span>
                @else
                  <span class="badge bg-emerald-100 text-emerald-700">Tayang</span>
                @endif
              </td>
              <td class="px-5 py-3 text-right whitespace-nowrap">
                <x-action-menu>
                  <button type="button" @click="open = false; $wire.set('showForm', true, false); $wire.open({{ $a->id }})"
                    class="w-full text-left px-3 py-2 text-xs text-slate-700 hover:bg-slate-50 flex items-center gap-2">
                    <x-icon name="pencil" class="w-3.5 h-3.5 text-amber-500" /> Edit
                  </button>
                  <button wire:click="delete({{ $a->id }})" wire:confirm="Hapus pengumuman ini?" @click="open = false"
                    class="w-full text-left px-3 py-2 text-xs text-red-600 hover:bg-red-50 flex items-center gap-2">
                    <x-icon name="trash" class="w-3.5 h-3.5" /> Hapus
                  </button>
                </x-action-menu>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-5 py-12 text-center text-slate-500">
                Belum ada pengumuman.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if ($announcements->hasPages())
      <div class="px-5 py-3 border-t border-slate-100">{{ $announcements->links() }}</div>
    @endif
  </div>
</div>