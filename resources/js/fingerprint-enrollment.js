window.fingerprintEnrollment = function ({ hasFingerprint }) {
    return {
        enrolling: false,
        statusMsg: '',
        supported: !!window.PublicKeyCredential,

        base64urlToBuffer(base64url) {
            const base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
            const padded = base64.padEnd(base64.length + ((4 - (base64.length % 4)) % 4), '=');
            return Uint8Array.from(atob(padded), (c) => c.charCodeAt(0)).buffer;
        },

        bufferToBase64url(buffer) {
            const bytes = new Uint8Array(buffer);
            let str = '';
            bytes.forEach((b) => (str += String.fromCharCode(b)));
            return btoa(str).replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
        },

        async registerFingerprint() {
            if (this.enrolling) return;

            if (!this.supported) {
                this.statusMsg = 'Perangkat ini tidak mendukung autentikasi biometrik.';
                return;
            }

            this.enrolling = true;
            this.statusMsg = 'Menghubungi server…';

            try {
                const options = await this.$wire.getRegistrationOptions();

                const createOptions = {
                    challenge: this.base64urlToBuffer(options.challenge),
                    rp: options.rp,
                    user: {
                        id: this.base64urlToBuffer(options.user.id),
                        name: options.user.name,
                        displayName: options.user.displayName,
                    },
                    pubKeyCredParams: options.pubKeyCredParams,
                    authenticatorSelection: options.authenticatorSelection,
                    timeout: options.timeout,
                    attestation: options.attestation,
                };

                this.statusMsg = 'Ikuti instruksi perangkat Anda…';

                const credential = await navigator.credentials.create({ publicKey: createOptions });

                const credentialId = this.bufferToBase64url(credential.rawId);
                const deviceName = this.getDeviceName();

                this.statusMsg = 'Menyimpan sidik jari…';
                await this.$wire.storeCredential(credentialId, deviceName);

                hasFingerprint = true;
                this.statusMsg = '';
            } catch (e) {
                if (e?.name === 'NotAllowedError') {
                    this.statusMsg = 'Pendaftaran dibatalkan atau sidik jari tidak dikenali.';
                } else if (e?.name === 'InvalidStateError') {
                    this.statusMsg = 'Perangkat ini sudah terdaftar.';
                } else {
                    console.error('[fingerprint-enrollment]', e);
                    this.statusMsg = 'Gagal mendaftarkan sidik jari. Coba lagi.';
                }
            } finally {
                this.enrolling = false;
            }
        },

        getDeviceName() {
            const ua = navigator.userAgent;
            if (/iPhone/.test(ua)) return 'iPhone';
            if (/iPad/.test(ua)) return 'iPad';
            if (/Android/.test(ua)) {
                const match = ua.match(/Android.*;\s*([^)]+)\)/);
                return match ? match[1].trim() : 'Android';
            }
            if (/Windows/.test(ua)) return 'Windows PC';
            if (/Mac/.test(ua)) return 'Mac';
            return 'Perangkat ini';
        },
    };
};
