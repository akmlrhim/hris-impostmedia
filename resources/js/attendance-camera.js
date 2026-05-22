window.attendanceCamera = function ({ workType, officeLocations, faceDescriptor, hasFaceEnrolled }) {
    return {
        stream: null,
        cameraReady: false,
        loadingMsg: 'Memuat kamera…',

        modelsLoaded: false,
        faceStatus: 'loading',
        faceBox: null,
        faceBoxCss: { left: '0', top: '0', width: '0', height: '0' },
        lastDescriptor: null,
        detectionTimer: null,

        gpsStatus: 'loading',
        geofenceOk: false,
        latitude: null,
        longitude: null,
        address: '',

        processing: false,

        get needsGeofence() {
            return workType === 'wfo';
        },

        get faceLabel() {
            const map = {
                loading: 'Memuat…',
                'no-face': 'Arahkan wajah ke kamera',
                matched: 'Wajah Cocok ✓',
                'no-match': 'Wajah Tidak Cocok ✗',
                'no-enrolled': 'Belum terdaftar',
            };
            return map[this.faceStatus] ?? '';
        },

        get faceStatusText() {
            const map = {
                loading: 'Memuat model AI…',
                'no-face': 'Tidak terdeteksi',
                matched: 'Terverifikasi',
                'no-match': 'Tidak cocok',
                'no-enrolled': 'Belum terdaftar',
            };
            return map[this.faceStatus] ?? '';
        },

        get gpsStatusText() {
            if (this.gpsStatus === 'loading') return 'Mendapatkan lokasi…';
            if (this.gpsStatus === 'error') return 'Gagal mendapatkan lokasi';
            if (!this.needsGeofence) return 'Lokasi diperoleh';
            return this.geofenceOk ? 'Dalam radius kantor' : 'Di luar radius';
        },

        get canProceed() {
            const locationOk = !this.needsGeofence || this.geofenceOk;
            return this.cameraReady && locationOk;
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
                    video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
                });
                const video = this.$refs.video;
                if (!video) {
                    this.loadingMsg = 'Elemen video tidak ditemukan.';
                    return;
                }
                video.muted = true;
                video.setAttribute('playsinline', '');
                video.srcObject = this.stream;

                await new Promise((resolve) => {
                    if (video.readyState >= 1) return resolve();
                    video.onloadedmetadata = () => resolve();
                });

                try {
                    await video.play();
                } catch (playErr) {
                    this.loadingMsg = 'Ketuk layar untuk memulai kamera.';
                    const resume = async () => {
                        try { await video.play(); } catch (_) {}
                        document.removeEventListener('click', resume);
                        document.removeEventListener('touchstart', resume);
                    };
                    document.addEventListener('click', resume, { once: true });
                    document.addEventListener('touchstart', resume, { once: true });
                }

                await new Promise((resolve) => {
                    if (video.videoWidth > 0 && !video.paused) return resolve();
                    const onPlaying = () => {
                        video.removeEventListener('playing', onPlaying);
                        resolve();
                    };
                    video.addEventListener('playing', onPlaying);
                    setTimeout(resolve, 2000);
                });

                this.cameraReady = video.videoWidth > 0;
                if (!this.cameraReady) {
                    this.loadingMsg = 'Kamera tidak menghasilkan gambar. Coba refresh halaman.';
                }
            } catch (e) {
                console.error('[camera]', e);
                this.loadingMsg = 'Kamera tidak dapat diakses. Izinkan akses kamera.';
            }
        },

        async loadModels() {
            this.loadingMsg = 'Memuat model AI…';
            const MODEL_URL = window.FACE_API_MODEL_URL;
            try {
                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                    faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODEL_URL),
                    faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
                ]);
                this.modelsLoaded = true;
                this.faceStatus = 'no-face';
            } catch (e) {
                this.loadingMsg = 'Gagal memuat model AI.';
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
            const mirroredX = vw - (box.x + box.width) * scaleX;
            this.faceBoxCss = {
                left: mirroredX + 'px',
                top: box.y * scaleY + 'px',
                width: box.width * scaleX + 'px',
                height: box.height * scaleY + 'px',
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
                },
                () => {
                    this.gpsStatus = 'error';
                },
                { enableHighAccuracy: true, timeout: 12000 },
            );
        },

        checkGeofence() {
            if (!this.needsGeofence) {
                this.geofenceOk = true;
                return;
            }
            this.geofenceOk = officeLocations.some(
                (o) => this.haversine(this.latitude, this.longitude, o.latitude, o.longitude) <= o.radius_meters,
            );
        },

        haversine(lat1, lon1, lat2, lon2) {
            const R = 6371000;
            const dLat = ((lat2 - lat1) * Math.PI) / 180;
            const dLon = ((lon2 - lon1) * Math.PI) / 180;
            const a =
                Math.sin(dLat / 2) ** 2 +
                Math.cos((lat1 * Math.PI) / 180) * Math.cos((lat2 * Math.PI) / 180) * Math.sin(dLon / 2) ** 2;
            return R * 2 * Math.asin(Math.sqrt(a));
        },

        captureFrame() {
            const video = this.$refs.video;
            const canvas = this.$refs.canvas;
            if (!video || !canvas || !video.videoWidth) {
                return null;
            }
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0);
            return canvas.toDataURL('image/jpeg', 0.75);
        },

        notify(message, type = 'error') {
            if (window.Livewire && typeof window.Livewire.dispatch === 'function') {
                window.Livewire.dispatch('notify', { message, type });
            }
        },

        async doCheckIn() {
            if (this.processing) return;
            if (!this.cameraReady) {
                this.notify('Kamera belum siap. Tunggu sebentar.', 'warning');
                return;
            }
            if (this.needsGeofence && !this.geofenceOk) {
                this.notify('Anda berada di luar radius kantor.', 'error');
                return;
            }
            this.processing = true;
            try {
                const photo = this.captureFrame();
                if (!photo) {
                    this.notify('Gagal mengambil foto. Coba lagi.', 'error');
                    return;
                }
                await this.$wire.checkIn(photo, this.latitude, this.longitude, this.address);
            } catch (e) {
                console.error('[checkIn]', e);
                this.notify('Check-in gagal: ' + (e?.message || 'kesalahan jaringan'), 'error');
            } finally {
                this.processing = false;
            }
        },

        async doCheckOut() {
            if (this.processing) return;
            if (!this.cameraReady) {
                this.notify('Kamera belum siap. Tunggu sebentar.', 'warning');
                return;
            }
            this.processing = true;
            try {
                const photo = this.captureFrame();
                if (!photo) {
                    this.notify('Gagal mengambil foto. Coba lagi.', 'error');
                    return;
                }
                await this.$wire.checkOut(photo, this.latitude, this.longitude, this.address);
            } catch (e) {
                console.error('[checkOut]', e);
                this.notify('Check-out gagal: ' + (e?.message || 'kesalahan jaringan'), 'error');
            } finally {
                this.processing = false;
            }
        },

        onAttendanceRecorded() {
            clearInterval(this.detectionTimer);
            if (this.stream) this.stream.getTracks().forEach((t) => t.stop());
        },

        destroy() {
            clearInterval(this.detectionTimer);
            if (this.stream) this.stream.getTracks().forEach((t) => t.stop());
        },
    };
};
