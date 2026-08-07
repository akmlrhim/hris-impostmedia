<div x-data="biometricEnrollment({ hasFaceId: {{ $hasFaceId ? 'true' : 'false' }} })">

  {{-- Header --}}
  <div class="hero px-5 pt-7 pb-16">
    <div class="flex items-center gap-3">
      <a wire:navigate href="{{ route('mobile.profile.edit') }}"
        class="w-9 h-9 -ml-1 rounded-full bg-white/15 flex items-center justify-center active:bg-white/25 transition shrink-0">
        <x-icon name="arrow-left" class="w-5 h-5" />
      </a>
      <div class="flex-1 min-w-0">
        <h1 class="text-lg font-bold leading-tight truncate" x-text="biometricLabel">Biometrik</h1>
        <p class="text-xs text-navy-200 mt-0.5 truncate">Digunakan untuk verifikasi saat absensi</p>
      </div>
      <span class="hero-action {{ $hasFaceId ? 'text-emerald-200' : 'text-rose-200' }}">
        {{ $hasFaceId ? 'Terdaftar' : 'Belum terdaftar' }}
      </span>
    </div>
  </div>

  <div class="px-4 -mt-10 pb-32 space-y-4">

    {{-- Perangkat tidak didukung --}}
    <template x-if="!supported">
      <div class="card-float p-5 text-center space-y-3">
        <div class="w-14 h-14 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
          <x-icon name="scan-face" class="w-7 h-7" />
        </div>
        <div>
          <p class="font-semibold text-slate-900">Perangkat Tidak Didukung</p>
          <p class="text-xs text-slate-500 mt-1">Browser atau perangkat Anda tidak mendukung autentikasi biometrik. Gunakan browser terbaru di HP yang mendukung biometrik.</p>
        </div>
      </div>
    </template>

    {{-- Status card --}}
    <template x-if="supported">
      <div class="space-y-4">
        <div class="card-float p-5 flex items-center gap-4">
          <div class="w-14 h-14 rounded-full flex items-center justify-center shrink-0
            {{ $hasFaceId ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-400' }}">
            <x-icon name="scan-face" class="w-7 h-7" />
          </div>
          <div class="flex-1">
            @if ($hasFaceId)
              <p class="font-semibold text-slate-900"><span x-text="biometricLabel">Biometrik</span> terdaftar</p>
              <p class="text-xs text-slate-500 mt-0.5">{{ $deviceName ?: 'Perangkat ini' }} &bull; Siap digunakan untuk absensi.</p>
            @else
              <p class="font-semibold text-slate-900">Belum ada <span x-text="biometricLabel">biometrik</span></p>
              <p class="text-xs text-slate-500 mt-0.5">Daftarkan <span x-text="biometricLabel">biometrik</span> agar bisa melakukan absensi tanpa kata sandi.</p>
            @endif
          </div>
        </div>

        {{-- Panduan --}}
        <div class="card-float p-4 space-y-2">
          <p class="text-xs font-semibold text-slate-700">Cara kerja:</p>
          <ul class="space-y-1.5 text-xs text-slate-500">
            <li class="flex items-start gap-2">
              <x-icon name="check" class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" />
              <span>Tekan tombol di bawah untuk mendaftarkan <span x-text="biometricLabel">biometrik</span> perangkat ini</span>
            </li>
            <li class="flex items-start gap-2">
              <x-icon name="check" class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" />
              <span>Ikuti instruksi <span x-text="biometricLabel">biometrik</span> yang muncul di perangkat</span>
            </li>
            <li class="flex items-start gap-2">
              <x-icon name="check" class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" />
              <span>Setelah terdaftar, absensi cukup dengan sentuh tombol check-in</span>
            </li>
            <li class="flex items-start gap-2">
              <x-icon name="check" class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" />
              <span>Hanya satu perangkat yang bisa terdaftar per akun</span>
            </li>
          </ul>
        </div>

        {{-- Status pesan --}}
        <p x-show="statusMsg" x-text="statusMsg"
          class="text-sm text-center text-slate-600 bg-slate-50 rounded-xl px-4 py-3 border border-slate-100">
        </p>

        {{-- Tombol daftar --}}
        <button type="button" @click="registerBiometric()" :disabled="enrolling"
          class="btn-accent disabled:opacity-50">
          <span x-show="!enrolling" class="flex items-center justify-center gap-2">
            <x-icon name="scan-face" class="w-5 h-5" />
            <span x-text="{{ $hasFaceId ? "'Perbarui ' + biometricLabel" : "'Daftarkan ' + biometricLabel" }}"></span>
          </span>
          <span x-show="enrolling" class="flex items-center justify-center gap-2">
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
            Memproses…
          </span>
        </button>

        @if ($hasFaceId)
          <button type="button"
            wire:click="deleteCredential"
            :wire:confirm="'Hapus ' + biometricLabel + ' yang terdaftar? Anda tidak akan bisa absen biometrik hingga mendaftar ulang.'"
            class="btn-secondary w-full text-rose-600">
            Hapus <span x-text="biometricLabel">Biometrik</span>
          </button>
        @endif
      </div>
    </template>

  </div>
</div>
