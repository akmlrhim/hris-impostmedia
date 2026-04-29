<div class="space-y-6">
  {{-- Stats grid --}}
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    @php
      $cards = [
          [
              'label' => 'Total Karyawan',
              'value' => $stats['total_employees'],
              'color' => 'bg-blue-500',
              'icon' => 'users',
          ],
          [
              'label' => 'Hadir Hari Ini',
              'value' => $stats['present_today'],
              'color' => 'bg-emerald-500',
              'icon' => 'check',
          ],
          ['label' => 'Absen', 'value' => $stats['absent_today'], 'color' => 'bg-red-500', 'icon' => 'x'],
          [
              'label' => 'Cuti Hari Ini',
              'value' => $stats['on_leave_today'],
              'color' => 'bg-amber-500',
              'icon' => 'calendar',
          ],
          [
              'label' => 'Cuti Pending',
              'value' => $stats['pending_leaves'],
              'color' => 'bg-violet-500',
              'icon' => 'calendar',
          ],
          [
              'label' => 'Reimburse Pending',
              'value' => $stats['pending_reimbursements'],
              'color' => 'bg-sky-500',
              'icon' => 'receipt',
          ],
      ];
    @endphp

    @foreach ($cards as $c)
      <div class="card p-4">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ $c['label'] }}</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $c['value'] }}</p>
          </div>
          <div class="w-9 h-9 rounded-lg {{ $c['color'] }} flex items-center justify-center text-white">
            <x-icon :name="$c['icon']" class="w-5 h-5" />
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Pending leaves --}}
    <div class="card overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
        <h2 class="font-semibold text-slate-900">Pengajuan Cuti Terbaru</h2>
        <a href="{{ route('admin.leave') }}" class="text-sm text-brand-600 hover:underline">Lihat semua</a>
      </div>
      <div class="divide-y divide-slate-100">
        @forelse ($recentLeaves as $leave)
          <div class="px-5 py-3 flex items-center justify-between">
            <div>
              <p class="font-medium text-slate-900">{{ $leave->employee->full_name }}</p>
              <p class="text-xs text-slate-500">
                {{ $leave->leaveType->name }} · {{ $leave->start_date->format('d M') }} –
                {{ $leave->end_date->format('d M Y') }}
              </p>
            </div>
            <span class="badge bg-amber-100 text-amber-700">Pending</span>
          </div>
        @empty
          <p class="px-5 py-8 text-center text-sm text-slate-500">Tidak ada pengajuan baru.</p>
        @endforelse
      </div>
    </div>

    {{-- Today's check-ins --}}
    <div class="card overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
        <h2 class="font-semibold text-slate-900">Check-in Hari Ini</h2>
        <a href="{{ route('admin.attendance') }}" class="text-sm text-brand-600 hover:underline">Lihat semua</a>
      </div>
      <div class="divide-y divide-slate-100">
        @forelse ($recentAttendance as $att)
          <div class="px-5 py-3 flex items-center justify-between">
            <div>
              <p class="font-medium text-slate-900">{{ $att->employee->full_name }}</p>
              <p class="text-xs text-slate-500">
                Check-in: {{ $att->check_in_at?->format('H:i') ?? '—' }}
                @if ($att->late_minutes > 0)
                  · <span class="text-red-600">Terlambat {{ $att->late_minutes }} mnt</span>
                @endif
              </p>
            </div>
            <span class="badge bg-{{ $att->status?->color() }}-100 text-{{ $att->status?->color() }}-700">
              {{ $att->status?->label() }}
            </span>
          </div>
        @empty
          <p class="px-5 py-8 text-center text-sm text-slate-500">Belum ada check-in hari ini.</p>
        @endforelse
      </div>
    </div>
  </div>
</div>
