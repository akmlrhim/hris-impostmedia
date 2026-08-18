<div class="space-y-4">
  <x-page-header title="Hari Kerja"
    description="Tentukan hari operasional perusahaan. Dipakai untuk rekap absensi, deteksi tidak hadir, dan pengingat check-in." />

  <div class="card p-5 max-w-2xl space-y-5">
    <div>
      <h3 class="text-sm font-semibold text-slate-900">Jadwal Mingguan</h3>
      <p class="text-sm text-slate-500 mt-1">
        Hari yang dimatikan dianggap libur mingguan, karyawan tidak wajib absen dan tidak dihitung
        tidak hadir. Hari libur nasional diatur terpisah di
        <a wire:navigate href="{{ route('admin.holidays') }}" class="text-brand-600 hover:underline">Hari Libur</a>.
      </p>
    </div>

    <div class="divide-y divide-slate-100 border-y border-slate-100">
      @foreach ($weekdays as $weekday)
        <label class="flex items-center justify-between gap-4 py-3 cursor-pointer group"
          wire:key="weekday-{{ $weekday }}">
          <span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">
            {{ $this->weekdayName($weekday) }}
          </span>
          <span class="flex items-center gap-3">
            <span @class([
                'text-xs font-medium w-16 text-right',
                'text-emerald-600' => $schedule[$weekday] ?? false,
                'text-slate-400' => !($schedule[$weekday] ?? false),
            ])>
              {{ ($schedule[$weekday] ?? false) ? 'Kerja' : 'Libur' }}
            </span>
            {{-- Peer checkbox drives the track and knob colours without any JS --}}
            <span class="relative inline-flex items-center">
              <input type="checkbox" wire:model.live="schedule.{{ $weekday }}" class="sr-only peer">
              <span
                class="w-10 h-6 rounded-full bg-slate-200 peer-checked:bg-emerald-500 transition-colors"></span>
              <span
                class="absolute left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4"></span>
            </span>
          </span>
        </label>
      @endforeach
    </div>

    <div class="rounded-lg bg-slate-50 border border-slate-200 px-4 py-3 text-sm">
      <span class="text-slate-500">Hari kerja saat ini:</span>
      <span class="font-medium text-slate-800">{{ $this->summary() }}</span>
    </div>

    <div class="flex justify-end">
      <button type="button" wire:click="save" class="btn-primary" wire:loading.attr="disabled" wire:target="save">
        <span wire:loading.remove wire:target="save">Simpan Perubahan</span>
        <span wire:loading wire:target="save">Menyimpan…</span>
      </button>
    </div>
  </div>
</div>
