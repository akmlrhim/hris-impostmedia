<div class="space-y-6">

  {{-- Personal attendance card (hanya tampil jika admin punya data karyawan) --}}
  @if ($myEmployee)
    <div class="card p-5">
      <div class="flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
            {{ $myAttendance?->check_out_at ? 'bg-emerald-100 text-emerald-600' : ($myAttendance?->check_in_at ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-500') }}">
            <x-icon name="scan-face" class="w-5 h-5" />
          </div>
          <div>
            <p class="text-xs text-slate-500">Absensi Saya - {{ now()->translatedFormat('l, d F Y') }}</p>
            @if ($myAttendance?->check_out_at)
              <p class="font-semibold text-emerald-700">
                Selesai · {{ $myAttendance->check_in_at->format('H:i') }} – {{ $myAttendance->check_out_at->format('H:i') }}
                <span class="font-normal text-slate-500 text-xs ml-1">({{ floor($myAttendance->work_minutes / 60) }}j {{ $myAttendance->work_minutes % 60 }}m)</span>
              </p>
            @elseif ($myAttendance?->check_in_at)
              <p class="font-semibold text-amber-700">
                Check-in {{ $myAttendance->check_in_at->format('H:i') }}
                @if ($myAttendance->late_minutes > 0)
                  · <span class="text-red-600 font-normal text-sm">Terlambat {{ $myAttendance->late_minutes }} mnt</span>
                @endif
              </p>
            @else
              <p class="font-semibold text-slate-700">Belum absen hari ini</p>
            @endif
          </div>
        </div>

        <a wire:navigate href="{{ route('mobile.attendance') }}" class="btn-primary shrink-0 text-sm">
          @if (! $myAttendance?->check_in_at) Absen Sekarang
          @elseif (! $myAttendance?->check_out_at) Check-out
          @else Lihat Detail
          @endif
        </a>
      </div>
    </div>
  @endif

  {{-- Stats grid --}}
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <x-stat-card label="Total Karyawan" :value="$stats['total_employees']" icon="users" color="bg-blue-500" />
    <x-stat-card label="Hadir Hari Ini" :value="$stats['present_today']" icon="check" color="bg-emerald-500" />
    <x-stat-card label="Absen" :value="$stats['absent_today']" icon="x" color="bg-red-500" />
  </div>

  <div class="grid grid-cols-1 gap-6">
    {{-- Today's check-ins --}}
    <div class="card overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
        <h2 class="font-semibold text-black">Check-in Hari Ini</h2>
        <a wire:navigate href="{{ route('admin.attendance') }}" class="text-sm text-brand-600 hover:underline">Lihat
          semua</a>
      </div>
      <div class="divide-y divide-slate-100">
        @forelse ($recentAttendance as $att)
          <div class="px-5 py-3 flex items-center justify-between">
            <div>
              <p class="font-medium text-black">{{ $att->employee->full_name }}</p>
              <p class="text-xs text-slate-500">
                Check-in: {{ $att->check_in_at?->format('H:i') ?? '-' }}
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
