<div class="space-y-4">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div>
      <h2 class="text-base font-semibold text-slate-900">Daftar Shift</h2>
      <p class="text-sm text-slate-500">Kelola jam kerja & toleransi keterlambatan.</p>
    </div>
    <button wire:click="open" class="btn-primary">
      <x-icon name="plus" class="w-4 h-4" /> Shift Baru
    </button>
  </div>

  <x-modal show="showForm" max-width="2xl" :title="$editingId ? 'Ubah Shift' : 'Shift Baru'">
    <form wire:submit="save" class="space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="label">Kode</label>
          <input wire:model="code" class="input" maxlength="30" placeholder="REG">
          @error('code')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label class="label">Nama</label>
          <input wire:model="name" class="input" placeholder="Reguler 09-18">
          @error('name')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label class="label">Mulai</label>
          <input type="time" onclick="this.showPicker()" wire:model="start_time" class="input">
        </div>
        <div>
          <label class="label">Selesai</label>
          <input type="time" onclick="this.showPicker()" wire:model="end_time" class="input">
        </div>
        <div>
          <label class="label">Istirahat Mulai</label>
          <input type="time" onclick="this.showPicker()" wire:model="break_start" class="input">
        </div>
        <div>
          <label class="label">Istirahat Selesai</label>
          <input type="time" onclick="this.showPicker() wire:model="break_end" class="input">
        </div>
        <div class="md:col-span-2">
          <label class="label">Toleransi Terlambat (menit)</label>
          <input type="number" wire:model="late_tolerance_minutes" class="input" min="0">
        </div>
      </div>
      <div class="flex gap-2 justify-end pt-2 border-t border-slate-100">
        <button type="button" wire:click="$set('showForm', false)" class="btn-secondary">Batal</button>
        <button type="submit" class="btn-primary">Simpan</button>
      </div>
    </form>
  </x-modal>

  <div class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr class="text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">
            <th class="px-5 py-3">Kode</th>
            <th class="px-5 py-3">Nama</th>
            <th class="px-5 py-3">Jam Kerja</th>
            <th class="px-5 py-3">Istirahat</th>
            <th class="px-5 py-3">Toleransi</th>
            <th class="px-5 py-3 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm">
          @forelse ($shifts as $s)
            <tr class="hover:bg-slate-50">
              <td class="px-5 py-3 font-mono">{{ $s->code }}</td>
              <td class="px-5 py-3 font-medium">{{ $s->name }}</td>
              <td class="px-5 py-3">{{ \Illuminate\Support\Str::limit($s->start_time, 5, '') }} –
                {{ \Illuminate\Support\Str::limit($s->end_time, 5, '') }}</td>
              <td class="px-5 py-3 text-slate-600">
                @if ($s->break_start)
                  {{ \Illuminate\Support\Str::limit($s->break_start, 5, '') }} –
                  {{ \Illuminate\Support\Str::limit($s->break_end, 5, '') }}
                @else
                  —
                @endif
              </td>
              <td class="px-5 py-3">{{ $s->late_tolerance_minutes }} mnt</td>
              <td class="px-5 py-3">
                <div class="flex items-center justify-end gap-2">
                  <button wire:click="open({{ $s->id }})"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-amber-50 text-amber-600 hover:bg-amber-100 transition">
                    <x-icon name="pencil" class="w-3.5 h-3.5" /> Edit
                  </button>
                  <button wire:click="delete({{ $s->id }})" wire:confirm="Hapus shift ini?"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition">
                    <x-icon name="trash" class="w-3.5 h-3.5" /> Hapus
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-5 py-12 text-center text-slate-500">Belum ada shift.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
