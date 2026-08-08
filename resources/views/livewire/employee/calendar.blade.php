<div>
  {{-- Header --}}
  <x-mobile-header title="Kalender" subtitle="Hari libur & riwayat absensi" :back="route('mobile.home')" />

  {{-- The whole month is handed to Alpine, so switching days never hits the
       server; only the month arrows do. Re-keyed per month so a new month
       re-initialises the client state with fresh data. --}}
  <div class="relative z-10 px-4 -mt-10 pb-32 space-y-4" wire:key="cal-{{ $year }}-{{ $month }}"
    x-data="{ selected: @js($selectedDate), days: @js($dayDetails) }">
    {{-- Month navigation. The card surface stays opaque — it overlaps the navy
         hero, so fading the card itself would let the hero bleed through. Only
         its contents dim while the new month loads. --}}
    <div class="card-float p-3">
      <div class="flex items-center justify-between transition-opacity" wire:loading.class="opacity-50"
        wire:target="previousMonth,nextMonth">
        <button type="button" wire:click="previousMonth"
          class="p-2 rounded-lg text-navy-500 hover:bg-slate-100 active:bg-slate-200 transition">
          <x-icon name="chevron-left" class="w-5 h-5" />
        </button>
        <p class="font-bold text-navy-800 capitalize">{{ $monthLabel }}</p>
        <button type="button" wire:click="nextMonth"
          class="p-2 rounded-lg text-navy-500 hover:bg-slate-100 active:bg-slate-200 transition">
          <x-icon name="chevron-right" class="w-5 h-5" />
        </button>
      </div>
    </div>

    {{-- Calendar grid --}}
    <div class="card-float p-3">
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
                  $restingClasses = trim(
                      ($holiday
                          ? 'bg-red-50 text-red-600'
                          : ($day->isSunday() ? 'bg-slate-100 text-slate-400' : 'text-slate-700 active:bg-slate-100')) .
                          ($day->isToday() ? ' ring-2 ring-accent-500' : ''),
                  );
                @endphp
                <button type="button" wire:key="day-{{ $dateKey }}"
                  @click="selected = '{{ $dateKey }}'; $wire.$set('selectedDate', selected, false)"
                  :class="selected === '{{ $dateKey }}' ? 'bg-accent-500 text-white font-bold' : '{{ $restingClasses }}'"
                  class="aspect-square w-full rounded-lg flex flex-col items-center justify-center gap-0.5 text-xs transition"
                  title="{{ $holiday?->holiday_name }}">
                  <span class="font-medium">{{ $day->day }}</span>
                  @if ($attendance?->status)
                    <span class="w-1.5 h-1.5 rounded-full"
                      :class="selected === '{{ $dateKey }}' ? 'bg-white' : 'bg-{{ $attendance->status->color() }}-500'"></span>
                  @endif
                </button>
              @endif
            @endforeach
          </div>
        @endforeach
      </div>
    </div>

    {{-- Detail of the selected day, rendered from the pre-loaded month data --}}
    <template x-if="days[selected]">
      <div class="card-float p-4">
        <div class="flex items-center justify-between gap-3">
          <div class="min-w-0">
            <p class="font-bold text-navy-800" x-text="days[selected].title"></p>
            <p class="text-xs mt-0.5" x-show="days[selected].note" :class="days[selected].noteClass"
              x-text="days[selected].note"></p>
          </div>
          <span class="badge shrink-0" x-show="days[selected].status" :class="days[selected].statusClass"
            x-text="days[selected].status"></span>
        </div>

        <template x-if="days[selected].hasCheckIn">
          <div>
            <div class="grid grid-cols-2 gap-3 mt-3">
              <div class="flex items-center gap-2">
                <x-icon name="map-pin" class="w-5 h-5 text-rose-500 shrink-0" />
                <div class="min-w-0">
                  <p class="text-[11px] text-navy-400 leading-tight">Mulai Kerja</p>
                  <p class="text-sm font-bold text-navy-600" x-text="days[selected].checkIn"></p>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <x-icon name="map-pin" class="w-5 h-5 text-rose-500 shrink-0" />
                <div class="min-w-0">
                  <p class="text-[11px] text-navy-400 leading-tight">Selesai Kerja</p>
                  <p class="text-sm font-bold text-accent-600" x-text="days[selected].checkOut"></p>
                </div>
              </div>
            </div>

            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-3 pt-3 border-t border-slate-100 text-[11px] text-navy-400"
              x-show="days[selected].meta.length">
              <template x-for="item in days[selected].meta" :key="item">
                <span x-text="item"></span>
              </template>
            </div>
          </div>
        </template>

        <p class="text-sm text-navy-400 mt-3" x-show="! days[selected].hasCheckIn" x-text="days[selected].empty"></p>
      </div>
    </template>

    {{-- Legend --}}
    <div class="card-float p-4 space-y-2">
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
        <h3 class="section-title mb-3">Hari Libur Bulan Ini</h3>
        <div class="card-float overflow-hidden divide-y divide-slate-100">
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
