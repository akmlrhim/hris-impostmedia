@push('head')
  <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
@endpush

@push('scripts')
<script>
function faceEnrollment({ hasFaceEnrolled }) {
    return {
        showCamera: false,
        cameraReady: false,
        stream: null,
        modelsLoaded: false,
        faceDetected: false,
        enrolling: false,
        statusMsg: '',
        detectionTimer: null,
        currentDescriptor: null,

        async init() {},

        async openCamera() {
            this.showCamera = true;
            this.cameraReady = false;
            this.faceDetected = false;
            this.statusMsg = 'Memuat kamera…';

            try {
                this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
                const video = this.$refs.video;
                video.srcObject = this.stream;
                await new Promise(resolve => video.onloadedmetadata = resolve);
                video.play();
                this.cameraReady = true;
            } catch (e) {
                this.statusMsg = 'Kamera tidak dapat diakses.';
                return;
            }

            if (!this.modelsLoaded) {
                this.statusMsg = 'Memuat model AI (±10 detik pertama)…';
                const MODEL_URL = 'https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/weights';
                try {
                    await Promise.all([
                        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                        faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODEL_URL),
                        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
                    ]);
                    this.modelsLoaded = true;
                } catch (e) {
                    this.statusMsg = 'Gagal memuat model. Periksa koneksi internet.';
                    return;
                }
            }

            this.statusMsg = 'Arahkan wajah ke kamera, pastikan pencahayaan cukup.';
            this.detectionTimer = setInterval(() => this.detectFace(), 1000);
        },

        async detectFace() {
            const video = this.$refs.video;
            if (!video || !this.modelsLoaded) return;

            const detection = await faceapi
                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224 }))
                .withFaceLandmarks(true)
                .withFaceDescriptor();

            this.faceDetected = !!detection;
            if (detection) {
                this.currentDescriptor = Array.from(detection.descriptor);
                this.statusMsg = 'Wajah terdeteksi! Klik "Simpan Wajah".';
            } else {
                this.currentDescriptor = null;
                this.statusMsg = 'Arahkan wajah ke kamera, pastikan pencahayaan cukup.';
            }
        },

        async captureAndEnroll() {
            if (!this.currentDescriptor) return;
            this.enrolling = true;
            clearInterval(this.detectionTimer);
            try {
                await this.$wire.call('enrollFace', this.currentDescriptor);
                hasFaceEnrolled = true;
                this.stopCamera();
            } finally {
                this.enrolling = false;
            }
        },

        stopCamera() {
            clearInterval(this.detectionTimer);
            if (this.stream) {
                this.stream.getTracks().forEach(t => t.stop());
                this.stream = null;
            }
            this.showCamera = false;
            this.faceDetected = false;
            this.cameraReady = false;
        },
    };
}
</script>
@endpush

