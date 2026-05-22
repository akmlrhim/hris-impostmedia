window.faceEnrollment = function ({ hasFaceEnrolled }) {
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

        async openCamera() {
            this.showCamera = true;
            this.cameraReady = false;
            this.faceDetected = false;
            this.statusMsg = 'Memuat kamera…';

            await new Promise((r) => requestAnimationFrame(r));

            try {
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
                });
                const video = this.$refs.video;
                if (!video) {
                    this.statusMsg = 'Elemen video tidak ditemukan.';
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
                    this.statusMsg = 'Ketuk layar untuk memulai kamera.';
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
                    this.statusMsg = 'Kamera tidak menghasilkan gambar. Coba refresh halaman.';
                    return;
                }
            } catch (e) {
                console.error('[camera]', e);
                this.statusMsg = 'Kamera tidak dapat diakses. Izinkan akses kamera.';
                return;
            }

            if (!this.modelsLoaded) {
                this.statusMsg = 'Memuat model AI (sekitar 5–10 detik)…';
                const MODEL_URL = window.FACE_API_MODEL_URL;
                try {
                    await Promise.all([
                        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                        faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODEL_URL),
                        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
                    ]);
                    this.modelsLoaded = true;
                } catch (e) {
                    this.statusMsg = 'Gagal memuat model AI.';
                    return;
                }
            }

            this.statusMsg = 'Arahkan wajah ke kamera, pastikan pencahayaan cukup.';
            this.detectionTimer = setInterval(() => this.detectFace(), 800);
        },

        async detectFace() {
            const video = this.$refs.video;
            if (!video || !this.modelsLoaded || !this.cameraReady) return;

            const detection = await faceapi
                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224 }))
                .withFaceLandmarks(true)
                .withFaceDescriptor();

            this.faceDetected = !!detection;
            if (detection) {
                this.currentDescriptor = Array.from(detection.descriptor);
                this.statusMsg = 'Wajah terdeteksi! Tekan "Simpan Wajah".';
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
                this.stream.getTracks().forEach((t) => t.stop());
                this.stream = null;
            }
            this.showCamera = false;
            this.faceDetected = false;
            this.cameraReady = false;
            this.statusMsg = '';
        },
    };
};
