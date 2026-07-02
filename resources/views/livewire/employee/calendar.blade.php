<div>
  {{-- Header --}}
  <div class="px-5 pt-6 pb-4 bg-white border-b border-slate-200 flex items-center gap-3 sticky top-0 z-10">
    <a wire:navigate href="{{ route('mobile.home') }}" class="p-2 -ml-2 rounded-lg hover:bg-slate-100">
      <x-icon name="arrow-left" class="w-5 h-5" />
    </a>
    <div class="flex-1">
      <h1 class="text-lg font-bold text-slate-900">Kalender</h1>
      <p class="text-xs text-slate-400 mt-0.5">Hari libur & riwayat absensi</p>
    </div>
  </div>

  <div class="px-5 pt-4 pb-32 space-y-4">
    {{-- Month navigation --}}
    <div class="card p-3 flex items-center justify-between">
      <button type="button" wire:click="previousMonth" class="p-2 rounded-lg hover:bg-slate-100 active:bg-slate-200 transition">
        <x-icon name="chevron-left" class="w-5 h-5 text-slate-600" />
      </button>
      <p class="font-semibold text-slate-900 capitalize">{{ $monthLabel }}</p>
      <button type="button" wire:click="nextMonth" class="p-2 rounded-lg hover:bg-slate-100 active:bg-slate-200 transition">
        <x-icon name="chevron-right" class="w-5 h-5 text-slate-600" />
      </button>
    </div>

    {{-- Calendar grid --}}
    <div class="card p-3">
      <div class="grid grid-cols-7 text-center text-[11px] font-semibold text-slate-400 mb-1">
        <span>Min</span>
        <span>Sen</span>
        <span>Sel</span>
        <span>Rab</span>
        <span>Kam</span>
        <span>Jum</span>
        <span>Sab</span>
      </div>

      <div class="space-y-1">
        @foreach ($weeks as $week)
          <div class="grid grid-cols-7 gap-1">
            @foreach ($week as $day)
              @if (! $day)
                <div></div>
              @else
                @php
                  $dateKey = $day->toDateString();
                  $holiday = $holidays->get($dateKey);
                  $attendance = $attendances->get($dateKey);
                  $isSunday = $day->isSunday();
                  $isToday = $day->isToday();
                @endphp
                <div
                  class="aspect-square rounded-lg flex flex-col items-center justify-center gap-0.5 text-xs
                  {{ $holiday ? 'bg-red-50 text-red-600' : ($isSunday ? 'bg-slate-100 text-slate-400' : 'text-slate-700') }}
                  {{ $isToday ? 'ring-2 ring-brand-600' : '' }}"
                  title="{{ $holiday?->holiday_name }}">
                  <span class="font-medium tabular-nums">{{ $day->day }}</span>
                  @if ($attendance?->status)
                    <span class="w-1.5 h-1.5 rounded-full bg-{{ $attendance->status->color() }}-500"></span>
                  @endif
                </div>
              @endif
            @endforeach
          </div>
        @endforeach
      </div>
    </div>

    {{-- Legend --}}
    <div class="card p-4 space-y-2">
      <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Keterangan</p>
      <div class="grid grid-cols-2 gap-2 text-xs text-slate-600">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span> Hadir
        </div>
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-yellow-500"></span> Terlambat
        </div>
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> Absen
        </div>
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Cuti / Sakit
        </div>
        <div class="flex items-center gap-2">
          <span class="w-3 h-3 rounded bg-red-50 border border-red-100"></span> Hari Libur
        </div>
        <div class="flex items-center gap-2">
          <span class="w-3 h-3 rounded bg-slate-100"></span> Hari Minggu
        </div>
      </div>
    </div>

    {{-- Holidays this month --}}
    @if ($holidays->isNotEmpty())
      <div>
        <h3 class="text-sm font-semibold text-slate-900 mb-3">Hari Libur Bulan Ini</h3>
        <div class="card overflow-hidden divide-y divide-slate-100">
          @foreach ($holidays as $holiday)
            <div class="px-4 py-3 flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-red-50 text-red-600 flex flex-col items-center justify-center shrink-0 text-[10px] font-bold leading-none">
                <span class="text-sm">{{ $holiday->date->format('d') }}</span>
                <span class="uppercase">{{ $holiday->date->translatedFormat('M') }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-slate-900">{{ $holiday->holiday_name }}</p>
                <p class="text-xs text-slate-500">{{ $holiday->date->translatedFormat('l, d F Y') }}</p>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endif
  </div>
</div>
