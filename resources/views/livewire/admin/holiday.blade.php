<div class="space-y-4">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div>
      <h2 class="text-base font-semibold text-slate-900">Hari Libur</h2>
      <p class="text-sm text-slate-500">Kelola hari libur nasional & cuti bersama.</p>
    </div>
    <div class="flex gap-2">
      <select wire:model.live="year" class="input">
        @foreach ($availableYears as $y)
          <option value="{{ $y }}">{{ $y }}</option>
        @endforeach
      </select>
      <button wire:click="open" class="btn-primary">
        <x-icon name="plus" class="w-4 h-4" /> Tambah
      </button>
    </div>
  </div>

  {{-- Form modal --}}
  <x-modal show="showForm" max-width="lg" :title="$editingId ? 'Ubah Hari Libur' : 'Tambah Hari Libur'">
    <form wire:submit="save" class="space-y-4" wire:key="holiday-form-{{ $editingId ?? 'new' }}">
      <div>
        <label class="label">Tanggal <span class="text-red-500">*</span></label>
        <input type="date" wire:model="date" class="input">
        @error('date')
          <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
      </div>
      <div>
        <label class="label">Nama <span class="text-red-500">*</span></label>
        <input wire:model="name" class="input" placeholder="Hari Raya Idul Fitri">
        @error('name')
          <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
      </div>
      <div>
        <label class="label">Keterangan</label>
        <textarea wire:model="description" rows="2" class="input"></textarea>
      </div>
      <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" wire:model="is_national" class="rounded border-slate-300">
        Libur nasional (berlaku untuk semua karyawan)
      </label>

      <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
        <button type="button" wire:click="$set('showForm', false)" class="btn-secondary">Batal</button>
        <button type="submit" class="btn-primary" wire:loading.attr="disabled">
          <span wire:loading.remove wire:target="save">Simpan</span>
          <span wire:loading wire:target="save">Menyimpan…</span>
        </button>
      </div>
    </form>
  </x-modal>

  {{-- Table --}}
  <div class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr class="text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">
            <th class="px-5 py-3 w-40">Tanggal</th>
            <th class="px-5 py-3 w-28">Hari</th>
            <th class="px-5 py-3">Nama</th>
            <th class="px-5 py-3 w-32">Tipe</th>
            <th class="px-5 py-3 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm">
          @forelse ($holidays as $h)
            <tr class="hover:bg-slate-50">
              <td class="px-5 py-3 text-slate-700">{{ $h->date->translatedFormat('d M Y') }}</td>
              <td class="px-5 py-3 text-slate-500">{{ $h->date->translatedFormat('l') }}</td>
              <td class="px-5 py-3">
                <p class="font-medium text-slate-900">{{ $h->name }}</p>
                @if ($h->description)
                  <p class="text-xs text-slate-500">{{ $h->description }}</p>
                @endif
              </td>
              <td class="px-5 py-3">
                @if ($h->is_national)
                  <span class="badge bg-red-100 text-red-700">Nasional</span>
                @else
                  <span class="badge bg-slate-100 text-slate-700">Internal</span>
                @endif
              </td>
              <td class="px-5 py-3">
                <div class="flex items-center justify-end gap-2">
                  <button wire:click="open({{ $h->id }})"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-amber-50 text-amber-600 hover:bg-amber-100 transition">
                    <x-icon name="pencil" class="w-3.5 h-3.5" /> Edit
                  </button>
                  <button wire:click="delete({{ $h->id }})"
                    wire:confirm="Hapus hari libur '{{ $h->name }}'?"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition">
                    <x-icon name="trash" class="w-3.5 h-3.5" /> Hapus
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-5 py-12 text-center text-slate-500">
                Belum ada hari libur untuk {{ $year }}.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
