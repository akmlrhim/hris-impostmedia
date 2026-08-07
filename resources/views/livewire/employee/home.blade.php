@php
  $hour = (int) now('Asia/Makassar')->format('H');
  $greeting = match (true) {
      $hour < 11 => 'Pagi,',
      $hour < 15 => 'Siang,',
      $hour < 19 => 'Sore,',
      default => 'Malam,',
  };
  $displayName = $employee?->nickname ?? ($employee?->full_name ?? auth()->user()->name);
  $checkedIn = (bool) $todayAttendance?->check_in_at;
  $checkedOut = (bool) $todayAttendance?->check_out_at;
@endphp

<div>
  @php
    $menus = [
        [
            'route' => 'mobile.attendance',
            'label' => 'Absensi',
            'icon' => 'check-circle',
            'fg' => 'text-emerald-500',
        ],
        [
            'route' => 'mobile.leave',
            'label' => 'Cuti & Izin',
            'icon' => 'calendar',
            'fg' => 'text-sky-500',
        ],
        [
            'route' => 'mobile.overtime',
            'label' => 'Lembur',
            'icon' => 'clock',
            'fg' => 'text-accent-500',
        ],
        $isWfo
            ? ['route' => 'mobile.remote-work', 'label' => 'Ajukan WFA', 'icon' => 'laptop', 'fg' => 'text-amber-500']
            : ['route' => 'mobile.payslip', 'label' => 'Slip Gaji', 'icon' => 'wallet', 'fg' => 'text-amber-500'],
    ];
  @endphp
  {{-- Amber slab sitting just behind the navy hero, so it only shows as two
       ears beside the hero's rounded bottom corners --}}
  <div class="sun-slab pb-6">
    {{-- Hero --}}
    <div class="hero px-5 pt-7 pb-12">
      <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
          <p class="text-sm text-navy-200">{{ $greeting }}</p>
          <h1 class="text-2xl font-bold leading-tight truncate">{{ $displayName }}</h1>
          <p class="text-xs text-navy-200 mt-1.5 truncate">{{ $employee?->position ?? 'Karyawan' }}</p>
        </div>
        <a wire:navigate href="{{ route('mobile.profile') }}"
          class="w-14 h-14 rounded-full bg-white/15 flex items-center justify-center overflow-hidden ring-2 ring-white/25 shrink-0">
          @if ($employee?->avatar_path)
            <img src="{{ route('files.avatar', $employee) }}" alt="{{ $employee->full_name }}"
              class="w-full h-full object-cover">
          @else
            <x-icon name="user" class="w-6 h-6 text-white" />
          @endif
        </a>
      </div>

      {{-- Absensi hari ini --}}
      <div class="card-float p-5 mt-6">
        <div class="flex items-center justify-between gap-3">
          <p class="font-bold text-navy-800">Absensi Hari Ini</p>
          <p class="text-xs font-semibold text-rose-500">{{ now()->translatedFormat('D, d M Y') }}</p>
        </div>

        <div class="mt-4 rounded-2xl bg-navy-50 py-4 grid grid-cols-2 divide-x divide-navy-100">
          @foreach ([['Masuk', $todayAttendance?->check_in_at, 'text-navy-800'], ['Keluar', $todayAttendance?->check_out_at, 'text-accent-600']] as [$label, $time, $fg])
            <div class="text-center">
              <p class="text-[11px] font-medium text-navy-400">{{ $label }}</p>
              <p class="text-3xl font-bold {{ $fg }} tracking-tight mt-0.5">
                {{ $time?->format('H:i') ?? '--:--' }}
              </p>
            </div>
          @endforeach
        </div>

        @if ($isOffDay)
          <div class="btn-accent mt-4 bg-slate-100 text-slate-500 shadow-none pointer-events-none">
            <x-icon name="coffee" class="w-4 h-4" />
            Hari Libur
          </div>
        @elseif ($checkedOut)
          <div class="btn-accent mt-4 bg-emerald-50 text-emerald-700 shadow-none pointer-events-none">
            <x-icon name="check-circle" class="w-4 h-4" />
            Absensi Selesai
          </div>
        @else
          <a wire:navigate href="{{ route('mobile.attendance') }}" class="btn-accent mt-4">
            {{ $checkedIn ? 'Check Out' : 'Check In' }}
          </a>
        @endif
      </div>
    </div>
  </div>

  {{-- Quick menu, straddling the hero's bottom edge --}}
  <div class="px-5 -mt-14">
    <div class="card-float p-4 grid grid-cols-4 gap-2">
      @foreach ($menus as $m)
        <a wire:navigate href="{{ Route::has($m['route']) ? route($m['route']) : '#' }}"
          class="flex flex-col items-center gap-2 py-1 active:scale-95 transition">
          <x-icon :name="$m['icon']" class="w-6 h-6 {{ $m['fg'] }}" />
          <span class="text-[11px] font-medium text-navy-700 text-center leading-tight">{{ $m['label'] }}</span>
        </a>
      @endforeach
    </div>
  </div>

  {{-- Late alert --}}
  @if ($isLateAlert)
    <div class="px-5 mt-5">
      <div class="flex items-center gap-3 p-3 rounded-2xl bg-red-50 border border-red-100">
        <div class="w-9 h-9 rounded-full bg-red-100 flex items-center justify-center shrink-0">
          <x-icon name="alert-circle" class="w-4 h-4 text-red-600" />
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-bold text-red-800">Belum absen!</p>
          <p class="text-xs text-red-600">Sudah lewat pukul {{ $workStartLabel }} WITA. Segera lakukan check-in.</p>
        </div>
        <a wire:navigate href="{{ route('mobile.attendance') }}"
          class="shrink-0 px-3 py-1.5 rounded-lg bg-red-600 text-white text-xs font-bold">
          Absen
        </a>
      </div>
    </div>
  @endif

  {{-- Monthly stats --}}
  @if ($monthStats !== null)
    <div class="px-5 mt-6">
      <h3 class="section-title mb-3">Statistik Bulan Ini</h3>
      <div class="grid grid-cols-3 gap-3">
        @foreach ([['Hadir', $monthStats['hadir'], 'text-emerald-500'], ['Terlambat', $monthStats['terlambat'], 'text-amber-500'], ['Tidak Hadir', $monthStats['tidak_hadir'], 'text-rose-500']] as [$label, $value, $fg])
          <div class="card-float p-3 text-center">
            <p class="text-2xl font-bold {{ $fg }}">{{ $value }}</p>
            <p class="text-[11px] text-navy-400 mt-0.5">{{ $label }}</p>
          </div>
        @endforeach
      </div>
    </div>
  @endif

  {{-- Attendance history --}}
  <div class="px-5 mt-6">
    <div class="flex items-center justify-between mb-3">
      <h3 class="section-title">Absensi</h3>
      <a wire:navigate href="{{ route('mobile.calendar') }}" class="link-accent">Lihat Semua</a>
    </div>

    @if ($recentAttendances->isEmpty())
      <div class="card-float p-6 text-center text-sm text-navy-400">
        Belum ada riwayat absensi.
      </div>
    @else
      <div class="space-y-3">
        @foreach ($recentAttendances as $a)
          <div class="card-float p-4" wire:key="att-{{ $a->id }}">
            <p class="font-bold text-navy-800 text-sm">{{ $a->attendance_date->translatedFormat('D, d M Y') }}</p>
            <div class="grid grid-cols-2 gap-3 mt-3">
              @foreach ([['Mulai Kerja', $a->check_in_at, 'text-navy-600'], ['Selesai Kerja', $a->check_out_at, 'text-accent-600']] as [$label, $time, $fg])
                <div class="flex items-center gap-2">
                  <x-icon name="map-pin" class="w-5 h-5 text-rose-500 shrink-0" />
                  <div class="min-w-0">
                    <p class="text-[11px] text-navy-400 leading-tight">{{ $label }}</p>
                    <p class="text-sm font-bold {{ $fg }}">{{ $time?->format('H:i') ?? '--:--' }}</p>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>

  {{-- Announcements --}}
  <div class="px-5 mt-6">
    <h3 class="section-title mb-3">Pengumuman</h3>
    @if ($announcements->isEmpty())
      <div class="card-float p-6 text-center text-sm text-navy-400">
        Belum ada pengumuman.
      </div>
    @else
      <div class="card-float overflow-hidden divide-y divide-slate-100">
        @foreach ($announcements as $a)
          <a wire:navigate href="{{ route('mobile.announcements.show', $a) }}"
            class="block p-4 active:bg-slate-50 transition">
            <div class="flex items-start gap-3">
              @if ($a->is_pinned)
                <span class="badge bg-sun-400/25 text-amber-700 shrink-0 mt-0.5">📌</span>
              @endif
              <div class="flex-1 min-w-0">
                <p class="font-bold text-navy-800 text-sm">{{ $a->title }}</p>
                <p class="text-xs text-navy-400 mt-0.5">{{ $a->published_at?->diffForHumans() }}</p>
                <div class="ql-snow mt-1.5">
                  <div class="ql-editor ql-readonly text-xs line-clamp-2">{!! $a->content !!}</div>
                </div>
              </div>
              <x-icon name="chevron-right" class="w-4 h-4 text-slate-300 shrink-0 mt-0.5" />
            </div>
          </a>
        @endforeach
      </div>
    @endif
  </div>
</div>
