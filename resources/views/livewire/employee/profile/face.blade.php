<x-face-api />

<div x-data="faceEnrollment({ hasFaceEnrolled: {{ $hasFaceEnrolled ? 'true' : 'false' }} })">

  {{-- Header --}}
  <x-mobile-header title="Data Wajah" subtitle="Digunakan untuk verifikasi saat absensi"
    :back="route('mobile.profile.edit')">
    <x-slot:action>
      <span class="hero-action {{ $hasFaceEnrolled ? 'text-emerald-200' : 'text-rose-200' }}">
        {{ $hasFaceEnrolled ? 'Terdaftar' : 'Belum terdaftar' }}
      </span>
    </x-slot:action>
  </x-mobile-header>

  <div class="px-4 -mt-10 pb-32 space-y-4">

    {{-- Status card --}}
    <div class="card-float p-5 flex items-center gap-4">
      <div class="w-14 h-14 rounded-full flex items-center justify-center shrink-0
        {{ $hasFaceEnrolled ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-400' }}">
        <x-icon name="scan-face" class="w-7 h-7" />
      </div>
      <div class="flex-1">
        @if ($hasFaceEnrolled)
          <p class="font-semibold text-slate-900">Wajah sudah terdaftar</p>
          <p class="text-xs text-slate-500 mt-0.5">Data wajah Anda siap digunakan untuk absensi.</p>
        @else
          <p class="font-semibold text-slate-900">Belum ada data wajah</p>
          <p class="text-xs text-slate-500 mt-0.5">Daftarkan wajah agar bisa melakukan absensi selfie.</p>
        @endif
      </div>
    </div>

    {{-- Panduan --}}
    <div class="card-float p-4 space-y-2">
      <p class="text-xs font-semibold text-slate-700">Tips pendaftaran wajah:</p>
      <ul class="space-y-1.5 text-xs text-slate-500">
        <li class="flex items-start gap-2"><x-icon name="check" class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" /> Pastikan pencahayaan cukup dan merata</li>
        <li class="flex items-start gap-2"><x-icon name="check" class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" /> Hadapkan wajah langsung ke kamera</li>
        <li class="flex items-start gap-2"><x-icon name="check" class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" /> Lepas kacamata hitam atau masker</li>
        <li class="flex items-start gap-2"><x-icon name="check" class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" /> Jangan bergerak saat menekan "Simpan"</li>
      </ul>
    </div>

    {{-- Kamera --}}
    <div x-show="showCamera" x-cloak class="space-y-3">
      <div class="card-float overflow-hidden">
        <div class="relative bg-slate-900 aspect-video flex items-center justify-center">
          <video x-ref="video" autoplay playsinline muted
            class="w-full h-full object-cover"
            style="transform: scaleX(-1);">
          </video>

          {{-- Loading overlay --}}
          <div x-show="!cameraReady" class="absolute inset-0 flex flex-col items-center justify-center text-white gap-2">
            <svg class="w-8 h-8 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
            <p class="text-sm" x-text="statusMsg"></p>
          </div>

          {{-- Face detected indicator --}}
          <div x-show="cameraReady && faceDetected" x-cloak
            class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-emerald-500/90 text-white text-xs font-semibold px-3 py-1 rounded-full">
            <x-icon name="check" class="w-3.5 h-3.5 shrink-0" /> Wajah terdeteksi
          </div>
          <div x-show="cameraReady && !faceDetected" x-cloak
            class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-slate-700/80 text-white text-xs px-3 py-1 rounded-full">
            Arahkan wajah ke kamera
          </div>
        </div>
      </div>

      <p class="text-xs text-center text-slate-500" x-text="statusMsg"></p>

      <div class="grid grid-cols-2 gap-3">
        <button type="button" @click="stopCamera()" class="btn-secondary">Batal</button>
        <button type="button" @click="captureAndEnroll()"
          :disabled="!faceDetected || enrolling"
          class="btn-accent py-2.5 disabled:opacity-40">
          <span x-show="!enrolling">Simpan Wajah</span>
          <span x-show="enrolling">Menyimpan…</span>
        </button>
      </div>
    </div>

    {{-- Tombol aksi (kamera tertutup) --}}
    <div x-show="!showCamera" class="space-y-3">
      <button type="button" @click="openCamera()" class="btn-accent">
        {{ $hasFaceEnrolled ? 'Perbarui Data Wajah' : 'Daftarkan Wajah Sekarang' }}
      </button>

      @if ($hasFaceEnrolled)
        <button type="button"
          wire:click="deleteFace"
          wire:confirm="Hapus data wajah yang terdaftar? Anda tidak akan bisa absen hingga mendaftar ulang."
          class="btn-secondary w-full text-rose-600">
          Hapus Data Wajah
        </button>
      @endif
    </div>

  </div>
</div>

@include('livewire.employee.partials.face-script')
