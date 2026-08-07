<div class="space-y-4">
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
</div>
