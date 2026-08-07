<div class="space-y-4">
  {{-- Tabs --}}
  <div class="border-b border-slate-200">
    <nav class="flex gap-1 -mb-px" aria-label="Tab data absensi">
      @php
        $tabs = [
          \App\Livewire\Admin\Attendance::TAB_DAILY => 'Absensi Harian',
          \App\Livewire\Admin\Attendance::TAB_RECAP => 'Rekap Bulanan',
        ];
      @endphp
      @foreach ($tabs as $value => $label)
        <button type="button" wire:click="setTab('{{ $value }}')"
          @class([
              'px-4 py-2.5 text-sm font-medium border-b-2 transition whitespace-nowrap',
              'border-brand-600 text-brand-600' => $tab === $value,
              'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' => $tab !== $value,
          ])
          @if ($tab === $value) aria-current="page" @endif>
          {{ $label }}
        </button>
      @endforeach
    </nav>
  </div>

  @if ($tab === \App\Livewire\Admin\Attendance::TAB_DAILY)
    <div class="card p-4 flex flex-col sm:flex-row gap-3">
      <input wire:model.live="date" type="date" class="input md:w-52">

      <select wire:model.live="status" class="input md:w-52">
        <option value="">Semua Status</option>
        @foreach (\App\Enums\AttendanceStatus::cases() as $s)
          <option value="{{ $s->value }}">{{ $s->label() }}</option>
        @endforeach
        <option value="{{ \App\Livewire\Admin\Attendance::STATUS_MISSING }}">Belum Absen</option>
      </select>
    </div>

    <div class="card-table">
      <div class="overflow-x-auto">
        <table class="table-grid">
          <thead class="bg-slate-50">
            <tr class="text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">
              <th class="px-5 py-3 whitespace-nowrap">Karyawan</th>
              <th class="px-5 py-3 whitespace-nowrap text-center">Check-in</th>
              <th class="px-5 py-3 whitespace-nowrap text-center">Check-out</th>
              <th class="px-5 py-3 whitespace-nowrap">Terlambat</th>
              <th class="px-5 py-3 whitespace-nowrap">Status</th>
            </tr>
          </thead>
          <tbody class="text-sm">
            @forelse ($employees as $employee)
              @php $att = $employee->attendances->first(); @endphp
              <tr class="hover:bg-slate-50" wire:key="att-{{ $employee->id }}">
                <td class="px-5 py-3">
                  <div class="flex items-center gap-2.5">
                    <div
                      class="w-8 h-8 rounded-full bg-slate-200 overflow-hidden flex items-center justify-center font-semibold text-slate-600 shrink-0">
                      @if ($employee->avatar_path)
                        <img src="{{ route('files.avatar', $employee) }}" class="w-full h-full object-cover">
                      @else
                        {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                      @endif
                    </div>
                    <div class="whitespace-nowrap">
                      <p class="font-medium text-slate-900">{{ $employee->full_name }}</p>
                      <p class="text-xs text-slate-500">{{ $employee->employee_number }}</p>
                    </div>
                  </div>
                </td>
                {{-- A recorded time turns its cell green so a filled shift reads at a glance --}}
                <td
                  class="px-5 py-3 whitespace-nowrap text-center {{ $att?->check_in_at ? 'bg-emerald-50 font-semibold text-emerald-700' : 'text-slate-400' }}">
                  {{ $att?->check_in_at?->format('H:i') ?? '-' }}
                </td>
                <td
                  class="px-5 py-3 whitespace-nowrap text-center {{ $att?->check_out_at ? 'bg-emerald-50 font-semibold text-emerald-700' : 'text-slate-400' }}">
                  {{ $att?->check_out_at?->format('H:i') ?? '-' }}
                </td>

                <td class="px-5 py-3 whitespace-nowrap">
                  {{ $att && $att->late_minutes > 0 ? $att->late_minutes . ' mnt' : '-' }}
                </td>
                <td class="px-5 py-3 whitespace-nowrap">
                  @if ($att?->status)
                    <span class="badge bg-{{ $att->status->color() }}-100 text-{{ $att->status->color() }}-700">
                      {{ $att->status->label() }}
                    </span>
                  @else
                    <span class="badge bg-slate-100 text-slate-600">Belum Absen</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="px-5 py-12 text-center text-slate-500">
                  Tidak ada karyawan yang cocok dengan filter ini.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="px-5 py-3 border-t border-slate-200">
        {{ $employees->links() }}
      </div>
    </div>
  @else
    <div class="card p-4 space-y-3">
      <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <input wire:model.live="month" type="month" class="input md:w-52">
        <p class="text-sm text-slate-500">
          Rekap kehadiran <span class="font-medium text-slate-700">{{ $monthLabel }}</span> —
          <span class="font-medium text-slate-700">{{ $workingDays }}</span> hari kerja sudah berjalan
        </p>
      </div>
      <p class="text-xs text-slate-500">
        "Tanpa Keterangan" dihitung dari hari kerja yang sudah lewat tanpa absensi dan tanpa cuti/izin
        yang disetujui. Hari libur mingguan diatur di
        <a wire:navigate href="{{ route('admin.working-days') }}" class="text-brand-600 hover:underline">Hari Kerja</a>.
      </p>
    </div>

    <div class="card-table">
      <div class="overflow-x-auto">
        <table class="table-grid">
          <thead class="bg-slate-50">
            <tr class="text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">
              <th class="px-5 py-3 whitespace-nowrap">Karyawan</th>
              <th class="px-3 py-3 whitespace-nowrap text-center">Total Hadir</th>
              <th class="px-3 py-3 whitespace-nowrap text-center">Tepat Waktu</th>
              <th class="px-3 py-3 whitespace-nowrap text-center">Terlambat</th>
              <th class="px-3 py-3 whitespace-nowrap text-center">Pulang Cepat</th>
              <th class="px-3 py-3 whitespace-nowrap text-center">Cuti</th>
              <th class="px-3 py-3 whitespace-nowrap text-center">Izin</th>
              <th class="px-3 py-3 whitespace-nowrap text-center">Sakit</th>
              <th class="px-3 py-3 whitespace-nowrap text-center">Tanpa Keterangan</th>
              <th class="px-3 py-3 whitespace-nowrap text-right">Total Terlambat</th>
              <th class="px-5 py-3 whitespace-nowrap text-right">Jam Kerja</th>
            </tr>
          </thead>
          <tbody class="text-sm">
            @forelse ($employees as $employee)
              @php $row = $recap[$employee->id]; @endphp
              <tr class="hover:bg-slate-50" wire:key="recap-{{ $employee->id }}">
                <td class="px-5 py-3">
                  <div class="flex items-center gap-2.5">
                    <div
                      class="w-8 h-8 rounded-full bg-slate-200 overflow-hidden flex items-center justify-center font-semibold text-slate-600 shrink-0">
                      @if ($employee->avatar_path)
                        <img src="{{ route('files.avatar', $employee) }}" class="w-full h-full object-cover">
                      @else
                        {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                      @endif
                    </div>
                    <div class="whitespace-nowrap">
                      <p class="font-medium text-slate-900">{{ $employee->full_name }}</p>
                      <p class="text-xs text-slate-500">{{ $employee->employee_number }}</p>
                    </div>
                  </div>
                </td>
                <td
                  class="px-3 py-3 text-center font-semibold {{ $row['present_total'] > 0 ? 'text-emerald-700 bg-emerald-50' : 'text-slate-400' }}">
                  {{ $row['present_total'] }}
                </td>
                <td class="px-3 py-3 text-center {{ $row['present'] > 0 ? 'text-slate-700' : 'text-slate-300' }}">
                  {{ $row['present'] }}
                </td>
                <td class="px-3 py-3 text-center {{ $row['late'] > 0 ? 'font-semibold text-amber-600' : 'text-slate-300' }}">
                  {{ $row['late'] }}
                </td>
                <td class="px-3 py-3 text-center {{ $row['early_leave'] > 0 ? 'font-semibold text-amber-600' : 'text-slate-300' }}">
                  {{ $row['early_leave'] }}
                </td>
                <td class="px-3 py-3 text-center {{ $row['leave'] > 0 ? 'text-blue-600' : 'text-slate-300' }}">
                  {{ $row['leave'] }}
                </td>
                <td class="px-3 py-3 text-center {{ $row['permission'] > 0 ? 'text-blue-600' : 'text-slate-300' }}">
                  {{ $row['permission'] }}
                </td>
                <td class="px-3 py-3 text-center {{ $row['sick'] > 0 ? 'text-blue-600' : 'text-slate-300' }}">
                  {{ $row['sick'] }}
                </td>
                <td
                  class="px-3 py-3 text-center {{ $row['absent'] > 0 ? 'font-semibold text-red-600 bg-red-50' : 'text-slate-300' }}">
                  {{ $row['absent'] }}
                </td>
                <td class="px-3 py-3 text-right whitespace-nowrap {{ $row['late_minutes'] > 0 ? 'text-amber-600' : 'text-slate-300' }}">
                  {{ $row['late_minutes'] > 0 ? $row['late_minutes'] . ' mnt' : '-' }}
                </td>
                <td class="px-5 py-3 text-right whitespace-nowrap {{ $row['work_minutes'] > 0 ? 'text-slate-700' : 'text-slate-300' }}">
                  {{ $row['work_minutes'] > 0 ? number_format($row['work_minutes'] / 60, 1, ',', '.') . ' jam' : '-' }}
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="11" class="px-5 py-12 text-center text-slate-500">
                  Belum ada karyawan aktif.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="px-5 py-3 border-t border-slate-200">
        {{ $employees->links() }}
      </div>
    </div>
  @endif
</div>
