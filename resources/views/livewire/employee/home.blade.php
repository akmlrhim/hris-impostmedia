<div>
  {{-- Header --}}
  <div class="bg-gradient-to-br from-brand-600 to-brand-800 text-white px-5 pt-8 pb-20 rounded-b-3xl">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm text-brand-100">Halo,</p>
        <h1 class="text-xl font-bold">{{ $employee?->nickname ?? ($employee?->full_name ?? auth()->user()->name) }} 👋
        </h1>
      </div>
      <a wire:navigate href="{{ route('mobile.profile') }}"
        class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center overflow-hidden ring-2 ring-white/30 shrink-0">
        @if ($employee?->avatar_path)
          <img src="{{ route('files.avatar', $employee) }}" alt="{{ $employee->full_name }}"
            class="w-full h-full object-cover">
        @else
          <x-icon name="user" class="w-5 h-5 text-white" />
        @endif
      </a>
    </div>
  </div>

  {{-- Today attendance card (overlapping) --}}
  <div class="px-4 -mt-14">
    <div class="card p-5">
      <div class="flex items-center justify-between mb-3">
        <div>
          <p class="text-xs text-slate-500 uppercase">Absensi Hari Ini</p>
          <p class="text-lg font-bold text-slate-900">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
        <a wire:navigate href="{{ route('mobile.attendance') }}" class="text-sm text-brand-600 font-medium">Lihat</a>
      </div>

      <div class="grid grid-cols-2 gap-3 text-center">
        <div class="p-3 rounded-xl bg-emerald-50">
          <p class="text-xs text-emerald-700 font-medium">Check-in</p>
          <p class="text-xl font-bold text-emerald-700 mt-1">
            {{ $todayAttendance?->check_in_at?->format('H:i') ?? '--:--' }}
          </p>
        </div>
        <div class="p-3 rounded-xl bg-rose-50">
          <p class="text-xs text-rose-700 font-medium">Check-out</p>
          <p class="text-xl font-bold text-rose-700 mt-1">
            {{ $todayAttendance?->check_out_at?->format('H:i') ?? '--:--' }}
          </p>
        </div>
      </div>

      <a wire:navigate href="{{ route('mobile.attendance') }}" class="btn-primary w-full mt-4">
        {{ $todayAttendance?->check_in_at && !$todayAttendance->check_out_at ? 'Check-out Sekarang' : 'Check-in Sekarang' }}
      </a>
    </div>
  </div>

  {{-- Quick menu --}}
  <div class="px-4 mt-6">
    <h3 class="text-sm font-semibold text-slate-900 mb-3">Menu Cepat</h3>
    <div class="grid grid-cols-4 gap-3">
      @php
        $menus = [
            [
                'route' => 'mobile.directory',
                'label' => 'Direktori',
                'icon' => 'users',
                'bg' => 'bg-sky-100',
                'fg' => 'text-sky-600',
            ],
            [
                'route' => 'mobile.payslip',
                'label' => 'Slip Gaji',
                'icon' => 'wallet',
                'bg' => 'bg-emerald-100',
                'fg' => 'text-emerald-600',
            ],
            [
                'route' => 'mobile.leave',
                'label' => 'Cuti & Izin',
                'icon' => 'calendar',
                'bg' => 'bg-blue-100',
                'fg' => 'text-blue-600',
            ],
        ];
        if ($isWfo) {
            $menus[] = [
                'route' => 'mobile.remote-work',
                'label' => 'Ajukan WFA',
                'icon' => 'laptop',
                'bg' => 'bg-purple-100',
                'fg' => 'text-purple-600',
            ];
        }
      @endphp
      @foreach ($menus as $m)
        <a wire:navigate href="{{ Route::has($m['route']) ? route($m['route']) : '#' }}"
          class="flex flex-col items-center gap-2">
          <div class="w-12 h-12 rounded-2xl {{ $m['bg'] }} flex items-center justify-center {{ $m['fg'] }}">
            <x-icon :name="$m['icon']" class="w-5 h-5" />
          </div>
          <span class="text-xs text-slate-700">{{ $m['label'] }}</span>
        </a>
      @endforeach
    </div>
  </div>

  {{-- Announcements --}}
  <div class="px-4 mt-6">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-sm font-semibold text-slate-900">Pengumuman</h3>
    </div>
    @if ($announcements->isEmpty())
      <div class="card p-6 text-center text-sm text-slate-500">
        Belum ada pengumuman.
      </div>
    @else
      <div class="card overflow-hidden divide-y divide-slate-100">
        @foreach ($announcements as $a)
          <a wire:navigate href="{{ route('mobile.announcements.show', $a) }}"
            class="block p-4 active:bg-slate-50 transition">
            <div class="flex items-start gap-3">
              @if ($a->is_pinned)
                <span class="badge bg-amber-100 text-amber-700 shrink-0 mt-0.5">📌</span>
              @endif
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-slate-900 text-sm">{{ $a->title }}</p>
                <p class="text-xs text-slate-500 mt-0.5">{{ $a->published_at?->diffForHumans() }}</p>
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