<div>
  <div class="px-5 pt-6 pb-4 bg-white border-b border-slate-200 flex items-center gap-3 sticky top-0 z-10">
    <a wire:navigate href="{{ route('mobile.profile') }}" class="p-2 -ml-2 rounded-lg hover:bg-slate-100">
      <x-icon name="arrow-left" class="w-5 h-5" />
    </a>
    <h1 class="text-lg font-bold text-slate-900">Edit Profil</h1>
  </div>

  <div class="p-4 space-y-4 pb-32">
    {{-- Profile form --}}
    <form wire:submit="saveProfile" class="space-y-4">

      {{-- Avatar --}}
      <div class="card p-5">
        <div class="flex items-center gap-4">
          <div
            class="w-20 h-20 rounded-full bg-slate-200 overflow-hidden flex items-center justify-center text-2xl font-bold text-slate-500 shrink-0">
            @if ($avatar)
              <img src="{{ $avatar->temporaryUrl() }}" class="w-full h-full object-cover">
            @elseif ($employee?->avatar_path)
              <img src="{{ route('files.avatar', $employee) }}" class="w-full h-full object-cover">
            @else
              {{ strtoupper(substr($full_name ?: $name ?: '?', 0, 1)) }}
            @endif
          </div>
          <div class="flex-1">
            <label class="label">Foto Profil</label>
            <input type="file" wire:model="avatar" accept="image/*"
              class="block w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-slate-100 file:text-slate-700">
            <p class="text-[11px] text-slate-500 mt-1">JPG/PNG, max 2MB.</p>
            <div wire:loading wire:target="avatar" class="text-[11px] text-slate-500 mt-1">Mengunggah…</div>
            @error('avatar')
              <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
          </div>
        </div>
      </div>

      {{-- Akun --}}
      <div class="card p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-900 -mb-1">Akun</h3>

        <div>
          <label class="label">Nama Tampilan</label>
          <input wire:model="name" class="input">
          @error('name')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="label">Email</label>
          <input type="email" wire:model="email" class="input" inputmode="email">
          @error('email')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="label">Nama Lengkap</label>
          <input wire:model="full_name" class="input">
          @error('full_name')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="label">Nama Panggilan</label>
          <input wire:model="nickname" class="input">
        </div>

        <div>
          <label class="label">Telepon</label>
          <input wire:model="phone" class="input" inputmode="tel">
        </div>
      </div>

      {{-- Data pribadi --}}
      <div class="card p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-900 -mb-1">Data Pribadi</h3>

        <div>
          <label class="label">Jenis Kelamin</label>
          <select wire:model="gender" class="input">
            <option value="">— Pilih —</option>
            <option value="male">Laki-laki</option>
            <option value="female">Perempuan</option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="label">Tanggal Lahir</label>
            <input type="date" onclick="this.showPicker()" wire:model="date_of_birth" class="input">
            @error('date_of_birth')
              <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
          </div>
          <div>
            <label class="label">Tempat Lahir</label>
            <input wire:model="place_of_birth" class="input">
          </div>
        </div>

        <div>
          <label class="label">Agama</label>
          <select wire:model="religion" class="input">
            <option value="">— Pilih —</option>
            <option value="Islam">Islam</option>
            <option value="Kristen">Kristen</option>
            <option value="Katolik">Katolik</option>
            <option value="Hindu">Hindu</option>
            <option value="Budha">Budha</option>
            <option value="Konghucu">Konghucu</option>
          </select>
        </div>
      </div>

      {{-- Bank --}}
      <div class="card p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-900 -mb-1">Rekening Bank</h3>

        <div>
          <label class="label">Bank</label>
          <input wire:model="bank_name" class="input" placeholder="BCA / Mandiri">
        </div>

        <div>
          <label class="label">No. Rekening</label>
          <input wire:model="bank_account_number" class="input" inputmode="numeric">
        </div>

        <div>
          <label class="label">Atas Nama</label>
          <input wire:model="bank_account_holder" class="input">
        </div>
      </div>

      <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="saveProfile">
        <span wire:loading.remove wire:target="saveProfile">Simpan Profil</span>
        <span wire:loading wire:target="saveProfile">Menyimpan…</span>
      </button>
    </form>

    {{-- Face Enrollment --}}
    <div class="card p-5 space-y-4"
      x-data="faceEnrollment({ hasFaceEnrolled: {{ $hasFaceEnrolled ? 'true' : 'false' }} })"
      x-init="init()">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-sm font-semibold text-slate-900">Verifikasi Biometrik</h3>
          <p class="text-xs text-slate-500 mt-0.5">Daftarkan wajah untuk absensi selfie.</p>
        </div>
        @if ($hasFaceEnrolled)
          <span class="badge bg-emerald-100 text-emerald-700 text-xs">Terdaftar</span>
        @else
          <span class="badge bg-slate-100 text-slate-500 text-xs">Belum Terdaftar</span>
        @endif
      </div>

      {{-- Camera preview (hidden until opened) --}}
      <div x-show="showCamera" x-cloak class="space-y-3">
        <div class="relative bg-slate-900 rounded-xl overflow-hidden aspect-video">
          <video x-ref="video" autoplay playsinline muted
            class="w-full h-full object-cover"
            style="transform: scaleX(-1);"></video>
          <canvas x-ref="canvas" class="hidden"></canvas>

          <div x-show="!cameraReady" class="absolute inset-0 flex items-center justify-center">
            <svg class="w-6 h-6 animate-spin text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
          </div>

          <div x-show="faceDetected && cameraReady" x-cloak
            class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-emerald-500/80 text-white text-xs px-3 py-1 rounded-full">
            Wajah terdeteksi ✓
          </div>
          <div x-show="!faceDetected && cameraReady" x-cloak
            class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-slate-700/70 text-white text-xs px-3 py-1 rounded-full">
            Arahkan wajah ke kamera
          </div>
        </div>

        <p class="text-xs text-slate-500 text-center" x-text="statusMsg"></p>

        <div class="flex gap-2">
          <button type="button" @click="stopCamera()"
            class="btn-secondary flex-1 text-sm">Batal</button>
          <button type="button" @click="captureAndEnroll()"
            :disabled="!faceDetected || enrolling"
            class="btn-primary flex-1 text-sm disabled:opacity-40">
            <span x-show="!enrolling">Simpan Wajah</span>
            <span x-show="enrolling">Memproses…</span>
          </button>
        </div>
      </div>

      {{-- Actions --}}
      <div x-show="!showCamera" class="flex gap-2">
        <button type="button" @click="openCamera()"
          class="btn-primary flex-1 text-sm">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          {{ $hasFaceEnrolled ? 'Perbarui Wajah' : 'Daftarkan Wajah' }}
        </button>
        @if ($hasFaceEnrolled)
          <button type="button" wire:click="deleteFace" wire:confirm="Hapus data wajah yang terdaftar?"
            class="btn-secondary text-sm text-red-600">Hapus</button>
        @endif
      </div>
    </div>

    {{-- Password --}}
    <form wire:submit="changePassword" class="card p-5 space-y-4">
      <h3 class="text-sm font-semibold text-slate-900 -mb-1">Ubah Kata Sandi</h3>

      <div>
        <label class="label">Kata Sandi Saat Ini</label>
        <div class="relative" x-data="{ show: false }">
          <input :type="show ? 'text' : 'password'" wire:model="current_password" class="input pr-10"
            autocomplete="current-password">
          <button type="button" @click="show = !show" tabindex="-1"
            class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600"
            :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
            <x-icon name="eye" class="w-5 h-5" x-show="!show" />
            <x-icon name="eye-off" class="w-5 h-5" x-show="show" x-cloak />
          </button>
        </div>
        @error('current_password')
          <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="label">Kata Sandi Baru</label>
        <div class="relative" x-data="{ show: false }">
          <input :type="show ? 'text' : 'password'" wire:model="new_password" class="input pr-10"
            autocomplete="new-password">
          <button type="button" @click="show = !show" tabindex="-1"
            class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600"
            :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
            <x-icon name="eye" class="w-5 h-5" x-show="!show" />
            <x-icon name="eye-off" class="w-5 h-5" x-show="show" x-cloak />
          </button>
        </div>
        @error('new_password')
          <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="label">Konfirmasi Kata Sandi Baru</label>
        <div class="relative" x-data="{ show: false }">
          <input :type="show ? 'text' : 'password'" wire:model="new_password_confirmation" class="input pr-10"
            autocomplete="new-password">
          <button type="button" @click="show = !show" tabindex="-1"
            class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600"
            :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
            <x-icon name="eye" class="w-5 h-5" x-show="!show" />
            <x-icon name="eye-off" class="w-5 h-5" x-show="show" x-cloak />
          </button>
        </div>
      </div>

      <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="changePassword">
        <span wire:loading.remove wire:target="changePassword">Ubah Kata Sandi</span>
        <span wire:loading wire:target="changePassword">Memproses…</span>
      </button>
    </form>
  </div>
</div>
