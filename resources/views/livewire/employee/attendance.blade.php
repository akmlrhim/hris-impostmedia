@push('head')
  <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
@endpush

<div
  x-data="attendanceCamera({
    workType: '{{ $workType->value }}',
    officeLocations: @json($officeLocations->values()),
    faceDescriptor: @json($faceDescriptor),
    hasFaceEnrolled: {{ $faceDescriptor ? 'true' : 'false' }},
    hasCheckedIn: {{ $attendance?->check_in_at ? 'true' : 'false' }},
    hasCheckedOut: {{ $attendance?->check_out_at ? 'true' : 'false' }},
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
      <h1 class="text-lg font-bold text-slate-900">Absensi Saya</h1>
      @if ($isAdminPanelUser)
        <p class="text-xs text-slate-400 leading-none mt-0.5">{{ auth()->user()?->role?->label() }}</p>
      @endif
    </div>
    @if ($employee)
      <span class="badge text-xs px-2 py-0.5
        {{ $workType->value === 'wfo' ? 'bg-blue-100 text-blue-700' : ($workType->value === 'wfh' ? 'bg-emerald-100 text-emerald-700' : 'bg-purple-100 text-purple-700') }}">
        {{ $workType->label() }}
      </span>
    @endif
  </div>

  <div class="px-5 pt-4 space-y-4 pb-32">

    @if (! $employee)
      {{-- No employee record linked --}}
      <div class="card p-6 text-center space-y-3 mt-4">
        <div class="w-14 h-14 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center mx-auto">
          <x-icon name="alert-triangle" class="w-7 h-7" />
        </div>
        <div>
          <p class="font-semibold text-slate-900">Akun belum terhubung ke data karyawan</p>
          <p class="text-sm text-slate-500 mt-1">
            Agar dapat melakukan absensi, akun Anda perlu dihubungkan ke profil karyawan.<br>
            Hubungi Super Admin atau HR untuk mengatur ini.
          </p>
        </div>
        @if ($isAdminPanelUser)
          <a wire:navigate href="{{ route('admin.employees') }}" class="btn-primary inline-flex text-sm">
            <x-icon name="users" class="w-4 h-4" /> Kelola Karyawan
          </a>
        @endif
      </div>

    @else

    {{-- Date & Clock --}}
    <div class="card p-4 text-center">
      <p class="text-sm text-slate-500">{{ now()->translatedFormat('l, d F Y') }}</p>
      <p x-data="{ now: new Date() }" x-init="setInterval(() => now = new Date(), 1000)"
        x-text="now.toLocaleTimeString('id-ID')"
        class="text-4xl font-bold text-slate-900 mt-1"></p>
    </div>

    {{-- Already completed today --}}
    @if ($attendance?->check_out_at)
      <div class="card p-5 bg-emerald-50 border border-emerald-100 space-y-1 text-emerald-800 text-sm">
        <p class="font-semibold text-base">Absensi hari ini selesai</p>
        <p>Check-in: <strong>{{ $attendance->check_in_at->format('H:i') }}</strong></p>
        <p>Check-out: <strong>{{ $attendance->check_out_at->format('H:i') }}</strong></p>
        <p>Total kerja: <strong>{{ floor($attendance->work_minutes / 60) }}j {{ $attendance->work_minutes % 60 }}m</strong></p>
      </div>

    @else
      {{-- Camera preview --}}
      <div class="card overflow-hidden">
        <div class="relative bg-slate-900 aspect-video flex items-center justify-center">
          <video x-ref="video" autoplay playsinline muted
            class="w-full h-full object-cover"
            x-show="cameraReady"
            style="transform: scaleX(-1);">
          </video>
          <canvas x-ref="canvas" class="hidden"></canvas>

          {{-- Loading overlay --}}
          <div x-show="!cameraReady" class="absolute inset-0 flex flex-col items-center justify-center text-white gap-2">
            <svg class="w-8 h-8 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
            <p class="text-sm" x-text="loadingMsg"></p>
          </div>

          {{-- Face detection box --}}
          <div x-show="cameraReady && faceBox" x-cloak
            class="absolute border-2 rounded pointer-events-none transition-all duration-100"
            :class="faceStatus === 'matched' ? 'border-emerald-400' : (faceStatus === 'no-match' ? 'border-red-400' : 'border-yellow-400')"
            :style="`left: ${faceBoxCss.left}; top: ${faceBoxCss.top}; width: ${faceBoxCss.width}; height: ${faceBoxCss.height};`">
          </div>

          {{-- Face label --}}
          <div x-show="cameraReady && faceBox" x-cloak
            class="absolute bottom-2 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full text-xs font-medium text-white"
            :class="faceStatus === 'matched' ? 'bg-emerald-500/80' : (faceStatus === 'no-match' ? 'bg-red-500/80' : 'bg-yellow-500/80')"
            x-text="faceLabel">
          </div>
        </div>

        {{-- Status strip --}}
        <div class="grid grid-cols-2 divide-x divide-slate-100 border-t border-slate-100">
          {{-- Face status --}}
          <div class="p-3 flex items-center gap-2">
            <div class="w-2 h-2 rounded-full"
              :class="{
                'bg-slate-300': faceStatus === 'loading',
                'bg-yellow-400': faceStatus === 'no-face',
                'bg-emerald-500': faceStatus === 'matched' || faceStatus === 'no-enrolled',
                'bg-red-500': faceStatus === 'no-match',
              }">
            </div>
            <div>
              <p class="text-[10px] text-slate-400 leading-none">Wajah</p>
              <p class="text-xs font-medium text-slate-700 leading-snug" x-text="faceStatusText"></p>
            </div>
          </div>

          {{-- GPS / Geofence status --}}
          <div class="p-3 flex items-center gap-2">
            <div class="w-2 h-2 rounded-full"
              :class="{
                'bg-slate-300': gpsStatus === 'loading',
                'bg-emerald-500': geofenceOk,
                'bg-yellow-400': gpsStatus === 'ok' && !needsGeofence,
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

      {{-- Check-in done banner --}}
      @if ($attendance?->check_in_at)
        <div class="p-3 rounded-xl bg-emerald-50 text-emerald-800 text-sm flex items-center gap-2">
          <x-icon name="check" class="w-4 h-4 shrink-0" />
          Check-in pukul <strong>{{ $attendance->check_in_at->format('H:i') }}</strong>
          @if ($attendance->late_minutes > 0)
            — terlambat {{ $attendance->late_minutes }} mnt
          @endif
        </div>
      @endif

      {{-- Action button --}}
      @if (! $attendance?->check_in_at)
        <button
          @click="doCheckIn()"
          :disabled="!canProceed || processing"
          class="w-full bg-emerald-600 text-white rounded-xl py-4 font-semibold flex items-center justify-center gap-2 disabled:opacity-40 transition active:scale-95">
          <template x-if="!processing">
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              <span>Check-in</span>
            </div>
          </template>
          <template x-if="processing">
            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
          </template>
        </button>

        <p x-show="!canProceed && cameraReady" x-cloak class="text-xs text-center text-slate-500">
          <span x-show="faceStatus === 'no-face'">Pastikan wajah Anda terlihat di kamera</span>
          <span x-show="faceStatus === 'no-match'">Wajah tidak dikenali. Coba lagi atau hubungi admin.</span>
          <span x-show="needsGeofence && !geofenceOk && gpsStatus !== 'loading'">Anda di luar radius kantor yang ditentukan.</span>
        </p>

      @else
        <button
          @click="doCheckOut()"
          :disabled="!canProceed || processing"
          class="w-full bg-rose-600 text-white rounded-xl py-4 font-semibold flex items-center justify-center gap-2 disabled:opacity-40 transition active:scale-95">
          <template x-if="!processing">
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"/>
              </svg>
              <span>Check-out</span>
            </div>
          </template>
          <template x-if="processing">
            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
          </template>
        </button>
      @endif

      {{-- No face enrolled notice --}}
      @if (! $faceDescriptor)
        <div class="p-3 rounded-xl bg-amber-50 border border-amber-100 text-amber-800 text-xs flex items-start gap-2">
          <x-icon name="alert-triangle" class="w-4 h-4 shrink-0 mt-0.5" />
          <span>Wajah belum terdaftar. Daftarkan wajah di <a wire:navigate href="{{ route('mobile.profile.edit') }}" class="underline font-medium">halaman profil</a> untuk verifikasi biometrik.</span>
        </div>
      @endif
    @endif {{-- end check_out_at --}}

    @endif {{-- end employee check --}}

    {{-- History --}}
    <div class="mt-2">
      <h3 class="text-sm font-semibold text-slate-900 mb-3">Riwayat 10 Hari Terakhir</h3>
      <div class="space-y-2">
        @forelse ($history as $h)
          <div class="card p-3 flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-slate-900">{{ $h->attendance_date->translatedFormat('d M Y') }}</p>
              <p class="text-xs text-slate-500">
                {{ $h->check_in_at?->format('H:i') ?? '—' }} → {{ $h->check_out_at?->format('H:i') ?? '—' }}
              </p>
            </div>
            <span class="badge bg-{{ $h->status?->color() }}-100 text-{{ $h->status?->color() }}-700">
              {{ $h->status?->label() }}
            </span>
          </div>
        @empty
          <p class="text-sm text-slate-500 text-center py-6">Belum ada riwayat.</p>
        @endforelse
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
function attendanceCamera({ workType, officeLocations, faceDescriptor, hasFaceEnrolled, hasCheckedIn, hasCheckedOut }) {
    return {
        // Camera
        stream: null,
        cameraReady: false,
        loadingMsg: 'Memuat kamera…',

        // Face
        modelsLoaded: false,
        faceStatus: 'loading', // loading | no-face | matched | no-match | no-enrolled
        faceBox: null,
        faceBoxCss: { left: '0', top: '0', width: '0', height: '0' },
        lastDescriptor: null,
        detectionTimer: null,

        // GPS
        gpsStatus: 'loading', // loading | ok | error
        geofenceOk: false,
        latitude: null,
        longitude: null,
        address: '',

        // Action
        processing: false,

        get needsGeofence() {
            return workType === 'wfo';
        },

        get faceLabel() {
            const map = {
                loading: 'Memuat…',
                'no-face': 'Arahkan wajah ke kamera',
                matched: 'Wajah Cocok ✓',
                'no-match': 'Wajah Tidak Dikenali',
                'no-enrolled': 'Wajah Terdeteksi',
            };
            return map[this.faceStatus] ?? '';
        },

        get faceStatusText() {
            const map = {
                loading: 'Memuat model…',
                'no-face': 'Tidak terdeteksi',
                matched: 'Terverifikasi',
                'no-match': 'Tidak cocok',
                'no-enrolled': 'Terdeteksi (belum terdaftar)',
            };
            return map[this.faceStatus] ?? '';
        },

        get gpsStatusText() {
            if (this.gpsStatus === 'loading') return 'Mendapatkan lokasi…';
            if (this.gpsStatus === 'error') return 'Gagal';
            if (!this.needsGeofence) return 'Diperoleh';
            return this.geofenceOk ? 'Dalam radius' : 'Di luar radius';
        },

        get canProceed() {
            const faceOk = this.faceStatus === 'matched' || this.faceStatus === 'no-enrolled';
            const locationOk = !this.needsGeofence || this.geofenceOk;
            return faceOk && locationOk;
        },

        async init() {
            await this.startCamera();
            this.getLocation();
            await this.loadModels();
            if (this.modelsLoaded) {
                this.startDetection();
            }
        },

        async startCamera() {
            this.loadingMsg = 'Memuat kamera…';
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }
                });
                const video = this.$refs.video;
                video.srcObject = this.stream;
                await new Promise(resolve => video.onloadedmetadata = resolve);
                video.play();
                this.cameraReady = true;
            } catch (e) {
                this.loadingMsg = 'Kamera tidak dapat diakses.';
                console.error('Camera error', e);
            }
        },

        async loadModels() {
            this.loadingMsg = 'Memuat model AI…';
            const MODEL_URL = 'https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/weights';
            try {
                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                    faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODEL_URL),
                    faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
                ]);
                this.modelsLoaded = true;
                this.faceStatus = 'no-face';
            } catch (e) {
                console.error('Model load error', e);
                this.faceStatus = 'no-enrolled';
            }
        },

        startDetection() {
            this.detectionTimer = setInterval(() => this.detectFace(), 1200);
        },

        async detectFace() {
            const video = this.$refs.video;
            if (!video || !this.modelsLoaded || !this.cameraReady) return;

            const detection = await faceapi
                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224 }))
                .withFaceLandmarks(true)
                .withFaceDescriptor();

            if (!detection) {
                this.faceBox = null;
                this.faceStatus = 'no-face';
                return;
            }

            this.updateFaceBox(detection.detection.box, video);

            if (!hasFaceEnrolled || !faceDescriptor) {
                this.faceStatus = 'no-enrolled';
                this.lastDescriptor = detection.descriptor;
                return;
            }

            const stored = new Float32Array(faceDescriptor);
            const distance = faceapi.euclideanDistance(stored, detection.descriptor);
            this.faceStatus = distance < 0.5 ? 'matched' : 'no-match';
            if (this.faceStatus === 'matched') {
                this.lastDescriptor = detection.descriptor;
            }
        },

        updateFaceBox(box, video) {
            this.faceBox = box;
            const vw = video.offsetWidth;
            const vh = video.offsetHeight;
            const scaleX = vw / video.videoWidth;
            const scaleY = vh / video.videoHeight;
            // Mirror horizontally because video is CSS-flipped
            const mirroredX = vw - (box.x + box.width) * scaleX;
            this.faceBoxCss = {
                left: mirroredX + 'px',
                top: (box.y * scaleY) + 'px',
                width: (box.width * scaleX) + 'px',
                height: (box.height * scaleY) + 'px',
            };
        },

        getLocation() {
            if (!navigator.geolocation) {
                this.gpsStatus = 'error';
                return;
            }
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.latitude = pos.coords.latitude;
                    this.longitude = pos.coords.longitude;
                    this.address = `${pos.coords.latitude.toFixed(6)}, ${pos.coords.longitude.toFixed(6)}`;
                    this.gpsStatus = 'ok';
                    this.checkGeofence();
                    this.$wire.set('latitude', this.latitude);
                    this.$wire.set('longitude', this.longitude);
                    this.$wire.set('address', this.address);
                },
                () => { this.gpsStatus = 'error'; },
                { enableHighAccuracy: true, timeout: 12000 }
            );
        },

        checkGeofence() {
            if (!this.needsGeofence) { this.geofenceOk = true; return; }
            this.geofenceOk = officeLocations.some(o =>
                this.haversine(this.latitude, this.longitude, o.latitude, o.longitude) <= o.radius_meters
            );
        },

        haversine(lat1, lon1, lat2, lon2) {
            const R = 6371000;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) ** 2
                + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) ** 2;
            return R * 2 * Math.asin(Math.sqrt(a));
        },

        captureFrame() {
            const video = this.$refs.video;
            const canvas = this.$refs.canvas;
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            // Flip to match natural orientation (undo CSS mirror)
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0);
            return canvas.toDataURL('image/jpeg', 0.75);
        },

        async doCheckIn() {
            this.processing = true;
            try {
                const photo = this.captureFrame();
                await this.$wire.set('checkInPhoto', photo);
                await this.$wire.call('checkIn');
            } finally {
                this.processing = false;
            }
        },

        async doCheckOut() {
            this.processing = true;
            try {
                const photo = this.captureFrame();
                await this.$wire.set('checkOutPhoto', photo);
                await this.$wire.call('checkOut');
            } finally {
                this.processing = false;
            }
        },

        onAttendanceRecorded() {
            clearInterval(this.detectionTimer);
            if (this.stream) {
                this.stream.getTracks().forEach(t => t.stop());
            }
        },

        destroy() {
            clearInterval(this.detectionTimer);
            if (this.stream) {
                this.stream.getTracks().forEach(t => t.stop());
            }
        },
    };
}
</script>
@endpush
