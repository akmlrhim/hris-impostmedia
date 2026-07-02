<div class="space-y-4">
  <x-page-header title="Hari Libur" description="Kelola daftar hari libur nasional & cuti bersama. Karyawan tidak wajib absen pada tanggal ini.">
    <x-slot:action>
      <button type="button" @click="$wire.set('showForm', true, true); $wire.open()" class="btn-primary">Tambah Hari Libur</button>
    </x-slot:action>
  </x-page-header>

  <x-modal show="showForm" max-width="lg" :title="$editingId ? 'Ubah Hari Libur' : 'Tambah Hari Libur'">
    <form wire:submit="save" class="space-y-4">
      <div>
        <label class="label">Tanggal</label>
        <input type="date" wire:model="date" class="input">
        @error('date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>
      <div>
        <label class="label">Nama Hari Libur</label>
        <input wire:model="holiday_name" class="input" placeholder="Contoh: Hari Kemerdekaan RI">
        @error('holiday_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>
      <div>
        <label class="label">Keterangan (opsional)</label>
        <input wire:model="description" class="input" placeholder="Contoh: Cuti bersama">
        @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <x-form-actions />
    </form>
  </x-modal>

  <div class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr class="text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">
            <th class="px-5 py-3 whitespace-nowrap">Tanggal</th>
            <th class="px-5 py-3 whitespace-nowrap">Nama Hari Libur</th>
            <th class="px-5 py-3 whitespace-nowrap">Keterangan</th>
            <th class="px-5 py-3 text-right whitespace-nowrap">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm">
          @forelse ($holidays as $holiday)
            <tr class="hover:bg-slate-50">
              <td class="px-5 py-3 whitespace-nowrap">
                <p class="font-medium text-slate-900">{{ $holiday->date->translatedFormat('d M Y') }}</p>
                <p class="text-xs text-slate-500">{{ $holiday->date->translatedFormat('l') }}</p>
              </td>
              <td class="px-5 py-3 text-slate-900 whitespace-nowrap">{{ $holiday->holiday_name }}</td>
              <td class="px-5 py-3 text-slate-500">{{ $holiday->description ?: '-' }}</td>
              <td class="px-5 py-3">
                <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                  <button type="button" @click="$wire.set('showForm', true, true); $wire.open({{ $holiday->id }})"
                    class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-amber-50 text-amber-600 hover:bg-amber-100 transition">
                    Edit
                  </button>
                  <button wire:click="delete({{ $holiday->id }})" wire:confirm="Hapus hari libur ini?"
                    class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition">
                    Hapus
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-5 py-12 text-center text-slate-500">
                Belum ada hari libur yang terdaftar.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if ($holidays->hasPages())
      <div class="px-5 py-3 border-t border-slate-100">{{ $holidays->links() }}</div>
    @endif
  </div>

  @if ($holidays->isNotEmpty())
    <div class="card p-4 bg-blue-50 border border-blue-100 text-sm text-blue-800">
      <p class="font-medium mb-1">Catatan</p>
      <p>Karyawan tidak diwajibkan absen pada tanggal yang terdaftar di sini. Hari Minggu tetap menjadi hari libur otomatis tanpa perlu didaftarkan.</p>
    </div>
  @endif
</div>
