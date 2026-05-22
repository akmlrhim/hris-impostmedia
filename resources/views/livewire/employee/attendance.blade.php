<x-face-api />

<div
  x-data="attendanceCamera({
    workType: '{{ $workType->value }}',
    officeLocations: @json($officeLocations->values()),
    faceDescriptor: @json($faceDescriptor),
    hasFaceEnrolled: {{ $faceDescriptor ? 'true' : 'false' }},
  })"
  x-init="init()"
  @attendance-recorded.window="onAttendanceRecorded()"
  wire:ignore.self>

  {{-- Header --}}
  <div class="px-5 pt-6 pb-4 bg-white border-b border-slate-200 flex items-center gap-3 sticky top-0 z-10">
    <a wire:navigate href="{{ $isAdminPanelUser ? route('admin.dashboard') : route('mobile.home') }}"
      class="p-2 -ml-2 rounded-lg hover:bg-slate-100">
      <x-icon name="arrow-left" class="w-5 h-5" />
    </a>
    <div class="flex-1">
      <h1 class="text-lg font-bold text-slate-900">Absensi</h1>
      <p class="text-xs text-slate-400 mt-0.5">{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>
    @if ($employee)
      <span class="badge text-xs px-2 py-0.5
        {{ $workType->value === 'wfo' ? 'bg-blue-100 text-blue-700' : ($workType->value === 'wfh' ? 'bg-emerald-100 text-emerald-700' : 'bg-purple-100 text-purple-700') }}">
        {{ $workType->label() }}
      </span>
    @endif
  </div>

  <div class="px-5 pt-4 space-y-4 pb-32">

    {{-- ===== STATE 1: Tidak ada data karyawan ===== --}}
    @if (! $employee)
      <div class="card p-6 text-center space-y-4 mt-4">
        <div class="w-16 h-16 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center mx-auto">
          <x-icon name="alert-triangle" class="w-8 h-8" />
        </div>
        <div>
          <p class="font-semibold text-slate-900">Akun belum terhubung ke data karyawan</p>
          <p class="text-sm text-slate-500 mt-1">Hubungi Admin atau HR untuk menghubungkan akun Anda ke profil karyawan.</p>
        </div>
        @if ($isAdminPanelUser)
          <a wire:navigate href="{{ route('admin.employees') }}" class="btn-primary text-sm">
            Kelola Karyawan
          </a>
        @endif
      </div>

    @elseif (! $faceDescriptor)
      <div class="card p-4 flex items-center gap-3">
        <div class="w-12 h-12 rounded-full bg-slate-100 overflow-hidden flex items-center justify-center text-lg font-bold text-slate-500 shrink-0">
          @if ($employee->avatar_path)
            <img src="{{ route('files.avatar', $employee) }}" class="w-full h-full object-cover">
          @else
            {{ strtoupper(substr($employee->full_name, 0, 1)) }}
          @endif
        </div>
        <div>
          <p class="font-semibold text-slate-900">{{ $employee->nickname ?? $employee->full_name }}</p>
          <p class="text-xs text-slate-500">{{ $employee->employee_number }}</p>
        </div>
      </div>

      <div class="card p-6 text-center space-y-4">
        <div class="w-16 h-16 rounded-full bg-red-50 text-red-500 flex items-center justify-center mx-auto">
          <x-icon name="scan-face" class="w-8 h-8" />
        </div>
        <div>
          <p class="font-semibold text-slate-900">Data wajah belum terdaftar</p>
          <p class="text-sm text-slate-500 mt-1">Anda perlu mendaftarkan wajah terlebih dahulu sebelum dapat melakukan absensi.</p>
        </div>
        <a wire:navigate href="{{ route('mobile.profile.edit') }}" class="btn-primary w-full">
          Daftarkan Wajah Sekarang
        </a>
      </div>

    @elseif ($attendance?->check_out_at)
      <div class="card p-4 flex items-center gap-3">
        <div class="w-12 h-12 rounded-full bg-slate-100 overflow-hidden flex items-center justify-center text-lg font-bold text-slate-500 shrink-0">
          @if ($employee->avatar_path)
            <img src="{{ route('files.avatar', $employee) }}" class="w-full h-full object-cover">
          @else
            {{ strtoupper(substr($employee->full_name, 0, 1)) }}
          @endif
        </div>
        <div>
          <p class="font-semibold text-slate-900">{{ $employee->nickname ?? $employee->full_name }}</p>
          <p class="text-xs text-slate-500">{{ $employee->employee_number }}</p>
        </div>
      </div>

      <div class="card p-6 text-center space-y-4 bg-emerald-50 border-emerald-100">
        <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto">
          <x-icon name="check-circle" class="w-9 h-9" />
        </div>
        <div>
          <p class="font-bold text-emerald-800 text-lg">Absensi Selesai</p>
          <p class="text-sm text-emerald-700 mt-1">Selamat beristirahat, {{ $employee->nickname ?? explode(' ', $employee->full_name)[0] }}!</p>
        </div>
        <div class="grid grid-cols-2 gap-3 text-center">
          <div class="bg-white rounded-xl p-3 border border-emerald-100">
            <p class="text-[11px] text-slate-500 mb-0.5">Masuk</p>
            <p class="text-xl font-bold text-emerald-700">{{ $attendance->check_in_at->format('H:i') }}</p>
          </div>
          <div class="bg-white rounded-xl p-3 border border-emerald-100">
            <p class="text-[11px] text-slate-500 mb-0.5">Keluar</p>
            <p class="text-xl font-bold text-emerald-700">{{ $attendance->check_out_at->format('H:i') }}</p>
          </div>
        </div>
        <p class="text-xs text-emerald-700">
          Durasi kerja: <strong>{{ floor($attendance->work_minutes / 60) }}j {{ $attendance->work_minutes % 60 }}m</strong>
        </p>
      </div>

    {{-- ===== STATE 4: Proses absensi (check-in / check-out) ===== --}}
    @else

      {{-- Info karyawan + jam --}}
      <div class="card p-4 flex items-center gap-3">
        <div class="w-12 h-12 rounded-full bg-slate-100 overflow-hidden flex items-center justify-center text-lg font-bold text-slate-500 shrink-0">
          @if ($employee->avatar_path)
            <img src="{{ route('files.avatar', $employee) }}" class="w-full h-full object-cover">
          @else
            {{ strtoupper(substr($employee->full_name, 0, 1)) }}
          @endif
        </div>
        <div class="flex-1">
          <p class="font-semibold text-slate-900">{{ $employee->nickname ?? $employee->full_name }}</p>
          <p class="text-xs text-slate-500">{{ $employee->employee_number }}</p>
        </div>
        <div class="text-right">
          <p x-data="{ t: '' }" x-init="setInterval(() => t = new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit', second:'2-digit'}), 1000)"
            x-text="t || new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit', second:'2-digit'})"
            class="font-mono text-xl font-bold text-slate-900 tabular-nums">
          </p>
        </div>
      </div>

      {{-- Step indicator --}}
      <div class="flex items-center gap-2">
        <div class="flex items-center gap-1.5 flex-1">
          <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
            {{ $attendance?->check_in_at ? 'bg-emerald-500 text-white' : 'bg-brand-600 text-white' }}">
            @if ($attendance?->check_in_at) ✓ @else 1 @endif
          </div>
          <span class="text-xs font-medium {{ $attendance?->check_in_at ? 'text-emerald-600' : 'text-slate-900' }}">
            Check-in {{ $attendance?->check_in_at ? '('.$attendance->check_in_at->format('H:i').')' : '' }}
          </span>
        </div>
        <div class="flex-1 h-px bg-slate-200"></div>
        <div class="flex items-center gap-1.5 flex-1 justify-end">
          <span class="text-xs font-medium {{ $attendance?->check_in_at ? 'text-slate-900' : 'text-slate-400' }}">
            Check-out
          </span>
          <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
            {{ $attendance?->check_in_at ? 'bg-rose-600 text-white' : 'bg-slate-200 text-slate-400' }}">
            2
          </div>
        </div>
      </div>

      {{-- Jika WFA override oleh remote request --}}
      @if ($remoteRequest && $baseWorkType->requiresGeofencing())
        <div class="p-3 rounded-xl bg-purple-50 border border-purple-100 text-purple-800 text-xs flex items-start gap-2">
          <x-icon name="check-circle" class="w-4 h-4 shrink-0 mt-0.5 text-purple-500" />
          <span>Pengajuan <strong>{{ $remoteRequest->work_type->label() }}</strong> disetujui - GPS tidak wajib hari ini.</span>
        </div>
      @endif

      {{-- Kamera --}}
      <div class="card overflow-hidden">
        <div class="relative bg-slate-900 aspect-video flex items-center justify-center">
          <video x-ref="video" autoplay playsinline muted
            class="w-full h-full object-cover"
            x-show="cameraReady"
            style="transform: scaleX(-1);">
          </video>
          <canvas x-ref="canvas" class="hidden"></canvas>

          {{-- Loading --}}
          <div x-show="!cameraReady" class="absolute inset-0 flex flex-col items-center justify-center text-white gap-2">
            <svg class="w-8 h-8 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
            <p class="text-sm" x-text="loadingMsg"></p>
          </div>

          {{-- Face box --}}
          <div x-show="cameraReady && faceBox" x-cloak
            class="absolute border-2 rounded pointer-events-none transition-all duration-100"
            :class="faceStatus === 'matched' ? 'border-emerald-400' : (faceStatus === 'no-match' ? 'border-red-400' : 'border-yellow-400')"
            :style="`left: ${faceBoxCss.left}; top: ${faceBoxCss.top}; width: ${faceBoxCss.width}; height: ${faceBoxCss.height};`">
          </div>

          {{-- Face label --}}
          <div x-show="cameraReady && faceBox" x-cloak
            class="absolute bottom-2 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full text-xs font-semibold text-white"
            :class="faceStatus === 'matched' ? 'bg-emerald-500/90' : (faceStatus === 'no-match' ? 'bg-red-500/90' : 'bg-amber-500/90')"
            x-text="faceLabel">
          </div>
        </div>

        {{-- Status strip --}}
        <div class="grid grid-cols-2 divide-x divide-slate-100 border-t border-slate-100">
          <div class="p-3 flex items-center gap-2">
            <div class="w-2 h-2 rounded-full"
              :class="{
                'bg-slate-300': faceStatus === 'loading',
                'bg-yellow-400': faceStatus === 'no-face',
                'bg-emerald-500': faceStatus === 'matched',
                'bg-amber-400': faceStatus === 'no-enrolled',
                'bg-red-500': faceStatus === 'no-match',
              }">
            </div>
            <div>
              <p class="text-[10px] text-slate-400 leading-none">Wajah</p>
              <p class="text-xs font-medium text-slate-700 leading-snug" x-text="faceStatusText"></p>
            </div>
          </div>
          <div class="p-3 flex items-center gap-2">
            <div class="w-2 h-2 rounded-full"
              :class="{
                'bg-slate-300': gpsStatus === 'loading',
                'bg-emerald-500': geofenceOk || (gpsStatus === 'ok' && !needsGeofence),
                'bg-red-500': gpsStatus === 'ok' && needsGeofence && !geofenceOk,
                'bg-red-400': gpsStatus === 'error',
              }">
            </div>
            <div>
              <p class="text-[10px] text-slate-400 leading-none">Lokasi</p>
              <p class="text-xs font-medium text-slate-700 leading-snug" x-text="gpsStatusText"></p>
            </div>
          </div>
        </div>
      </div>

      {{-- Info check-in jika sudah check-in --}}
      @if ($attendance?->check_in_at)
        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-800 text-sm flex items-center gap-2">
          <x-icon name="check" class="w-4 h-4 shrink-0" />
          <span>Masuk pukul <strong>{{ $attendance->check_in_at->format('H:i') }}</strong>
            @if ($attendance->late_minutes > 0) - terlambat {{ $attendance->late_minutes }} mnt @endif
          </span>
        </div>
      @endif

      {{-- Peringatan checkout dini --}}
      @if ($showEarlyCheckoutWarning)
        @php
          $workedH = intdiv($workedMinutes, 60);
          $workedM = $workedMinutes % 60;
          $remainingMinutes = max(0, 480 - $workedMinutes);
          $remainH = intdiv($remainingMinutes, 60);
          $remainM = $remainingMinutes % 60;
        @endphp
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 space-y-3">
          <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
              <x-icon name="clock" class="w-5 h-5 text-amber-600" />
            </div>
            <div>
              <p class="text-sm font-semibold text-amber-900">Jam Kerja Belum Cukup</p>
              <p class="text-xs text-amber-700 mt-0.5">
                Anda baru bekerja
                <strong>{{ $workedH > 0 ? $workedH.'j ' : '' }}{{ $workedM }}m</strong>
                dari minimal <strong>8 jam</strong>.
                @if ($remainH > 0 || $remainM > 0)
                  Sisa <strong>{{ $remainH > 0 ? $remainH.'j ' : '' }}{{ $remainM }}m</strong> lagi.
                @endif
              </p>
            </div>
          </div>
          <div class="flex gap-2">
            <button wire:click="cancelEarlyCheckout" class="flex-1 py-2.5 rounded-lg text-sm font-medium bg-white border border-amber-200 text-amber-800 active:bg-amber-100 transition">
              Batal
            </button>
            <button @click="doCheckOut()" :disabled="processing"
              class="flex-1 py-2.5 rounded-lg text-sm font-semibold bg-amber-500 text-white active:bg-amber-600 disabled:opacity-50 transition">
              <span x-show="!processing">Tetap Check-out</span>
              <span x-show="processing" class="flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                Memproses…
              </span>
            </button>
          </div>
        </div>
      @endif

      {{-- Tombol aksi --}}
      @if (! $attendance?->check_in_at)
        <button @click="doCheckIn()" :disabled="!canProceed || processing"
          class="w-full bg-emerald-600 text-white rounded-xl py-4 text-base font-semibold disabled:opacity-40 transition active:scale-[0.98]">
          <span x-show="!processing">Check-in Sekarang</span>
          <span x-show="processing" class="flex items-center justify-center gap-2">
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
            Memproses…
          </span>
        </button>
      @elseif (! $showEarlyCheckoutWarning)
        <button @click="doCheckOut()" :disabled="!canProceed || processing"
          class="w-full bg-rose-600 text-white rounded-xl py-4 text-base font-semibold disabled:opacity-40 transition active:scale-[0.98]">
          <span x-show="!processing">Check-out Sekarang</span>
          <span x-show="processing" class="flex items-center justify-center gap-2">
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
            Memproses…
          </span>
        </button>
      @endif

      {{-- Pesan panduan --}}
      <p x-show="!canProceed && cameraReady" x-cloak class="text-xs text-center text-slate-500 -mt-1">
        <span x-show="faceStatus === 'no-face'">Arahkan wajah ke kamera agar terdeteksi</span>
        <span x-show="faceStatus === 'no-match'">Wajah tidak cocok - pastikan pencahayaan cukup atau hubungi admin</span>
        <span x-show="needsGeofence && !geofenceOk && gpsStatus !== 'loading'">Anda berada di luar radius kantor</span>
      </p>

    @endif {{-- end state check --}}

    {{-- Riwayat --}}
    @if ($history->isNotEmpty())
      <div class="mt-2">
        <h3 class="text-sm font-semibold text-slate-900 mb-3">Riwayat Terakhir</h3>
        <div class="space-y-2">
          @foreach ($history as $h)
            <div class="card p-3 flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-slate-900">{{ $h->attendance_date->translatedFormat('d M Y') }}</p>
                <p class="text-xs text-slate-500">
                  {{ $h->check_in_at?->format('H:i') ?? '—' }} → {{ $h->check_out_at?->format('H:i') ?? '—' }}
                </p>
              </div>
              <span class="badge bg-{{ $h->status?->color() ?? 'slate' }}-100 text-{{ $h->status?->color() ?? 'slate' }}-700">
                {{ $h->status?->label() ?? '—' }}
              </span>
            </div>
          @endforeach
        </div>
      </div>
    @endif

  </div>
</div>

@include('livewire.employee.partials.attendance-script')
